<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй
// rss заметок дневника

$GLOBALS['rssmode']=1;

include_once $include_sys."_onetext.php"; // обработка заметки

$subst1=array("{foto_www_preview}");
$subst2=array($foto_www_preview);

//$RSSZ_skip = 10;
//$RSSZ_mode = 1;

ob_clean();
header("Content-Type: text/xml; charset='".$wwwcharset."'");

$skip=intval($_GET['skip']);

$pp=ms("SELECT `Date`,`Body`,`Header`,`DateUpdate`,`Access`,`num` FROM `dnevnik_zapisi` ".WHERE("`DateDatetime`!=0").ANDC()." ORDER BY `Date` DESC LIMIT ".$skip.",".$RSSZ_skip,"_a");

$s1="<?xml version='1.0' encoding='".$wwwcharset."'?>
<rss version='2.0' xmlns:ya='http://blogs.yandex.ru/yarss/' xmlns:wfw='http://wellformedweb.org/CommentAPI/'>

<channel>
  <title>".$admin_name.": блог</title>
  <lastBuildDate>";

$s="</lastBuildDate>
  <link>".$httphost."</link>
  <description>".$admin_name.": блог</description>
  <generator>Binoniq 3.0</generator>
  <wfw:commentRss>".$httphost."rssc"."</wfw:commentRss>
  <ya:more>".$httpsite.$mypage."?skip=".($skip+$RSSZ_skip)."</ya:more>
  <image>
    <url>".$httphost."design/userpick.jpg"."</url>
    <width>120</width>
    <height>155</height>
  </image>
";

$lastupdate=0; foreach($pp as $p) {
	$lastupdate = max($lastupdate,$p["DateUpdate"]);
	$link=$httphost.$p["Date"].".html"; // полна€ ссылка на статью

		$p['Body']=RSS_zaban($p['Body']); // обработать забаненных
		$Body=onetext($p,1); // обработать текст заметки как положено
		if($RSSZ_mode==1) $Body=RSSZ_mode1($Body,$link); // если в настройках указано не давать полный RSS
		$Body=zamok($p['Access']).$Body; // добавить картинки подзамков
		$Body=str_replace($subst1,$subst2,$Body);

	$Header=$p["Date"].($p["Header"]?" - ".$p["Header"]:"");

$s .= "\n<item>
	<guid isPermaLink='true'>".$link."</guid>
	<author>".$httphost."</author>
	<pubDate>".date("r", $p["DateUpdate"])."</pubDate>
	<link>".$link."</link>
	<description>".h($Body)."</description>
	<title>".h($Header)."</title>
	<comments>".$link."</comments>
</item>\n";
}


$s .= "\n</channel></rss>";


check_if_modified($lastupdate,"$lastupdate"); // врем€ последней модификации (оно же как ETag)

ob_end_clean();
die($s1.date("r",$lastupdate).$s);

//=========================================================================================================

// процедура времени последней модификации
function check_if_modified($date, $etag = NULL) { $cache = NULL;
        if( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) $cache = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $date;
        if( $cache !== false && isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ) {
                $cache = $_SERVER['HTTP_IF_NONE_MATCH'] == '*' || isset( $etag )
                        && in_array( '"'.$etag.'"', explode( ', ',$_SERVER['HTTP_IF_NONE_MATCH'] ) );
                }
        if($cache) { header('HTTP/1.1 304 Not Modified'); ob_clean(); exit(); }
        else {
                header( 'Last-Modified: '.date( DATE_RFC822, $date ) );
                if( isset( $etag ) ) header( 'ETag: "'.$etag.'"' );
        }
}


// функци€ подготовки RSS, если он неполный

function RSS_zaban($s) { global $admin,$IP,$BRO; 	// если это забаненные мудаки, воры и роботы
	if( $IP=='78.46.74.53' // http://feedex.net/view/lleo.aha.ru/dnevnik/rss.xml
		|| strstr($BRO,'eedjack') // BRO='Feedjack 0.9.10 - http://www.feedjack.org/'
		// || $_SERVER["REMOTE_ADDR"]=='79.165.191.215' // Feed43.com? http://feeds.feedburner.com/lleo ?
//		|| strstr($_SERVER["HTTP_USER_AGENT"],'Wget/') // а нехуй вгетом качать!
		|| $IP=='140.113.88.218' || strstr($BRO,'Yahoo Pipes')
	) return "ƒл€ вас полный текст заметки <a href=".mkna("читатель RSS!",
"вы настолько обленились, что вам лень ткнуть в ссылку и вы пытаетесь читать RSS пиратскими способами.",
"ходите и читайте дневник по-человечески.").">находитс€ здесь</a>";
	return $s;
}


function RSSZ_mode1($s,$link) { global $admin,$BRO,$IP;
	$sim=strip_tags(html_entity_decode($s)); // удалить все теги
	// $sim=ereg_replace("{_[^}]*_}",'',$sim); // удалить все модули в фигурных скобках
        $sim=preg_replace("/\{_[^\}]+_\}/si",'',$sim); // удалить все модули в фигурных скобках
	$bukv=round(((strlen($sim))+99 )/100)*100;
	$sim=trim(preg_replace("/^(.{260}[^\.\?\!]*[\.\!\?]).*$/si","$1",$sim))
	."... [<a href='$link'>читать полностью: примерно $bukv символов</a>]\n\n";
	// if(strstr($s,'<img')) $sim .= " + картинки или фотки";
	// if(strstr($s,'<script')) $sim .= " + скрипты какие-то";
	// if(strstr($s,'<object') || strstr($s,'<OBJECT')) $sim .= " + флэш вставлен (может, ролик или музыка?)";
	// $sim .= ".";

	// если это яндекс

	if( strstr($BRO,'Yandex') || $IP=='78.110.50.100' ) return $sim."
\n<p><b>ѕытаетесь читать мой дневник через RSS-ленту яндекса? «десь лишь груба€ текстова€ выжимка
дл€ индексации в поиске - с битыми абзацами, без фоток, картинок, верстки, роликов, скриптов, голосований и прочего.
Ќасто€щую версию моего дневника вы можете прочесть только на моем сайте (причины описаны <a href=".$httphost."/about_rss>здесь</a>).</b>";

	// если это робот трансл€ции ∆∆

	// 204.9.177.18 (): , 'LiveJournal.com (webmaster@livejournal.com; for http://www.livejournal.com/users/lleo_run/; 488 readers)'
	if(strstr($BRO,'LiveJournal.com') )
	return $sim."\n„итатели ∆∆-трансл€ции! ќставл€йте комментарии только на моем сайте, иначе € их не увижу.";

	return $sim;
}

function mkna($name,$prichina,$delat) { // создать ссылку посыла нахуй
	$stroka=$name."%".$prichina."%".$delat;	$stroka=base64_encode($stroka);
	$stroka=str_replace("=","",$stroka); $stroka=str_replace("/","-",$stroka);
	return "http://natribu.org/?".$stroka;
}

?>