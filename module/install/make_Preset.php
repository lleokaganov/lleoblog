<?php

function installmod_init() { 
        $f=$GLOBALS['foto_file_small']."_fotoset.dat";
        if(!is_file($f)) return false;
        if(($r=unserialize(file_get_contents($f)))===false || !sizeof($r)) return false;
        if($GLOBALS['acc']!='') return "ERROR: acc!=''!";
	return "Перевести пресеты в новый формат";
}

function installmod_do() {
        if($GLOBALS['acc']!='') idie("ERROR: acc!=''!");
        $f=$GLOBALS['foto_file_small']."_fotoset.dat";
        saveset(unserialize(file_get_contents($f)));
	unlink($f);
	return "Пресеты переведены в новый формат, старый файл удален";
}

?>