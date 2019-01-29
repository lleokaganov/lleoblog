<?php // Протоколы внешних соцсетей

// idie($GLOBALS['net']." ".$GLOBALS['nett']);

/* вообще документация на каждый протокол:

VK:
https://vk.com/dev/methods

FB:
https://developers.facebook.com/docs/graph-api/reference/v2.2
Вот окно теста: https://developers.facebook.com/tools/explorer

LJ:
http://www.livejournal.com/doc/server/ljp.csp.proplist.html







Instagramm:

use:
    $GLOBALS['instagram_cookie']='cookie.txt';
    $GLOBALS['instagram_login']='dmitryvaskovske';
    $GLOBALS['instagram_password']='nbgfgfhjkm123';
    $o=instagramm_send('./photo.jpg','Моя жена Бибигюль на море'); // UTF-8
    print_r($o,1);

include_once $GLOBALS['include_sys'].'protocol/instagramm/instagramm.php';

$GLOBALS['instagramm_cookie']=$GLOBALS['hosthidden'].'instagramm_cookie.txt';
$GLOBALS['instagramm_username']='';
$GLOBALS['instagramm_password']='';
$o=instagramm_send(rtrim($GLOBALS['host'],'/').$file,wu('Фото №2 с поездки на Байкал #Байкал'));

*/








function gettags_($num) { $r=gettags($num); foreach($r as $n=>$l) $r[$n]=str_replace(str_split("'".' ,.-=+?!#@%&^"*()<>`~:;{}[]|\\/'),'_',$l); return $r; }



// =============================================================================

function image2instagram($from,$to) { // вырезать квадратиком середину картинки из файла $from в файл $to
    require_once($GLOBALS['include_sys']."_fotolib.php");
    list($W,$H,$itype)=getimagesize($from);
    if(false===($img1=openimg($from,$itype))) return 0;
    $X=min($W,$H); $img2=imagecreatetruecolor_addalpha($X,$X,$itype);
    if($W>$H) { $w=($W-$X)/2; $h=0; } else { $h=($H-$X)/2; $w=0; }
    imagecopy($img2,$img1,0,0,$w,$h,$X,$X);
    closeimg($img2,$to,$itype,90); imagedestroy($img1);
    return 1;
}


function instagramm_url($l,$user,$type){
    if($type=='post') return "https://instagram.com/".$user."/";
    if(!strstr($l,'|')) return "https://instagram.com/".$user."/".$l;
    list($url,$img,$pre)=explode('|',$l,3); return $url;
}


function del_instagramm($i){ global $nett,$num,$include_sys,$net,$user,$nscr; // $nscr=$r['n']; $user=$r['user'];
    if(($r=get_autopost($nett))===false) idie('Error flat');
    if(false===($p=ms("SELECT `type`,`id` FROM `socialmedias` WHERE `i`='".e($i)."'".ANDC(),"_1",0))) return 'Deleted already';
    $id=$p['id'];

    $GLOBALS['instagramm_cookie']=$GLOBALS['hosthidden'].'instagramm_cookie.txt';
    $GLOBALS['instagramm_username']=$r[2];
    $GLOBALS['instagramm_password']=$r[3];
    include_once $include_sys.'protocol/instagramm/instagramm.php';
    $o=instagramm_del($id); // /api/v1/media/{media_id}/delete/ media_id
    // msq("DELETE FROM `socialmedias` WHERE `i`='".e($i)."'".ANDC());
    idie($o);
    return "OK";
}


function autopost_instagramm($r){ global $nett,$num,$include_sys,$net,$user,$nscr; $nscr=$r['n']; $user=$r['user'];

    $title=(empty($r['Header'])?$r['Date']:$r['Date'].' - '.$r['Header']);
    $tags=gettags_($GLOBALS['num']); $tags=(sizeof($tags)?' #'.implode(' #',$tags):'');

    // 0. ищем фотки
    $imgs=search_fotos(plain_Body($r['p']),1); if(!sizeof($imgs)) return 'photos: 0';

    // 3. позаливать фотки

    $newfotos=0; // счетчик
    $oldfotos=0; // счетчик

    include_once $GLOBALS['include_sys'].'protocol/instagramm/instagramm.php';

    foreach($imgs[0] as $nn=>$l) {
	$l2=savefoto_cutname($l); // обрезать до 128 символов, а то пипец базе
	$file=rtrim($GLOBALS['host'],'/').substr($l,strlen($GLOBALS['httpsite']));
        if(false===($p=ms("SELECT `num`,`i`,`id` FROM `socialmedias` WHERE `type`='instagramm_foto' AND `url`='".e($l2)."'".ANDP0(),"_1",0))) {

	// залить фотку
	    $GLOBALS['instagramm_cookie']=$GLOBALS['hosthidden'].'instagramm_cookie.txt';
	    $GLOBALS['instagramm_username']=$user;
	    $GLOBALS['instagramm_password']=$r[3];
	    $txt=$imgs['txt'][$nn];

	    $o=instagramm_send($file,wu($title.' - '.$txt.$tags));
	    if(gettype($o)!='array' && (strstr($o,'allowed aspect ratio')||strstr($o,"image isn't in the right format"))) { // не грузится фотка
		$tmp=$GLOBALS['hosttmp']."tmp_instagram.jpg"; if(!image2instagram($file,$tmp)) idie("INSTAGRAM: Error image crop"); // обрезать квадратом
		$o=instagramm_send($tmp,wu($title.' - '.$txt.$tags)); // и снова попробовать
	    }

	    if(gettype($o)!='array') { // совсем не грузится фотка - ну что ж, пометить как ошибку
		msq_add_update("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'type'=>'instagramm_foto','num'=>$GLOBALS['num'],'url'=>$l2,'id'=>'error')),"net acn type num url ANDC");
		continue;
	    }

	    if(!isset($o['media_id'])) dier($o,'media_id not found');
	    $id=$o['media_id'];
	    $o=instagramm_geturl($id,$user); if(gettype($o)=='array') $id=$o['url']."|".$o['img']."|".$o['pre'];
	    msq_add_update("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'type'=>'instagramm_foto','num'=>$GLOBALS['num'],'url'=>$l2,'id'=>$id)),"net acn type num url ANDC");
	    // idie('zalito '.$l2." @".$id);
	    microimg_send($l);

	} else { // если фотка уже залита
	    if($p['num']!=$num) { $oldfotos++; continue; } // если эта фотка уже была залита в другую заметку - ничего не делать
	    $newfotos++;
	}
    }

    if(!$newfotos) return "<b>".$nett."</b> <font color=green>0</font>".($oldfotos?" (".$oldfotos." old)":'');
    $u='https://instagram.com/'.h($user);
    return "<a href='".$u."'>".$u."</a> <font color=green>".$newfotos.($oldfotos?" (".$oldfotos.")</font>":'');
}

//=======================================================================================





$GLOBALS['fbkey_name']='tmp.fb-access_code-';
$GLOBALS['vkkey_name']='tmp.vk-access_code-';
$GLOBALS['yakey_name']='tmp.ya-access_code-';

// ===========================================================================
function POST_atom($urla,$ara,$oauth,$ct='',$test) { if($ct=='') $ct="application/atom+xml; charset=utf-8; type=entry";
        $url=array_merge(array('scheme'=>'http','port'=>80),parse_url($urla));
        if(!($fp=fsockopen($url['host'],$url['port']))) return "ERROR: can't open url ".$url['host'].":".$url['port'];
        if(fputs($fp,"POST ".$url['path']." HTTP/1.1\r\nHost: ".$url['host']."\r\n"
	    ."Content-Type: ".$ct."\r\n"
	    ."Authorization: OAuth ".$oauth."\r\n"
	    ."Content-Length: ".strlen($ara)."\r\n"
."\r\n".$ara)===false) return "ERROR: can't send #1";
        // и получить ответ
	if(!function_exists('feof_fp')) include_once $GLOBALS['include_sys']."_files.php";
        $s=feof_fp($fp); if($s=='') return "ERROR: NO RESPONSE";
	return $s;
}

function POST_put($urla,$ara,$oauth,$ct="application/atom+xml; charset=utf-8; type=entry") {
        $url=array_merge(array('scheme'=>'http','port'=>80),parse_url($urla));
        if(!($fp=fsockopen($url['host'],$url['port']))) return "ERROR: can't open url ".$url['host'].":".$url['port'];
        if(fputs($fp,"PUT ".$url['path']." HTTP/1.1\r\nHost: ".$url['host']."\r\n"
	    ."Content-Type: ".$ct."\r\n"
	    ."Authorization: OAuth ".$oauth."\r\n"
	    ."Content-Length: ".strlen($ara)."\r\n"
."\r\n".$ara)===false) return "ERROR: can't send #1";
        // и получить ответ
	if(!function_exists('feof_fp')) include_once $GLOBALS['include_sys']."_files.php";
        $s=feof_fp($fp); if($s=='') return "ERROR: NO RESPONSE";
	return $s;
}

function POST_del($urla,$oauth) {
        $url=array_merge(array('scheme'=>'http','port'=>80),parse_url($urla));
        if(!($fp=fsockopen($url['host'],$url['port']))) return "ERROR: can't open url ".$url['host'].":".$url['port'];
        if(fputs($fp,"DELETE ".$url['path']." HTTP/1.1"."\r\nHost: ".$url['host']."\r\n"
	    ."Authorization: OAuth ".$oauth."\r\n"
."\r\n")===false) return "ERROR: can't send #1";
        // и получить ответ
	if(!function_exists('feof_fp')) include_once $GLOBALS['include_sys']."_files.php";
        $s=feof_fp($fp); if($s=='') return "ERROR: NO RESPONSE";
	return $s;
}

function gokey_yandex() { $n=$GLOBALS['nscr'];
    // account hack protect
    savesite($GLOBALS['yakey_name'].$GLOBALS['user'],'asc');
    $url="https://oauth.yandex.ru/authorize?response_type=code&client_id=".$GLOBALS['r'][3]."&state=".$n."|".$GLOBALS['num'];
otprav("if(winp_".$n.") winp_".$n.".close(); else var winp_".$n.";
winp_".$n."=window.open('".$url."','Yandex Login','width='+(getWinW()*0.8)+',height='+(getWinH()*0.8));
if(winp_".$n."===null) ohelpc('yalogin_".$n."','Yandex Login',\""
."Please login in Yandex: <input type='button' value='Yandex Login' onclick=\\\"winp_".$n."=window.open('".$url."','Yandex Login','width='+(getWinW()*0.8)+',height='+(getWinH()*0.8));\\\"\");
");
}

function yandex_key() { // если нет ключа - залогиниться
    if(isset($GLOBALS['yandex_key'])) return;
    if(($GLOBALS['yandex_key']=loadsite($GLOBALS['yakey_name'].$GLOBALS['user']))===false) gokey_yandex(); // если нет ключа - залогиниться
}

function yandex_getid($s) {
 if(preg_match("/.*201 CREATED.*\r\nLocation: [^\s]+\/(\d+)\/\r\n/si",$s,$m) && $m[1]) return $m[1];
 if(preg_match("/.*401 UNAUTHORIZED.*Invalid token/si",$s)) gokey_yandex();
 idie(nl2br(h(uw($s))),'Yandex: get ID error');
}

function ANDP() { return " AND `num`='".e($GLOBALS['num'])."' AND `net`='".e($GLOBALS['nett'])."'".ANDC(); }
function ANDP0() { return " AND `net`='".e($GLOBALS['nett'])."'".ANDC()." LIMIT 1"; }

function del_yandex($i){ global $nett,$net,$user; list($net,$user)=explode(':',$nett);
    if(false===($p=ms("SELECT `type`,`id` FROM `socialmedias` WHERE `i`='".e($i)."'".ANDC(),"_1",0))) return 'Deleted already';
    yandex_key();
    $t=$p['type'];
    if($t=='post') $u="http://api-fotki.yandex.ru/api/users/".$user."/album/".$p['id']."/";
    else return "unknown type ".h($t);
    $s=POST_del($u,$GLOBALS['yandex_key']);
    if(!strstr($s,'204 NO CONTENT')) idie("Yandex del error");
    msq("DELETE FROM `socialmedias` WHERE `num`='".e($GLOBALS['num'])."' AND `net`='".e($GLOBALS['nett'])."'".ANDC());
    return "OK";
}

// создать альбом (если не было корневого - то создать и корневой)
function yandex_mkalbum(){ global $nett,$user,$num,$yandex_album,$yandex_album_name,$yandex_album_title;
    if(isset($yandex_album)) return;

    yandex_key();
    $url0="http://api-fotki.yandex.ru/api/users/".$user."/";
    $url=$url0."albums/";

    // 1. разобраться с корневым альбомом, если надо - создать
    $ra='rootalbum'; if(false===($rootalbum=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post' AND `num`='0' AND `net`='".e($nett)."' AND `url`='".$ra."'".ANDC(),"_l",0))) {
        $s='<entry xmlns="http://www.w3.org/2005/Atom" xmlns:f="yandex:fotki"><title>autopost</title><summary>Blog autopost system</summary></entry>';
        $s=POST_atom($url,wu($s),$GLOBALS['yandex_key'],'','root_album');
	$rootalbum=yandex_getid($s);
	msq_add_update("socialmedias",arae(array('acn'=>$GLOBALS['acn'],'net'=>$nett,'type'=>'post','num'=>0,'url'=>$ra,'cap_sha1'=>'','id'=>$rootalbum)),"net type num url ANDC");
    }

    // 2. создать альбом сегодняшней заметки, если не было
    $u=$url0."album/".$rootalbum;
    if(false===($yandex_album=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post' AND `url`='".e($yandex_album_name)."'".ANDP(),"_l",0))) {
        $s="<entry xmlns=\"http://www.w3.org/2005/Atom\" xmlns:f=\"yandex:fotki\">"
	."<title>".h($yandex_album_name)."</title>"."<summary>".h($yandex_album_title)."</summary>"."<link href=\"".$u."/\" rel=\"album\" />"."</entry>";
        $s=POST_atom($url,wu($s),$GLOBALS['yandex_key'],'','create_album');
	$yandex_album=yandex_getid($s);
	msq_add("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'url'=>$yandex_album_name,'cap_sha1'=>'','id'=>$yandex_album)));
    }
    return $yandex_album;
}

function autopost_yandex($r){ global $nett,$num,$include_sys,$net,$user,$nscr,$yandex_album_name,$yandex_album_title; $nscr=$r['n']; $user=$r['user'];

///eeeeeeeeeeeeeeeeeeeeeeeee
// return 'error php';

    $yandex_album_name=$r['Date'];
    $yandex_album_title=(empty($r['Header'])?$r['Date']:$r['Date'].' - '.$r['Header']);

    // 0. ищем фотки
    $imgs=search_fotos(plain_Body($r['p'])); if(!sizeof($imgs)) return 'photos: 0';

    // 3. позаливать фотки

    $newfotos=0; // счетчик
    $oldfotos=0; // счетчик

    foreach($imgs[0] as $nn=>$l) {
	$l2=savefoto_cutname($l); // обрезать до 128 символов, а то пипец базе
        if(false===($p=ms("SELECT `num`,`i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='ya_foto' AND `url`='".e($l2)."'".ANDP0(),"_1",0))) {
	// залить фотку
	yandex_mkalbum(); // проверить, есть ли альбом
	$f=fileget($l); if($f=='') idie("Error load my photo: ".h($l));
	$i=explode('.',$l); $i=strtolower($i[sizeof($i)-1]);
	    if($i=='jpg'||$i=='jpeg') $ct="image/jpeg";
	    elseif($i=='gif') $ct="image/gif";
	    elseif($i=='png') $ct="image/png";
	    else idie("Wrong image type ".h($l));

        $u="http://api-fotki.yandex.ru/api/users/".$user."/album/".$GLOBALS['yandex_album']."/photos/";
        $s=POST_atom($u,$f,$GLOBALS['yandex_key'],$ct,'LOAD: '.h($l));
	$id=yandex_getid($s);
	} else { // если фотка уже залита
	    if($p['num']!=$num) { $oldfotos++; continue; } // если эта фотка уже была залита в другую заметку - ничего не делать
	    $newfotos++;
	    list($id,$origlink)=explode('|',$p['id'],2);
	}

	$textf=$imgs['txt'][$nn]; $cap_sha1=sha1($textf);
        if(isset($p['cap_sha1']) && $cap_sha1==$p['cap_sha1']) continue; // если ничего не надо править
	// поправить описание
        include_once $GLOBALS['include_sys'].'protocol/_protocol_patchs.php';
	yandex_mkalbum(); // проверить, есть ли альбом
        $s=file_get_contents_https("http://api-fotki.yandex.ru/api/users/".$r['user']."/photo/".$id."/?oauth_token=".$GLOBALS['yandex_key']);
	// найти линк
	if(!preg_match("/<content src=[\'\"]*([^\'\"\s]+)/si",$s,$m)) idie("Yandex: foto error orig link".nl2br(h($s))); $origlink=$m[1];
	// исправить
        $s=preg_replace("/<title>[^<>]+<\/title>/si","<title>".h($l)."</title><summary>".h(wu($textf))."</summary>",$s);
        $s=preg_replace("/<f\:access value=\"[^\"\'<>]+\" \/>/si","<f:access value=\"public\" />",$s);
        $s=POST_put("http://api-fotki.yandex.ru/api/users/".$r['user']."/photo/".$id."/",$s,$GLOBALS['yandex_key']);
	if(!strstr($s,"HTTP/1.1 200 OK")) idie("Yandex Error Update Foto".nl2br(h($s)));

	$newfotos++;

        msq_add_update("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'type'=>'ya_foto','num'=>$GLOBALS['num'],'url'=>$l2,'cap_sha1'=>$cap_sha1,'id'=>$id.'|'.$origlink)),"net acn type num url ANDC");
	microimg_send($l);
    }

//    yandex_mkalbum(); // проверить, есть ли альбом
//    msq_add_update('socialmedias',arae(array('acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'net'=>$nett,'id'=>$GLOBALS['yandex_album'])),"num net id type ANDC");

    if(!$newfotos) return "<b>".$nett."</b> <font color=green>0</font>".($oldfotos?" (".$oldfotos." old)":'');
    yandex_mkalbum(); $z=rtrim(yandex_mkurl($GLOBALS['yandex_album'],$user),'/');
    return "<a href='".h($z)."'>".h($z)."</a> <font color=green>".$newfotos.($oldfotos?" (".$oldfotos.")</font>":'');
}


// ===========================================================================
// function loadsite($n,$i=0) { return ms("SELECT `text` FROM `site` WHERE `name`='".e($n)."'".ANDC(),"_l",$i); }
// function delsite($n) { return msq("DELETE FROM `site` WHERE `name`='".e($n)."'".ANDC()); }
// function savesite($n,$v) { $u=msq_add_update('site',array('name'=>e($n),'text'=>e($v),'acn'=>$GLOBALS['acn']),'name ANDC'); }

function gokey_facebook() { $n=$GLOBALS['nscr']; $num=$GLOBALS['num']; $AppID=$GLOBALS['r'][3];
$returnurl=acc_link($GLOBALS['acc'],$GLOBALS['wwwhost']."ajax/protocol.php?n=".$n."&num=".$num);

$url="https://www.facebook.com/v2.2/dialog/oauth?client_id=".$AppID
."&redirect_uri=".urlencode($returnurl)
."&response_type=code"
."&scope="
.($GLOBALS['fb_version']==2?'':"user_notes,offline_access,publish_stream,photo_upload,")
."user_photos,manage_pages,read_stream".",publish_actions";
// ohelpc('fblogin','Facebook <a href=\\'".$url."\\'>Login</a>',\"<iframe name='fblogin_ifr' id='fblogin_ifr' src='".$url."' onload='ajaxoff()' style='"."width:\"+(getWinW()*0.8)+\"px;height:\"+(getWinH()*0.8)+\"px;'></iframe>\");ajaxon();

// https://www.facebook.com/dialog/oauth?client_id=641978925872395&redirect_uri=http%3A%2F%2Flleo.me%2Fdnevnik%2Fajax%2Fprotocol.php%3Fn%3D10%26num%3D2857&response_type=code&scope=user_note,user_photos,photo_upload,manage_pages,offline_access,publish_stream,read_stream

    // account hack protect
    savesite($GLOBALS['fbkey_name'].$GLOBALS['user'],'asc');

otprav("
if(winp_".$n.") winp_".$n.".close(); else var winp_".$n.";
winp_".$n."=window.open('".$url."','Facebook Login','width='+(getWinW()*0.8)+',height='+(getWinH()*0.8));
if(winp_".$n."===null) ohelpc('fblogin_".$n."','Facebook Login',\""
// Facebook need login, but your browser block popup window."
// ."<div><a href='".$url."'>".$url."</a></div>"
."Please login in Facebook: <input type='button' value='Facebook Login' onclick=\\\"winp_".$n."=window.open('".$url."','Facebook Login','width='+(getWinW()*0.8)+',height='+(getWinH()*0.8));\\\"\");
");
}


function test_deprecate_dir($dir) { $f=rtrim($dir,'/')."/.htaccess";
	    if(is_file($f)) return;
	    testdir($dir);
	    fileput($f,"strongly nahui deprecated");
}

function fb_go($a,$id=0,$s='',$fotos,$email='') { $go=0;
    $zametka=false;
    $script=$GLOBALS['filehost']."extended/phantom-facebook.js";
    if(!is_file($script)) return "Error: not install ".$script;

    $domain=$GLOBALS['r'][2]; $domain=preg_replace("/[^a-z0-9\-\_\.]+/si",'',$domain);
    $pass=$GLOBALS['r'][3]; $pass=preg_replace("/[\n\r\t\"\']+/s",'',$pass);
    $id=preg_replace("/[^\d]+/s",'',$id);

// [Fri Jun 30 14:29:45 2017] [error] [client 162.158.88.111] PHP Fatal error:  Can't use function return value in write context in /home/lleo/binoniq.ru/htdocs/include_sys/protocol/protocols.php on line 390
    // $pdir=$GLOBALS['filehost'].rpath("hidden/user/".(empty(accd())?'x':accd())."/".$domain); // ЙОБАНАЯ СРАНАЯ НЕПОНЯТНАЯ ОШИБКА ТОЛЬКО НА FREEBSD ПОЯВИЛАСЬ
    $pdir=$GLOBALS['filehost'].rpath("hidden/user/".(accd()==''?'x':accd())."/".$domain);

    test_deprecate_dir($pdir);
    $LSdir=$pdir."/fb_LS"; test_deprecate_dir($LSdir);

    $SHOTdir=$pdir."/fb_SHOT"; test_deprecate_dir($SHOTdir);
    foreach(glob($SHOTdir."/*") as $l) { if(basename($l)!=".htaccess") unlink($l); } // удалить все шоты если были

    $logfile=$pdir."/log.txt";

    if($a=='ver') return "phantomjs ver: ".exec("phantomjs -v");

    $PHANTOM="phantomjs --debug=false --disk-cache=false --local-storage-path=\"".escapeshellcmd($LSdir)."\" --cookies-file=\"".escapeshellcmd($pdir)."/fb_cookie.dat\" --ssl-protocol=any"
." ".escapeshellcmd($script)
." debug=1"
." logfile=\"".escapeshellcmd($logfile)."\""
." shotdir=\"".escapeshellcmd($SHOTdir)."\""
." domain=\"".escapeshellcmd($domain)."\""
." email=-" // \"".escapeshellcmd(($domain)."\""
." pass=-" // \"".escapeshellcmd(($pass)."\""
;

// " DEL id=${ID} d

    if($a=='DEL') {
	if(empty($id)) return "No ID";
        $go=$PHANTOM." DEL id=".escapeshellcmd($id);
    }

    if($a=='EDIT') {
	if(empty($s)) return "No text";
	if(empty($id)) return "No ID";
	$zametka=wu($s); $go=$PHANTOM." EDIT id=\"".escapeshellcmd($id)."\" saveto=\"".escapeshellcmd($pdir)."/fb_posts.txt\" -";
	// $zametka_file=$pdir."/fb_zametka.txt"; fileput($zametka_file,wu($s));
        // $go=$PHANTOM." EDIT id=".h($id)." saveto=\"".$pdir."/fb_posts.txt\" \"".$zametka_file."\"";
	    // if(gettype($fotos)=='array') { if(sizeof($fotos)==1) $fotos[]=$fotos[0]; foreach($fotos as $img) $go.=" \"".h($img)."\""; }
    }

    if($a=='POST') {
	if(empty($s)) return "No text";
	$zametka=wu($s); $go=$PHANTOM." POST saveto=\"".escapeshellcmd($pdir)."/fb_posts.txt\" -";
	// $zametka_file=$pdir."/fb_zametka.txt"; fileput($zametka_file,wu($s));
        // $go=$PHANTOM." POST saveto=\"".$pdir."/fb_posts.txt\" \"".$zametka_file."\"";

/*
     if(gettype($fotos)=='array') // взять первую и не ебать мозг
	// if(sizeof($fotos)>1) // $fotos[]=$fotos[0];
	foreach($fotos as $img) {
	    $img=preg_replace("/[\n\r\t\"\']+/s",'',$img); if(!is_file($img)) idie("ERROR!!! IMG ERROR!!! ".h($img));
	    $go.=" \"".h($img)."\"";
	}
*/
    }

    if(!$go) return "Error action: `".h($a)."`";


$flog=fileget($logfile); // log

if($flog != "") { // если есть лог, но нету его копии
    $o=$flog;
} else {

    if(is_file($logfile)) { unlink($logfile); touch($logfile); chmod($logfile,0666); } // обнуляем лог

    $proc=proc_open($go,array(array("pipe","r"),array("pipe","w"),array("pipe","w")),$pipes);
	fwrite($pipes[0],($email==''?$domain:$email)."\n");
	fwrite($pipes[0],$pass."\n");
	if($zametka) fwrite($pipes[0],str_replace("\n","\\n",$zametka)."\n");
    $o=stream_get_contents($pipes[1]);

    fileput($pdir."/olog.txt",$o); // мы получили и сохраним
}

    $lastlog=$pdir."/lastlog.txt";
    if(is_file($lastlog)) { unlink($lastlog); }
    if(is_file($logfile)) { rename($logfile,$lastlog); if(!is_file($lastlog)) idie("Write error: ".h($lastlog)); }

    $o=explode("\n",rtrim($o,"\n"));

    $lin=sizeof($o)-1;
    $last=$o[$lin];
    while(--$lin>=0 && ( c0($last)=='' || strstr($last,'Unsafe JavaScript') )) $last=$o[$lin];
/*
    [34] => DONE: Posted success: https://m.facebook.com/lleokaganov/posts/1499261353452979
    [35] => Unsafe JavaScript attempt to access frame with URL about:blank from frame with URL file:///home/lleo/binoniq.ru/htdocs/extended/phantom-facebook.js. Domains, protocols and ports must match.
    [36] => 
    [37] => Unsafe JavaScript attempt to access frame with URL about:blank from frame with URL file:///home/lleo/binoniq.ru/htdocs/extended/phantom-facebook.js. Domains, protocols and ports must match.
    [38] => 
    [39] => Unsafe JavaScript attempt to access frame with URL about:blank from frame with URL file:///home/lleo/binoniq.ru/htdocs/extended/phantom-facebook.js. Domains, protocols and ports must match.
*/

    list($err,$res)=explode(': ',$last,2);
    if($err!='DONE') return "<font color=red>".h($last)."</font>";
    return $res;
}




function fb_url($id,$user){ if(empty($id)) idie("empty id"); return "https://m.facebook.com/".h($user)."/posts/".$id; }


function del_fb($i) { global $num,$net,$nett,$user,$r; if(($r=get_autopost($nett))===false) idie('Error flat');
    if(false===($id=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_l",0))) return 'Deleted already';
    $x=fb_go("DEL",$id,0,$r[4]);
    if(!preg_match("/ id=(\d+)$/s",$x,$m)) return "$nett: ".$x; if($m[1]!=$id) idie("id error: `".h($id)."` != `".h($m[1])."`");
    // а если все хорошо
    msq("DELETE FROM `socialmedias` WHERE `num`='".e($num)."' AND `net`='".e($nett)."'".ANDC());
    return 'OK';
}

function autopost_fb($r){ global $num,$include_sys,$net,$user,$nscr,$nett; $user=$r['user']; $nscr=$r['n'];

// dier($GLOBALS['r']);

    $head=$r['Date']." ".(empty($r['Header'])?"":$r['Header']);
    $link=getlink($r['Date']);
    $s=plain_Body($r['p']);
    $ulink=$GLOBALS['r']['url'];
// idie(nl2br(h($s)));
    $fotos=array(); $imgs=search_fotos($s);

    foreach($imgs[0] as $n=>$l) {
	$file=rtrim($GLOBALS['host'],'/').rpath(substr($l,strlen($GLOBALS['httpsite'])));
	if(!is_file($file)) continue; // idie("Foto not found: ".h($file));
	$fotos[]=$file;
	if(sizeof($imgs[0])==1) {
		$imz="\n\n[ см. фото ]\n\n"; // если фотка единственная
		$a=explode($l,$s,2); if(c0($a[0])=='') { $s=$a[1]; break; } // если фотка единственная и стояла в самом верху - вообещ не помечать, сами увидят
	} else $imz="\n\n[ см. фото № ".($n+1)." в заметке ]\n\n"; // если фотка единственная
	if(basename($l)=='play_youtube.png') $imz="\n\n[ тут в оригинальном посте открывается видеоролик Ютуба ]";
	$s=str_replace($l,$imz,$s);
    }

/*
//    preg_match_all("/<(object|iframe)[^>]*?>/si",$r['text'],$objs, PREG_PATTERN_ORDER);
    foreach($objs[0] as $n=>$l) {
	if(strtoupper($objs[1][$n])=='IFRAME' && preg_match("/src=[\'\"]*([^>\'\"\s]+)/si",$objs[0][$n],$u)) { $u=preg_replace("/\?rel=\d/si",'',$u[1]);
	    if(strstr($u,'youtube.com/')||strstr($u,'youtu.be/')) $s=str_replace($l,"\n\n[ Смотреть ролик на YOUTUBE: ".$u." ]\n\n",$s);
	    else $s=str_replace($l,"\n\n[ Ролик: ".$u." ]\n\n",$s);
	} else $s=str_replace($l,"\n\n[ объект вырезал фейсбук: ".$objs[1][$n]." ]\n\n",$s);
    }
    $s=preg_replace("/<input[^>]*type=[\'\"]*radio[^>]*>/si","\n\n--- ",$s); // оформление тестов
    $s=strip_tags($s);
    $s=str_ireplace(array('©',' '),array('(c)',' '),$s);
*/
//     $s=$head."\n\n".c0($s);

    $s=preg_replace("/\/pre(\/[^\/\n]+\.(jpg|png|gif))/si","$1",$s);

    $s=str_ireplace(" ",chr(160),$s);

    $s=mpers(rconf('body'),array(
	    'Header'=>$head,
	    'text'=>$s,
	    'url'=>$ulink,
	    'flink' => // "link:$link ulink:$ulink ".
		    ( sizeof($imgs[0])? $link :  get_link_($r['Date']) )
		    // preg_replace("/^(http\:\/\/|http\:\/\/|http\:\/\/www\.|https\:\/\/www\.)/si",'',$ulink)
    ));
    $s=c0($s);
    $s=str_replace(" \n","\n",$s);
    $s=preg_replace("/\n[".chr(160)." \t\r]+\n/s","\n",$s);
    $s=preg_replace("/\n{3,}/s","\n\n",$s);
    $s=str_replace("\n\n","\n".chr(160)."\n".chr(160)."\n",$s);

    $flfile=$GLOBALS['include_sys']."protocol/filter_fb.txt";
    if(is_file($flfile)) {
	$fl=file($flfile);
	foreach($fl as $l) { $l=trim($l,"\n\r\t"); if(empty($l) || !strstr($l,'|')) continue;
	    list($ffrom,$fto)=explode('|',$l,2);
	    $s=str_replace(	$ffrom	,	$fto	,$s);
	    $s=str_replace(	mb_convert_case($ffrom,MB_CASE_UPPER,'cp1251')	,	mb_convert_case($fto,MB_CASE_UPPER,'cp1251')	,$s);
	    $s=str_replace(	mb_convert_case($ffrom,MB_CASE_TITLE,'cp1251')	,	mb_convert_case($fto,MB_CASE_TITLE,'cp1251')	,$s);
	}
    }

    // $s=htmlspecialchars_decode($s,ENT_QUOTES);
    // dier($fotos);

//    $s=str_replace("\n","[ENTER]",$s);  $s=str_replace(" ","[SPACE]",$s);
// idie(nl2br(h($s)));

    $cap_sha1=sha1($s);
    if(($i=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_1",0))!==false) { $id=$i['id']; $url=fb_url($id,$user);
	if($cap_sha1!=$i['cap_sha1']) { // обновить
		$x=fb_go("EDIT",$id,$s,$fotos,$r[4]);
		if(!preg_match("/ id=(\d+)$/s",$x,$m) && !preg_match("/[^\s]+\/posts\/(\d+)$/s",$x,$m)) return "$nett: ".$x; if($m[1]!=$id) idie("id error: `".h($id)."` != `".h($m[1])."`");
		msq_update('socialmedias',arae(array('cap_sha1'=>$cap_sha1)),"WHERE `i`='".$i['i']."'");
	    	$x=" - <font color=green>changed</font>";
	} else { $x=" - <font color=green>ok</font>"; }
    } else {
	$x=fb_go("POST",0,$s,$fotos,$r[4]); if(!preg_match("/[^\s]+\/posts\/\d+$/s",$x,$m)) return "$nett: ".$x; $url=$m[0]; $id=basename($url);
	msq_add('socialmedias',arae(array('cap_sha1'=>$cap_sha1,'acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'net'=>$nett,'id'=>$id)));
	$x="";
    }
    return "<a href='".$url."'>".$url."</a>".$x;
}






//===================================================================== TELEGRAM ================================================
//    include_once $GLOBALS['include_sys']."protocol/telegram/api_telegram.php";
/*
function telegram_url($id,$user) { return "https://t.me/".$user."/".$id; }
function telegram_delete($id,$channel) { $e=apiRequest("deleteMessage", array( 'chat_id' => "@".$channel, 'message_id' => $id)); return 1; }
function telegram_post($channel,$s,$fotos='') { /*$fotos='';
function telegram_edit($id,$channel,$s,$fotos='') {
*/

function telegram_url($id,$user) { include_once $GLOBALS['include_sys']."protocol/telegram/api_telegram.php"; return telegram_geturl($id,$user); }

function autopost_telegram($r){ global $num,$include_sys,$net,$user,$nscr,$nett; $user=$r['user']; $nscr=$r['n'];
    $GLOBALS['telegram_API_key']=$r['password'];
    include_once $GLOBALS['include_sys']."protocol/telegram/api_telegram.php";

    $head=$r['Date']." ".(empty($r['Header'])?"":$r['Header']);
    $link=getlink($r['Date']);
    $s=plain_Body($r['p'],"<i><b><strong><em><code><pre>");

    $ulink=$GLOBALS['r']['url'];

    $fotos=array(); $imgs=search_fotos($s);

    $s=preg_replace("/\/pre(\/[^\/\n]+\.(jpg|png|gif))/si","$1",$s);
    $s=str_ireplace(" ",chr(160),$s);

    $s=mpers(rconf('body'),array('Header'=>$head,'text'=>$s,'url'=>$ulink));
    $s=c0($s);
    $s=str_replace(" \n","\n",$s);
    $s=preg_replace("/\n[".chr(160)." \t\r]+\n/s","\n",$s);
    $s=preg_replace("/\n{3,}/s","\n\n",$s);

    if(strlen($s)>4000) { $s=substr($s,0,4000)."... <a href=\"".$ulink."\">[не уместилось, окончание на сайте]</a>"; } // йобаное ограничение!

    $cap_sha1=sha1($s);

    if(($i=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_1",0))!==false) { $id=$i['id']; $url=telegram_url($id,$user);
	if($cap_sha1!=$i['cap_sha1']) { // обновить
		$x=telegram_edit($id,$user,$s,$fotos); if($x!==$id) idie("error: ".nl2br(h($x)));
		msq_update('socialmedias',arae(array('cap_sha1'=>$cap_sha1)),"WHERE `i`='".$i['i']."'");
	    	$x=" - <font color=green>changed</font>";
	} else { $x=" - <font color=green>ok</font>"; }
    } else {
	$id=telegram_post($user,$s,$fotos);
	if(!intval($id)) return "$nett: error: ".$id;
	$url=telegram_url($id,$user);
	msq_add('socialmedias',arae(array('cap_sha1'=>$cap_sha1,'acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'net'=>$nett,'id'=>$id)));
	$x="";
    }
    return "<a href='".$url."'>".$url."</a>".$x;
}


function del_telegram($i) {
    if(false===($id=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_l",0))) return 'Deleted already';
    if(($r=get_autopost($GLOBALS['nett']))===false) idie('Error telegram nett');
    $GLOBALS['telegram_API_key']=$r[3];
    include_once $GLOBALS['include_sys']."protocol/telegram/api_telegram.php";
    $e=telegram_delete($id,$r[2]);
    if(!$e) dier(h($s),"telegram DELETE error");
    // а если все хорошо
    msq("DELETE FROM `socialmedias` WHERE `num`='".e($GLOBALS['num'])."' AND `net`='".e($GLOBALS['nett'])."'".ANDC());
    return 'OK';
}

//===================================================================== FIDO ================================================

function fido_url($id,$fido_url) { return dirname($fido_url)."/".$id; } // http://lleo.me/fido/#area:PVT.LLEO|id:956553

function autopost_fido($r){ global $num,$include_sys,$net,$user,$nscr,$nett; $nscr=$r['n'];

    list($fido_user,$fido_area)=explode('@',$r['user'],2); $fido_pass=$r[3]; $fido_url=$r[4];

    $head=$r['Date']." ".(empty($r['Header'])?"":$r['Header']);
    $link=getlink($r['Date']);
    $s=plain_Body($r['p'],"<i><b><strong><em><code><pre>");
    $ulink=$GLOBALS['r']['url'];

    // $fotos=array(); $imgs=search_fotos($s);
    // $s=preg_replace("/\/pre(\/[^\/\n]+\.(jpg|png|gif))/si","$1",$s);

    $s=mpers(rconf('body'),array('Header'=>$head,'text'=>$s,'url'=>$ulink));
    $s=c0($s);
    $s=str_replace(" \n","\n",$s);
    $s=preg_replace("/\n[".chr(160)." \t\r]+\n/s","\n",$s);
    $s=preg_replace("/\n{3,}/s","\n\n",$s);

    $cap_sha1=sha1($s);

    if(($i=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_1",0))!==false) { $id=$i['id']; $url=fido_url($id,$fido_url);
	if($cap_sha1!=$i['cap_sha1']) { // обновить
	    $x=" - <font color=green>impossible</font>";
	} else { $x=" - <font color=green>ok</font>"; }
    } else {

	// POST
	$id=fido_post($s,$head,$fido_url,$fido_area,$fido_user,$fido_pass);
	if('ERROR'==substr($id,0,5)) return "$nett: ".$id;
	$url=fido_url($id,$fido_url);
	return "<a href='".$url."'>".$url."</a>".$x;

	// msq_add('socialmedias',arae(array('cap_sha1'=>$cap_sha1,'acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'net'=>$nett,'id'=>$id)));
	$x="";
    }
    return "<a href='".$url."'>".$url."</a>".$x;
}

// function del_fido($i) { return 'impossible'; }
// function edit_fido($i) { return 'impossible'; }
function fido_post($text,$subj,$fido_url,$fido_area,$fido_user,$fido_pass) {
    $s=json_encode(array(
	'user'=>$fido_user,
	'pass'=>$fido_pass,
	'action'=>'post',
	'area'=>$fido_area,
	'subj'=>'subj',
	'text'=>'text'
    ));

    $ch=curl_init(); curl_setopt_array($ch,array(
        CURLOPT_URL => $fido_url,
        CURLOPT_POST => 1, CURLOPT_POSTFIELDS => $s,
        CURLOPT_HEADER => 0,
        CURLOPT_FRESH_CONNECT => 1, CURLOPT_RETURNTRANSFER => 1, CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 10
    ));
    if(!$o=curl_exec($ch)) return "ERROR CURL: ".curl_error($ch);
    curl_close($ch);
    if(empty($a=(array)json_decode($o))) return "ERROR JSON: ".$o;
    if(isset($a['success'])) { if(empty($a['answer'])) return "ERROR empty id"; 

dier($a);

return $a['answer']; }
    if(isset($a['error'])) return "ERROR ANSWER: ".$a['error'];
    return "ERROR UNKNOWN: ".print_r($a,1);

    // idie("url: `".h($fido_url)."`<br>json:".h($s)."<br>output=`".nl2br(h($o))."`");
//    return "ERROR ".print_r($a,1);
//    return "#area:".$fido_area."|id:".rand(0,999999); //$id;
}



// ==============================================================================================================================






// старый фейсбук

function del_facebook($i) { // global $num,$net,$user;
    include_once $GLOBALS['include_sys'].'protocol/_protocol_patchs.php';
/*
    .. блядский фейсбук не удаляет фотки ..
    $r=ms("SELECT `id` FROM `socialmedia_fotos` WHERE `type`='fb_foto' AND `num`=".$GLOBALS['num']."".ANDC(),"_a",0);
    foreach($r as $l) { list($id,)=explode('#',$l['id'],2);
        if('true'!=($i=file_get_contents_https("https://graph.facebook.com/".$id."?access_token=".$GLOBALS['fb_aukey'],'DELETE'))) dier($i,"facebook DELETE error");
//	msq("DELETE FROM `socialmedia_fotos` WHERE `id`='".e($l['id'])."' AND `type`='fb_foto' AND `num`=".$GLOBALS['num']."".ANDC()); // удалить фотку
	idie($id);
    }
*/

    if(false===($id=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_l",0))) return 'Deleted already';
    facebook_key(); // разобраться с ключом

    $e=file_get_contents_https("https://graph.facebook.com/".$id."?access_token=".$GLOBALS['fb_aukey'],'DELETE');
    if($e=='true') $e='{"success":true}'; $e=(array)json_decode1($e);
    if(!$e['success']) dier(h($s),"facebook DELETE error");
    // а если все хорошо
//    msq("DELETE FROM `socialmedias` WHERE `type`='post'".ANDP()); // удалить запись
    msq("DELETE FROM `socialmedias` WHERE `num`='".e($GLOBALS['num'])."' AND `net`='".e($GLOBALS['nett'])."'".ANDC());
    return 'OK';
}

function facebook_savefoto($imgs,$a,$t) { global $facebook_album,$facebook_albumtext; $facebook_album=$a; $facebook_albumtext=$t;
    $att=array(); foreach($imgs[0] as $n=>$l) $att[]=facebook_savefoto_do($l,$imgs['txt'][$n]);
    return $att;
}

function facebook_savefoto_do($l,$txt) { global $nett,$facebook_alb; // upload 1 photo

    // $cap_sha1=sha1($txt);
    $l2=savefoto_cutname($l); // обрезать до 128 символов, а то пипец базе
    if(false!==($p=ms("SELECT `num`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='fb_foto' AND `url`='".e($l2)."'".ANDP0(),"_1",0)))
    {  return $p['id'];
    /*  if($cap_sha1==$p['cap_sha1']) return $p['id']; // фотка уже загружена и текст не изменился
	// заменить описание фотки пока не знаю как
	list($id0,)=explode('#',$p['id'],2);
	$e=facebook_graph($id0,array('message' => wu($txt)));
	dier($e);
	    if(!isset($e['id'])) dier($e,'facebook: foto update error');
	msq_update("socialmedia_fotos",arae(array('cap_sha1'=>$cap_sha1)),"WHERE `id`='".$p['id']."' AND `type`='fb_foto'".ANDC());
	return $id; */
     }

    facebook_mkalbum(); // сделать альбом если не было

    // ну а если фотки и не было, то загрузить ее
	$e=facebook_graph($facebook_alb."/photos",array('no_story'=>1, // не срать постом в ленту
	    'message'=>wu($txt),'url' => $l));
        if(!isset($e['id'])) dier($e,'facebook: fotoupload error');
	$id=$e['id'];

    // теперь получить url
    $e=(array)json_decode1(file_get_contents_https("https://graph.facebook.com/".$id."?access_token=".$GLOBALS['fb_aukey']."&fields=source"));
    if(!isset($e['source'])) dier($e,'facebook: fotourl error');
    $id=$id."#".$e['source'];

    msq_add("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'type'=>'fb_foto','num'=>$GLOBALS['num'],'url'=>$l2,'cap_sha1'=>$cap_sha1,'id'=>$id)));
    microimg_send($l);
}

function microimg_send($l) { $bn=basename($l); $bd=dirname($l).'/'; $ll=$bd."pre/".$bn;
    otprav("var r='autopostr_".$GLOBALS['nscr']."'; zabil(r,vzyal(r)+' <img src=".$ll.">'); majax('protocol.php',{a:'post',num:'".$GLOBALS['num']."',n:'".$GLOBALS['nscr']."'});");
    // return $id;
}

function facebook_mkalbum() { global $num,$nett,$facebook_album,$facebook_albumtext,$facebook_alb,$user;
	if(isset($facebook_alb)) return $facebook_alb;
        if(false!==($facebook_alb=ms("SELECT `id` FROM `socialmedias` WHERE `type`='fb_album' AND `url`='".e($facebook_album)."'".ANDP(),"_l",0))) return $facebook_alb;
	// создать новый альбом create albums
	$e=facebook_graph($user."/albums",array('name' => wu($facebook_album),'description' => wu($facebook_albumtext)));
	if(!isset($e['id'])) dier($e,'facebook');
	$facebook_alb=$e['id'];
	msq_add("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'num'=>$num,'type'=>'fb_album','url'=>$facebook_album,'cap_sha1'=>'','id'=>$facebook_alb)));
	if($facebook_alb===false) idie("Facebook error mk_album: problem with photoalbum `".h($facebook_album)."`");
	return $facebook_alb;
}

//===============================
function facebook_key() { // если нет ключа - залогиниться
    if(($GLOBALS['fb_aukey']=loadsite($GLOBALS['fbkey_name'].$GLOBALS['user']))===false) gokey_facebook(); // если нет ключа - залогиниться
    $GLOBALS['fb_version']=false===loadsite($GLOBALS['fbkey_name'].$GLOBALS['user'].'.ver')?1:2; // версия блядского FB
}

function search_fotos($text,$vnutr=1) { // ищем фотки

if($vnutr) preg_match_all("/".preg_quote($GLOBALS['httphost'],'/')."[^<>\s]+\.(jpg|png|jpeg|gif)/si",$text,$imgs,PREG_PATTERN_ORDER);
else preg_match_all("/https?\:\/\/[^<>\s]+\.(jpg|png|jpeg|gif)/si",$text,$imgs,PREG_PATTERN_ORDER);
unset($imgs[1]);


$fo=array();
    $t=$text; $imgs['txt']=array(); foreach($imgs[0] as $n=>$l) {
//if(!strstr($t,$l)) idie('err1');
    list($a,$b)=explode($l,$t,2); if($n) $imgs['txt'][$n-1]=c0(strip_tags(// '<'.
trim($a))); else $text=c0($a);
    $t=$b;
    if(in_array($l,$fo)) unset($imgs[0][$n]); else $fo[]=$l;
} $imgs['txt'][$n]=c0(strip_tags(// '<'.
trim($t)));

// и удалить все маленькие фотки-дубли с /pre/
foreach($imgs[0] as $n=>$l) { if(!strstr($l,'/pre/')) continue;
    $l=str_replace('/pre/','/',$l);
    if(false===($k=array_search($l,$imgs[0]))) continue;
    $imgs['txt'][$k]=trim($imgs['txt'][$k].' '.$imgs['txt'][$n]);
    unset($imgs['txt'][$n]);
    unset($imgs[0][$n]);
}

    return $imgs;
}

function autopost_facebook($r){ global $num,$include_sys,$net,$user,$nscr,$nett; $user=$r['user']; $nscr=$r['n'];
//    include_once $include_sys.'protocol/_recbasa.php'; recbasa();
    facebook_key();

    $head=$r['Date']." ".(empty($r['Header'])?"":$r['Header']);
    $link=getlink($r['Date']);
    include_once $include_sys.'protocol/_protocol_patchs.php';
    $text=plain_Body($r['p']);

$imgs=search_fotos($text);

// $GLOBALS['fb_version']=2;

if($GLOBALS['fb_version']==2) {
//======================================================================================================


    preg_match_all("/<(img)[^>]+src=[\'\"]?([^>\s\'\"]+)[\'\"]?[^>]*?>/si",$r['text'],$imgs, PREG_PATTERN_ORDER);
    preg_match_all("/<(object|iframe)[^>]*?>/si",$r['text'],$objs, PREG_PATTERN_ORDER);

    // preg_match_all("/<script[> ]/si",$r['text'],$scripts, PREG_PATTERN_ORDER);
    $n_img=sizeof($imgs[1]); $n_obj=sizeof($objs[1]);
    if($n_img) {
	$img=$imgs[2][0]; // взяли первое
	foreach($imgs[1] as $n=>$l) if($l=='IMG') { $img=$imgs[2][$n]; break; } // если тэг заглавным - то его
    } else $img='';
    // $text=$head."\n".plain_Body($r['p']);

$s=$r['text'];
$s=str_ireplace(array('<br>','<p>'),array("\n","\n\n"),$s);
$s=preg_replace("/<p[^>]*margin\-top\:2\%\;[^>]*>/si","\n\n",$s);
$s=preg_replace("/<p[^>]*margin\-top\:0pt\;[^>]*>/si","\n",$s);

// "\n[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[[|||||||||||||||||||||]]]]]]]]]]]]]]]]]]]]]]]]]]]\n";
// $k="\n[[[[[[|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||\n";
// $k="\n[ ";

foreach($imgs[0] as $n=>$l) $s=str_replace($l,(basename($imgs[2][$n])=='play_youtube.png'
?"\n\n[ тут в оригинальном посте открывается видеоролик Ютуба" // .print_r($imgs[3],1) // [2][$n]
:"\n\n[ картинку вырезал фейсбук: ".str_replace('/pre/','/',$imgs[2][$n])
)." ]\n\n",$s); // ."\n[[ оригинал заметки: ".$link

foreach($objs[0] as $n=>$l) {

    if(strtoupper($objs[1][$n])=='IFRAME' && preg_match("/src=[\'\"]*([^>\'\"\s]+)/si",$objs[0][$n],$u)) { $u=preg_replace("/\?rel=\d/si",'',$u[1]);
	if(strstr($u,'youtube.com/')||strstr($u,'youtu.be/')) $s=str_replace($l,"\n\n[ Смотреть ролик на YOUTUBE: ".$u." ]\n\n",$s);
	else $s=str_replace($l,"\n\n[ Ролик: ".$u." ]\n\n",$s);
    } else $s=str_replace($l,"\n\n[ объект вырезал фейсбук: ".$objs[1][$n]." ]\n\n",$s); }

$s=preg_replace("/<input[^>]*type=[\'\"]*radio[^>]*>/si","\n\n--- ",$s); // оформление тестов

$s=strip_tags($s);
$s=str_ireplace(array('©',' '),array('(c)',' '),$s);

$text=preg_replace("/\n{3,}/s","\n\n",$s);

$text=htmlspecialchars_decode($text,ENT_QUOTES);

// idie(nl2br($text));

    $a=array('message' => wu($text),
        'link' => $link,
        'description' => $link, // wu($desc),
//	'actions' => json_encode(array('name' => $action_name,'link' => $action_link)),
// 'privacy[value]'=>"SELF",
        'name' => wu($head)
    ); if($img!='') $a['picture']=$img;

    // залить все фотки - ахуя?
    // $imgs=search_fotos($text); if(sizeof($imgs)) { $att=facebook_savefoto($imgs,$r['Date'],(empty($r['Header'])?$r['Date']:$r['Header'])); }

    $cap_sha1=sha1($text);
    if(($i=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_1",0))!==false) { $u=$i['id'];
	if($cap_sha1!=$i['cap_sha1']) { // обновить
		$e=facebook_graph($u,$a);
		msq_update('socialmedias',arae(array('cap_sha1'=>$cap_sha1)),"WHERE `i`='".$i['i']."'");
	    	$x=" - <font color=green>changed</font>";
	} else { $x=" - <font color=green>ok</font>"; }
    $u=facebook_mkurl($u,$r['user']);
    } else {
	$e=facebook_graph("me/feed",$a); $id=$e['id'];
	$u=facebook_mkurl($id,$r['user']);
	msq_add('socialmedias',arae(array('acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'net'=>$nett,'id'=>$id)));
	$x="";
    }
// $e=facebook_graph("me/feed",$a); $id=$e['id'];
// $u=facebook_mkurl($id,$r['user']);
// idie("<a href='$u'>$u</a>");
// dier($e);
//======================================================================================================
} else {

// увы, этот протокол уже отключен у всех

/*
// -------------------------------------------
    $imgs=search_fotos($text); if(sizeof($imgs)) { // ищем фотки, если надо - заливаем фотки на Фейсбук и меняем адреса фоток
	$att=facebook_savefoto($imgs,$r['Date'],(empty($r['Header'])?$r['Date']:$r['Header']));
	foreach($imgs[0] as $n=>$l) { list(,$img)=explode('#',$att[$n],2); $text=str_replace($l,$img,$text); }
    }

    // реально берем тест
    $text=str_ireplace(array('</form>','</label>','<center>','</center>','<hr>','</table>','</td>','</tr>','</tbody>','<tbody>'),'',$r['text']);
    $text=str_ireplace(array('©'),array('(c)'),$text);
    $text=preg_replace("/<form[^>]*>/si","\n",$text);
    $text=preg_replace("/<input[^>]*>/si","\n",$text);
    $text=preg_replace("/<label[^>]*>/si","\n",$text);
    $text=preg_replace("/<table[^>]*>/si",'',$text);
    $text=preg_replace("/<td[^>]*>/si",'',$text);
    $text=preg_replace("/<tr[^>]*>/si","\n",$text);
    $text=preg_replace("/<ins[^>]*>/si","",$text);
    $text=preg_replace("/<\/ins>/si","",$text);
    $text=preg_replace("/<iframe[^>]+src\=[\'\"]*([^\'\">]+).*?<\/iframe>/si","\n\n<b><big>VIDEO: $1</b></big>\n\n",$text);
    $text=preg_replace("/<script[^>]*>.*?<\/script>/si","",$text);
    $text=preg_replace("/<style[^>]*>.*?<\/style>/si","",$text);
    $text=str_ireplace("<p>","<br><br>",$text); // эта блядва ещё и тэг <p> не понимает как надо, видимо закрывающего ждет, сучка
    // запостить заметку
    $a=array('message'=>wu($text),'subject'=>wu($r['Header']));

    $cap_sha1=sha1($text);

    if(($i=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_1",0))!==false) { $u=$i['id'];
	if($cap_sha1!=$i['cap_sha1']) { // обновить
		$e=facebook_graph($u,$a);
		msq_update('socialmedias',arae(array('cap_sha1'=>$cap_sha1)),"WHERE `i`='".$i['i']."'");
	    	$x=" - <font color=green>changed</font>";
	} else { $x=" - <font color=green>ok</font>"; }
    $u=facebook_mkurl($u,$r['user']);
    } else {
	$e=facebook_graph($r[2]."/notes",$a); $id=$e['id'];
	$u=facebook_mkurl($id,$r['user']);
	msq_add('socialmedias',arae(array('acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'net'=>$nett,'id'=>$id)));
	$x="";
    }

//---------------------------------------------
*/
}

    return "<a href='".$u."'>".$u."</a>".$x;
}

function facebook_graph($u,$a) { $a['access_token']=$GLOBALS['fb_aukey'];

if($GLOBALS['fb_version']==2) {
    if(strstr($u,'/')) { list(,$u)=explode('/',$u,2); $u="https://graph.facebook.com/v2.3/me/".$u; }
    else $u="https://graph.facebook.com/v2.3/".$u;
} else {
    $u="https://graph.facebook.com/".$u;
}

    $e=(array)json_decode1(curlpost($u,$a));
    if(!isset($e['error'])) return $e;
    $er=(array)$e['error']; if(isset($er['message']) && strstr($er['message'],'token')) gokey_facebook(); // Token-problem - relogin

    if(strstr($er['message'],'(#803) Cannot query users by their username')
|| strstr($er['message'],'deprecated for versions v2.0 and higher')
) { // обработка протокола
	$fbl=$GLOBALS['fbkey_name'].$GLOBALS['user'].'.ver';
	savesite($fbl,'v2.3');
	idie("Блядский Фейсбук для приложений, созданных после мая 2014, запрещает постинг заметок формата notes.
Вам придется отныне пользоваться только протоколом новой версии 2.0 В этом режиме можно публиковать заметку с ссылками,
но без множественных фоток: фотка будет только одна, внизу заметки.

<p>Мы пометили, что протокол 2, создав переменную <b>".h($fbl)."</b> в редакторе переменных.

<p>Теперь просто запустите постинг еще раз, больше это сообщение не повторится.");
    }
    idie("<font color=red>".$er['message']."</font>",'facebook error:');
}

/* *******************************************
Вот окно теста: https://developers.facebook.com/tools/explorer
https://developers.facebook.com/docs/graph-api/reference/v2.2
https://developers.facebook.com/docs/graph-api/reference/v2.2/note
/{user-id}/notes
message The content of the note.
subject The title of the note.
Deleting: DELETE graph.facebook.com/{note-id}
******************************************* */
// =============================================================================
function rconf($l) { if(!isset($GLOBALS['r']['conf'][$l])) return false; return $GLOBALS['r']['conf'][$l]; }





function autopost_lj($r){ global $num,$include_sys,$nett;

    include_once $include_sys."protocol/lj/lj.php"; // ето моя библиотечка ljpost
    $opt=array();
    $opt['prop_opt_noemail']=rconf('noemail')?rconf('noemail'):1;

    // доступ
//    if($r['p']['Access']=='admin') return "lj <b>".$r['user']."</b> - <font color=red>admin only</font>";
    if($r['p']['Access']=='all') { $opt['security']='public'; $screen=0; } else { $opt['security']='usemask'; $opt['allowmask']=1; $screen=1; }
    // тэги
    $tags=implode(',',gettags($GLOBALS['num'])); $opt['prop_taglist']=wu($tags);
    //вообще документация: http://www.livejournal.com/doc/server/ljp.csp.proplist.html
    $flat=(isset($r[4])?$r[4]:"http://www.livejournal.com/interface/flat"); // http://lj.rossia.org/interface/flat
    list($log,$community)=explode(':',$r['user'],2);


    if($community) $opt['usejournal']=$community; // автопостинг в коммюнити

    $text=$r['text'];

    $text=preg_replace("/<script[^>]*>.*?<\/script>/si","",$text);
    $text=preg_replace("/<style[^>]*>.*?<\/style>/si","",$text);

//----------------------
if(preg_match("/\{foto_source\:([^\}\{]+)\}/si",$text,$m)) { $fnett=c($m[1]); list($fnet,$fuser)=explode(':',$fnett);
$text=str_replace($m[0],'',$text);
if(false!==($imgs=ms("SELECT `id`,`url`,`type` FROM `socialmedias` WHERE `net`='".e($fnett)."' AND `num`='".$num."'".ANDC(),"_a",0))) {

// dier($imgs);

foreach($imgs as $p) { // $u=
	if($p['type']=='ya_foto') list(,$ol)=explode('|',$p['id'],2);
	elseif($p['type']=='fb_foto') list(,$ol)=explode('#',$p['id'],2);
	elseif($p['type']=='vk_foto') list(,,,$ol)=explode('|',$p['id'],4);
	else continue;
    $text=str_replace($p['url'],$ol,$text);
}}
}


// dier("|".$text);
//    $text=trim($s);


//============================== но если это пост в блог...
if(rconf('mode')=='lleoblog') {
    $p=ms("SELECT * FROM `dnevnik_zapisi` WHERE `num`='".e($num)."'",'_1',0);
    $opt['link']=getlink($p['Date']);

    foreach(cleanopt(unser($p['opt'])) as $i=>$l) $opt['lleoopt_'.$i]=$l;

    // добавить тэги из опций темплейта, например:
    // opt_tags=hultura.ru, стихи
    // opt_design=hultura
    $x='opt_'; foreach($GLOBALS['r']['conf'] as $n=>$l) if(substr($n,0,strlen($x))==$x) $opt['lleoopt_'.substr($n,strlen($x))]=$l;
    foreach(array('Date','Access','visible') as $i) $opt['lleo_'.$i]=$p[$i];
    $r['Header']=$p['Header'];
    $text=mpers(rconf('body'),array('text'=>$p['Body'],'url'=>$opt['link']));

    $text=prepare_link($text,$p); // поправить относительные ссылки и картинки
//    idie($text);

    $flat.='?'.time().microtime();
    $d=explode(' ',"view_counter num DateDatetime DateDate DateUpdate acn opt Header Body Date Access visible"); foreach($p as $i=>$l) if(in_array($i,$d)) unset($p[$i]);
}
//==============================

$cap_sha1=sha1($text.$r['Header'].implode('|',$opt)
// .rand(0,1000000)
);
    if(($ll=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_1",0))!==false) {
	if($cap_sha1!=$ll['cap_sha1']) { // были изменения
	    list($item,)=explode('|',$ll['id'],2);
	    $lj=LJ_edit($log,$r[3],$item,wu($r['Header']),wu($text),$opt,$flat); if($lj['success']!='OK') return "<b>".$nett."</b> - <font color=red>Error: ".uw(h($lj['errmsg']))."</font>";
	    msq_update('socialmedias',arae(array('cap_sha1'=>$cap_sha1)),"WHERE `i`='".$ll['i']."'");
	    $x=" - <font color=green>updated</font>"; $u=$lj['url'];
	} else { $x=" - <font color=green>ok</font>"; list(,$u,)=explode('|',$ll['id']); }
    } else {
	$lj=LJ_post($log,$r[3],wu($r['Header']),wu($text),$opt,$flat); if($lj['success']!='OK') return "<b>".$nett."</b> - <font color=red>Error: ".uw(h($lj['errmsg']))."</font>";
	msq_add('socialmedias',arae(array('type'=>'post','cap_sha1'=>$cap_sha1,'acn'=>$GLOBALS['acn'],'num'=>$num,'net'=>$nett,'id'=>$lj['itemid'].'|'.$lj['url'].'|'.$screen)));
	$x=""; $u=$lj['url'];
    }

    return "<a href='".h($u)."'>".h($u)."</a>".$x;
}
// =============================================================================
function del_lj($i) { global $include_sys,$nett;
    include_once $include_sys."protocol/lj/lj.php"; // ето моя библиотечка ljpost
	if(($r=get_autopost($nett))===false) idie('Error flat');
	        $flat=(isset($r[4])?$r[4]:"http://www.livejournal.com/interface/flat");
	        list($log,)=explode(':',$r[2],2);
		$pas=$r[3];

    if(false===($url=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_l",0))) return 'Deleted already';
    list($item,)=explode('|',$url,2);
    $lj=LJ_edit($log,$pas,$item,'','',array(),$flat); // удаляем
    if($lj['success']!='OK') return "<b>".$nett."</b> - <font color=red>Error: ".h($lj['errmsg'])."</font>";
    // а если всё в порядке
    msq("DELETE FROM `socialmedias` WHERE `num`='".e($GLOBALS['num'])."' AND `net`='".e($GLOBALS['nett'])."'".ANDC());
//    msq("DELETE FROM `socialmedias` WHERE `type`='post'".ANDP()); // удалить запись
    return "OK";
}
// =============================================================================

function get_autopost($nett) {
    foreach(explode("\n",loadsite('autopost')) as $a=>$l) { if(($l=c0($l))=='') continue;
	$r=explode(' ',$l); if($r[0].':'.$r[2]==$nett) return $r;
}}

function twitter_url($l,$type){ return $l; }
function twitter_screen($l){ return 0; }

function lj_url($l){ list(,$l,)=explode('|',$l); return $l; }
function lj_screen($l){ list(,,$l)=explode('|',$l); return $l; }

function facebook_url($l,$u,$type){
    if($type=='post') { return facebook_mkurl($l,$u); }
    if($type=='fb_note') { return $l.' '.$u; }
    if($type=='fb_foto') { list(,$i)=explode('#',$l,2); return $i; } //797847320261056#https://fbcdn-sphotos-b-a.akamaihd.net/hphotos-ak-xap1/v/t1.0-9/s720x720/10516775_797847320261056_8915313668576531900_n.jpg?oh=d54f505499af4ec4f32fc3fe8de939db&oe=54E86F50&__gda__=1423580686_0bcc6a2a1fb01b79ca2d02af7ae48f18
    if($type=='fb_album') { return "https://www.facebook.com/".$u."/media_set?set=a.797847316927723".$l; } // 797847316927723
    return "Unknown type:`".h($type)."`";
}
function facebook_mkurl($i,$user) { if(strstr($i,'_')) list($ii,$i)=explode('_',$i);
// if($GLOBALS['fb_version']==2) return "https://www.facebook.com/permalink.php?story_fbid=".$ii."&id=".$i;
return "https://www.facebook.com/".$user."/posts/".$i;
}

function facebook_screen($l){ return 0; }

function yandex_url($l,$user,$type){
    if($type=='post') return yandex_mkurl($l,$user);
    if($type=='ya_album' || $type=='post') yandex_mkurl($l,$user); // http://fotki.yandex.ru/users/lleokaganov/album/463358/
    if($type=='ya_foto') { list(,$u)=explode('|',$l); return $u; } // yandex_mkurl($l,$user);
    return "unknown type `".h($type)."` - $l";
}
function yandex_mkurl($i,$user) { return "http://fotki.yandex.ru/users/".$user."/album/".$i."/"; }

function vk_getfullsize($l) {
    $f="https://vk.com/photo".h($GLOBALS['vk_user_id'])."_".h($l);
    $s=fileget($f);
    if(preg_match("/<a href=\"([^\"\s]+)\"[^>]+>Download full size/si",$s,$m)) return $m[1];
    return '';
}

function vk_url($l,$u=0,$type='post') {
    if($type=='post') { list($i,$u)=explode('|',$l); return vk_mkurl($i,$u); }
    if($type=='vk_note') { vk_token(); return "https://vk.com/note".h($GLOBALS['vk_user_id'])."_".h($l); } //[net] => vk:4350243            [id] => 12009290
    if($type=='vk_foto') { if(strstr($l,'|')) list($l,)=explode('|',$l);

vk_token();

/*
idie($l);

$f="https://vk.com/photo".h($GLOBALS['vk_user_id'])."_".h($l);
$s=file_get_contents($f);
if(preg_match("/<a href=\"([^\"\s]+)\"[^>]+>Download full size/si",$s,$m)) dier($m);
else idie(nl2br(h($s)));
// "x_src":"https:\/\/pp.vk.me\/c622530\/v622530314\/9416\/BsIiGcGQxbw.jpg","x_"
*/

return "https://vk.com/photo".h($GLOBALS['vk_user_id'])."_".h($l); } // photo83220314_344061201
    if($type=='vk_album') { vk_token(); return "https://vk.com/album".h($GLOBALS['vk_user_id'])."_".h($l); } // [id] => 205815846 // https://vk.com/album83220314_205815846
    return "Unknown type:`".h($type)."`";
}
function vk_mkurl($i,$user_id){ return "https://vk.com/wall".h($user_id)."_".h($i); }



function yandex_screen($l){ return 0; }

function autopost_twitter($r){ global $num,$include_sys,$nett;
    include_once $include_sys.'protocol/twitter/twitter.class.php';
    include_once $include_sys.'protocol/_protocol_patchs.php';

    if($r['p']['Access']!='all') return "twitter <b>".$r['user']."</b> - <font color=red>not open</font>";

    $g=explode(" ",$r['txt']); $g=array_chunk($g,10); $g=$g[0]; $txt=implode(" ",$g);

    $text=(empty($r['Header'])?$txt." [...]":$r['Header']);

    if(($u=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_l",0))!==false)
        return "<a href='".h($u)."'>".h($u)."</a> <font color=green>ok</font>";

    $twitter = new Twitter($r[3],$r[4],$r[5],$r[6]);
    try { $s = $twitter->send(wu($text).' '.$r['url']); // you can add $imagePath as second argument
    } catch (TwitterException $e) { return "Twitter <b>".$r['user']."</b> - <font color=red>Error: ".$e->getMessage()."</font>"; }

    $url='https://twitter.com/'.$r['user'].'/status/'.$s->id_str;
    msq_add('socialmedias',arae(array('acn'=>$GLOBALS['acn'],'num'=>$num,'net'=>$nett,'id'=>$url,'type'=>'post')));
    return "<a href='".h($url)."'>".h($url)."</a>";
}

//=================================== twitter ===============================

function vk_go($url,$ara,$i=0) { global $ALLR_E; $ara["access_token"]=$GLOBALS['vk_access_token'];
    if(isset($GLOBALS['captcha_sid'])) { $ara["captcha_sid"]=$GLOBALS['captcha_sid']; $ara["captcha_key"]=$GLOBALS['captcha_key']; }
    $ara['v']='3.0'; // вечно пидоры что-то меняют и всё отваливается: теперь, видите ли, с 1 марта 2018 надо указывать версию, хуй с вами, вот вам версия

    $ALLR_E=uw(
curlpost("https://api.vk.com/method/".$url,$ara)
// file_get_contents_https("https://api.vk.com/method/".$url."?",implode('&',ssdsdfs ну и еще urlencode$s)
);
    $e=(array)json_decode1($ALLR_E);

    if(isset($e['error'])) { $er=$e['error']->error_msg;
	if(stristr($er,'token')||stristr($er,'authorization')) gokey_vk(); // Token-problem - relogin
	if(stristr($er,'captcha')) { $n=intval($GLOBALS['autopost_n']); otprav("
ohelpc('vklogin_".$n."','vk: captcha needed',\"".njsn("
Блядский VK зачем-то требует ввести капчу:
<p><form onsubmit=\"setTimeout('clean(\\'vklogin_".$n."\\')',100);return send_this_form(this,'protocol.php',{a:'post',captcha_sid:'".$e['error']->captcha_sid."',n:'".$n."',num:".intval($GLOBALS['autopost_num'])."});\">
<center><img src='".h($e['error']->captcha_img)."'><br><input type='text' name='captcha_key' size=6> <input type='submit' value='Go'></center></form>
")."\");"); }
	idie("<font color=red>ERROR: ".h($er)."</font><br>".h($s),'vk_go: error');
    }

    if($i && $e['response']!=1) dier($e,'vk_go error:<br>'.h($s)."<hr>ALLR_E:".h($ALLR_E));

    return $e;
}

function savefoto_cutname($l,$n=128) { return (strlen($l)<$n?$l:substr($l,0,($n-33)).'|'.md5($l)); } // обрезать до 128 символов, а то пипец базе

function vk_getfotourl() { global $vk_fotourl,$vk_alb; if(isset($vk_fotourl)) return;
    vk_mkalbum();
    $vk="photos.getUploadServer"; $e=vk_go($vk,array("album_id"=>$vk_alb)); // адрес сервера для загрузки фотографий
    $vk_fotourl=$e['response']->upload_url;
}

function vk_savefoto_do($l,$cap,$num) { global $num,$nett,$vk_fotourl,$vk_alb;
    $cap_sha1=sha1($cap);

    $l2=savefoto_cutname($l); // обрезать до 128 символов, а то пипец базе
    if(false!==($p=ms("SELECT `num`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='vk_foto' AND `url`='".e($l2)."'".ANDP0(),"_1",0))) {
    if($num!=$p['num'] || $cap_sha1==$p['cap_sha1']) return $p['id']; // фотка уже загружена в прошлую заметку или текст не изменился
    list($id,)=explode('|',$p['id']);
    $vk="photos.edit"; $e=vk_go($vk,array( // изменить текст фото
        "photo_id"=>$id,
        "caption"=>wu($cap)
	),1);
    msq_update("socialmedias",arae(array('cap_sha1'=>$cap_sha1)),"WHERE `id`='".$p['id']."' AND `type`='vk_foto'".ANDP());
    return $p['id'];
    }

//idie($l);

    // ну а если фотки и не было, то загрузить ее
    vk_token();
    vk_getfotourl(); // узнать url

    if(strstr($l,'://')) {
	$file=$GLOBALS['filehost'].'tmp/vkfile.jpg';
	file_put_contents($file,file_get_contents($l));
    } else $file=$GLOBALS['filehost'].substr($l,strlen($GLOBALS['httphost']));
    $mr=getimagesize($file); $imgX=$mr[0]; $imgY=$mr[1]; // узнать габариты картинки
/*
    [0] => 900
    [1] => 598
    [2] => 2
    [3] => width="900" height="598"
    [bits] => 8
    [channels] => 3
    [mime] => image/jpeg
*/
//    dier($r);
//    list($W,$H,$itype)=getimagesize($from);

// dier($mr);

// idie(h(file_get_contents($file)));
// /var/www/dnevnik/2014/05/DSC_8388.JPG

// $file='http://lleo.me/dnevnik/2014/05/DSC_8388.JPG';

// idie($file);

// idie($file);

// $fl=file_get_contents()

    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$vk_fotourl);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,array('file1'=>'@'.$file));
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $res=curl_exec($ch); curl_close($ch);
    $e=(array)json_decode(uw($res)); if(!isset($e['photos_list'])) dier($e,'curl:file`'.h($l).'`'); // какая-то ошибка

// dier($e);
/*
Array
(
    [server] => 624830
    [photos_list] => [{"photo":"0e7bff8e6a:y","sizes":[["s","624830314","9521","fVTRxOwuCIg",75,50],["m","624830314","9522","0dwDHM1puOU",130,86],["x","624830314","9523","ytQVndBkwj0",604,401],["y","624830314","9524","S53UIQgam1o",700,465],["o","624830314","9525","PJwsEol1_2c",130,87],["p","624830314","9526","-ab9M562sEs",200,133],["q","624830314","9527","vOmgTpRAT_M",320,213],["r","624830314","9528","d6ki-dkXYUc",510,340]],"kid":"621bb926a2addf93f2f51b66be251567"}]
    [aid] => 205930731
    [hash] => 647887ccfe695c2770a543d109f2c512
)

    [server] => 624830
    [photos_list] => []
    [aid] => 206025831
    [hash] => 3e4bfceb2d90d3cab8f3f4bbbd3d212f
*/
    $vk="photos.save"; $e=vk_go($vk,array( // сохранить фото
        "album_id"=>$vk_alb,
        "photos_list"=>$e['photos_list'],
        "hash"=>$e['hash'],
        "server"=>$e['server'],
        "caption"=>wu($cap)
    ));
    $id=$e['response'][0]->id; if(!strlen($id)) dier($e,$vk);
    list(,$id)=explode('_',$id);

    $realurl=vk_getfullsize($id);
    $id=$id.'|'.$imgX.'|'.$imgY.'|'.$realurl;

    msq_add("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'type'=>'vk_foto','num'=>$num,'url'=>$l2,'cap_sha1'=>$cap_sha1,'id'=>$id)));
    microimg_send($l);
}

function vk_token() { global $include_sys,$user,$vk_user_id,$vk_access_token;
    if(isset($vk_access_token) && isset($vk_user_id)) return;
    include_once $include_sys.'protocol/_protocol_patchs.php';
    if(($key=loadsite($GLOBALS['vkkey_name'].$user))===false) gokey_vk(); // если нет ключа - залогиниться
    list($vk_user_id,$vk_access_token)=explode('|',$key);
}

function vk_mkalbum() { global $num,$nett,$vk_alb,$vk_album,$vk_albumtext;
	if(isset($vk_alb)) return $vk_alb;
        if(false!==($vk_alb=ms("SELECT `id` FROM `socialmedias` WHERE `type`='vk_album' AND `url`='".e($vk_album)."'".ANDP(),"_l",0))) return $vk_alb;
	// создать новый альбом
	vk_token();
	$vk="photos.createAlbum"; $e=vk_go($vk,array(
	    "title"=>wu($vk_album),
	    "description"=>wu($vk_albumtext))); $e['response']=(array)$e['response'];
	if(isset($e['response']['aid'])) {
		$vk_alb=$e['response']['aid']; if(empty($vk_alb)) idie("ERROR: Problem with photoalbum `".h($vk_album)."`");
		msq_add("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'num'=>$num,'type'=>'vk_album','url'=>$vk_album,'cap_sha1'=>'','id'=>$vk_alb)));
		return $vk_alb;
	}
	dier($e,$vk);
}

function vk_savefoto($imgs,$a,$t) { global $num,$vk_album,$vk_albumtext; $vk_album=$a; $vk_albumtext=$t;
    $att=array(); foreach($imgs[0] as $n=>$l) { $att[]=vk_savefoto_do($l,$imgs['txt'][$n],$num); }
    return $att;
}


function del_vk($i,$one=0) {
    if(false===($type=ms("SELECT `type` FROM `socialmedias` WHERE `i`='".e($i)."'".ANDC(),"_l",0))) return 'Deleted already';
//    list($post_id,$owner_id)=explode('|',$p['id']);
//    include_once $include_sys.'protocol/_protocol_patchs.php';

if($one==1) $r=array($type=>$i);
else {
    if($type=='vk_foto') $r=array('vk_foto'=>$i);
    elseif($type=='vk_album') $r=array('vk_foto'=>0,'vk_album'=>$i);
    elseif($type=='vk_note') $r=array('vk_foto'=>0,'vk_album'=>0,'vk_note'=>$i);
    elseif($type=='post') $r=array('vk_foto'=>0,'vk_album'=>0,'vk_note'=>0,'post'=>$i);
    else return 'error type: '.h($i);
}

$agidel=array();

    foreach($r as $l=>$i) {
	$ee=($i?"`i`='".e($i)."'".ANDC():"`type`='".$l."'".ANDP());
	if(false===($e=ms("SELECT `i`,`id` FROM `socialmedias` WHERE ".$ee,"_a",0))) continue;

	foreach($e as $k) {
	    vk_token();
	    if($l=='vk_foto') {
		if(strstr($k['id'],'_')) list(,$k['id'])=explode('_',$k['id'],2); // удалить потом
		if(strstr($k['id'],'|')) list($k['id'],)=explode('|',$k['id'],2); // удалить потом
		$e=vk_go("photos.delete",array("photo_id"=>$k['id']),1); }
	    elseif($l=='vk_album') { $e=vk_go("photos.deleteAlbum",array("album_id"=>$k['id']),1); }
	    elseif($l=='vk_note') { $e=vk_go("notes.delete",array("note_id"=>$k['id']),1); }
	    elseif($l=='post') { list($k['id'],)=explode('|',$k['id'],2); $e=vk_go("wall.delete",array("post_id"=>$k['id']),1); }
	    else return 'error type: '.h($l);
	    msq("DELETE FROM `socialmedias` WHERE `i`='".e($k['i'])."'");
	    $agidel[]=$k['i'];
	}
    }
    return implode(' ',$agidel);
}


function gokey_vk(){
    $n=$GLOBALS['autopost_n']; $num=$GLOBALS['autopost_num'];
    $url="https://oauth.vk.com/authorize?client_id=".$GLOBALS['vk_api_id']
    ."&scope=notify,friends,photos,audio,video,docs,notes,pages,status,wall,groups,notifications,stats,ads,offline"
// ."&scope=offline,wall"
// notify >Пользователь разрешил отправлять ему уведомления.
// friends <------>Доступ к друзьям.
// photos >Доступ к фотографиям.
// audio <>Доступ к аудиозаписям.
// video <>Доступ к видеозаписям.
// docs <->Доступ к документам.
// notes <>Доступ заметкам пользователя.
// pages <>Доступ к wiki-страницам.
// status >Доступ к статусу пользователя.
// offers >Доступ к предложениям (устаревшие методы).
// questions <---->Доступ к вопросам (устаревшие методы).
// wall <->Доступ к обычным и расширенным методам работы со стеной.
// groups >Доступ к группам пользователя.
// messages <-->(для Standalone-приложений) Доступ к расширенным методам работы с сообщениями.
// notifications <>Доступ к оповещениям об ответах пользователю.
// stats <>Доступ к статистике групп и приложений пользователя, администратором которых он является.
// ads <-->Доступ к расширенным методам работы с рекламным API.
// offline <------>Доступ к API в любое время со стороннего сервера.
// nohttps <------>Возможность осуществлять запросы к API без HTTPS.
//."&redirect_uri=".urlencode($returnurl)
."&display=page" // DISPLAY – внешний вид окна авторизации, поддерживаются: page, popup, touch и wap
."&response_type="."code";

// account hack protect - TUT NE NADO
//    savesite($GLOBALS['****_name'].$GLOBALS['user'],'asc');

otprav("ohelpc('vklogin_".$n."','vk: get the access_code',\"".njsn("
Внимание! Откройте <a href='$url' target=_blank>эту ссылку</a> в новом окне, и когда после перенаправления
появится результат, сделайте именно то, что запрещает предупреждение - скопируйте получившуюся строку адреса
(что-то типа: <i>https://oauth.vk.com/blank.html#code=c0ba6e49679b88a512</i>) и вбейте сюда:
<form onsubmit=\"return send_this_form(this,'protocol.php',{a:'vkcode',n:".$n.",num:".$num."})\">
<input type='text' name='code' size=50> <input type='submit' value='Go'></form>")."\");
");
}


function vk_drawurl($t) {  return "[".strtr($t[1],' ','%20')."|".$t[2]."]"; }


// документация: https://vk.com/dev/methods
function autopost_vk($r){ global $num,$include_sys,$net,$user,$nscr,$nett;

// msq("DELETE FROM `socialmedias` WHERE `url` LIKE '%play%'".ANDC());
// delete
// $r=ms("SELECT `i`,`id`,`type`,`net`,`url`,`num` FROM `socialmedias` WHERE `url` LIKE '%play%' AND `type`='vk_foto' AND `net`='".e($nett)."'","_a",0); dier($r);

    $user=$r['user']; $nscr=$r['n']; $GLOBALS['vk_api_id']=$r[2]; $nett=$net.':'.$user;
    vk_token();
    $head=$r['Date']." ".(empty($r['Header'])?"":$r['Header']);
    $link=getlink($r['Date']);
    $r['p']['Body']=$r['text'];
    $s=$r['text'];
    $s=prepare_link($s,$r['p']);
    $imgs=search_fotos($s,0);
/*
==H1==
===h2===
====h3====
<blockquote>цитатка
</blockquote>
//    $s=preg_replace("/<h1[^>]*>(.*?)<\/h1>/si","\n== $1 ==\n",$s);
//    $s=preg_replace("/<h2[^>]*>(.*?)<\/h2>/si","\n=== $1 ===\n",$s);
//    $s=preg_replace("/<h3[^>]*>(.*?)<\/h3>/si","\n==== $1 ====\n",$s);
*/
    // реально берем тест
    $s=preg_replace("/<p[^>]*>/si","<br><br>",$s);

    $s=str_ireplace(explode(' ','<ol> </ol> <ul> </ul> </span> </form> </label> </ins> </div> <big> </big> <center> </center> <hr> </table> </td> </tr> </tbody> <tbody> </section>'),'',$s);
    $tags=explode(' ','section div table td ins'); foreach($tags as $x) $s=preg_replace("/<".$x."[^>]*>/si",'',$s); // эти тэги убрать
    $tags=explode(' ','br tr span form input label'); foreach($tags as $x) $s=preg_replace("/<".$x."[^>]*>/si","\n",$s); // эти тэги заменить пустой строкой

    $s=preg_replace("/<script[^>]*>.*?<\/script>/si","",$s);
    $s=preg_replace("/<style[^>]*>.*?<\/style>/si","",$s);
    $s=preg_replace("/<iframe[^>]+src\=[\'\"]*([^\'\">]+).*?<\/iframe>/si","\n<blockquote>VIDEO: $1 </blockquote>\n",$s);
    $s=preg_replace("/<p\sclass\=[\'\"]*name[\'\"]*>([^\n]*)/si","\n==$1==\n",$s);
    $s=preg_replace("/<p\sclass\=[\'\"]*z[\'\"]*>([^\n]*)/si","\n===$1===\n",$s);

    $s=str_ireplace(array(' ','[',']'),array(' ','(',')'),$s);

    $s=preg_replace("/<img[^>]+src\=[\'\"]*([^\'\"\s>]+)[^>]*>/si","$1",$s);

    $s=preg_replace_callback("/<a[^>]+href\=[\'\"]*([^\'\">]+)[^>]*>(.*?)<\/a>/si","vk_drawurl",$s);

    $s=preg_replace("/<blockquote[^>]+>/si",'<blockquote>',$s);
    $s=trim($s);

    // разобраться с фотками
    if(sizeof($imgs)) {
	$att=vk_savefoto($imgs,$r['Date'],(empty($r['Header'])?$r['Date']:$r['Header'])); // загрузить фотки все
	foreach($imgs[0] as $n=>$l) {
		list($img,$X,$Y,$realurl)=explode('|',$att[$n]); if(!(1*$X)) $X=50; if(!(1*$Y)) $Y=50;
		if(!strstr($img,'_')) $img="photo".h($GLOBALS['vk_user_id'])."_".$img;
		$s=str_replace($l,"[[".$img."|".$X."px|".$Y."px]]",$s);
	} // расставить фотки в посте
    }

// ihie($s);

// ---- NOTE ----
$ara=array("title"=>wu($r['Header']), //     [Date] => 2014/09/21_lexus_traffic
"text"=>wu($s),
"privacy"=>0, // 0 — все пользователи, 1 — только друзья, 2 — друзья и друзья друзей, 3 — только пользователь.
"comment_privacy"=>0); // 0 — все пользователи, 1 — только друзья, 2 — друзья и друзья друзей, 3 — только пользователь.

$capnote=sha1($s); if(false===($i=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='vk_note'".ANDP(),"_1",0))) {
    // создать новую заметку
    $vk="notes.add"; $e=vk_go($vk,$ara); if(!isset($e['response']->nid)) dier($e,$vk);
    $note_id=$e['response']->nid;
    msq_add("socialmedias",arae(array('net'=>$nett,'acn'=>$GLOBALS['acn'],'type'=>'vk_note','num'=>$num,'url'=>$r['Date'],'cap_sha1'=>$capnote,'id'=>$note_id)));
    $x='';
} else {
    $ara['note_id']=$note_id=$i['id'];
    if($capnote!=$i['cap_sha1']) { // НАДО обновить
	$vk="notes.edit"; $e=vk_go($vk,$ara,1); $x=" - <font color=green>updated</font>";
	msq_update('socialmedias',arae(array('cap_sha1'=>$capnote)),"WHERE `i`='".$i['i']."'");
	$x=" - <font color=green>updated</font>";
    } else $x=" - <font color=green>ok</font>"; // без обновлений
}
    $note='note'.$GLOBALS['vk_user_id']."_".$note_id; $link='https://vk.com/'.$note;
// ---- NOTE ----

    $ara=array("message"=>wu($text),"attachments"=>$note);

    // а был ли сам пост
    if(($u=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post'".ANDP(),"_l",0))===false) { // сделать пост
	$vk="wall.post"; $e=vk_go($vk,$ara); if(!isset($e['response']->post_id)) dier($e,$vk);
        // а если всё в порядке
        $i=$e['response']->post_id; $u=vk_mkurl($i,$GLOBALS['vk_user_id']);
        msq_add('socialmedias',arae(array('acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$num,'net'=>$nett,'id'=>$i.'|'.$GLOBALS['vk_user_id'])));
	$x.=' posted';
    } else { // если пост уже был - не трогать
	$u=vk_url($u);
	// list($post_id,$post_user)=explode('|',$u);
        // $ara["post_id"]=$post_id; $vk="wall.edit"; $e=vk_go($vk,$ara,1);
    }
        return "<a href='".$u."'>".$u."</a> ".$x;
}

/*
Ссылки
vk.com/history.php - История счета
vk.com/deact.php - Удаление телефона от страницы полезно
vk.com/apps_cashout.php?act=conclusion - Вывод голосов с приложения
vk.com/payments.php?act=votes_transfer - Перевод голосов другу
vk.com/help.php?page=contest - Конкурс
vk.com/help.php?page=demo - С чего начать
vk.com/blank.php?code=666 - Страница заблокирована
vk.com/infested_ip_list.html - Список IP-адресов
vk.com/help.php?page=warning - Заполнение информации о себе
vk.com/badbrowser.php - Левый браузер
vk.com/blog.php?act=archive - Архив новостей
vk.com/deact.php - Удаление номера, привязанного к странице
vk.com/browse.php - Поиск групп
vk.com/events.php - Мои события
vk.com/confirm.php - Подтверждение регистрации
vk.com/friend.php - Мои друзья
vk.com/groups.php - Мои группы
vk.com/help.php - Помощь, но прямой ссылки на эту страницу вроде нет
vk.com/home.php - Redirect на свою страницу
vk.com/index.php - Redirect на свою страницу
vk.com/profile.php - Моя страница
vk.com/invite.php - Пригласить друга
vk.com/login.php - Вход
vk.com/mail.php - Мои сообщения
vk.com/notes.php - Мои заметки
vk.com/people.php - 10 случайных пользователей
vk.com/reg0 - Регистрация
vk.com/reg.php - Регистрация, но ссылок на нее вроде нет
vk.com/search.php - Поиск людей
vk.com/settings.php - Мои настройки
vk.com/wall.php - Без параметров моя стена
vk.com/blog.php - Блог Павла Дурова
*/
// =============================================================================
function ihie($s) { idie(nl2br(h($s))); }
?>