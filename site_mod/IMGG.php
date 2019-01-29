<?php /* Вставка фоток, саморакскрывающихся на экране

Модуль используем при оформлении ссылки на фотку, вставляя в тэг 'a':

Вот <a{_IMGG: http://lleo.me/dnevnik/2012/11/AMR/Sharp-IS01.jpg А это после пробела наачался текст,<br>который будет показан при наведении.<br>В нем можно использовать знаки форматирования._}>моя фотка</a>!

*/

function IMGG($e) { if(strstr($e,' ')) { list($e,$text)=explode(' ',$e,2);
$text=' title="'.$text.'"'; } else $text='';
	return " onclick='return bigfoto(this);' href='".h($e)."'".$text;
}

?>