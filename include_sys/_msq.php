<?php //if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй

$starttime=time();

function set_ttl() { global $admin,$ttl,$jaajax,$MYPAGE,$MYPAGE_MD5;
	if($admin) { if($jaajax) $ttl=0; else {
		$MYPAGE_MD5=md5($MYPAGE);
		$ttl=(isset($_COOKIE['MYPAGE']) && $MYPAGE_MD5==$_COOKIE['MYPAGE']?0:60);
		setcoo('MYPAGE',$MYPAGE_MD5,time()+20);
		}
	} else if(!isset($ttl)) $ttl=60;
} set_ttl();

/*
ПОЛЕЗНЫЕ ПРИМЕРЫ

include_once $_SERVER['DOCUMENT_ROOT']."/dnevnik/_msq.php"; msq_open('lleo');

	$ara=array();
	$ara['name']=e($name);
	$ara['sc']=e($sc);
	$ara['ipipx']=e($_SERVER['REMOTE_ADDR'].' '.$_SERVER['HTTP_X_FORWARDED_FOR']);
	$ara['value']=e($value);

if(!msq_exist($db_,"WHERE `name`='".$ara['name']."' AND (`sc`='".$ara['sc']."' OR `ipipx`='".$ara['ipipx']."')"))
msq_add($db_,$ara);

$n=intval(msqn(msq("SELECT `value` FROM `$db_` WHERE `name`='$name' AND `value`='$l'")));

msq_update($tb,$ara,"WHERE `name`='lleo'");

msq_add_update($db_,array('name'=>$name,'text'=>implode("\n",$o)),'name');

msq_del($tb,$ara,$u='')
*/

// if(!isset($memcache)) cache_init();
$msqe=''; // сюда пишем ошибки
ms_connect(); // соединиться с базой - эта процедура в _autorize.php

function ms_connect() { if(isset($GLOBALS['ms_connected'])) return;

if(!($GLOBALS['ms_connected']=(function_exists('mysqli_connect') ?
    @mysqli_connect($GLOBALS['msq_host'], $GLOBALS['msq_login'], $GLOBALS['msq_pass'],$GLOBALS['msq_basa'])
    :@mysql_connect($GLOBALS['msq_host'], $GLOBALS['msq_login'], $GLOBALS['msq_pass'])
))) {
	logi("MSQ_ERRORS.txt","\n".date("Y-m-d H:i:s")." error");
	idie("<p>MySQL error!"
    .($GLOBALS['admin']?"Check config.php:<ul> \$msq_host = '".$GLOBALS['msq_host']."';
<br>\$msq_login = '".$GLOBALS['msq_login']."';<br>\$msq_pass = [...]":"
May be it is a temporarry problem? Try to reload page in several seconds or minutes."));
    }

    if(function_exists('mysqli_connect')) {

	// еще одна сраная йобаная заплатка
	if(!function_exists('mysqli_fetch_all')) { function mysqli_fetch_all($sql) { for($res=array(); $tmp=mysqli_fetch_array($sql);) $res[]=$tmp; return $res; } }

	@mysqli_query($GLOBALS['ms_connected'],"SET NAMES ".$GLOBALS['msq_charset']);
	@mysqli_query($GLOBALS['ms_connected'],"SET @@local.character_set_client=".$GLOBALS['msq_charset']);
	@mysqli_query($GLOBALS['ms_connected'],"SET @@local.character_set_results=".$GLOBALS['msq_charset']);
	@mysqli_query($GLOBALS['ms_connected'],"SET @@local.character_set_connection=".$GLOBALS['msq_charset']);

    } else {

	@mysql_select_db($GLOBALS['msq_basa']) or idie("<p>Good news: engine is working! Then, MySQL detected and connect successfull.
<br>Bad news: MySQL BASE <b>`".$GLOBALS['msq_basa']."`</b> is not exist.<br>You have to define base name in config.sys: <b>\$msq_basa = '".$GLOBALS['msq_basa']."';</b>");

	@mysql_query("SET NAMES ".$GLOBALS['msq_charset']);
	@mysql_query("SET @@local.character_set_client=".$GLOBALS['msq_charset']);
	@mysql_query("SET @@local.character_set_results=".$GLOBALS['msq_charset']);
	@mysql_query("SET @@local.character_set_connection=".$GLOBALS['msq_charset']);
    }

}

function msq_id() { return (function_exists('mysqli_connect')?mysqli_insert_id($GLOBALS['ms_connected']):mysql_insert_id()); }

function e($s) { return (function_exists('mysqli_connect')?@mysqli_real_escape_string($GLOBALS['ms_connected'],$s):@mysql_real_escape_string($s)); }
function msq_exist($tb,$u) { return ms("SELECT COUNT(*) FROM $tb $u","_l",0); }
//function msqn($sql) { return mysql_num_rows($sql); }

function msq_add($tb,$ara) {
        $a=$b=''; foreach($ara as $n=>$m) { $a.="`$n`,"; $b.="'$m',"; } $a=trim($a,','); $b=trim($b,',');
        $s = "INSERT INTO $tb ($a) VALUES ($b)";
        return msq($s);
}

function msq_add1($tb,$ara) {
        $a=$b=''; foreach($ara as $n=>$m) { $a.="`$n`,"; $b.="$m,"; } $a=trim($a,','); $b=trim($b,',');
        $s = "INSERT INTO $tb ($a) VALUES ($b)";
        return msq($s);
}


function msq_update($tb,$ara,$u='') {
        $a=''; foreach($ara as $n=>$m) $a.="`$n`='$m',"; $a=trim($a,',');
        $s="UPDATE $tb SET $a $u";
        return msq($s);
}

function msq_add_update($tb,$ara,$u='id') {
	if(!stristr($u,'WHERE ')) { $keys=explode(' ',$u);
		$u=array(); foreach($keys as $k) { if($k=='ANDC') break; $u[]="`".e($k)."`='".e($ara[$k])."'"; }
		$u="WHERE ".implode(' AND ',$u).($k=='ANDC'?ANDC():'');
	}
	if(!msq_exist($tb,$u)) $s=msq_add($tb,$ara);
	else { if(sizeof($keys)) { foreach($keys as $k) unset($ara[$k]); } $s=msq_update($tb,$ara,$u); }
	return $s;
}

function msq_del($tb,$ara,$u='') {
	$a=''; foreach($ara as $n=>$m) $a.="`$n`='$m' AND "; $a=substr($a,0,-5);
	$s="DELETE FROM $tb WHERE $a $u";
	return msq($s);
}

$GLOBALS['msqe_last']='';

function msq($s) { global $msqe;
	$GLOBALS['msqe_last']=$s;

	if(time()-$GLOBALS['starttime']>15) {
		logi('starttime.log',"\nerror: ".$GLOBALS['MYPAGE']);
		if($GLOBALS['ajax']) idie('Timeout error'); die('Timeout error');
	}

    if(function_exists('mysqli_connect')) {
	$sql=@mysqli_query($GLOBALS['ms_connected'],$s);
	$e=@mysqli_error($GLOBALS['ms_connected']); if($e!='') { // $sql=false; 
$msqe .= "<p><font color=green>mysqli_query(\"$s\")</font><br><font color=red>$e</font>"; }
    } else {
	$sql=@mysql_query($s);
	$e=@mysql_error(); if($e!='') { $sql=false; $msqe .= "<p><font color=green>msq_query(\"$s\")</font><br><font color=red>$e</font>"; }
    }
	return($sql);
}

function msq_pole($tb,$pole) { // проверить, существует ли такое поле в таблице $tb
	if(!msq_table($tb)) return false;
        $pp=ms("SHOW COLUMNS FROM ".e($tb)."","_a",0); foreach($pp as $p) if($p['Field']==$pole) return $p['Type'];
	return false;
}

function msq_table($table) { // проверить, существует ли такая таблица
        $ppp=ms("SHOW TABLES","_a",0); if($ppp!==false) foreach($ppp as $pp) if(sizeof($pp)) foreach($pp as $p) if($p==$table) return true;
        return false;
}

function msq_index($tb,$index) { // проверить, существует ли такой индекс (если указан еще ,0 - то первичный)
	if(!msq_table($tb)) return false;
        $pp=ms("SHOW INDEX FROM $tb","_a",0); if($pp!==false) foreach($pp as $p)
//	if($p['Column_name']==$index && $p['Non_unique']=='1') return true; // [Seq_in_index] => 1
	if($p['Column_name']==$index) return ($p['Key_name']=='PRIMARY'?1:true); 
	return false;
}

//function tos($e) { return str_replace(array("\\","'",'"',"\n","\r"),array("\\\\","\\'",'\\"',"\\n",""),$e); }

function ms($query,$mode='_a',$ttl=666) { $s = false; $magic='@'.$GLOBALS['blogdir']; if($ttl==666) $ttl=$GLOBALS['ttl'];

	if($ttl < 0) { cache_rm($mode.$magic.$query); return true; } // сбросить кэш
	elseif ($ttl > 0) {  $result=cache_get($mode.$magic.$query); if(false!==$result) {
		$GLOBALS['ms_ttl']='cache';
		return $result; }
	}
	$GLOBALS['ms_ttl']='new';
	$sql = @msq($query);

if(function_exists('mysqli_connect')) {
	if(gettype($sql)!='object') { /*print "SQL error: ".mysqli_error($GLOBALS['ms_connected']);*/ return false; }
	if($mode == '_1') { $s=mysqli_fetch_assoc($sql); if(empty($s)) $s=false; }
	elseif($mode == '_l') { $s=mysqli_fetch_all($sql); if(empty($s)) $s=false; else $s=$s[0][0]; } // [0][0]
	else { $s=array(); while($p=mysqli_fetch_assoc($sql)) $s[]=$p; }
	mysqli_free_result($sql);
} else {
	if($sql === false) { /*print "SQL error: ".msq_error();*/ return false; }
	if($mode == '_a') { $s=array(); while($p = mysql_fetch_assoc($sql)) $s[]=$p; }
	elseif($mode == '_1') { $s=(mysql_num_rows($sql)<1?false:mysql_fetch_assoc($sql)); }
	elseif($mode == '_l') {
		if(gettype($sql)!='resource') $s=false;
		else $s=(mysql_num_rows($sql)<1?false:mysql_result($sql,0,0));
	} else { $s=array(); while($p=mysql_fetch_assoc($sql)) $s[$p[$mode]]=$p; }
}

	if($ttl > 0) { cache_set($mode.$magic.$query, $s, $ttl); }
	return $s;
}

// function cache_init() { global $memcache; $memcache=memcache_connect('memcache_host', 11211); }
function cache_md5($k) { global $msq_host,$msq_basa; return substr(sha1("$msq_host $msq_basa $k"),0,8); }
function cache_set($k,$v,$e) { global $memcache; if(!$memcache) return false; return memcache_set($memcache,cache_md5($k),$v,MEMCACHE_COMPRESSED,$e); }
function cache_get($k) { global $memcache; if(!$memcache) return false; return memcache_get($memcache,cache_md5($k)); }
function cache_get_raw($k) { global $memcache; if(!$memcache) return false; return memcache_get($memcache,$k); }
function cache_rm($k) { global $memcache; if(!$memcache) return false; $k=cache_md5($k); memcache_set($memcache,$k,false,0,1); return memcache_delete($memcache,$k); }
function arae($ara){ $p=array(); foreach($ara as $n=>$l) $p[e($n)]=e($l); return $p; }

// утилиты работы с юзердатой
function userdata_load($basa,$name) { return ms("SELECT `data` FROM `".get_dbuserdata()."` WHERE `basa`='".e($basa)."' AND `name`='".e($name)."'".ANDC(),"_l",0); }
function userdata_save($basa,$name,$data) { return msq_add_update(get_dbuserdata(),array('data'=>e($data),'basa'=>e($basa),'name'=>e($name),'acn'=>intval($GLOBALS['acn'])),"basa name ANDC"); }
function userdata_get($basa,$f=0,$l=99999) { return ms("SELECT `name`,`data` FROM `".get_dbuserdata()."` WHERE `basa`='".e($basa)."'".ANDC()." LIMIT ".intval($f).",".intval($l),"_a",0); }
function get_dbuserdata() { return e(empty($GLOBALS['db_userdata'])?'userdata':$GLOBALS['db_userdata']); }
?>