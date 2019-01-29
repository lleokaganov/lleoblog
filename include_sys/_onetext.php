<?php // Редактор заметки

include_once $GLOBALS['include_sys']."_modules.php";

// Отображение статьи с каментами - дата передана в $Date
function onetext($p,$q=1) { global $wwwhost,$unic;

/*
if(!function_exists('modules_pre')) { // если не было установлено модулей - подкачать модули
    $f=$GLOBALS['file_template']."module/".$p['template'].".php"; if(is_file($f)) { include $f;
    $GLOBALS['_SCRIPT'][]="var pagetmpl='".h($p['template'])."';";
}
    else { function modules_pre($p) { return $p; } function modules_post($s) { return $s; } $GLOBALS['_SCRIPT'][]="var pagetmpl='';"; }
}
*/

if(isset($GLOBALS['premodule_enable']) && function_exists('modules_pre')) $p=modules_pre($p);

$GLOBALS['IMG_TMPL']="{_IMG: {img} _}";

	// Посчитать юзера

	if($q&&$unic
		&& !$GLOBALS['ahtung'] // если нагрузка большая - ничего не делать
		&& $p['num'] // и если это не служебная страница
	) {
		$msqe_old=$GLOBALS['msqe']; // запомним накопленные ошибки
		$GLOBALS['msqe']='';
		msq_add("dnevnik_posetil",array('unic'=>$unic,'url'=>$p['num'],'date'=>time())); // если есть - не внесет, а даст ошибку, нам не важно
		$GLOBALS['page_pervonah']=$GLOBALS['msqe'];
		$GLOBALS['msqe']=$msqe_old; // восстановим ошибки (без учета последней)
	}

if(isset($GLOBALS['rssmode'])) return prepare_Body($p); // простое форматирование

	$GLOBALS['article']=$p;
	$s=$p["Body"];

    if(strstr($s,'+++++')) $s=preg_replace("/\+{5,}\s*/s","{_cut: if=cutplus\n[читать дальше]\n",$s)."_}";

	$s=modules($s); // процедуры site

//---------- PRAVKI ----------------
if($GLOBALS['unic']) {
$ee=ms("SELECT `text`,`textnew` FROM `".$GLOBALS['db_pravka']."` WHERE `unic`='".$GLOBALS['unic']."'
AND `metka`='new'
AND `Date`='".e("@dnevnik_zapisi@Body@num@".$p['num'])."'".ANDC()." ORDER BY `id`",'_a',0);
if($ee) { foreach($ee as $e) $s=str_replace($e['text'],$e['textnew'],$s); }
}
//----------------------------------

	if(isset($_GET['search'])) $s=search_podsveti_body($s); // подсветка выделенных слов
	if(isset($_GET['mode']) and ($_GET['mode']=='mudoslov' or $_GET['mode']=='mudoslov_rating')) {
        	$ara=explode("\n",fileget($GLOBALS['host_design'].'mudoslov.txt'));
	        foreach($ara as $m) { $m=trim($m); if($m!='') { $_GET['search']=$m; $s=search_podsveti_body($s); }}
	}
/*
//} elseif($_GET['mode']=='hash') { include_once $include_sys."_hashdata2.php"; $article['Body'] = hashflash($article['Body']);

} elseif( $login!='corwin' && !$podzamok && !$admin && $_GET['mode']!='h' ) { // hashdata для чужих
	$article['Body'] = str_replace(array('&nbsp;','&copy;','$mdash;','&laquo','&raquo;'),array(chr(160),chr(169),chr(151),chr(171),chr(187)),$article['Body']);
	//include_once $include_sys."_hashdata2.php"; $pa=hashinit();
	// $article['Body'] = hashdata($article['Body'],$pa);
}
*/


// произвести автоформатирование
if($p['autoformat']!='no') {

include_once $GLOBALS['include_sys']."_obracom.php";
$s=hyperlink("\n".$s."\n",0);
$s=hypermail("\n".$s."\n",0);
$s=trim($s);

$s=preg_replace("/[\=\-\_\#]{7,}/s","<hr width=100%>",$s);






$s=str_replace(
	array("\n\n","\n"),($p['autoformat']=='p'?array("<p>","<br>"):array("<p class=pd>","<p class=d>")),
str_replace("\n ","\n<p class=z>","\n\n".$s));

}

if(isset($GLOBALS['premodule_enable']) && function_exists('modules_post')) $s=modules_post($s);
return $s;
}


//===============
function prepare_Body($p) { global $httphost,$httpsite,$include_sys; $GLOBALS['article']=$p;
        $s=modules($p['Body']); // процедуры site
	// произвести автоформатирование
	if($p['autoformat']!='no') $s=str_replace(array("\n\n","\n"),($p['autoformat']=='p'?array("<p>","<br>"):array("<p class=pd>","<p class=d>")),"\n\n".str_replace("\n ","\n<p class=z>","\n".$s));

$s=str_ireplace(array( // заменить классы на стили
'</p>',
'<p class=d>',
'<p class=pd>',
'<p class=name>',
'<p class=podp>',
'<p class=z>',
'<p class=epigraf>',
'<p class=epigrafp>'
),array(
'',
'<p style="text-align:justify;text-indent:5%;margin-top:0pt;margin-bottom:0pt;">',
'<p style="text-align:justify;text-indent:5%;margin-top:2%;margin-bottom:0pt;">',
'<p style="text-indent:0pt;margin-top:4%;margin-bottom:6%;text-align:center;font-weight:bold;font-size:150%;">',
'<p style="text-indent:0pt;margin-top:30pt;margin-bottom:12%;text-align:right;font-style:italic;">',
'<p style="text-indent:0pt;margin-top:4%;margin-bottom:4%;text-align:center;font-weight:bold;font-size:100%;">',
'<p style="text-indent:0pt;text-align:justify;margin-top:10pt;margin-bottom:0pt;margin-right:4%;margin-left:60%;font-size:80%;">',
'<p style="text-indent:0pt;text-align:right;margin-top:0pt;margin-bottom:4%;margin-right:4%;margin-left:60%;font-size:80%;font-style:italic;">'
),$s);

	$s=prepare_link($s,$p);

return $s;
}


function plain_Body($p,$allowed_tags='') { global $httphost,$httpsite,$include_sys; $GLOBALS['article']=$p;

	if($p['autoformat']=='no') $p['Body']=str_replace("\n",'',$p['Body']);

        $s=modules($p['Body']); // процедуры site
	// произвести автоформатирование
	if($p['autoformat']!='no') $s=str_replace(array("\n\n","\n"),($p['autoformat']=='p'?array("<p>","<br>"):array("<p class=pd>","<p class=d>")),"\n\n".str_replace("\n ","\n<p class=z>","\n".$s));

$ots=chr(160).chr(160).chr(160).chr(160).chr(160).chr(160).chr(160).chr(160).chr(160).chr(160);

$s=str_ireplace(array( // заменить классы на стили
'</td>',
'</tr>',
'<p>',
'<br>',
'<p class=d>',
'<p class=pd>',
'<p class=name>',
'<p class=podp>',
'<p class=z>',
'<p class=epigraf>',
'<p class=epigrafp>'
),array(
" ",
"\n",
"\n\n",
"\n",
"\n".$ots,
"\n\n".$ots,
"\n\n\n".$ots.$ots.$ots,
"\n\n".$ots.$ots.$ots.$ots.$ots.$ots.$ots,
"\n\n\n".$ots.$ots.$ots,
"\n".$ots.$ots.$ots.$ots.$ots.$ots.$ots,
"\n".$ots.$ots.$ots.$ots.$ots.$ots.$ots.$ots.$ots
),$s);

    $s=prepare_link($s,$p);
    $s=preg_replace("/<[^>]+(src|href)=[\'\"]?([^>\'\"\s]+)[^>]*>/si"," $2 ",$s);
    $s=strip_tags($s,$allowed_tags);

return $s;
}


function prepare_link($s,$p) { global $httphost,$httpsite,$mydir;
    $mydir=$httphost.substr($p['Date'],0,(strlen($p['Date'])-strlen(strrchr($p['Date'],"/")))+1);
    $s=preg_replace_callback("/([<\s])(src|href)(\=[\'\"]*)([^\s\'\">]+)/si","linkzamen",$s); // картинки поставить на места
    $s=preg_replace_callback("/(\{\_IMG\:)(\s*)([\'\"]*)([^\s\'\"]+)/si","linkzamen",$s); // картинки тэга {_IMG: поставить на места
    return $s;
}

/*
function prepare_link($s,$p) { global $httphost,$httpsite,$mydir;
	$mydir=$httphost.substr($p['Date'],0,(strlen($p['Date'])-strlen(strrchr($p['Date'],"/")))+1);
//if(strstr('#'.$_REQUEST['up'],'#4-')) {
        $s=preg_replace_callback("/([<\s])(src|href)(\=[\'\"]*)([^\s\'\">]+)/si","linkzamen",$s); // картинки поставить на места
// }
//        $s=preg_replace("/(<[^>]+(src|href)\=[\'\"]*)(\/)/si","$1".$httpsite."/",$s); // картинки поставить на места
//        $s=preg_replace("/(<[^>]+(src|href)\=[\'\"]*)([^>\s\:]{7})/si","$1".$mydir."$3",$s); // картинки поставить на места
	return $s;
}
*/

function linkzamen($p) { global $httpsite,$mydir;
    if(strstr($p[4],'://')) return $p[0];
    if(substr($p[4],0,1)=='/') return $p[1].$p[2].$p[3].$httpsite.$p[4];
    return $p[1].$p[2].$p[3].$mydir.$p[4];
}

function fulllink($l,$link) {
    if(strstr($l,'://')) return $l;
    if(substr($l,0,1)=='/') return $GLOBALS['httpsite'].$l;
    return dirname($link).'/'.$l;
}

?>