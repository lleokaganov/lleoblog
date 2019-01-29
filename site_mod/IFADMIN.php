<?php /* только для админа

Если страницу открыл админ - выдается текст перед разделителем |, если не админ - то текст после |.
 
{_IFADMIN: Не забудь свой пароль: 1Кe2fHD | Это закрытая запись, как вы здесь оказались? _}
{_IFADMIN: | &lt;script&gt;href.location='http://lleo.aha.ru/na'&lt;/script&gt; _}

*/

function IFADMIN($e) {
	list($a,$b)=explode('|',$e,2);
	return (
($GLOBALS['acn']?$GLOBALS['ADM']:$GLOBALS['admin'])
? c($a) : c($b)
);
}

?>