<?php

function urldata($d) { return $GLOBALS['wwwhost'].h($d).(substr($d,4,1).substr($d,7,1)=='//'?".html":''); }

function DESIGN($template,$title) {

if($template=='plain') $GLOBALS['_PAGE'] = array('design'=>file_get_contents($GLOBALS['host_design']."plain.html"),
'header'=>$title,
'title'=>strip_tags($title),

'www_design'=>$GLOBALS['www_design'],
'admin_name'=>$GLOBALS['admin_name'],
'httphost'=>$GLOBALS['httphost'],
'wwwhost'=>$GLOBALS['wwwhost'],
'wwwcharset'=>$GLOBALS['wwwcharset'],
'signature'=>$GLOBALS['signature']
);

}

function blogpage($title='') { global $IS,$_PAGE,$wwwhost,$login,$podzamok;

	STYLE_ADD($GLOBALS['www_css']."blog.css");

	if(isset($IS['user']) and isset($IS['obr'])) $loginobr=$GLOBALS['imgicourl'];
	else $loginobr='login&nbsp;'.$GLOBALS['unic'];

$loginobr=preg_replace("/<a\s[^>]+>/s","",$loginobr);
$loginobr=str_replace('</a>','',$loginobr);

$loginobr="<div id=loginobr style='cursor: pointer; padding: 2px; margin: 1px 10px 1px 10px; border: 1px dotted #B0B0B0;' onclick=\"majax('login.php',{action:'openid_form'})\">
<spav style='font-size:7px;'>ваш логин:</span><div style='font-weight: bold; color: blue; font-size: 8px;'>".$loginobr."</div></div>";

$_PAGE = array('design'=>file_get_contents($GLOBALS['host_design']."dnevnik.html"),
'prevnext'=>'',
'preword'=>'',
'preheader'=>'',
'calendar'=>'',
'counter'=>'',
// 'linkoff'=>"<a class=br href='".($_COOKIE['ctrloff']=='off'?$wwwhost."ctrl-on'>включить":$wwwhost."ctrl-off'>отключить")."</a>",
'coments'=>'',
'javascript'=>'',
'ajaxscript'=>'',
'oembed'=>'',
'otherblogs'=>'',

'prevlink'=>$wwwhost,
'nextlink'=>$wwwhost,
'uplink'=>$wwwhost,
'downlink'=>$wwwhost."contents/",

'unic'=>$loginobr.$GLOBALS['jog_kuki'],

'www_design'=>$GLOBALS['www_design'],
'admin_name'=>$GLOBALS['admin_name'],
'httphost'=>$GLOBALS['httphost'],
'wwwhost'=>$wwwhost,
'signature'=>$GLOBALS['signature'],
'wwwcharset'=>$GLOBALS['wwwcharset'],

'hashpage'=>$GLOBALS['hashpage'],
'foto_www_preview'=>$GLOBALS['foto_www_preview'],
'foto_res_small'=>$GLOBALS['foto_res_small'],

'header'=>$title,
'title'=>strip_tags($title) );

SCRIPTS_mine();
SCRIPT_ADD($GLOBALS['www_design']."JsHttpRequest.js"); // подгрузить внешний скрипт

}

?>