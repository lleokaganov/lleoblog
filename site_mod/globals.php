<?php /* дать значение любой переменной

Через пробел перечисляем данные для поиска в массиве:

{_globals: article num _}

*/

function globals($e) { if($e!='IP') if(($ur=onlyroot(__FUNCTION__.' '.h($e),1))) return $ur;
	$a=explode(" ",$e);$a=explode(" ",$e);
	$l=$GLOBALS; foreach($a as $i) { $i=trim($i); if(!isset($l[$i])) return 'false'; $l=$l[trim($i)]; }
	return h($l);
}

?>