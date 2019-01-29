<?php /* модуль REDIR

заебали
91.224.183.*
95.173.128.*
*/

function REDIR($e) {

//return '';

$conf=array_merge(array(
	'ip'=>'',
	'url'=>'/blocked.htm?redirect_from={url}',
	'error'=>301 // Заблокировано
),parse_e_conf($e));

if($conf['ip']!='') {
	$q=(strstr($conf['ip'],' ')?explode(' ',$conf['ip']):array($conf['ip']));
	$k=1; foreach($q as $i) { $i=trim($i); if($i=='') continue;
		if(substr($GLOBALS['IP'],0,strlen($i))==$i){$k=0;break;}
	} if($k) return;
}

/*
        300 Multiple Choices (Множество выборов).
        301 Moved Permanently (Перемещено окончательно).
        302 Found (Найдено).
        303 See Other (Смотреть другое).
        304 Not Modified (Не изменялось).
        305 Use Proxy (Использовать прокси).
        306 (зарезервировано).
        307 Temporary Redirect (Временное перенаправление).
*/

// return str_replace('{url}',$GLOBALS['mypage'],$conf['url']).'#'.$conf['error'];
redirect(str_replace('{url}',$GLOBALS['mypage'],$conf['url']),$conf['error']);
}

?>