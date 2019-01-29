<?php // Авторизация пользователей

include "../config.php";

function mail_sendconfirm($mail) { include_once $GLOBALS['include_sys']."_sendmail.php";
	if(!mail_validate($mail)) idie("Неверный формат mail!");
	$realname=imgicourl_text($GLOBALS['imgicourl']);
	send_mail_confirm($mail,$realname);
	return "salert('Проверьте почту $mail',1000);";
}

function tel_validate0($s) {
    if(substr($s,0,1)!='+') idie("Номер `".h($s)."` в международном формате должен начинаться на +");
    if(strlen($s)<10) idie("Подозрительно короткий номер: ".h($s));
    if(strlen($s)>15) idie("Подозрительно длинный номер: ".h($s));
    if(!preg_match("/^\+\d{11,15}$/s",$s)) idie("Номер должен состоять из цифр: ".h($s));
    return tel_validate($s);
}

function tel_sendconfirm($tel) { return; }



//============================================================================================

include_once $include_sys."_autorize.php"; $a=RE('action').RE('a'); ADH();


//=========================================================
if($a=='memcache_flush') { AD(); if(!$memcache) otprav("salert('Memcache not found',3000)");
    $ok=system("echo 'flush_all' | nc localhost 11211");
    otprav("salert('Memcache flushed: ".(memcache_flush($memcache)?'done':'error')." : ".h($ok)."',3000)");
}

//=========================================================
if($a=='send_me_pass') {

list($mail,$confirm)=var_confirmed($IS['mail']);

//if($IS['mail'])
// otprav("alert('Ну и иди нахуй бгг!')");
// $IS['mail']
idie("На ваш изначальный email выслана инструкция по смене пароля. # $mail , $confirm");

}
//=========================================================
if($a=='secret_link') { ADMA();
	$Date=RE('Date');
	$Y=RE0('Y'); $M=sprintf("%02d",RE0('M')); $D=sprintf("%02d",RE0('D'));
	$H=sprintf("%02d",RE0('H')); $I=sprintf("%02d",RE0('I')); $S=sprintf("%02d",RE('S'));

// idie(" $Y,$M,$D,$H,$I,$S ");

	if(!$Y) list($Y,$M,$D,$H,$I,$S)=explode('-',date("Y-m-d-H-i-s",strtotime("+3 day")));

	$o="<form onsubmit=\"return send_this_form(this,'login.php',{a:'secret_link',Date:'".h($Date)."'})\">Before:";
	$a=array(); for($i=1;$i<=31;$i++) $a[$i]=sprintf("%02d",$i); $o.=selecto('D',$D,$a,"name");
	$a=array(); for($i=1;$i<=12;$i++) $a[$i]=$GLOBALS['months_rod'][$i]; $o.=selecto('M',$M,$a,"name");
	$a=array(); for($i=$Y;$i<($Y+10);$i++) { $l=sprintf("%04d",$i); $a[$l]=$l; } $o.=selecto('Y',$Y,$a,"name")." &nbsp; ";
	$a=array(); for($i=0;$i<24;$i++) $a[$i]=sprintf("%02d",$i); $o.=selecto('H',$H,$a,"name").":";
	$a=array(); for($i=0;$i<60;$i++) $a[$i]=sprintf("%02d",$i); $o.=selecto('I',$I,$a,"name").":";
	$a=array(); for($i=0;$i<60;$i++) $a[$i]=sprintf("%02d",$i); $o.=selecto('S',$S,$a,"name");

	$Before="$Y-$M-$D--$H-$I-$S";
	$link=getlink($Date).'?hash='.urlencode(get_hashlink($Date." ".$Before,$acc))."&before=".urlencode($Before);

	otprav("ohelpc('secret_hash','secret link',\"".njsn($o)."<p>Пришлите ссылку тому, кому хотите показать эту скрытую страницу:"
."<p><input type='text' size='".(strlen($link)+1)."' value='$link' onclick='this.focus();this.select();customizeEmbed(isWidescreen,true);' readonly='readonly'>"
."<p class=r><a href='$link'>$link</a>"
."<p><input type='submit' value='Refresh'></form>"
."\");");
}
//=========================================================


if($a=='do_logout'||$a=='logout') { // разлогиниться: перенесено!!!! использовать рекомиендуется такую форму!!!
	otprav("majax('module.php',{mod:'LOGIN',a:'logout'})");
}

if($a=='do_login'||$a=='login') { // залогиниться
//	otprav("ifhelpc(".($GLOBALS['mnogouser']==1?"xdomain+wwwhost+'":"'".$GLOBALS['httphost'])."login','logz','Login');");
	otprav("ifhelpc(wwwhost+'login','logz','Login');setTimeout('ajaxoff()',1000);");
}

if($a=='getinfo'||$a=='openid_form') { // unic личная карточка автора
	if(($un=RE0('unic'))===false) $un=$unic; // if(!$un) idie('unic=0');
	$is=getis($un);

	if($un==$unic) { $tmpl="unic-edit.htm"; $center=1;
		$is['all']="<pre>".nl2br(h(print_r($is,1)))."</pre>";
		$is['time_reg_date']=date('Y-m-d H:i:s',$is['time_reg']);
//		if($is['birth']=='0000-00-00') {
			list($Y,$M,$D)=explode('-',$is['birth']);
			$a=array(''=>'---'); for($i=1;$i<=31;$i++) $a[$i]=sprintf("%02d",$i); $is['set_birth'].=selecto('d',$D,$a,"name");
			$a=array(''=>'---'); for($i=1;$i<=12;$i++) $a[$i]=$GLOBALS['months_rod'][$i]; $is['set_birth'].=selecto('m',$M,$a,"name");
			$a=array(''=>'---'); for($i=(date('Y')-5);$i>1900;$i--) { $l=sprintf("%04d",$i); $a[$l]=$l; } $is['set_birth'].=selecto('y',$Y,$a,"name");
//		} else $is['set_birth']=$is['birth'];

		foreach($GLOBALS['IS']['useropt'] as $n=>$l) $is['opt_'.$n]=$l;

	} else { $tmpl="unic.htm"; $center=0; }
	$tmpl=get_sys_tmp($tmpl);

	$is['httphost']=$GLOBALS['httphost'];
	$is['sysadmin']=$GLOBALS['admin'];
	$is['adm']=verf(); // $GLOBALS['ADM'];
	$is['podzamok']=$GLOBALS['podzamok'];

		list($is['mail'],$is['mail_confirm'])=var_confirmed($is['mail']);
		list($is['mailw'],$is['mailw_confirm'])=var_confirmed($is['mailw']);
		list($is['tel'],$is['tel_confirm'])=var_confirmed($is['tel']);
		list($is['telw'],$is['telw_confirm'])=var_confirmed($is['telw']);

	if($GLOBALS['admin']) { // шоб зря не мучаться
		$is['ip']=ipn2ip($is['ipn']);
		$is['set_dostup']=selecto('admin',h($is['admin']),array('user'=>'user','podzamok'=>'podzamok','admin'=>'admin','mudak'=>'mudak')
			,"class='in' onchange='majax(\"login.php\",{action:\"dostup\",unic:\"".$is['id']
			."\",value:this.value})' name");
		$is['set_karma']=selecto('capchakarma',h($is['capchakarma']),
			array('0'=>'нет','1'=>'юзер','2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'10'=>10,'11'=>11,'12'=>12,'13'=>13,'14'=>14,'15'=>15,'16'=>16,'17'=>17,'18'=>18,'20'=>20,'25'=>25,'30'=>30,'40'=>40,'50'=>50,'60'=>60,'80'=>80,'100'=>100,'150'=>150,'255'=>255)
			,"class='in' onchange='majax(\"login.php\",{action:\"karma\",unic:\"".$is['id']."\",value:this.value})' name");
	} else $is['set_dostup']=$is['set_karma']=$is['ip']='';

	$is['jurs']='';
	if($mnogouser && false!=($p=ms("SELECT `acc` FROM `jur` WHERE `unic`='$unic'","_a"))) {
		foreach($p as $l) { $l=$l['acc']; $ln=acc_link($l);
			$is['jurs'].="<b><big>$l</big></b> &nbsp; <span class=ll onclick=\"go('$ln')\">$ln</span><br>";
		}
		if($is['jurs']!='') $is['jurs']="accounts:<div style='margin-left:20px;'>".$is['jurs']."</div>";
	}

	// число его комментариев
	$is['ncomm']=intval(ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `unic`='".$un."'".($GLOBALS['podzamok']?'':" AND `scr`='0'"),"_l"));

	$is['time_reg']=date("Y-m-d H:i:s",$is['time_reg']);

	$s=mpers($tmpl,$is);

	otprav("helps('userinfo',\"".njs($s)."\",0,'del_onlogon'); posdiv('userinfo',".($center?"-1,-1":"mouse_x+10,mouse_y+10").");");
}
//===== записать карточку =========================
if($a=='save_card') {

	teddyid_confirm('ttcard','Save usercard?'); // подтверждение изменений в карточке

	$js=''; // unic личная карточка автора
	$ara=array();

	// дата
	if($IS['birth']=='0000-00-00') {
		$y=RE0('y');$m=RE0('m');$d=RE0('d');
		if($y*$m*$d && ($i=strtotime("$y-$m-$d"))) $ara['birth']=date("Y-m-d",$i);
	}

	// смена пароля
	$i=trim(RE('password')); if($i!='') {
		// if(RE('old_password'))
	}

    // смена юзерпика
    if(!empty($_FILES) && ($FL=array_shift($_FILES)) && $FL['error']===0 && is_uploaded_file($FL['tmp_name'])) {
        $foto_replace_resize=1; require_once $include_sys."_fotolib.php";
        list($W,$H,$itype)=getimagesize($FL['tmp_name']); $img=openimg($FL['tmp_name'],$itype);
        if($img===false) idie(LL('Comments:foto:musor',implode(', ',$foto_rash))); // шо за мусор?
        $imgs=obrajpeg_sam($img,150,$W,$H,$itype,'');
        imagedestroy($img);
	$ff=rpath("user/".$unic."/userpick.jpg");
	$fff=$filehost.$ff;
	$www=$httphost.$ff;
	testdir(dirname($fff));
	closeimg($imgs,$fff,$itype,95);
    if(!is_file($fff)) idie('Image Error');
    ms("UPDATE $db_unic SET `img`='".e($www)."' WHERE `id`='$unic'","_l",0);

    otprav("
idd('img_no').style.display='none';
idd('img_src').src='".$www."?'+Math.random();
idd('img_yes').style.display='inline';
");
    }

	// site
	$i=trim(RE('site')); if($i!=$IS['site']) {
		if(!strstr($i,'://')) $i="http://".$i; 
		if(!site_validate($i)&&!site_validate(($i='http://'.$i))) idie("Неправильный формат сайта");
		$ara['site']=h($i);
	}

	// realname
	$i=trim(RE('realname')); if($i!=$IS['realname']) $ara['realname']=h($i);

	// mail
	$mail=trim(RE('mail')); $mailw=trim(RE('mailw'));
	list($mm,$mail_confirm)=var_confirmed($IS['mail']); list($mw,$mailw_confirm)=var_confirmed($IS['mailw']);
	if(!$mail_confirm) { // если первичный не подтвержден
		if($mail==''&&$mailw!='') list($mail,$mailw)=array($mailw,$mail);
		if($mail!=''&&$mail!=$mm) {
			if(!mail_validate($mail)) idie("Ошибочный формат mail: `".h($mail)."`");
			$ara['mail']=$mm=$mail; $js.=mail_sendconfirm($mm);
		}
	}
	if($mailw!=$mw&&$mailw!=$mm) {
		if($mailw!=''&&!mail_validate($mailw)) idie("Ошибочный формат mailw: `".h($mailw)."`");
		$ara['mailw']=$mw=$mailw; if($mm!=$mw&&$mailw!='') mail_sendconfirm($mw);
	} if($mm==$mw) $ara['mailw']=''; // дублей не надо

	if(RE0('mail_confirm')) $js.=mail_sendconfirm($mm);
	if(RE0('mailw_confirm')) $js.=mail_sendconfirm($mw);

	// tel
	$tel=trim(RE('tel')); $telw=trim(RE('telw'));
	list($mm,$tel_confirm)=var_confirmed($IS['tel']); list($mw,$telw_confirm)=var_confirmed($IS['telw']);
	if(!$tel_confirm) { // если первичный не подтвержден
		if($tel==''&&$telw!='') list($tel,$telw)=array($telw,$tel);
		if($tel!=''&&$tel!=$mm) {
			if(!tel_validate0($tel)) idie("Ошибочный формат основного мобильного:<br>вместо `".h($tel)."`<br>должен быть типа +79166801685");
			$ara['tel']=$mm=$tel; $js.=tel_sendconfirm($mm);
		}
	}
	if($telw!=''&&$telw!=$mw&&$telw!=$mm) {
		if(!tel_validate0($telw)) idie("Ошибочный формат запасного мобильного:<br>вместо `".h($telw)."`<br>должен быть типа +79166801685");
		$ara['telw']=$mw=$telw; if($mm!=$mw) $js.=tel_sendconfirm($mw);
	} if($mm==$mw) $ara['telw']=''; // дублей не надо

	if(RE0('tel_confirm')) $js.=tel_sendconfirm($mm);
	if(RE0('telw_confirm')) $js.=tel_sendconfirm($mw);

	// login
	$i=trim(RE('login')); if($i!='' && $IS['login']=='') { // установить логин
		if(preg_match("/[^0-9a-z\-\_]/s",$i)) idie("В логине допустимы только строчные латинские буквы, цифры, подчеркивание или минус.");
		if(strlen($i)>32) idie("Длина логина - не более 32 символов.");
		if(ms("SELECT COUNT(*) FROM ".$db_unic." WHERE `login`='".e($i)."'","_l",0)!=0) idie("Логин `".h($i)."` уже занят!");
		$ara['login']=$i;
	}

	// password
	if(''!=($i=trim(RE('password')))) { // установить пароль
		$i=md5($i.$hashlogin);
		if(''==$IS['login'].$ara['login']) idie('Какой смысл устанавливать пароль при пустом логине?');
		//if(ms("SELECT COUNT(*) FROM ".$db_unic." WHERE `password`='".e($i)."'","_l")!=0) idie("Этот пароль уже кем-то занят. Придумай другой.");
		if($i=='password'||$i=='123457') idie("Это <a href='https://news.mail.ru/society/21065199/?frommail=1'>личный пароль Барака Обамы</a>, вам нельзя его использовать!");

		if(''!=$IS['password']) { // если смена пароля
			if(''==($j=trim(RE('oldpassword')))) idie('Для смены пароля нужно также указать старый.');
			if($IS['password']!=md5($j.$hashlogin)) idie('Старый пароль неправильный.');
		}

		$ara['password']=$i;
	}

	// mail_comment
	$i=intval(RE0('mail_comment')); if($i!=1*$IS['mail_comment']) $ara['mail_comment']=$i;

	// opt
	$bylo=unser($GLOBALS['IS']['opt']); $def=mkuopt('');
	$o=array(); foreach($def as $n=>$l) { $c=intval(RE0('opt_'.$n)); if($c!=$l || isset($bylo[$n])) $o[$n]=$c; } // если не дефолт или была хоть раз установлена

// $bylo=array();
// dier($bylo);
//dier(mkzopt(array('opt'=>$o)));

/*
dier(mkuopt(ser($o)));

function mkuopt($opt) { $o=unser($opt); // сделать из $opt массив опций пользователя
<------>foreach($GLOBALS['uopt_a'] as $n=>$l) { if(!isset($o[$n])) $o[$n]=$l[0]; }
<------>return $o;
}

function makuopt($r,$i=0) { // создать массив opt из заданного массива и дефолта
<------>$opt=array(); foreach($GLOBALS['uopt_a'] as $n=>$l) {
<------>if(isset($r[$n]) && $r[$n]!='default') $opt[$n]=$r[$n]; elseif($i) $opt[$n]=$l[0];
<------>} return $opt;
}

function mkzopt($p) { $o=unser($p['opt']); // сделать из $p массив опций и вернуть его
<------>foreach($GLOBALS['zopt_a'] as $n=>$l) { if(!isset($o[$n])) $o[$n]=$l[0]; }
<------>return array_merge($p,$o);
}

function makeopt($r,$i=0) { // создать массив opt из заданного массива и дефолта
<------>$opt=array(); foreach($GLOBALS['zopt_a'] as $n=>$l) {
<------>if(isset($r[$n]) && $r[$n]!='default') $opt[$n]=$r[$n]; elseif($i) $opt[$n]=$l[0];
<------>} return $opt;
}


*/

	if(sizeof($o)) $ara['opt']=ser(array_merge($bylo,$o));
	// $ara['oo']=unser($ara['opt']); dier($ara['oo']);

if(sizeof($ara)) if(false===msq_update($GLOBALS['db_unic'],$ara,"WHERE `id`='".$unic."'")) idie("MySQL error");
otprav("clean('userinfo');".$js);

/*
	if(substr($name,0,7)=='capcha-') { include_once $GLOBALS['include_sys']."_antibot.php";
		list($name,$val)=explode('-',$name);
	        if(!antibot_check($value,$val)) {
			errpole("цифры введены неверно!","zabil('ozcapcha',\"<table><tr valign=center><td><input onkeyup='polese(this)' onchange='polesend(this)' class='capcha' maxlength=".$GLOBALS['antibot_C']." type=text name='capcha-".antibot_make()."'></td><td>".antibot_img()."</td></tr></table>\");");
	        } else {
			$value='yes';
			setpole("не робот"," zabil('ozcapcha','факт: не робот');");
		}
	}

if($IS['capcha']=='no') { include_once $include_sys."_antibot.php";
$s.="<div class=l0>
<div class=l1>я не робот:</div>
<div class=l2 id='ozcapcha'><table><tr valign=center><td><input onkeyup='polese(this)' onchange='polesend(this)'
maxlength=".$GLOBALS['antibot_C']." class='capcha' type=text name='capcha-"
.antibot_make()."'></td><td>".antibot_img()."</td></tr></table></div>
<br class=q /></div>";
}


*/
}
//========================================================================================================================
if($a=='dostup') { AD(); $v=RE('value'); // смена доступа
	ms("UPDATE ".$db_unic." SET `admin`='".e($v)."' WHERE `id`='".RE0('unic')."'","_l",0);
	mailbox_send($GLOBALS['unic'],RE0('unic'),"Изменен доступ на: ".h($v));
	otprav("zabil('openidotvet','<font size=1 color=green>изменен доступ: ".h($v)."</font>')");
}

if($a=='karma_delmud') { AD(); $u=RE0('unic');// удалить комменты мудака
	$a=ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `unic`='".$u."'","_l",0);
	ms("DELETE FROM `dnevnik_comm` WHERE `unic`='".$u."'","_l",0);
	otprav("salert('$a comments deleted',500)");
}

if($a=='karma') { AD(); $karma=RE0('value'); $u=RE0('unic'); // смена кармы
	ms("UPDATE ".$db_unic." SET `capchakarma`='".e($karma)."' WHERE `id`='".e($u)."'","_l",0);
	$s="zabil('openidotvet','<font size=1 color=green>изменена карма: ".h($karma)."</font>');";
	if($karma>=50) {
		$nkom=ms("SELECT COUNT(*) FROM `dnevnik_comm` WHERE `unic`='".e($u)."'","_l",0);
		$s.="if(confirm('".LL('login:delmud',$nkom)."')) majax('login.php',{action:'karma_delmud',unic:".h($u)."});";
	}
	otprav($s);
}


if($a=='remove_teddyid') { teddyid_confirm('ttcard','Remove TeddyId account?');
    ms("UPDATE $db_unic SET `teddyid`='0' WHERE `id`='$unic'","_l",0);
    otprav("idd('teddyid_no').style.display='inline'; idd('teddyid_yes').style.display='none';");
}

if($a=='remove_openid') { teddyid_confirm('ttcard','Remove Social Network account?');
    ms("UPDATE $db_unic SET `openid`='' WHERE `id`='$unic'","_l",0);
    otprav("idd('openid_no').style.display='inline'; idd('openid_yes').style.display='none';");
}

if($a=='remove_img') { teddyid_confirm('ttcard','Remove Photo?');
    $f=$filehost."user/".$unic."/userpick.jpg"; if(is_file($f)) unlink($f); // если наша, то удалить
    ms("UPDATE $db_unic SET `img`='' WHERE `id`='$unic'","_l",0);
    otprav("idd('img_no').style.display='inline'; idd('img_yes').style.display='none';");
}


idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>