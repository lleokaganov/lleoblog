<?php // выводит список тэгов заметки

function TAGS($e) { global $article;
    $r=gettags(); if(empty($r)) return ''; // получить тэги заметки, если их нет, то до свидания

$conf=array_merge(array(
'template'=>"<div style='font-size: 10pt; margin: 10px 0 10px 0; text-align:left;'><span class=l onclick=\"majax('search.php',{a:'alltag'})\">Тэги записи:</span> {tags}</div>"
),parse_e_conf($e));

    foreach($r as $n=>$l) $r[$n]="<a href=\"javascript:majax('search.php',{a:'tag',tag:'".h($l)."'});\">".h($l)."</a>";
    return mper($conf['template'],array('tags'=>implode($r,' ,')));

}

?>