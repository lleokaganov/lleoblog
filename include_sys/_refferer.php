<?php


// $GLOBALS['REF']='http://www.google.ru/url?sa=t&amp;amp;rct=j&amp;amp;q=&amp;amp;esrc=s&amp;amp;source=web&amp;amp;cd=1&amp;amp;ved=0CCoQFjAA&amp;amp;url=http%3A%2F%2Flleo.me%2Fdnevnik%2F2013%2F02%2F06.html&amp;amp;ei=BVoSUq2-GsnL4ATUwYGIAw&amp;amp;usg=AFQjCNHrQSxDHL2Rni3PqQaXxdp8Hxa62Q&amp;amp;sig2=QTz2o-PAndP8CxZee9jRIw&amp;amp;bvm=bv.50768961,d.bGE&amp;amp;cad=rjt';

function refferer($ref,$DateID) { global $IPNUM,$unic;

if($GLOBALS['ahtung']) return false; // если нагрузка большая - ничего не делать

// if(!$unic || ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `unic`='$unic' AND `url`='$DateID'","_l")) return false;

	if(striplink($ref)) return false;

	$u=poiskovik($ref);

if($unic && !ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `unic`='$unic' AND `url`='$DateID'","_l")) {
    if($u[0]!="") { // если поиск - дополнить базу `dnevnik_search`
	$n=intval(ms("SELECT `n` FROM `dnevnik_search` WHERE `DateID`='$DateID' AND `search`='".e($u[0])."'","_l",0));
	if($n) ms("UPDATE `dnevnik_search` SET count=count+1 WHERE `n`='$n'","_l",0); // + счетчик
	else msq_add("dnevnik_search",array("DateID"=>$DateID,"poiskovik"=>e($u[1]),"search"=>e($u[0]),"link"=>e($ref),"count"=>1));
    } else { // иначе дополнить базу `dnevnik_link`
	$n=intval(ms("SELECT `n` FROM `dnevnik_link` WHERE `DateID`='$DateID' AND `link`='".e($ref)."'","_l",0));
	if($n) ms("UPDATE `dnevnik_link` SET count=count+1 WHERE `n`='$n'","_l",0);
	else msq_add("dnevnik_link",array("DateID"=>$DateID,"link"=>e($ref),"count"=>1));
    }
}

// if($GLOBALS['admin']) { dier($u); }

return $u;
}

function striplink($l) {

return (
strstr($l,$GLOBALS['admin_site'])

|| strstr($l,'/feed/')
|| strstr($l,'feedly.com')
|| strstr($l,'reader.')

|| (strstr($l,'www.google.') && strstr($l,'/reader/'))
|| (strstr($l,'.livejournal.com') && strstr($l,'/friends')) // из френдленты
|| (strstr($l,'yandex.ru/read.xml') or strstr($l,'yandex.ru/unread.xml') or strstr($l,'blogs.yandex.ru') or strstr($l,'lenta.yandex.ru')
) // яндексовые читалки
?1:0);

/*
http://www.google.ru/reader/view/?hl=ru&tab=wy
http://www.google.ru/reader/view/
http://www.google.com/reader/view/?tab=my
http://www.google.com/reader/view/
if( ( strstr($l,'.livejournal.com') && strstr($l,'/friends') ) // из френдленты
//    || strstr($l,'yandex.ru/top/') // топы
//    || strstr($l,'blog.yandex.ru') // топы
//    || strstr($l,'blogs.yandex.ru') // топы
    || strstr($l,'yandex.ru/read.xml') // яндексовые читалки
    || strstr($l,'yandex.ru/unread.xml') // яндексовые читалки
    || strstr($l,'bloglines.com') // блоглайнс какой-то
    || strstr($l,'graveron.fatal.ru') // спаммер что ли?
    || ( strstr($l,'www.google.') && strstr($l,'/reader/') )  // google reader
) return true;
*/
}


// =======================================================================


function poiskovik($urlo) { if(strstr($urlo,$GLOBALS['httphost'])) return false; //$s=array();

$u=parse_url($urlo); // $_SERVER["HTTP_REFERER"];
parse_str($u['query'],$outr);
$hp=$u["host"].$u["path"];

// патч для всяких блядских поисковиков, которые начали вдруг экранировать свой поиск
foreach($outr as $n=>$l) { if(strstr($n,'amp;')) { $n2=substr($n,4);
	if((!isset($outr[$n2])||$outr[$n2]=='')) $outr[$n2]=$outr[$n];
}}

// GOOGLE
if (strstr($u["host"],"google.") && !strstr($u["host"],"/reader/")
&& !strstr($u["host"],"/accounts/")
) {
    // http://www.google.ru/url?sa=t&amp;rct=j&amp;q=%D0%B2%D1%8B%D1%81%D0%BE%D1%82%D0%B0%20102%20%D0%B2%D0%BE%D0%BB%D0%B3%D0%BE%D0%B3%D1%80%D0%B0%D0%B4%20%D0%BA%D0%BE%D0%BC%D0%BF%D1%80%D0%BE%D0%BC%D0%B0%D1%82%20%D0%BD%D0%B0%20%D0%BF%D0%BE%D0%BB%D0%B8%D1%86%D0%B5%D0%B9%D1%81%D0%BA%D0%B8%D1%85%20%D0%B3%D0%BE%D1%80%D0%BE%D0%B4%D0%B0&amp;source=web&amp;cd=46&amp;ved=0CE4QFjAFOCg&amp;url=htt
//	if($GLOBALS['admin']) dier($outr);
	$s[2]=urldecode($outr['q'].(empty($outr['as_epq'])?'':" [".$outr['as_epq']."]"));
	$s[0]=uw($s[2]);
	$s[1]="Google";
}

// YANDEX: yandex.ru/yandsearch || yandex.ru/clck || yandex.ru/touchsearch || yandex.ru/msearch
// http://yandex.ua/yandsearch?text=инструкция по монтажe rjylbwbjythjd&amp;amp;lr=10347
// http://hghltd.yandex.net/yandbtm?fmode=inject&amp;amp;url=http://maxpark.com/community/1441/content/2106715&amp;amp;tld=ru&amp;amp;text=Изменение физиологических показателей млекопитающих при кормлении ГМ компонентами&amp;amp;l10n=ru&amp;amp;mime=html&amp;amp;sign=5375bf0c90fbbf2c166276292d21b7ca&amp;amp;keyno=0

elseif(
   strstr($hp,"yandex.ru/yandsearch")
|| strstr($hp,"yandex.ru/clck")
|| strstr($hp,"yandex.ru/touchsearch")
|| strstr($hp,"yandex.ru/msearch")
|| strstr($hp,"hghltd.yandex.ru/yandbtm")

|| strstr($hp,"yandex.ua/yandsearch")
|| strstr($hp,"yandex.ua/clck")
|| strstr($hp,"yandex.ua/touchsearch")
|| strstr($hp,"yandex.ua/msearch")
|| strstr($hp,"hghltd.yandex.ua/yandbtm")

|| strstr($hp,"yandex.net/yandsearch")
|| strstr($hp,"yandex.net/clck")
|| strstr($hp,"yandex.net/touchsearch")
|| strstr($hp,"yandex.net/msearch")
|| strstr($hp,"hghltd.yandex.net/yandbtm")

// http://yandex.ru/msearch?clid=1767234&amp;amp;lr=36&amp;amp;oprnd=3550545344&amp;amp;text=леонид каганов перельман&amp;amp;redircnt=1377546868.1
// http://yandex.ru/msearch?lr=36&amp;amp;text=леонид каганов басманный суд&amp;amp;clid=1767234&amp;a
) {
	$s[2]=urldecode($outr['text']);
	$s[0]=$s[2];
	$s[1]="Yandex";
}

// YANDEX-yandpage
elseif(strstr($u["host"].$u["path"],"yandex.ru/yandpage")) {
	parse_str(urldecode($outr['qs']),$outr2);
	$s[2]=urldecode($outr2['text']);
	$s[0]=kw($s[2]);
	$s[1]="Yandex";
}


/*
http://nostradamvs.livejournal.com/tag/Чужие стихи
http://lurkmore.to/Кошачье_дело
http://lurkmore.to/Синдром_Туретта
http://lurkmore.to/Чуров
http://lurkmore.to/Грибы
http://www.facebook.com/l.php?u=http://bit.ly/17d0VkE&amp;amp;h=nAQGSaqceAQGXSvJrGn47ywjzvVMnu9YoN_1PENhHtinWEQ&amp;amp;s=1
http://lurkmore.to/Каганов
http://ru.ask.com/web?q=как готовить в микроволновке&amp;amp;qsrc=466&amp;amp;o=6821&amp;amp;l=sem&amp;amp;qo=relatedSearchNarrow
http://lurkmore.to/Каганов
http://lurkmore.to/Каганов
http://lurkmore.to/Синдром_Туретта
http://lurkmore.to/Каганов
http://www.gday.ru/forum/Общий-форум/207126-О-России-и-вообще.html
http://lurkmore.to/Синдром_Туретта
http://yandex.ru/touchsearch?text=мой брат щипает меня за попу это нормально&amp;amp;clid=1770475&amp;amp;redircnt=1377522304.1
http://lurkmore.to/Каганов
http://lurkmore.to/Леонид_Каганов
http://lurkmore.to/Яблочник
http://lurkmore.to/Леонид_Каганов
http://lurkmore.to/Яблочник
http://lurkmore.to/Синдром_поиска_глубинного_смысла
*/

// |http://yandex.ru/clck/jsredir?from=yandex.ru%3Byandsearch%3Bweb%3B%3B
//&text=%22%D0%B7%D0%B0%20%D1%81%D0%BF%D0%B8%D0%BD%D0%BE%D0%B9%20%D0%BD%D0%B5%D1%81%D1%8F%20%D1%82%D1%8F%D0%B6%D0%B5%D0%BB%D1%8B%D0%B9%20%D1%80%D0%B0%D0%BD%D0%B5%D1%86%22
//&state=AiuY0DBWFJ4ePaEse6rgeAjgs2pI3DW99KUdgowt9XvqxGyo_rnZJjm8yb_44X6dr_Rb0DmaPGV1kfrWZB9hw78hxFXcwpVxVaf890sbEFFJAEyX0jwB1XZDEs5COCJ3ggqBZkhyCmfJ3hg-oDUFEsjDRf7fxXs0H6nmHBgtRP4Ibg26e3pBfc5eHFP4H6QtznZdRJBBJ9s
//&data=UlNrNmk5WktYejR0eWJFYk1LdmtxZ2E0VWkwSDlhdWlQUGFPLW00NXBwRWpQR3RzSE9ndHBIUzdxQUl6cXBsSGNiN0NnUlFTRkN2d0ZNdUNNOEVxT1N6aXRYa01ScXdEdlJta3pGdUxMSHNTM3dKWkxRZXhIMWNvbm9GMFFqSVU
//&b64e=2
//&sign=0b36ee16363114ee435c4360492154e5
//&keyno=0
//&l10n=ru
//&mc=3.4332696895151082|


// RAMBLER
elseif (strstr($u["host"],"rambler.ru")) {
	$s[2]=urldecode($outr['words']." ".$outr['old_q']);
	$s[0]=$s[2];
	 $k_koi=strlen(str_replace("-","",strtr($searchtext,"КГХЛЕОЗЫЭЪИЯЖЩЧБРТПМДЦЬСЮУНЙФШВА","--------------------------------")));
	 $k_win=strlen(str_replace("-","",strtr($searchtext,"кгхлеозыэъияжщчбртпмдцьсюунйфшва","--------------------------------")));
	 if ($k_koi < $k_win) $s[0]=wk($s[0]);
	$s[1]="Rambler";
}

// GO.MAIL.RU
elseif (strstr($u["host"].$u["path"],"go.mail.ru/search")) {
	$s[2]=urldecode($outr['q']." ".$outr['old_q']);
	$s[0]=$s[2];
	$s[1]="Go.mail.ru";
}

// MSN
elseif (strstr($u["host"].$u["path"],"search.msn.com")) {
	$s[2]=urldecode($outr['q']." ".$outr['old_q']);
	$s[0]=$s[2];
	$s[1]="msn";
}  //  http://search.msn.com/results.aspx?q=%D0%BF%D0%BE%D0%BB%D1%83%D1%87%D0%B5%D0%BD%D0%B8%D0%B5+%D0%BF%D0%BE%D0%B2%D0%B0%D1%80%D0%B5%D0%BD%D0%BD%D0%BE%D0%B9+%D1%81%D0%BE%D0%BB%D0%B8&form=QBRE

// YAHOO
elseif (strstr($u["host"],"search.yahoo.com")) {
	$s[2]=urldecode($outr['p']);
	$s[0]=$s[2];
	$s[1]="yahoo";
	if($outr['ei']=='UTF-8') $s[0]=uw($s[0]);
}  //http://search.yahoo.com/search;_ylt=A0geu7e3IMJHbZgABa9XNyoA?p=%D1%81%D0%B8%D0%BB%D0%B0+%D0%B5%D1%81%D1%82%D1%8C+%D1%83%D0%BC%D0%B0+%D0%BD%D0%B5+%D0%BD%D0%B0%D0%B4%D0%BE+%D0%BA%D0%BD%D1%8F%D0%B7%D1%8C+%D0%B2%D0%BB%D0%B0%D0%B4%D0%B8%D0%BC%D0%B8%D1%80&y=Search&fr=yfp-t-501&ei=UTF-8

// LIVE.COM
elseif (strstr($u["host"],"search.live.com")) {
	$s[2]=urldecode($outr['q']);
	$s[0]=$s[2];
	$s[1]="live.com";
} // http://search.live.com/results.aspx?srch=105&FORM=AS5&q=%d0%a4%d0%be%d1%82%d0%ba%d0%b8+%d0%b8%d0%b7+%d0%b3%d0%b5%d1%80%d0%bc%d0%b0%d0%bd%d0%b8%d0%b8


// NIGMA
elseif (strstr($u["host"],"nigma.ru")) {
	$s[2]=urldecode($outr['s']);
	$s[0]=$s[2];
	$s[1]="nigma.ru";
} //http://www.nigma.ru/index.php?s=%D1%84%D0%B8%D0%BB%D1%8C%D0%BC+%D0%92%D0%BE%D1%81%D0%BF%D0%BE%D0%BC%D0%B8%D0%BD%D0%B0%D0%BD%D0%B8%D1%8F+%D0%BE+%D0%B1%D1%83%D0%B4%D1%83%D1%89%D0%B5%D0%BC&t=web&gl=1&yh=1&ms=1&yn=1&rm=1&av=1&ap=1&nm=1&lang=all

// ask.com
elseif (strstr($u["host"],"ask.com")) {
	$s[2]=urldecode($outr['q']);
	$s[0]=$s[2];
	$s[1]="ask.com";
} // http://ru.ask.com/web?q=как готовить в микроволновке&amp;amp;qsrc=466&amp;amp;o=6821&amp;amp;l=sem&amp;amp;qo=relatedSearchNarrow

// bing.com
elseif (strstr($u["host"],"bing.com")) {
	$s[2]=urldecode($outr['q']);
	$s[0]=$s[2];
	$s[1]="bing.com";
} // http://www.bing.com/search?q=как защитить кнопку дверного звонка от вандалов&

elseif(strstr($urlo,'%') && isset($GLOBALS['qlog'])) logi('REFS-test.txt',maybelink($urlo)."\n");

$s[2]=trim(h($s[2]));
$s[0]=trim(h($s[0]));

// тупая блядь диагностика UTF
$s[0]=maybelink($s[2]);

return $s;
}

?>