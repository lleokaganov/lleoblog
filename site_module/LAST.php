<?php

$GLOBALS['LAST_started']=0;

function LAST($e) {

	$oldarticle=$GLOBALS['article'];

	if($GLOBALS['LAST_started']++) return "{_CENTER:<b>LAST<b>_}";

$conf=array_merge(array(
'author'=>false,
'mode'=>'',
'blog'=>1,
'redirect'=>false,
'nskip'=>5,
'next'=>"<small><a href={nextpage}>&lt;&lt;&nbsp;предыдущие {n}</a></small>",
'prev'=>"<small><a href={prevpage}>следующие {n}</a>&nbsp;&gt;&gt;</small>",
'prevnext'=>"<table width=100%><tr><td align=left>{next}</td><td align=right>{prev}</td></tr></table><p>",
// 'comment'=>"<p align=right><a style='font-size:10pt;' href={link}#comments>Добавить комментарий</a> <small>(сейчас {ncomm} шт)</small></p>",
'comment'=>"<div style='text-align: right; font-size:10pt; margin-right: 5px'><a href={link}#comments>комментариев {ncomm}</a> | <a href=\"javascript:majax('comment.php',{a:'comform',id:0,lev:0,comnu:comnum,dat:{num}});\">оставить комментарий</a></div>",
'template'=>"<div style='text-align:justify;padding:0 15px;'><div class='header' id='Header_{num}' style='text-align:left'>{edit}<a href='{link}'>{Y}-{M}-{D}: {Header}</a></div><div id='Body_{num}'>{Body}</div>{comment}</div><hr width=100% color=green>"
),parse_e_conf($e));

// $oldarticle["Prev"]='http://5dfsdfsdf';
// $oldarticle["Next"]='http://6dfsdfsdf';

if($conf['redirect']!==false) {
if(empty($conf['redirect'])) { // если не указан тэг
	$Date=ms("SELECT `Date` FROM `dnevnik_zapisi` "
.WHERE($conf['blog']?"`DateDatetime`!=0":'')
." ORDER BY `Date` DESC LIMIT 1","_l");
} else {
	$Date=ms("SELECT d.`Date` FROM `dnevnik_zapisi` AS d INNER JOIN `dnevnik_tags` AS t ON t.`num`=d.`num` AND t.`tag`='".e($conf['redirect'])."'"
.WHERE($conf['blog']?"`DateDatetime`!=0":'')
." ORDER BY d.`Date` DESC LIMIT 1","_l");
}
	if(!empty($GLOBALS['msqe'])) die($GLOBALS['msqe']);
	if($GLOBALS['article']['Date']==$Date) return "<font color=red> error: last-redirect </font>"; // защита от саморедиректа
	redirect($GLOBALS['httphost'].$Date.".html".($GLOBALS['admin']?"?redir=".$GLOBALS['article']['Date']:''),302); // на последнюю
}


include_once $GLOBALS['include_sys']."_onetext.php";


if($conf['mode']=='hultura') {
//============================ hultura =============================




function nn_pages($conf,$n,$all) {
    $G=$_GET; unset($G['page']); $getlink=''; foreach($G as $a=>$b) $getlink.="&$a=$b";
    $o=''; for($i=$all;$i>0;$i--) if($i==$n /*|| (!$pag && $i==$pages)*/ ) $o.= "$i "; else $o.="[<a href='".$GLOBALS['mypage']."?page=".$i.$getlink."'>".$i."</a>] ";
    $o=trim($o); if($o=='1') $o=''; return $o;
}

$s='';


$where=WHERE(array(
    ($conf['blog']?"`DateDatetime`!=0":'')
    ,($conf['author']?"`author`='".e($conf['author'])."'":'')
//    ,($GLOBALS['admin']?'':"`DateDatetime`>'".(time()-365*24*60*60)."'")
));

$nums=ms("SELECT COUNT(*) FROM `dnevnik_zapisi` ".$where,'_l');

$all=intval( ($nums+$conf['nskip']-1) / $conf['nskip'] );
$page=intval($_GET['page'])-1;
$skip=$page*$conf['nskip'];
if($page<=0) { $page=$all-1; $skip=max(0,$nums-$conf['nskip']); }

$pp=ms("SELECT * FROM `dnevnik_zapisi` ".$where." ORDER BY `DateDatetime` LIMIT ".e($skip).",".e($conf['nskip']),"_a");

$prevnext=nn_pages($conf,$page+1,$all);

for($i=sizeof($pp)-1;$i>=0;$i--) { $p=mkzopt($pp[$i]); if($p['Date']=='index.htm') continue;
	$GLOBALS['article']=$p;
	$link=get_link_($p["Date"]); // неполная ссылка на статью
	list($Y,$M,$D) = explode('/', $p['Date'], 3); $article["Day"]=substr($article["Day"],0,2);

if($p['Comment_view']!='off' && strstr($conf['template'],'{comment}')) {
   $idzan=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `DateID`='".e($p["num"])."'",'_l'));
   $comment = mper($conf['comment'],array('link'=>$link,'ncomm'=>$idzan,'num'=>$p['num']));
} else $comment = '';

$s.=mper(

str_replace(array('{@','@}'),array('{_','_}'),$conf['template'])

,

array_merge($p,array(
'Body'=>onetext($p),
'Header'=>$p["Header"],
'link'=>$link,
'num'=>$p["num"],
'comment'=>$comment,
'Y'=>$Y,'M'=>$M,'D'=>$D,
'edit'=>($GLOBALS['admin']?"<i style='margin: 0 10px 0 10px;' class='knop e_color_line' onClick=\"majax('editor.php',{a:'editform',num:'".$p['num']."',comments:(idd('commpresent')?1:0)})\" alt='editor'></i>":'')
))

);

}

$GLOBALS['article'] = $oldarticle;

return $s.$prevnext;

//============================ hultura =============================
} else {









// idie('k32');



$LAST_skip=intval($conf['nskip']); // 5;
$skip=intval($_GET['skip']);

/*
idie("SELECT `opt`,`Date`,`Body`,`Header`,`DateUpdate`,`Access`,`num` FROM `dnevnik_zapisi` "
.($conf['blog']?WHERE("`DateDatetime`!=0"):'')
.($conf['author']?WHERE("`author`='".e($conf['author'])."'"):'')
." ORDER BY `Date` DESC LIMIT ".$skip.",".($LAST_skip+1));
*/

$pp=ms("SELECT `opt`,`Date`,`Body`,`Header`,`DateUpdate`,`Access`,`num` FROM `dnevnik_zapisi` "
.($conf['blog']?WHERE("`DateDatetime`!=0"):'')
// .($conf['author']?WHERE("`author`='".e($conf['author'])."'"):'')
." ORDER BY `Date` DESC LIMIT ".$skip.",".($LAST_skip+1),"_a");

}

if(!isset($prevnext)) { // если это был не режим hultura, когда номера страниц перечислены внизу

$n=sizeof($pp);
if($n>$LAST_skip){ unset($pp[$n-1]);
    $lnkn=$mypage."?skip=".($skip+$LAST_skip).$catpn;
    $next=mper($conf['next'],array('nextpage'=>$lnkn,'n'=>$LAST_skip));
} else { $next=''; $lnkn=''; }
$n=$skip-$LAST_skip;
if($n>=0) {
    $lnkp=$mypage."?skip=".$n.$catpn;
    $prev=mper($conf['prev'],array('prevpage'=>$lnkp,'n'=>$LAST_skip));
} else { $prev=''; $lnkp=''; }

$oldarticle["Prev"]=$lnkp;
$oldarticle["Next"]=$lnkn;

$s=$prevnext=mper($conf['prevnext'],array('next'=>$next,'prev'=>$prev));

}

foreach($pp as $p) { $p=mkzopt($p);
	$GLOBALS['article']=$p;
	$link=get_link_($p["Date"]); // неполная ссылка на статью
	list($Y,$M,$D) = explode('/', $p['Date'], 3); $article["Day"]=substr($article["Day"],0,2);

if($p['Comment_view']!='off' && strstr($conf['template'],'{comment}')) {
   $idzan=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `DateID`='".e($p["num"])."'",'_l'));
   $comment = mper($conf['comment'],array('link'=>$link,'ncomm'=>$idzan,'num'=>$p['num']));
} else $comment = '';

$s.=mper($conf['template'],array(
'Body'=>onetext($p),
'Header'=>$p["Header"],
'link'=>$link,
'num'=>$p["num"],
'comment'=>$comment,
'Y'=>$Y,'M'=>$M,'D'=>$D,
'edit'=>($GLOBALS['admin']?"<i style='margin: 0 10px 0 10px;' class='knop e_color_line' onClick=\"majax('editor.php',{a:'editform',num:'".$p['num']."',comments:(idd('commpresent')?1:0)})\" alt='editor'></i>":'')
));

}

$GLOBALS['article'] = $oldarticle;

return $s.$prevnext;
}




/*

//============================================================

function guestbook_out($guestbook,$napage,$pag) { global $IS_EDITOR,$pagex,$SELECT,$order;
<------>$prostynka='';
<------>if(!$pag) $lim=0; else $lim=$pagex-$pag*$napage;
<------>$limit=max($lim,0).",".min($napage,$lim+$napage);

//if($GLOBALS['login']!='lleo') $pp=ms("SELECT * FROM `$guestbook` ".(!$IS_EDITOR?"WHERE `metka`!='screen' ":"")."ORDER BY `time` DESC LIMIT $limit","_a",0);
//else {

               $pp=ms("SELECT * $SELECT ORDER BY `$order` DESC LIMIT $limit","_a",0);

//}
<------>$ws=($pag?true:false);
<------>foreach($pp as $p) $prostynka .= odincomment_all($p,false,$ws);
<------>return $prostynka;
}


function guestbook_pages($guestbook,$pag) { global $pagex,$napage,$mysite;

<------>$G=$_GET; unset($G['page']); $getlink=''; foreach($G as $a=>$b) $getlink.="&$a=$b";
<------>
<------>$pages = intval( ($pagex+$napage-1) / $napage );
<------>$a='';
<------>for($i=$pages;$i>0;$i--) if($i==$pag || (!$pag && $i==$pages) ) $a .= "$i "; else $a .= "[<a href='$mysite/?page=$i$getlink'>$i</a>] ";
<------>$a=trim($a);
<------>if($a=='1') $a='';
        return $a;
}

function redirect($path = "/") {
        if (!headers_sent()) { header("Location: ".$path); exit(); } else print "
<script>
\tlocation.replace(\"".$path."\")
</script>
<noscript>
\t<meta http-equiv=refresh content=\"0;url=\"".$path."\">
</noscript>
";
}

*/

?>