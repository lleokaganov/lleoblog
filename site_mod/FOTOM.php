<?php // Отображение фотки

function FOTOM($e) {
	if(!isset($GLOBALS['article']['Date'])&&isset($GLOBALS['article']['num'])) {
	    $GLOBALS['article']['Date']=ms("SELECT `Date` FROM `dnevnik_zapisi` WHERE `num`='".intval($GLOBALS['article']['num'])."'".ANDC(),"_l");
	}
// idie("num:".$GLOBALS['article']['num']." Date: ".$GLOBALS['article']['Date']);
// idie("@@@@@".$e."###(".nl2br(print_r($GLOBALS['article'],1)).")##");

	list($y,$m,)=explode('/',$GLOBALS['article']['Date'],3);
	if(!preg_match("/\.(jpg|jpeg|gif|png)$/si",$e)) $e.='.jpg';
	if(!strstr($e,'/')) $e=$GLOBALS['wwwhost'].$y.'/'.$m.'/'.$e;
	return "<center><img src='".h($e)."' border=1></center>";
}

?>