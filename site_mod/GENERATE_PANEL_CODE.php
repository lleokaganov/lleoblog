<?php /* Генерация кода панели */

function GENERATE_PANEL_CODE($e) { 

return "javascript:var%20d=document,o=(d.selection)?d.selection.createRange().text:window.getSelection(),s=d.createElement('script');s.setAttribute('type','text/javascript');s.setAttribute('src','".$GLOBALS['httphost']."ajax/m.php?l='+encodeURIComponent(location)+'&t='+encodeURIComponent(''+o));var%20h=d.getElementsByTagName('head').item(0);h.insertBefore(s,h.firstChild);void(0);";

}
?>