<?php /* Модуль работы с магазином read.ru

В CONFIG.PHP желательно прописать:
	$readru_partner = '601'; // номер вашей партнерской программы
	$readru_api = 'api2468713479'; // номер api, выданный вашему сайту
подробнее про API, если интересно: http://read.ru/partner/api/

Модуль выдает информацию о книжке в заданном формате, время от времени (при заходе админа каждые 3 дня)
обновляя данные через API.

Админ может обновить информацию принудительно, дав GET-команду при запросе страницы ?readru=1


Параметры:
id - номер товара на read.ru
template - формат HTML-кода, в котором вставляются конструкции, заменяемые на данные:
{small} - url маленькой картинки
{big} - url большой картинки
{link} - ссылка на товар с учетом номера партнерской программы
остальные данные на примере id=521730:
{id} = 521730
{name} = Лена Сквоттер и парагон возмездия
{price} = 166
{img} = 1
{type_id} = 1
{author} = Каганов Леонид
{author.id} = 5493
{pubhouse} = АСТ
{pubhouse.id} = 996
{series} = Звездный лабиринт (АСТ)
{series.id} = 6430
{genre} = Русская фантастика
{genre.id} = 96
{isbn} = "978-5-17-062202-3"
{ean} = "9785170622023"
{supply_date} = 1296421200
{supply_date_str} = Сегодня
{tags} =
{is_new} =
{rate} = 0.00
{rate_count} = 0
{weight} = 340

По умолчанию template такой:
<p><div style="width:100px;text-align:center;" class=br><a href="{link}"><div><img src="{small}" title="{name}" border=0></div><div><b>{name}</b></a><br>купить: {price}р</div></div>

Пример вызова модуля:
{_readru:id=521730_}

*/

function readru_ajax() { $url=RE('url'); //$url='http://lleo.me/0.php';
	if(!strstr($url,':')) return "idie('readru error url: `".h($url)."`')";
	$timeout=(RE('upd')?5:10*60*60);

	$s=urlget($url,$timeout); // раз в 10 минут
	if($s===false) { if(!$GLOBALS['admin']) return '';
		$fu=urlget_name($url);
		if(!is_file($fu)) $s='file not';
		else $s="timeout: ".(time()-filemtime($fu))." / ".$timeout;
		return "salert('readru: $s',3000)";
	}

	$R=array();
	if($GLOBALS['wwwcharset']!='UTF-8') $s=iconv("UTF-8",$GLOBALS['wwwcharset']."//IGNORE",$s); // из UTF8
	preg_match_all("/\"([a-z0-9\_]+)\":\s*[\"\[\{](.*?)[\"\}\]],/si",$s,$m);
	foreach($m[1] as $n=>$l) {
		if(!preg_match("/^\s*\"([a-z0-9\_]+)\"\s*:\s*\"\s*(.*?)\s*\"\s*$/si",$m[2][$n],$m1)) $R[$l]=$m[2][$n];
		else { $R[$l]=$m1[2]; $R[$l.'.id']=$m1[1]; }
	}
	if(!empty($R['supply_date'])) $R['supply_date']=date("Y-m-d",$R['supply_date']);
	$R['read_time']=time();

//	if($GLOBALS['admin'] && isset($R['errors'])) dier($R,'readru: ERROR');

	msq_add_update('site',array('name'=>e(RE('name')),'text'=>e(serialize($R))),'name');

//	dier($R,RE('name').' #'.$GLOBALS['msqe']); //salert('readru get true',2000)";
}




function readru($e) { return '';
$conf=array_merge(array(
'id'=>'521730',
'rub'=>'р',
'not_found'=>"<span style='font-size: 6px; color:#909090;'>пока нет</span>",
'url'=>"http://api.read.ru/?key=".$GLOBALS['readru_api']."&action=get_book&not_available=1&full_info=1&book_id={id}",
'template'=>'<p><div style="width:100px;text-align:center;" class=br><a href="{link}"><div><img src="{small}" title="&laquo;{name}&raquo;<br>заказать на read.ru" border=0></div><div><b>{name}</b></a><br>купить: {price}</div></div>'
),parse_e_conf($e));

$name=e('read.ru:'.$conf['id']);

$p=ms("SELECT `text` FROM `site` WHERE `name`='".$name."'","_l");
$R=($p!==false?unserialize($p):array());

if($p===false && isset($GLOBALS['readru_api']) // если данных нет
|| (time()-$R['read_time']>3*86400) // или не обновлялись 3 дня
|| $GLOBALS['admin'] && isset($_GET['readru']) // или админ дал команду обновиться
) {
	$url=mpr('url',$conf);
	$fu=urlget_name($url); if($GLOBALS['admin']||!is_file($fu) or time()-filemtime($fu)>10*60*60)
	SCRIPTS("readru_timeout","page_onstart.push(\"majax('module.php',{mod:'readru',url:'$url',name:'$name'"
.($GLOBALS['admin'] && isset($_GET['readru'])?",upd:1":'')
."});"
	.($GLOBALS['admin']?"salert('loading read.ru: "
.((time()-$R['read_time']>3*86400)?'NO DATA':
($p===false && isset($GLOBALS['readru_api'])?'3 DAY':'GET'
))
."',500);":'')
	."\")");
}

if($d===false || !sizeof($R) || isset($R['errors'])) 
return "<div class=br><s><a href='".mpr('url',$conf)."'>".$conf['id']."</a></s></div>";

//not_available=1&
//not_available=1&
// $conf['template']='<hr><p><br>EE: {?}';
// "supply_date": "",

return mper($conf['template'],array_merge($conf,$R,array(
	'?'=>"R='#".intval($R)."#'", // <pre>".(print_r($R,1))."</pre>",
	'price'=>(empty($R["supply_date"])?$conf['not_found']:$R['price'].$conf['rub']),
	'small'=>"http://read.ru/covers_rr/small/".$conf['id'].".jpg",
	'big'=>"http://read.ru/covers_rr/big/".$conf['id'].".jpg",
	'link'=>"http://read.ru/id/".$conf['id']."/?pp=".$GLOBALS['readru_partner'])
));
}

/*

{ "id": "435706",
 "name": "Наша Маша и Волшебный Орех",
 "price": "155",
 "img": "1",
 "type_id": "1",
 "author": { "5493": "Каганов Леонид",
 "7287": "Ткаченко Игорь",
 "18981": "Бачило Александр" },
 "pubhouse": { "989": "Росмэн" },
 "series": { "16468": "Наша Маша" },
 "genre": { "151": "Произведения отечественных писателей" },
 "isbn": [ "978-5-353-04466-6" ],
 "ean": [ "9785353044666" ],
 "supply_date": "",
 "supply_date_str": "01.01.1970",
 "tags": [  ],
 "is_new": "",
 "rate": "0.00",
 "rate_count": "0",
 "weight": "226",
 "time": "0.009" }

{ "id": "995080",
 "name": "Роман и Лариса",
 "price": "227",
 "img": "1",
 "type_id": "1",
 "author": { "5493": "Каганов Леонид" },
 "pubhouse": { "996": "АСТ" },
 "series": [  ],
 "genre": { "96": "Русская фантастика" },
 "isbn": [ "978-5-17-065481-9" ],
 "ean": [ "9785170654819" ],
 "supply_date": "1297803600",
 "supply_date_str": "Сегодня",
 "tags": [  ],
 "is_new": "",
 "rate": "4.86",
 "rate_count": "14",
 "weight": "375",
 "time": "0.008" }
*/

?>