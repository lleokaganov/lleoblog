<?php

// Эта функция возвращает false, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.
function installmod_init() {
// if(!$GLOBALS['aharu']) return false; // только для отладчика
return "Провести тест!"; }

// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать, allwork - общее количество (измерено ранее), $o - то, что кидать на экран.
function installmod_do() { global $o,$skip,$allwork,$delknopka; $starttime=time();

$i=array('http://www.','https://www.','http://','https://');
$v=explode(' ','realname login password mail mailw tel telw img site birth opt');

	while((time()-$starttime)<10 && $skip<$allwork) {
		$p=ms("SELECT `openid` FROM ".$GLOBALS['db_unic']." WHERE `openid`!='' LIMIT ".($skip++).",1","_l",0);
		$l=str_ireplace($i,'',$p);

		$r=ms("SELECT * FROM ".$GLOBALS['db_unic']." WHERE `openid` IN ('".$i[0].$l."','".$i[1].$l."','"
.$i[2].$l."','".$i[3].$l."')","_a",0);

		if(sizeof($r)>1) {
		    // найти самую позднюю запись
		    $ch=0; $rl=array('id'=>0); foreach($r as $u) { if($u['id']>$rl['id']) $rl=$u; }
		    $o.="<br>".$rl['id'].' '.$rl['openid'];

	    // обработать
	    foreach($r as $u) { if($u['id']==$rl['id']) continue; // саму не трогать
		    $o.="<br> ---- &gt; ".$u['id'].' '.$u['openid'];

		    foreach($v as $a) { if($rl[$a]=='' && $u[$a]!='') { $rl[$a]=$u[$a]; $ch=1;
$o.="<br> -------------- &gt; (".$a."=".$u[$a].")";
} } // восстановить старые
		    $c='admin'; if($u[$c]=='podzamok' && $rl[$c]!=$u[$c]) { $rl[$c]=$u[$c]; $ch=1;
$o.="<br> -------------- &gt; (".$c.")";
 } // восстановить подзамок
		    $c='capchakarma'; if($u[$c]>3 && $rl[$c]!=$u[$c]) { $rl[$c]=$u[$c]; $ch=1;
$o.="<br> -------------- &gt; (".$c."=".$u[$c].")";
 } // восстановить капчу
		    if($ch) {} // записать
		    // стереть id
	    }
		}

//		usleep(100000);
//		$skip+=57;

/*
admin: enum('user','podzamok') NOT NULL
capcha: enum('yes','no') NOT NULL default 'no'
capchakarma: tinyint(3) unsigned NOT NULL default '0'



id: int(10) unsigned NOT NULL auto_increment
realname: varchar(64) NOT NULL
openid: varchar(128) NOT NULL
login: varchar(32) NOT NULL
password: varchar(32) NOT NULL
mail: varchar(64) NOT NULL
mailw: varchar(64) NOT NULL
tel: varchar(16) NOT NULL
telw: varchar(16) NOT NULL
img: varchar(180) NOT NULL
mail_comment: enum('1','0') NOT NULL default '1'
site: varchar(128) NOT NULL
birth: date NOT NULL
admin: enum('user','podzamok') NOT NULL
ipn: int(10) unsigned NOT NULL
time_reg: int(11) NOT NULL default '0'
timelast: timestamp NOT NULL default 'CURRENT_TIMESTAMP'
capcha: enum('yes','no') NOT NULL default 'no'
capchakarma: tinyint(3) unsigned NOT NULL default '0'
opt: text NOT NULL
*/

	}

	$o.="<hr>итого: ".$skip;
	if($skip<$allwork) return $skip;
	$delknopka=1;
	return 0;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
function installmod_allwork() { return ms("SELECT COUNT(*) FROM ".$GLOBALS['db_unic']." WHERE `openid`!=''","_l",0); }

?>