<?php /* Модуль рисует Яндекс-карту по указанным параметрам

автор - Тема Павлов http://temapavloff.ru/

для встраивания на сайт необходимо получить ключ: http://api.yandex.ru/maps/form.xml/
и прописать его в config.php, пример:

$api_key='AJyqd00BAAAAfIxMQQMA1iQr2bwfHslJWjOqsh7QPtdO66wAAAAAAAAAAAAw2gLfwW9dGcYQHcB74cwRm-BdWg==';

Опции:

lt = широта
lg = долгота
zoom = масштаб
name = имя метки
descr = описание метки
width = ширина окна
height = высота окна

{_YAMAP:
lt=73.380411
lg=54.981549
zoom=14
name=Омск, остановка Площадь Ленина
descr=Сегодня в переходе у Площади Ленина кто-то воспользовался<br />газовым баллончиком. Спускаюсь, значит, в переход и вижу,<br />как мне на встречу бегут люди, зажимая при этом носы.<br />Сразу не понял в чем дело, но когда дико защипало глаза,<br />последовал общей идеи.
_}
*/

// APk4KksBAAAAs4gYHAIASD1kwFy_udVGmZYJyzBDUOSXh68AAAAAAAAAAADKbQVBR-EoF0QrCIQbjVSd4Gn0fg==

$GLOBAL['yamap_count'] = 0;

SCRIPT_ADD('http://api-maps.yandex.ru/1.1/index.xml?key='.$GLOBALS['api_key']);

SCRIPTS('yandex map','
function create_yamap(id, lt, lg, zoom, name, descr) {
	var map = new YMaps.Map(idd(id));
	var point = new YMaps.GeoPoint(lt, lg)
	map.setCenter(point, zoom);
	var placemark = new YMaps.Placemark(point);
	if(!name && !descr) placemark.openBalloon = function() { return false; };
	else { placemark.name = name; placemark.description = descr; }
	map.addOverlay(placemark);
}
');

// page_onstart.push(\"loadScript('http://api-maps.yandex.ru/1.1/index.xml?key='+api_key);\");

function YAMAP($e) { global $yamap_count;

$conf = array_merge(array(
// 'api_key' => $GLOBALS['api_key'],
'lt' => 0,
'lg' => 0,
'zoom' => 10,
'name' => '',
'descr' => '',
'width' => 400,
'height' => 200
),parse_e_conf($e));

// SCRIPTS('yandex map key',"var api_key='".$conf['api_key']."';");

$yamap_count++;

// $map_container = 
// $map_script = '<script type="text/javascript">create_yamap(\'yamap_'.$yamap_count.'\', '.$conf['lt'].','.$conf['lg'].','.$conf['zoom'].',\''.$conf['name'].'\',\''.$conf['descr'].'\')</script>';
SCRIPTS("page_onstart.push(\"create_yamap('yamap_".$yamap_count."',".$conf['lt'].",".$conf['lg'].",".$conf['zoom'].",'".$conf['name']."','".$conf['descr']."')\")");
return '<div style="width:'.$conf['width'].'px;height:'.$conf['height'].'px;" id="yamap_'.$yamap_count.'"></div>';

 // $map_container.$map_script;
}

?>