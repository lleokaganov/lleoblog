<?php /* запрет публикации

{_NOPUBL:_} текст
*/

function NOPUBL($e) {
    if(!isset($GLOBALS['PUBL']) || isset($GLOBALS['rssmode'])) return '';
    idie('Publication restricted');
}
?>