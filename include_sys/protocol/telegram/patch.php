<?php

date_default_timezone_set("Etc/GMT-3");
header_remove('X-Powered-By');

// if(@stristr($_SERVER["HTTP_USER_AGENT"],'bot')) { header("HTTP/1.1 404 Not Found"); die("Error 404"); }

if(isset($_GET['qnginx'])) {
    if(strstr($_GET['qnginx'],'?')) {
list(,$_SERVER['QUERY_STRING'])=explode('?',$_SERVER['QUERY_STRING'],2);
list(,$a)=explode('?',$_GET["qnginx"],2);
if(strstr($a,'=')) list($a,$b)=explode('=',$a,2); else $b=''; if($b===NULL) $b=''; if($a!='') $_GET[$a]=$_REQUEST[$a]=$b;
    } else $_SERVER['QUERY_STRING']='';
    unset($_GET["qnginx"]); unset($_REQUEST["qnginx"]);
}

if(!function_exists('e')) {

function h($s) { return htmlspecialchars($s); }

function selecto($n,$x,$a,$t='name') { if($x==='0'||intval($x)) $x=intval($x);
    $s="<select ".$t."='".$n."'>";
    foreach($a as $l=>$t) $s.="<option value='$l'".($x===$l?' selected':'').">".$t."</option>";
    return $s."</select>";
}

// MySQLI NEW

function msq_id() { return (function_exists('mysqli_connect')?mysqli_insert_id($GLOBALS['ms_connected']):mysql_insert_id()); }
function e($s) { return (function_exists('mysqli_connect')?@mysqli_real_escape_string($GLOBALS['ms_connected'],$s):@mysql_real_escape_string($s)); }

function msq($s) {
    if(function_exists('mysqli_connect')) {
        $sql=@mysqli_query($GLOBALS['ms_connected'],$s);
        $e=@mysqli_error($GLOBALS['ms_connected']); if($e!='') { $GLOBALS['msqe'].= "<p><font color=green>mysqli_query(\"$s\")</font><br><font color=red>$e</font>"; }
        return($sql);
    }
    $sql=mysql_query($s); if(($e=mysql_error())=='') return($sql);
    $GLOBALS['msqe'].="<p><font color=green>mysql_query(\"".h($s)."\")</font><br><font color=red>".h($e)."</font>";
}

function ms($query,$mode='_a') { $s = false;
        $sql = @msq($query);

if(function_exists('mysqli_connect')) {
        if(gettype($sql)!='object') { return false; }
        if($mode == '_1') { $s=mysqli_fetch_assoc($sql); if(empty($s)) $s=false; }
        elseif($mode == '_l') { $s=mysqli_fetch_all($sql); if(empty($s[0][0])) $s=false; else $s=$s[0][0]; }
        else { $s=array(); while($p=mysqli_fetch_assoc($sql)) $s[]=$p; }
        mysqli_free_result($sql);
} else {
        if($sql === false) { return false; }
        if($mode == '_a') { $s=array(); while($p = mysql_fetch_assoc($sql)) $s[]=$p; }
        elseif($mode == '_1') { $s=(mysql_num_rows($sql)<1?false:mysql_fetch_assoc($sql)); }
        elseif($mode == '_l') {
                if(gettype($sql)!='resource') $s=false;
                else $s=(mysql_num_rows($sql)<1?false:mysql_result($sql,0,0));
        } else { $s=array(); while($p=mysql_fetch_assoc($sql)) $s[$p[$mode]]=$p; }
}
        return $s;
}

function msq_connect() { if(isset($GLOBALS['ms_connected'])) return;

    $GLOBALS['msqe']='';

/*
    $GLOBALS['msq_host']='localhost';
    $GLOBALS['msq_login']='dnevnik';
    $GLOBALS['msq_pass']='';
    $GLOBALS['msq_basa']='dnevnik';

    $GLOBALS['msq_charset']="utf8";
*/

$GLOBALS['ms_connected']=(function_exists('mysqli_connect') ?
    @mysqli_connect($GLOBALS['msq_host'], $GLOBALS['msq_login'], $GLOBALS['msq_pass'],$GLOBALS['msq_basa'])
    :@mysqli_connect($GLOBALS['msq_host'], $GLOBALS['msq_login'], $GLOBALS['msq_pass'])
);

    if(!$GLOBALS['ms_connected']) die("<p>MySQL error! Check config.php:<ul> \$msq_host = '".$GLOBALS['msq_host']."';<br>\$msq_login = '".$GLOBALS['msq_login']."';<br>\$msq_pass = [...]");

if(!function_exists('mysqli_connect')) {
    @mysqli_select_db($GLOBALS['msq_basa']) or die("<p>Good news: engine is working! Then, MySQL detected and connect successfull.
<br>Bad news: MySQL BASE <b>`".$GLOBALS['msq_basa']."`</b> is not exist.<br>You have to define base name in config.sys: <b>\$msq_basa = '".$GLOBALS['msq_basa']."';</b>");
}

if(function_exists('mysqli_connect')) {

    // еще одна сраная йобаная заплатка
    if(!function_exists('mysqli_fetch_all')) { function mysqli_fetch_all($sql) { for($res=array(); $tmp=mysqli_fetch_array($sql);) $res[]=$tmp; return $res; } }

   @mysqli_query($GLOBALS['ms_connected'],"SET NAMES ".$GLOBALS['msq_charset']);
   @mysqli_query($GLOBALS['ms_connected'],"SET @@local.character_set_client=".$GLOBALS['msq_charset']);
   @mysqli_query($GLOBALS['ms_connected'],"SET @@local.character_set_results=".$GLOBALS['msq_charset']);
   @mysqli_query($GLOBALS['ms_connected'],"SET @@local.character_set_connection=".$GLOBALS['msq_charset']);

} else {
   @mysql_query("SET NAMES ".$GLOBALS['msq_charset']);
   @mysql_query("SET @@local.character_set_client=".$GLOBALS['msq_charset']);
   @mysql_query("SET @@local.character_set_results=".$GLOBALS['msq_charset']);
   @mysql_query("SET @@local.character_set_connection=".$GLOBALS['msq_charset']);
}


    return $GLOBALS['ms_connected'];
}


// общее
function msq_exist($tb,$u) { return ms("SELECT COUNT(*) FROM $tb $u","_l",0); }
function msq_add($tb,$ara) { return msq("INSERT INTO $tb (`".implode('`,`',array_keys($ara))."`) VALUES ('".implode("','",array_values($ara))."')"); }
function msq_update($tb,$ara,$u='') { $a=''; foreach($ara as $n=>$m) $a.="`$n`='$m',"; $a=trim($a,','); return msq("UPDATE $tb SET $a $u"); }
function msq_add_update($tb,$ara,$u='') { return msq_exist($tb,$u)?msq_update($tb,$ara,$u):msq_add($tb,$ara); }
function arae($ara){ $p=array(); foreach($ara as $n=>$l) $p[e($n)]=e($l); return $p; }
function njsn($s) { return str_replace(array("\\","'",'"',"\n","\r"),array("\\\\","\\'",'\\"',"\\n",""),$s); }

}
?>