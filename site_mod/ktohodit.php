<?php //  то ходит в гости по утрам?

function ktohodit($e) { 

	$num=$GLOBALS['article']['num'];

	if($e=='num') return $num;
	if($e=='count') return ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `url`='$num'","_l",0);

	$p=ms("SELECT `unic` FROM `dnevnik_posetil` WHERE `url`='$num'","_a",0);

	$s=''; foreach($p as $l) $s.=", ".$l['unic'];
	$s=trim($s,' ,');
	return $s;

}

?>