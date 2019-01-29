<?php /* модуль SIGNAL

Сообщение о заходе на страницу в лог или mail (в будущем - и по СМС).

'mode'=>'mail', // sms alert

*/

function SIGNAL($e) { global $IP,$BRO,$REF,$httpsite,$blogdir,$admin_mail,$admin_mobile,$unic,$podzamok,$acc,$acn,$mypage,$linksearch,$include_sys;

// $IP='77.105.137.60';
// $BRO='Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.22 (KHTML, like Gecko)';
// $REF="http://www.google.ru/url?sa=t&rct=j&q=%D1%84%D1%81%D0%B1&source=web&cd=7&ved=0CEoQFjAG&url=http%3A%2F%2Flleo.me%2Fdnevnik%2F2013%2F08%2F03.html&ei=1EL_UaeRHfH74QTo44GgAg&usg=AFQjCNHinvpvHRujRKvT5-m-X3dNy0wWwA";
// include_once $GLOBALS['include_sys']."_refferer.php"; $linksearch=poiskovik($REF);

$conf=array_merge(array(
'mode'=>'mail', // sms log alert
'to_addr'=>'{admin_mail}', // file tel mail
'to'=>'admin', // file tel mail
'from'=>'blog',
'log'=>'',
'from_addr'=>'{admin_mail}',
'subj'=>"SIGNAL {podzamok}{infom} {link}",

'redirect'=>'', // blocked.htm?redirect_from={url}',
'redirect_error'=>301, // Заблокировано

/*
        300 Multiple Choices (Множество выборов).
        301 Moved Permanently (Перемещено окончательно).
        302 Found (Найдено).
        303 See Other (Смотреть другое).
        304 Not Modified (Не изменялось).
        305 Use Proxy (Использовать прокси).
        306 (зарезервировано).
        307 Temporary Redirect (Временное перенаправление).
*/

'template_log'=>"{date} {podzamok}{linksearch}
info: {info}
link: {httpsite}{link}
IP: {IP} {IPX}
BRO: {BRO}
REF: {REF}
unic: {unic} acn: {acn} acc: {acc}

",

'template'=>"{podzamok}{linksearch}
<font color=red>{info}</font>
{date} <a href='{httpsite}{link}'>{httpsite}{link}</a>
<font color=magenta>{IP} {IPX}</font> <font color=grey>{BRO}</font>
Refferer: <a href='{REF}'>{REF}</a>
unic: {unic} acn: {acn} acc: {acc}"
),parse_e_conf($e));


$m=array_merge($conf,array(
'httpsite'=>$httpsite,
'blogdir'=>$blogdir,
'admin_mail'=>$admin_mail,
'admin_mobile'=>$admin_mobile,
'unic'=>$unic,
'podzamok'=>($podzamok?"<font color=red> !!!PODZAMOK!!! </font>":''),
'acc'=>$acc,
'acn'=>$acn,
'link'=>h($mypage),
'date'=>date('Y-m-d H:i:s'),
'BRO'=>h($BRO),
'IP'=>$IP,
// 'REMOTE_ADDR'=>$_SERVER['REMOTE_ADDR'],
'IPX'=>$_SERVER['HTTP_X_FORWARDED_FOR'],
'REF'=>h($REF),
// 'METHOD'=>$_SERVER['REQUEST_METHOD'],
// 'PROTOCOL'=>$_SERVER['SERVER_PROTOCOL'],
'linksearch'=>($linksearch==false?'':$linksearch[1].': <font color=green>'.$linksearch[0].'</font>'),
'COOKIE'=>h(print_r($_COOKIE,1))
));

//=================================================================
$r=array(); $info=array();

if(c($conf['body'])!='*') { // если * - отслеживать всех

    if(($s=c($conf['body']))=='') $s=ms("SELECT `text` FROM `site` WHERE `name`='SIGNALS.txt' AND `acn`='0'","_l");
    if($s=='') return ''; // '<font color=red>Не заведена переменная SIGNALS.txt</font>';

    $s=str_replace("\r",'',$s); $s=str_replace("\t",' ',$s);
    foreach(explode("\n",$s) as $a) { if(c0($a)==''||substr($a,0,1)=='#') continue;
	list($n,$l)=explode((strstr($a,'|')?'|':' '),$a,2); $n=c0($n); $l=c0($l);
	//-----------
	if(preg_match("/^[\d\.\/]+$/s",$n)) { if(is_ip($IP,$n)) $info[]=h("IP: ".$n." ".$l); } // IP
	elseif(preg_match("/^https*\:\/\//s",$n)) { if(strstr($l,$REF)) $info[]=h("REFFERER: ".$n." ".$l); } // REF
	else { if(strstr($BRO,$l)) $info[]=h("BRO: ".$n." ".$l); } // BRO
	//-----------
    }
//=================================================================
    if(!sizeof($info)) return '';
}

$m['info']=implode("<br>",$info);
$m['infom']=$info[0].(sizeof($info)>1?" (".sizeof($info).")":'');

if($conf['log']!='') {
    $s=str_replace('\n',"\n",$conf['template_log']); $s=mper($s,$m);
    logi(rpath($conf['log']),$s);
}

$s=str_replace('\n',"\n",$conf['template']); $s=nl2br(mper($s,$m));
$subj=strip_tags(mper($conf['subj'],$m));

if($conf['mode']=='mail') { $s=nl2br($s); $s=str_replace("  ","&nbsp;&nbsp;",$s);
    include_once $GLOBALS['include_sys']."_sendmail.php";
	sendmail(
	    mper($conf['from'],$m),
	    mper($conf['from_addr'],$m),
	    mper($conf['to'],$m),
	    mper($conf['to_addr'],$m)
	,$subj,$s);
    return signal_final($conf,'');
}

if($conf['mode']=='alert') return signal_final($conf,strip_tags($subj)."<hr>".$s);

return "<font color=red>SIGNAL: unknown mode `".h($conf['mode'])."`</font>";
}

//=====================================================================

function signal_final($conf,$s) {
    if($conf['redirect']!='') return str_replace('{url}',urlencode($GLOBALS['mypage']),$conf['redirect']);
    //redirect(str_replace('{url}',urlencode($GLOBALS['mypage']),$conf['redirect']),$conf['redirect_error']);
    return $s; // return str_replace('{url}',$GLOBALS['mypage'],$conf['url']).'#'.$conf['error'];
}

function is_ip($ipmy,$a) { if($ipmy==$a) return 1;
    list($ip,$m)=strstr($a,'/')?explode('/',$a):array($a,0);
    $ip=trim($ip,' .'); $n=substr_count($ip,'.'); if(!$m) $m=($n+1)*8;
    $n=3-$n; if($n<4) { while($n-- >0) $ip.='.0'; }
    $min=base_convert(str_pad(substr(str_pad(base_convert(ip2ipn($ip),10,2),32,'0',STR_PAD_LEFT),0,$m),32,'0'),2,10);
    $max=$min+base_convert(str_repeat('1',32-$m),2,10);
    $ipn=ip2ipn($ipmy);
    return ($ipn>=$min && $ipn<=$max?1:0);
}

//======================================================================

function SIGMAY($ipmy) { global $SIGBASA;
    if(!isset($SIGBASA)) { $SIGBASA=array();
	    $s=ms("SELECT `text` FROM `site` WHERE `name`='SIGNALS.txt' AND `acn`='0'","_l"); if(empty($s)) return '';
	    $s=str_replace("\r",'',$s); $s=str_replace("\t",' ',$s);
	    foreach(explode("\n",$s) as $a) { if(c0($a)==''||substr($a,0,1)=='#') continue;
		list($aa,$l)=explode((strstr($a,'|')?'|':' '),$a,2); $aa=c0($aa); $l=c0($l);
		if(preg_match("/^[\d\.\/]+$/s",$aa)) {
			list($ip,$m)=strstr($aa,'/')?explode('/',$aa):array($aa,0);
			$ip=trim($ip,' .'); $n=substr_count($ip,'.'); if(!$m) $m=($n+1)*8;
			$n=3-$n; if($n<4) { while($n-- >0) $ip.='.0'; }
			$min=base_convert(str_pad(substr(str_pad(base_convert(ip2ipn($ip),10,2),32,'0',STR_PAD_LEFT),0,$m),32,'0'),2,10);
			$max=$min+base_convert(str_repeat('1',32-$m),2,10);
			$SIGBASA[]=array($min,$max,$l/*,ipn2ip($min),ipn2ip($max)*/);
		}
	    }
    }
//     dier($SIGBASA);
    $i=(strstr($ipmy,'.')?ip2ipn($ipmy):$ipmy);
    foreach($SIGBASA as $x) { if($i>=$x[0] && $i<=$x[1]) return $x[2]; }
    return '';
}
?>