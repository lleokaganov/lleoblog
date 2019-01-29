<?php

function iuw($s) { return(iconv("utf-8","windows-1251//TRANSLIT",$s)); }


function idien($s) { idie(nl2br(h($s))); }
function cf_tmpl($cf) { return loadsite($cf['template']); }
function ret_err($s) { if(gettype($s)!='array') $s=array('error'=>$s); $s['OK']='ERROR'; $s['msqe']=$GLOBALS['msqe']; return $s; }
function ret_ok($s) { if(gettype($s)!='array') $s=array('message'=>$s); if($GLOBALS['msqe']!=''||(isset($s['OK'])&&$s['OK']!='OK')) return ret_err($s); $s['OK']='OK'; return $s; }
function test_deprecate_dir($dir) { $f=rtrim($dir,'/')."/.htaccess"; if(is_file($f)) return; testdir($dir); fileput($f,"strongly nahui deprecated"); }
function search_fotos($text,$vnutr=1) { // ищем фотки
    if($vnutr) preg_match_all("/".preg_quote($GLOBALS['httphost'],'/')."[^<>\s]+\.(jpg|png|jpeg|gif)/si",$text,$imgs,PREG_PATTERN_ORDER);
    else preg_match_all("/https?\:\/\/[^<>\s]+\.(jpg|png|jpeg|gif)/si",$text,$imgs,PREG_PATTERN_ORDER);
    if(!sizeof($imgs[0])) return array();
    unset($imgs[1]);
    $fo=array(); $t=$text; $imgs['txt']=array(); foreach($imgs[0] as $n=>$l) {
	list($a,$b)=explode($l,$t,2); if($n) $imgs['txt'][$n-1]=c0(strip_tags(trim($a))); else $text=c0($a);
	$t=$b;
	if(in_array($l,$fo)) unset($imgs[0][$n]); else $fo[]=$l;
    } $imgs['txt'][$n]=c0(strip_tags(trim($t)));

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







function fb_link($cf,$id){ if(empty($id)) idie("empty id"); return "https://m.facebook.com/".h($cf['domain'])."/posts/".$id; }

function fb_delete($cf,$num) {
    $sm=ms("SELECT `id`,`i` FROM `socialmedias` WHERE `type`='post' AND `num`='".e($num)."' AND `net`='".e($cf['nett'])."'".ANDC()." LIMIT 1","_1",0);
    if($sm===false) return ret_ok('deleted from base');
    $x=fb_del($cf,$sm['id']); if($x['OK']!='OK') return $x; // ошибка
    msq("DELETE FROM `socialmedias` WHERE `i`='".e($sm['i'])."'");
    $x['smi']=$sm['i'];
    return ret_ok($x); // обновлено
}


function fb_posting($cf,$p){
    $head=$p['Date']." ".(empty($p['Header'])?"":$p['Header']);
    if(!isset($p['link'])) $p['link']=getlink($p['Date']); $link=$p['link'];
    include_once $GLOBALS['include_sys']."_onetext.php";
    $admin=$ADM=$podzamok=$unic=0; // сбросить ВСЕ авторизационные переменные чтоб не показывать подзамки!!!
    $p=mkzopt($p);
    $s=plain_Body($p);

// dier($p);

// idie(nl2br(h($s)));

    $fotos=array(); $imgs=search_fotos($s); if(sizeof($imgs))foreach($imgs[0] as $n=>$l) {
	$file=rtrim($GLOBALS['host'],'/').rpath(substr($l,strlen($GLOBALS['httpsite'])));
	if(!is_file($file)) idie("Foto not found: ".h($file));
	$fotos[]=$file;
	if(sizeof($imgs[0])==1) {
		$imz="\n\n[ см. фото ]\n\n"; // если фотка единственная
		$a=explode($l,$s,2); if(c0($a[0])=='') { $s=$a[1]; break; } // если фотка единственная и стояла в самом верху - вообещ не помечать, сами увидят
	} else $imz="\n\n[ см. фото № ".($n+1)." в заметке ".$link."]\n\n"; // если фотка единственная
	if(basename($l)=='play_youtube.png') $imz="\n\n[ тут в оригинальном посте открывается видеоролик Ютуба ]";
	$s=str_replace($l,$imz,$s);
    }

    $s=mpers(cf_tmpl($cf),array('Header'=>$head,'text'=>$s,'url'=>$link));

// idien($s);

    $cap_sha1=sha1(fb_prepare_text($s));
    $sm=ms("SELECT `i`,`id`,`cap_sha1` FROM `socialmedias` WHERE `type`='post' AND `num`='".e($p['num'])."' AND `net`='".e($cf['nett'])."'".ANDC()." LIMIT 1","_1",0);
    if($sm!=false) { // Edit
	$id=$sm['id']; $link=fb_link($cf,$id);
	if($cap_sha1!=$i['cap_sha1']) return ret_ok(array('message'=>'no changed','link'=>$link,'smi'=>$sm['i'])); // не надо обновлять
	$x=fb_edit($cf,$id,$s,$fotos); if($x['OK']!='OK') return $x; // ошибка
	msq_update('socialmedias',arae(array('cap_sha1'=>$cap_sha1)),"WHERE `i`='".$sm['i']."' LIMIT 1");
	return array_merge($x,array('smi'=>$sm['i'])); // обновлено
    }
    // New
    $x=fb_post($cf,$s,$fotos); if($x['OK']!='OK') return $x; // ошибка
    msq_add('socialmedias',arae(array('cap_sha1'=>$cap_sha1,'acn'=>$GLOBALS['acn'],'type'=>'post','num'=>$p['num'],'net'=>$cf['nett'],'id'=>$x['id'],'url'=>$x['link'])));
    $x['smi']=msq_id();
    return ret_ok($x); // обновлено
}


function fb_prepare_text($s) {
    $s=c0($s);
    $s=str_replace(" \n","\n",$s);
    $s=preg_replace("/\n{3,}/s","\n\n",$s);
    $s=str_replace("\n\n","\n".chr(160)."\n".chr(160)."\n",$s);
    return $s;
}

// процедуры без движка

function fb_post($cf,$text,$fotos) {
    $text=fb_prepare_text($text);
    $x=fb_run("POST",$cf['user'],$cf['pass'],$cf['domain'],array('id'=>0,'text'=>$text,'fotos'=>$fotos)); // выполнить запрос
    if($x['OK']!='OK' || !preg_match("/[^\s]+\/posts\/\d+$/s",$x['res'],$m)) return ret_err($x);
    $x['link']=$m[0]; $x['id']=basename($x['link']);
    return $x;
}

function fb_edit($cf,$id,$text,$fotos) {
    $text=fb_prepare_text($text);
    $x=fb_run("EDIT",$cf['user'],$cf['pass'],$cf['domain'],array('id'=>$id,'text'=>$text,'fotos'=>$fotos)); // выполнить запрос
    if($x['OK']!='OK' || $m[1]!=$id || (!preg_match("/ id=(\d+)$/s",$x['res'],$m) && !preg_match("/[^\s]+\/posts\/(\d+)$/s",$x['res'],$m))) return ret_err($x);
    $x['link']=fb_link($cf,$id);
    return $x;
}

function fb_del($cf,$id) {
    $x=fb_run("DEL",$cf['user'],$cf['pass'],$cf['domain'],array('id'=>$id)); // выполнить запрос
    if($x['OK']!='OK' || !preg_match("/ id=(\d+)$/s",$x['res'],$m) || $m[1]!=$id) return ret_err($x); return $x;
}

// ======================================== основная процедура =====================================================

function fb_run($a,$user='',$pass='',$domain='',$args=array()) { $go=0;
    if($a=='ver') return "phantomjs ver: ".exec("phantomjs -v");

    // где лежит скрипт
    $script_file=$GLOBALS['filehost']."extended/phantom-facebook.js"; if(!is_file($script_file)) idie("Error: not install ".$script_file);

    // папка для файлов
    if(!$GLOBALS['unic']) idie("unic = 0, reload page once");
    $acc=verf_acc(); if($GLOBALS['ADM']) $pdir=$GLOBALS['filehost']."hidden/user/".($acc==''?'x':$acc); else
    $pdir=$GLOBALS['filehost']."hidden/unic/".$GLOBALS['unic'];

    // создать всякие полезные папки в ней
    test_deprecate_dir($pdir);
    $LSdir=$pdir."/fb_LS"; test_deprecate_dir($LSdir);
    $SHOTdir=$pdir."/fb_SHOT"; test_deprecate_dir($SHOTdir);
    $g=glob($SHOTdir."/*");if($g)foreach($g as $l) { if(basename($l)!=".htaccess") unlink($l); } // удалить все шоты если были
    $logfile=$pdir."/log.txt"; if(is_file($logfile)) { unlink($logfile); touch($logfile); chmod($logfile,0666); }

    if($a=='CLEAN') { return ret_ok(deldir($LSdir).deldir($SHOTdir).deldir($pdir)); }

    $user=preg_replace("/[^a-z0-9\-\_\.\@]+/si",'',$user);
    if($domain=='' && !strstr($user,'@')) $domain=$user;
    $pass=preg_replace("/[\n\r\t\"\']+/s",'',$pass);
    if(isset($args['id'])) $args['id']=preg_replace("/[^\d]+/s",'',$args['id']);

    $PHANTOM="phantomjs --debug=false --disk-cache=false"
." --local-storage-path=\"".escapeshellcmd($LSdir)."\""
." --cookies-file=\"".escapeshellcmd($pdir)."/cookie_".md5($user).".dat\" --ssl-protocol=any"
." ".escapeshellcmd($script_file)
." debug=".(empty($args['debug'])?1:intval($args['debug']))
." logfile=\"".escapeshellcmd($logfile)."\""
." shotdir=\"".escapeshellcmd($SHOTdir)."\""
." domain=-" // \"".escapeshellcmd($domain)."\""
." email=-" // \"".escapeshellcmd($domain)."\""
." pass=-" // \"".escapeshellcmd($pass)."\""
; $TO_PIPES=array($domain,$user,$pass);

    if($a=='GROUPLIST') { $go=$PHANTOM." GROUPLIST debug=0"; }
    if($a=='GROUPPOST') { $go=$PHANTOM." GROUPPOST groupreturn=1 groupname=- grouplink=- -"; $TO_PIPES[]=$args['groupname']; $TO_PIPES[]=$args['grouplink']; $TO_PIPES[]=wu($args['text']); }
    if($a=='GROUPLEAVE') { $go=$PHANTOM." GROUPLEAVE groupname=- grouplink=-"; $TO_PIPES[]=$args['groupname']; $TO_PIPES[]=$args['grouplink']; }

    if($a=='DEL') {
	if(empty($args['id'])) idie("No ID");
        $go=$PHANTOM." DEL id=-"; $TO_PIPES[]=$args['id'];
    }

    if($a=='EDIT') {
	if(empty($args['text'])) idie("No text");
	if(empty($args['id'])) idie("No ID");
	$go=$PHANTOM." EDIT id=- saveto=\"".escapeshellcmd($pdir)."/fb_posts.txt\" -";
	$TO_PIPES[]=$args['id'];
	$TO_PIPES[]=wu($args['text']);
	// if(gettype($args['fotos'])=='array') { if(sizeof($args['fotos'])==1) $fotos[]=$fotos[0]; foreach($args['fotos'] as $img) $go.=" \"".h($img)."\""; }
    }

    if($a=='POST') {
	if(empty($s)) idie("No text");
	$go=$PHANTOM." POST saveto=\"".escapeshellcmd($pdir)."/fb_posts.txt\" -"; $TO_PIPES[]=wu($args['text']);
//     if(gettype($fotos)=='array') // взять первую и не ебать мозг
//	// if(sizeof($fotos)>1) // $fotos[]=$fotos[0];
//	foreach($fotos as $img) {
//	    $img=preg_replace("/[\n\r\t\"\']+/s",'',$img); if(!is_file($img)) idie("ERROR!!! IMG ERROR!!! ".h($img));
//	    $go.=" \"".h($img)."\"";
//	}
    }

    if(!$go) idie("fb.php error action: `".h($a)."`");

// dier($TO_PIPES,$go);

    $proc=proc_open($go,array(array("pipe","r"),array("pipe","w"),array("pipe","w")),$pipes);
	foreach($TO_PIPES as $l) fwrite($pipes[0],str_replace("\n","\\n",$l)."\n");
//	fwrite($pipes[0],$user."\n");
//	fwrite($pipes[0],$pass."\n");
//	if($zametka) fwrite($pipes[0],str_replace("\n","\\n",$zametka)."\n");
    $log=stream_get_contents($pipes[1]); $log=uw(rtrim($log,"\n")); $o=explode("\n",$log);
 $last=$o[sizeof($o)-1]; list($err,$res)=explode(': ',$last,2);
    $x=array('res'=>$res,'last'=>$last,'log'=>$log);
    return ($err=='DONE'?ret_ok($x):ret_err($x));
}


function deldir($d) { $o="\nclean: ".$d;
    if(!is_dir($d)) return $d."/ not exist\n";
    $g=glob($d."/*"); $g[]=$d."/.htaccess"; foreach($g as $l) {
	unlink($l);
	$o.=$l." - ".(is_file($l)?'ERROR':'deleted')."\n";
    }
    rmdir($d);
    return $o.=$d."/ ".(is_dir($d)?'ERROR FOLDER':'folder erased')."\n";
}

?>