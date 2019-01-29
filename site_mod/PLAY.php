<?php /* Вставить проигрыватель с mp3 или роликом Ютуба

Указывается линк на файл (абсолютная ссылка или относительная).

{_PLAY: http://lleo.aha.ru/dnevnik/img/2008/05/Nikolo_Digalo-The_Little_Man.mp3 _}
{_PLAY: http://lleo.aha.ru/dnevnik/img/2008/05/Nikolo_Digalo-The_Little_Man.mp3 Песенка! _}
{_PLAY: http://lleo.aha.ru/dnevnik/img/2008/05/Nikolo_Digalo-The_Little_Man.mp3 | Песенка! _}
{_PLAY: <iframe width="560" height="315" src="https://www.youtube.com/embed/i13GaWLqpzk" frameborder="0" allowfullscreen></iframe> | Вот ролик _}
{_PLAY: https://youtu.be/BAi6sJ1TJ6k Ролик _}
{_PLAY: BAi6sJ1TJ6k И так тоже можно _}
{_PLAY: /mysite/file.mp4 Можно также играть видео плеером _}
{_PLAY: /mysite/pan1.jpg А если JPG, то проигрывание круговой панорамы _}
*/

function PLAY($e) { $txt=''; if($e=='') return;
    list($e,$txt)=explode(strstr($e,'|')?'|':' ',$e,2); $txt=c0($txt); $e=c($e);
    $e=urldecode($e);

    if(isset($GLOBALS['nett'])) { // SOCIAL NETWORK
        $e=fulllink($e,$GLOBALS['r']['url']);
        if(in_array($GLOBALS['r']['net'],array('telegram','fb','facebook','vk'))) {
	    if(substr($txt,0,1)=='#') { $txt=substr($txt,1); return " ".($txt==''?'':$txt.": ").$e." "; }
	    return "\n".($txt==''?'':$txt.": ").$e."\n";
	}
    }


    $tag='div'; if(substr($txt,0,1)=='#') { $txt=substr($txt,1); $tag='span'; }

/*
    if((strstr($e,'youtu.be/')||strstr($e,'youtube.com/')||!strstr($e,'.'))&&
	    preg_match("/(^|\/)(watch\?v\=|)([^\s\?\/\&]+)($|\"|\'|\?.*|\&.*)/s",$e,$m)) {
    $start=0; if(preg_match("/^[\?\&]t=([\d+hms]+)$/si",$m[4],$sm)) { // ?t=7m40s -> 460 sec
	    preg_match("/(\d+)s/si",$sm[1],$str); $start+=1*$str[1];
	    preg_match("/(\d+)m/si",$sm[1],$str); $start+=60*$str[1];
	    preg_match("/(\d+)h/si",$sm[1],$str); $start+=60*60*$str[1];
    }
    return "<".$tag." class='ll plv' title='Play' onclick='changemp3x(\"".h($m[3])."\",\""
.h($txt)."\",this,\"youtube\",0,\"".$start."\")'><i style='vertical-align:middle;padding-right:10px;' class='e_play-youtube'></i>".($txt==''?h($m[3]):$txt)."</".$tag.">";
    }
*/

    return "<".$tag." class='ll pla' title='Play' onclick='changemp3x(\"".h($e)."\",\""
.h($txt)."\",this)'><img style='vertical-align:middle;padding-right:5px;' src='".$GLOBALS['www_design']."img/play.png' width='22' height='22'>".($txt==''?h($e):$txt)."</".$tag.">";
}

?>