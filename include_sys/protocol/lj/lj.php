<?php // Про

function LJ_feof_load($url,$ara) {
    if(!function_exists('feof_fp')) include_once $GLOBALS['include_sys']."_files.php";
    return feof_load($url,stream_context_create(array(
        'http'=>array(
        'method'=>"POST",
        'header'=>
                "Accept-language: ru-ru,ru\r\n",
                "Content-type: application/x-www-form-urlencoded\r\n",
                "USER_AGENT: Mozilla/5.0 (compatible; MSIE 6.0; Windows NT 5.1)\r\n",
        'content'=>http_build_query($ara)
    ))));
}

// =============================================== LJ functions =========================================
function LJ_edit($user,$pass,$item,$subj,$body,$opts,$flat="http://www.livejournal.com/interface/flat") { if(gettype($opts)!='array') $opts=array();
    $ans=LJ_feof_load($flat,array_merge(array('user'=>$user,'password'=>$pass,'ver'=>'1',
	'mode'=>'editevent',
	'itemid'=>$item,
	'subject'=> $subj,
	'event'=> $body
    ),$opts));
    preg_match_all("/([^\n]+)\n([^\n]+)\n/si",$ans,$m); unset($ans); for($i=0;$i<sizeof($m[1]);$i++) $ans[$m[1][$i]]=$m[2][$i];
    return($ans);
}


function LJ_get($user,$pass,$item,$flat="http://www.livejournal.com/interface/flat") {
    $ans=LJ_feof_load($flat,array_merge(array('user'=>$user,'password'=>$pass,'ver'=>'1',
	'mode'=>'getevents',
	'selecttype'=>'one',
	'itemid'=> $item
    ),$opts));
    preg_match_all("/([^_\n]+)([_\d]*)([^_\n]+)\n([^\n]+)\n/si",$ans,$m); unset($ans); $ans=array();
    for($i=0;$i<sizeof($m[1]);$i++) $ans[intval(str_replace("_","",$m[2][$i]))][$m[3][$i]]=urldecode($m[4][$i]);
    return($ans[1]);
}


function LJ_getlast($user,$pass,$flat="http://www.livejournal.com/interface/flat") {
    $ans=LJ_feof_load($flat,array_merge(array('user'=>$user,'password'=>$pass,'ver'=>'1',
	'mode'=>'getevents',
	'selecttype'=>'one',
	'itemid'=> '-1',
    ),$opts));
    preg_match_all("/([^_\n]+)([_\d]*)([^_\n]+)\n([^\n]+)\n/si",$ans,$m); unset($ans); $ans=array();
    for($i=0;$i<sizeof($m[1]);$i++) $ans[intval(str_replace("_","",$m[2][$i]))][$m[3][$i]]=urldecode($m[4][$i]);
    return($ans[1]);
}


function LJ_post($user,$pass,$subj,$body,$opts,$flat="http://www.livejournal.com/interface/flat") {
    $ans=LJ_feof_load($flat,array_merge(array('user'=>$user,'password'=>$pass,'ver'=>'1',
	'mode'=>'postevent',
	'subject'=> $subj,
	'event'=> $body,
	'year'=>date("Y"),
	'mon'=>date("m"),
	'day'=>(date("d")),
	'hour'=>date("H"),
	'min'=>date("i")
    ),$opts));
    preg_match_all("/([^\n]+)\n([^\n]+)\n/si",$ans,$m); $ans=array(); foreach($m[1] as $i=>$l) $ans[$l]=$m[2][$i];
    return($ans);
}
?>