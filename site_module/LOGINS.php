<?php // Комментарии

include_once $GLOBALS['include_sys']."getlastcom.php"; getlastcom();

function LOGINS($e) { global $lim,$admin,$mode,$lastcom,$ncom;
$conf=array_merge(array(
'mode'=>'time_reg', // 'timelast'
'template'=>"<p><table width=100% border=1 cellspacing=0 cellpadding=10><tr><td><img src='{img}' align=left>
<b>{zamok}&nbsp; <span onclick=\"majax('login.php',{action:'getinfo',unic:{unic}})\">{imgicourl}</span></b>
<br>[{unic}] <b>{capchakarma}</b> {time_reg} / {timelast}
<br>login: {login} password: {password}
<br>{openid}
<br>{realname} mail: {mail} site: {site}
</td></tr></table>"
),parse_e_conf($e));

// $lastcom=strtotime("2011-01-01");
// $conf['mode']='time_reg';
$mytime=time();

$pp=ms("SELECT `img`,`timelast`,`id`,`login`,`password`,`openid`,`realname`,`mail`,`site`,`admin`,`time_reg`,`capchakarma`
FROM ".$GLOBALS['db_unic']."
WHERE `".e($conf['mode'])."`>='".
e($conf['mode']=='timelast'?date("c",$lastcom):$lastcom)."' AND (`password`!='' OR `openid`!='') ORDER BY `".e($conf['mode'])."`
","_a");

$s=''; foreach($pp as $p) { $p=get_ISi($p);

	$s.=mper($conf['template'],array(
		'zamok'=>($GLOBALS['podzamok']?zamok($p['admin']):''),
		'imgicourl'=>$p['imgicourl'],
		'img'=>h($p['img']),
		'unic'=>$p['id'],
		'login'=>h($p['login']),
		'password'=>substr('password',7,10),
		'openid'=>h($p['openid']),
//		'lju'=>h($p['lju']),
		'realname'=>h($p['realname']),
		'mail'=>h($GLOBALS['podzamok']?$p['mail']:'yes'),
		'site'=>h($p['site']),
		'admin'=>$p['admin'],
		'capchakarma'=>h($p['capchakarma']),
		'timelast'=>h(date('Y-m-d H:i:s',$p['timelast'])),
		'time_reg'=>h(date('Y-m-d H:i:s',$p['time_reg']))
	));

}

return $s;
}

?>