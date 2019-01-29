<?php

function installmod_init() { if(
!msq_table('dnevnik_posetil')||
0==($f=ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `unic`=0 OR `url`=0 OR `date`=0","_l",0))
) return false;
	return "Удалить битые посещения: $f";
}

function installmod_do() {
	msq("DELETE FROM `dnevnik_posetil` WHERE `unic`=0 OR `url`=0 OR `date`=0");
	return "Удалено";
}

?>