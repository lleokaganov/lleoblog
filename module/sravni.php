<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй
// сравни - чи срав, чи ни

	ini_set("display_errors","1");
	ini_set("display_startup_errors","1");
	ini_set('error_reporting', E_ALL); // включить сообщения об ошибках

$_PAGE = array('design'=>file_get_contents($GLOBALS['host_design']."plain.html"),
'header'=>"Сравнить два текста",
'title'=>"Сравнить два текста",

'www_design'=>$www_design,
'admin_name'=>$admin_name,
'httphost'=>$httphost,
'wwwhost'=>$wwwhost,
'wwwcharset'=>$wwwcharset,
'signature'=>$signature
);

include_once $include_sys."_podsveti.php"; // процедура вывода окошка с одной правкой

$txt1=RE('txt1');
$txt2=RE('txt2');

$o="<form action=".$mypage." method=post><center>
<p>текст1:<br><textarea name='txt1' cols=80 class=t>".h($txt1)."</textarea>
<br>текст2:<br><textarea name='txt2' cols=80 class=t>".h($txt2)."</textarea>
<p><input type='submit' name='go' value='сравнить'>
</form>";

if($txt1==''||$txt2=='') die($o."</center>");

$o.= "

<style>
.p1 { color: #3F3F3F; text-decoration: line-through; background: #DFDFDF; } /* вычеркнутый */
.p2 { background: #FFD0C0; } /* вставленный */
</style>

<p>результат:
<table border=1 cellspacing=0 cellpadding=10 width=90%><td>".podsveti(h($txt2),h($txt1))."</td></table>
<p>&nbsp;";

die($o."</center>");

?>