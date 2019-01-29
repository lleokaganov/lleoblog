<?php /* установка тэга META

{<b></b>_META: author Leonid Kaganov _<b></b>} - такой модуль формирует в заголовке страницы новый 
тэг мета с указанными аргументами: &lt;meta name="author" content="Leonid Kaganov"&gt; Разделителем аргументов считается первый пробел, поскольку имя тэга, насколько я помню, содержать пробелов не должно.

*/

function META($e) { list($n,$v)=explode(" ",$e,2);
//	$GLOBALS['_META'][]='<meta name="'.h(c($n)).'" content="'.h(c($v)).'">';
	$e='meta name="'.h(c($n)).'" content="'.h(c($v)).'"';
	$GLOBALS['_HEADD'][$e]=$e;
	return '';
}
?>