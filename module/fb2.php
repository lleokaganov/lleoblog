<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй
// fb2 заметок дневника

$GLOBALS['SNOSKI']=array();
function SNOSKA($e) { global $SNOSKI;
    $SNOSKI[]=$e;
    $n=sizeof($SNOSKI);
    return '<a l:href="#footnote'.$n.'" type="note">['.$n.']</a>';
}

function STIH($e) {
    if(strstr($e,"\n\n")) $a=explode("\n\n",$e); else $a=array($e);
    $e=''; foreach($a as $l) {
		if(strstr($l,"\n")) $b=explode("\n",$l); else $b=array($l);
		$l=''; foreach($b as $i) $l.="<v>".$i."</v>";
		$e.="<stanza>".$l."</stanza>";
	}
    return "<p><poem>".$e."</poem>";
}

// <coverpage><image l:href="#cover.png" /></coverpage>
// <image l:href="#picture.jpg"/>
$GLOBALS['fb2bin']=array();
function imgfb2($t) {
    $name=$t[1]; list($a,$b)=explode('://',$name); if($a!='http'&&$a!='https') return '<p>[error img: '.$name.']';
    $file=file_get_contents($name);
    if(empty($file)) return '<p>[error img empty: '.$name.']';
    $bn=basename($name);
    $mimetypes=array('jpg'=>'jpeg','jpeg'=>'jpeg','gif'=>'gif','png'=>'png','bmp'=>'bmp');
    $GLOBALS['fb2bin'][$bn]="<binary content-type=\"image/".$mimetypes[getras($bn)]."\" id=\"".$bn."\">".base64_encode($file)."</binary>";
    return "<image l:href=\"#".$bn."\"/>";
}

include_once $include_sys."_onetext.php"; // обработка заметки

// ob_clean();
// header("Content-Type: text/xml; charset='".$wwwcharset."'");

// $name='E'.$_SERVER['QUERY_STRING'];
$name=$_SERVER['REQUEST_URI']; if(!strstr($name,'?')) die('Empty string');
list(,$name)=explode('?',$name);
// idie('##'.$name);

if($n=intval($name)) $p=ms("SELECT * FROM `dnevnik_zapisi` ".WHERE("`num`='".$n."'").ANDC(),"_1");
else $p=ms("SELECT * FROM `dnevnik_zapisi` ".WHERE("`Date`='".e($name)."'").ANDC(),"_1");
if($p==false) die("Not found: `".h($name)."`");

$s=onetext($p); // обработать текст заметки как положено
$s=prepare_link($s,$p);

$s=preg_replace_callback("/<img\s[^>]*src=[\'\"]*([^\'\"\s>]+).*?>/si",'imgfb2',$s);

// dier($GLOBALS['fb2bin']); // =array();

// $s="<body>".$s."</body>";

if(sizeof($GLOBALS['SNOSKI'])) {
    $s.="\n</section>\n</body>\n\n<body name=\"notes\">";
    foreach($GLOBALS['SNOSKI'] as $n=>$l) $s.="
<section id=\"footnote".(++$n)."\">
    <title><p>".$n."
    </title><p>".$l."
</section>";
//     $s.="\n</body>";
    }

// предобработка
$s=str_ireplace("<p class=br>","<p>",$s);
$s=str_ireplace("</p>","",$s);
$s=str_replace("<p","\n<p",$s);
$s=str_replace("<table","\n<table",$s);




// главы
$s=str_replace("<p class=name>","<p class=z>",$s);

if(strstr($s,"<p class=z>")) {
    $a=explode("<p class=z>",$s); $q='';
    foreach($a as $n=>$l) {
	$l=preg_replace("/^([^\n]+)/si","<title>\n<p>$1\n</title>\n",$l);
	$q.="\n\n<section>\n".$l."</section>";
    } $s=$q;
} else $s="\n\n<section>".$s."</section>";


// вычистить секции из таблиц
$s=preg_replace_callback("/(<table.+?<\/table>)/si",function($t){return str_ireplace(array("<section>","</section>"),'',$t[0]);},$s);

// вычистить из таблицы говна
$s=preg_replace("/<table[^>]+>/si","<table>",$s);
$s=preg_replace("/<table>\s*<td>/si","<table><tr><td>",$s);
$s=preg_replace("/<\/td>\s*<\/table>/si","</td></tr></table>",$s);
$s=preg_replace("/\s*<\/td>\s*/si","\n</td>\n",$s);
$s=preg_replace("/\s*<td>\s*/si","\n<td>\n",$s);
$s=preg_replace("/\s*<table>\s*(.+?)\s*<\/table>\s*/si","\n<empty-line/>\n<empty-line/>\n<table>\n$1\n</table>\n<empty-line/>\n<empty-line/>\n",$s);


// header('Content-Type: text/plain; charset=windows-1251');header('Content-Length: '.strlen($s));ob_clean();flush();die($s);





$s=str_replace("<p class=pd>\n",'',$s);
$s=str_replace("<p class=p>\n",'',$s);
$s=str_replace("<p class=pd>","<empty-line/>\n<p class=d>",$s);
$s=str_ireplace("<p>&nbsp;","\n<empty-line/>",$s);
$s=str_ireplace("<br>","\n<p>",$s);
$s=str_ireplace("<p class=d>","<p>",$s);
$s=str_ireplace("<p>\n",'',$s);




// эпиграфы
$s=preg_replace("/<p class=epigraf>(.+)\n<p class=epigrafp>([^\n]+)/si","<epigraph>\n<p>$1\n<text-author>\n<p>$2\n</text-author>\n</epigraph>",$s);
$s=preg_replace("/<p class=epigraf>(.+)/si","<epigraph>\n<p>$1\n</epigraph>",$s);
$s=preg_replace("/<p class=epigrafp>([^\n]+)\n/si","<epigraph>\n<text-author>\n<p>$1\n</text-author>\n</epigraph>",$s);
// header('Content-Type: text/plain; charset=utf-8');header('Content-Length: '.strlen($s));ob_clean();flush();die(wu($s));
$s=preg_replace("/<p class=podp>([^\n]+)/si","<empty-line/>\n<empty-line/>\n<empty-line/>\n<p><b><i>$1</i></b>",$s);





// $s=str_replace("<p class=podp>","<empty-line/>\n<empty-line/>\n<empty-line/>\n<p>",$s);

// основные абзацы
$s=preg_replace("/<p>([^\n]+)(\n|$)/si","<p>$1</p>$2",$s);

// форматирование
$s=str_replace("<p>* * *</p>","<p><b><i>* * *</i></b></p>",$s);

$s=preg_replace("/<b>([^\n]+)<\/b>/si","<strong>$1</strong>",$s);
$s=preg_replace("/<i>([^\n]+)<\/i>/si","<emphasis>$1</emphasis>",$s);
$s=preg_replace("/<s>([^\n]+)<\/s>/si","<strikethrough>$1</strikethrough>",$s);
$s=preg_replace("/<strike>([^\n]+)<\/strike>/si","<strikethrough>$1</strikethrough>",$s);
$s=preg_replace("/<pre>(.*?)<\/pre>/si","<code>$1</code>",$s);








// убираем все лишние тэги
$s0=$s;
$l=''; foreach(explode("\n","strong
emphasis
strikethrough
code
title
empty-line
epigraph
section
v
p
body
stanza
poem
table
tr
td
image
sup") as $i) $l.="<".$i."></".$i.">";
$s=strip_tags($s,$l);


// финальная чистка
$s=str_replace("</title>\n\n<empty-line/>","</title>",$s);
$s=str_replace("</epigraph>\n<empty-line/>","</epigraph>\n",$s);
$s=str_replace("<section>\n\n<p","<section>\n<p",$s);

$s=str_replace("<v>","\n<v>",$s);
$s=str_replace("<stanza>","\n<stanza>",$s);
$s=str_replace("</stanza>","\n</stanza>",$s);
$s=str_replace("<poem>","\n<poem>",$s);
$s=str_replace("</poem>","\n</poem>\n",$s);






// уносим завершение сеcсии в самый конец тэгов
$stop=1000; while(--$stop && preg_match("/(\s*<\/section>\s*)(<\/[a-z]+>)/si",$s,$m) && $m[2]!='</body>') $s=str_replace($m[1].$m[2],$m[2]."\n</section>",$s);

// $s=str_replace("</section></section>","</section>",$s);
// $s=str_replace("</section></p>","</p>\n</section>",$s);
// $s=str_replace("</section></emphasis></strong></p>","</emphasis></strong></p></section>",$s);

$s=str_replace("\n<p></p>","",$s);



$s=str_replace("&copy;","(c)",$s);
$s=str_replace("&nbsp;"," ",$s);


$tmpl="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<FictionBook xmlns=\"http://www.gribuser.ru/xml/fictionbook/2.0\" xmlns:l=\"http://www.w3.org/1999/xlink\">

<description>

<title-info>
    <genre>prose_contemporary</genre>

    <author>
	<first-name>{FirstName}</first-name>
	<middle-name></middle-name>
	<last-name>{LastName}</last-name>
	<nickname>{nickname}</nickname>
	<email>{email}</email>
    </author>

    <book-title>{Header}</book-title>

    <annotation></annotation>

{IMG}

    <lang>ru</lang>
    <src-lang>ru</src-lang>
</title-info>

<document-info>
   <author>
    <first-name>{FirstName}</first-name>
    <middle-name></middle-name>
    <last-name>{LastName}</last-name>
    <nickname>{nickname}</nickname>
    <email>{email}</email>
   </author>

    <program-used>binoniq-engine http://lleo.me/blog</program-used>

    <date value=\"{date}\">{date}</date>

    <src-url>{url}</src-url>
</document-info>

</description>


<body>
<title><p>{Header}</p></title>

{Body}
</body>

{Binary}

</FictionBook>";


// idie(nl2br(h($s)));

// <coverpage><image l:href="#cover.png" /></coverpage>
// <image l:href="#picture.jpg"/>
$img=''; $bin=''; foreach($GLOBALS['fb2bin'] as $n=>$l) {
    if($img=='') { // первый взять в обложку
	$i="<image l:href=\"#".$n."\"/>";
	$s=str_replace($i,'',$s);
	$img="\t<coverpage>".$i."</coverpage>";
	}
    $bin.="\n".$l."\n";
}

list($firstname,$lastname)=explode(' ',$GLOBALS['admin_name']);

$s=mpers($tmpl,array(
    'Header'=>$p['Header'],
    'FirstName'=>$firstname,
    'LastName'=>$lastname,
    'nickname'=>$GLOBALS['blog_name'],
    'email'=>$GLOBALS['admin_mail'],
    'url'=>getlink($p['Date']),
    'IMG'=>$img, // '<coverpage><image l:href="#cover.png" /></coverpage>',
    'Binary'=>$bin,
    'date'=>date("Y-m-d",$p['DateUpdate']),
    'Body'=>$s
));

// if($GLOBALS['unic']==4) idie("<pre>".h($s));
// idie("<table border=1><tr valign=top><td width=50%>".nl2br(h($s))."</td><td width=50%>".nl2br(h($s0))."</td></tr></table>");

$d=$p['Date']; if(strstr($d,'/')) { $d=explode('/',$d);
    if($d[0]*$d[1]) $d=implode('-',$d);
    else $d=basename($p['Date']);
} $d=$GLOBALS['blog_name'].'-'.$d.'.fb2';

$s=wu($s);

// header('Content-Type: text/plain; charset=utf-8');header('Content-Length: '.strlen($s));ob_clean();flush();die($s);



header('Content-Description: File Transfer');
header('Content-Type: application/x-fictionbook+xml');
header('Content-Disposition: attachment; filename="'.$d.'"');
header('Content-Transfer-Encoding: binary');
// header('Expires: 0');
// header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
// header('Pragma: public');
header('Content-Length: '.strlen($s));
ob_clean(); flush();
die($s);

// Connection: keep-alive^M
// Accept-Ranges: bytes^M
// Content-Length: 221430^M
// Content-Disposition: attachment; filename=Patrik_Ourzhednik__The_Opportune_Moment__1855.fb2^M




//    [Date] => 2015/10/25
//    [Header] => кино - МАРСИАНИН
//    [Body] => <p class=epigraf>{_STIH: Мы не заметили жука
//    [DateUpdate] => 1445844630
//    [view_counter] => 20648
//    [num] => 3161

//	$link=$httphost.$p["Date"].".html"; // полная ссылка на статью
//	$Body=onetext($p,1); // обработать текст заметки как положено
//	$Body=zamok($p['Access']).$Body; // добавить картинки подзамков
//	$Header=$p["Date"].($p["Header"]?" - ".$p["Header"]:"");

// ob_end_clean();
// die($Body);

?>