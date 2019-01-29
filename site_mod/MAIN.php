<?php // Отображение статьи с каментами - дата передана в $Date

SCRIPTS_mine();

function MAIN($e) { global $article;

list($article["Year"],$article["Mon"],$article["Day"])=explode("/",substr($article['Date'],0,10),3);
if(!empty($article["Year"]) && !empty($article["Mon"]) && !empty($article["Day"])) $article["DateTime"]=mktime(1,1,1,1*$article["Mon"],1*$article["Day"],1*$article["Year"]);

// [prevlink] [nextlink] [prevnext] - ссылки на соседние заметки
list($article['Prev'],$article['PrevHeader'])=ms("SELECT `Date` as '0',`Header` as '1' FROM `dnevnik_zapisi` ".WHERE("`DateDatetime`<'".e($article['DateDatetime'])."' AND `DateDatetime`!=0")." ORDER BY `DateDatetime` DESC LIMIT 1","_1");
list($article['Next'],$article['NextHeader'])=ms("SELECT `Date` as '0',`Header` as '1' FROM `dnevnik_zapisi` ".WHERE("`DateDatetime`>'".e($article['DateDatetime'])."'")." ORDER BY `DateDatetime` LIMIT 1","_1");
return ''; }

//==============================================================================================

function PRAVKA($e) {
SCRIPTS("text_scripts","
var ajax_pravka='".$GLOBALS['www_ajax']."ajax_pravka.php';
var dnevnik_data='".$GLOBALS['article']['Date']."';
var ctrloff=".($_COOKIE['ctrloff']=='off'?1:0).";
");
}

//==============================================================================================


function PREVNEXT($e='') { global $article,$wwwhost,$httphost,$hmypage,$_PAGE,$acc,$blogdir;

$conf=array_merge(array(
	'prev'=>"<a title=\"{#prevHeader}\" href='{prevlink}'>&lt;&lt; предыдущая заметка</a>",
	'next'=>"<a title=\"{#nextHeader}\" href='{nextlink}'>следующая заметка &gt;&gt;</a>",
	'no'=>"&nbsp;",
	'template'=>"<center><table width=98% cellspacing=0 cellpadding=0><tr valign=top>
<td width=50%><font size=1>{prev}</font></td>
<td width=50% align=right><font size=1>{next}</font></td>
</tr></table></center>"
),parse_e_conf($e));

	$prevlink=get_link_($article['Prev']);
	$nextlink=get_link_($article['Next']);
	$_PAGE["prevlink"] = $article['Prev']!=''?$prevlink:$hmypage;
	$_PAGE["nextlink"] = $article['Next']!=''?$nextlink:$hmypage;

	$a=array(
		'prevlink'=>$prevlink,
		'nextlink'=>$nextlink,
		'prevHeader'=>$article['PrevHeader'],
		'nextHeader'=>$article['NextHeader']
	);

	$a['prev']=$article['Prev']==''?$conf['no']:mper($conf['prev'],$a);
	$a['next']=$article['Next']==''?$conf['no']:mper($conf['next'],$a);
	return mper($conf['template'],$a);
}

//==============================================================================================
// [title] - заголовок html
function TITLE($e) { global $article;

	if($e=='') $e="{site}: {date} {header}";

	$e=str_ireplace('{site}',$GLOBALS['admin_name'],$e);
	$e=str_ireplace('{date}',$article['Date'],$e);
	$e=str_ireplace('{header}',($article['Header']!=''?$article['Header']:''),$e);

	$GLOBALS['mytitle']=$e;

	return '';
}

function STATISTIC($e) { global $article;
$c=array_merge(array(
	'COUNTER'=>'{_COUNTER:_}',
	'majax'=>"majax('statistic.php',{a:'loadstat',data:'".$article['num']."'})",
	'template'=>"<div class=l onclick=\"{majax}\">статистика</div>"
),parse_e_conf($e));
	return mper($c['template'],$c);
}

//==============================================================================================
// [body] - обработка текста заметки
function TEXT($e) { global $article,$ADM; include_once $GLOBALS['include_sys']."_onetext.php";
if($ADM&&$_SERVER['QUERY_STRING']=='edit') return "EDIT MODE
<script>page_onstart.push=\"majax('editor.php',{a:'editform',num:'".$article['num']."',comments:(idd('commpresent')?1:0)})\"</script>";

$conf=array_merge(array(
'template'=>"<div id='Body_{num}'>{text}</div>"
),parse_e_conf($e));

return mper($conf['template'],array('text'=>onetext($article),'num'=>$article['num']));
}

function PODZAMCOLOR() { $a=$GLOBALS['article']['Access']; if($a=='all') return "";
	return " style=\"background-color: ".$GLOBALS['podzamcolor']."\"";
}

//==============================================================================================
function OEMBED($e) { return '
<link rel="alternate" type="application/json+oembed" href="'.$httphost
."ajax_imbload.php?mode=oembed&date=".urlencode($GLOBALS['article']['Date']).'" />
<link rel="alternate" type="application/xml+oembed" href="'.$httphost
."ajax_imbload.php?mode=xml&date=".urlencode($GLOBALS['article']['$Date']).'" />
'; }

//==============================================================================================
// [counter] - счетчик на странице
function COUNTER($e) {
	// return $GLOBALS['article']["view_counter"]+1; // старый счетчик
	return "<span class=counter"
.($GLOBALS['memcache']?" onclick=\"this.onclick='';this.style.color='red';inject('counter.php?num="
.trim($GLOBALS['blogdir'],'/').'_'.$GLOBALS['article']['num']
."&ask=1&old=0');\"":'')
.">".get_counter($GLOBALS['article'])."</span>";
}

//==============================================================================================
function UNIC($e) { global $IS;

    if(empty($e)) $e="<div style='display:inline;position:absolute;z-index:3;top:5px;right:10px;font-size:9px;'>"
."<img onclick=\"{onclick}\" alt=\"{imgicourl}\" style=\"cursor:pointer;height:43px !important;width:43px;vertical-align:middle;border-radius:50%;box-sizing:border-box;\" src=\"{wwwhost}user/{id}/userpick.jpg\">"
."<span style=\"bottom:5px;height:10px;position:absolute;right:0;width:10px;\">{zamok}</span>"
."<span alt='личная почта' onclick=\"majax('mailbox.php',{a:'mail',showbox:1})\" style=\"cursor:pointer;bottom:25px;right:-5px;position:absolute;\">"
."<i class='e_kmail'></i>"
."{_MAILBOX:<span style=\"background:#dc4666;border-radius:50%;color:#fff;font-size:10px;text-align:center;padding:3px;\">{count}</span>_}"
."</span>"
."</div>";

//https://hencework.com/theme/doodle/full-width-light/
// <span class='myunic' title='регистрационная карточка' style='font-weight:bold;' onclick="{onclick}">{name}</span>
// <span style="background: #469408;border-radius: 50%;bottom: 10px;height: 10px;border: 2px solid #fff;position: absolute;right: 0;width: 10px;"></span>


// if($GLOBALS['unic']==4) dier($GLOBALS['IS']);

if(($GLOBALS['podzamok'] || $GLOBALS['ADM']) && $IS['loginlevel']<3) { // отправить сообщение чтоб поправил карточку
mailbox_send(4,$GLOBALS['unic'],"Привет! У тебя установлен подзамочный доступ, но авторизации на сайте нет - "
."как только браузер сбросит куки, учетная запись пропадет вместе с доступом. Поэтому просьба: справа вверху кнопка логина, пожалуйста "
."нажми ее и залогинь свою карточку, как там написано."
.($GLOBALS['IS']['loginlevel']==2&&$GLOBALS['IS']['login']!=''&&$GLOBALS['IS']['password']!=''&&$GLOBALS['IS']['mailconfirm']==0?"\n\nОсновная сейчас проблема -"
." неподтвержденный email. Надо зайти в карточку и подтвердить там его.\n\n"
:'')
." Спасибо! Всегда буду рад тебя видеть!");
}


$c=array_merge($GLOBALS['IS'],array(
	'kuki'=>'', // $GLOBALS['jog_kuki'],
	'onclick_card'=>"majax('login.php',{a:'getinfo'})",
	'onclick_nocard'=>"ifhelpc('".$GLOBALS['httphost']."login','logz','Login')",
	'anonym'=>"<input type=button value='".($IS['loginlevel']==2&&$IS['login']!=''&&$IS['password']!=''?"ждём подтверждения email":"войти")."'>",
	'tmpl'=>"<div class='myunic' title='регистрационная карточка' style='font-weight:bold;font-size:8px;' onclick=\"{onclick}\">{name}</div>",
),parse_e_conf($e));

	if(empty($c['onclick'])) $c['onclick']=$IS['loginlevel']?$c['onclick_card']:$c['onclick_nocard'];
	if(empty($c['name'])) $c['name']=$IS['loginlevel']==3?$IS['imgicourl']:$c['anonym'];

    $c['body']=c0($c['body']);
    $tmpl=!empty($c['body'])?$c['body']:(isset($c['template'])?$c['template']:$c['tmpl']);

    return mper($tmpl,$c);
}

//==============================================================================================

// [another_in_date] - блок, показывающий остальные заметки за это число
function ANOTHER_DATE() { global $article; $s='';
    if($article['DateDate']) {
	$pp=ms("SELECT `Date`,`Header` FROM `dnevnik_zapisi` ".WHERE("`DateDate`='".$article['DateDate']."' AND `Date`!='".e($article['Date'])."' ".ANDC()),"_a");
	if($pp!==false && sizeof($pp)) {
	   foreach($pp as $p) $s.="<br><a href='".getlink($p['Date'])."'>".$p['Date'].($p['Header']!=''?" - ".$p['Header']:'')."</a>";
	   return "<div style='text-align: left; border: 2px dashed #ccc; margin: 10px 10px 20px 10px; padding: 10px;'><i>Другие записи за это число:</i>".$s."</div>";
	}
    }
return '';
}

//==============================================================================================

// [title] - заголовок html

// [Header] - заголовок на странице
function HEAD($e) { global $article;

return "<div class='header'"
.($article['Access']!='all'?" style=\"padding:10pt;background-color:".$GLOBALS['podzamcolor']."\">".zamok($article['Access'])
:">")
.$article["Day"]." ".$GLOBALS['months_rod'][intval($article["Mon"])]." ".$article["Year"]
.(empty($e)?ADMINSET():$e)
."<div id=Header_".$article['num'].($GLOBALS['admin']||$GLOBALS['ADM']?" class=l onclick=\"majax('editor.php',{acn:'".$GLOBALS['acn']."',a:'editform',num:'".$article['num']."'})\"":'').">"
.($article["Header"]!=''?$article["Header"]:'(...)')
."</div></div>";
}


function HEADERS($e) { global $article,$admin;
$conf=array_merge(array(
'zamok_template'=>"{zamok}&nbsp;", // темплейт замка
'onclick_editor'=>'',
'onclick_editor_title'=>'нажать для редактирования',
'num'=>$article["num"],
'Header'=>$article["Header"],
'empty_Header'=>'(...)',
'adminset'=>ADMINSET(),
'podzamstyle'=>" style='padding:10pt;background-color:{podzamcolor}'",
'template'=>"<div{onclick_editor} class='header' id='Header_{num}'>{Y}-{MONTH}-{D} {H}:{i}:{s}</div>"
// "<div style='display:inline' {podzamstyle}>{adminset} {zamok}{D} {MONTH} {Y} ? <span{onclick_editor} id=Header_{num}>
// {Header}</span></div>"
),parse_e_conf($e));

list($conf['UY'],$conf['UM'],$conf['UD'],$conf['H'],$conf['i'],$conf['s'])=explode(":",date("Y:m:d:H:i:s",$article['DateUpdate']));
list($conf['Y'],$conf['M'],$conf['D'])=($article['DateDatetime']!=0?array($article["Year"],$article["Mon"],$article["Day"]):array($conf['UY'],$conf['UM'],$conf['UD']));
$conf['MONTH']=$GLOBALS['months_rod'][intval($conf['M'])];

$conf['zamok']=mper($conf['zamok_template'],array('zamok'=>zamok($article['Access'])));
if(empty($conf['Header'])) $conf['Header']=$conf['empty_Header'];
$conf['podzamstyle']=($article['Access']!='all'?str_replace('{podzamcolor}',$GLOBALS['podzamcolor'],$conf['podzamstyle']):'');
if($admin||$GLOBALS['ADM']) $conf['onclick_editor']=" onclick=\"majax('editor.php',{acn:'".$GLOBALS['acn']."',a:'editform',num:'".$article['num']."'})\""
.($conf['onclick_editor_title']==''?'':" title='".h($conf['onclick_editor_title'])."'")
;
return mper($conf['template'],$conf);
}


function HEAD_D($e) { global $article;
	$s="<div class='header'>".zamok($article['Access']).$article["Day"]." ".$GLOBALS['months_rod'][intval($article["Mon"])]." ".$article["Year"]."</div>";
	if(!$GLOBALS['admin']&&$GLOBALS['ADM'] or $e!='1') return $s;
	else return "<div class=l onclick=\"majax('editor.php',{acn:'".$GLOBALS['acn']."',a:'editform',num:'".$article['num']."'})\">$s</div>";
}

function HEAD_N($e) { global $article;
	$s="<div class='header' id='Header_".$article['num']."'>".($article["Header"]!=''?$article["Header"]:'(...)')."</div>";
	if(!$GLOBALS['admin']&&$GLOBALS['ADM'] or $e!='1') return $s;
	else return "<div class=l onclick=\"majax('editor.php',{acn:'".$GLOBALS['acn']."',a:'editform',num:'".$article['num']."'})\">$s</div>";
}

function HEAD_TXT($e) { return $GLOBALS["article"]["Header"]; }

?>