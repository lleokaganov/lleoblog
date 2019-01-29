<?php

function match($e,$s){ return preg_match($e,$s,$m)?$m[1]:false; }
function confvar($n,$v,$s) { return preg_replace("/([\n\r]+\s*[\$]".$n."\s*=\s*[\'\"]{0,1})[^\'\"\n\r\;]*([\'\"]{0,1}\s*;)/s",'${1}'.$v.'${2}',$s); }
// function hash_generate(){ $i=0; if(($g=fopen("/dev/random","rb"))!==false) { $i+=fgets($g); fclose($g); } else $i+=rand(0,256); for($c=0;$c<=$i;$c++) mt_rand(); $A='ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz01234567890'; for($s='',$i=0,$n=strlen($A);$i<64;$i++) $s.=$A[mt_rand(0,$n-1)]; return $s; }

// Эта функция возвращает false, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.
function installmod_init() {
	$a=array();

	// операции с конфигом
	$f=$GLOBALS['filehost']."config.php";
	$q=fileget($f); if(empty($q)) $a['config.php']="Can't read $f";

	$s=$q;
	$hash=explode(" ","newhash_user hashinput hashlogin hashrss"); foreach($hash as $h) {
	    if(preg_match("/(\n\s*[\$]".$h."\s*\=\s*[\'\"])([\'\"]\s*\;)/s",$q,$m)) {
		$a["hash $h"]="OK creating hash \$".$h." in config.sys";
		$q=str_replace($m[0],$m[1].hash_generate().$m[2],$q);
	    }
	}

	if($s!=$q) {
		if(false===fileput($f,$q)) $a['config.php update']="Can't replace file: ".$f;
		$a['hash seeds']="OK create hashs";
	}

//	$engine=array(); $r=explode(' ',"name hostname subdir admin_name admin_email admin_password");
//	foreach($r as $l) { if(false===($engine[$l]=match("/".$l."\=([^\s\$]*)/s",$q))) $a['CONFIG:'.$l]="Var `".$l."` not set"; }

	// сервисы
	$a['GD']=(function_exists('imagecreatefromjpeg')?"OK":"install PHP-GD module: apt install php-gd<br>А иначе фотки не будут работать и прочее говно.");
	$a['curl']=(function_exists('curl_init')?"OK":"install PHP-CURL module: apt install php-curl<br>А иначе может не работать авторизация внешних сайтов и прочие сложные сервисы.");

	$folders=explode(" ","hidden hidden/tmp hidden/log"); foreach($folders as $l) { $l=$GLOBALS['filehost'].$l;
	    if(!is_dir($l)) {
		$a[$l] = (false===mkdir($l) || !is_dir($l) ? "Error. Create folder $l manually" : "OK: folder $l sucessfully created");
		if(false===chmod($l,0777)) $a["$l chmod"] = "can't chmod 0777";
	    }
	    $ht=$l."/.htaccess";
	    if(is_file($ht)) continue;
	    file_put_contents($ht,"ZABORONENO"); if(!is_file($ht)) $a[$ht] = "Can't create $ht If you use NGINX you need deprecate $ht";
	}

	$a['unic system']=(!empty($GLOBALS['unic'])?"OK":"Blank login");

	if(empty($GLOBALS['db_unic'])) $a['db_unic']="Set <b>\$db_unic=\"`unic`\";</b> in config.sys";
	if(empty($GLOBALS['db_mailbox'])) $a['db_mailbox']="Set <b>\$db_mailbox=\"`mailbox`\";</b> in config.sys";

	if(!empty($GLOBALS['admin_unics'])) $a['admin unics']="OK";
	else {
	    $a['admin unics']="Empty \$admin_unics "
	    .(empty($GLOBALS['unic'])?"Continue installation...":"Set <b>\$admin_unics=\"".$GLOBALS['unic']."\";</b> in config.sys");
	}

	// ИТОГИ

	$o=''; foreach($a as $n=>$l) {
	    if($l=='OK') continue;
	    if(substr($l,0,2)=='OK') $l="<font color=green>$l</font>";
	    else $l="<font color=red>$l</font>";
	    $o.="<div>module $n: $l</div>";
	}

	return (empty($o)?false:$o);
}

function installmod_do() { return 0; }
function installmod_allwork() { return 0; }

?>