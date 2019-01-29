<?php /* Экранировать html и отквоттить

То же самое, что и модуль neh, только вдобавок вся конструкция заключается в рамочку.
Полезно при постингах кода.


{_nehb:Я использую тэги <b></b> и <s></s> _}

*/

function nehb($e) { return "<blockquote style='border: 1px dashed rgb(255,0,0); margin-left: 50px; margin-right: 50px; background-color: rgb(255,252,223); text-align:left;'>".h($e)."</blockquote>"; }

?>