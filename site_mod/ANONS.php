<?php /* модуль ANONS

Анонсирую новый модуль ANONS. При построении сайтов и прочего говна часто возникают такие задачи, как, например:
- вывести 10 последних новостей
- вывести новости, которые не старше 30 дней
- вывести ссылки на все страницы с тэгами ?пылесос?, ?чайник? или ?бытовая техника?
- вывести ссылки на страницы, которые ты (да, именно ты!) еще не открывал своим браузером
- выводить краткие заголовки или короткие куски текста с ссылкой на продолжение и т.п.

Для этого теперь есть модуль ANONS, он может делать все вышеперечисленное в любых комбинациях и сочетаниях. Его, как обычно, можно встраивать хоть в заметку, хоть в темплейт любой страницы.

Вот список его настроек:

acn - номер аккаунта, в котором идет выборка (по умолчанию - во всех аккаунтах сервера) Будьте внимательны: указывается не ИМЯ аккаунта, а его НОМЕР.

mode - тип выборки, варианты:
- all (по умолчанию) - все страницы
- blog - только страницы блога
- page - только статические страницы

tags - если не пусто (по умолчанию пусто), то выборка будет проводиться по тэгам, перечисленным здесь через запятую

tags_and - как объединять тэги, если их в списке несколько. Варианты:
- OR (по умолчанию) - выбирать записи, содержащие хотя бы какой-то из перечисленных тэгов
- AND - выбирать записи, содержащие все перечисленные тэги

limit - выводить максимальное число записей, по умолчанию 20

days - выводить записи только за последние N дней (по умолчанию 0 ? выводить все)

sort - сортировать записи:
- date (по умолчанию) - по дате заметки блога
- update - по времени последнего обновления
- name - по имени заметки

sortx - как сортировать записи: DESC (по умолчанию) обратная сортировка, пусто (пустые кавычки '') - прямая

podzamok - по умолчанию 0, но если 1, то для подзамочных друзей будут выводиться только подзамочные записи, для всех остальных - вообще ничего

length - число букв в отрывке текста, если 0 - то текст целиком (по умолчанию 200)

media - если 0 (по умолчанию) то выводить голый текст без верстки, картинок и т.п. При media=1 параметр length роли не играет - заметка выводится целиком 2 - очистить от html

template - html-шаблон вывода, по умолчанию:
<div style='text-align:left; padding: 10px 0 10px 0; font-size:12px;'><b>{Y}-{M}-{D}: {Header}</b><br>{Body}&nbsp;<a href='{link}'>(...)</a></div>\n\n
Ну здесь понятно, в {Header}, {Body}, {link} подставляется заголовок заметки, ее содержимое и ссылка на нее, {Y} {M} {D} ? год, месяц и день заметки (если заметка блога)


Несколько примеров работы модуля:

1. Вывести все ссылки на заметки с тэгом ?и смех и грех? или ?пирдуха?

{_ANONS:
template = <div style='border: 1px dotted red'>{Y}-{M}-{D}: <a href='{link}'>{Header}</a></div>
tags = пирдуха, и смех и грех
days = 0
_}

2. Вывести 5 самых старых не обновлявшихся статических страниц

{_ANONS:
template = <div style='border: 1px dotted red'>{Y}: <a href='{link}'>{Header}</a></div>
mode = page
sort = update
sortx = ''
limit = 5
_}

3. Вывести 10 последних записей блога с тэгом ?движок?, которые лично ты (посетитель) еще не читал, но не старше 365 дней, с заголовком и кратким анонсом в 60 символов. Разместить их на странице блоками в зеленой рамочке.

<div style='border: 3px solid green; padding: 10px;'>{_BLOKI: WIDTH=150
{_ANONS:
template = <div class=r><font color=red>{Y}/{M}/{D}:</font><br><b>{Header}</b><br>{Body} <a href='{link}'>читать&nbsp;далее</a></div>\n\n
days = 365
mode = blog
sort = date
tags = движок
limit = 10
length = 60
unread = 1
_}_}</div>


PS: Служебная переменная $GLOBALS['ANONS_count'] устанавливается равной числу выбранных постов.
Ее можно затем вывести командой {_PHPEVAL: $o=$GLOBALS['ANONS_count']; _}

*/

include_once $GLOBALS['include_sys']."_onetext.php";

function ANONS($e) {
	$oldarticle=$GLOBALS['article'];
	$old_premodule_enable=isset($GLOBALS['premodule_enable'])?$GLOBALS['premodule_enable']:0;
	$GLOBALS['premodule_enable']=0;

$conf=array_merge(array(
'acn'=>false,
'redirect'=>0, // делать редирект на самую свежую заметку из найденных по этим критериям
'mode'=>'all', // тип выборки: 'all' - все, 'blog' - только блог, 'page' - статические страницы
'unread'=>0, // не читанные посетителем
'podzamok'=>0, // только подзамки
'tags'=>'', // выбрать по тэгу (если пусто - то все записи), тэги можно перечислить через запятую
'tags_and'=>'OR', // объединение тэгов OR или AND
'limit'=>20, // максимальное число записей
'days'=>0, // ограничить последними N днями (0 - без ограничений)
'sort'=>'date', // сортировка: 'date' - по дате заметки, 'update' - по последнему обновлению
'sortx'=>'DESC', // сортировка: 'DESC' - самый новый сверху; если '' - то наоборот
'length'=>200, // число букв в отрывке текста, если 0 - то тест целиком
'length1'=>100, // число букв в отрывке Body1
'media'=>0, // 0 - голый текст (без верстки, картинок и т.п.)
'list'=>0, // 1 - делать оглавление
'list_element'=>"<div>{numer}. <a href='#L{num}'>{Header}</a></div>", // темплейт элемента оглавления
'list_tmpl'=>"<div><h2>ОГЛАВЛЕНИЕ</h2></div><div>{list}</div>", // темплейт оглавления
'template'=>"<div style='text-align:left; padding: 10px 0 10px 0; font-size:12px;'>"
// ."{edit}"
."<b> {acn} {Y}-{M}-{D}: {Header}</b>"
."<br>{Body}&nbsp;<a href='{link}'>(...)</a>"
."</div>\n\n"
),parse_e_conf($e));

$conf['template']=str_replace(array('{@','@}',"\\n"),array('{_','_}',"\n"),$conf['template']);


if($conf['redirect']) $conf['limit']=1; // если будет редирект - то взять всего одну

$bodyneed1=strstr($conf['template'],'{Body1}');
$bodyneed=$bodyneed1?1:strstr($conf['template'],'{Body}');

$wher=array();
$on=array();
$as=array("`dnevnik_zapisi` as z");

if($conf['mode']=='blog') $wher[]="z.`DateDatetime`!='0'";
elseif($conf['mode']=='page') { $wher[]="z.`DateDatetime`='0'"; $conf['days']=0; }

if($conf['acn']!==false) $wher[]="z.`acn`='".intval($conf['acn'])."'";

if($conf['days']!=0) $wher[]="z.`DateDatetime`>='".(time()-$conf['days']*86400)."'";


if($conf['podzamok']) {
	if($GLOBALS['podzamok']) $wher[]="z.`Access`='podzamok'";
	else return '';
}

if($conf['unread']) {
	$wher[]="(z.`num` NOT IN (SELECT `url` FROM `dnevnik_posetil` WHERE `unic`='".$GLOBALS['unic']."'))";
//	$wher[]="p.`url` IS NULL";
//	$as[]="LEFT JOIN `dnevnik_posetil` as p";
//	$on[]="z.`num`=p.`url`";
}

if($conf['tags']=='') $mstag_sel=$mstag_gr='';
else { $a=explode(',',$conf['tags']);
	$t=array(); foreach($a as $l) $t[]="t.`tag`='".e(trim($l))."'";
		$wher[]="(".implode(' '.e($conf['tags_and']).' ',$t).")";
		$mstag_sel=", GROUP_CONCAT(t.`tag` SEPARATOR ';') as `t`";
		$as[]="INNER JOIN `dnevnik_tags` as t";
		$on[]="t.`num`=z.`num`";
		$mstag_gr="GROUP BY z.`num`";
}

$sq="SELECT z.`acn`,z.`opt`,z.`Date`,".($bodyneed?"z.`Body`,":'')."z.`Header`,z.`Access`,z.`num` $mstag_sel FROM ".implode(" ",$as)." "
.(sizeof($on)?" ON (".implode(' AND ',$on).") ":'')
.WHERE(implode(' AND ',$wher),'z.')
." $mstag_gr"
." ORDER BY z.`".($conf['sort']=='date'?'DateDatetime':$conf['sort']=='name'?'Date':'DateUpdate')."` ".($conf['sortx']=='DESC'?'DESC':'')
.($conf['limit']==0?'':" LIMIT ".e($conf['limit']));

$sq=str_replace("AND `acn`=","AND z.`acn`=",$sq); // йобаный сраный патч!!!

$pp=ms($sq,"_a"); $GLOBALS['ANONS_count']=sizeof($pp);
// dier($pp,$sq);

if($conf['redirect']) { $p=$pp[0];
if($oldarticle['Date']==$p['Date']) return "<font color=red> error: redirect </font>"; // защита от саморедиректа
redirect($GLOBALS['httphost'].$p['Date'].".html".($GLOBALS['admin']?"?redir=".$oldarticle['Date']:''),302); // на последнюю
}

$body1='';
$list=''; $s=''; $numer=0; if(sizeof($pp)) foreach($pp as $p) { if($p['num']==$oldarticle['num']) continue;
	$numer++;
	$p=mkzopt($p); $GLOBALS['article']=$p;
	list($Y,$M,$D) = @explode('/',$p['Date'],3); $D=substr($D,0,2);
// $article["Day"]=substr($article["Day"],0,2); ?????????

	if($bodyneed) {
		$body=onetext($p,0);
		if($bodyneed1) {
			$body1=str_ireplace(array('<br>','<p>','<div>'),array("\n","\n","\n"),$body);
// idie(h('###'.$p['Body']));
			$body1=strip_tags(html_entity_decode($body1));
			$body1=preg_replace("/\n+/s","\n",trim($body1,"\t\r\n ")); // убрать лишние
			list($body1,)=explode("\n",$body1,2); // взять первую строку
			if(strlen($body1)>$conf['length1']) $body1=substr($body1,0,$conf['length1']+strcspn($body1,' ,:;.',$conf['length1'])); // обрезать
		}

	if($conf['media']==1) { // текст полный
		$body=preg_replace("/(<img[^>]+src\=[\'\"]*)([^\/\:]{4,})/si","$1".$GLOBALS['wwwhost'].$Y."/".$M."/$2",$body);
	} elseif($conf['media']==0) { // текст урезанный
		$body=str_replace('<',' <',$body); // добавить пробелы перед вычисткой тэгов
		$body=strip_tags($body); // вычистить все тэги
		$body=str_ireplace('&nbsp;',' ',$body);
		$body=preg_replace("/\s+/s",' ',$body); // убрать двойные пробелы и переносы
		$body=trim($body);
		if($conf['length']!=0) $body=substr($body,0,$conf['length']+strcspn($body,' ,:;.',$conf['length'])); // обрезать
	} elseif($conf['media']==2) { // текст очищенный от html
		$body=strip_tags($body); // вычистить все тэги
		$body=nl2br($body); // сохранить только enter
	} elseif($conf['media']==3||$conf['media']=='nohtml') { // текст очищенный от html, но с энтерами
		$body=str_ireplace(array('<br>','<p>','<div>'),array("\n","\n","\n"),$body);
		$body=strip_tags($body); // вычистить все тэги
		$body=trim($body);
		$body=nl2br($body); // сохранить только enter
	} else return "ANONS: wrong media type";
	} else $Body='';

$s.=($conf['list']?"<a name='L".$p["num"]."'></a>":'').mper(str_replace("\\n","\n",$conf['template']),array(
'Body'=>$body,
'Body1'=>h($body1),
'Header'=>(empty($p["Header"])?'[...]':$p["Header"]),
'acn'=>$p['acn'],
'link'=>getlink($p["Date"],$p['acn']), // неполная ссылка на статью
'num'=>$p["num"],
'numer'=>$numer,
'Y'=>$Y,'M'=>$M,'D'=>$D
));

if($conf['list']) $list.=mper($conf['list_element'],array(
'numer'=>$numer,
'Header'=>(empty($p["Header"])?'[...]':$p["Header"]),
'link'=>getlink($p["Date"],$p['acn']), // неполная ссылка на статью
'Date'=>$p["Date"], // неполная ссылка на статью
'num'=>$p["num"],
'Y'=>$Y,'M'=>$M,'D'=>$D
));

}

$GLOBALS['article'] = $oldarticle;
$GLOBALS['premodule_enable']=$old_premodule_enable;


$s=str_replace(array('-{M}','-{D}'),'',$s);

if($conf['list']) { $list=mper($conf['list_tmpl'],array('list'=>$list)); return $list.$s; }

return $s;
}
?>