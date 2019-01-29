<?php

function KTOPOSETIL($e) { list($a,$c,$b)=explode(',',$e,3); $a=c($a); $b=c($b); $c=c($c);
	if($a=='admin' and !$GLOBALS['admin']) return '';
	if($a=='podzamok' and !$GLOBALS['podzamok']) return '';
	return "<div class=l onclick=\"majax('statistic.php',{a:'ktoposetil',data:'".$GLOBALS['article']['num']."',mode:'$c'})\">$b</div>";
}

?>