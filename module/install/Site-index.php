<?php

function installmod_init() { if(
!msq_table('site') || sizeof(ms("SHOW INDEX FROM `site`","_a",0))>1) return false;

//	$pp=ms("SHOW INDEX FROM `dnevnik_zapisi`","_a",0);
//	dier($pp);

	return "Изменить первичный индекс `site`"; // .msq_index1('dnevnik_zapisi','num');

}

function installmod_do() {
//	msq_del_index('site','name');
	msq("ALTER TABLE `site` DROP PRIMARY KEY");
	msq("ALTER TABLE `site` ADD PRIMARY KEY(`acn`,`name`)");
	return "Изменена таблица `site`, изменен индекс";
}

?>