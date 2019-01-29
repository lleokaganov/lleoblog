<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй

// logi("rssc.log","\n".date("Y-m-d H:i:s")." - ".$_SERVER["REMOTE_ADDR"]." ".$_SERVER["HTTP_USER_AGENT"]);

ob_clean(); header("Content-Type: text/xml; charset=".$wwwcharset);

$skip=intval($_GET['skip']);


// взять соответствия Date - num, чтоб по одной всякий раз не лазить
$e=ms("SELECT `num`,`Date` FROM `dnevnik_zapisi`","_a",$ttl_longsite);
	$d=array(); foreach($e as $l) $d[$l['num']]=getlink($l['Date']); unset($e);

/*
$pp=ms("SELECT `id`,`Text`,`Name`,`Parent`,`Time`,`DateID`
FROM `dnevnik_comm` ".($podzamok?'':"WHERE `scr`='0' OR `unic`='$unic'")." ORDER BY `Time` DESC LIMIT ".$skip.",".$RSSC_skip."",'_a',0);
*/

$whe=array();
if(!$podzamok) $whe[]="(c.`scr`='0' OR c.`unic`='$unic')";
if(isset($_GET['unic'])) $whe[]="c.`unic`='".e($_GET['unic'])."'";
if(isset($_GET['name'])) $whe[]="c.`Name`='".e($_GET['name'])."'";

//dier(implode(' AND ',$whe));

$pp=ms("SELECT c.`id`,c.`Text`,c.`Name`,c.`Parent`,c.`Time`,c.`DateID`
FROM `dnevnik_comm` AS c LEFT JOIN `dnevnik_zapisi` AS z ON c.`DateID`=z.`num`
".WHERE(implode(' AND ',$whe))." ORDER BY c.`Time` DESC LIMIT ".$skip.",".$RSSC_skip."",'_a',0);

/*
c.`unic`,c.`group`,,c.`whois`,c.`rul`,c.`ans`,
c.`golos_plu`,c.`golos_min`,c.`scr`,c.`DateID`,c.`BRO`,
u.`capchakarma`,u.`mail`,u.`admin`
FROM `dnevnik_comm` AS c LEFT JOIN $db_unic AS u ON c.`unic`=u.`id` WHERE `DateID`='".e($num)."'
ORDER BY `Time`","_a",0);
*/

$s="<?xml version='1.0' encoding='".$wwwcharset."'?>
<rss version='2.0' xmlns:ya='http://blogs.yandex.ru/yarss/'>

<channel>
	<title>".$admin_name.": comments</title>
	<link>".$httphost."</link>
	<generator>LLeoBlog 1.0:comments</generator>
"; //  <lastBuildDate></lastBuildDate>

$s.="	<ya:more>".$httpsite.$mypage."?skip=".($skip+$RSSC_skip)."</ya:more>
	<category>ya:comments</category>
";

foreach($pp as $p) {
	$post=$d[$p['DateID']];
	$link=$post."#".$p['id'];

$s .= "\n<item>
	<guid isPermaLink='true'>".$link."</guid>
	<ya:post>".$post."</ya:post>
".($p['Parent']!=0?"        <ya:parent>".$post."#".$p['Parent']."</ya:parent>":'')."
	<pubDate>".date("r", $p['Time'])."</pubDate>
	<author>".h(strtr($p['Name'],"\r",""))."</author>
	<link>".$link."</link>
	<title></title>
	<description>".h(strtr($p['Text'],"\r",""))."</description>
</item>\n";

}

$s .= "\n</channel>\n\n</rss>\n";

ob_end_clean();
die($s);
// die($s1.date("r",$lastupdate).$s);

?>