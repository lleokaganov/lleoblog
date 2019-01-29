<?php // почта пользователей

include "../config.php"; $ajax=1; include $include_sys."_autorize.php";

// $db_mailbox="`dnevnik`.`mailbox`";

// if(!$admin) idie('отладочная ошибка - сейчас все заработает снова!');

$a=RE('a'); ADH();

/*
id: int(10) unsigned NOT NULL auto_increment
unicfrom: int(10) unsigned NOT NULL
unicto: int(10) unsigned NOT NULL
timecreate: int(11) unsigned NOT NULL default '0'
timeview: int(11) unsigned NOT NULL default '0'
timeread: int(11) unsigned NOT NULL default '0'
text: text NOT NULL
IPN: int(10) unsigned NOT NULL
BRO: varchar(1024) NOT NULL
whois: varchar(128) NOT NULL
*/



function oneletter($p,$n) { global $box,$mode,$uf;

include_once $GLOBALS['include_sys']."_obracom.php";

	$id=$p['id']; $u=intval($p[$uf]);
	$is=getis($u,'#'); $from=$is['imgicourl'];

$text=nl2br(h($p['text']));
$text=AddBB($text);
$text="\n$text\n";
$text=hyperlink($text);
$text=c($text);
$text=preg_replace("/\{(\_.*?\_)\}/s","&#123;$1&#125;",$text); // удалить подстыковки нахуй из пользовательского текста!
$text=preg_replace("/&amp;(#[\d]+;)/si","&$1",$text); // отображать спецсимволы и национальные кодировки
$text=str_replace('{','&#123;',$text); // чтоб модули не срабатывали
// $p['text']=search_podsveti($text);

	return mpers(get_sys_tmp("mailbox_letter.htm"),array(
		'n'=>(1+$n/*+$nn*/),'u'=>$u,'from'=>$from,'time'=>date("Y-m-d H:i:s",$p['timecreate']),'text'=>$text,'answerid'=>$p['answerid'],
		'img'=>$is['img'],
		'box'=>$box,'mode'=>$mode,'timeread0'=>$p['timeread'],'timeread'=>date("Y-m-d H:i:s",$p['timeread']),'id'=>$id,'whois'=>$p['whois'],
		'BRO'=>$p['BRO'],'IP'=>ipn2ip($p['IPN']))
	);
}

//========================================================================================================================
if($a=='newform') { // окно письма
    if($IS['loginlevel']<3) idie(LL('mailbox:loginlevel3'),'Login level Error'); // доступно только авторизованным
    $u=RE0('unic'); if(!$u) idie(LL('error:unic0'),'Error: unic=0');
    $is=getis($u,'#'); $to=$is['imgicourl'];
    if(!($tmpl=RE('tmpl'))) $tmpl="new"; $tmpl=preg_replace("/[^0-9a-z\-\_]+/si",'',$tmpl);
    $s=mpers(get_sys_tmp("mailbox_".$tmpl.".htm"),array('hid'=>'newmail_'.$u,'unicto'=>$u,'to'=>$to,'text'=>'','answerid'=>0));
    otprav($s);
}

if($a=='new') { // письмо готово
    if($IS['loginlevel']<3) idie(LL('mailbox:loginlevel3'),'Login level Error'); // доступно только авторизованным
    $text=str_replace("\r",'',trim(RE('text'),"\r\n\t ")); if($text=='') otprav("salert('".LL('wherethetext')."',1000);");
    $u=RE0('unicto');
    if(1!=ms("SELECT COUNT(*) FROM ".$db_unic." WHERE `id`='".$u."'","_l",0)) idie("Unic ".$u." not found");
    if(!$unic) idie(LL('error:unic0'),'Error: unic=0');
    include_once $include_sys."geoip.php"; $w=geoip($IP,$IPN); $whois=$w['country'].' '.$w['city'];

    $ara=arae(array(
	'answerid'=>RE0('answerid'),
	'unicfrom'=>$unic,
	'unicto'=>$u,
	'timecreate'=>time(),
	'timeview'=>0,
	'timeread'=>0,
	'text'=>$text,
	'IPN'=>$IPN,
	'BRO'=>$BRO,
	'whois'=>$whois
    ));

if(!ms("SELECT COUNT(*) FROM ".$db_mailbox." WHERE `unicfrom`='".$ara['unicfrom']."' AND `unicto`='".$ara['unicto']."' AND `text`='".$ara['text']."'","_l",0)) msq_add($db_mailbox,$ara);

$js="salert('sent',500);";

if(!empty($GLOBALS['teddyid_nodeid'])) {
    $fromname=str_replace('&nbsp;','',strip_tags($imgicourl)); // от кого
    $p=ms("SELECT `teddyid`,`opt` FROM ".$db_unic." WHERE `id`='".e($u)."'","_1");
    if($p['teddyid']) {
        $opt=mkuopt($p['opt']); // посчитать опции
	if($opt['ttmailnew']==1) {

	$ttxt=$fromname." пишет в личку на сайте ".$httphost.":\n\n".$text;
	$ttxt=substr($ttxt,0,400); // $ttxt=substr($ttxt,0,1000);
	$e=teddyid_opovest($p['teddyid'],$ttxt);
	if(intval($e)) $js="salert('Teddy send',1000);";
}}}

otprav("clean('".RE('hid')."');".$js);

/*
// ================ отправить почтой =============================
$js=''; if($id) { // если это ответ (не в корне коммент), остальное проверит сама процедура
<------>include_once $include_sys."_sendmail.php";
<------>if(0!==($sys=mail_answer($id,$ara))) $js.="salert('mail send: ".njsn($sys['name_parent'])."',1000);";
}
// ===============================================================
*/
}
//========================================================================================================================
if($a=='mail') { // письма

    teddyid_confirm('ttmailarh','Open Mailbox?'); // TeddyId: спрашивать разрешение на чтение архивов личной переписки
    $ty=(RE('teddyid_response')?",teddyid_response:'".RE('teddyid_response')."'".",teddyid_response_date:'".RE('teddyid_response_date')."'".",teddyid_hash:'".RE('teddyid_hash')."'":'');

    $LIM=20;
    $nn=RE0('nn')|0; if($nn<0) $nn=0;
    $limit=RE0('limit')|($LIM+1); if($limit<0 ||  $limit>1000) $limit=0;
    $mode=RE('mode'); if($mode=='')$mode='new';
    $box=RE('box'); if($box=='')$box='in';

    $uf=($box=='in'?'unicfrom':'unicto');

    $pp=ms("SELECT `answerid`,`id`,`timecreate`,`timeread`,`$uf`,`text`,`whois`,`IPN`,`BRO` FROM ".$db_mailbox." WHERE `"
.($box!='in'?'unicfrom':'unicto')."`='".e($unic)."'"
.($mode=='new'?" AND `timeread`='0'":'')
." ORDER BY `timecreate` DESC"
." LIMIT ".e($nn).",".e($limit)
,"_a",0); if(RE0('showbox')!=1 && !sizeof($pp)) otprav('');

$prevnext="<table width=100% border=0><tr><td width=50% align=left class=r>{prev}</td><td width=50% align=right class=r>{next}</td></tr></table>";

// idie(nl2br(h('###'.system("tail -50 /var/log/nginx/error.log"))));

    $s=''; foreach($pp as $n=>$p) { if($n>=$LIM) break; $s.=oneletter($p,$n); }

// idie('###44:'.$s);

    $pn=mpers($prevnext,array(
'next'=>(sizeof($pp)>$LIM?"<div class='r l' onclick=\"majax('mailbox.php',{a:'mail',nn:".($nn+$LIM).",limit:$limit,mode:'$mode'".$ty."})\">следующие&nbsp;-&gt;</div>":''),
'prev'=>($nn?"<div class='r l' onclick=\"majax('mailbox.php',{a:'mail',nn:".($nn-$LIM<0?0:$nn-$LIM).",limit:$limit,mode:'$mode'".$ty."})\">&lt;-&nbsp;предыдущие</div>":''),
));

$s=$pn.$s.$pn;

    otprav("ohelpc('newmail',\"".njsn(
"mailbox: <span title='change: INBOX / OUTBOX' class='l' onclick=\"majax('mailbox.php',{a:'mail',nn:0,limit:$limit,mode:'all',box:'".($box!='in'?'in':'out')."'".$ty."})\">".($box=='in'?'INBOX':'OUTBOX')."</span>"
." / message: "
."<span title='change: new message / all message' class='l' onclick=\"majax('mailbox.php',{a:'mail',nn:$nn,limit:$limit,box:'$box',mode:'".($mode!='new'?'new':'all')."'".$ty."})\">".($mode=='new'?'new':'all')."</span>"
)."\",\"".njsn($s)."\");");
}


if($a=='readed') { // прочитано
    $id=RE0('id'); msq_update($db_mailbox,array('timeread'=>time()),"WHERE `id`='".$id."' AND `unicto`='".h($unic)."'");
    otprav("var i='mmsg".$id."'; clean(i); doclass(i,function(e,l){clean(e);});
setTimeout(\"var e=idd('newmail').getElementsByTagName('BLOCKQUOTE'); if(!e.length) clean('newmail');\",100);");
}

if($a=='delete') { // удалить
    $id=RE0('id'); ms("DELETE FROM ".$db_mailbox." WHERE `id`='".$id."' AND `unicto`='".h($unic)."'",'_l',0);
    otprav("salert('deleted',250); clean('mmsg".$id."');setTimeout(\"var e=idd('newmail').getElementsByTagName('BLOCKQUOTE'); if(!e.length) clean('newmail');\",100);");
}

if($a=='answer') { // ответить
    $id=RE0('id');
    if(false===($p=ms("SELECT `text`,`unicfrom` FROM ".$db_mailbox." WHERE `id`='".$id."' AND `unicto`='".$unic."'","_1",0))) otprav("salert('message not found',1000)");
    msq_update($db_mailbox,array('timeread'=>time()),"WHERE `id`='".$id."' AND `unicto`='".h($unic)."'"); // для начала - оно прочитано
    $u=$p['unicfrom'];
//    $text=preg_replace("/\n(>*)\s*/","\n$1> ","> ".$p['text']);
    $text=preg_replace("/\n\s*/"," ",h($p['text']));
//    $text=h($p['text']);
//    $text=preg_replace("/\n\s*/","\n<br>",h($p['text']));
    $is=getis($u,'#'); $to=$is['imgicourl'];
    $s=mpers(get_sys_tmp("mailbox_new.htm"),array('hid'=>'newmail_'.$u,'unicto'=>$u,'to'=>$to,'text'=>$text,'answerid'=>$id));
    otprav("var i='mmsg".$id."'; clean(i); doclass(i,function(e,l){clean(e);});
setTimeout(\"var e=idd('newmail').getElementsByTagName('BLOCKQUOTE'); if(!e.length) clean('newmail');\",100);".$s);
}

if($a=='parent') { // верхнее
    $id=RE0('id');
    if(false===($parent=ms("SELECT `answerid` FROM ".$db_mailbox." WHERE `id`='".$id."' AND (`unicto`='".$unic."' OR `unicfrom`='".$unic."')","_l",0))) otprav("salert('message not found',1000)");
    if(false===($p=ms("SELECT * FROM ".$db_mailbox." WHERE `id`='".$parent."'","_1",0))) otprav("salert('parent not found',1000)");

    $uf=($p['unicfrom']==$id?'unicfrom':'unicto');

    $s=oneletter($p,000);

    otprav("var i='mmsg".$id."',j='mmsg".$parent."';
    /*if(idd(j)) clean(j);*/

    var c=idd(i).className; if(c=='') { c=i; idd(i).className=c; }
    doclass(c,function(e,l){e.style.marginLeft=(1*e.style.marginLeft.replace(/px/gi,'')+l)+'px';},50);

    var div=document.createElement('DIV');
    div.className=c; div.id=j; div.innerHTML=\"".njsn($s)."\";
    idd(i).parentNode.insertBefore(div,idd(i));
");
}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>