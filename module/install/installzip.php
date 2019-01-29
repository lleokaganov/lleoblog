<?php

// Эта функция возвращает false, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.
function installmod_init() {
// if(!$GLOBALS['aharu']) return false; // только для отладчика
return "Make minimum.zip"; }

// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать, allwork - общее количество (измерено ранее), $o - то, что кидать на экран.
function installmod_do() { global $o,$skip,$allwork,$delknopka; $starttime=time();

    // if(empty($iall=fileget($f."installall.txt"))) return $o='Error: file not found installall.txt';

    $iall="
config.php.tmpl
htaccess

ajax/module.php

css/sys.css

design/img/ajax.gif

include_sys/_autorize.php
include_sys/_files.php
include_sys/_modules.php
include_sys/_msq.php
include_sys/_onetext.php
include_sys/_podsveti.php
include_sys/_refferer.php

js/main.js

site_mod/MAIN.php

site_module/INSTALL.php

template/blank.html
template/blog.html

index.php
";

$f=$GLOBALS['filehost'];
$fi="minimum.zip";

$s="cd '".escapeshellcmd($f)."'; zip '".escapeshellcmd($fi)."'"; foreach(explode("\n",$iall) as $l) { $l=c($l); if($l=='') continue;
    if(!is_file($f.$l)) return "Error - file not found: `".h($l)."`";
    $s.=" '".escapeshellcmd($l)."'";
}

$w=$GLOBALS['httphost'].$fi;
$ff=$f.$fi;

$o=shell_exec($s);

$o="<i>".$s."</i><div style='border:1px solid red'>".nl2br(h($o))."</div><p><a href='".h($w)."'>".h($w)."</a>";

if(!is_file($ff)) {
    if(shell_exec('which zip')=='') return "Error - ZIP not installed (which zip == '').<br><b>sudo apt-get install zip<b>";
    return "Error - file not created: `".h($fi)."`! Check permission of `$ff` <b>".nl2br(h(shell_exec("ls -ld '".escapeshellcmd(rtrim($f,'/'))."'")))."</b>";
}

return $o;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
function installmod_allwork() { return 0; }

?>