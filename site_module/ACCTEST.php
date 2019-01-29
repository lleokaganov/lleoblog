<?php

/*  */

function ACCTEST_ajax(){ // if_iphash(RE('iphash'));
	
	$a=RE('a');

    if($a='addadmin') {
	global $acc,$acn;
	ADMA(); 

	$un=RE0('un'); if(!$un) idie("Error unic #".$un." (\"<b>".h(RE('un'))."</b>\") Numeric only.");

	$is=getis($un);
	if($is==false) idie("Error: unic #".$un." not found!");
	$name="<div class=ll onclick=\"majax('login.php',{a:'getinfo',unic:".$unic."})\">".$is['imgicourl']."</div>";
	if($is['loginlevel']<2) idie("Пользователь ".$name." недостаточно заполнил свой профиль");


	if(0!=ms("SELECT COUNT(*) FROM `jur` WHERE `unic`='".e($un)."' AND `acc`='".e($acc)."'","_l",0)) idie("Этот человек уже админ");

// dier(ms("SELECT MAX(`acn`) AS `acn` FROM `jur`",'_l'));
// SELECT MAX(article) AS article FROM shop
// idie("ADD: acn=$acn acc=$acc unic=$un");
//    $max=1*ms("SELECT MAX(`acn`) AS `acn` FROM `jur`",'_l')+1; // найти наибольшее
	msq_add('jur',array('acc'=>e($acc),'unic'=>$un,'acn'=>e($acn)));

    dier(ms("SELECT * FROM `jur`"),$GLOBALS['msqe']);

	idie("n=$n".$GLOBALS['msqe']);


	dier($is);
	idie($name." unic #".$un);

	// $pp=ms("SELECT `unic`,`acn` FROM `jur` WHERE `acc`='".e($acc)."'","_a",0);
// acn: int(10) unsigned NOT NULL auto_increment
//acc: varchar(32) NOT NULL
//unic: int(10) unsigned NOT NULL

	idie($a);

    }

	if($a=='mailto') {
		return "

wewew

";
//  ".$mail."
	}

	$acc=RE('acc');
}

function ACCTEST($e) { global $admin,$acc,$acn,$ADM,$IS,$httphost,$db_unic;
$conf=array_merge(array(
'template'=>"<br><a href='{acc_link}'>{acc}</a> (<a href='{acc_link}contents'>{count}</a>)"
),parse_e_conf($e));

	$o="<h1>аккаунт `".h($acc)."`</h1>";
	$pp=ms("SELECT `unic`,`acn` FROM `jur` WHERE `acc`='".e($acc)."'","_a",0);
	if(!sizeof($pp)) return $o."<p>Аккаунта `".h($acc)."` на сервере не существует.
	<br><br>Вы можете его завести: заполнить в своей <span class=ll onclick=\"majax('login.php',{a:'getinfo'})\">личной карточке</span> login: ".h($acc).", после чего зайти на <a href='".$GLOBALS['wwwhost']."acc'>".$GLOBALS['wwwhost']."acc</a> и создать себе одноименный аккаунт.";

	    foreach($pp as $p) { $unic=$p['unic'];
		$is=getis($unic);
		$o.="<div>аккаунт №".$p['acn']." <b>".$acc."</b> - админ <div class=ll onclick=\"majax('login.php',{a:'getinfo',unic:".$unic."})\">".$is['imgicourl']."</div> (unic #".$unic.")</div>";
	    }


	$o.="<p>добавить ещё одного админа unic: <input id='addunic' type=text size=10 value=''> <input type=button value='add admin' onclick=\"majax('module.php',{mod:'ACCTEST',a:'addadmin',un:idd('addunic').value})\">";


	return $o."<p>для справки: ваш номер посетителя в базе посетителей unic = ".$GLOBALS['unic']
	."<p>Ну и как вы думаете, это ваш аккаунт или вам надо <a href='".$httphost."/login'>перелогиниться</a> его владельцем, чтобы получить к нему доступ?"
	."<p>Подсказка: если в самом верхнем левом углу страницы вы не видите оранжевого шарика, значит вы не залогинены админом аккаунта ".h($acc).". Админ аккаунта на любой странице своего аккаунта видит оранжевый шарик в левом верхнем углу.";
}
?>