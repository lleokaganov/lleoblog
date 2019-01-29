<?php
/* Управление настройками из самой заметки

    [opt] => a:5:{s:7:"autokaw";s:4:"auto";s:10:"autoformat";s:1:"p";s:13:"Comment_write";s:2:"on";s:12:"Comment_view";s:2:"on";s:14:"Comment_screen";s:4:"open";}
    [Access] => all
    [visible] => 1
    [autokaw] => auto
    [autoformat] => p
    [Comment_write] => on
    [Comment_view] => on
    [Comment_screen] => open
    [include] => 
    [Comment_foto_logo] => 
    [Comment_foto_x] => 600
    [Comment_foto_q] => 75
    [Comment_media] => all
    [Comment_tree] => 1
    [template] => blog

    [SOCIALMEDIA_NO] => *
    [SOCIALMEDIA_ONLY] => VK*,FB

*/

function SETTINGS($e) {
    $conf=parse_e_conf($e); unset($conf['body']);  unset($conf['Body']);
    $GLOBALS['article']=array_merge($GLOBALS['article'],$conf);
    return '';
}

?>