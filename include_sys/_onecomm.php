<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй

include_once $GLOBALS['include_sys']."flagistran.php";

ADMA(1);

// include_once $GLOBALS['include_sys']."_modules.php";

//if(!$GLOBALS['admin']) die("REMONT<br>сейчас работает только /dnevnik");

function commclass($p) { return 'c0'; }

function commcolor($p) { global $ACOLOR;

// if($GLOBALS['admin']) idie($GLOBALS['ADM']);

    $f=$GLOBALS['filehost'].'unic-colors.txt'.".dat"; if(!isset($ACOLOR)) { $ACOLOR=(is_file($f)?unserialize(fileget($f)):array()); 
    if(!isset($ACOLOR['screen'])) $ACOLOR['screen']=array('CADFEF','screen');
    if(!isset($ACOLOR['default'])) $ACOLOR['default']=array('EEEEEE','default');
}
    if($p['scr']) return $ACOLOR['screen'][0];
    if(isset($ACOLOR[$p['unic']])) return $ACOLOR[$p['unic']][0];
    return $ACOLOR['default'][0];
}

function doSIGMAY($ip) { if(!$GLOBALS['admin']) return ''; include_once $GLOBALS['site_mod']."SIGNAL.php"; return SIGMAY($ip); }

if(!isset($GLOBALS['comments_on_page'])) $GLOBALS['comments_on_page']=0;

// if(!$GLOBALS['admin']) idie('переделываю, зайдите чуть позже');
//if($GLOBALS['admin']){ // idie('template1: '.h($template));
// if(strstr($template,'<')) $GLOBALS['comment_tmpl']=$template;
// else 
//if(!isset($GLOBALS['comment_tmpl'])) $GLOBALS['comment_tmpl']=fileget($GLOBALS['file_template']."comm/".(empty($template)?"comment_tmpl.htm":h($template)));
// idie('###'.$GLOBALS['comment_tmpl']);
//}
//else $GLOBALS['comment_tmpl']=fileget($GLOBALS['file_template']."comm/".(1||empty($template)?"comment_tmpl.htm":$template));

$GLOBALS['browsers']=array('Linux'=>'Linux','Windows'=>'Windows','NokiaE90'=>'Nokia-E90','Mac OS X'=>'Mac','FreeBSD'=>'FreeBSD','Ubuntu'=>'Ubuntu','Debian'=>'Debian','Firefox'=>'Firefox','Opera'=>'Opera','Safari'=>'Safari','MSIE'=>'IE','Konqueror'=>'Konqueror','Chrome'=>'Chrome');

function comment_one($p,$mojno_comm,$level=false) {

	// if($level<0) $level=-$level;
	if($level<0) { $level=-$level; $par=1; } else $par=0;

	$lev=$level*$GLOBALS['comment_otstup'];
//	$otstup=($level?"<div style='position:absolute;top:30px;left:30px'>".str_repeat("<i style='width:".$GLOBALS['comment_otstup']."px' class='e_ledyellow'></i>",$level)."</div>":'');

	if($p['Time']==0 && $level!==false) // удаленный комментарий
		return "<div id=".$p['id']." name=".$p['id']." class='cdel' style='margin-left:".$lev."px'></div>";

	if(($p=comment_prep($p,$mojno_comm,$level))===false) return ''; // подготовить данные

// idie($GLOBALS['file_template']);

	if(isset($GLOBALS['comment_template'])) $tmpl=$GLOBALS['comment_template']; // если уже готов темплейт
	else $tmpl=$GLOBALS['comment_template']=fileget($GLOBALS['file_template']."comm/".(empty($GLOBALS['comment_tmpl'])?"comment_tmpl.htm":h($GLOBALS['comment_tmpl'])));

// if($GLOBALS['unic']==4 && $p['id']==239796) dier($p);

$p['paren']=($par/*$p['Parent']*/?
"<i title='show parent ".intval($p['id'])."' id='sp".$p['id']."' onclick=\"majax('comment.php',{a:'paren2',id:".$p['id']."})\" style='float:left;display:inline;margin-right:10pt;' class='e_expand_plus'></i>"
//."<img onmouseout=\"clean('show_parent')\" onmouseover=\"majax('comment.php',{a:'paren',id:".$p['Parent']."})\" style='float:left;display:inline;margin-right:10pt;' src='".$GLOBALS['www_design']."e3/kontact_journal.png'>"
:'');


if($level!==false) $tmpl="<div id={id} unic={unic} name={id} class='c0 lc{level}' style='background-color:#{commcolor};position:relative;margin-left:".$lev."px'>".$tmpl."</div>";

//	foreach($c as $n=>$l) $tmpl=str_replace('{'.$n.'}',$l,$tmpl);
	$tmpl=preg_replace("/\n\s*\#[^\n]*/s",'',"\n".$tmpl."\n"); // ubrat commentarii #

$p['www_design']=$GLOBALS['www_design'];

// if($GLOBALS['admin']) dier($p);

$p['img']=(isset($p['img'])?site_validate($p['img']):'');

$p['level']=$level;
$p['lev']=$lev;
$p['levelfalse']=($level===false?1:0);
$p['par']=$par;
$p['comment_otstup']=$GLOBALS['comment_otstup'];

// if($GLOBALS['ADM']) dier($p);

	return str_replace("\n",'',mpers($tmpl,$p));
}

// =========================================================================================================

include_once $GLOBALS['include_sys']."_obracom.php";

// 3 значени€: false - нельз€ вообще 'root' - можно в корне 'tree' - можно везде
function mojno_comment($p) { global $IS,$podzamok,$N_maxkomm,$enter_comentary_days;
	if(isset($p['Comment'])&&$p['Comment']=='disabled') return false; // если запрещены вообще
	if(isset($p['Comment_tree'])&&$p['Comment_tree']=='0') return 0; // если запрещено отвечать на комменты

	// ѕревышение количества посещений или слишком стара€ заметка
	if(!isset($p['counter'])) $t=0;
	else $t=($p['counter'] < $N_maxkomm and $p["DateDatetime"] > time()-86400*$enter_comentary_days ?1:0);

$comm_zapret = $IS['loginlevel']<3?0:1;

	switch($p["Comment_write"]) {
		case 'off': return false;
		case 'on': return 1;
		case 'friends-only': return ($podzamok?1:false);
		case 'login-only': return ($comm_zapret?1:false);
		case 'timeoff': return ($t?1:false);
		return (($t and $comm_zapret)?1:false);
	}
}

// 

function comment_prep($p,$mojno_comm,$level) { global $ADM,$unic,$podzamok,$geoip_color;

    if($GLOBALS['podzamok'] && !isset($p['time_reg']) && $p['unic']!=0) $p['time_reg']=ms("SELECT `time_reg` FROM ".$GLOBALS['db_unic']." WHERE `id`='".e($p['unic'])."'","_l");
    $p['newuser']=(isset($p['time_reg']) && $p['time_reg'] && (time()-$p['time_reg'] < 86400)?1:0);

	$p['commcolor']=commcolor($p);
	if($GLOBALS['admin']) $p['sigmay']=doSIGMAY($p['IPN']);
	// ---- город и страна ----
//	list($p['gorod'],$p['strana'])=(strstr($p['whois'],"\001")?explode("\001",$p['whois'],2):array('',''));
	list($p['country'],$p['citylong'])=(strstr($p['whois']," ")?explode(" ",$p['whois'],2):array('',''));
	$p['countryname']=$GLOBALS['flagistran'][$p['country']];
	$p['city']=(strstr($p['citylong'],',')?'':$p['citylong']);
	$p['ip']=ipn2ip($p['IPN']);
//	$p['whois_small'] = ($p['strana']?search_podsveti(hh($p['strana'])):'').($p['gorod']?($p['strana']?", ":'').search_podsveti(hh($p['gorod'])):'');

	// ---- врем€ ----
	$p['date']=date('Y-m-d',$p["Time"]);
	$p['datetime']=date('H:i',$p["Time"]);
	// ---- Mail ----
	$p['kn_answer']=intval($p['ans']!='0' and ($ADM or $mojno_comm=="1" or $p['ans']=='1'));
	$p['ifadmin']=intval($ADM);
	$p['ifsuperadmin']=intval($GLOBALS['admin']);
	$p['ifpodzamok']=intval($podzamok);
$p['kn_edit']=intval($ADM // если админ
or ($unic==$p['unic'] and ( // или это твой (посетител€) комментарий, и ...
	!$GLOBALS['comment_time_edit_sec'] or // комментарии разрешено редактировать вечно, или ...
	time()-$p['Time'] < $GLOBALS['comment_time_edit_sec'] // врем€ на редактирование не кончилось
	)
));
$p['kn_screen']=intval($GLOBALS['comment_friend_scr'] && $podzamok || $ADM);
$p['kn_del']=intval($ADM || ($GLOBALS['del_user_comments'] && $unic==$p['unic']));
	// ---- браузер ----
	$x=''; foreach($GLOBALS['browsers'] as $a=>$b) if(stristr($p['BRO'],$a)) $x.=($x?' ':'').$b;
	$p['BROlong']=$p['BRO'];
	$p['BRO']=search_podsveti(hh($x));
	// ---- им€ автора ----
	// if(empty(!$p['imgicourl']&&!empty($p['Name']))
	$p['name']=search_podsveti($p['imgicourl']); //."<div style='border:1px dotted red;font-size:6px;'>".h($p['Name'])."</div>";
	if($p['unic']==0) { $p=array_merge($p,
array('name'=>"<font color=gray>".h($p['Name'])."</font>",
'capchakarma'=>'',
'admin'=>'user'));
}

/*
<noindex>
<img src='".$is['IMG']."' alt=' (".$is['ROOT'].") '><b><a href='http://".($is['DOMAIN']=='lleo.aha.ru'?'lleo.aha.ru/user/':'').$logn."' rel='nofollow'>".search_podsveti($is['USER0'])."</a></b>
</noindex>
*/

// ---- заголовок комментари€ ---- // нужен ли?

// ---- текст комментари€ ----
	$text=h($p["Text"]);

if(stristr($text,'{screen:') or stristr($text,'{scr:')) {
	$text=(($ADM||$unic==$p['unic'])?
			preg_replace("/\{screen:\s*(.+?)\s*\}/si","<div style='border: 1px dotted red; background: #eeeeee'>$1</div>",$text)
			: preg_replace("/\{screen:.*?\}/si",'',$text)
	);

	$text=($podzamok||$unic==$p['unic']?
			preg_replace("/\{scr:\s*(.+?)\s*\}/si","<div style='border: 1px dotted blue; background: #eeeeee'>$1</div>",$text)
			: preg_replace("/\{scr:.*?\}/si",'',$text)
	);
}

	if(!$ADM && c($text)=='') return false;

		$text=str_replace("\n","\n<br>\n",$text); // nl2br($text);

		$text=AddBB($text);
		$text="\n$text\n";
		$text=hyperlink($text);
		$text=c($text);
		$text=preg_replace("/\{(\_.*?\_)\}/s","&#123;$1&#125;",$text); // удалить подстыковки нахуй из пользовательского текста!
		$text=preg_replace("/&amp;(#[\d]+;)/si","&$1",$text); // отображать спецсимволы и национальыне кодировки
        $text=str_replace('{','&#123;',$text); // чтоб модули не срабатывали
	$p['text']=search_podsveti($text);


return $p;
}

//====================================================================
function comment_cachename($num) { return $GLOBALS['blogdir'].'-comment-'.$num; }
//function clean_commentcache($num) { cache_rm(comment_cachename($num)); }


//==========================================================================
function load_mas($num) { global $kstop,$comc,$comindex,$db_unic,$ADM,$podzamok,$ttl_longsite;

    if($podzamok || $ADM || !empty($_GET['nocache'])) return get_realmas($num); // подзамкам и админам не кэшировать!

    // дл€ всех остальных попробуем поработать с кэшем

    $cachename=comment_cachename($num); $mas=cache_get($cachename); // есть ли в кэше?
    if($mas!=false) return $mas;

    $mas=get_realmas($num);
    cache_set($cachename,$mas,$ttl_longsite); // закэшировать
    return $mas;
}


function get_realmas($num) { global $kstop,$comc,$comindex;

//c.`group`,
$sql=ms("SELECT c.`id`,c.`unic`,c.`Name`,c.`Text`,c.`Parent`,c.`Time`,c.`whois`,c.`rul`,c.`ans`,
c.`golos_plu`,c.`golos_min`,c.`scr`,c.`DateID`,c.`BRO`,c.`IPN`,
u.`capchakarma`,u.`mail`,u.`admin`
,u.`openid`,u.`realname`,u.`login`,u.`img`,u.`time_reg`
FROM `dnevnik_comm` AS c LEFT JOIN ".$GLOBALS['db_unic']." AS u ON c.`unic`=u.`id` WHERE `DateID`='".e($num)."'
ORDER BY `Time`","_a",0);

	if(!sizeof($sql)) return false;

	$kstop=10000; $comc=array(); $comindex=array();
	foreach($sql as $p) { $p=get_ISi($p);
		$comc[$p['id']]=$p;
		if($p['rul']==1) $comindex[$p['Parent']][$p['id']]='rul';
		elseif($p['scr']==0) $comindex[$p['Parent']][$p['id']]='open';
		else $comindex[$p['Parent']][$p['id']]=intval($p['unic']);
	}
	$mas=vseprint_comm(0,0,0,0); // запихнуть в массив всю простыню комментов комменты
	// добавить потер€нные комменты
	if(sizeof($comc)) { foreach($comc as $id2=>$p) $mas[]=array('p'=>$p,'value'=>1,'id'=>$id2,'level'=>0); }

    return $mas;
}

// $GLOBALS['Comment_media']=$article['Comment_media'];

function load_comments($art,$addprevnext=1) { global $ADM,$opt,$IP,$BRO,$MYPAGE,$www_design,$ADM,$podzamok,
$unic,$comment_otstup,$comment_pokazscr,$maxcommlevel,
$comments_pagenum,$comments_on_page;

	$num=$art['num'];
	if(($mas=load_mas($num))===false) return ppp_nocomment();

	$GLOBALS['opt']=mkzopt(array('opt'=>$art['opt'])); // дл€ обработки каментов могут оказатьс€ полезные опции

	$s=($podzamok||$ADM?"<a href='".$MYPAGE."?nocache=1'><i class='e_ledgreen'></i>$s</a>":'');

	$mojno_comm=mojno_comment($art); // установить, на какие комменты можно отвечать

// а вот теперь, откуда бы ни был массив $mas, из кэша или собранный вживую, выдать комменты

	$podz = $podzamok && (sizeof($mas)<100 || isset($_GET['screen']));
	$yandex = (strstr($BRO,'Yandex') || $IP=='78.110.50.100'?1:0);

//$comments_on_page=6;

// отсчитать предыдущие страницы, чтобы найти начальный $i
$i=0; $m=$comments_pagenum*$comments_on_page; while($m && isset($mas[$i]) && ($mas[$i]['level']!=1 || $m-- )) // первоуровневые
	while(isset($mas[++$i]) && $mas[$i]['level']!=1) { } //

//if($GLOBALS['ADM']) {
//idie("i=$i m=$m");
//dier($art); // [Comment_view] => rul
//}

$k=0; while(isset($mas[$i]) && ( $mas[$i]['level']!=1 || (++$k <= $comments_on_page) ) ) {

if(isset($GLOBALS['ADM']) && $GLOBALS['ADM'] && $art['Comment_view']=='rul') {
    if($mas[$i]['p']['rul']==1) $m=$mas[$i++]; else { $i++; continue; }
} else $m=$mas[$i++];

		if( // открыт ли?
$podz // если ты подзамок
or !$m['value'] // или если коммент глюкавый или старой системы
or ($m['value']==$unic and $unic) // или если это твой коммент, при том, что ты не 0
or ($m['parent_unic']==$unic and $unic) // или если это ќ“¬≈“ на твой коммент, при том, что ты не 0
// or $yandex // или ты яндекс
) {
	if($m['level'] == $maxcommlevel) { // подготовить заплатку
		$zaglush="<div id='o".$m['p']['id']."' class='opc e_expand_plus' onclick='opc(this,$num)' style='margin-left:".($m['level']*$GLOBALS['comment_otstup'])."px'></div>";
	}

	if(($m['level'] <= $maxcommlevel) or $yandex) $s.=comment_one($m['p'],$mojno_comm,$m['level']); // выдать раскладушку
	else if($zaglush) { $s.=$zaglush; $zaglush=false; } // выдать заплатку, но только одну
  } elseif($comment_pokazscr) $s.="<div class=cscr style='margin-left:".($m['level']*$comment_otstup)."px'>"
// .($unic=='1073733'?"<p>".nl2br(h(print_r($m,1))):'')
."</div>";
	}

if(sizeof($mas)>10) {
	$s.=LL('comm:itogo',array('nmas'=>sizeof($mas),'u'=>(!$podz && $podzamok && sizeof($mas)>=100),
	'majax'=>"onclick=\"majax('comment.php',{a:'loadcomments',dat:".intval($art['num']).",mode:'all'})\""
	));



//	$s.="<center><p class=br>всего комментариев: ".sizeof($mas)."</p>";
//	if(!$podz && $podzamok && sizeof($mas)>=100) $s.="<p>показаны только открытые комментарии - 
// <a href=\"javascript:majax('comment.php',{a:'loadcomments',dat:".intval($art['num']).",mode:'all'})\">показать все</a>";
//	$s.="</center>";

}
if(sizeof($mas) && function_exists('PREVNEXT') && $addprevnext) $s.=PREVNEXT();

return get_comm_button($num).$s.get_comm_button($num);
}

//====================================================================
function vseprint_comm($id,$level,$l,$parent_unic=0) { global $comc,$comindex,$kstop; if(!isset($comindex[$id])) return array();
	$mas=array(); $level++; if(!$kstop--) idie('err kstop'.$id.h(print_r($mas,1)));
	foreach($comindex[$id] as $id2=>$value) { //if(!$value) continue;
		$mas[]=array('p'=>$comc[$id2],'value'=>( ($value=='open' or $value=='rul')?0:$value ),'id'=>$id2,'level'=>$level,'parent_unic'=>$parent_unic);
		$mas=array_merge($mas,vseprint_comm($id2,$level,$l,$comc[$id2]['unic']));
		unset($comc[$id2]); // в любом случае удалить коммент из массива
	}
	return $mas;
}
//====================================================================

function search_podsveti($a) { if(empty($_GET['search'])) return $a;
	$a=preg_replace_callback("/>([^<]+)</si","search_p",'>'.$a.'<');
	$a=ltrim($a,'>'); $a=rtrim($a,'<');
	return $a;
} function search_p($r) { return '>'.str_ireplace2($_GET['search'],"<span class=search>","</span>",$r[1]).'<'; }

function str_ireplace2($search,$rep1,$rep2,$s){	$c=chr(1); $nashlo=array(); $x=strlen($search);
	$S=strtolower2($s);
	$SEARCH=strtolower2($search);
	while (($i=strpos($S,$SEARCH))!==false){
		$nashlo[]=substr($s,$i,$x);
		$s=substr_replace($s,$c,$i,$x);
		$S=substr_replace($S,$c,$i,$x);
	} foreach($nashlo as $l) $s=substr_replace($s,$rep1.$l.$rep2,strpos($s,$c),1);
	return $s;
} function strtolower2($s){ return strtr(strtolower($s),'јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„Ўўџ№ЏЁёя','абвгдеЄжзийклмнопрстуфхцчшщыьъэю€'); }

function ppp_nocomment() { return LL('comm:nocomments'); } // "<p class=z>комментариев нет или они все скрыты";

?>