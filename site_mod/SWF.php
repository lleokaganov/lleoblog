<?php /* Вставить флэш-объект без ручной возни с тэгами

Через пробел указываются размеры X,Y, также можно указать noloop, чтобы ролик не зацикливался по умолчанию.

{_SWF: http://lleo.aha.ru/dnevnik/2010/09/philips.swf 900 600 noloop _}

*/

function SWF($e) { list($swf,$x,$y,$loop)=explode(' ',$e,4); $swf=h($swf); $x=intval($x); $y=intval($y);

if($x*$y==0) return "<font color=red> Module SWF error: x or y = 0!</font>";

return "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' wmode='transparent' width='$x' height='$y'>"
."<param name='wmode' value='transparent' />"
."<param name='movie' value='$swf' />"
.($loop=='noloop'?"<param name='loop' value='false' />":'')
."<embed type='application/x-shockwave-flash' src='$swf' wmode='transparent' width='$x' height='$y'"
.($loop=='noloop'?" loop='false'":'')."></embed></object>";
}

?>