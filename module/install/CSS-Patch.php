<?php

function installmod_init() { return "Patch CSS"; }

function installmod_do() { global $o; $o='';

if(!strstr($GLOBALS['httphost'],'://lleo.me/dnevnik/')) {

    $file2=$GLOBALS['filehost']."template/blog.html";
    $s=fileget($file2);

    if(strstr($s,"'lleo.me'")) {
	$g="\n@@@@@@@@@@@@@@@@\n((((((((((((((((   ";
	$gg="   )))))))))))))))))\n@@@@@@@@@@@@@@@\n";

	$s=preg_replace("/<\!\-\-LiveInternet counter\-\->(.*?)<\!\-\-\/LiveInternet\-\->/si",'',$s);
	$s=preg_replace("/<\!\-\-LiveInternet logo\-\->(.*?)<\!\-\-\/LiveInternet\-\->/si",'',$s);
	$s=preg_replace("/<\!\-\-Openstat\-\->(.*?)<\!\-\-\/Openstat\-\->/si",'',$s);
	$s=preg_replace("/<script>(.*?i,s,o,g,r,a,m.*?'lleo\.me'.*?)<\/script>/si",'',$s);
	$s=preg_replace("/\{(_SIGNAL\:.*?_)\}/si",'',$s);
	$s=preg_replace("/\{(_BTCINFO\:.*?_)\}/si",'',$s);

	$s=preg_replace("/\{(_NO\:\s*YANDEXCOUNT.*?_)\}/si",'',$s);
	$s=preg_replace("/\{(_NO\:\s*ADDTHIS.*?_)\}/si",'',$s);
	$s=preg_replace("/\{(_NO\:.*?_)\}/si",'',$s);

	$s=preg_replace("/\{(_PHPEVAL\:.*?_)\}/si",'',$s);
	$s=preg_replace("/\{(_REKOMENDA\:.*?_)\}/si",'',$s);

$s=str_replace("<div><a title='Хотите заказать рекламную статью или обзор?<br>Это возможно, но есть определенные ограничения.<br>Все подробности на странице рекламной политики.' href='/reklama'>реклама в блоге</a></div>",'',$s);
$s=str_replace("<div><a title='Перейти на страницу в техническом блоге /blog (вы-то находитесь в блоге /dnevnik), где рассказывается, как можно установить себе мой движок блога (он открытый).' href=/blog/lleoblog>движок сайта</a></div>","",$s);

	$s=preg_replace("/(<div title=\"Грузоперевозки \(Москва и межгород\),<br>мой друг.*?<\/div>)/si",'',$s);
	$s=preg_replace("/(<div title=\"Профессиональные измерительные приборы<br>мой друг.*?)<\/div>/si",'',$s);
	$s=preg_replace("/(<div title='Фирма установки пластиковых окон моих друзей.*?)<\/div>/si",'',$s);
	$s=preg_replace("/\n<div><a href='https\:\/\/www\.instagram\.com\/lleokaganov.*?<\/div>/si",'',$s);
	$s=preg_replace("/\n<div style='margin-left:15px;margin-bottom:10px;' class='l r' alt='Сообщите администрации портала о нарушениях.+?<\/div>/si",'',$s);
	$s=preg_replace("/\noff_socialmedia=<p>Комментарии к этой заметке на моем сервере отключены, надеюсь на понимание[^\n]+/si",'',$s);
	$s=preg_replace("/url\('\/dnevnik\/design\/userpick\/[^\.]+.jpg'\)/si","url('".$GLOBALS['www_design']."userpick.jpg')",$s);

$s=str_replace("<div><a href='http://onlime.ru'><img border=0 src='https://lleo.me/dnevnik/2015/03/onlimeru.gif' title='Мой любимый домашний провайдер, через него я хожу в интернет'></a></div>","",$s);

$s=str_replace("<div class=r>Bitcoin: \$</div>","",$s);
$s=str_replace("<div class=br>ощущается как: \$</div>","",$s);
$s=str_replace("<div>Как читать мой сайт <a href='http://lleo.me/dnevnik/free'>если что</a></div>","",$s);
$s=str_replace("{_SCRIPT_ADD: {httphost}js/kuku.js _}","",$s);
$s=str_replace("\nsocialmedia=lj:lleo","",$s);

$s=str_replace("Дневник Леонида Каганова<br>","Дневник<br>",$s);

	$s=preg_replace("/<div[^>]*>\s*<\/div>/si",'',$s);
	$s=preg_replace("/\n{3,}/si","\n\n",$s);

$s=str_replace("sys.css? ","sys.css?rand=2 ",$s);

    $f3=$file2.".rename_".date("Y-m-d_H-i-s").".old";
    rename($file2,$f3);
    if(!is_file($f3)) idie('Error save: '.$f3);

    fileput($file2,$s);
    if(fileget($file2)!=$s) idie('Error save: '.$file2);

    $o.="<div>".$file2." - patched</div>
<div>OLD File blog.html renamed to: ".$f3."</div>";


    }
}

// а теперь собственно CSS

// $css=fileget($GLOBALS['filehost']."css/sys.css");
// if(empty($css)) return $o="ERROR: Empty css/sys.css";

// if(!preg_match("/url\([\'\"](.*?)\/design\//si",$css,$m)) return $o="ERROR: NO MATCH url('/design/...') in css/sys.css: ".nl2br(h($css));

// return $o="OK! NO ERROR: Empty css/sys.css m=".$m[1];


$r=glob($GLOBALS['filehost']."css/*.css");


foreach($r as $file) {
    $s=fileget($file); $s0=$s;

    if(preg_match_all("/(url\([\'\"]\/)(.*?)(design\/[^\'\"]+[\'\"]\))/si",$s,$m,PREG_SET_ORDER) && $m[2]!=$GLOBALS['blogdir']) {

    $o.="<br><b>".$file."</b>";

	foreach($m as $l) {
	    $to=$l[1].$GLOBALS['blogdir'].$l[3];
	    if($l[0] == $to) continue;

	    $o.="<br><dd>".h($l[0])." --&gt; ".h($to);
	    $s=str_replace($l[0],$to,$s);
	}

	$s=preg_replace("/\s+\n/s","\n",$s);

	if($s==$s0) continue;

	fileput($file,$s);
	if(fileget($file)!==$s) $o="<br><font color=red>ERROR! Can't change file ".h($file)." ! Check permissions!";
    }
}

	return $o;
}

?>