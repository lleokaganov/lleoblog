<?php

function PHPEVAL($e) { if(($ur=onlyroot(__FUNCTION__.' '.h($e),1))) return $ur;
	$o=''; eval($e); return $o;
}

?>