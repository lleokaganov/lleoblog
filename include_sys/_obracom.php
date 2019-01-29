<?php

//SCRIPTS("cot","function cot(e){e.style.display='none';e.nextSibling.style.display='inline';};");

/*
$commentary=nl2br(htmlspecialchars($commentary));
$commentary=AddBB($commentary);
$commentary="\n$commentary\n";
$commentary=hyperlink($commentary);
$commentary=trim($commentary,"\n");
*/

// die("1");


function link_lj_var($t) {
	$t1=str_ireplace('&quot;','"',$t[1]);
	$t2=str_ireplace('&quot;','"',$t[2]);
	$t1=trim($t1,"'\"\n ");
	$t2=str_ireplace('&lt;wbr&gt;&lt;/wbr&gt;','',trim($t2,"'\"\n "));
	if($t2==$t1) return $t1;
//	idie($t2." ".$t1);
	return $t2." (".$t1.")";
}

function AddBB($s) {

	$s=preg_replace_callback("/&lt;a href=(.*?)&gt;(.*?)&lt;\/a&gt;/si","link_lj_var",$s);

//              $text=
//<a href="http://www.handhelds.org/moin/moin.cgi/GeneratingSyntheticX11Events">
//http://www.handhelds.org/moin/moin.c<wbr></wbr>gi/GeneratingSyntheticX11Events</a>


	$s = str_replace('&quot;','"', $s);

        $search = array(
                '/\[h\](.*?)\[\/h\]/is',
                '/\[b\](.*?)\[\/b\]/is',
                '/&lt;b&gt;(.*?)&lt;\/b&gt;/is',
                '/&lt;strong&gt;(.*?)&lt;\/strong&gt;/is',

                '/\[i\](.*?)\[\/i\]/is',
                '/&lt;i&gt;(.*?)&lt;\/i&gt;/is',
                '/&lt;em&gt;(.*?)&lt;\/em&gt;/is',

                '/\[u\](.*?)\[\/u\]/is',
                '/&lt;u&gt;(.*?)&lt;\/u&gt;/is',

                '/\[s\](.*?)\[\/s\]/is',
                '/&lt;s&gt;(.*?)&lt;\/s&gt;/is',

                '/&lt;quote&gt;(.*?)&lt;\/quote&gt;/is',
                '/&lt;cite&gt;(.*?)&lt;\/cite&gt;/is',

                '/&gt;([^\&\n<]+)/is',

                '/\[img\](.*?)\[\/img\]/is',
                '/\[url\](.*?)\[\/url\]/is',
                '/\[url\=([^\>\<\'\"\=\:\)\(\;\#]*?)\](.*?)\[\/url\]/is'
                );
/*

this.style.display=\\'none\\';this.nextSibling.style.display=\\'inline\\';

*/
        $replace = array(
"<div class=ll onclick='cot(this)'>[...]</div><div style='display:none'>$1</div>",
		'<b>$1</b>',
                '<b>$1</b>',
                '<b>$1</b>',

                '<i>$1</i>',
                '<i>$1</i>',
                '<i>$1</i>',

                '<u>$1</u>',
                '<u>$1</u>',

                '<s>$1</s>',
                '<s>$1</s>',

                '<i><font color=gray>$1</font></i>',
                '<i><font color=gray>$1</font></i>',

                '<font color=gray>&gt;$1</font>',

                ' $1 ',		// '<img src="$1" />',
                ' $1 ',		// '<a href="$1">$1</a>',
		'<a href=\'$1\'>$2</a>'
                );

        $s = preg_replace ($search, $replace, $s);
	$s = str_replace('"','&quot;',$s);
        return $s;
}


function hypermail($s,$k=1) { return preg_replace("/"
."([\s".($k?">":'')."\(\:])" // символы перед [1]
."([0-9a-z\-\_\.]+\@[0-9a-z\-\_\.]+)" // http:// или www. [3]
."(" // символы после
."[\.\?\:][^a-zA-Z0-9\/]"
."|[\s".($k?"<>":'').",\)$]"
.")"
."/si","$1<a href='mailto:$2'>$2</a>$3", $s);
}


function hyperlink($s,$k=1) {

$papki="[a-zA-Z0-9\!\#\$\%\(\)\*\+\,\-\.\/\:\;\=\[\]\\\^\_\`\{\}\|\~]+";
$lastaz="[a-zA-Z0-9\/]";
$quer="[a-zA-Z0-9\!\#\$\%\&\(\)\*\+\,\-\.\/\:\;\=\?\@\[\]\\\^\_\`\{\}\|\~]+";
$lastquer="[a-zA-Z0-9\#\$\&\(\)\*\/\=\@\]\\\^\_\`\}\|\~]";

// http://avatars.yandex.net:80/get-profile-avatar?id=feed.33390&prefix=normal
		
return preg_replace_callback("/"
."([\s>"
//.($k?">":'')
."\(\:])" // символы перед [1]
."(" // [2]
."([a-z]+:\/\/|(www\.))" // http:// или www. [3]
."([0-9a-zA-Z][A-Za-z0-9_\.\-]*[A-Za-z]{2,6})" // aaa.bb-bb.c_c_c [4]
	."(\:\d{1,6}|)" // порт йопта блять или пустота [5]
	."("
//	            ."\/".$papki.$lastaz."\?".$quer.$lastquer // /papka/papka.html?QUERY_STRING#HASH
//		."|"."\?".$quer.$lastquer // ?QUERY_STRING#HASH
//		."|"."\/".$papki.$lastaz // /papka/papka
	            ."\/".$papki.$lastaz."\?".$quer.$lastquer // /papka/papka.html?QUERY_STRING#HASH
		."|"."\?".$quer.$lastquer // ?QUERY_STRING#HASH
		."|"."\/".$papki.$lastaz // /papka/papka

	."|)"
.")"
."(" // символы после
."[\.\?\:][^a-zA-Z0-9\/]"
."|[\s"
.($k?"<>":'')
.",\)$]"
.")"
."/s","url_present", $s);
}

$GLOBALS['media_id']=0;

function url_click($p,$s,$l=0) { $m='media_'.($GLOBALS['media_id']++);

// return '###'.$p[8].'###';

return $p[1]."<div id='$m'>"
."<div title=\"".LL('obracom:click_this')."\" class='l' onclick=\"majax('comment.php',{a:'show_url',type:'$s',url:'".($l===0?$p[2]:$l)."',media_id:'$m'})\">".reduceurl($p[2],60)."</div>"
."</div>".$p[8];

// .'###'.h($p[7]).'###'
// .$p[7];
//return $p[1]."<div class='l' title=\"".LL('obracom:click_this')."\" id='media_".($GLOBALS['media_id']++)."' onclick=\"".$s."\">".reduceurl($p[2],60)."</div>".$p[7];
}

function url_present($p) { global $httpsite,$opt,$media_id,$site_mod;

// if($GLOBALS['admin']) dier($p);

	$o=( !isset($opt)
	or $opt['Comment_media']=='all'
	or $opt['Comment_media']=='my' && $p[3].$p[5]==$httpsite?1:0);

// idie('#'.$p[7]);

	$r=urldecode($p[7]); // if(strstr($r,'?')) $r=explode_first('?',$r);
	if(!strstr($r,'.')) $r=''; else $r=explode_last('.',$r);

// if($GLOBALS['admin']) {
// if(strstr($p[7],'mp3.php')) idie($r.'--------------'.$p[7]);
// }

// $r=(?strtolower(array_pop(($x00=explode('.',$r)))):'');

	if($r=='mp3') { // вставка mp3
		if($o){ include_once $site_mod."MP3.php"; return $p[1].MP3($p[2]." | mp3").$p[8]; }
		else return url_click($p,'mp3');
	}

	if($r=='flv') { // вставка flv
		if($o) return $p[1].'<center><object type="application/x-shockwave-flash" wmode="transparent" data="'
.$GLOBALS['www_design'].'flvplayer.swf?file='.$p[2].'&amp;autostart=false" height="240" width="320">'
.'<param name="movie" value="'
.$GLOBALS['www_design'].'flvplayer.swf?file='.$p[2].'&amp;autostart=false" height="240" width="320">'
.'<param name="wmode" value="transparent"></object></center>'.$p[8];
		else return url_click($p,'flv');
	}

	if(in_array($p[5],array('www.youtube.com','youtu.be','m.youtube.com'))) { // вставка роликов с ютуба
	preg_match("/(v=|youtu\.be\/)([0-9a-z\_\-]+)/si",$p[2],$m);

	$t=''; if(strstr($p[2],"?t=")) { // подсчитать время старта в секундах, если оно указано
	    if(preg_match("/\?t=[\dmsh]*?(\d+)h/si",$p[2],$i)) $t+=$i[1]*60*60;
	    if(preg_match("/\?t=[\dmsh]*?(\d+)m/si",$p[2],$i)) $t+=$i[1]*60;
	    if(preg_match("/\?t=[\dmsh]*?(\d+)s/si",$p[2],$i)) $t+=$i[1];
	    if($t) $t="?start=".$t; else $t='';
	}

	if($o){ 

/*
https://img.youtube.com/vi/<insert-youtube-video-id-here>/0.jpg
https://img.youtube.com/vi/<insert-youtube-video-id-here>/1.jpg
https://img.youtube.com/vi/<insert-youtube-video-id-here>/2.jpg
https://img.youtube.com/vi/<insert-youtube-video-id-here>/3.jpg

The first one in the list is a full size image and others are thumbnail images. The default thumbnail image (ie. one of 1.jpg, 2.jpg, 3.jpg) is:

https://img.youtube.com/vi/<insert-youtube-video-id-here>/default.jpg

For the high quality version of the thumbnail use a url similar to this:

https://img.youtube.com/vi/<insert-youtube-video-id-here>/hqdefault.jpg

There is also a medium quality version of the thumbnail, using a url similar to the HQ:

https://img.youtube.com/vi/<insert-youtube-video-id-here>/mqdefault.jpg

For the standard definition version of the thumbnail, use a url similar to this:

https://img.youtube.com/vi/<insert-youtube-video-id-here>/sddefault.jpg

For the maximum resolution version of the thumbnail use a url similar to this:

https://img.youtube.com/vi/<insert-youtube-video-id-here>/maxresdefault.jpg
*/

return "<div alt='play'>".h($m[2].$t)." 

<div style='position:relative;width:320px;height:180px;display:inline-block;background-image:url(https://img.youtube.com/vi/".h($m[2])."/mqdefault.jpg);'>
<i style='position:absolute;top:70px;left:150px;' class='e_play-youtube'></i>
</div>

</div>";
// return "<div alt='play'>".h($m[2].$t)." <img src='https://img.youtube.com/vi/".h($m[2])."/0.jpg'></div>";
// return '<iframe width="480" height="385" src="//www.youtube.com/embed/'.$m[2].$t.'" frameborder="0" allowfullscreen></iframe>';
		//include_once $site_mod."YOUTUB.php"; return $p[1]."<center>".YOUTUB($m[2].",480,385")."</center>".$p[8];
	} else return url_click($p,'youtub',$m[2]);
	}

	if($p[3]=='www.') $p[2]='http://'.$p[2];
	$l=$p[7];

	if(!strstr($l,'module=') && ( $r=='jpg' or $r=='gif' or $r=='jpeg' or $r=='png'
		or stristr($p[0],'http://pix2.blogs.yandex.net/getavatar')
		or stristr($p[0],'http://avatars.yandex.net')
		)
	) if($o) {

if($GLOBALS['HTTPS']=='https') $p[2]=str_ireplace('http'.substr($GLOBALS['httpsite'],5),'',$p[2]); // патчим для HTTPS

return $p[1]
// .(isset($GLOBALS['IMG_TMPL'])?mpers($GLOBALS['IMG_TMPL'],array('img'=>$p[2])):
.'<img style="max-width:900px;max-height:800px" src="'.$p[2].'"'.(strstr($l,'&amp;prefix=normal')?' align=left hspace=10':'').'>'
// )
.$p[8]; }
	  else return url_click($p,'img');
//url_click($p,"bigfoto('".$p[2]."')");

elseif($p[3]=='area://') $s='<a href="http://fghi.pp.ru/?'.$p[2].'">'.$p[3].$p[5].$l.'</a>';
else $s='<noindex><a href="'.$p[2].'" rel="nofollow">'.reduceurl(maybelink($p[3].$p[5].$l),60).'</a></noindex>';

	return $p[1].$s.$p[8];
}

function reduceurl($s,$l) { if(strlen($s) > $l) $s=substr($s,0,$l)."[...]"; return $s; }

?>