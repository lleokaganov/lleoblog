<?php
// это процедура не аякса, я просто не нашел для него лучшего места
//
// эта штука выдает секретные файлы (работает с модулем {_SECRET_FILE: link _})
//
// файлы секретны тем, что:
// а) по прямой ссылке их открыть невозможно
// (для этого разместите их в папке, где создайте файл .htaccess, куда наколотите строчку ереси типа 'trololololo')
// б) ссылку на такой файл постороннему лицу переслать не получится - она зависит от IP и Браузера

include "../config.php"; include_once $GLOBALS['include_sys']."_files.php"; // операции с файлами

if(isset($_GET['qnginx'])) {
    if(strstr($_GET['qnginx'],'?')) {
	list(,$_SERVER['QUERY_STRING'])=explode('?',$_SERVER['QUERY_STRING'],2);
	list(,$a)=explode('?',$_GET["qnginx"],2); if(strstr($a,'=')) list($a,$b)=explode('=',$a,2); else $b=''; if($b===NULL) $b=''; if($a!='') $_GET[$a]=$_REQUEST[$a]=$b;
    } else $_SERVER['QUERY_STRING']='';
    unset($_GET["qnginx"]); unset($_REQUEST["qnginx"]);
}

function explode_first($c,$s){ if(!strstr($s,$c)) return $s; list($s,)=explode($c,$s,2); return $s; }

function hh($s) {
        if(stristr(substr($s,0,10),'javascript')) $s="jаvаsсriрt".substr($s,10);
        return str_replace(array('&','"',"'",'<','>',"\t","\r","\n"),array('&amp;','&quot;','&#039;','&lt;','&gt;','\t','\r','\n'),$s);
}



$IP=isset($_SERVER["HTTP_X_FORWARDED_FOR"])?explode_first(',',$_SERVER["HTTP_X_FORWARDED_FOR"]):$_SERVER["REMOTE_ADDR"];
$BRO=hh($_SERVER["HTTP_USER_AGENT"]);
$file=$_REQUEST['file'];

if( $_REQUEST['o'] != md5($hashinput.$IP.$BRO.$file) ) die('Error 404: DATA ERROR '
//."<p>o=".$_REQUEST['o']
//."<br>md5=".md5($hashinput.$IP.$BRO.$file)
//."<p>IP=".$IP
//."<p>BRO=".$BRO
//."<p>file=".$file
);

function getras($s){ $r=explode('.',$s); if(sizeof($r)==1) return ''; return strtolower(array_pop($r)); }

Exit_SendFILE(realpath($filehost.$file),false,3);

?>