<?php

function installmod_init() { if(msq_pole('dnevnik_zapisi','Body')!="text") return false;
	return "Снять ограничение 64кб для заметки";
}

function installmod_do() {
	msq("ALTER TABLE `dnevnik_zapisi` CHANGE `Body` `Body` MEDIUMTEXT CHARACTER SET cp1251 COLLATE cp1251_general_ci NOT NULL COMMENT 'Текст заметки (до 16М)'");
	return "Изменена база `dnevnik_zapisi`: теперь нет ограничения в 64кб текста";
}

?>