<?php /* только для админа

Если страницу открыл админ - выдается текст перед разделителем |, если не админ - то текст после |.

{_is_admin: Не забудь свой пароль: 1Кe2fHD | Это закрытая запись, как вы здесь оказались? _}
{_is_admin: | &lt;script&gt;href.location='http://lleo.aha.ru/na'&lt;/script&gt; _}

*/

function is_admin($e) {
        list($a,$b)=(strstr($e,'|')?explode('|',$e,2):array($e,''));
        return ($GLOBALS['admin']||$GLOBALS['ADM'] ? c($a) : c($b) );
}

?>