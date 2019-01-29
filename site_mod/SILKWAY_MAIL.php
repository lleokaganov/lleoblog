<?php // Отсылка формы письмом

function SILKWAY_MAIL($e) { if(sizeof($_POST)) {

$s="";
	foreach($_POST as $n=>$l) { $s.="<tr><td><b>".h($n)."</b>:</td><td>".nl2br(h($l))."</td></tr>"; }

$s="<table cellpadding=10 border=0 cellspacing=0>$s</table>";

	$Name=h($_POST['Name']); $Name=($Name!=''?$Name:'anonymouse');
	$Mail=$_POST['Mail'].$_POST['Tele_Mail']; $Mail=(strstr($Mail,'@')?h($Mail):'no_reply@silk-way.ru');


$s="<p>Посетитель сайта ".$GLOBALS["blog_name"]." заполнил заявку:".$s; 

	include_once $GLOBALS['include_sys']."_sendmail.php";
//	sendmail($Name,$Mail, $GLOBALS["admin_name"], 'lleo@aha.ru', 'silk-way.ru - '.$Name, $s);
	sendmail($Name,$Mail, $GLOBALS["admin_name"], $GLOBALS["admin_mail"], $GLOBALS["blog_name"].' - '.$Name, $s);

return ''; // $s;
}
}


?>