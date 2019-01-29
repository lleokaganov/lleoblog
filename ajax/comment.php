<?php // Комментирование заметки

include "../config.php"; include $include_sys."_autorize.php";
$a=RE('a');

if($a=='unsubscribe') { $ajax=0; $mail=RE('mail'); // мгновенно отписаться
    if(RE('confirm') != substr(sha1(RE('date').'|'.$mail.'|'.$newhash_user),1,16) ) { sleep(5); idie('UNSUBSCRIBE: Error confirm'); }
    $r=ms("SELECT * FROM ".$db_unic." WHERE `mail_comment`=1 AND (`mail`='!".e($mail)."' OR `mailw`='!".e($mail)."')","_a",0);
    if($r==false) idie('UNSUBSCRIBE: mail <b>'.h($mail).'</b> is not subscribed!');
    $tmpl="<br>unic:#{id} last time: {timelast} user: {imgicourl} (realname:`{#realname}` login:`{#login}`)";
    $o=""; foreach($r as $x) { $p=get_ISi($x); $o.=mpers($tmpl,$p);
	msq("UPDATE ".$db_unic." SET `mail_comment`=0 WHERE `id`='".$x['id']."'"); if($msqe!='') { $o.=$msqe; break; }
	$o.="   <font color=green>UNSUBSCRIBED</font>";
    }
    idie($o);
}

// if($admin) idie('отладочная ошибка - сейчас все заработает снова!');

$erorrs=array();
 ADH();

$id=RE0('id');
$comnu=RE0('comnu');
$idhelp='cm'.$comnu;
$lev=RE0('lev');
$dat=RE0('dat');
include $include_sys."_onecomm.php";



//=====================================================================================================================


$select_color_file=$GLOBALS['filehost'].'unic-colors.txt';

function select_color_getbasa() { global $select_color_file; $R=array();
    $s=(is_file($select_color_file)?fileget($select_color_file):''); $s=explode("\n",$s);
    foreach($s as $l) { if(c($l)=='' || !strstr($l,'|')) continue; list($u,$c,$n)=explode('|',$l); $R[c($u)]=array(c($c),c($n)); }
    if(!isset($R['default'])) $R=array_merge(array('default'=>array('EEEEEE','default messages color')),$R);
    if(!isset($R['screen'])) $R=array_merge(array('screen'=>array('CADFEF','hidden messages color')),$R);
    return $R;
}

function select_color_setbasa($R) { global $select_color_file; $s=array();
    foreach($R as $u=>$x) $s[]=$u.'|'.$x[0].'|'.$x[1];
    fileput($select_color_file,implode("\n",$s));
    fileput($select_color_file.".dat",serialize($R));
    if(!is_file($select_color_file)||!is_file($select_color_file.".dat")) idie("Error: file not saved: ".h($select_color_file));
}

function can_i_edit($p) { // разрешено ли редактировать?
    if($GLOBALS['admin']) return 1;
    if($GLOBALS['unic'] != $p['unic']) idie('Comments:not_own');
    if($GLOBALS['comment_time_edit_sec'] && (time()-$p['Time'] > $GLOBALS['comment_time_edit_sec'])) idie("Редактировать можно только в течение ".floor($GLOBALS['comment_time_edit_sec']/60)." минут.");
    if(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `Parent`=".$p['id'],"_l",0)) idie("Редактировать нельзя - уже есть ответы.");
    if($GLOBALS['IS']['capchakarma']>9) idie("Нет, дружище, тебе редактировать свои комментарии нельзя:<br>что написано, то и останется, пусть все видят.");
    return 1;
}

if($a=='select_color_save') { AD();
    $R=select_color_getbasa();
    $c=RE('color');
    if(!preg_match("/^[0-9A-F]{6}$/s",$c)) idie("Error color format: `".h($c)."`");
    $rn=h(str_replace('|','#',$IS['realname']));
    $R[$unic]=array($c,$rn);
    $R=select_color_setbasa($R);
    otprav("clean('select_color');salert('Saved',500);");
}

if($a=='select_color_form') { AD();
    $r=md5(time().rand(0,10000));
	$N=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `unic`='".e($unic)."'","_l",0)); if(!$N) idie('Comments:'.$N);
	if(($p=ms("SELECT * FROM `dnevnik_comm` WHERE `unic`='".e($unic)."' LIMIT ".(rand(0,$N-1)).",1","_1",0))===false) idie('Comments:not_found');
        $opt=ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='".$p['DateID']."'","_1"); $GLOBALS['opt']=mkzopt($opt);
    $R=select_color_getbasa(); $c=(isset($R[$unic])?$R[$unic][0]:'FFFFFF');
	$O=comment_one(ppu($p));
	$O="<div id='out_color' unic=0 name=0 style='background-color:#".h($c).";position:relative;"
	    ."border: 1px solid #bbb; box-shadow: #888 5px 5px 5px; border-radius: 0.8em 0.8em 0.8em 0.8em; overflow: auto; padding: 0.4em 0.4em 0.4em 0.4em; margin: 0.4em 0 0 0.4em;"
	    ."margin-left:0px'>$O</div>";
otprav("
ohelpc('select_color','Select Color',\"".njsn($O."<p>

<div style='position:relative;margin-top:10px;height:250px;'>

<div class='picker' id='primary_block'>

    <div id='line'><div id='arrows'><div class='left_arrow'></div><div class='right_arrow'></div></div></div>

    <div id='block_picker'>
        <img src='".$www_design."select_color/select_color_bgGradient.png' class='bk_img'>
        <div class='circle' id='circle'></div>
    </div>

    <p><input type='button' value='SET' onclick=\"majax('comment.php',{a:'select_color_save',color:document.getElementById('txt_color').value})\">   <input type='text' id='txt_color' size=6 maxlength=6 value='000000'>

</div>

</div>
")."\");

idd('out_color').style.width=((getWinW()-50)*0.8)+'px';
center('select_color');

LOADS(['".$www_design."select_color/select_color.js?$r','".$www_design."select_color/select_color.css?rand=$r'],function(){picker.init('".h($c)."')});
");
// 'alert('unic: ".$unic."\\n(".$IS['realname'].") \\ncolor:')

}

//========================================================================================================================
if($a=='why_hidden_comm') { // почему забанен?
    $u=RE0('unic');
    $e=RE0('e');


    $p=ms("SELECT id,login,openid,admin,realname,mail FROM ".$GLOBALS['db_unic']." WHERE `id`='".e($u)."'","_1");
    $p=get_ISi($p,'<small><i>{name}</i></small>');

$o="Почему этот комментарий забанен?<p>Потому что его автор "
."<span class=l onclick=\\\"majax('login.php',{a:'getinfo',unic:".$p['id']."})\\\">".$p['imgicourl']."</span>"

."<p>Вы можете:"

."<br>— разбанить, а затем перегрузить страницу: "
."<i alt='разбанить' class='e_ledred' onclick=\\\"var s,r=f5_read('ueban'),p=r?r.split(','):[],e=in_array(".$p['id'].",p);if(e){delete(p[e]);s='e_ledgreen';}else{p.push(".$p['id'].");s='e_ledred';}this.className=s;f5_save('ueban',p.join(','));\\\"></i>"
."<br>— все равно <span class=l onclick=\\\"clean('whyban');var i=idd(".h($e).").id.replace(/scc_/g,''),s='scc_'+i;otkryl(s); removeEvent(e.target,'click',restore_comm); comhif5(i,0);\\\">показать</span> этот подлый комментарий"
."<br>— список <span class=l onclick=\\\"majax('comment.php',{a:'my_ueban',ueban:f5_read('ueban')})\\\">моих забаненных</span>"
."<br>— <span class=l onclick=\\\"clean('whyban');clean(".h($e).");removeEvent(e.target,'click',restore_comm);\\\">закрыть</span> окно и убрать с глаз долой"
;

otprav("helps('whyban',\"<fieldset><legend>Почему этот комментарий забанен?</legend>".$o."</fieldset>\");");
}

if($a=='my_ueban') { // мои забаненные
    $ueban=trim(RE('ueban'),','); if($ueban!=preg_replace("/[^\d\,]+/",'',$ueban)) idie('Error u-ban Cookie');
    if($ueban=='') otprav("idie('Ваш список забаненных пуст.<p>Чтобы не видеть комментарии неприятного собеседника,<br>откройте его личную карточку и кликните там `забанить`."
."<p>Учтите, что список забаненных находится в хранилище вашего браузера для даного сайта и не зависит от логина.<br>Если вы обнулите хранилище или воспользуетесь другим браузером, список будет снова пуст."
."');");
    $pp=ms("SELECT id,login,openid,admin,realname,mail FROM ".$GLOBALS['db_unic']." WHERE `id` IN (".e($ueban).")","_a");
    $o=''; foreach($pp as $p) { $p=get_ISi($p,'<small><i>{name}</i></small>');

$o.="<div>"
."<i alt='разбанить' class='e_ledred' onclick=\\\"var s,r=f5_read('ueban'),p=r?r.split(','):[],e=in_array(".$p['id'].",p);if(e){delete(p[e]);s='e_ledgreen';}else{p.push(".$p['id'].");s='e_ledred';}this.className=s;f5_save('ueban',p.join(','));\\\"></i>"
." "
."<span class=l onclick=\\\"majax('login.php',{a:'getinfo',unic:".$p['id']."})\\\">".$p['imgicourl']."</span>"
."</div>"; }


// <span onclick="var s,r=f5_read('ueban'),p=r?r.split(','):[],e=in_array(".$p['id'].",p);if(e){delete(p[e]);s='забанить';}else{p.push(".$p['id'].");s='разбанить?';}this.innerHTML=s;f5_save('ueban',p.join(','));">разбанить</span>

// idie(h("helps('my_ueban',\"<fieldset><legend>мои забаненные</legend>".$o."</fieldset>\");"));

    otprav("helps('my_ueban',\"<fieldset><legend>мои забаненные</legend>".$o."</fieldset>\");");
}
//========================================================================================================================
if($a=='imgban') { AD(); // удалить и забанить картинку
    $u=RE0('u');
    $img=RE('img');
    $file=rpath($GLOBALS['filehost']."user/".$u."/".$img);
    $www=$GLOBALS['httphost']."user/".$u."/".h($img);
    $md5=md5(fileget($file));
    $mm=''; foreach(file($GLOBALS["host_log"]."comment_foto_banned.log") as $l) { $l=c0($l); if(empty($l)) continue;
	list($m5,$f5)=explode(' ',$l,2); $m5=c0($m5); $f5=c0($f5);
	if($m5==$md5) $mm.="<br>".$md5." : `".h($f5)."` `".h($file)."`";
    }
    if(!empty($mm)) otprav("salert(\"banned\",200)");

    if(!is_file($file)) idie("Error: file not found: `".h($file)."`");
    logi("comment_foto_banned.log",$md5.' '.$file."\n");
    if(!unlink($file)||is_file($file)) idie("Error: file NOT DELETE: `".h($file)."`");
    $d=''; $dir=dirname($file);
    if(!sizeof(glob($dir."/*"))) { if(!rmdir($dir)||is_dir($dir)) idie("Error: DIR NOT DELETE: `".h($dir)."`"); else $d="<div>DIR DELETED: `".h($dir)."`</div>"; }

    otprav("salert(\"$file [$md5]$d<div><img src='$www'></div>\",12000);");
}

if($a=='deltroll') { // удалить тролля

    if(!$GLOBALS['podzamok']) idie("error");
    if(false===($u=ms("SELECT `unic` FROM `dnevnik_comm` WHERE `id`='".$id."'","_l",0))) idie("error");
    if(false===($t=ms("SELECT `time_reg` FROM ".$db_unic." WHERE `id`='".e($u)."'","_l",0))) idie("error");
    if(time()-$t >= 84600) idie("timeout");

    logi("comment_deltroll.log",date("Y-m-d_H:i:s")." admin: ".$unic." ".$IS['realname']." id=".$id." u=".$u."\n");
//  idie("опция временно отключена");


if($admin) { // админ может банить
    $banned=array(); foreach(file($GLOBALS["host_log"]."comment_foto_banned.log") as $l) { $l=c0($l); if(empty($l)) continue; list($m5,)=@explode(' ',$l,2); $banned[]=$m5; }

    // ЦЕЛАЯ СИСТЕМА ПО ПОИСКУ ПУСТЫХ ПАПОК
/*
    $pp=ms("SELECT `id` FROM ".$db_unic." WHERE `capchakarma`='255'","_a",0);
    $r=array(); $s='';
    foreach($pp as $n=>$u) { $u=$u['id'];
	$a=array();
	if(!is_dir($GLOBALS['filehost']."user/".$u)) continue;
	    foreach(glob(rpath($GLOBALS['filehost']."user/".$u."/*")) as $l) {
		$a[]=basename($l);
		$md5=md5(fileget($l));
		$w=h($GLOBALS['httphost']."user/".$u."/".basename($l));

		$d='';
		if(in_array($md5,$banned)) {
			if(!unlink($l)||is_file($l)) idie("Error: file NOT DELETE: `".h($l)."`");
			$dir=dirname($l); if(empty(glob($dir."/*"))) { if(!rmdir($dir)||is_dir($dir)) idie("Error: DIR NOT DELETE: `".h($dir)."`"); else $d="DIR DELETED: `".h($dir)."`"; }

			$s.="<div><big>".$u."</big>: $w [$md5] - <font color=red>BANNED $d</font></div>";
		} else $s.="<div><big>".$u."</big>: $w [<span class=ll  onclick=\"if(confirm('ban img?'))"
."majax('comment.php',{a:'imgban',u:'".h($u)."',img:'".h(basename($l))."'})"
."\">$md5</span>]<br><img style='max-width=500px;max-height=500px;' src='$w'></div>";
	    }
	    if(!empty($a)) $r[$u]=$a;
    }
    unset($pp);

    idie($s);
*/

    $p=ms("SELECT `id` FROM `dnevnik_comm` WHERE `unic`='".$u."'","_a");
    $r=''; foreach($p as $i) { $i=$i['id'];

	// $s='';
	foreach(glob($GLOBALS['filehost']."user/".$u."/".$i.".*") as $fot) {
	    $md5=md5(fileget($fot));
	    $s.="<br>".h($fot)." ".$md5;
	    if(!in_array($md5,$banned)) { $s.=" - new"; logi("comment_foto_banned.log",$md5.' '.$fot.' '.date("Y-m-d_H:i:s")."\n"); $banned[]=$md5; }
	    // else { /*$i=array_search($md5,$banned); $s.=" - old";*/  }
	}

//	idie($s);
	logi("comment_ban.log",date("Y-m-d_H:i:s")." user: ".$u." deleted comment: ".$i."\n");
        $r.=del_comm($i)."if(idd('$i'))clean('$i');";
	// $r.="alert('$i');if(idd('$i'))clean('$i');";
    }
//    idie(nl2br(h(fileget($GLOBALS["host_log"]."comment_foto_banned.log"))));

    msq_update($db_unic,array('capchakarma'=>255),"WHERE `id`='".e($u)."'"); // забанить
    otprav($r);
}

    // все остальные только скринить
    $count=ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `unic`='".e($u)."'",'_l',0); // заскринить все его комменты`")
    msq("UPDATE `dnevnik_comm` SET `scr`='1' WHERE `unic`='".e($u)."'"); // заскринить все его комменты
    msq("UPDATE `dnevnik_comm` SET `Text`=CONCAT(`Text`,'".e("\n\n{scr:"
."[b]".date("H:i")." озалуплено by ".$IS['user']."[/b]"
.($count>1?" всего комментариев: ".$count:" за этот единственный комментарий")
."}")."') WHERE `unic`='".e($u)."'");
if($count>1) msq("UPDATE `dnevnik_comm` SET `Text`=CONCAT(`Text`,'".e("{scr:[i]озалуплено именно за этот комментарий[/i]}")."') WHERE `id`='".e($id)."'"); // подписать

    cache_rm(comment_cachename(ms("SELECT `DateID` FROM `dnevnik_comm` WHERE `id`='".$id."'","_l")));
    otprav("salert('Убрано комментариев: ".$count."<br>Чтобы увидеть результат, обнови страницу',2000);");
}




//========================================================================================================================
if($a=='whois') { // принудительно определить
        if(false===($p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0))) otprav("salert('error ID: ".$id."',500);");
	if(!$p['IPN']) otprav("salert('error: 0.0.0.0',500);");
	if(($f=RE0('f')) || trim($p['whois'])=='') {
	    $ip=ipn2ip($p['IPN']); if(empty($ip)) idie("Whois: IP error");
	    include_once $include_sys."geoip.php";
	    if(!RE0('f')) $w=geoip($ip,$p['IPN']); else { $w=ipgeobase($ip); if(!$w || $r['city']=='') $w=geoip_whois($ip); }
	    if(!$w || $r['city']=='') idie('Whois IP error');
	    $p['whois']=$w['country'].' '.$w['city'];
	    msq("UPDATE `dnevnik_comm` SET `whois`='".e($p['whois'])."' WHERE `IPN`='".$p['IPN']."' AND (`whois`='' OR `whois`=' ')");
	}
	otprav_comment($p);
}
//========================================================================================================================
/*
if($a=='sendmemail') { // посетитель отправляет некий емайл самому себе
    list($mail,$confirm)=var_confirmed($IS['mail']);
    if($mail=='') idie("Your mail not defined, set in <a href=\"javascript:majax('login.php',{a:'getinfo'})\">usercard</a>");
    include_once $include_sys."_sendmail.php"; // send_mail_confirm($mail,$name);
    sendmail('my mail',$mail,$mail,$mail,RE('subj'),RE('text'));
    otprav("salert('Read your mail ".$mail."',5000);");
}
*/


// загрузить простыню комментариев к заметке
if($a=='loadcomments') {
	$art=ms("SELECT `opt`,`num` FROM `dnevnik_zapisi` ".WHERE("`num`='$dat'"),"_1");
	$art=mkzopt($art);
	$_GET['screen']=RE("mode");
	$comments_pagenum=RE("page");
	otprav("
	zabil('0',\"".njs(load_comments($art))."\");
	var c=gethash_c(); if(c && idd(c)) { kl(idd(c)); c=document.location.href; document.location.href=c; }
");
}

// загрузить ту страницу комментов, где коммент id
if($a=='loadpage_with_id') { // $id=RE0('id'); $dat
	if(!$id) otprav('');
	$do="if(idd(".$id.")) { kl(idd(".$id.")); var c=document.location.href; document.location.href=c; }";
	$pages=($comments_on_page?ceil(get_idzan1($dat)/$comments_on_page)-1:0); // число страниц комментов
//	if(!$pages) otprav($do); блять загрузить - значит загрузить
	if(($mas=load_mas($dat))===false) idie("err num: $id in $dat");
	$i=0; $n=0; while(isset($mas[$i]) && ($mas[$i]['level']!=1 || ++$n) && $mas[$i++]['id']!=$id){}
	$n=ceil($n/$comments_on_page)-1;
//	if($n==RE0('page')) otprav($do); блять загрузить - значит загрузить
	otprav("majax('comment.php',{a:'loadcomments',dat:$dat,page:$n})");
}

// ======================================================
if(!$unic) {
idie("<b>Ошибка авторизации: unic=0</b>

<p>Вы впервые на этом сайте? Пожалуйста, просто обновите страницу,
<br>и тогда сможете оставлять комментарии. Так сделано специально:
<br>случайным посетителям, которые на сайте первый и последний раз,
<br>недоступны некоторые функции.

<p>Если не впервые - значит, ваш браузер упорно не принимает авторизацию,
<br>которую ему пытается выдать сайт. Как такое могло произойти? Попробуйте
<br>победить паранойю и вернуть браузеру обычные настройки: включите обратно
<br>отключенные куки и скрипты - без них не получится оставить комментарий.
<br>Проверьте, не внесли ли вы этот сайт в список каких-то запретов в своем
<br>браузере. Если вы убедились, что ваши настройки браузера вполне обычные,
<br>и считаете, что это ошибка движка, пожалуйста напишите мне:
<a href=mailto:lleo@aha.ru?subject=Kaganovu_UNIC0_error>lleo@aha.ru</a>
");
}


//========================================================================================================================
// сервер периодически принимает недописанные комментарии и складывает их пользователю в базу
if($a=='autosave') { put_last_tmp(RE('text')); otprav(''); }

//========================================================================================================================
// запросили подгрузить дополнительную панельку
if($a=='loadpanel') { $idhelp=h(RE('idhelp')); $idhelp0=substr($idhelp,0,strlen($idhelp)-1);
        $id=$idhelp0."_textarea";
        $panel=nort(mpers(get_sys_tmp("panel_comm.htm"),
array('id'=>$id,'idhelp'=>$idhelp)));
        otprav("
zabil('".$idhelp."','".njs($panel)."');
idd('".$idhelp."').onclick=function(){return true};
idd('".$id."').focus();");
}

// show_url
if($a=='show_url') { $t=RE('type'); $u=RE('url'); $s='Error media type';
	switch($t) {
	        case 'mp3': include_once $site_mod."MP3.php"; $s=MP3($u); break;
	        case 'youtub': include_once $site_mod."YOUTUB.php"; $s=YOUTUB($u.",480,385,autoplay"); break;
	        case 'img': $s='<img src="'.$u.'" hspace="10">'; break;
        }
	otprav("zabil('".RE('media_id')."',\"".njs($s)."\")");
}
//========================================================================================================================
// запросили подгрузить дополнительную панельку фото
/*
if($a=='loadfoto') {
	$id=h(RE("id"));
	$idh=str_replace("_textarea","",$id);
	$panel="<br><input name='foto' type='file' onchange=\"idd('$id').value=idd('$id').value.replace(/\[IMG\]/gi,'')+'[IMG]'\">";
        otprav("clean('".$id."loadfoto');zabil('".$idh."p',vzyal('".$idh."p')+\"".njsn($panel)."\");");
}
*/

//========================================================================================================================
if($a=='pokazat') { // показать
	$oid=RE("oid"); $id=intval(substr($oid,1));
	$level=($lev/$comment_otstup)+1;

	if(!$id /*or !$dat*/ or substr($oid,0,1)!='o') oalert("WTF?! oid:'".h($oid)."' id:'$id' dat:'$dat'");

$maxcommlevel=$level+2;
        $mas=load_mas($dat); if($mas===false) otprav("clean('$oid')");

$mojnocom=getmojno_comm($dat);

$r=''; $rr="clean('$oid');";

function otdalcomm($p,$id,$mojnocom){ return "
mkdiv(".$p['id'].",\"".njs(comment_one(ppu($p['p']),$mojnocom))."\",'".commclass($p['p'])."',idd(0),idd($id));
var e=idd(".$p['id'].");
e.style.marginLeft='".($p['level']*$GLOBALS['comment_otstup'])."px';
e.style.backgroundColor='#".commcolor($ara)."';
otkryl(".$p['id'].");
";
}
	for($i=0,$max=sizeof($mas);$i<$max;$i++){if($mas[$i]['p']['Parent']==$id){
		$rr.=otdalcomm($mas[$i],$id,$mojnocom);
		$i++; for(;$i<$max;$i++) { if($mas[$i]['level']<$level) break; $r=otdalcomm($mas[$i],$id,$mojnocom).$r; }
	}}

otprav($r.$rr);
}
//========================================================================================================================

if($a=='paren') { // показать коммент
	if(!$id) otprav('');
        $p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0);
        $opt=ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='".$p['DateID']."'","_1"); $GLOBALS['opt']=mkzopt($opt);
otprav("
if(idd('show_parent')) clean('show_parent');
else {
mkdiv('show_parent',\"".njs(comment_one(ppu($p),getmojno_comm($p['DateID']),0 ))."\",'popup');
posdiv('show_parent',mouse_x+10,mouse_y);
}
");
}


if($a=='paren1') { // показать коммент
	if(!$id) otprav('');
	// достанем parent
	$parent=ms("SELECT `parent` FROM `dnevnik_comm` WHERE `id`='$id'","_l",0);
        $p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$parent'","_1",0);
        $opt=ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='".$p['DateID']."'","_1"); $GLOBALS['opt']=mkzopt($opt);

	otprav("
	if(!idd('$parent')) {
		var d=idd('".$id."');
		var l=1*d.style.marginLeft.replace(/px/g,'');
		d.style.marginLeft=(l+50)+'px';
	        var p=document.createElement('DIV');
		p.id=p.name='$parent';
		p.style.marginLeft=l+'px';
		p.style.backgroundColor='#".commcolor($p)."';
		p.innerHTML=\"".njs(comment_one(ppu($p),getmojno_comm($p['DateID']) ))."\";
		idd(0).insertBefore(p,d);
	} else {
		idd('$parent').style.border=(idd('$parent').style.border==''?'5px dotted green':'');
		}
	");
// p.className='".commclass($p)."';

}


if($a=='paren2') { if(!$id) otprav(''); // показать коммент
	$parent=ms("SELECT `parent` FROM `dnevnik_comm` WHERE `id`='$id'","_l",0); // достанем parent
        $p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$parent'","_1",0);
        $opt=ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='".$p['DateID']."'","_1"); $GLOBALS['opt']=mkzopt($opt);

	otprav("
		var i=".$parent.";
		if(idd(i)) { var e='delme_'+i; idd(i).id=e; clean(e); }
		var d=idd('".$id."');
		var l=1*d.style.marginLeft.replace(/px/g,'');
		d.style.marginLeft=(l+50)+'px';

	        var p=document.createElement('DIV');
		p.id=p.name=i; p.style.marginLeft=l+'px';
		p.style.backgroundColor='#".commcolor($p)."';
		p.innerHTML=\"".njs(comment_one(ppu($p),getmojno_comm($p['DateID']) ))."\";
		idd(0).insertBefore(p,d);
		idd(i).style.border='3px dotted #ccc';
		idd(".$id.").style.border='3px dotted green';
		clean('sp".$id."');
	"); // p.className='".commclass($p)."'; 
}


//========================================================================================================================
if($a=='otprav_comment') { otprav_comment(ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0)); } // просто отправить коммент
if($a=='otprav_comment1') { $r=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0);

$r=comment_prep($r,1,0);

otprav("zabil('p".$id."',\"".njsn(h(print_r($r,1)))."\");"); } // просто отправить коммент
//========================================================================================================================

if($a=='plus' || $a=='minus') { // поставить плюсик или минусик
//	if(!$unic) otprav("А у вас всегда куки отключены?");

	if($IS['loginlevel']<3) otprav(nl2br("Ставить плюсы и минусы могут только залогиненные.
Залогиненным считается тот, кто залогинился соцсетью,
либо придумал и вписал в личной карточке логин/пароль,
а затем подтвердил email, получив письмо и пройдя по ссылке.

Все остальные, увы, не считаются залогиненными, и возможность
ставить плюсы-минусы для них отключена. Надеюсь на понимание."));

        $p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0); if($p['unic']==$unic) ktogadilminusom($id); // сам свои
	$golos='golos_'.($a=='plus'?'plu':'min');
	if(false!==msq_add('dnevnik_plusiki',array('commentID'=>$id,'unic'=>$unic,'var'=>$a))) {
	    msq("UPDATE `dnevnik_comm` SET `".$golos."`=`".$golos."`+1 WHERE `id`='$id'"); $p[$golos]++; otprav_comment($p);
	}
	$msqe=''; ob_clean(); // очистить ошибку
	if($a==ms("SELECT `var` FROM `dnevnik_plusiki` WHERE `commentID`='$id' AND `unic`='$unic'",'_l',0)) ktogadilminusom($id);
	msq_update('dnevnik_plusiki',array('var'=>$a),"WHERE `commentID`='$id' AND `unic`='$unic'");
	$golos2='golos_'.($a!='plus'?'plu':'min');
	msq("UPDATE `dnevnik_comm` SET `".$golos."`=`".$golos."`+1,`".$golos2."`=`".$golos2."`-1 WHERE `id`='$id'");
	$p[$golos]++; $p[$golos2]--; otprav_comment($p);
}
//---------- кто гадил ----------------------
function ktogadilminusom($id) {
        $pp=ms("
SELECT r.var,r.unic,a.login,a.openid,a.admin,a.realname,a.mail
FROM `dnevnik_plusiki` AS r, ".$GLOBALS['db_unic']." AS a
WHERE r.commentID='".intval($id)."' AND a.id=r.unic LIMIT 20000","_a");

// $s=$s0='';
$smin=$splu=$spmin=$spplu=''; $km=$kp=0;
foreach($pp as $p) { $p=get_ISi($p,'<small><i>{name}</i></small>'); $c=$p['imgicourl'];
        $c="<span onmouseover='kus(".$p['unic'].")'>$c</span>, ";
        if(($GLOBALS['admin']||$GLOBALS['podzamok']) and $p["admin"]=="podzamok") { if($p['var']=='plus') { $kp++; $spplu.=$c; } else { $km++; $spmin.=$c; } }
        else { if($p['var']=='plus') { $kp++; $splu.=$c;} else { $km++; $smin.=$c; } }
}
otprav("helps('ktominusil',\"<fieldset><legend>кто ставил плюс/минус</legend>"
."<table><tr valign=top><td width=50%><i><b>плюсы $kp</b></i><p><small>".njs(str_replace(', ','<br>',trim($spplu.$splu,', ')))."</small></td>"
."<td width=50%><i><b>минусы $km</b></i><p><small>".njs(str_replace(', ','<br>',trim($spmin.$smin,', ')))."</small></td></tr></table>"
."</fieldset>\");");
}
//========================================================================================================================
if($a=='editsend') { // прислан отредактированный комментарий

	if(($p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0))===false) idie('Comments:not_found');
	can_i_edit($p); // а можно ли редактировать?

	$text=RE("text"); $text=trim($text,"\n\r\t "); $text=str_replace("\r","",$text);
	if($text==$p['Text']) otprav("clean('$idhelp');"); // если текст не изменился - просто закрыть

	$scr=$p['scr']; include_once $GLOBALS['include_sys']."spamoborona.php";

	msq_update('dnevnik_comm',array('Text'=>e($text),'scr'=>$scr),"WHERE `id`='$id'");
	$p['Text']=$text;
	otprav_comment($p,"clean('$idhelp');");
}
//========================================================================================================================
if($a=='del') { // id удалить комментарий
    $p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_l");
    can_i_edit($p); // разрешено ли редактировать/удалять?
    cache_rm(comment_cachename(ms("SELECT `DateID` FROM `dnevnik_comm` WHERE `id`='$id'","_l"))); otprav( del_comm($id) );
    otprav("clean($id)");
}
//========================================================================================================================
if($a=='edit') { // id редактировать комментарий

	if(($p=ms("SELECT `id`,`unic`,`Text`,`Time`,`Name` FROM `dnevnik_comm` WHERE `id`='$id'","_1",0))===false) idie('Comments:not_found');
	can_i_edit($p);

$s="<form name='sendcomment_".$comnu."' onsubmit='cmsend_edit(this,".$comnu.",".$id."); return false;'><div id='co_$comnu'></div>"
."<textarea onkeyup='while(this.scrollTop)this.rows++' id='textarea_".$comnu."' style='border: 1px dotted #ccc; margin: 0; padding: 0;' name='txt' cols=50 rows="
.max(3,page(h($p['Text']),50)).">".h(str_replace("\n",'\\n',$p['Text']))."</textarea>"
."<div><input title='Ctrl+Enter' id='editcomsend_".$comnu."' type=submit value='send'></div>"
."</form>";

if($comment_time_edit_sec && !$admin){
	$delta=$comment_time_edit_sec-(time()-$p['Time']); $dmin=date("i",$delta); $dsec=date("s",$delta);
	$o.="
var comm_red_timeout=function(id,n){ if(!idd('editcomsend_'+id)) return;
        if(!n) { idd('textarea_'+id).style.color='#AAAAAA'; return zakryl('editcomsend_'+id); }
        var N=new Date(); N.setTime(n*1000);
	var sec=N.getSeconds(); if(sec<10) sec='0'+sec;
        idd('editcomsend_'+id).value='Send before: '+N.getMinutes()+':'+sec;
        setTimeout('comm_red_timeout('+id+','+(--n)+')',1000);
}; comm_red_timeout(".$comnu.",".($dmin*60+$dsec-5).");";
} else $o='';

$s="comnum++; helps('".$idhelp."',\"<fieldset id='commentform_".$comnu."'><legend>".($admin?h($p['Name']):"редактирование")."</legend>"
.$s."</fieldset>\"); idd('textarea_".$comnu."').focus();
setkey('enter','ctrl',function(){idd('editcomsend_".$comnu."').click()},false,1);

".$o;

otprav("loadCSS('commentform.css');
cm_mail_validate=function(p) { var l=p.value; return l; };

cmsend_edit=function(t,comnu,id) { majax('comment.php',{a:'editsend',text:t['txt'].value,comnu:comnu,id:id,commenttmpl:commenttmpl}); return false; };

cmsend=function(t,comnu,id,dat,lev) {
    var ara={a:'comsend',comnu:comnu,id:id,dat:dat,lev:lev,commenttmpl:commenttmpl};
    if(t['mail']) ara['mail']=t['mail'].value;
    if(t['nam']) ara['name']=t['nam'].value;
    if(t['txt']) ara['text']=t['txt'].value;
    if(t['capcha']) ara['capcha']=t['capcha'].value;
    if(t['capcha_hash']) ara['capcha_hash']=t['capcha_hash'].value;
    majax('comment.php',ara);
    return false;
};".$s); // otprav_sb('commentform.js',$s);
}

//========================================================================================================================
if($a=='ans') { // запретить-разрешить ответы на этот комментарий
	AD();
	if(($p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0))===false) idie('Comments:not_found');
	$p['ans']=($p['ans']=='u'?'0':($p['ans']=='0'?'1':'u'));
	msq_update('dnevnik_comm',array('ans'=>$p['ans']),"WHERE `id`='$id'");
	otprav_comment($p); // ,"idd($id).className='".commclass($p)."';");
}

//========================================================================================================================
if($a=='scr') { // скрыть-раскрыть этот комментарий
	if( !( ($GLOBALS['comment_friend_scr'] && $podzamok || $admin) ) ) oalert("У тебя нет прав делать это.");
	if(($p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0))===false) idie('Comments:not_found');
	$p['scr']=($p['scr']==1?0:1);
	msq_update('dnevnik_comm',array('scr'=>$p['scr']),"WHERE `id`='$id'");
	otprav_comment($p,"idd('".$id."').style.backgroundColor='#".commcolor($p)."';");
}
//========================================================================================================================
if($a=='rul') { // установить/снять особую метку на этот комментарий
	AD();
	if(($p=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$id'","_1",0))===false) idie('Comments:not_found');
	$p['rul']=($p['rul']==1?0:1);
	msq_update('dnevnik_comm',array('rul'=>$p['rul']),"WHERE `id`='$id'");
	otprav_comment($p); // ,"idd($id).className='".commclass($p)."';");
}

//========================================================================================================================
// if($a=='editpanel') { 	otprav("alert('$a')");	// otprav_sb('commentform.js',$s); }
//========================================================================================================================

if($a=='comsend') { razreshi_comm();
$text=str_replace("\r",'',trim(RE('text'),"\r\n\t ")); if($text=='') $erorrs[]=LL('Comments:empty_comm');

if($IS['user']!=''&&$IS['user_noname']!='noname') $name=$IS['user'];
else {
    $name=trim(RE("name")); $name=preg_replace("/\s+/si",' ',$name);
    if($name=='') $erorrs[]=LL('Comments:empty_name');
    else { // введено новое имя
	$p=ms("SELECT `id` FROM ".$db_unic." WHERE `realname`='".e($name)."'");
	if(sizeof($p)) {
	    $o=''; foreach($p as $l) { $l=intval($l['id']); $o.="   <span class=ll onclick='kus(".$l.")'>".$l."</span>"; }
	    $erorrs[]='Пользователь с таким именем уже зарегистрирован:'.$o.'<br>Залогиньтесь или измените имя.';
	}
    }
}

$mail=mail_validate(RE('mail'));

//=====

if(count($_FILES)>0) {

	$opt=mkzopt(ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='$dat'","_1")); unset($opt['opt']);
//	dier($opt);
//    [Comment_foto_sign] => Фотки новой заметочки
//    [Comment_foto_x] => 40
//    [Comment_foto_q] => 75

	foreach($_FILES as $n=>$FILE) if(is_uploaded_file($FILE["tmp_name"])){

// idie("UPL: ".is_uploaded_file($FILE["tmp_name"]));

	$foto_replace_resize=1; require_once $include_sys."_fotolib.php";
        list($W,$H,$itype)=getimagesize($FILE["tmp_name"]);

// idie("under construction, wait 5 min: ".count($_FILES)."W=$W, H=$H, itype=$itype");

$img=openimg($FILE["tmp_name"],$itype);
        if($img===false) idie(LL('Comments:foto:musor',implode(', ',$foto_rash))); // шо за мусор?
	$imgs=obrajpeg_sam($img,$opt['Comment_foto_x'],$W,$H,$itype,str_ireplace('{name}',$name,$opt['Comment_foto_logo']));
	imagedestroy($img);
	} // else idie("File upload error: ".nl2br(h(print_r($_FILES,1))));
}

// $imgs=array();
// $fname=h($FILE["name"]);
//	$frash=end(explode(".",strtolower($FILE['name'])));
//        if(!preg_match("/\.(jpe*g|gif|png)$/si",$fname)) idie("Это разве фотка?");
//        if(preg_match("/^\./si",$fname)) idie("Имя с точки, да? Бугагага!");
//        if(strstr($fname,'..') or strstr($fname,'/') or strstr($fname,"\\") ) idie("Хакерствуем, лошарик?");
//	elseif(is_file($fotodir.$fname)){$fname.='_'; $k=0; while(is_file($fotodir.$fname.(++$k))){} $fname.=$k;}
//        closeimg($img2,$to,$itype); imagedestroy($img);
//	if(false===obrajpeg($FILE["tmp_name"],$fotodir.$fname,$fotouser_x,$fotouser_q,str_ireplace('{name}',$name,$fotouser_logo))) idie("Что ж ты за мусор всякий мне шлешь?");
//	$text=str_ireplace('[IMG]',"\n".$httphost."user/".$unic."/{comment_id}.".(3)."\n",$text);
//$imgs[]=array(obrajpeg_sam($img,$fotouser_x,$W,$H,$itype,$fotouser_q,str_ireplace('{name}',$name,$fotouser_logo)),$itype);
//        closeimg($img2,$to,$itype); imagedestroy($img);
//	if(false===obrajpeg($FILE["tmp_name"],$fotodir.$fname,$fotouser_x,$fotouser_q,str_ireplace('{name}',$name,$fotouser_logo))) idie("Что ж ты за мусор всякий мне шлешь?");
//	$text=str_ireplace('[IMG]',"\n".$httphost."user/".$unic."/".urlencode($fname)."\n",$text);



//===

if(!sizeof($erorrs)) {

	$ara_kartochka=array(); // сюда будем обновлять данные с карточки

// ============ если нужна проверка капчи ==============
// 0 - по умолчанию новому посетителю, ему надо ввести капчу один раз
// 1 - капча была введена один раз, далее ее требовать не надо
// 2 ... 255 - требовать капчу с этим количеством цифр
if($IS['capchakarma']!=1) {
	$karma=($IS['capchakarma']==0?$GLOBALS['antibot_C']:$IS['capchakarma']);
	include_once $GLOBALS['include_sys']."_antibot.php";
	if(RE('capcha')=='') otprav_error("Введите цифры с картинки в окошечко.");
        if(!antibot_check(RE('capcha'),RE('capcha_hash'))) otprav_error("Неверные цифры с картинки, повторите!",
"zabil('ozcapcha_".$comnu."',\"".njs("<table><tr valign=center><td>
<input maxlength=".$karma." class='capcha' type=text name='capcha'>
<input type=hidden name='capcha_hash' value='".antibot_make($karma)."'></td><td>".antibot_img()."</td></tr></table>")."\");");
	if($IS['capchakarma']==0) $ara_kartochka['capchakarma']=1; // пометить в базе, что капчу однажды ввел
}
// ============ // если нужна проверка капчи ==============
	$scr=0; include_once $GLOBALS['include_sys']."spamoborona.php";

	// $c=ms("SELECT `Comment_screen` FROM `dnevnik_zapisi` WHERE `num`='$dat'","_l");
	$po=ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='$dat'","_1");
	$po=mkzopt($po); $c=$po['Comment_screen'];

	if($c=='screen' or (!$podzamok && $c=='friends-open')) $scr=1;

ADMA(1);
// if(isset($admin_colors[$unic])) $group=$admin_colors[$unic]; else $group=$ADM?1:0;

	$ara=array(
		'Text'=>$text,
			'Mail'=>$mail!=''?$mail:$IS['mail'],
			'Name'=>$name,
//		'group'=>$group, // $admin?3:0,
		'IPN'=>$IPN,
		'BRO'=>$BRO,
// 'whois'
		'DateID'=>$dat,
		'unic'=>$unic,
		'Time'=>time(),
		'scr'=>$scr,
		'Parent'=>$id );

//dier($ara,'####');

// а имеем ли мы право забубенить этот комм?
	$ans=($id==0?'u':ms("SELECT `ans` FROM `dnevnik_comm` WHERE `id`='$id'","_l"));
	if(!$ADM and $ans=='0') idie('Админ запретил отвечать на этот комментарий.');
	if($ans=='u') { $e=getmojno_comm($dat);
		if(!$ADM and $e===false) idie('В этой заметке отвечать нельзя.');
		if(!$ADM and $e=='root' and $id!=0) idie('В этой заметке разрешены комментарии, но не ответы на них.');
	}
// ------------------------------------------

// $IP='83.151.5.155';

    include_once $include_sys."geoip.php"; $w=geoip($GLOBALS['IP'],$GOBALS['IPN']);
    if(!$w || $w['country']=='') $ara['whois']='';  else $ara['whois']=$w['country'].' '.$w['city'];

/*
    include_once $include_sys."_files.php";
    function gettg($s,$n) { return (!preg_match("/<".$n.">([^<>]+)<\/".$n.">/si",$s,$l)?false:$l[1]); }
    $g=fileget_timeout('http://ipgeobase.ru:7020/geo?ip='.$IP,1);
    if(($c=gettg($g,'country'))===false) $ara['whois']='';
    else $ara['whois']=$c."|".gettg($g,'city')."|".gettg($g,'region').", ".gettg($g,'district');
*/
//    idie("<pre>".nl2br(h($ara['whois'])).strlen($ara['whois'])."</pre>".h($g));
// whois: varchar(128) NOT NULL
// 
// <ip-answer><ip value="83.151.5.155"><inetnum>83.151.0.0 - 83.151.15.255</inetnum><country>RU</country><city>Казань</city><region>Республика Татарстан</region><district>Приволжский федеральный округ</district><lat>55.796539</lat><lng>49.108200</lng></ip></ip-answer>
// dier($ara);

	msq_add('dnevnik_comm',arae($ara)); $newid=msq_id(); $ara['id']=$newid;
	del_last_tmp(); // удалить кэш

//===================

if(isset($imgs)) { // если были приложены фотки
	$fotodir=$filehost."user/".$unic."/";
	if(!is_dir($fotodir)){ if(mkdir($fotodir)===false) idie("mkdir `".h($fotodir)."`"); chmod($fotodir,0777); }
	$to="user/$unic/$newid".".".$foto_rash[$itype];
	closeimg($imgs,$filehost.$to,$itype,$opt['Comment_foto_q']);

    // защита от тролля уже после после записи фоточек
    if(is_file($GLOBALS['include_sys']."spamoborona2.php")) include_once $GLOBALS['include_sys']."spamoborona2.php";

	$ara['Text']=str_ireplace("[IMG]","\n".$httphost.$to."\n",$text);
	msq_update('dnevnik_comm',array('Text'=>e($ara['Text'])),"WHERE `id`='$newid'");
}

//===================
//	$ara=ms("SELECT * FROM `dnevnik_comm` WHERE `id`='$newid'","_1",0);
//	$c=njs(comment_one($ara,getmojno_comm($ara['DateID'])));
	$ara['whois']=''; $ara['rul']=$ara['golos_plu']=$ara['golos_min']=0; $ara['ans']='u';

	$GLOBALS['comment_tmpl']=h(RE('commenttmpl')); // темплейт
	$c=njs(comment_one(ppu($ara),getmojno_comm($ara['DateID'])));

// ================= сохраняем данные в карточку =================
	if($IS['realname']=='') { $ara_kartochka['realname']=$IS['realname']=e($name); }

// ================= сохраняем данные в карточку =================
	if($mail!='' && $IS['mail']=='') { $ara_kartochka['mail']=$IS['mail']=e($mail);
		include_once $include_sys."_sendmail.php"; send_mail_confirm($mail,$name);
	}
	if(sizeof($ara_kartochka)) msq_update($db_unic,$ara_kartochka,"WHERE `id`='$unic'");
// ================= сохраняем данные в карточку =================

	cache_rm(comment_cachename($dat));

// ================ отправить почтой =============================
$js=''; if($id) { $p=get_user_toans($ara,$id); if($ara['unic']!=$p['id']) { // если это ответ (не в корне коммент), остальное проверит сама процедура

    // mail
    if($p['mail_comment']==1 && ($m=get_workmail($p)) ){ // и он хочет получать ответы и у него указан и подтвержден mail
	include_once $include_sys."_sendmail.php";
	if(0!==($sys=mail_answer($id,$ara,$p,$m))) $js.="salert('mail send: ".njsn($sys['name_parent'])."',1000);";
    }

    $p['opt']=mkuopt($p['opt']); // посчитать опции

    // TeddyId
    if(!empty($GLOBALS['teddyid_nodeid']) && $p['teddyid']  // если был teddyid
    && ($p['opt']['ttcom'] || $p['opt']['ttcom1'] && $p['group']==1) // и разрешены оповещения при комментах всех или админа
    ) { // если был teddyid и разрешены оповещения
    $ttxt="".$ara['Name']." отвечает в заметке:
".$p['Date']." - ".$p['Header']."

".$ara['Text'];
    // $ttxt=substr($ttxt,0,1000);

    $ttxt=substr($ttxt,0,400);
	$e=teddyid_opovest($p['teddyid'],$ttxt);
	if(intval($e)) $js.="salert('Teddy send',1000);";
	// else idie('Error: '.h($e));
    }

}}
// ===============================================================



otprav("/*WWW*/f_save('comment',''); clean('$idhelp');
".($id?"mkdiv($newid,\"$c\",'".commclass($ara)."',idd(0),idd($id));":"mkdiv($newid,\"$c\",'".commclass($ara)."',idd(0));")."
var e=idd($newid);
e.style.marginLeft='".($lev+25)."px';
e.name='$newid';
e.style.backgroundColor='#".commcolor($ara)."';
otkryl(e);
".($id?'':"window.location=mypage.replace(/#[^#]+$/g,'')+'#$newid';")."
$js
if(typeof(playswf)!='undefined') playswf('".$httphost."/design/kladez/'+((Math.floor(Math.random()*100)+1)%27));
"
////if(typeof(playswf)!='undefined')playswf('http://lleo.me/dnevnik/design/kladez/'+((Math.floor(Math.random()*100)+1)%27));
// .($GLOBALS['admin']?"alert('сбросили кэш заметки #".$dat." = ".comment_cachename($dat)."'); ":'')
);

} else { otprav_error(implode('<br>',$erorrs)); }

}

//=================================== запросили форму ===================================================================

if($a=='comform') { // a:'comform',id:e.id,lev:e.style.marginLeft,comnu:comnum; ответить
 razreshi_comm();

if($dat==0) $dat=ms("SELECT `DateID` FROM `dnevnik_comm` WHERE `id`='$id'","_l",0); if($dat===false) idie("Фатальный сбой.");


//dier($IS);

$ar=array(
'comnu'=>$comnu,'id'=>$id,'dat'=>$dat,'lev'=>$lev,'idhelp'=>$idhelp,
'imgicourl'=>$imgicourl,
'httphost'=>$httphost,
'is_name'=>($IS['user']!=''&&(!isset($IS['user_noname'])||$IS['user_noname']!='noname')),
'capchakarma'=>$IS['capchakarma']
); list($ar['mail'],$ar['mail_confirm'])=var_confirmed($IS['mail']);

if($IS['capchakarma']!=1) { include_once $include_sys."_antibot.php";
    $ar['antibot_karma']=($IS['capchakarma']==0?$GLOBALS['antibot_C']:$IS['capchakarma']);
    $ar['antibot_hash']=antibot_make($ar['antibot_karma']);
    $ar['antibot_img']=antibot_img();
}

    otprav(nor(mpers(get_sys_tmp("comment_new.htm"),$ar)));
}

//=================================== удалить комментарий ===================================================================

function del_comm($id,$l=1,$delcache=1) { $id=intval($id); if(!$id) return " alert('id=0?!');";
	// для начала сбросить кэш этой заметки, НО ТОЛЬКО В ПЕРВЫЙ РАЗ
	if($delcache) cache_rm(comment_cachename(ms("SELECT `DateID` FROM `dnevnik_comm` WHERE `id`='".e($id)."'","_l")));

	if($l and ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `Parent`='$id'","_l",0) ) { // если у него есть потомки - просто пометить
		msq_update('dnevnik_comm',array(
			'Time'=>0,'unic'=>0,'Name'=>'','Mail'=>'','Text'=>'','IPN'=>0,
			'BRO'=>'','whois'=>'','rul'=>'no','ans'=>'0','golos_plu'=>0,
			'golos_min'=>0 ),"WHERE `id`='$id'");

		return "idd($id).innerHTML=''; idd($id).className='cdel';";
	}
	// иначе удалить вообще
	
	$Parent=intval(ms("SELECT `Parent` FROM `dnevnik_comm` WHERE `id`='$id'","_l",0)); // сперва запомнили верхний
	$unic=intval(ms("SELECT `unic` FROM `dnevnik_comm` WHERE `id`='$id'","_l",0)); // узнать unic

	// затем удалили
	ms("DELETE FROM `dnevnik_comm` WHERE `id`='$id'","_l",0);

	// убить все картинки к этому комменту
	//if($unic) { $glob=glob($GLOBALS['filehost']."user/".$unic."/".$id.".*"); foreach($glob as $f) { unlink($f); $d=dirname($f); $glob2=glob($d."/*"); if(empty($glob2)) rmdir($d); } }
	if($unic) {
	    $glo=glob($GLOBALS['filehost']."user/".$unic."/".$id.".*");
	    if(!empty($glo)) foreach($glo as $f) { unlink($f); $d=dirname($f); $kglo=glob($d."/*"); if(!empty($kglo)) rmdir($d); }
	}

	$r=" clean($id);";

	if( ! $Parent // если он был в корне
		or ms("SELECT `Time` FROM `dnevnik_comm` WHERE `id`='$Parent'","_l",0) // или его верхний не удален
		or ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `Parent`='$Parent'","_l",0) // если у верхнего есть другие потомки
	) return $r; // то просто вернуться и удалить один

	return $r.del_comm($Parent,0,0); // иначе повторить удаление с ним
}

function otprav_comment($p,$r='') { $GLOBALS['comment_tmpl']=h(RE('commenttmpl'));
	cache_rm(comment_cachename($p['DateID'])); // сбросить кэш коментов этой записи
	$opt=ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='".$p['DateID']."'","_1");
	$GLOBALS['opt']=mkzopt($opt);
	otprav("idd(".$p['id'].").innerHTML=\"".njs(comment_one(ppu($p),getmojno_comm($p['DateID']) ))."\"; ".$r);
}

function ppu($p) {
$pu=ms("SELECT `capchakarma`,`mail`,`admin`,`openid`,`realname`,`login`,`img` FROM ".$GLOBALS['db_unic']." WHERE `id`='".$p['unic']."'","_1",0);
return get_ISi(array_merge($pu,$p));
}

function getmojno_comm($num) {
	$p=ms("SELECT `opt`,`DateDatetime`,`num` FROM `dnevnik_zapisi` WHERE `num`='".e($num)."'","_1");
	$p=mkzopt($p);
	$p['counter']=get_counter($p);
	return mojno_comment($p);
}

function otprav_error($s,$p='') { global $comnu; otprav("zabil('co_".$comnu."',\"<div class=e>".njs($s)."</div>\");".$p); }

//=================================== запросили форму ===================================================================
function send_comment_form($text,$id,$lev,$comnu) { // {a:'comform',id:e.id,lev:e.style.marginLeft,comnu:comnum}); } // ответить

razreshi_comm();

$s="<form name='sendcomment' onsubmit='cmsend(this,".$comnu.",".$id.",".$dat.",".$lev."); return false;'><div id='co_$comnu'></div>";

$s.= "<div><div class=l1>"
.($IS['user']!=''&&$IS['user_noname']!='noname'?$imgicourl:"имя: <input name='name' class='in' type='text'>")."
<div id='".$idhelp."p' style='display:inline; margin-left: 3px;'><i onclick=\"majax('comment.php',{a:'loadpanel',idhelp:'".$idhelp."'})\" class='e_finish' alt='panel'></i></div>
</div><div class=l2>"
.($IS['mail']!=''?"<acronym title='ответы придут на ".h($IS['mail'])."'><i class='e_mail' align=right></i></acronym>"
:"mail: <input name='mail' class=in type=text onkeyup='this.value=cm_mail_validate(this)'>"
)."</div>
<br class=q /></div>";

if($IS['capchakarma']!=1) { include_once $include_sys."_antibot.php";
	$karma=($IS['capchakarma']==0?$GLOBALS['antibot_C']:$IS['capchakarma']);
$s.="<div><div class=l1>".($IS['capchakarma']==0?"вы впервые на сайте<br>":'')."подтвердите, что вы не робот:</div>
<div class=l2 id='ozcapcha_$comnu'><table><tr valign=center><td><input maxlength=$karma class='capcha'
type=text name='capcha'><input type=hidden name='capcha_hash' value='".antibot_make($karma)."'></td>
<td>".antibot_img()."</td></tr></table></div><br class=q /></div>";
}

}

function razreshi_comm() { global $max_comperday,$unic,$admin;
// if($GLOBALS['podzamok']) return; // друзьям можно писать комментарии неограничено
    if($admin) return; // админу можно писать комментарии неограничено
	if(!$max_comperday) return;
	$time=time();
	$p=ms("SELECT `Time` FROM `dnevnik_comm` WHERE `unic`='".e($unic)."' AND `Time`>'".($time-86400)."' ORDER BY `Time` LIMIT ".e($max_comperday)."","_a",0);
	if(sizeof($p)<$max_comperday) return;

$to=$p[0]['Time']+86400;
idie("Допустимое количество комментариев от человека в сутки — $max_comperday
<br>Сейчас ".date("H:i",$time).", новый комментарий можно оставить "
.(date("d",$time)!=date("d",$to)?"завтра":"сегодня")." после ".(date("H:i",$to)) );
}


function get_user_toans($ara,$id) {
$p=ms("SELECT z.`Header`,z.`Date`,c.Time,c.Text,u.login,u.teddyid,u.openid,u.realname,u.img,u.mail,u.mailw,u.mail_comment,u.id,u.opt,c.*
 FROM `dnevnik_zapisi` AS z, `dnevnik_comm` AS c
 LEFT JOIN ".$GLOBALS['db_unic']." AS u ON c.`unic`=u.`id`
 WHERE z.`num`='".$ara['DateID']."' AND c.`id`='".$id."'","_1",0);

// dier($p);

return get_ISi($p);
}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>