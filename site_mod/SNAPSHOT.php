<?php
// послать нахуй ЖЖ-шного снапшот-робота

function SNAPSHOT($e) {

if(strstr($GLOBALS['BRO'],"SnapPreviewBot")) { 

	ob_end_clean();

	die("<html><body bgcolor=red><font size=6 color=black><p><br>"
.(rand(0,1)==0?"ДЯДЯ САМЫХ ЧЕСТНЫХ ПРАВИЛ<br>МНЕ В ПИЗДУ ЗАМОК ПОСТАВИЛ<br>НО ЯВИЛСЯ СЛЕСАРЬ ВАСЯ<br>Я ЕМУ И ОТДАЛАСЯ"
		:"наш навязчивый Snap-Shots<br>неразборчиво сосёт-с")
."</body></html>");

}

}

?>