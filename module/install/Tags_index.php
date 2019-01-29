<?php

function installmod_init() { // return "Исправить `111dnevnik_tags`";
    if(!msq_table('dnevnik_tags')) return false;

    $pp=ms("SHOW INDEX FROM `dnevnik_tags`","_a",0);
    foreach($pp as $p) {
	if($p['Column_name']!='num') continue;
	if($p['Non_unique']==1) return false;
	return "Исправить `dnevnik_tags`";
    }
    return false;
}

function installmod_do() {

//    $pp=ms("SHOW INDEX FROM `dnevnik_tags`","_a",0);

	msq("ALTER TABLE `dnevnik_tags` DROP PRIMARY KEY");
	msq("ALTER TABLE `dnevnik_tags` ADD KEY(`num`)");

foreach(explode(' ',"dnevnik_zapisi dnevnik_tags pravki") as $t) { if(!msq_table($t)) continue;
    $o.="<p>check table: $t";
    $pp=ms("SHOW INDEX FROM `".e($t)."`","_a",0);
    $k=1; foreach($pp as $p) { if($p['Column_name']=='acn') { $k=0; break; } }
    if($k) {
	$o.=" - <font color=red>ADD KEY(`acn`)</font>";
	msq("ALTER TABLE `".e($t)."` ADD KEY(`acn`)");
    } else $o.=" - <font color=green>OK</font>";
}

    return $o;

//	$r=ms("SHOW INDEX FROM `dnevnik_tags`","_a",0); dier($r);
//	return "Исправлены индексы таблицы `dnevnik_tags`";
}

?>