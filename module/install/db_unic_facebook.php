<?php

// Эта функция возвращает false, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.
function installmod_init() {
// if(!$GLOBALS['aharu']) return false; // только для отладчика
return "Поправить Facebook"; }


function id_to_id($i1,$i2) { if($i1*$i2 == 0 or $i1==$i2) return false;
	$new=min($i1,$i2);
	$old=max($i1,$i2);

$do=array(
    'mailbox'=>"unicfrom",
    'mailbox'=>"unicto",
    'golosovalka'=>"unic",
    'jur'=>"unic",
    'dnevnik_comm'=>"unic",
    'dnevnik_plusiki'=>"unic",
    'dnevnik_posetil'=>"unic",
    'pravki'=>"unic",
    'unictemp'=>"unic",
    'golosovanie_golosa'=>"unic"
); foreach($do as $n=>$l) {
	//    msq("UPDATE `".e($n)."` SET `".e($l)."`='".e($new)."' WHERE `".e($l)."`='".e($old)."'");
}

/*
id: int(10) unsigned NOT NULL auto_increment
realname: varchar(64) NOT NULL
openid: varchar(128) NOT NULL
login: varchar(32) NOT NULL
teddyid: int(11) NOT NULL default '0'
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
time_reg: int(11) NOT NULL default '0'
timelast: timestamp NOT NULL default 'CURRENT_TIMESTAMP'
capcha: enum('yes','no') NOT NULL default 'no'
capchakarma: tinyint(3) unsigned NOT NULL default '0'
opt: text NOT NULL
*/

//    msq("DELETE FROM ".$GLOBALS['db_unic']." WHERE `id`='".e($old)."'");

}



function get_fb_id($url) {
    $url=str_replace('://www.','://m.',$url);
    $url=str_replace('http://','https://',$url);
    $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.3) Gecko/20070309 Firefox/2.0.0.3");
    $s=curl_exec($ch);
    if(preg_match("/\;id=(\d+)\&/si",$s,$m)) return $m[1];
    return false;
}


// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать, allwork - общее количество (измерено ранее), $o - то, что кидать на экран.
function installmod_do() { global $o,$skip,$allwork,$delknopka; $starttime=time();

$SK=10;

	while((time()-$starttime)<3 && $skip<$allwork) {
		$p=ms("SELECT `id`,`openid` FROM ".$GLOBALS['db_unic']." WHERE `openid`!='' && `admin`='podzamok' && `openid` LIKE '%www.facebook.com/%' LIMIT ".($skip++).",".$SK,"_a",0);

		foreach($p as $u) { $l=$u['openid'];
		    // if(!strstr($u['openid'],'www.'))
		    if(
			preg_match("/profile\.php\?id\=(\d+)/si",$l,$m)
//			preg_match("/app_scoped_user_id\/(\d+)/si",$l,$m)
			|| preg_match("/www\.facebook\.com\/(\d+)/si",$l,$m)
		    ) $o.= "<br>".h($u['id']).") <font color=green>".$m[1]."</font> - ".h($u['openid']);
		    elseif(preg_match("/www\.facebook\.com\/([^\/]+)$/si",$l,$m)) {
			$id=get_fb_id($l);
			// $id=false;
			if($id==false) $o.="<br>".h($u['id']).") <font color=red>ERROR: ".h($l)."</font>";
			else $o.="<br>".h($u['id']).") <font color=blue>".h($id)." = ".h($l)."</font>";
		    } else $o.="<br>".h($u['id']).") ".$u['openid'];
/*
> 4 https://www.facebook.com/app_scoped_user_id/100001073866092
> 995 http://www.facebook.com/100002742038207
> 1376 http://www.facebook.com/profile.php?id=100001069196962)
*/

		}
//		usleep(100000);
//		$skip+=57;

// return $o;
	$skip+=$SK;
	if(sizeof($p)<$SK) return 0;
	}

	$o.="<hr>итого: ".$skip;
	if($skip<$allwork) return $skip;
//	$delknopka=1;
//	return 0;
	return false;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
function installmod_allwork() { return ms("SELECT COUNT(*) FROM ".$GLOBALS['db_unic']." WHERE `openid` LIKE '%www.facebook.com/%'","_l",0); }

?>