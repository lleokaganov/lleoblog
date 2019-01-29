<?php /* ¬ставить флэш-музыкальный ролик

≈сть така€ утилитка под Ћинуксом wav2swf, котора€ делает из wav (в который можно развернуть mp3) короткий swf, который только играет звук и ничего на экране не показывает. Ёто удобно дл€ вс€ких заставок и подачи сигналов.

”казываетс€ линк на файл (абсолютна€ ссылка или относительна€), также через пробел можно указать один необ€зательный аргумент, он может быть таким:

silent - не запускать при загрузке страницы (по умолчанию запускаетс€)
noloop - не зацикливать по кругу (про умолчанию зацикливаетс€)

<script>function sound_off() { idd('music').innerHTML = "<img onclick='sound_on()' src='http://lleo.aha.ru/blog/design/e2/sound_on.gif'>"; } function sound_on() { idd('music').innerHTML = "<img onclick='sound_off()' src='http://lleo.aha.ru/blog/design/e2/sound_off.gif'><object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width=1 height=1 style='width:1px;height:1px;overflow:hidden;position:absolute;left:-400px;top:0;border:0;'><param name=movie value='http://lleo.aha.ru/dnevnik/img/2006/10/2006-10-04.swf' /><param name=loop value='false' /><embed src='http://lleo.aha.ru/dnevnik/img/2006/10/2006-10-04.swf' width=1 height=1 loop=false type='application/x-shockwave-flash'></embed></object>"; }</script>

{_SWFMUZ: http://lleo.aha.ru/dnevnik/img/2006/10/2006-10-04.swf silent_}

*/

function SWFMUZ($e) { list($swf,$e)=explode(' ',$e,2);

if(strstr($e,'noloop')) $incmuz="<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width=1 height=1 style='width:1px;height:1px;overflow:hidden;position:absolute;left:-400px;top:0;border:0;'>"
."<param name=movie value='".h($swf)."' />"
."<param name=loop value='false' />"
."<embed src='".h($swf)."' width=1 height=1 loop=false type='application/x-shockwave-flash'></embed></object>";

else $incmuz="<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' width=1 height=1 style='width:1px;height:1px;overflow:hidden;position:absolute;left:-400px;top:0;border:0;'>"
."<param name=movie value='".h($swf)."' />"
."<embed src='".h($swf)."' width=1 height=1 type='application/x-shockwave-flash'></embed></object>";

return "<script>
function sound_off() { idd('music').innerHTML = \"<img onclick='sound_on()' src='".$GLOBALS['www_design']."e2/sound_on.gif'>\"; }
function sound_on() { idd('music').innerHTML = \"<img onclick='sound_off()' src='".$GLOBALS['www_design']."e2/sound_off.gif'>".$incmuz."\"; }
</script>

".(strstr($e,'silent')?
"<table align=right><td align=right><div id='music'>
<img onclick='sound_on()' src='".$GLOBALS['www_design']."e2/sound_on.gif'>
<div class=br onclick='sound_on()'>включить звук</div>
</div></td></table>"
:
"<table align=right><td align=right><div id='music'>
<img onclick='sound_off()' src='".$GLOBALS['www_design']."e2/sound_off.gif'>
<div class=br onclick='sound_off()'>выключить звук</div>".$incmuz."
</div></td></table>"
);

}

?>