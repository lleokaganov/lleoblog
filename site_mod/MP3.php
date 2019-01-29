<?php /* Вставить проигрыватель с mp3

Указывается линк на файл (абсолютная ссылка или относительная). Если затем после разделителя | указано mp3, то рядом рисуется ссылка на файл для скачивания.

{_MP3: http://lleo.aha.ru/dnevnik/img/2008/05/Nikolo_Digalo-The_Little_Man.mp3 | mp3  _}

*/

SCRIPTS("playmp3","

var youtubeapiloaded=0;

var mp3imgs={play:'".$GLOBALS['www_design']."img/play.png',pause:'".$GLOBALS['www_design']."img/play_pause.png',playing:'".$GLOBALS['www_design']."img/play_go.gif'};

stopmp3x=function(ee){ ee.src=mp3imgs.play; setTimeout(\"clean('audiosrcx_win')\",50); };

changemp3x=function(url,name,ee,mode){

if(mode=='youtube') return ohelpc('audiosrcx_win','YouTube '+h(name),'<div id=audiosrcx>'+\"<iframe width='640' height='480' src='https://www.youtube.com/embed/\"+h(url)+\"?rel=0&autoplay=1' frameborder='0' allowfullscreen></iframe>\"+'</div>');

var s='<div>'+h(name)+'</div><div><audio controls autoplay id=\"audiidx\"><source src=\"'+h(url)+'\" type=\"audio/mpeg; codecs=mp3\"><span style=\"border:1px dotted red\">ВАШ БРАУЗЕР НЕ ПОДДЕРЖИВАЕТ MP3, МЕНЯЙТЕ ЕГО</span></audio></div>';

if(e=idd('audiidx')) {
    if(-1!=ee.src.indexOf('play_pause')){ ee.src=mp3imgs.playing; return e.play(); }
    if(-1!=ee.src.indexOf('play_go')){ ee.src=mp3imgs.pause; return e.pause(); }
    zabil('audiosrcx',s);
} else { ohelpc('audiosrcx_win','<a class=r href=\"'+h(url)+'\" title=\"download\">'+h(url.replace(/^.*\\//g,''))+'</a>','<div id=audiosrcx>'+s+'</div>'); var e=idd('audiidx'); }

addEvent(e,'ended',function(){ stopmp3x(ee) });
addEvent(e,'pause',function(){ if(e.currentTime==e.duration) stopmp3x(ee); else ee.src=mp3imgs.pause; });
addEvent(e,'play',function(){ ee.src=mp3imgs.playing; });

}
");

function MP3($e) {
    $e=urldecode($e);
    $f=$txt='';
    if(strstr($e,'|')) { list($e,$f)=explode('|',$e,2); $f=c0($f); $e=c($e);
        if(strstr($f,'|')) { list($txt,$f)=explode('|',$f,2); $txt=c0($txt); $f=c($f); }
    }

    if(strstr($e,' ')) { list($e,$txt)=explode(' ',$e,2); $e=c($e); $txt=c($txt); }

    if(isset($GLOBALS['nett'])) {
	$e=fulllink($e,$GLOBALS['r']['url']);
	if(in_array($GLOBALS['r']['net'],array('telegram','fb','facebook','vk'))) return "\n\n".$e."\n<code>$txt</code>\n"; // для постинга в Telegram
    }

    if($f=='play') {
	if((strstr($e,'youtu.be/')||strstr($e,'youtube.com/')||!strstr($e,'.'))&&preg_match("/(^|\/)([^\s\?\/]+)($|\"|\'|\?)/s",$e,$m)) {
	return 
"<img style='vertical-align: middle;' src='".$GLOBALS['www_design']."img/play_youtube.png' width=32 height=32 title='Play Video' onclick='changemp3x(\"".h($m[2])."\",\"".h($txt)."\",this,\"youtube\")'>"
.($txt==''?'':'&nbsp;'.$txt)
;
	}
	return "<img style='vertical-align: middle;' src='".$GLOBALS['www_design']."img/play.png' width=22 height=22 title='play' onclick='changemp3x(\"".h($e)."\",\"".h($txt)."\",this)'>"
.($txt==''?'':'&nbsp;'.$txt)
;
    }


return "<center>"
."<audio controls><source src=\"".$e."\" type='audio/mpeg; codecs=mp3'>"
."<span style='border:1px dotted red'>ОШИБКА ВОСПРОИЗВЕДЕНИЯ: Тут должен быть плеер с файлом ".$e."</span>"
."</audio>"
.($f=='mp3'?"<p class=r><a title='Download file mp3' href=\"".h($e)."\">".h(basename($e))."</a>":'')
."</center>"
;

}

?>