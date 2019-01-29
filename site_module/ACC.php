<?php

/*  */

function ClaudFlare_reg($acc) { // прописать домен на CloudFlare
    if(!isset($GLOBALS['dyndns_clowdflare_mail']) || !isset($GLOBALS['dyndns_clowdflare_token'])) return false;
    $site=explode('/',$GLOBALS['httpsite'].'/',4); $site=$site[2]; if(empty($site)) idie("Error domain `".h($GLOBALS['httpsite'].'/')."`");
    include_once $GLOBALS['include_sys']."protocol/CloudFlare.php";

//    $otv1="record already exists";

    return // "salert(\"".njsn(nl2br(h(
	cloud_add_items($acc,$site,'CNAME',120,'true')
	."<br>"
	.cloud_add_items('www.'.$acc,$site,'CNAME',120,'true')
    // )))."\",3000);"
    ;
}

function ACC_ajax(){ if_iphash(RE('iphash'));
	$acc=RE('acc');
	if(preg_match("/[^a-z0-9\_\-]+/s",$l)) idie("В вашем логине `".h($acc)."` постороние символы!");
  if(($acn=ms("SELECT `acn` FROM `jur` WHERE `acc`='".e($acc)."' LIMIT 1","_l",0))!==false) {
    return "idie(\"Аккаунт уже существует."
.njsn($GLOBALS['IS']['login']==$acc?"<p>Написать <a href=\"javascript:majax('editor.php',{acn:".$acn.",a:'newform',hid:hid})\">новую заметку</a>":'')
."\");"
    .ClaudFlare_reg($acc);
    }

  if(($u=ms("SELECT `id` FROM ".$GLOBALS['db_unic']." WHERE `login`='".e($acc)."'","_l",0))===false) idie("User `".h($acc)."` not found!");

    $o='';

    $max=1*ms("SELECT MAX(`acn`) AS `acn` FROM `jur`",'_l')+1; // найти наибольшее

  msq_add('jur',array('acc'=>e($acc),'unic'=>$u,'acn'=>$max));
  $acn=ms("SELECT `acn` FROM `jur` WHERE `acc`='".e($acc)."'","_l",0);

    $o.=ClaudFlare_reg($acc);
    return $o."
idie(\"User: `".h($acc)."` unic=$u <font color=green>CREATED</font> with id=$acn"
."<p>Написать <a href=\"javascript:majax('editor.php',{acn:".$acn.",a:'newform',hid:hid})\">новую заметку</a>"
."\");";

}

function ACC($e) { global $admin,$acc,$acn,$ADM,$IS,$httphost;
$conf=array_merge(array(
'mode'=>"admin",
'sort'=>'',
'day'=>30,
'all'=>0,
'visible'=>1,
'maketwo'=>0,
'template'=>"<br><a href='{acc_link}'>{acc}</a> (<a href='{acc_link}contents'>{count}</a>)"
),parse_e_conf($e));

if($conf['mode']=='list') { // AND z.DateDate>".(time()-$conf['day']*86400) //,z.COUNT(*) as `count`
	$pp=ms("SELECT `acc`,`acn` FROM `jur`".($conf['sort']!=''?" ORDER BY `".e($conf['sort'])."`":''),"_a",5000);
	$x=strstr($conf['template'],'{count}');
	$o=''; foreach($pp as $p) {
// die(WHERE()."   ### $acn");
		$count=($x?ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `acn`='".$p['acn']."'"
		.($conf['day']?" AND `DateDate`>".(time()-$conf['day']*86400):'')
		.($conf['visible']?" AND `Access`='all'":'')
		,"_l"):0);
		if($count || $conf['all']) $o.=mper($conf['template'],array('acc'=>h($p['acc']),'acc_link'=>acc_link($p['acc']),'count'=>$count));
	}
	return $o;
}

if($conf['mode']=='count') { return ms("SELECT COUNT(*) FROM `jur`","_l"); }

// if($conf['mode']=='admin' && !$admin) { if(empty($acc)) return "Admin only!"; /* redirect($httphost.'acc'); */ }

// return "admin: ".intval($admin);

	// админ зашел создать аккаунт:
//	if($admin&&!empty($acc)) return "<span class='ll' onclick=\"if(confirm('create?'))majax('module.php',{mod:'ACC',acc:'$acc',iphash:'".iphash()."'});\">Create '".h($acc)."'?</span>";

	// логин зашел создать аккаунт:
	if(empty($IS['login'])) return "У вас не заполнено поле `login` в <span class='ll' onclick=\"majax('login.php',{a:'getinfo'})\">карточке</span>";
	if(empty($IS['password'])&&empty($IS['openid'])) return "У вас не заполнено поле `password` в <a href=\"javascript:majax('login.php',{action:'openid_form'})\">карточке</a>. Как вы планируете вернуть свой аккаунт, когда авторизация браузера слетит?";
	$l=h($IS['login']);

	if(preg_match("/[^a-z0-9\_\-]+/s",$l)) return "В вашем логине `$l` постороние символы (допустимы строчные: a-z0-9_-). Сменить логин возможности нет. Только разлогиниться и завести новый аккаунт ;)";

	if($acc!='') return "Этот раздел работает по адресу: <a href='".$GLOBALS['httphost']."acc'>".$GLOBALS['httphost']."acc</a>";
//	    return "Хотите завести себе аккаунт <b>$l</b>? <input type='button' value='Create ".$l."' onclick=\"if(confirm('create?'))majax('module.php',{mod:'ACC',acc:'$l',iphash:'".iphash()."'});\">";
// то можно это сделать, зайдя по адресу <a href='".acc_link($l)."acc'>".acc_link($l)."acc</a>";

// return 'D';

	if(0!=ms("SELECT COUNT(*) FROM `jur` WHERE `acc`='".e($acc)."'","_l",0))
	    return "Аккаунт `$acc` уже создан, посмотрите информацию о нем: <a href='".h(acc_link($l))."acctest'>".h(acc_link($l))."acctest</a>";

	if($conf['maketwo']==0 && 0!=ms("SELECT COUNT(*) FROM `jur` WHERE `acc`='".e($l)."'","_l",0)) {
	    return ClaudFlare_reg($l)."<p>Account <b>$l</b> already created, see more: <a href='".h(acc_link($l))."acctest'>".h(acc_link($l))."acctest</a>";
	}
//	.($acc!=''?"<p>Согласно настройкам именно этого сервера, здесь запрещено создавать множественные аккаунты для одного пользователя."
//	."<p>Если хотите завести себе аккаунт <b>$l</b>, то поменять логин в личной карточке сейчас возможности нет - можно только разлогиниться и завести новую карточку.":'');

/*
	if($conf['maketwo']==0 && $l!=$acc) return "В вашей <span class=ll onclick=\"majax('login.php',{a:getinfo})\">карточке</span> прописан логин <b>$l</b>, а вы почему-то пытаетесь создать аккаунт <b>$acc</b>. Согласно настройкам именно этого сервера, здесь запрещено создавать множественные аккаунты для одного пользователя."
."<p>Вы разберитесь, чего вы хотите:"
."<p>1. Если хотите завести себе аккаунт <b>$acc</b>, то можно это сделать, зайдя по адресу <a href='".acc_link($acc)."acc'>".acc_link($acc)."acc</a>"
."<p>2. Если хотите завести себе аккаунт <b>$l</b>, то поменять логин в личной карточке сейчас возможности нет - можно только разлогиниться и завести новую карточку ;)";
*/

	return "Да, здесь можно создать журнал для своего аккаунта <b>$l</b>:<p>
<center><input type='button' value='Создать журнал ".$l."' onclick=\"if(confirm('create?'))majax('module.php',{mod:'ACC',acc:'$l',iphash:'".iphash()."'});\"></center>";
}

?>