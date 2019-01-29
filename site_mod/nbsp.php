<?php /* Убрать переводы строки

Полезно при постингах стихов лесенкой и прочих строк. где в начале надо вставить пробелы.
Все пробелы в начале строки будут заменены на &nbsp;

{_nbsp:
Да я
     Маяковским
               рубил бы
                        ритм
_}
*/



function nbsp($e) { 
	return preg_replace_callback("/\n +/si","nbsp_cb",$e);
}

function nbsp_cb($t) { return str_replace(' ','&nbsp;',$t[0]); }

?>