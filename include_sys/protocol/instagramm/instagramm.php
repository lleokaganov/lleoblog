<?php
/*
    Great thanks for script:

    https://phpbl.ru/php/instagram-api-avtomaticheskij-posting.html

use:
    $GLOBALS['instagramm_cookie']='cookie.txt';
    $GLOBALS['instagramm_username']='dmitry_vaskovske';
    $GLOBALS['instagramm_password']='nbgfgfhjkm123';

    $o=instagramm_send('./photo.jpg','РњРѕСЏ Р¶РµРЅР° Р‘РёР±РёРіСЋР»СЊ РЅР° РјРѕСЂРµ'); // UTF-8
    print_r($o,1);

http://habrahabr.ru/post/166773/
*/





// а эта процедура тупо парсит главную страницу инстаграмма, чтобы надыбать адреса страниц для только что загруженной картинки $id
// пример вызова: $o=instagramm_geturl("1092366765374540978_277291506","lleokaganov"); if(gettype($o)!=='array') die("ERROR: ".$o); echo print_r($o,1);

function instagramm_geturl($id,$user) {
    $s=file_get_contents("https://instagram.com/".$user."/");
    if(empty($s)) return 'Instagramm error wget';
    if(!preg_match("/window\._sharedData = \{(.*?)\};/s",$s,$m)) return 'Instagramm error match';
    $s=json_decode("{".$m[1]."}")->entry_data->ProfilePage[0]->user->media->nodes;
    foreach($s as $n=>$l) if($l->id."_".$l->owner->id == $id)
	return array('url'=>"https://instagram.com/p/".($l->code)."/",'img'=>($l->thumbnail_src),'pre'=>($l->display_src));
    return 'Instagramm error not found';
}




function instagramm_r($url, $post, $post_data, $user_agent, $cookies) {
	if(empty($GLOBALS['instagramm_cookie'])) return 'Cookie file not defined';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://instagram.com/api/v1/'.$url);
        curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }
	curl_setopt($ch,($cookies?CURLOPT_COOKIEFILE:CURLOPT_COOKIEJAR),$GLOBALS['instagramm_cookie']);
        $r=curl_exec($ch); curl_close($ch);

        if(empty($r)) return "Empty response received from ".$url;
        if(strpos($r,"Sorry, an error occurred while processing this request.")) return "Request failed, there's a chance that this proxy/ip is blocked ".$url;
        if(strpos($r,"login_required")) return "You are not logged in. There's a chance that the account is banned";
        $obj=@json_decode($r,true); if(empty($obj)) return "Could not decode the response ".$url;
        if($obj['status']=='fail') return "Status is fail: `".$obj['message']."`"." ".print_r((array)$obj,1);
        return (array)$obj;
}

function instagramm_GenerateGuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0,65535),mt_rand(0,65535),mt_rand(0,65535),mt_rand(16384,20479),mt_rand(32768,49151),mt_rand(0,65535),mt_rand(0,65535),mt_rand(0,65535));
}

function instagramm_GenerateUserAgent() {
        $resolutions = array('720x1280', '320x480', '480x800', '1024x768', '1280x720', '768x1024', '480x320');
        $versions = array('GT-N7000', 'SM-N9000', 'GT-I9220', 'GT-I9100');
        $dpis = array('120', '160', '320', '240');

        $ver = $versions[array_rand($versions)];
        $dpi = $dpis[array_rand($dpis)];
        $res = $resolutions[array_rand($resolutions)];

        return 'Instagram 4.'.mt_rand(1,2).'.'.mt_rand(0,2).' Android ('.mt_rand(10,11).'/'.mt_rand(1,3).'.'.mt_rand(3,5).'.'.mt_rand(0,5).'; '.$dpi.'; '.$res.'; samsung; '.$ver.'; '.$ver.'; smdkc210; en_US)';
}

function instagramm_GenerateSignature($d) { return hash_hmac('sha256', $d, 'b4a23f5e39b5929e0666ac5de94c89d1618a2916'); }

function instagramm_send($filename, $caption) {
        $username = $GLOBALS['instagramm_username'];
        $password = $GLOBALS['instagramm_password'];
	if($username==''||$password=='') return 'Login/Password not set';
	if(!is_file($filename)) return "The image doesn't exist ".$filename;

	// Login (Cookie)
        $agent = instagramm_GenerateUserAgent();
        $guid = instagramm_GenerateGuid();

        $data = '{"device_id":"android-'.$guid.'","guid":"'.$guid.'","username":"'.$username.'","password":"'.$password.'","Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}';
        $data = 'signed_body='.instagramm_GenerateSignature($data).'.'.urlencode($data).'&ig_sig_key_version=4';
        $obj=instagramm_r('accounts/login/',true,$data,$agent, false); if(gettype($obj)!='array') return $obj;

	// Status - media_id
        $data = array('device_timestamp' => time(),'photo' => '@'.$filename);
        $obj=instagramm_r('media/upload/', true, $data, $agent, true); if(gettype($obj)!='array') return $obj;
        if($obj['status']!='ok') return "Status isn't ok: `".$obj['status'].": ".$obj['message']."`";

        $data = json_encode(
	    (object)array(
            'device_id' => "android-".$guid,
            'guid' => $guid,
            'media_id' => $obj['media_id'],
            'caption' => trim($caption),
            'device_timestamp' => time(),
            'source_type' => '5',
            'filter_type' => '0',
            'extra' => '{}',
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8')
	);
        $data = 'signed_body='.instagramm_GenerateSignature($data).'.'.urlencode($data).'&ig_sig_key_version=4';
        $conf = instagramm_r('media/configure/', true, $data, $agent, true); if(gettype($obj)!='array') return $obj;
        return $obj;
}

?>