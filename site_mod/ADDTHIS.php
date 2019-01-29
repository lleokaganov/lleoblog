<?php
/*
автор: јртем ѕавлов, http://temapavloff.ru

ћодуль репоста через сервис http://addthis.com

ѕараметры:
link - ссылка на запись (по умолчанию - ссылка на текущую станицу)
title - заголовок записи (по умолчанию - заголовок текущей записи)
template - шаблон (по умолчанию показывает одну кнопку дл€ разворачивни€ меню со списком всех сервисов. ƒл€ добавлени€ кнопок необходимо в блок div с классами addthis_toolbox и addthis_pill_combo_style добавить тег <a></a> с соответствующем указателем на сервис. Ќапример конструкци€ <a class="addtis_button_livejournal"></a> добавл€ет кнопку дл€ перепоста в ∆∆

Ётот модуль можно вставить в параметр template модулей LAST и CONTENTER, в этом случае параметры link и title должны быть равны {link} и {Header} соответственно.

P.S. ќсторожнее с переводом строки, он служит разделителем параметров :-)

{_ADDTHIS:_}

*/

SCRIPT_ADD("http://s7.addthis.com/js/250/addthis_widget.js");

function ADDTHIS($e) { global $httphost, $article, $admin_name; //link, title, description
$conf=array_merge(array(
'link'=>getlink($article['Date']),
'title'=>h($article['Header']),
'text'=>'утащить&nbsp;к&nbsp;себе',
'template'=>'<div style="float:right" class="addthis_toolbox addthis_pill_combo_style" addthis:url="{link}" addthis:title="{title}"><a class="addthis_button_compact">{text}</a></div>'
),parse_e_conf($e));

return mper($conf['template'],$conf);
}

?>