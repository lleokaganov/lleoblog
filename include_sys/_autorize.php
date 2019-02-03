<?php

$fico=array(
    "apple.com"=>'e_ico-apple',
    "binoniq.net"=>'e_ico-binoniq',
    "blogspot.com"=>'e_ico-blogspot',
    "facebook.com"=>'e_ico-facebook',
    "google.com"=>'e_ico-google',
    "google.ru"=>'e_ico-google',
    "instagram.com"=>'e_ico-instagram',
    "livejournal.com"=>'e_ico-livejournal',
    "ljrossia.org"=>'e_ico-ljrossia',
    "lleo.me"=>'e_ico-lleo',
    "lleo.me/dnevnik"=>'e_ico-lleo',
    "erft"=>'e_ico-moikrug',
    "erfe"=>'e_ico-myopenid',
    "twitter.com"=>'e_ico-twitter',
    "vk.com"=>'e_ico-vk',
    "wikipedia.org"=>'e_ico-wikipedia',
    "ya.ru"=>'e_ico-ya',
    "yandex.ru"=>'e_ico-yandex',
    "youtube.com"=>'e_ico-youtube',
    "mail.ru"=>'e_ico-mailru'
); // die(print_r($GLOBALS['fico'],1));


# $HTTPS_REDIRECT=1;
if(!isset($HTTPS)) $HTTPS=(isset($_SERVER['HTTPS']) && 'off'!=$_SERVER['HTTPS'] || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https'==$_SERVER['HTTP_X_FORWARDED_PROTO']?'https':'http');

if(get_magic_quotes_gpc()) { // бл€дь пиздец как заебал этот умный php на хостингах с неотключаемыми настройками // ini_set(Тmagic_quotes_gpcТ, СoffТ);
    function stripslashes_deep($s) { return is_array($s)?array_map('stripslashes_deep',$s):stripslashes($s); }
    $_POST=array_map('stripslashes_deep',$_POST);
    $_GET=array_map('stripslashes_deep',$_GET);
    $_COOKIE=array_map('stripslashes_deep',$_COOKIE);
    $_REQUEST=array_map('stripslashes_deep',$_REQUEST);
}

if(isset($_GET['qnginx'])) {
    if(strstr($_GET['qnginx'],'?')) {
	list(,$_SERVER['QUERY_STRING'])=explode('?',$_SERVER['QUERY_STRING'],2);
	list(,$a)=explode('?',$_GET["qnginx"],2); if(strstr($a,'=')) list($a,$b)=explode('=',$a,2); else $b=''; if($b===NULL) $b=''; if($a!='') $_GET[$a]=$_REQUEST[$a]=$b;
    } else $_SERVER['QUERY_STRING']='';
    unset($_GET["qnginx"]); unset($_REQUEST["qnginx"]);
}

$uopt_a=array(
        'ope'=>array('0','1 0'), // опечатки
        's'=>array('1','1 0'), // звук
        'n'=>array('0','1 0'), // навигаци€
        'i'=>array('1','1 0'), // картинки
        'er'=>array('0','1 0'), // ошибки
        'mat'=>array('0','1 0'), // замен€ть мат
        'ani'=>array('1','1 0'), // замен€ть мат
	'ttcard'=>array('0','1 0'), // TeddyId: спрашивать разрение на изменени€ в личной карточке
	'ttmailarh'=>array('1','1 0'), // TeddyId: спрашивать разрешение на чтение архивов личной переписки
	'ttmailnew'=>array('1','1 0'), // TeddyId: уведомл€ть о новом личном сообщении
	'ttcom'=>array('0','1 0'), // TeddyId: уведомл€ть о любых ответах на ваш комментарий
	'ttcom1'=>array('0','1 0') // TeddyId: уведомл€ть об ответе автора блога на ваш комментарий

); foreach($uopt_a as $n=>$l) if(isset(${'uopt_'.$n})) $uopt_a[$n][0]=${'uopt_'.$n};

if(empty($MYHOST)) list(,$MYHOST)=explode('://',$httpsite,2);
if($_SERVER["HTTP_HOST"]==$MYHOST) $acc=$acc2=''; else { list($acc,$acc2,)=explode('.',$_SERVER["HTTP_HOST"],3); }

$dopa=trim(str_replace('/','_',$blogdir),"/_ ");

$uc=$GLOBALS['cookiepre'].str_replace('/','_',$blogdir);
$ux_name=$GLOBALS['cookiepre'].'ux_'.$dopa;

//========================================================================================
//Ќекоторые ебанутые сборки PHP не имеют элементарных функций, мне придетс€ их эмулировать
// “акже надо будет сделать эмул€цию curl и iconv.

if(!function_exists('iconv')) include $include_sys."iconv.php";
if(!function_exists('mb_basename')) {function mb_basename($file) { return end(explode('/',$file)); } }
if(!function_exists('file_put_contents')) { function file_put_contents($url,$s) { $f=fopen($url,"w"); fputs($f,$s); fclose($f); } }
// ≈ЅјЌ”“№—я!!!!!!
if(!function_exists('str_ireplace')){ function str_ireplace($a,$b,$s){ $t=chr(1); $h=strtolower($s); $n=strtolower($a);
 while(($pos=strpos($h,$n))!==FALSE){ $s=substr_replace($s,$t,$pos,strlen($a)); $h=substr_replace($h,$t,$pos,strlen($a)); }
 return str_replace($t,$b,$s);
}}

// некоторые настройки серверов бывают ебануты:
if(strstr($_SERVER['HTTP_HOST'],':')) list($_SERVER["HTTP_HOST"],)=explode(':',$_SERVER["HTTP_HOST"],2);


// “акже надо прописать пермиссионс

function filechmod($f,$p=''){ if(!$GLOBALS['fchmod']) return; if($p=='') $p=issor('fchmod',0644); echmod($f,$p); }
function dirchmod($d,$p=''){ if(!$GLOBALS['dchmod']) return; if($p=='') $p=issor('dchmod',0755); echmod($d,$p); }
function fileget($f) {
    $f=str_replace($GLOBALS['httpsite'].$GLOBALS['wwwhost'],$GLOBALS['filehost'],$f);
    return (stristr($f,'https://')?file_get_contents_https($f):file_get_contents($f));
}

function file_get_contents_https($url,$i='') { $ch=curl_init();
    curl_setopt_array($ch,array(CURLOPT_URL => $url,CURLOPT_SSL_VERIFYPEER=>0,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_RETURNTRANSFER=>1,CURLOPT_TIMEOUT => 10));
    if($i!='') curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$i);
    if(!$o=curl_exec($ch)) return false; // "ERROR CURL: ".curl_error($ch);
    curl_close($ch);
    return $e;
}

function urlget_name($f) { return $GLOBALS['hosttmp']."GET-".rpath(parse_url($f,PHP_URL_HOST).'.flag'); }
function urlget($f,$time=0) { if(!$time) $time=($GLOBALS['admin']?10:60);
	if(!is_dir($GLOBALS['hosttmp'])) return file_get_contents($f);
	$l=urlget_name($f);
	if(is_file($l)) {
		if(time()-filemtime($l)<$time) {
				logi('urlget.txt',"\n$l - false:".time()."/".filemtime($l));
				return false; } // если меньше 5 секунд
		unlink($l); // иначе сбросить его и начать повторное
	}
	touch($l);
	$s=file_get_contents($f);
	unlink($l);
	return $s;
}

function issor($n,$i=false) { if(gettype($n)!='array') $n=array($n);
	foreach($n as $l) { $l=(strstr($l,'|')?explode('|',$l):array($l));
		$e=$GLOBALS; foreach($l as $c) { if(!isset($e[$c])) { $e=false; break; } $e=$e[$c]; }
		if($e!==false) return $e;
	} return $i;
}

function explode_first($c,$s){ if(!strstr($s,$c)) return $s; list($s,)=explode($c,$s,2); return $s; }
function explode_last($c,$s){ return substr(strrchr($s,$c),strlen($c)); }

function fileput($f,$s) { $o=file_put_contents($f,$s); filechmod($f); return $o; }
function dirput($d) { $o=mkdir($d); dirchmod($d); return $o; }
function testdir($s) {
    $a=explode('/',rtrim($s,'/')); $s='';
    $ah=explode('/',rtrim($GLOBALS['filehost'],'/')); $sh='';
    for($i=0;$i<sizeof($a);$i++) { $s.='/'.$a[$i]; $sh.='/'.$ah[$i]; if($s!=$sh && !is_dir($s)) dirput($s); }
}
function getras($s){ $r=explode('.',basename($s)); return (sizeof($r)==1?'':strtolower(array_pop($r))); }
function rpath($l) { // $p=array_filter(explode(DIRECTORY_SEPARATOR,$l),'strlen');
  $l=str_replace("\\",'/',$l); $a=array();
  foreach(explode('/',$l) as $x){ if((''==$x&&!empty($a))||'.'==$x) continue; if('..'==$x) array_pop($a); else $a[]=$x; }
  return implode('/',$a);
}

//========================================================================================
$ajax=$jaajax=(isset($_GET['lajax']) || RE('zi') || strstr($_SERVER['REQUEST_URI'],'/ajax/')?1:0);

if($ajax) { error_reporting(E_ALL & ~E_NOTICE);

    if(!function_exists('SCRIPTS')) {
	$GLOBALS['_SCRIPT']=array();
	function SCRIPTS($s,$l=0) { if(!$l) $GLOBALS['_SCRIPT'][]=$s; else $GLOBALS['_SCRIPT'][$s]=$l; }
    }

    header('Content-type: text/html; charset='.$GLOBALS['wwwcharset']);
    header("Access-Control-Allow-Origin: *");

    if(isset($_GET['lajax'])) $MOUTPUT="<html><body>
<div id='erra'>{PART}</div>
<script>
var erra=document.getElementById('erra').innerHTML;if(erra && erra!='')alert('ERRA:\\n\\n'+erra);
var s=\"var cleany=function(){clean('".$_GET['lajax']."_form'); clean('".$_GET['lajax']."_ifr')};ajaxoff();{PART}\";
try{parent.eval(s);}catch(e){

var o=e.stack.replace(/eval\\:(\\d+)\\:(\\d+)/gi,function(t,t1,t2){ var c=s.split('\\n')[t1-1]; return t+'\\n\\n\\nERROR: '+c.substring(t2-1)+'\\n\\n'; });

alert('Lajax error:'+e.name+': '+e.message+'\\n\\n'+o+'\\n\\n'+s);

}
</script><!--ScR--></body></html>";


    elseif(isset($_GET['mjax'])) $MOUTPUT="<html><head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=".$GLOBALS['wwwcharset']."\" />
<script>
var page_onstart=[];
var mnogouser=1;
var IMBLOAD_MYID='".$_GET['mjax']."';
var MYHOST='".$GLOBALS['MYHOST']."';
var wwwhost='".$GLOBALS['wwwhost']."';
var www_design=wwwhost+'design/';
var www_js=wwwhost+'js/';
var www_css=wwwhost+'css/';
var www_ajax=wwwhost+'ajax/';
var xdomain='".$GLOBALS['HTTPS']."://".$GLOBALS['xdomain'].".'+MYHOST;
var wwwcharset='".$GLOBALS['wwwcharset']."';
var hashpage='".get_hashpage()."';
</script>
<script type='text/javascript' language='JavaScript' src='".$GLOBALS['wwwhost']."js/main-mini.js?".filemtime($GLOBALS['filehost'].'js/main-mini.js')."'></script>
<script type='text/javascript' language='JavaScript' src='".$GLOBALS['wwwhost']."js/transportm.js?".filemtime($GLOBALS['filehost'].'js/transportm.js')."'></script>
<link href='".$GLOBALS['wwwhost']."css/sys.css?".filemtime($GLOBALS['filehost'].'css/sys.css')."' rel='stylesheet' type='text/css' charset='windows-1251' />
<!--ScR--></head>
<body onload=\"\" style='margin:0;padding:0;text-align:left;border:none;background:#F0F0EA;'>
<div id='erra'>{PART}</div>
<script>
var erra=document.getElementById('erra').innerHTML;if(erra && erra!='')alert('ERRA:\\n\\n'+erra);
{PART}</script></body></html>";

    else $MOUTPUT="/"."**{PART}**"."/{PART}";

    list($p1,)=explode('{PART}',$MOUTPUT,2);
    echo $p1;
}

function otprav($s,$noclean=0) { if(!empty($GLOBALS['msqe'])) echo "\n".$GLOBALS['msqe'];
    $s=str_replace(array('{PART}','**/'),array('{–•–£–ХPART}','&#10017;&#10017;/'),$s); // ******************
    if(isset($_GET['lajax'])) $s=($noclean?'':"cleany();").str_replace("</","<\"+\"/",njsk($s)); // защита от </script>
    elseif(isset($_GET['mjax'])) $s="var unic='".$GLOBALS['unic']."',acn=".verf_acn().";".$s;


    $M=$GLOBALS['MOUTPUT'];
    if(isset($M)) {
	if(!empty($GLOBALS['_SCRIPT'])) { $J=implode("\n",$GLOBALS['_SCRIPT']);
	    $M = (strstr($M,"<!--ScR-->") ? str_replace("<!--ScR-->","\n<script>".$J."</script>",$M) : $M."\n".$J);
	}

    list(,$p2,$p3)=explode('{PART}',$M,3); $s=$p2.$s.$p3;
    }
    die($s);
}

$aharu=issor('aharu'); // сайт разработчика
$lju=getlj(); if($lju!==false) setcoo("lju",base64_encode($lju));

// разберемс€ с IP
$IP=$_SERVER['REMOTE_ADDR']; $IPN=ip2ipn($IP);
if(isset($claudflare_IP) && isset($_SERVER['HTTP_CF_CONNECTING_IP'])) { // $claudflare_IP='162.158.0.0-162.159.255.255';
    $e=explode(' ',$claudflare_IP); foreach($e as $i) {
	    list($a,$b)=explode('/',$i);
	    if($IPN>=ip2ipn($a) && $IPN<=ip2ipn($b)) { $IP=$_SERVER['HTTP_CF_CONNECTING_IP']; $IPN=ip2ipn($IP); break; }
    }

	if($IP!=$_SERVER['HTTP_CF_CONNECTING_IP']) {
	    logi('CF-IP-log.txt',h("IP: ".$IP)."\n");
	    // die("BOT ERROR: REMOTE_ADDR ($IP) != claudflare_IP");
	    $IP=$_SERVER['HTTP_CF_CONNECTING_IP']; $IPN=ip2ipn($IP);
	}
}
// $IP=isset($_SERVER["HTTP_X_FORWARDED_FOR"])?explode_first(',',$_SERVER["HTTP_X_FORWARDED_FOR"]):$_SERVER["REMOTE_ADDR"];

$BRO=@hh($_SERVER["HTTP_USER_AGENT"]);
$REF=hh(issor('_SERVER|HTTP_REFERER',''));
$MYPAGE=str_replace(array('<','>',"\'","\""),array('%3C','%3E','%27','%22'),$_SERVER["REQUEST_URI"]); list($mypage)=explode('?',$MYPAGE.'?',2);
$HMYPAGE=$HTTPS.'://'.$_SERVER["HTTP_HOST"].$MYPAGE;
$hmypage=$HTTPS.'://'.$_SERVER["HTTP_HOST"].$mypage;

$admin=$podzamok=0;

include $include_sys."_msq.php"; // все процедуры работы с MySQL
$months = explode(" ", " €нварь февраль март апрель май июнь июль август сент€брь окт€брь но€брь декабрь");
$months_rod = explode(" ", " €нвар€ феврал€ марта апрел€ ма€ июн€ июл€ августа сент€бр€ окт€бр€ но€бр€ декабр€");
$jog_kuki='';

// ===== ј¬“ќ–»«ј÷»я =====
if(!isset($autorizatio)) { // не работать с авторизацией при а€ксе или €вном запрете (модуль restore_unic.php)

if(empty($admin_unics)) admin1(); // свежеустановленный движок
else {
    if($mnogouser!==1) { // стара€ система авторизации
	    $up=issor(array('_REQUEST|up','_COOKIE|'.$uc),''); // вз€ть куку авторизации
	    if($up=='candidat') { if(!$jaajax) set_unic(); } // был кандидатом, зашел второй раз? получи свой номер!
	    else { // ошибка парол€ или нет такого номера в базе - назначить кандидатом
			$unic=u2unic($up);
			if( !$unic || !upcheck($up) ) set_unic_candidat();
			elseif(getis_global($unic)===false) remake_unic($unic); // если мы убили запись, но он снова пришел - восстановим
	    }
    } else { // нова€ система авторизации
	    $ux=issor(array('_REQUEST|ux','_COOKIE|'.$ux_name),''); // вз€ть куку авторизации

	    if(empty($ux) // если не была установлена кука
		|| ( 0==($unic=u2unic($ux)) ) // или если unic=0
		|| !uxcheck($ux) // или не прошел проверку
	    ) {
		$ux='c';
		$uname='@logout';
	    } else {
		if(getis_global($unic)===false) remake_unic($unic); // если мы убили запись, но он снова пришел - восстановим
		else $uname='@error';
	    }
    }
}

}


// ============================= ƒјЋ№Ў≈ “ќЋ№ ќ ‘”Ќ ÷»» ==========================
// ==============================================================================
// ==============================================================================
// ==============================================================================
// ==============================================================================
// ==============================================================================
// ==============================================================================
// ==============================================================================
function remake_unic($unic) {
    if(msq_add($GLOBALS['db_unic'],array('id'=>$unic,'ipn'=>$GLOBALS['IPN'],'time_reg'=>time()))===false
    || getis_global($unic)===false) { trevoga("DB ADD FALSE!!!!"); idie('Pipec dvijku'); }
}
// работа с куками старой системы
function u2unic($u) { return intval(substr($u,0,strpos($u,'-'))); }
function upcheck($up) { if(!strstr($up,'-')) return false; list($u,$p)=explode('-',$up,2); return ($p==md5($u.$GLOBALS['newhash_user'])?true:false); }
function upset($unic) { return $unic."-".md5($unic.$GLOBALS['newhash_user']); }
// работа с куками новой системы (x-домена)
function uxcheck($ux) {	if(!strstr($ux,'-')) return false; list($u,$p)=explode('-',$ux,2); return ($p==sha1($u.$GLOBALS['IP'].$GLOBALS['BRO'].$GLOBALS['newhash_user'])?true:false); }
function uxset($unc) { if(!$unc) return 0; global $IP,$BRO,$newhash_user; return $unc."-".sha1($unc.$IP.$BRO.$newhash_user); }
function upx_check($upx) { if(!strstr($upx,'-')) return false; list($u,$p)=explode('-',$upx,2); return ($p==sha1($u.$GLOBALS['newhash_user'])?true:false); }
function upx_set($unc) { return $unc."-".sha1($unc.$GLOBALS['newhash_user']); }
// login.php:secret_page
function get_hashlink($Date,$acc) { return sha1($Date.$acc."secrethash".$GLOBALS['hashinput']); }
function test_hashlink($Date,$acc,$hash) { return $hash==get_hashlink($Date,$acc)?1:0; }
// хэши пол€
function iphash(){ global $hashinput,$IP,$BRO; return sha1($hashinput.$IP.$BRO); }
function if_iphash($h){ if($h!=iphash()) idie("IPhash error!"); }

// хэши запрета хака по пр€мой переадресации браузера без ведома хоз€ина (без ключа в странице)
function mk_hashpage($h){ global $IPN,$hashinput; return substr(sha1($h." ".$hashinput." ".$IPN),0,16);} // $BRO
function get_hashpage(){ $h=time(); // rand(0,1000000);
return $h.'-'.mk_hashpage($h); }
function test_hashpage($l){ list($h,$k)=explode('-',$l,2);
    if(1*$h < (time()-60*60*4)) return false; // за последние 4 часа
    return (mk_hashpage($h)==$k);
}

function llog($s) { global $aharu,$IP,$BRO,$MYPAGE; if(!$aharu) return; logi('autoriza.txt',"\n".h($s." ".$IP." | ".$BRO." | ".$MYPAGE)); }
function trevoga($s) { global $aharu,$IP,$BRO,$MYPAGE; if(!$aharu) return; logi('TREVOGA.txt',"\n".h($s." ".$IP." | ".$BRO." | ".$MYPAGE)); }

function getlj() { global $REF; // ќпределение ∆∆-истов
if(isset($_COOKIE['lju'])&&$_COOKIE['lju']!='null'&&$_COOKIE['lju']!='undefined') return preg_replace("/[^0-9a-z\_\-]/si",'',base64_decode($_COOKIE['lju']));
if(strstr($REF,'/friends') && (preg_match("/\Ahttp\:\/\/(.+?)\.livejournal\.com\/friends/",$REF,$m) || preg_match("/\Ahttp\:\/\/users\.livejournal\.com\/(.+?)\/friends/",$REF,$m)))
return preg_replace("/[^0-9a-z\_\-]/si","",$m[1]);
return false;
}

function set_unic_candidat() { global $up,$unic,$uc,$podzamok,$imgicourl,$IS; $up='candidat'; $unic=0; $IS=array('loginlevel'=>0,'mailconfirm'=>0,'openid'=>'','login'=>'','unic'=>0); $podzamok=0; $imgicourl=$up; setcoo($uc,$up); }

function set_unic($m=1) { global $IS,$uc,$IPN,$unic,$up,$podzamok,$imgicourl; $unic=0;
if(msq_add($GLOBALS['db_unic'],array('ipn'=>$IPN,'time_reg'=>time()))===false) { echo "MySQL ERROR: ".$GLOBALS['msqe']; trevoga("DB ADD FALSE!!!!"); return 0; }
	$unic=msq_id(); if(!$unic) { trevoga("msq_id():".$unic); die('unic=0 '.$GLOBALS['msqe']); }
	if($m) { $up=upset($unic); $IS=array('loginlevel'=>1,'mailconfirm'=>0,'openid'=>'','login'=>'','unic'=>$unic); $podzamok=0; $imgicourl=$unic; setcoo($uc,$up); }
	return $unic;
}

function WHERE($s='',$z='') { // какие заметки доступны?
	$r=($z=='novis'?array():array("`visible`='1'"));
	    if(gettype($s)=='array') { foreach($s as $l) if(trim($l)!='') $r[]=$l; }
	    elseif($s!='') $r[]=$s;

	$acn=verf_acn();
	$a=(empty($acn)?
	($GLOBALS['admin']?"":($GLOBALS['podzamok']?"`Access` IN ('all','podzamok')":"`Access`='all'"))
	:"`acn`='".$acn."'".($GLOBALS['ADM']?"":" AND `Access`='all'"));
	if(!empty($a)) $r[]=$a;
	if(!sizeof($r)) return '';
	return "WHERE ".implode(' AND ',$r);
}

function getis_global($unic) { global $IS,$admin_unics,$admin,$podzamok,$imgicourl,$admin_unics;
	if(($IS=getis($unic))!==false) { $GLOBALS['unic']=$unic;
	    if(strstr($admin_unics,',')) { if(in_array($unic,explode(',',$admin_unics))) admin1(); } else { if($unic==$admin_unics) admin1(); } // если этот unic из списка админов
	    $podzamok=$admin||$IS['admin']=='podzamok'?1:0;
	    $imgicourl=(!empty($IS['imgicourl'])?$IS['imgicourl']:'#'.$unic);
	}
	return $IS;
}

function getis($unic,$tmpl='{realname}') {
	if(($is=ms("SELECT * FROM ".$GLOBALS['db_unic']." WHERE `id`='$unic'","_1",0))!==false) {
		$is=get_ISi($is,$tmpl);
		$is['useropt']=mkuopt($is['opt']);
	}
	return $is;
}


function get_ISi($is,$tmpl='{realname}') {
	if($GLOBALS['HTTPS']=='https') { $g=substr($GLOBALS['httphost'],5); $is['img']=str_ireplace('http'.$g,'https'.$g,$is['img']); } // патчим дл€ HTTPS

	$is=array_merge($is,get_ISim($is));
	$is['zamok']=zamok($is['admin']);
	$is['icon']='';

	if(empty($is['user'])) {
		if(!isset($is['unic'])) $is['unic']=$is['id'];
		if($tmpl=='') $is['imgicourl']='Anonymous#'.$is['unic'];
		elseif($tmpl=='#') $is['imgicourl']='#'.$is['unic'];
		elseif($tmpl=='login') $is['imgicourl']="<input title='Login!' value='anonymous' onclick=\"ifhelpc('".$GLOBALS['httphost']."login','logz','Login')\" type='button'>";
		else {
				if(!empty($is['realname']) and $is['realname']!='anonymouse') { $name=$is['realname']; $tmpl='<i>{realname}</i>'; }
				elseif(!empty($is['mail'])) list($name,)=explode('@',$is['mail'],2);
				// elseif(!empty($is['lju'])) $name=$is['lju'];
				else $name=$is['unic'];
			$is['imgicourl']=str_replace(
				array('{name}','{id}','{realname}'),
				array(h($name),h($is['unic']),h(isset($is['realname'])?$is['realname']:'')),$tmpl
			);
		}
	} else {
	    if(isset($is['port'])) {
		$p=explode('://',$is['port']); $p=str_replace(array('www.','m.'),'',$p[1]);
		if(isset($GLOBALS['fico'][$p])) $is['icon']="<i class='".$GLOBALS['fico'][$p]."'></i>";
		else $is['icon']=h($p)."@";
	    }
	    $is['imgicourl']=$is['icon'].h($is['user']);
	}

	return $is;
}

function get_ISim($is) {

$mailconfirm=!empty($is['mail']) && '!'==$is['mail'][0] ? 1:0;
// PHP message: PHP Notice:  Undefined index: teddyid in /var/www/home/dnevnik/include_sys/_autorize.php on line 330

if(isset($is['teddyid'])&&1*$is['teddyid'] // залогинен teddyid
    or !empty($is['openid']) // имеет внешний логин
    or (!empty($is['login']) && !empty($is['password']) && $mailconfirm) // имеет полную авторизацию сайта
) $loginlevel=3;
elseif(!empty($is['realname']) or !empty($is['login']) or $mailconfirm) $loginlevel=2; // какие-то имена прописаны
else $loginlevel=1; // вообще ничего

if(!empty($is['openid'])) { // если опенид
// порт приписки вычислим

// nocache
preg_match("/^(.*?)([^\.]+\.[^\.]+)$/s",preg_replace("/www\./si",'',parse_url($is['openid'],PHP_URL_HOST)),$m); $port=$m[2];

if($port=='facebook.com'||$port=='google.com') {
	if(empty($is['realname'])) return array('loginlevel'=>$loginlevel,'mailconfirm'=>$mailconfirm,'user'=>h($is['openid']),'port'=>'https://'.$port);
	return array('loginlevel'=>$loginlevel,'mailconfirm'=>$mailconfirm,'user'=>h($is['realname']),'port'=>'https://'.$port);
} elseif($port=='rambler.ru') {
	if(!empty($is['realname'])) $realname=$is['realname'];
	elseif(($realname=trim(parse_url($is['openid'],PHP_URL_PATH),'/'))!=''){}
	else $realname=preg_replace("/^(.*)\.[^\.]+\.[^\.]+$/s","$1",preg_replace("/www\./si",'',parse_url($is['openid'],PHP_URL_HOST)));
} elseif($port=='mail.ru') {
	$realname=preg_replace("#.+mail\.ru\/(mail|list)\/(.+)$#si","$2",$is['openid']); // http://openid.mail.ru/mail/electro2005
} else {
	if(($realname=trim(parse_url($is['openid'],PHP_URL_PATH),'/'))!=''){}
	else $realname=preg_replace("/^(.*)\.[^\.]+\.[^\.]+$/s","$1",preg_replace("/www\./si",'',parse_url($is['openid'],PHP_URL_HOST)));
	if(empty($realname) && !empty($is['realname'])) $realname=$is['realname'];
}

// вот тут вс€ ебола с именами:
	if(empty($is['realname'])) {
	    $rn=$realname;
	} elseif(strtolower(str_replace(array(' ','_','-','.'),'',$is['realname']))==strtolower(str_replace(array(' ','_','-','.'),'',$realname))) {
	    $rn=$is['realname'];
	} else {
	    $rn=$is['realname'];
	    if($port!='vk.com') $rn.=' ('.$realname.')';
	}

	return array('loginlevel'=>$loginlevel,'mailconfirm'=>$mailconfirm,'user'=>$rn,'port'=>'https://'.$port);
}

// если бы пуст openid
	if(empty($is['login'])) // дл€ вообще пустых
	return array('loginlevel'=>$loginlevel,'mailconfirm'=>$mailconfirm,'user'=>(!empty($is['realname'])?$is['realname'].' (#'.(empty($is['unic'])?$is['id']:$is['unic']).')':''));

	return array(
		'mailconfirm'=>$mailconfirm,
		'loginlevel'=>$loginlevel, // внутренний логин login+password
		'user'=>(!empty($is['realname'])?$is['realname']:$is['login']),
		'port'=>rtrim($GLOBALS['httpsite'].'/'.$GLOBALS['blogdir'],'/') // порт приписки
	);
}

function njsk($s) {
    $s=preg_replace("/( *[\n\r\t]+|[\n\r\t]+ *)/s",'',$s);
    return str_replace(array("\\",'"',"\n","\r","\t"),array("\\\\",'\\"',"","",""),$s);
}


function nor($s) { return str_replace(array("\n","\r"),'',$s); }
function nort($s) { return str_replace(array("\n","\r","\t"),'',$s); }
function njs($s) { return str_replace(array("\\","'",'"',"\n","\r"),array("\\\\","\\'",'\\"',"",""),$s); }
function njss($s) { return str_replace(array("\\","'",'"'),array("\\\\","\\'",'\\"'),$s); }
function njsn($s) { return str_replace(array("\\","'",'"',"\n","\r"),array("\\\\","\\'",'\\"',"\\n",""),$s); }
function oalert($s) { otprav("alert(\"".njs($s)."\")"); }
function otprav_sb($scr,$s) { otprav("loadScriptBefore('$scr',\"".njs($s)."\");"); }

function dier($a,$t='') { idie($t."<pre>".nl2br(h(print_r($a,1)))."</pre>"); } // отладочна€ процедурка

function idie($s,$h='') { // если это был а€кс - выдать а€кс-окно
	if(!empty($GLOBALS['ajax'])) {
		if($h=='') $h="Fatal error: ".h($GLOBALS['mypage']);
		otprav("helpc('idie',\"<fieldset><legend>".$h."</legend><div style='text-align: left;'><small>".njs($s)."</small></div></fieldset>\")");
	}
	ob_end_clean();
	if(!empty($h)) header($h);
	die("<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=".$GLOBALS['wwwcharset']."\" /><title>Error</title></head><body>".$s."</body></html>");
}

function mystart() {
	Error_Reporting(E_ALL & ~E_NOTICE);
	session_start();
	ob_start("onPostPage");
	header("Content-Type: text/html; charset=".$GLOBALS['wwwcharset']);
}

function onPostPage($buffer) { global $_PAGE,$_SCRIPT,$_SCRIPT_ADD,$_STYLE,$_HEADD;
	// отключить кэширование страниц дл€ админа
	if($GLOBALS['admin']) $GLOBALS['_HEADD']['nocache']='meta http-equiv="Cache-Control" content="no-cache"';

        if(!isset($_PAGE) || $_PAGE['design']=='') return $buffer;
	$s = str_replace("{body}",(isset($_PAGE['body'])?$_PAGE['body']:'').$buffer,$_PAGE["design"]);

	$myscript=''; // прописать скрипты
	foreach($_SCRIPT as $n=>$l) $myscript.="\n\n// --- ".$n." ---\n".$l."\n// --- / ".$n." ---\n";
	if($myscript!='') $myscript="<script language='JavaScript'>\n".c0($myscript)."\n</script>";
	foreach($_SCRIPT_ADD as $l) $myscript .= "\n<script type='text/javascript' language='JavaScript' src='".$l."'></script>"; //.$myscript;

	if(isset($GLOBALS['mytitle'])) if(stristr($s,'<title>')) $s=preg_replace("/<title>.*?<\/title>/si","<TITLE>".$GLOBALS['mytitle']."</TITLE>",$s);
	else $s=str_ireplace("</head>","<TITLE>".c0($GLOBALS['mytitle'])."</TITLE>\n</head>", $s);

	$mystyle=implode("\n",$_STYLE); if($mystyle!='') $mystyle="<style type='text/css'>\n".c0($mystyle)."\n</style>";
	if(sizeof($_HEADD)) $mystyle.="\n<".implode(" />\n<",$_HEADD)." />"; // добавить в head

	$s=str_ireplace("</head>",$myscript."\n".$mystyle."\n</head>",$s); // добавить в head

	unset($_PAGE["design"]); unset($_PAGE["body"]);
        foreach($_PAGE as $k=>$v) $s=str_replace("{".$k."}",$v,$s);
	return $s;
}

function redirect($path='/',$code=301) {
	if(isset($GLOBALS['ajax'])&&$GLOBALS['ajax']) otprav("window.top.location='$path';");
        if(!headers_sent()) {
                header("HTTP/1.1 301 Moved Permanently");
                header("Location: ".$path,TRUE,$code); // навсегда: 301
                exit;
        }
	die("<noscript><meta http-equiv=refresh content=\"0;url=\"".$path."\"></noscript><script>location.replace(\"".$path."\")</script>");
}

function echmod($a,$b) { if(!isset($GLOBALS['disable_chmod'])) chmod($a,$b); }

function logi($f,$s,$a="a+") { $n=$GLOBALS["host_log"].$f; $l=fopen($n,$a); fputs($l,$s); fclose($l); echmod($n,0666); }
function ifdebug($a){ if(isset($GLOBALS['debug'])) logi('debug.log',"\n\n================== ".date("Y-m-d h:i:s")." ---> ".print_r($a,1)."\n\n"); }
function add_get() { if(sizeof($_GET)==0) return ''; $s='?';foreach($_GET as $a=>$b) if($b!='') $s.="$a=".urlencode($b)."&"; return trim($s,'&'); }
function page($l,$c=50) { $m=explode("\n",$l); $i=0; foreach($m as $t) if(strlen($t)<$c) $i++; else $i=$i+1+(floor(strlen($t)/$c)); return($i); }
/*
function uw($s) { return(iconv("utf-8","windows-1251//IGNORE",$s)); }
function uk($s) { return(iconv("utf-8","koi8-r//IGNORE",$s)); }
function wu($s) { $s=strtr($s,chr(152),'@'); return(iconv("windows-1251","utf-8//IGNORE",$s)); } // а знали ли вы, что бл€дский код 152 всЄ нахуй вешает?
function ku($s) { return(iconv("koi8-r","utf-8//IGNORE",$s)); }
function kw($s) { return(iconv("koi8-r","windows-1251//IGNORE",$s)); }
function wk($s) { return(iconv("windows-1251","koi8-r//IGNORE",$s)); }
*/
function uw($s) { return(iconv("utf-8","windows-1251//TRANSLIT",$s)); }
function uk($s) { return(iconv("utf-8","koi8-r//TRANSLIT",$s)); }
function wu($s) { $s=strtr($s,chr(152),'@'); return(iconv("windows-1251","utf-8//TRANSLIT",$s)); } // а знали ли вы, что бл€дский код 152 всЄ нахуй вешает?
function ku($s) { return(iconv("koi8-r","utf-8//TRANSLIT",$s)); }
function kw($s) { return(iconv("koi8-r","windows-1251//TRANSLIT",$s)); }
function wk($s) { return(iconv("windows-1251","koi8-r//TRANSLIT",$s)); }


function selecto($n,$x,$a,$t='name') { if($x==='0'||intval($x)) $x=intval($x);
	$s="<select ".$t."='".$n."'>";
	foreach($a as $l=>$t) $s.="<option value='$l'".($x===$l?' selected':'').">".$t."</option>";
	return $s."</select>"; }

function kawa($p) { $s=$p[1];
        $s=preg_replace("/([A-Za-z\x80-\xFF.,?!])\"/s","$1\xBB",$s); // "$1&raquo;"
        $s=preg_replace("/\"([A-Za-z\x80-\xFF.])/s","\xAB$1",$s); // "&laquo;$1"
        return $s;
}

function ispravkawa($s) {
	$s=preg_replace_callback("/(>[^<]+<)/si","kawa","<>$s<>");
	$s=preg_replace("/([\s>]+)\-([\s<]+)/si","$1".chr(151)."$2","<>$s<>"); // длинное тире
	return str_replace('<>','',$s);
}

function nekawa($s) { return strtr($s,"\xBB"."\xAB".chr(151),'""-'); }

function setcoo($n,$v,$t=0) {
	if(!$GLOBALS['jaajax']) $GLOBALS['_SCRIPT']['setcoo_'.$n]="page_onstart.push(\"c_save('$n','$v',1)\");";
}

function set_cookie($Name,$Value='',$MaxAge=0,$Path='',$Domain='',$Secure=false,$HTTPOnly=false) {
	if(isset($GLOBALS['cookie_method_old'])) { setcookie($Name, $Value, $MaxAge, $Path, $Domain, 0); return; }
	header('Set-Cookie: '.rawurlencode($Name).'='.rawurlencode($Value)
		.(empty($MaxAge) ? '' : '; Max-Age=' . $MaxAge)
		.(empty($Path)   ? '' : '; path=' . $Path)
		.(empty($Domain) ? '' : '; domain=' . $Domain)
		.(!$Secure       ? '' : '; secure')
		.(!$HTTPOnly     ? '' : '; HttpOnly'), false);
}


function file_get($f,$c=true) { if(!$GLOBALS['cache_get'] or !is_dir($GLOBALS['fileget_tmp'])) return file_get_contents($f);
	$n=preg_replace("/[^0-9a-zA-Z_\-\.\~]+/si","#", str_replace("http://","",$f) );
	if(strlen($n)<100) $n=$GLOBALS['fileget_tmp'].$n.".dat"; else $n=$GLOBALS['fileget_tmp'].md5($n).".dat";
	if(file_exists($n)) { if(!$c) return unlink($n); return file_get_contents($n); }
	$x=file_get_contents($f); file_put_contents($n,$x); echmod($n,0666); return $x;
}

function zamok($d) {
        if($d=='all'||$d=='user') return '';
        $z="<i class='e_podzamok'></i>";
        if($d=='podzamok') return $z;
        if($d=='mudak') return '-';
        if($d=='admin') return $z.$z;
	return '';
}

function h($s) { return str_replace(array('&','<','>',"'",'"'/*,'*'*/),array('&amp;','&lt;','&gt;','&#39;','&#34;'/*,'&#10017;'*/),$s);
//htmlspecialchars($s);
}

function hh($s) {
	if(stristr(substr($s,0,10),'javascript')) $s="jаvаsсriрt".substr($s,10);
	return str_replace(
		array('&','"',"'",'<','>',"\t","\r","\n"),
		array('&amp;','&quot;','&#039;','&lt;','&gt;','\t','\r','\n'),$s);
}

// дл€ x, в transportm.js раскодировка
function hl($s) { return str_replace(array(';','|','=',':',"'",'"'),array('@1@','@2@','@3@','@4@','@5@','@6@'),$s); }

function c($s) { return trim($s,"\n\r\t \'\""); }
function c0($s) { return trim($s,"\n\r\t "); }

function ip2ipn($s){ if(($i=ip2long($s))<0) $i+=4294967296; return $i; }
function ipn2ip($i){ return long2ip($i); }

function mail_validate($s) {
	$s=preg_replace("/[^0-9a-z\_\-\.\@]+/si",'',$s);
	return (preg_match("/^[0-9a-z\_\-\.]+\@[0-9a-z\-\.]+\.[0-9a-z]{2,10}$/si", $s) ? $s : false);
}
function site_validate($s) { return (preg_match("/^([a-z]+:\/\/|(www\.))[a-z][a-z0-9_\.\-]*\.[a-z]{2,6}[\[a-zA-Z0-9!#\$\%\&\(\)\*\+,\-\.\/:;=\?\@\_]*$/i",$s)? $s : false); }

function tel_validate($s) {
    $s=str_replace(array('(',')','-',' ',"\n","\r","\t"),'',$s); // убрать скобки и всю хню
    $s=preg_replace("/^8/s",'+7',$s); // в международный формат
    if(substr($s,0,1)!='+') $s='+'.$s; // и плюс добавить если не было
    return (preg_match("/^\+?\d{9,15}$/",$s)? $s : false);
}

function acc_link($acc='',$s='') { return (empty($acc)?$GLOBALS['httpsite']:
str_replace('://','://'.$acc.'.',$GLOBALS['httpsite'])).(empty($s)?$GLOBALS['wwwhost']:$s);
}

function getlink($Date,$acn=false,$acc=false) { global $httphost; if($acn==false) $acn=verf_acn(); if($acc==false) $acc=verf_acc();
    if(empty($acn)) $x=$httphost; else {
	if(empty($acc)) $acc=ms("SELECT `acc` FROM `jur` WHERE `acn`='".e($acn)."' LIMIT 1","_l");
	$x=acc_link($acc);
    }
    if(substr_count('/',$Date)==3) { list($y,$m,$d)=explode("/",substr($Date,0,10),3); if($y*$m*$d) return $x.$Date.".html"; } return $x.$Date;
}

function get_link_($Date) { list($y,$m,$d)=explode("/",substr($Date,0,10),3); if($y*$m*$d) return $GLOBALS['wwwhost'].$Date.".html"; return $GLOBALS['wwwhost'].$Date; }

function getmaketime($d) {
        if(!preg_match("/^(\d\d\d\d)\/(\d\d)\/(\d\d)(.*?)$/s",$d,$m)) return array(0,0);
        $d=$m[1]."-".$m[2]."-".$m[3];
        $t0=strtotime($d);
        if(preg_match("/^[\-_\s]*(\d\d)-(\d\d)/s",$m[4],$t)) $d .= " ".$t[1].":".$t[2];
        $t=strtotime($d);
        while(msq_exist('dnevnik_zapisi',"WHERE `DateDatetime`='$t'")) $t++;
        return array($t0,$t);
}

function get_counter($p) { // $p['view_counter']
	if(isset($GLOBALS['article']["counter"])) return $GLOBALS['article']["counter"];
        $c=intval(ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `url`='".intval($p['num'])."'","_l"));
        if($GLOBALS['old_counter']) $c+=$p["view_counter"];
        $article["counter"]=$c;
	cache_set('count_'.trim($GLOBALS['blogdir'],'/').'_'.intval($p['num']),$c,600); // записать в memcache
        return $c;
}

// работа с объектами tmp в запис€х и комменатри€х
function get_last_tmp() { $s=ms("SELECT `text` FROM `unictemp` WHERE `unic`='".intval($GLOBALS['unic'])."'","_l",0); return ($s===false?'':$s); }
function del_last_tmp() { msq("DELETE FROM `unictemp` WHERE `unic`=".intval($GLOBALS['unic']).""); }
function put_last_tmp($s) { msq_add_update('unictemp',array('unic'=>intval($GLOBALS['unic']),'text'=>e($s)),'unic'); }
// работа с установками пользовател€

function loadset() {
  if(($l=ms("SELECT `text` FROM `site` WHERE `name`='".e('u_'.verf_acc())."'","_l",0))===false
  ) return $GLOBALS['uset']; return array_merge($GLOBALS['uset'],unserialize($l));
}

function updset($r) { return saveset(array_merge(loadset(),$r)); }

function saveset($r) { // сперва удалим переменные по умолчанию
        if(isset($r['X']) && ( $r['X']<10 or $r['X']>1600 )) idie('Ќе па€сничай, выставь ширину человеческую: 10-1600.');
        if(isset($r['x']) && ( $r['x']<5 or $r['x']>500 )) idie('Ќе па€сничай, выставь ширину превью человеческую: 5-500');
        if(isset($r['Q']) && ( $r['Q']<50 or $r['Q']>98 )) idie(' ачество имеет смысл делать в пределах 50-95%');
        if(isset($r['q']) && ( $r['q']<50 or $r['q']>98 )) idie(' ачество имеет смысл делать в пределах 50-95%');
        if(isset($r['dir'])) { $r['dir']=rpath(trim($r['dir'],'/')); testdir($r['dir']); } // создать папки

	if(isset($r['logo'])) $r['logo']=mpers($r['logo'],array(
		'site'=>rtrim(acc_link(verf_acc()),'/'),
		'name'=>(verf_acc()==''?$GLOBALS['admin_name']:$GLOBALS['IS']['realname'])
	));

  foreach($GLOBALS['uset'] as $n=>$l) { if(isset($r[$n]) && $r[$n]===$l) unset($r[$n]); }

  msq_add_update('site',array('text'=>e(serialize($r)),'name'=>e('u_'.verf_acc())),'name');
	return $r;
}
// -------------------

function mk_prevnest($prev,$next) { // ЅЋ»“№ »ƒ»“≈ ¬—≈ Ќј’”…!!! Ќ≈ ѕќЋ”„ј≈“—я ” ћ≈Ќя — ¬јЎ»ћ» …ќЅјЌЌџћ» CSS!!! √ќ–≈“№ »ћ ¬ јƒ
$prev=($prev==''?'&nbsp;':"<font size=1>".$prev."</font>");
$next=($next==''?'&nbsp;':"<font size=1>".$next."</font>");
return "<center><table width=98% cellspacing=0 cellpadding=0><tr valign=top><td width=50%>$prev</td><td width=50% align=right>$next</td></tr></table></center>";
}


// =====================================================================================
$searchan=0;

function search_podsveti_body($a) {
        $a=preg_replace_callback("/>([^<]+)</si","search_p_body",'>'.$a.'<');
        $a=ltrim($a,'>'); $a=rtrim($a,'<');
        return $a;
} function search_p_body($r) {
	return '>'.str_ireplace2_body(RE('search'),"<span class='search'>","</span>",$r[1]).'<';
}

function str_ireplace2_body($search,$rep1,$rep2,$s){ $c=chr(1); $nashlo=array(); $x=strlen($search); if(!$x) return $s;
        $SEARCH=strtolower2_body($search);
        $S=strtolower2_body($s);
        while (($i=strpos($S,$SEARCH))!==false){
                $nashlo[]=substr($s,$i,$x);
                $s=substr_replace($s,$c,$i,$x);
                $S=substr_replace($S,$c,$i,$x);
        } foreach($nashlo as $l) $s=substr_replace($s,"<a name='search_".($GLOBALS['searchan']++)."'></a>".$rep1.$l.$rep2,strpos($s,$c),1);
        return $s;
}

function strtolower2_body($s){
$s=strtr($s,'јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„Ўўџ№ЏЁёя','абвгдеЄжзийклмнопрстуфхцчшщыьъэю€'); // русские в строчные
$s=strtr($s,'авсенкмортху','abcehkmoptxy'); // русские какие похожи - в латинские
$s=strtolower($s); // латинские в строчные
return $s;
}
// =====================================================================================
function ifu($s){ $l=uw($s); return $s==wu($l)?$l:$s; }

function RE($x) { if(!isset($_REQUEST[$x])) return false;
    if(!isset($_REQUEST['zi']) || $GLOBALS['wwwcharset']=='utf-8') return $_REQUEST[$x];
    if(function_exists('html_entity_decode')&&function_exists('html_entity_decode'))
	return html_entity_decode(mb_convert_encoding($_REQUEST[$x],"HTML-ENTITIES","UTF-8"),ENT_NOQUOTES,$GLOBALS['wwwcharset']);
    return iconv("utf-8",$GLOBALS['wwwcharset']."//TRANSLIT",$_REQUEST[$x]);
}

function RE0($s,$n=1) { if(!isset($_REQUEST[$s])) return $n?false:0; return $_REQUEST[$s]=='on'?1:intval($_REQUEST[$s]); }

// запрещенные модули
function onlyroot($s,$i=0) { if(verf_acc()=='') return 0; $s="<font color='red'>".$s." disabled.</font>"; if($i) return $s; idie($s); }

// запретить вызов вне x-домена
function ADX() { if($GLOBALS['mnogouser']==1 && $_SERVER["HTTP_HOST"]!=$GLOBALS['xdomain'].'.'.$GLOBALS['MYHOST']) idie('X-domain only'); }

$GLOBALS['mojaxsalt']='';
function ADZ() { return 1; // уберем пока

 if(!RE('zi')) return 0;
 $r=array(); foreach($_REQUEST as $n=>$l) { if($n!='zi') $r[]=$n; } asort($r); $zig=''; foreach($r as $n) $zig.=$n.',';
    $m=md5($GLOBALS['mojaxsalt'].'|'.$zig);
    if(RE('zi')==$m) return 1;
    otprav("idie(\"ZI ERROR<p>1: ".h($GLOBALS['mojaxsalt'].'|'.$zig.'/'.$m)."<br>2: \"+lastzig);");
}

// проверить хэш пол€
function ADH() {
    if(ADZ()) return;
    if(test_hashpage(RE('hashpage'))) return;
    list($h,$k)=explode('-',RE('hashpage'),2); idie('hashpage only'.($GLOBALS['admin']?"<br>hashpage=`".h(RE('hashpage'))."`"."<br>h____=`".h($h)."`"."<br>k_page=`".h($k)."`"."<p>IP=".$GLOBALS['IP']."<br>BRO=".$GLOBALS['BRO']:''));
}

// добавить к запросу базы поле `acn` i=AND или WHERE если первое
function ANDC($i="AND") { return ($GLOBALS['mnogouser']!=1?'':" ".$i." `acn`='".verf_acn()."'"); }

// проверка админа движка
function AD() { if(!$GLOBALS['admin']) idie("Admin only!<p>".$GLOBALS['mypage'].":".h(RE('a'))); }

// добавка файлов
function accd() { $acc=verf_acc(); return ($acc==''?'':"userdata/".$acc."/"); }

function verf() { global $acn,$acc,$unic,$mnogouser,$ADM,$admin;
    if($mnogouser!=1) { $acc=''; $acn=0; return $ADM=$admin; }
    $a=false;
    if(isset($acn)) $a=$acn; else $a=RE0('acn'); if($a!=false) {
	$l=ms("SELECT `acc` FROM `jur` WHERE `acn`='".e($a)."' AND `unic`='".e($unic)."'","_l");
	if($l!=false) { $acc=$l; $acn=$a; return $ADM=1; }
	$acc=''; return $ADM=$acn=$admin=0; // ты нас обманывал, сука
    }
    if(isset($acc)) $a=$acc; else $a=RE0('acc'); if($a!=false) {
	$l=ms("SELECT `acn` FROM `jur` WHERE `acc`='".e($a)."' AND `unic`='".e($unic)."'","_l");
	if($l!=false) { $acc=$a; $acn=$l; return $ADM=1; }
	$acc=''; return $ADM=$acn=$admin=0; // ты нас обманывал, сука
    }
    $acc=''; return $ADM=$acn=0;
}

function verf_acn() { if(!isset($GLOBALS['acn'])) verf(); return $GLOBALS['acn']; }
function verf_acc() { if(!isset($GLOBALS['acc'])) verf(); return $GLOBALS['acc']; }
function ADMA($i=0) { if(!isset($GLOBALS['ADM'])) verf(); if(!$GLOBALS['ADM']&&!$i) idie('You are not admin!'); return $GLOBALS['ADM']; } // проверка на админа акаунта

function allowfile($file,$w=1) { global $acc,$filehost;
    $accd=accd();

    if($GLOBALS['admin']) return; // админу можно всЄ
    if(stristr($file,'config.php')||stristr($file,'.htaccess')) idie("Security error #1");
    if($acc='') idie("Security error #2");
    if($w) if(!strstr($file,$filehost.$accd) && !strstr($file,$filehost."hidden/user/$accd") )  idie("Security error #3 ".h($file)."<br>".$filehost.$accd);
    else if(!strstr($file,"/template/") && !strstr($file,"/css/") && !strstr($file,"/js/"))  idie("Security error #4 ".h($file));
    return;
}

function LLoad($lang) { global $langbasa;
	$a=file($GLOBALS['filehost'].'/module/lang/'.$lang.'.lang');
	if(!isset($langbasa)) $langbasa=array();
	foreach($a as $l) { if(!strstr($l,"\t")) continue;
		list($c,$s)=explode("\t",$l,2);
		$langbasa[c($c)]=trim($s,"\t\r\n");
	}
}

function LL($n,$ara=false){ global $mylang,$langbasa; if(!isset($langbasa)) LLoad(isset($mylang)?$mylang:'ru');
        if(!isset($langbasa[$n])) return "[LL:ERROR:".$n."]";
	$s=$langbasa[$n]; if($ara===false) return $s;
	if(gettype($ara)!='array') $ara=array($ara);
	foreach($ara as $n=>$l) {
                $s=str_replace('{'.$n.'}',$l,$s);
                if(strstr($s,'{'.$n.'?')) { $k="\\".($l?'1':'2'); $s=preg_replace("/\{".$n."\?([^|]*)\|(.*?)\|\}/s",$k,$s); }
        }
        return $s;
}



function get_comm_button($num,$dopload='',$kn=0) { global $comments_on_page,$podzamok,$comments_pagenum,$idzan,$idzan1;

	if(!isset($idzan)) $idzan=get_idzan($num);
	if(!isset($idzan1)) $idzan1=($idzan?get_idzan1($num):0);
	if(!$idzan) return ''; // комментов нету
 	$pages=($comments_on_page?ceil($idzan1/$comments_on_page)-1:0); // число страниц комментов
	if(!$pages && !$kn) return ''; // если всего 1 и это не кнопка подгрузки - выйти ни с чем

	// нарисовать кнопку (если страница всего 1) или фразу о количестве комментов
	$o=LL(($pages?'comm:nobutton':'comm:button'),array(
       	        'dopload'=>$dopload,'podzamok'=>$podzamok,'idzan'=>$idzan,
               	'majax'=>"onClick=\"majax('comment.php',{a:'loadcomments',dat:$num,page:0})\""
        ));

	// если страниц много - вывести кнопочки
	if($pages) for($i=0;$i<=$pages;$i++) $o .= LL('comm:k',array(
		'u'=>((isset($comments_pagenum)||!$kn) && $i==$comments_pagenum),
                'majax'=>"onClick=\"majax('comment.php',{a:'loadcomments',dat:$num,page:".($i)."})\"",
		'n'=>$i+1	));

	return $o;
}

function get_idzan($num) { return intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `DateID`='$num'"
.($GLOBALS['podzamok']?'':" AND `scr`='0'"),'_l')); }

function get_idzan1($num) { return intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `DateID`='$num' AND `Parent`='0'"
.($GLOBALS['podzamok']?'':" AND `scr`='0'"),'_l')); }


// ======================= zopt ==========================
$zopt_a=array( // дефолтные значени€
        'include'=>array('','s',40),
        'Comment_foto_logo'=>array(chr(169)." ".chr(171)."{name}: ".$httpsite.chr(187),'s',64),
        'Comment_foto_x'=>array('600','s',6),
        'Comment_foto_q'=>array('75','s',6),
        'Comment_media'=>array('all','all no my'),
        'Comment_view'=>array('on','on off rul load timeload'),
        'Comment_write'=>array('on','on off friends-only login-only timeoff login-only-timeoff'),
        'Comment_screen'=>array('open','open screen friends-open'),
        'Comment_tree'=>array('1','0 1'),
        'autoformat'=>array('p','no p pd'),
        'template'=>array('blog','s',32),
        'autokaw'=>array('auto','auto no')
);
foreach($zopt_a as $n=>$l) if(isset(${'zopt_'.$n})) $zopt_a[$n][0]=${'zopt_'.$n};

// ======================= zopt ==========================

$admincolors=array(array('admin','e_ledred'),array('podzamok','e_ledyellow'),array('all','e_ledgreen'));
function ADMINSET($p='') { global $ADM,$article,$www_design,$admincolors;
	if(!$ADM) return ''; if(gettype($p)!='array') $p=$article;
        foreach($admincolors as $l) if($l[0]==$p['Access']) break;
        return "<i alt='".LL('Editor:dostup')."' onclick=\"majax('editor.php',{acn:'".verf_acn()."',a:'ch_dostup',d:this.className.split(' ')[0],num:".$p['num']."})\" class='".$l[1]." ".$p['num']."_adostup'></i>";
}

function mkuopt($s) { $o=unser($s); // сделать из сериализованной строки $s массив ¬—≈’ опций пользовател€, добавив дефолтные
	foreach($GLOBALS['uopt_a'] as $n=>$l) { if(!isset($o[$n])) $o[$n]=$l[0]; }
	return $o;
}

function makuopt($r,$i=0) { // создать массив opt из заданного массива и дефолта
	$opt=array(); foreach($GLOBALS['uopt_a'] as $n=>$l) {
	if(isset($r[$n]) && $r[$n]!='default') $opt[$n]=$r[$n]; elseif($i) $opt[$n]=$l[0];
	} return $opt;
}

function mkzopt($p) { $o=unser($p['opt']); // сделать из $p массив опций и вернуть его
	foreach($GLOBALS['zopt_a'] as $n=>$l) { if(!isset($o[$n])) $o[$n]=$l[0]; }
	return array_merge($p,$o);
}

function cleanopt($r) { foreach($GLOBALS['zopt_a'] as $n=>$l) { if(isset($r[$n]) && ($r[$n]==$l[0] or $l[1]=='s' && $r[$n]=='')) unset($r[$n]); } return $r; } // вычищаем дефолтные

function makeopt($r,$i=0) { // создать массив opt из заданного массива и дефолта
	$opt=array(); foreach($GLOBALS['zopt_a'] as $n=>$l) {
	if(isset($r[$n]) && $r[$n]!='default') $opt[$n]=$r[$n]; elseif($i) $opt[$n]=$l[0];
	} return $opt;
}

function unser($p){ return empty($p)?array():unserialize($p); }
function ser($p){ return sizeof($p)?serialize($p):''; }

function get_sys_tmp($s){
    if(verf_acc()!='') { // поискать сперва в /userdata/vasyapupkin/
	$f=$GLOBALS['filehost'].accd().'template/'.$s; if(is_file($f)) return fileget($f);
    }
    $f=$GLOBALS['file_template']."system/".$s;
    if(is_file($f)) return fileget($f);
    return '<hr><font color=red>Template not found: `'.h($s)."`</font>";
}

function var_confirmed($s){ return (substr($s,0,1)=='!'?array(substr($s,1),1):array($s,0)); }
function get_workmail($p) {
        if($p['mailw']!=''){ list($m,$c)=var_confirmed($p['mailw']); if($c && mail_validate($m)) return $m; }
        if($p['mail']!=''){ list($m,$c)=var_confirmed($p['mail']); if($c && mail_validate($m)) return $m; }
        return false;
}
function imgicourl_text($s) { return h(str_replace('&nbsp;','',strip_tags($s))); }

function mpers($s,$ara) {
	if(!function_exists('mper')) { include_once $GLOBALS['include_sys']."_modules.php"; }
	return mper($s,$ara);
}


function admin1() { //-- если случилась авторизаци€ админа
	$GLOBALS['admin']=1;
	if(!function_exists('set_ttl')) { function set_ttl(){} } // ЅЋяƒ— »… »Ќ—“јЋЋ
	set_ttl(); // и все-таки ttl установить как надо дл€ админа
	if(!$GLOBALS['jaajax']) { // если не а€кс - включить отладочные сообщени€ дл€ админа
		ini_set("display_errors","1"); ini_set("display_startup_errors","1"); ini_set('error_reporting', E_ALL); // включить сообщени€ об ошибках
	}
}

//-------- reset unic ---------

function reset_the_unic($uname,$u=false,$Q=' ',$iden='') { global $newhash_user,$mnogouser,$xdomain,$MYHOST,$blogdir;

	if($Q==' ') $js="doclass('del_onlogon',function(e,s){clean(e)}); salert('Restore: '+uname,1000);";
	else { $js=''; parse_str($Q,$e);
	    if(isset($e['clean']) && isset($e['opt'])) {
		    if( $e['clean'].$e['opt']=='userid'.'teddyid') $js="idd('teddyid_no').style.display='none'; idd('teddyid_yes').style.display='inline'; zabil('teddyid_id',".intval($iden).");";
		    if($e['clean'].$e['opt']=='userid'.'openid') $js="idd('openid_no').style.display='none'; idd('openid_yes').style.display='inline'; idd('openid_id').href=idd('openid_id').innerHTML='".h($iden)."';";
	    }
	}
    if($mnogouser!==1||$u===false) return "var s=\"".njsk("
		uname=\"".njsk($uname)."\"; realname=uname;
		".($u!==false?"unic='".($u?$u:'candidat')."'; up='".($u?$u.'-'.md5($u.$newhash_user):'candidat')."'; c_save(uc,up,1);":"")."
		zabilc('uname',uname); f5_save('uname',uname); zabilc('myunic',uname);
		setTimeout(\"clean('logz')\",500);
	".$js."
	")."\";
try{ if(window.top===window) eval(s); else window.top.eval(s); }
catch(e){
    alert((window.top===window?'main':'window')+' eval error: '+s);
}";

return "
var url='".$GLOBALS['HTTPS']."://".$xdomain.".".$MYHOST."/".$blogdir."ajax/autoriz.php?x=1&upx=".upx_set($u)."&uname=".urlencode($uname)."&QUERY=".urlencode($Q)."';
if(window.top!==window) window.location.href=url;
else ifhelpc(url,'xdomain','xdomain');";
}

//-------- reset unic ---------

function gettags($num=false) { global $article;
    if(!@isset($article['tag'])) {
	if($num==false && @isset($article['num'])) $num=$article['num'];
	$t=ms("SELECT `tag` FROM `dnevnik_tags` WHERE `num`='".e($num)."'".ANDC()." ORDER BY `tag`",'_a',0);
	$r=array(); foreach($t as $l) $r[]=$l['tag'];
	if(!isset($article)) $article=array();
	$article['tag']=$r;
    } return $article['tag'];
}

function teddysha($l='') { return sha1("from=".$GLOBALS['teddyid_nodeid'].";to=".(1).";".$GLOBALS['teddyid_secretkey'].$l); } // код дл€ teddyid.com
function teddyid_confirm($optname,$txt) {
    if(empty($GLOBALS['teddyid_nodeid']) || empty($GLOBALS['IS']['teddyid']) || empty($GLOBALS['IS']['useropt'][$optname])) return;

if(($x=RE('teddyid_response'))) {
    if(RE('teddyid_hash')!=teddysha($x.RE('teddyid_response_date').$GLOBALS['unic'].$optname)) idie('TeddyId hash error');
    if($x!='Y') otprav("salert('No, sorry',2000);"); return;
}
otprav("
teddyidfunc_cardotvet=function(o,d,hash){ repostToIframe('".h(RE('lajax'))."',{teddyid_response:o,teddyid_response_date:d,teddyid_hash:hash}); };
majax('module.php',{mod:'LOGIN',a:'teddyid_request',text:'".h($txt)."',f:'cardotvet',QUERY:'".h($optname)."'});
",1);
}

function teddyid_opovest($id,$text) {
    include_once $GLOBALS['include_sys']."protocol/_protocol_patchs.php"; $a=(array)json_decode1(curlpost('https://www.teddyid.com/authorize.php',array(
	'node_id'=>$GLOBALS['teddyid_nodeid'], 'token'=>teddysha(), 'employee_id'=>$id, 'question'=>wu($text)  )));
    if(isset($a['error']) || (!isset($a['result'])||$a['result']!='ok') || (!isset($a['request_id'])||!intval($a['request_id']))) return 'ERROR: '.h($a['error'])." ".h(print_r($a,1));
    return intval($a['request_id']);
}

function mailbox_send($from=0,$to=4,$text='error') { global $db_mailbox;
    $a=arae(array('unicto'=>$to,'unicfrom'=>$from,'text'=>$text));
    if(!ms("SELECT COUNT(*) FROM ".$db_mailbox." WHERE `unicfrom`='".$a['unicfrom']."' AND `unicto`='".$a['unicto']."' AND `text`='".$a['text']."'","_l",0))
	msq_add($db_mailbox,$a);
}


// записать тэги к заметке $GLOBALS['num'] (вместо старых)
function tags_save($s) { global $msqe,$num; if(!$num) return; // не записывать дл€ нулевой заметки
    $msqe0=$msqe;
    msq("DELETE FROM `dnevnik_tags` WHERE `num`='$num'".ANDC()); // удалить все тэги этой заметки
    $p=explode(',',$s); foreach($p as $l) { $l=c($l); if($l!='') msq_add('dnevnik_tags',array('acn'=>verf_acn(),'num'=>$num,'tag'=>e(h($l)))); }
    if(!stristr($msqe,'Duplicate')) $msqe0.=$msqe; // ошибка дублей - не ошибка
    $msqe=$msqe0;
}
//=====================

// issor('fkey_db','fkey')

//=====================
function zametka_del($num) { $num=intval($num);
    if(!msq("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `num`='$num'".ANDC(),'_l',0)) return false;
    msq("DELETE FROM `dnevnik_zapisi` WHERE `num`='$num'".ANDC()); // удалить запись
    msq("DELETE FROM `dnevnik_comm` WHERE `DateID`='$num'"); // удалить к ней все комментарии
    msq("DELETE FROM `dnevnik_posetil` WHERE `url`='$num'"); // удалить статистику ее посетителей
    msq("DELETE FROM `dnevnik_link` WHERE `DateID`='$num'"); // удалить статистику заходов по ссылкам
    msq("DELETE FROM `dnevnik_search` WHERE `DateID`='$num'"); // удалить статистику заходов с поисковиков
    msq("DELETE FROM `dnevnik_tags` WHERE `num`='$num'".ANDC()); // и тэги к ней
    // а удалить в соцсет€х?
    return true;
}

//=======================================

function zametka_save($ara) { ADMA();
    if(!isset($ara['DateUpdate'])) $ara['DateUpdate']=time();
    if(!isset($ara['acn'])) $ara['acn']=$GLOBALS['acn'];
    $num=@intval($ara['num']);
    if(!empty($ara['opt'])) $ara['opt']=ser($ara['opt']);
    if(!empty($ara['tags'])) $tags=$ara['tags']; else $tags=''; unset($ara['tags']);
    if($num) {
	if(0==intval(ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `num`='".e($num)."' AND `acn`='".e($ara['acn'])."'",'_l',0))) return "Error: not exist #".intval($num);
	msq_update('dnevnik_zapisi',arae($ara),"WHERE `num`='".e($num)."' AND `acn`='".e($ara['acn'])."'");
    } else { //== новую заметку =====
	if(ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date`='".e($ara['Date'])."' AND `acn`='".e($ara['acn'])."'",'_l',0)) return "Error: exist `".h($ara['Date'])."`";
	list($ara['DateDate'],$ara['DateDatetime'])=getmaketime($ara['Date']);
	msq_add('dnevnik_zapisi',arae($ara));
	$num=msq_id();
    }
    if($GLOBALS['msqe']) return "ERROR: mysql";

    if($tags!=='') { // записать тэги к заметке
	msq("DELETE FROM `dnevnik_tags` WHERE `num`='".e($num)."' AND `acn`='".e($ara['acn'])."'"); // удалить все тэги этой заметки
	foreach(explode(',',$tags) as $l) { $l=c($l); if($l!='') msq_add('dnevnik_tags',arae(array('acn'=>$ara['acn'],'num'=>$num,'tag'=>$l))); }
	if(stristr($GLOBALS['msqe'],'Duplicate')) $GLOBALS['msqe']=''; // ошибка дублей - не ошибка
    }
    if($GLOBALS['msqe']) return "ERROR: mysql";
    return $num;
}

//========================================================================================================================================

function maybelink($e,$i=11) {
    $s=urldecode($e); if($s!=$e) $s=h($s);
    if( ( strlen($s)/((int)substr_count($s,'–')+0.1) ) < $i ) return(iconv("utf-8",$GLOBALS['wwwcharset']."//TRANSLIT",$s));
    else return(trim($s));
}

function loadsite($n,$i=0) { return ms("SELECT `text` FROM `site` WHERE `name`='".e($n)."'".ANDC(),"_l",$i); }
function delsite($n) { return msq("DELETE FROM `site` WHERE `name`='".e($n)."'".ANDC()); }
function savesite($n,$v) { $u=msq_add_update('site',array('name'=>e($n),'text'=>e($v),'acn'=>verf_acn()),'name ANDC'); }

?>