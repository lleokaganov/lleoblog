<?php

// Эта функция возвращает 0, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.

$GLOBALS['DBSTRING']="FROM ".$GLOBALS['db_unic']
." WHERE `openid`!='' AND `openid` NOT LIKE '%".e('://')."%'";

function installmod_init() { if(!msq_table('unic')) return false;
		$a=ms("SELECT COUNT(*) ".$GLOBALS['DBSTRING'],"_l",0);
		return ($a?"DB `unic` UPGRADE ($a)":false);
}

// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать, allwork - общее количество (измерено ранее), $o - то, что кидать на экран.
function installmod_do() { global $o,$skip,$allwork,$delknopka; $starttime=time();

	$nLim=10;
	while((time()-$starttime)<5 && $skip<$allwork) {
		$pp=ms("SELECT * ".$GLOBALS['DBSTRING']." LIMIT $skip,$nLim","_a",0);

	foreach($pp as $p) { $i=$p['openid'];
		$i=strtolower($i);
		$i=trim($i,"/");
		if(!strstr($i,':')) $i='http://'.$i;
		//$o.="#".nl2br(h(print_r($pp,1)));
		$o.="<br><a href='".h($i)."'>".h($i)."</a>";
		//	usleep(1000);

		if($i!=$p['openid']) {
			msq_update($GLOBALS['db_unic'],array('openid'=>e($i)),"WHERE `id`=".$p['id'],"_l",0);
			$o.='<font color=green> upgrade</font> '.h($p['openid']);
		}
	}

		$skip+=$nLim;
	}
	$o.=" ".$skip;
	if($skip<$allwork) return $skip;
	$delknopka=0;
	return 0;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
function installmod_allwork() { return ms("SELECT COUNT(*) ".$GLOBALS['DBSTRING'],"_l",0); }

?>