<?php
/*
Модуль CONTENTER служит для вывода информации из любого подраздела сайта.
Синтаксис: параметр = значение, параметры разделяются переводом строки
1. namespace = -this | -date | %путь%
Указывает подраздел сайта из которого будем выводить статьи. Например, если дать ему значение ololo,
модуль будет выводить статьи из раздела http://sitename.ru/ololo/
Существует два специальных значения: -date и -this. Первое выводит дневниковые заметки,
Второе - заметки из текущего раздела. По умолчанию -this.
2. pager = yes | no
Определяет, показывать ли ссылки для перелистывания страниц, если количество записей больше, чем параметр nskip. По умолчанию yes.
3. nskip = число
Определяет количество записей на странице. По умолчанию 10.
4. next = %шаблон%
Определяет внешний вид ссылки на следующую страницу. По умолчанию <small>&larr;&nbsp;<a href={nextpage}>предыдущие {n}</a></small>
5. prev = %шаблон%
Определяет внешний вид ссылки на предыдущую страницу. По умолчанию <small><a href={prevpage}>следующие {n}</a>&nbsp;&rarr;</small>
6. prevnext = %шаблон%
Внешний вид контейнера для ссылок на предыдущую/следующую страницы. По умолчанию <table width=100%><tr><td align=left>{next}</td><td align=right>{prev}</td></tr></table>
7. comment = %шаблон%
Внешний вид ссылки на комментарии. По умолчанию <div style='text-align: right; font-size:10pt;'><a href={link}#comments>комментариев {ncomm}</a></div>
8. template = %шаблон%
Определяет шаблон статьи. По умолчанию <div style='padding:15px;'><div class='header' id='Header_{num}' style='text-align:left'>{D}.{M}.{Y}&nbsp;&#151;&nbsp;<a href='{link}'>{Header}</a>{edit}<br></div>{zamok}<div id='Body_{num}'>{Body}</div>{comment}</div>
*/

include_once $GLOBALS['include_sys']."_onetext.php";

function CONTENTER($e) { global $httphost;

$conf=array_merge(array(
'namespace'=>'-this',
'pager'=>'yes',
'nskip'=>10,
'next'=>"<small>&larr;&nbsp;<a href={nextpage}>предыдущие {n}</a></small>",
'prev'=>"<small><a href={prevpage}>следующие {n}</a>&nbsp;&rarr;</small>",
'prevnext'=>"<table width=100%><tr><td align=left>{next}</td><td align=right>{prev}</td></tr></table>",
'comment'=>"<div style='text-align: right; font-size:10pt;'><a href={link}#comments>комментариев {ncomm}</a></div>",
'template'=>"<div style='padding:15px;'><div class='header' id='Header_{num}' style='text-align:left'>{D}.{M}.{Y}&nbsp;&#151;&nbsp;<a href='{link}'>{Header}</a>{edit}<br></div>{zamok}<div id='Body_{num}'>{Body}</div>{comment}</div>"
),parse_e_conf($e));

$last_skip = intval($conf['nskip']);
$skip=intval($_GET['skip']);

//Какие записи выбирать из базы?
if($conf['namespace']=='-this') { //Записи текущего раздела
list($path) = explode('?',$GLOBALS['MYPAGE']);
$path = ltrim($path,$httphost);
$path = rtrim($path,'.html../');
$where = "`Date` LIKE '".e($path)."/%'";
} elseif($conf['namespace']=='-date') $where = "`DateDatetime`!='0'"; //Дневниковые заметки
else $where = "`Date` LIKE '".e($conf['namespace'])."/%'"; //Из указанного раздела

//Если в шаблоне нет {Body}, значит из базы берем самый минимум
if(strstr($conf['template'],'{Body}')) { $select = "*"; $fullmode = 1; }
else $select = "`Date`,`Header`,`Access`,`num`";

$oldarticle=$GLOBALS['article'];
$pp=ms("SELECT ".$select." FROM `dnevnik_zapisi` ".WHERE($where).ANDC()." ORDER BY `Date` DESC LIMIT ".e($skip).",".e($last_skip+1),"_a");

//Показывать ли листалку страниц?
if($conf['pager']=='yes') {
$n=sizeof($pp);
if($n>$last_skip){ unset($pp[$n-1]); $next=mper($conf['next'],array('nextpage'=>$mypage."?skip=".($skip+$last_skip),'n'=>$last_skip)); } else $next='';
$n=$skip-$last_skip;
if($n>=0) { $prev=mper($conf['prev'],array('prevpage'=>$mypage."?skip=".$n,'n'=>$last_skip)); } else $prev='';
$prevnext = mper($conf['prevnext'],array('next'=>$next,'prev'=>$prev));
} else $prevnext = '';
$s=$prevnext;


foreach($pp as $p) {
    $GLOBALS['article']=$p;
    $link=get_link_($p["Date"]); // неполная ссылка на статью
    list($Y,$M,$D) = explode('/', $p['Date'], 3); $D=substr($D,0,2);

if($p['Comment_view']!='off' && strstr($conf['template'],'{comment}')) {
   $idzan=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `DateID`='".e($p["num"])."'".ANDC(),'_l'));
   $comment = mper($conf['comment'],array('link'=>$link,'ncomm'=>$idzan,'num'=>$p['num']));
} else $comment = '';

//Обрабатывать ли текст заметки?
if($fullmode) $body = onetext($p); else $body = '';

$s.=mper($conf['template'],array(
'Body'=>$body,
'Header'=>$p["Header"],
'link'=>$link,
'num'=>$p["num"],
'comment'=>$comment,
'Y'=>$Y,'M'=>$M,'D'=>$D,
'zamok'=>zamok($p['Access']),
'edit'=>($GLOBALS['admin']?"<i style='margin: 0 10px 0 10px;' class='knop e_color_line' onClick=\"majax('editor.php',{a:'editform',num:'".$p['num']."',comments:(idd('commpresent')?1:0)})\" alt='editor'></i>":'')
));
}

$GLOBALS['article'] = $oldarticle;
return $s.$prevnext;
}

?>