<?php

include_once $GLOBALS['include_sys']."_onetext.php";

$GLOBALS['IMBLOAD_started']=0;


function IMBLOAD($e) {
	$oldarticle=$GLOBALS['article'];
	if($GLOBALS['IMBLOAD_started']++) return "{_CENTER:<b>IMBLOAD<b>_}";
$conf=array_merge(array(
'limit'=>20,
'maxlimit'=>200,
'pre'=>'',
'comment'=>"<div style='text-align: right; font-size:10pt; margin-right: 5px'><a href={link}#comments>комментариев {ncomm}</a> | <a href=\"javascript:majax('comment.php',{a:'comform',id:0,lev:0,comnu:comnum,dat:{num}});\">оставить комментарий</a></div>",
'template'=>"<p>&nbsp;<div style='text-align:justify;padding:0 15px;'><img src='".$GLOBALS['www_design']."/userpick.jpg'><div class='header' id='Header_{num}' style='text-align:left'>{edit}<a href='{link}'>{Y}-{M}-{D}: {Header}</a></div><div id='Body_{num}'>{Body}</div>{comment}</div><p>&nbsp;"
),parse_e_conf($e));

$time=isset($_GET['time'])?intval($_GET['time']):0;
$timeto=isset($_GET['timeto'])?intval($_GET['timeto']):time()+32000;
if(isset($_GET['limit'])) $conf['limit']=min($conf['maxlimit'],intval($_GET['limit']));

/*
if($_GET['act']=='list') {
	$pp=ms("SELECT `Date`,`Header`,`num`,`DateDatetime` FROM `dnevnik_zapisi` ".WHERE("`DateDatetime`>'$time'")." ORDER BY `DateDatetime` DESC LIMIT ".$conf['limit'],"_a");
	$a=array();
	foreach($pp as $p) $a[]="[".$p['DateDatetime'].",".$p['num'].",'".njsn($p['Date']." ".$p['Header'])."']";
	return("
<script>
var imblist=[".implode(',',$a)."];
alert(print_r(imblist));
</script>
");
}

return "";
*/

// $pp=ms("SELECT `opt`,`Date`,`Body`,`Header`,`DateUpdate`,`Access`,`num`,`DateDatetime` FROM `dnevnik_zapisi` ".WHERE("`DateDatetime`>'$time'")." ORDER BY `DateDatetime` DESC LIMIT ".$conf['limit'],"_a");
$pp=ms("SELECT `opt`,`Date`,`Body`,`Header`,`DateUpdate`,`Access`,`num`,`DateDatetime` FROM `dnevnik_zapisi` ".WHERE("`DateDatetime`!='0' AND `DateUpdate`>'$time' AND `DateUpdate`<'$timeto'")." ORDER BY `DateUpdate` DESC LIMIT ".$conf['limit'],"_a");

if(sizeof($pp)) foreach($pp as $p) { $p=mkzopt($p); // $p['template']=$conf['template_name'];
	$GLOBALS['article']=$p;
	$link=get_link_($p["Date"]); // неполная ссылка на статью
	list($Y,$M,$D) = explode('/', $p['Date'], 3); $article["Day"]=substr($article["Day"],0,2);

if($p['Comment_view']!='off' && strstr($conf['template'],'{comment}')) {
   $idzan=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `DateID`='".e($p["num"])."'",'_l'));
   $comment = mper($conf['comment'],array('link'=>$link,'ncomm'=>$idzan,'num'=>$p['num']));
} else $comment = '';

$s.=mper($conf['template'],array(
'Body'=>preg_replace("/(<img[^>]+src\=[\'\"]*)([^\/\:]{4,})/si","$1".$GLOBALS['wwwhost'].$Y."/".$M."/$2",onetext($p)), // обработать текст заметки как положено
'Header'=>$p["Header"],
'link'=>$link,
'num'=>$p["num"],
'comment'=>$comment,
'Y'=>$Y,'M'=>$M,'D'=>$D,
'edit'=>($GLOBALS['admin']?"<i style='margin: 0 10px 0 10px;' class='knop e_color_line' onClick=\"majax('editor.php',{a:'editform',num:'".$p['num']."',comments:(idd('commpresent')?1:0)})\" alt='editor'></i>":'')
));

$cleann='';
} else $cleann.=" window.top.postMessage('NO|'+IMBLOAD_MYID,'http://'+IMBLOAD_TOP); return;";


SCRIPTS('imbload',"

if(window.top !== window.self) {
	var r=window.location.hash.split('|');
	var IMBLOAD_ACT=r[0];
	var IMBLOAD_TOP=r[1];
	var IMBLOAD_MYID=r[2];
}

function raport_imbload() { if(window.top !== window.self) {
        if(IMBLOAD_ACT=='#IMBLOAD') { $cleann
        	window.top.postMessage('HH|'+IMBLOAD_MYID+'|'+getDocH(),'http://'+IMBLOAD_TOP);
	        setTimeout(\"window.top.postMessage('HH|'+IMBLOAD_MYID+'|'+getDocH(),'http://'+IMBLOAD_TOP)\",1000);
	}
}}

page_onstart.push('raport_imbload()');
");


$GLOBALS['article'] = $oldarticle;

return $conf['pre'].$s;
}
?>