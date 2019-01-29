<?php /* Если на этой странице впервые

Если посетитель еще не был на этой странице - выдается текст перед разделителем |, если уже бывал - текст после |.

{_is_pervonah: Вы здесь впервые? | Хватит сюда ходить! _}

*/

function is_pervonah($e) {
	// return "`".$GLOBALS['page_pervonah']."`";
	list($a,$b)=(strstr($e,'|')?explode('|',$e,2):array($e,''));
	return ( empty($GLOBALS['page_pervonah']) ? c($a) : c($b));
}

?>