<?php // ЖЖ определялка

function user_name($e) { global $IS;

	if($IS['lju']!='') return $IS['lju'];
	if($IS['login']!='') return $IS['login'];
	if($IS['openid']!='') return $IS['openid'];
	if($IS['realname']!='') return $IS['realname'];
	return $GLOBALS['unic'].($GLOBALS['unic']==0?"(временный)":""); 

}

?>