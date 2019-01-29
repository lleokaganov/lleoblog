<?php /* Ролики youtube.com

В некоторых флеш-плеерах (в частности, у меня в Линуксе) необходимо ставить атрибут wmode, иначе плеер youtube лезет на экран поверх всего.
У нас всплывающие окна, поэтому нам это критично. Поэтому этот модуль вставляет ролик грамотно, ему достаточно указать только код (его можно подсмотреть в ссылке) и размеры x y.

{_YOUTUB: 6KmxzYgle2U, 480, 385 _}

{_YOUTUB: 6KmxzYgle2U, 480, 385, autoplay _}

Также можно просто взять ссылку для вставки с ютуба (или вообще любой встраиваемый OBJECT) и забить ее в этот модуль - нужные поля `wmode` модуль вставит сам:

{_YOUTUB: 
<iframe width="560" height="315" src="http://www.youtube.com/embed/HJDv1ENVSw8" frameborder="0" allowfullscreen></iframe>

Или в старом стиле:

<object width="480" height="385"><param name="movie" value="http://www.youtube.com/v/BPesiqR457E?fs=1&amp;hl=ru_RU"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="http://www.youtube.com/v/BPesiqR457E?fs=1&amp;hl=ru_RU" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="480" height="385"></embed></object>
_}

*/


function YOUTUB($e) { $p=explode(",",$e); $e=c($p[0]); $x=intval(c($p[1])); $y=intval(c($p[2]));
	$autoplay=(isset($p[3])?'&autoplay=1':'');

if(!($x*$y)) { if(!stristr($e,'<object ')&&!stristr($e,'<iframe ')) return "YOUTUB: X-Y error! ".$e;

if(stristr($e,'<iframe ')) $e=preg_replace("/(src=[\'\"][^\'\"]+)([\'\"])/s","$1?wmode=transparent$2",$e);
// <iframe src="http://www.youtube.com/embed/VyY1fGD7-cU?wmode=transparent"... </iframe>

return str_ireplace(
array(
'<object ',
'<embed ',
// '<iframe ',
'<param name="movie"',
"<param name='movie'",
'<param name=movie'
),
array(
'<object wmode="transparent" ',
'<embed wmode="transparent" ',
// '<iframe wmode="transparent" '
"<param name='wmode' value='transparent'></param><param name='movie'",
"<param name='wmode' value='transparent'></param><param name='movie'",
"<param name='wmode' value='transparent'></param><param name='movie'"
),$e
);

//	$e=str_ireplace("<object ","<object wmode='transparent' ",$e);
//	$e=str_ireplace("<embed ","<embed wmode='transparent' ",$e);
//	$e=str_ireplace("<param name=\"movie\"","<param name='wmode' value='transparent'></param><param name=\"movie\"",$e);
//	return $e;
}

$v='http://www.youtube.com/v/'.h($e).'&hl=ru&fs=1'.$autoplay;

return "<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' wmode='transparent' width='$x' height='$y'>"
."<param name='movie' value='$v' />"
."<param name='wmode' value='transparent' />"
."<param name='allowFullScreen' value='true' />"
."<param name='allowscriptaccess' value='always' />"
."<embed src='$v' wmode='transparent' type='application/x-shockwave-flash' "
."allowscriptaccess='always' allowfullscreen='true' width='$x' height='$y'></embed></object>";

}

?>