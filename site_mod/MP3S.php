<?php /* Вставить проигрыватель с mp3

{_MP3S:
/VIDEO/mp3/Tikkey/ДКГ Тикки Шельен - Марш на редкость удачных людей.mp3 | Марш на редкость удачных людей
/VIDEO/mp3/Tikkey/ДКГ Тикки Шельен - Маша выбирает яркие цвета.mp3
/VIDEO/mp3/Tikkey/ДКГ Тикки Шельен - Пока не кончится вино.mp3
http://lleo.me/dnevnik/2013/11/Zilant/Tikkey_DURAKI.mp3
 _}

*/

$GLOBALS['audion']++;
SCRIPTS("

function changemp3(url,name,audion){ if(typeof('user_opt')!='undefined') user_opt.s=0;
zabil('audiosrc'+audion,'<div class=br>'+name+'</div><div><audio controls autoplay id=\"audiid'+audion+'\"><source src=\"'+h(url)+'\" type=\"audio/mpeg; codecs=mp3\"><span style=\"border:1px dotted red\">ВАШ БРАУЗЕР НЕ ПОДДЕРЖИВАЕТ MP3, МЕНЯЙТЕ ЕГО</span></audio></div>');
};

");


/*
idd('audiid'+audion).addEventListener('ended', function() {
alert(this.currentTime);
alert('end');
}, false);


медиа-объекты HTML5 имеют другие свойства, доступные только через JavaScript:

    currentSrc описывает медийный файл, который в данный момент воспроизводится браузером, если используются теги-источники;
    videoHeight и videoWidth iзадают исходные размеры видеокадра;
    volume указывает значение в диапазоне от 0 до 1, определяющее уровень громкости (мобильные устройства игнорируют это свойство; в них используются аппаратные регуляторы громкости);
    currentTime задает текущую позицию воспроизведения в секундах;
    duration— общее время длительности медийного файла в секундах;
    buffered — массив, указывающий, какие части медийного файла были скачаны;
    playbackRate — скорость воспроизведения видео (по умолчанию — 1). Измените это значение для ускорения (1.5) или замедления (0.5) воспроизведения;
    ended указывает, достигнут ли конец видео;
    paused всегда равен true при запуске, а затем — false (как только начинается воспроизведение видео);
    seeking указывает, что браузер пытается скачать следующую порцию и переходит в новую позицию.

Медиа-объекты HTML5 также включают следующие методы, применяемые при написании скриптов:

    play пытается загрузить и воспроизвести видео;
    pause останавливает проигрывание текущего видеоролика;
    canPlayType(type) ) распознает, какие кодеки поддерживает браузер. Если вы посылаете некий тип вроде video/mp4, браузер ответит строкой probably, maybe, no или пустой строкой;
    load iвызывается для загрузки нового видео, если вы изменяете атрибут src.

В спецификации HTML5 Media определено 21 событие; вот некоторые из наиболее часто используемых:

    loadedmetadata срабатывает, когда становятся известны длительность и размеры;
    loadeddata срабатывает, когда браузер может начать воспроизведение с текущей позиции;
    play запускает видео, когда оно больше не находится в состоянии paused или ended;.
    playing срабатывает, когда воспроизведение началось после паузы, буферизации или поиска;
    pause останавливает проигрывание видео;
    ended срабатывает, когда достигается конец видео;
    progress указывает, что была загружена очередная порция медийного файла;
    seeking равно true, когда браузер начал поиск;
    seeked равно false, когда браузер закончил поиск;
    timeupdate срабатывает, когда воспроизводится медиа-ресурс;
    volumechange срабатывает, когда изменилось свойство muted или volume.


*/



function MP3Sname($l) { $n=explode('/',$l); $n=$n[sizeof($n)-1]; return str_ireplace('.mp3','',$n); }
function MP3Scby($t) { $GLOBALS['MP3Syoutube'][]=$t[1]; return ''; }

function MP3S($e) { // $e=urldecode($e);

    $e=strtr($e,'—','-');

$tmpl="<tr valign=center><td><img src='".$GLOBALS['www_design']."img/play.png' title='play' onclick='changemp3(\"{#url}\",\"{#name}\",".$GLOBALS['audion'].");'></td><td><a title='Download mp3' href=\"{#url}\">{name}</a>{next}</td></tr>";
$tmpl0="<tr><td colspan=2>&nbsp;</td></tr>";
$tmplb="<tr><td></td><td><b>{name}</b></td></tr>";
$tmpl_youtube=" &nbsp; &nbsp; <a href='http://www.youtube.com/watch?v={code}'>youtube</a>";

$o="<div id='audiosrc".$GLOBALS['audion']."'></div><table border=0 cellspacing=0 cellpadding=2>";

foreach(explode("\n",$e) as $l) { if(c($l)=='') { $o.=$tmpl0; continue; } $next='';
    if(strstr($l,'|')) { list($l,$n)=explode('|',$l); if(c($l)=='') { $o.=mpers($tmplb,array('name'=>$n)); continue; }
	$GLOBALS['MP3Syoutube']=array(); $n=preg_replace_callback("/(\@[^\s]+)/s",'MP3Scby',$n);
	if(str_replace(' ','',$n)=='') $n=MP3Sname($l);

	if(sizeof($GLOBALS['MP3Syoutube'])) foreach($GLOBALS['MP3Syoutube'] as $e) $next.=mpers($tmpl_youtube,array('code'=>substr($e,1)));

    } else { $n=MP3Sname($l); }
    $n=c($n); $l=c0($l);
    $o.=mpers($tmpl,array('url'=>$l,'name'=>$n,'next'=>$next));
}

return $o."</table>";
}

/*
<audio id="player" src="sound.mp3"></audio>
<div>
    <button onclick="document.getElementById('player').play()">Воспроизведение</button>
    <button onclick="document.getElementById('player').pause()">Пауза</button>
    <button onclick="document.getElementById('player').volume+=0.1">Громкость +</button>
    <button onclick="document.getElementById('player').volume-=0.1">Громкость -</button>
</div>


<img src="http://www.w3schools.com/images/compatible_ie.gif" alt="Internet Explorer" title="Internet Explorer" width="31" height="30">
<img src="/images/compatible_firefox.gif" alt="Firefox" title="Firefox" width="31" height="30">
<img src="/images/compatible_opera.gif" alt="Opera" title="Opera" width="28" height="30">
<img src="/images/compatible_chrome.gif" alt="Google Chrome" title="Google Chrome" width="31" height="30">
<img src="/images/compatible_safari.gif" alt="Safari" title="Safari" width="28" height="30">
*/

?>