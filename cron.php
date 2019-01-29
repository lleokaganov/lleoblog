<?php // запуск процессов по крону

if(!isset($admin_name)) {
	$cronprint=true;
	require("config.php");

$cronrez0='<p>вставьте любым доступным способом запуск этого скрипта по крону (crontab -e) и учтите, что консольный php-cli
далеко не всегда работает как php web, поэтому я, во избежание глюков, передергиваю cron.php через web:
<br><font color=green>*/5 * * * * /usr/bin/fetch -o /dev/null '.$httphost.'cron.php >/dev/null 2>&1</font>
<br>или так:
<br><font color=green>*/5 * * * * wget -O /dev/null '.$httphost.'cron.php >/dev/null 2>&1</font>
<hr><i>результат выполнения cron.php:</i><p>';
}

// ====== проверяем доступность сайтов ========
//$cronrez=dostupen_li("f5.ru","http://kaganov.f5.ru/");
//$cronrez=dostupen_li("razgovor.org","http://www.razgovor.org/special/");

// ====== Подчистим старые антиботовые картинки ========

include_once $include_sys."_antibot.php";
$cronrez = "<br>".antibot_del();

// ====== установим флаг крона ========
file_put_contents($GLOBALS['cronfile'],$cronrez); chmod($GLOBALS['cronfile'],0666);

if($cronprint /*and !$admin_upgrade*/) die("<html><body>".$cronrez0.$cronrez."</body></html>");

//===========================================================================================
//===========================================================================================
//===========================================================================================
//===========================================================================================

function dostupen_li($name,$url) { $flag=$GLOBALS['host_log'].$name.".flag"; $s='';
$ch=curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_COOKIE,'');
curl_setopt($ch,CURLOPT_USERAGENT,'ROBOT from http://lleo.me/dnevnik/ - test if '.$name.' exist every 10 min');
	curl_setopt($ch,CURLINFO_HEADER_OUT,true);
	curl_setopt($ch,CURLOPT_HEADER,1); // get the header
//	curl_setopt($ch,CURLOPT_NOBODY,1); // and *only* get the header
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); // get the response as a string from curl_exec(), rather than echoing it
	curl_setopt($ch,CURLOPT_FRESH_CONNECT,1); // don't use a cached version of the url
curl_setopt($ch,CURLOPT_TIMEOUT,3); //Задает масимальное время выполнения операции в секундах.

if(!($ans=curl_exec($ch))) { $s.="<h1>error - not found!</h1>"; file_put_contents($flag,prichinar(1,$name)); chmod($flag,0666); }
elseif( strstr($ans,'.php</b> on line') ) { $s.="<h1>error - PHP error!</h1>"; file_put_contents($flag,prichinar(2,$name)); chmod($flag,0666); }
elseif( strstr($ans,'mysql_query(): ') ) { $s.="<h1>error - MySQL error!</h1>"; file_put_contents($flag,prichinar(3,$name)); chmod($flag,0666); }
elseif( strstr($ans,'gninx') ) { $s.="<h1>error - gnix error!</h1>"; file_put_contents($flag,prichinar(4,$name)); chmod($flag,0666); }
else unlink($flag);

$s.="<p>
<br>Средняя скорость закачки: ".curl_getinfo($ch,CURLINFO_SPEED_DOWNLOAD)."
<br>Полное время ".curl_getinfo($ch,CURLINFO_TOTAL_TIME)." сек
<br><pre>$ans</pre>
";

curl_close($ch);
}

function prichinar($l,$name) { $i=1; return "<select>
<option value=".$i.($i++ == $l ?' selected':'').">сайт снова не отвечает
<option value=".$i.($i++ == $l ?' selected':'').">сыпятся ошибки PHP-кода
<option value=".$i.($i++ == $l ?' selected':'').">упала MySQL-база
<option value=".$i.($i++ == $l ?' selected':'').">грохнулся сервер gninx
<option value=".$i.($i++ == $l ?' selected':'').">сайт дико тормозит
<option value=".$i.($i++ == $l ?' selected':'').">на сайте вирус
<option value=".$i.($i++ == $l ?' selected':'').">выдает пустую страницу
<option value=".$i.($i++ == $l ?' selected':'').">текст заслонен пиктограммами
<option value=".$i.($i++ == $l ?' selected':'').">слетел шрифт, кракозябры
<option value=".$i.($i++ == $l ?' selected':'').">сбились CSS, нечитаемо мелко
<option value=".$i.($i++ == $l ?' selected':'').">другая беда на ".$name."
</select>";
}

?>