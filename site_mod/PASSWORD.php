<?php /* информация скрыта паролем

После того, как пользователь введет пароль в предложенной форме (пароль - слово в первой строке), он увидит скрытую информацию.

{_PASSWORD: kreotif123
скрытый текст
_}

Или в новом формате:

{_PASSWORD:
dostup=podzamok
password=kreotif123

скрытый текст
_}

{_PASSWORD:
dostup=admin
password=kreotif123

скрытый текст
_}


*/

function PASSWORD($e) {
$conf=array_merge(array(
'password'=>false,
'dostup'=>false // доступ: 'podzamok' - подзамки, 'admin' - только админ
),parse_e_conf($e));

$pass=$conf['password'];
$e=$conf['body'];
if($pass===false) list($pass,$e)=explode("\n",$e,2);

    if(
	$GLOBALS['podzamok'] && $conf['dostup']=='podzamok' // для подзамоков
	or $GLOBALS['ADM'] && $conf['dostup']=='admin' // для админа
	or isset($_POST['password'])&&$_POST['password']==c($pass) // или если введен верный пароль
    ) return c($e);
	if(isset($_POST['password'])) sleep(5);
	return "<center><table border=1 cellspacing=0 cellpadding=40><tr><td align=center>
<form method=post action=".$GLOBALS['mypage'].">пароль для этой страницы:
<br><input type=text size=20 name=password>&nbsp;<input type=submit value='далее'></form>
</td></tr></table></center>";
}

?>