<?php /* запрет публикации - просто скрывающий содержимое

{_NOPUBLE:_} текст
*/

function NOPUBLE($e) { return (isset($GLOBALS['PUBL'])?'':$e); }
?>