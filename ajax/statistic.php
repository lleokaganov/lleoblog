<?php
include "../config.php"; include $include_sys."_autorize.php"; $a=RE("a"); ADH();
$data=RE0("data");

function prn_in_col($pp,$col) { if(!$col) $col=3;

$a=array(); foreach($pp as $p) {
	$p=get_ISi($p,"<small><i>{name}</i></small>");
	$a[]="<nobr><span onclick='kus(".$p['unic'].")'>".$p['imgicourl']."</span></nobr>";
}

// if($col==0) $s=implode(', ',$a); else
if($col==1) return implode('<br>',$a);

$n=sizeof($a); $x=ceil($n/$col); $s="<table cellspacing=10 cellpadding=10 border=0><tr valign=top>";
    for($i=0;$i<$col;$i++) {
	$s.="<td>";
	for($j=0;$j<$x&&$n>0;$j++) $s.=$a[--$n]."<br>";
	$s.="</td>";
    }

return $s."</tr></table>";
}

//====================== кто посетил ===========================================================================
if($a=="ktoposetil") {

    $vsego=intval(ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `url`='$data'","_l"));


    $comm=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `DateID`='".h($data)."'","_l"));

//    $comm=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `url`='$data'","_l"));

$pp=ms("SELECT r.url,r.unic,a.login,a.openid,a.admin,a.realname,a.mail
FROM `dnevnik_posetil` AS r, ".$db_unic." AS a
WHERE r.url='".$data."' AND a.id=r.unic AND a.admin='podzamok'
LIMIT 20000","_a");

otprav("helps('ktoposetil',\"<fieldset><legend>посетители страницы: $vsego комментариев: $comm</legend><div class=r>".njs(prn_in_col($pp,RE0('col')))
."<div id='more_posetil'><div class='ll' onclick=\\\"majax('statistic.php',{a:'ktoposetil_more',data:".$data."})\\\">[ more ]</div></div>"
."</div></fieldset>\");");
}


//===============================================================================================================
if($a=="ktoposetil_more") {
	$pp=ms("
SELECT r.url,r.unic,a.login,a.openid,a.admin,a.realname,a.mail
FROM `dnevnik_posetil` AS r, ".$db_unic." AS a
WHERE r.url='".$data."' AND a.id=r.unic AND a.admin!='podzamok'
AND (a.login != '' OR a.openid !='' OR a.realname != '')
LIMIT 20000","_a");

otprav("zabil('more_posetil',\"".njs(prn_in_col($pp,RE0('col')))."\");");
}



if($admin && $a == "delmusor") { msq_del((RE('type')=='l'?'dnevnik_link':'dnevnik_search'), array('n'=>e(RE('n'))) ); }


// $a==loadstat

$bloksearch='';
$sql=ms("SELECT `search`,`poiskovik`,`count`,`n` FROM `dnevnik_search` WHERE `DateID`='".$data."' ORDER BY `count` DESC","_a",0);
$nstatsearch=sizeof($sql);

foreach($sql as $p) {
        $dlink=hh($p['search']); if(strlen($dlink)>60) $dlink=substr($dlink,0,60-3)."...";
	$bloksearch .= "<br>".$p['count']." <b>'".$dlink."'</b> (".hh($p['poiskovik']).")";
	}

$bloklink='';
$nlimit=max(50,$nstatsearch);
$sql=ms("SELECT `link`,`count`,`n` FROM `dnevnik_link` WHERE `DateID`='".$data."' ORDER BY `count` DESC LIMIT ".$nlimit,"_a",0);
$nstatlink=sizeof($sql);

foreach($sql as $p) {
        $dlink=hh(maybelink($p['link'])); if(strlen($dlink)>60) $dlink=substr($dlink,0,60-3)."...";
	$bloklink .= "<br>".$p['count']." <a href='".$p['link']."'>".$dlink."</a>";
}

if($bloklink.$bloksearch!='') $blockblock = "<table style='margin: 5pt; font-size: 10pt;'><tr valign=top align=left>
<td width=50%><center><i>заходы по ссылкам (".$nlimit." из ".$nstatlink."):</i></center>".$bloklink."</td>
<td width=50%><center><i>запросы с поисковиков:</i></center>".$bloksearch."</td>
</tr></table>";
else $blockblock = "пока отсутствует";

otprav("helps('statistic',\"<fieldset><legend>статистика посещений страницы</legend>".njs($blockblock)."</fieldset>\");");

//==================================================================================================================================

/*
function maybelink($e) {
        $s=urldecode($e); if($s!=$e) $s=h($s);
        if( ( strlen($s)/((int)substr_count($s,'Р')+0.1) ) < 11 ) return(iconv("utf-8",$GLOBALS['wwwcharset']."//IGNORE",$s));
        else return(trim($s));
}
*/

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>