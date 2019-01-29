<?php

/*
        $s="DELETE FROM $tb WHERE $a $u";
// if(msq_pole($table,$pole)===false) msq("ALTER TABLE `".$table."` ADD `".$pole."` ".$s
msq_pole($tb,$pole) // проверить, существует ли такое поле в таблице $tb
 // проверить, существует ли такая таблица
msq_index($tb,$index) // проверить, существует ли такой индекс
// изменить поле в таблице	function msq_change_pole($table,$pole,$s)
// добавить поле таблицы	function msq_add_pole($table,$pole,$s)
// удалить поле из таблицы	function msq_del_pole($table,$pole)
// добавить ИНДЕКС в таблицу	function msq_add_index($table,$pole,$s)
// удалить ИНДЕКС из таблицы	function msq_del_index($table,$pole)
// создать таблицу		function msq_add_table($table,$s)
// удалить таблицу		function msq_del_table($table,$text)
*/

// Эта функция возвращает 0, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.
function installmod_init(){
	if(msq_pole('unic','mail_checked')===false) return false;
	return "преобразовать mail_checked (".installmod_allwork().")";
}

/*
function installmod_get(){ global $skip,$lim,$o;
	$pp=ms("SELECT * FROM `dnevnik_zapisi` LIMIT $skip,$lim","_a",0);
	if($pp===false or !sizeof($pp)) { $o.="<p><hr>done"; return 0; }
	return $pp;
}
*/

// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать (изначально 0), allwork - общее количество (измерено ранее), $o - то, что кидать на экран.

function installmod_do() { global $o,$skip,$allwork,$delknopka; $starttime=time();

	$o='';

        while((time()-$starttime)<2 && $skip<$allwork) {
		$pp=ms("SELECT `mail`,`id` FROM `unic` WHERE `mail`!='' AND `mail_checked`='1' AND `mail` NOT LIKE '!%' LIMIT 50","_a",0);

		foreach($pp as $p) { $m=$p['mail'];
			if($m=='' or substr($m,0,1)=='!') continue;
			$o.=" ".$m;
			msq_update('unic',array('mail'=>e('!'.$m),'mail_checked'=>0),"WHERE `id`='".$p['id']."'");
		}
                usleep(100000);
                $skip+=50;
        }

        $o.=" ".$skip;
        if($skip<$allwork) return $skip;

	msq_del_pole('unic','mail_checked');

        $delknopka=1;
        return 0;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
function installmod_allwork() { return ms("SELECT COUNT(*) FROM `unic` WHERE `mail`!='' AND `mail_checked`='1' AND `mail` NOT LIKE '!%'","_l",0); }

?>