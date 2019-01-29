<?php

global $omsg_new,$omsg_read,$db_fid,$db_fido,$db_fido_num,$db_fidopoints,$db_fidopodpiska,$db_fidomy,
$fido_node,$fido_dostup_default,$fido_msg_rows,$fido_msg_cols,$fido_point,$fido_myaddr,$fido_myname,$fidodir,
$httpfidodir,$fido_msg_new_default,$fido_msg_reply_default,$fido_nmes,
$www_design,$filehost,$httphost,$unic,$admin,$podzamok,$realname,
$alls;

$realname=$GLOBALS['IS']['realname'];

$fido_node=parse_config('address'); // "2:5020/313";

$omsg_new="e_ledgreen";
$omsg_read="e_ledyellow";

$searchtag=($_GET['search']?",search:'".strtr($_GET['search'],"\"'","--")."'":"");

$db_fid=parse_config('set sqldb='); if($db_fid!='') $db_fid="`$db_fid`."; // fido
$db_fido=$db_fid.'`'.parse_config('db_fido').'`'; // fidoecho
$db_fido_num=$db_fid.'`'.parse_config('db_fido_num').'`'; // fidoecho_num
$db_fidopoints=$db_fid.'`'.parse_config('db_fidopoints').'`'; // fidopoints
$db_fidopodpiska=$db_fid.'`'.parse_config('db_fidopodpiska').'`'; // fidopodpiska
$db_fidomy=$db_fid.'`'.parse_config('db_fidomy').'`'; // `fidomy`

$fido_dostup_default=parse_config('fido_dostup_default'); // write
$fido_msg_rows=parse_config('fido_msg_rows'); // 20
$fido_msg_cols=parse_config('fido_msg_cols'); // 80
$fido_nmes=parse_config('fido_nmes'); // 6

/*
$db_fid="`fido`";
$db_fido=$db_fid.".`fidoecho`";
$db_fido_num=$db_fid.".`fidoecho_num`";
$db_fidopoints=$db_fid.".`fidopoints`";
$db_fidopodpiska=$db_fid.".`fidopodpiska`";
$db_fidomy=$db_fid.".`fidomy`";

$fido_dostup_default='write';
$fido_msg_rows=20;
$fido_msg_cols=80;
$fido_nmes=6;
*/

$alls=(isset($_GET['all'])?1:0);

if(
!$alls && // если не задано ALL
($p=intval(ms("SELECT `point` FROM $db_fidopoints WHERE `unic`='$unic'","_l")))!=0
) { $fido_point=$p; $fido_myaddr=$fido_node.'.'.$p; }
else $fido_point=$fido_myaddr=0;

$fido_myname=c($GLOBALS['IS']['realname']); if($fido_myname=='') $fido_myname="#".$unic;

$fidodir=$filehost."fido/";
$httpfidodir=$httphost."fido/";

$fido_msg_new_default="Привет, дружище {to}!\n\n{body}\n\n\n---\nС уважением, {realname}\n";
$fido_msg_reply_default="Приветствую, {to}!\n\nВот {date} {to} ({addr}) пишет:\n\n{body}\n\n\n---\nС уважением, {realname}\n";

// idie("http://lleo.aha.ru/fido#".urlencode('area://ru.ftn.develop/?msgid=2:5020/313 03FDC37F'));

/*
$ara=('AREA'=>'$echo',
MSGID
REPLYID
SUBJ
FROMNAME
TONAME
FROMADDR
TOADDR
NUMBER
BODY
RAZMER
DATETIME
RECIVDATE
ATTRIB 	int(11) 	
*/

setmysql_utf();

function setmysql_utf() { global $msq_charset;
   $msq_charset='utf8';
   msq("SET NAMES $msq_charset");
   msq("SET @@local.character_set_client=$msq_charset");
   msq("SET @@local.character_set_results=$msq_charset");
   msq("SET @@local.character_set_connection=$msq_charset");
}

function ddump($s) { for($o='',$i=0;$i<min(1024,strlen($s));$i++) { $l=$s[$i];
$o.="<font color=green>".h($l)."</font>".sprintf("%03d", ord($l)).($i%20?" ":"<br>"); } return "<tt>$o</tt>";
}

function phfitorun($s) { global $SRC_PATH, $CONFIG_FILE, $DEBUG, $BASE_DIR, $MODULE, $COLOUR,$fidodir;
	$dd=getcwd(); chdir($fidodir); require_once('phfito.php');
	$str=explode(' ','phfito '.$s);
	run_phfito_run(sizeof($str), $str); $s=ob_get_contents();
	chdir($dd); return $s;
}

function fido_sendmail($echo,$fromuser,$touser,$fromaddr,$toaddr,$subject,$body,$replyid) {
	$echo=strtolower($echo);
	if(is_file($GLOBALS['filehost']."fido/phfito.lock")) idie('phito.lock!<br>Повторите через 10 секунд.');
	global $SRC_PATH, $CONFIG_FILE, $DEBUG, $BASE_DIR, $MODULE, $COLOUR;

	$DEBUG=1;

	$body=str_replace("\n","\r",$body); 
//	$body=str_replace("\n",chr(13),$body);

if($echo=='netmail') {
	setmysql_utf();
// idie('1');
	$ara=array(
		'AREAN'=>area2arean($echo),
		'SUBJ'=>e(wu($subject)),
		'MSGID'=>e($fromaddr." ".substr(md5($fromaddr.$body.rand(0,32000)),0,8)),
		'REPLYID'=>e($replyid),
		'FROMNAME'=>e(wu($fromuser)),
		'TONAME'=>e(wu($touser)),
		'FROMADDR'=>e($fromaddr),
		'TOADDR'=>e($toaddr),
		'NUMBER'=>intval(ms("SELECT COUNT(*) FROM ".$GLOBALS['db_fido']." WHERE `AREAN`='".area2arean($echo)."'","_l",0)),
		'BODY'=>e(chr(1)."CHRS: CP866 2\r".($replyid!=''?chr(1)."REPLY: ".$replyid."\r":'').wu($body)),
		'RAZMER'=>strlen($body),
		'DATETIME'=>e(date("Y-m-d H:i:s")),
		'RECIVDATE'=>e(date("Y-m-d H:i:s")),
		'ATTRIB'=>0
	);
	msq_add($GLOBALS['db_fido'],$ara);
	$GLOBALS['new_id']=msq_id();


//	dier($ara);
	$attrib=0x00000101;
} else $attrib=0;

if($replyid==0) $replyid=false; else $body=chr(1)."REPLY: ".$replyid."\r".$body;
	$dd=getcwd(); chdir($GLOBALS['fidodir']); require_once('phfito.php');
	$s=xinclude('S_config.php');
	$s.=xinclude('P_phfito.php');
	$s.=aks_init('Phfito', $CONFIG_FILE, array(), array());
	$s.=Tosser_init();
	$s.=PostMessage($echo,wa($fromuser),wa($touser),$fromaddr,$toaddr,wa($subject),wa($body),$attrib,$replyid);
	$s.=Tosser_done();
	$s.=aks_done();

/*
$g=fopen($GLOBALS['filehost']."/fido/00000000fidomail_test.log","a+"); fputs($g,"\n\n-----------------------------
echo='$echo'

from='$fromuser'

to='$touser'

fa='$fromaddr'

ta='$toaddr'

SUBJ='$subject'

BODY='$body'

att='$attrib'

repl='$replyid'
"); fclose($g);
*/
	chdir($dd);

$g=fopen("_fidomail_test_.log","a+"); fputs($g,"\n\n-----------------------------\n".$k); fclose($g);

return $s; // $s=ob_get_contents(); idie('s='.$s);
}

// function fido_toss() { return phfitorun('-t'); } // -ts

$GLOBALS['fidokluge']=0;

function obrabody2($s,$name) { global $fidokluge;
	$s=trim($s,"\t\n\r ");
	$a=explode(chr(13),$s);
	$p=explode(' ',preg_replace("/\s+/s",' ',$name));
	$pr=substr($p[0],0,1).(isset($p[1])?substr($p[1],0,1).(isset($p[2])?substr($p[2],0,1):''):'').'> ';
	foreach($a as $n=>$c) { $c=trim($c,"\n\r\t ");
		if(substr($c,0,6)==chr(1).'PATH:') { if(!$fidokluge) { unset($a[$n]); continue; } $c=h($pr.substr($c,1)); }
		elseif(substr($c,0,8)=='SEEN-BY:') { if(!$fidokluge) { unset($a[$n]); continue; } $c=h($pr.$c); }
		elseif(substr($c,0,1)==chr(1)) { if(!$fidokluge) { unset($a[$n]); continue; } $c=h($pr.substr($c,1)); }
		elseif(stristr(substr($c,0,10),'* Origin:')) $c=h($pr.$c);
		elseif(substr($c,0,3)=='---') $c=h($pr.$c);
		elseif(strstr(substr($c,0,5),'>'))  $c=h(ltrim(preg_replace("/^([^>]+>)/s","$1>",$c)));
		elseif($c!='') $c=h($pr.$c);
		$a[$n]=$c;
	} $s=implode("\n",$a);
	return uw($s);
}


function obrabody1($s) { //global $fidokluge; 

$a=explode(chr(13),$s);

$fidokluge=1;

	foreach($a as $n=>$c) {
		$c=str_replace('/','#l/l/e/o#',$c);
		if(substr($c,0,6)==chr(1).'PATH:') { if(!$fidokluge) { unset($a[$n]); continue; } $c="<font color=red>".h(substr($c,1))."</font>"; }
		elseif(substr($c,0,8)=='SEEN-BY:') { if(!$fidokluge) { unset($a[$n]); continue; } $c="<font color=red>".h($c)."</font>"; }
		elseif(substr($c,0,1)==chr(1)) { if(!$fidokluge) { unset($a[$n]); continue; } $c="<font color=red>".h(substr($c,1))."</font>"; }
		elseif(stristr(substr($c,0,10),'* Origin:')) $c="<font color=magenta>".h($c)."</font>";
		elseif(substr($c,0,3)=='---') $c="<font color=magenta>".h($c)."</font>";
		elseif(strstr(substr($c,0,5),'>')) $c="<font color=green>".h($c)."</font>";
		else $c=h($c);

		$c=rtrim(preg_replace("|([ ])#l/l/e/o#(.*?)#l/l/e/o#([ \.\!\?\,\n])|s","$1<i>/$2/</i>$3",$c." "));
		$c=str_replace('#l/l/e/o#','/',$c);
		$c=rtrim(preg_replace("/([ ])\*(.*?)\*([ \.\!\?\,$]*)/s","$1<b>*$2*</b>$3",$c." "));
//		$c=preg_replace("/([ ])\_(.*?)\_([ \.\!\?\,$\n])/s","$1<u style='font-size:120%;'>$2</u>$3",$c);
		$c=rtrim(preg_replace("/([ ])_(.*?)_([ \.\!\?\,$\n])/s","$1<u>_$2_</u>$3",$c." "));

// 1. Не обрабатывает "стандартные" для FIDONET (и для usenet) выделения *полужирный* /курсив/ _подчёркнутый_

		$a[$n]=$c;
	} $s=implode("<br>",$a); return uw($s);
}

//function aw($s) { return(iconv("cp866","windows-1251//IGNORE",$s)); }
function wa($s) { return(iconv("windows-1251","cp866//IGNORE",$s)); }

function FIDO_ajax() { $a=RE('a'); $alls=RE0('all');

global $omsg_new,$omsg_read,$db_fid,$db_fido,$db_fido_num,$db_fidopoints,$db_fidopodpiska,$db_fidomy,$db_unic,
$fido_node,$fido_dostup_default,$fido_msg_rows,$fido_msg_cols,$fido_point,$fido_myaddr,$fido_myname,$fidodir,
$httpfidodir,$fido_msg_new_default,$fido_msg_reply_default,$fido_nmes,
$www_design,$filehost,$httphost,$unic,$admin,$podzamok,$realname,$alls;

if($a=='admin_clean_lock') {
	$f=$filehost."fido/phfito.lock";
	unlink($f);
	return "salert('удален: `$f`',1000)";
}

//=========================================================================
if($a=='about_help') { // о проекте
$o="Перед вами проект первой полностью онлайн-ноды Фидо, собранной на скриптах PHP. Нода полностью автономна и
включает в себя веб-тоссер, веб-бинк и веб-голдед. Это стало возможным благодаря разработке Алекса Кочарина,
создавшего PHP-тоссер PhFito. Идею веб-ноды впервые <a href=http://vds.lushnikov.net/wfido/>реализовал</a> Макс Лушников,
он помогал консультациями и в создании этого проекта.

<p>Пока проект находится на стадии разработки. По окончании исходники будут выложены в свободный доступ.

<p>Разработку проекта ведет Леонид Каганов <a href='mailto:lleo@aha.ru'>lleo@aha.ru</a> Спасибо за участие в проекте:
Алексу Кочарину, Максу Лушникову, Теме Павлову (показал, как работать с хэшами на JS), а также всем, кто участвовал
в обсуждении - Мицголу, Виссарионову, Федорову, Рощупкину и остальным обитателям эхи
<a href='http://lleo.aha.ru/fido#area:ru.ftn.develop'>RU.FTN.DEVELOP</a>



<p><br>Краткая инструкция по клавишам. Здесь принято работать с шорткеями. На данный момент работают клавиши:

<br>стрелки вверх/вниз - листать эхи
<br>стрелки вперед/назад - листать мессаги
<br>R - ответить на сообщение
<br>N - новое сообщение в эхе
<br>M - показать клуджи
<br>DEL - удалить мессагу

<p><br>Краткая инструкция по ноде.

<br>Как админу создать новую эху?
<br>Откройте форму создания письма в любой эхе, нажмите на ее название, и в списке полных эх нажмите 'create'.
<br>После написания первого сообщения выберите в меню Admin - Uplink Echo, чтобы подписать на новую эху своего аплинка.

";

return "
helps('about_help',\"<fieldset><legend>FIDO: About v 1.1</legend>".njsn($o)."</fieldset>\");
posdiv('about_help',-1,-1); idd('about_help').focus();
";
}

if($a=='joinme') { // хочу поинта

if($GLOBALS['IS']['openid']!='' or ($GLOBALS['IS']['login']!='' and $GLOBALS['IS']['password']!='')) {
	if($realname=='' or preg_replace("/[^a-z0-9\-\_\. ]+/si","",$realname)!=$realname) {
$o="У тебя неправильно заполнено в личной карточке поле
<br><b>realname</b> (\"имя или ник\").
<p>Чтобы не было проблем в FIDO, оно должно состоять только
<br>из латинских букв любого регистра, цифр, пробела, точки,
<br>минуса или подчеркивания. Первыми и последними символами
<br>не должны быть пробелы. Пример: Vasyly Pupkin
<p>Исправь свою карточку и повтори: <span class=l onclick=\"majax('login.php',{a:'getinfo'});\">".$GLOBALS['imgicourl']."</span>";
	} else 
if(($rr=ms("SELECT `point` FROM $db_fidopoints WHERE `unic`='$unic'","_l"))!='')
$o="У тебя уже есть адрес <b>".$fido_node.".".$rr."</b>";
else { $o="
Привет, $realname!

<p>Выбери себе номер от ".($podzamok?5:20)." до 32768:
<center>".$fido_node.".<input id='fidoin_p' size=4 type=text></center>

<p class=r>Вступая в сеть ФИДО, я понимаю, что:
".str_replace("\n","<br>","

1. ФИДО является старинной некоммерческой сетью друзей,
чей возраст превышает возраст интернета, каким мы его знаем.
Участники сети считаются друзьями, здесь принято обращение
на \"ты\", неприемлем спам и вредительство.

2. Несмотря на нынешний веб-интерфейс конкретно этой станции,
позволяющей попадать в ФИДО через интернет, сеть ФИДО живет по
собственным законам и технологиям, отличным от интернета.

3. В ФИДО существует два типа общения: Нетмайл (личные письма)
и Эхоконференции - области, где письма, отправленные участниками,
видны всем подписчикам. Для эхоконференций существуют правила,
установленные их модераторами. Запрещается отправлять письма в
эхоконференции до тех пор, пока не прочитаны правила (они обычно
публикуются периодически) и не стало понятно, что здась разрешено.

4. В сети ФИДО не существует анонимности - любой имеет адрес, и
нарушитель правил может быть отключен. При этом ответственность
за поведение поинта несет босс (владелец узла). Иными словами,
если вы нарушаете правила эхоконференций, вы подставляете меня,
как владельца узла. Пожалуйста будьте аккуратны.

<input id='fidoin_ch' type=checkbox> я это понимаю

<center><input type=button value='Зарегистрироваться' onclick=\""
."majax('module.php',{mod:'FIDO',a:'joinme_reg',ch:idd('fidoin_ch').checked?1:0,p:idd('fidoin_p').value})\"></center>");
}
} else $o="
Чтобы тебе получить поинтовый номер на этой станции,
<br>необходимо сперва залогиниться в движке. Что это значит?
<br>Это значит, что нужно завести учетную запись с паролем,
<br>которая бы не терялась, если куки браузера сбросятся.

<p>Если есть Openid на другом сайте (или уже был логин здесь),
<br>можно зарегистрироваться как Openid, кликнув <span onclick=\"majax('login.php',{a:'do_login'})\" class='l'>залогиниться</span>
<br>в своей личной карточке.

<p>Либо просто открой свою <span onclick=\"majax('login.php',{a:'getinfo'})\" class='l'>карточку</span>
и заполни поля: 'Логин'
<br>(только латинские строчные буквы), 'Пароль', 'email' и 'Ник'
<br>(имя и фамилия латинскими буквами, это поле нужно для
<br>регистрации в ФИДО).

<p>Повтори этот запрос, когда залогинишься.
";

return "
helps('joinme',\"<fieldset><legend>FIDO: Join me!</legend>".njsn($o)."</fieldset>\");
posdiv('joinme',-1,-1); idd('joinme').focus();
";

}

if($a=='joinme_reg') { // регистрация

$r='';
$p=RE0('p');

if(RE('ch')!=1) $o="Еще раз внимательно прочти эти несколько несчастных пунктов<br>и поставь галочку, что понимаешь, о чем речь.";
elseif(!$unic) $o="Что-то логин твой не сохраняется.<br>Проверь, включены ли в браузере куки и залогинься заново.";
elseif(!$p) $o="Введи номер правильно.";
elseif(!$admin and (($podzamok && $p<5) or $p<20)) $o="Слишком маленький номер, читай внимательно.";
elseif(!$admin and $p>32768) $o="Не, ну надо иметь совесть.";
elseif($fido_point) $o="У тебя уже есть адрес <b>".$fido_node.".".$rr."</b>";
elseif(ms("SELECT COUNT(*) FROM $db_fidopoints WHERE `point`='$p'","_l")) $o="Номер $p занят.<br>Здесь можно <span class=l onclick=\"majax('module.php',{mod:'FIDO',a:'pointlist'})\">посмотреть список</span> занятых номеров.";
else {

	msq_add($db_fidopoints,array(
	'point'=>$p,
	'unic'=>$unic,
	'dostup'=>($podzamok?'write':$fido_dostup_default),
	'datereg'=>time(),
	'datelast'=>0,
	'msg_new'=>wu(str_replace('{realname}',$realname,$fido_msg_new_default)),
	'msg_reply'=>wu(str_replace('{realname}',$realname,$fido_msg_reply_default))
	));

	return "clean('joinme');
setTimeout(\"window.location='$httpfidodir';\", 5000);
salert(\"Поздравляю! Твой адрес в сети ФИДО:<p><center><b>".$fido_node.".".$p."</b></center>\",5000);
posdiv('salert',-1,-1);";
}

return "
helps('joinme_reg',\"<fieldset><legend>FIDO: Join me!</legend>".njsn($o)."</fieldset>\");
posdiv('joinme_reg',-1,-1); idd('joinme_reg').focus();
";

}


if($a=='pointlist') { // поинтлист

$pp=ms("SELECT p.`point`,u.`realname` FROM $db_fidopoints as p LEFT JOIN $db_unic as u ON p.`unic`=u.`id`","_a");

$o="<table>";
foreach($pp as $p) $o.="<tr><td>".$fido_node.".".$p['point']."</td><td>".$p['realname']."</td></tr>";
$o.="</table>";

return "
helps('pointlist',\"<fieldset><legend>FIDO: поинты станции $fido_node</legend>".njsn($o)."</fieldset>\");
posdiv('pointlist',-1,-1); idd('pointlist').focus();
";
}


if($a=='settings_echo') { // настройки
	$pp=ms("SELECT `echo`,`echonum` FROM $db_fido_num ORDER BY `echo`","_a");
	$dd=ms("SELECT `echonum` FROM $db_fidopodpiska WHERE `point`='$fido_point'","_a",0);
	$d=array(); foreach($dd as $p) $d[$p['echonum']]=1;

	$o="<table>";
	foreach($pp as $u=>$p) { if(isset($d[$p['echonum']])) { $arean=$p['echonum'];
		$o.="<tr><td>".h(strtoupper($p['echo']))."</td><td id='chp_".$arean."'><input onchange=\"chanpod(this.checked?1:0,$arean)\" type=checkbox checked></td></tr>";
		unset($pp[$u]);
	}}

	foreach($pp as $p) { $e=$p['echo']; if($e=='netmail') continue; $arean=$p['echonum'];
		$o.="<tr><td>".h(strtoupper($e))."</td><td id='chp_".$arean."'><input onchange=\"chanpod(this.checked?1:0,$arean)\" type=checkbox></td></tr>";
	}
	$o.="</table><center><input class=r type=button value='просто кнопка' onclick=\"clean('settings')\"></center>";

/*
$o="<table>"; foreach($pp as $p) { $e=$p['echo']; if($e=='netmail') continue; $arean=$p['echonum'];
	$o.="<tr><td>".h(strtoupper($e))."</td><td id='chp_".$arean."'><input onchange=\"chanpod(this.checked?1:0,$arean)\" type=checkbox".(isset($d[$arean])?" checked":'')."></td></tr>";
} $o.="</table><center><input class=r type=button value='просто кнопка' onclick=\"clean('settings')\"></center>";
*/

return "
chanpod=function(i,n) { majax('module.php',{mod:'FIDO',a:'change_podpiska',n:n,i:i}); };

helps('settings',\"<fieldset><legend>FIDO: $fido_myaddr подписка на эхи</legend>".njsn($o)."</fieldset>\");
posdiv('settings',-1,-1); idd('settings').focus();
";
}

if($a=='omsg_read') { // сигнал "прочитано"
	if(!$fido_point) return '';
	msq_update($db_fidomy,array('metka'=>'read'),"WHERE `point`='$fido_point' AND `id`='".RE0('id')."'");
	$arean=RE0('arean');
	$new=msgn_new($arean);
	$o="zabil('omsgn".$arean."',$new);";
	if(!$new) $o.="idd('omsr".$arean."').className='$omsg_read';";
	return $o;
}

if($a=='allmsg_read') { // эха: пометить как прочитанные
	$arean=RE0('arean'); if(!$arean || !$fido_point) return '';
	ms("UPDATE $db_fidomy SET `metka`='read' WHERE `point`='$fido_point' AND `arean`='$arean'","_l");
	return "
idd('omsr".$arean."').className='$omsg_read';
var a=ebasa[mya]; for(var i in a) { if(isNaN(i)) continue; a[i].m=0; }
echotype();
";
}

if($a=='msg_del') { // 1 сообщение: удалить
/*
ebasa[mya].i=0; - текущий номер в верхней строке экрана
ebasa[mya].b=0; - позиция бегунка
ebasa[mya].len=0; - нынешняя длина
ebasa[mya].st=0; - если N, то имеется еще N более новых записей, пока не закачанных
ebasa[mya].en=0; - если 1, то мы достигли конца архива
*/

$id=RE0('id'); $arean=RE0('arean'); if(!$arean || !$fido_point) return '';

// del:509279 потом разберемся
// idie('del:'.$id);

$area=arean2area($arean);

// idie("DEL: $area $db_fido $db_fidomy");

if($area=='netmail') { // вообще вынести мессагу с этим id
	$points="(`point`='$fido_point'".($GLOBALS['admin']?" OR `point`='0' OR `point`='1'":'').")";

	$n=ms("SELECT COUNT(*) FROM $db_fidomy WHERE `id`='$id' AND $points","_l",0);
	if(!$n) return "salert('NO NETMAIL #$id',500)"; // ничего
	if($n>2) return "salert('N=$n NETMAIL #$id',5000)"; // больше 2 участников переписки

	msq("DELETE FROM $db_fidomy WHERE `id`='$id' AND $points");
	if($n==1) msq("DELETE FROM $db_fido WHERE `id`='$id'"); // и если 1, то удалить и из общей базы
} else {
	if(0==ms("SELECT COUNT(*) FROM $db_fidomy WHERE `point`='$fido_point' AND `id`='$id'","_l",0)) return "salert('NO: #$id',500)";
	msq("DELETE FROM $db_fidomy WHERE `point`='$fido_point' AND `id`='$id'");
}

// idie("DEL: $id $arean `$e`".$GLOBALS['msqe']."<br>$q");

	return "
".rescan_n($arean)."
var a=cphash(ebasa[mya]);
ebasa[mya]=[];

for(var i in a) { if(isNaN(i) || a[i].id==$id) continue; pushid(a[i]); }
ebasa[mya].i=a.i; ebasa[mya].b=a.b; ebasa[mya].len=(a.len-1); ebasa[mya].st=a.st; ebasa[mya].en=a.en;

echotype();

if(ebasa[mya].en==0 && (ebasa[mya].i+2+fido_nmes) > ebasa[mya].len) {
setTimeout(\"majax('module.php',{mod:'FIDO',a:'loadarea',arean:mya,lastid:\"+ebasa[mya][ebasa[mya].len-1].id+\"},'echotype()')\",50);
}
";
}
//                 var lastid=ebasa[mya][ebasa[mya].len-1].id;
//		salert('load del nado mya='+mya+' lastid:'+lastid,2500);
// /*	salert('load del: '+(ebasa[mya].i+2+fido_nmes)+' len:'+ebasa[mya].len,2500); */


if($a=='allmsg_new') { // эха: пометить как новые
	$arean=RE0('arean'); if(!$arean || !$fido_point) return '';
	ms("UPDATE $db_fidomy SET `metka`='new' WHERE `point`='$fido_point' AND `arean`='$arean'","_l");
return "idd('omsr".$arean."').className='$omsg_new';
var a=ebasa[mya]; for(var i in a) { if(isNaN(i)) continue; a[i].m=1; } echotype();";
}

if($a=='allmsg_del') { // эха: удалить все
	$arean=RE0('arean'); if(!$arean || !$fido_point) return '';
	msq("DELETE FROM $db_fidomy WHERE `point`='$fido_point' AND `arean`='$arean'");
return "idd('omsr".$arean."').className='$omsg_read';
zabil('omsga".$arean."',0);
zabil('omsgn".$arean."',0);
ebasa[mya]=[]; ebasa[mya].i=0; ebasa[mya].b=0; ebasa[mya].len=0; ebasa[mya].st=0; ebasa[mya].en=0; echotype();";
}

if($a=='allmsg_restore') { // эха: восстановить
	$arean=RE0('arean'); if(!$arean || !$fido_point) return '';
	$pp=ms("SELECT `id` FROM $db_fido WHERE `AREAN`='$arean'","_a");
	if(!sizeof($pp)) return "salert('Сообщений в базе не найдено',2000)";
	msq("DELETE FROM $db_fidomy WHERE `point`='$fido_point' AND `arean`='$arean'"); // очистить
	foreach($pp as $p) msq_add($db_fidomy,array('id'=>$p['id'],'point'=>$fido_point,'arean'=>$arean,'metka'=>'new'));
	return "idd('omsr".$arean."').className='$omsg_new';
ebasa[mya]=[]; ebasa[mya].i=0; ebasa[mya].b=0; ebasa[mya].len=0; ebasa[mya].st=0; ebasa[mya].en=0;
salert('Восстановлено: ".sizeof($pp)."',2000);
".rescan_n($arean)."
echotype();
setTimeout(\"charea($arean,'echotype()')\",50);
";
}

if($a=='loadareas') { // область арий скачать

// idie('@'.RE0('all'));

	$z=($fido_point && !RE0('all')); // поинт ли это?
	$a=array();
	if($z) { // только эхи поинта
		$pp=ms("SELECT p.`echonum`,e.`echo`
			FROM $db_fidopodpiska as p INNER JOIN $db_fido_num as e ON p.`echonum`=e.`echonum`
			WHERE p.`point`='$fido_point'");
		$pp[]=array('echonum'=>area2arean('netmail'),'echo'=>'netmail'); // и добавить Нетмайл
		foreach($pp as $l) $a[$l['echonum']]=$l['echo'];
	}

	if(!sizeof($a) || (RE('allif') && !array_search(RE('allif'),$a))) {
		// если нет для поинта или конкретно запрошенной эхи у поинта не было
		$pp=ms("SELECT `echonum`,`echo` FROM $db_fido_num");
		foreach($pp as $l) $a[$l['echonum']]=$l['echo']; // массив $a - echonum->echo
		$z=0;
	}

	$s=$a; sort($s); // массив $s - n->echo
	$k=array_search('netmail',$s); if($k!==false) unset($s[$k]);
	if($z) $s=array_merge(array('netmail'),$s);

	$o='';
	$are="are={";
	$aren="aren=[";
	foreach($s as $area) { $arean=array_search($area,$a);

if($z) { $all=msgn_all($arean); $new=msgn_new($arean); } else { $all=msgn_all($arean,1); $new=-1; }

	$o.="\n<div id='a".$arean."' class='fidoa' onclick='charea($arean)'>"
	.($z?"<i id='omsr".$arean."' class='".($new?$omsg_new:$omsg_read)."'></i>&nbsp;":'')
	.h(strtoupper($area))." "
	."<span class=br id='omsga".$arean."'>$all</span>"
	.($new<0?'':"/<span class=br id='omsgn".$arean."'>$new</span>")
	."</div>";
$aren.="$arean,";
$are.="$arean:'$area',";
}

	return "
areasmode=".($z?0:1).";"
.rtrim($are,',')."};"
.rtrim($aren,',')."];"
."zabil('fidoarea',\"".njsn($o)."\");
setmya(aren[0]);
fidoselect('fidoarea');
";
}

if($a=='change_podpiska') { // настройки
	$arean=RE0('n'); if(!$arean) return '';
	$i=ms("SELECT `i` FROM $db_fidopodpiska WHERE `point`='$fido_point' AND `echonum`='$arean'","_l",0);
	if(RE('i')==1) { // подписаться
		if($i!==false)  return '';
		msq_add($db_fidopodpiska,array('point'=>$fido_point,'echonum'=>$arean));
	} else { // отписаться
		if($i===false) return '';
		msq("DELETE FROM $db_fidopodpiska WHERE `i`='$i'","_l",0); // убрать из подписки
		msq("DELETE FROM $db_fidomy WHERE `point`='$fido_point' AND `arean`='$arean'"); // потереть флажки
	}
	return "idd('chp_".$arean."').style.border='1px dotted #cfcfcf';";
}

if($a=='settings_podp') { // настройки
	if(!$fido_point) idie("Ты пока не поинт ФИДО");

	$p=ms("SELECT `msg_new`,`msg_reply` FROM $db_fidopoints WHERE `point`='$fido_point'","_1",0);
	$p['msg_new']=uw($p['msg_new']); $p['msg_reply']=uw($p['msg_reply']);
	if(trim($p['msg_new'])=='') $p['msg_new']=str_replace('{realname}',$realname,$fido_msg_new_default);
	if(trim($p['msg_reply'])=='') $p['msg_reply']=str_replace('{realname}',$realname,$fido_msg_reply_default);

$o="
<p>Шаблон нового сообщения:
<div><textarea id='m_msg_new' cols='".$fido_msg_cols."' rows='".min(10,$fido_msg_rows)."'>".$p['msg_new']."</textarea></div>

<p>Шаблон ответа:
<div><textarea id='m_msg_reply' cols='".$fido_msg_cols."' rows='".min(10,$fido_msg_rows)."'>".$p['msg_reply']."</textarea></div>

<div><input id='save' type=button value='Сохранить' onclick=\"majax('module.php',{mod:'FIDO',a:'settings_change',msg_new:idd('m_msg_new').value,msg_reply:idd('m_msg_reply').value})\"></div>";

return "
helps('settings',\"<fieldset><legend>FIDO: поинты станции $fido_node</legend>".njsn($o)."</fieldset>\");
posdiv('settings',-1,-1); idd('settings').focus();
setkey('enter','ctrl',function(e){idd('send').click()},false);
";
}

if($a=='settings_change') { // принять измененные настройки
	msq_update($db_fidopoints,array('msg_new'=>e(wu(RE('msg_new'))),'msg_reply'=>e(wu(RE('msg_reply')))),"WHERE `point`='$fido_point'");
	return "clean('settings')";
}


if($a=='toss') { // проверить почту
	if(is_file($filehost."fido/phfito.lock")) return "salert('phito.lock!<br>Повторите через 10 секунд.',2000)";
	$s=phfitorun('-t'); // -ts
	ob_clean(); return "zabil('fidomsg',\"<div class=br>".njs(nl2br($s))."</div>\");
	majax('module.php',{mod:'FIDO',a:'loadareas',all:".$alls."});";
}

if($a=='send') { // отослать почту
	$s=file_get_contents($httpfidodir."bink.php?send");
	return "zabil('fidomsg',\"".njsn($s)."\")";
}

if($a=='admin_del_tables') { AD();
//	$area=array(); foreach(ms("SHOW TABLES FROM ".$GLOBALS['db_fid'],"_a") as $p) $area[]=$p['Tables_in_fido'];
//	foreach($area as $l) msq("DROP TABLE ".$GLOBALS['db_fid'].".`".e($l)."`");
	return "salert('убито таблиц: ".sizeof($area)."',1000)";
}

// устарело
//if($a=='admin_del_dupes') { AD();
//	$dupes=glob($filehost."fido/dupes/*");
//	foreach($dupes as $l) unlink($l);
//	return "salert('очищено dupes: ".sizeof($dupes)."',1000)";
//}

if($a=='admin_copy_file') { AD();
//	$f="2011-01-08_11-10-44_28c51000.sa0";
	$f="32393000.su0";
	$fn=substr($f,strlen($f)-12);
	$file=$filehost."fido/in/dup/".$f;
	$filen=$filehost."fido/in/".$fn;
	copy($file,$filen);

	if(!is_file($file)) idie("File not found: ".$file);
	return "salert('скопирован файл: `$file` - `$filen`',5000)";
}

if($a=='admin_copy_allfiles') { AD();
	$pp=glob($filehost."fido/in/dup/*");
	$s=''; foreach($pp as $l) {
		$fn=substr($l,strlen($f)-12);
		$filen=$filehost."fido/in/".$fn;
		if(is_file($filen)) {
			$s.="<font color=red>File exist: $fn</font><br>"; }
		else { copy($l,$filen); $s.="<font color=green>$fn</font><br>"; }
	}
	return "salert('скопированы файлы<br>$s',50000)";
}

// ИЗМЕНИТЬ АДРЕС (НА АДМИНСКИЙ)
if($a=='select_froma') { AD(); if(!$fido_point) return; return "
selfromap=function(e){zabil('m_fido_froma',e.innerHTML);clean('Select_From_Address');};
helpc('Select_From_Address',\"<fieldset><legend>FIDO: Select From Address</legend>".njsn("
<div class=l0 onclick=selfromap(this)>".$fido_node."</div>
<div class=l0 onclick=selfromap(this)>".$fido_myaddr."</div>")."</fieldset>\")";
}

// ИЗМЕНИТЬ ЭХУ ДЛЯ ПИСЬМА
if($a=='select_area') { if(!$fido_point) return;
	$area=RE('area');

	if(RE('o')) $a=ms("SELECT DISTINCT `echo` FROM $db_fido_num");
	else $a=ms("SELECT a.`echo` FROM $db_fidopodpiska AS p INNER JOIN $db_fido_num AS a ON a.`echonum`=p.`echonum`
WHERE p.`point`='$fido_point'");

	$s=""; foreach($a as $l) { $l=h(strtoupper($l['echo']));
		$s.="<div".($l==$area?'':" class=l onclick='asel(this)'").">$l</div>";
	}

	if(!RE('o')) $s.="<p><div class=l0 onclick=\"majax('module.php',{mod:'FIDO',a:'select_area',area:'".h($area)."',o:1})\"><i class='e_expand_plus'></i>more</div>";
 	elseif($admin) $s.="<p><div class='l0 r' onclick=\"majax('module.php',{mod:'FIDO',a:'select_area_new'})\">&lt;Create&gt;</div>";

	return "
asel=function(e){ e=e.innerHTML.replace(/<.*?>/g,''); zabil('m_select_area',e); clean('Select_Area'); };
helpc('Select_Area',\"<fieldset><legend>FIDO: Select Area</legend>".njsn($s)."</fieldset>\")";
}

// СОЗДАТЬ НОВУЮ ЭХУ (только админ)
if($a=='select_area_new') { AD();
	$s="<input id='m_fido_newarea' type='text' size=20 value=''>&nbsp;&nbsp;<input type='submit' value='Create' onclick='asel()'>";
return "
asel=function(){ var a=idd('m_fido_newarea').value.toUpperCase();
if(a.replace(/[^a-z0-9\\.\\$\\&\\-\\_]/gi,'')!=a) { alert('Допустимы только символы 0-9a-z&\$-_.'); return false; }
zabil('m_select_area',a); clean('Select_Area');
};
helpc('Select_Area',\"<fieldset><legend>FIDO: Create New Area</legend>".njsn($s)."</fieldset>\");
idd('m_fido_newarea').focus();
";
}


if($a=='fidopost') { AD();
	$area=RE('area'); // "PVT.LLEO";
	$from=RE('from'); // h($fido_myname); // "<input id='ssm_fido_from' type='text' size=20 value='aaa'>";
	$froma=$fido_myaddr;
	$to="<input id='m_fido_to' type='text' size=20 value='All'>";
	$subj=RE('subj'); $subj=strtr(strip_tags($subj),"\xBB"."\xAB".chr(151),'""-');
		$subj=preg_replace("/&lt;.*?&gt;/si",'',$subj);
	$text=RE('text'); $text=strtr(strip_tags($text),"\xBB"."\xAB".chr(151),'""-');
		$text=preg_replace("/&lt;.*?&gt;/si",'',$text);
	$replyid="";

	return write_message_okno("постинг в FIDO",$area,$from,$froma,$to,$toa,$subj,$text,$replyid,0);

$aa=ms("SELECT DISTINCT `echo` FROM $db_fido_num","_a",0);

$s=''; foreach($aa as $a) { $l=$a['echo'];
		$bb=ms("SELECT `echo`,`echonum` FROM $db_fido_num WHERE `echo`='".e(strtr($l,'.','_'))."'
OR  `echo`='".e(strtr($l,'-','_'))."'
OR  `echo`='".e(strtr($l,'$','_'))."'
OR  `echo`='".e(strtr($l,'&','_'))."'","_a",0);
		if(sizeof($bb)>1) foreach($bb as $n=>$b) {
			$s.="<br>$n $l : ".$b['echo']." ".$b['echonum']."/".$bb[0]['echonum'];
		if($bb[0]['echonum']!=$b['echonum']) {
			$s.="<br>UPDATE $db_fido SET `AREAN` = '".$bb[0]['echonum']."' WHERE `AREAN`='".$b['echonum']."'";
			ms("UPDATE $db_fido SET `AREAN` = '".$bb[0]['echonum']."' WHERE `AREAN`='".$b['echonum']."'","_l");
		}
		}
 }

idie($s);
dier($aa);
}


//============================================================================

if($a=='replytext') { // принять мессагу и отправить
	$m=explode(" ","area subj to toa from froma replyid text"); foreach($m as $l) $$l=RE($l);

	mojno_write($area,$toa); // от кого можно принимать
	if($froma!=$fido_myaddr && !($admin && $froma==$fido_node)) idie('Хакер, блять?'); // подделка адреса

	$text=str_replace(array('\n','\r',"\r"),array("\n",'',''),$text);
	$pp=array(0=>array(
		'DATETIME'=>date("Y-m-d H:i:s"),
		'BODY'=>chr(1)."CHRS: CP866 2\r".($replyid!=''?chr(1)."REPLY: ".$replyid."\r":'').wu(str_replace("\n","\r",$text)),
		'NUMBER'=>0,
		'SUBJ'=>wu($subj),
		'FROMNAME'=>wu($from),
		'TONAME'=>wu($to),
		'FROMADDR'=>$froma,
		'TOADDR'=>$toa
	));

$arean=area2arean($area); // блять потому что после запуска тоссера уже к базе не обратиться
$zaebalo="zabil('omsga".$arean."',".(msgn_all($arean)+1)."); zabil('omsgn".$arean."',".(msgn_new($arean)+1).");";

	if(is_file($GLOBALS['filehost']."fido/phfito.lock")) idie('phito.lock!<br>Повторите через 10 секунд.');
	$s=fido_sendmail($area,$from,$to,$froma,$toa,$subj,$text,$replyid);
// idie('1='.h($s));
	$s.=file_get_contents($httpfidodir."bink.php?send"); // и сразу отослать
	$s.=file_get_contents($httpfidodir."bink.php?mail"); // и сразу отослать
	$pp[0]['id']=$GLOBALS['new_id'];


$o="clean('fido_message'); clean('fido_message_send');";
if(RE0('dei')) $o.="if(typeof echotype != 'undefined') {
		if(idd('fidomsg')) zabil('fidomsg',\"".njsn($s)."\");
		".loadmsg($pp,"unshift")."
		".$zaebalo."
		echotype();
	}";
return $o; //."alert(\"".njsn($s)."\");";

}

if($a=='reply') { // Ответить на сообщение
	$area=e(RE('area')); $id=RE0('id');
	$p=ms("SELECT `DATETIME`,`MSGID`,`BODY`,`SUBJ`,`FROMNAME`,`FROMADDR` FROM $db_fido WHERE `id`='$id'","_1");
	$from=h($fido_myname); $froma=h($fido_myaddr); $to=h(uw($p['FROMNAME'])); $toa=h(uw($p['FROMADDR']));
	$body=trim(obrabody2($p['BODY'],$p['FROMNAME']),"\n\t\r ");
if(!$fido_point) $text=str_replace('{realname}',$realname,$fido_msg_reply_default);
else $text=uw(ms("SELECT `msg_reply` FROM $db_fidopoints WHERE `point`='$fido_point'","_l",0));
$text=str_replace(array('{to}','{addr}','{date}','{body}'),array($to,$toa,$p['DATETIME'],$body),$text);
	return write_message_okno('Reply message',$area,$from,$froma,$to,$toa,"RE: ".uw($p['SUBJ']),$text,$p['MSGID']);
}



if($a=='newmsg') { // Написать новое сообщение
	$area=e(RE('area')); $from=h($fido_myname); $froma=h($fido_myaddr);
	if(!$fido_point) $text=str_replace('{realname}',$realname,$fido_msg_new_default);
	else $text=uw(ms("SELECT `msg_new` FROM $db_fidopoints WHERE `point`='$fido_point'","_l",0));
$text=str_replace(array('{to}','{addr}','{date}','{body}'),array(($area!='netmail'?'All':''),'',''),$text);
	if($area=='netmail') {
		$to="<input id='m_fido_to' type='text' size=20 value='Sysop'>";
		$toa="<input id='m_fido_toa' type='text' size=15 value='".$fido_node."'>";
	} else { $to="<input id='m_fido_to' type='text' size=20 value='All'>"; $toa=""; }
	return write_message_okno('New message',$area,$from,$froma,$to,$toa,uw($p['SUBJ']),$text,0);
}

/*
	удалить нахуй
if($a=='newarea') { if(!$GLOBALS['admin']) idie('Ты не админ'); // Создать новую эху
	$area=e($_REQUEST['area']); $from=h($GLOBALS['fido_myname']); $froma=h($GLOBALS['fido_myaddr']);
	$text=uw(ms("SELECT `msg_new` FROM ".$GLOBALS['db_fidopoints']." WHERE `point`='".$GLOBALS['fido_point']."'","_l",0));
	$text=str_replace(array('{to}','{addr}','{date}','{body}'),array(($area!='netmail'?'All':''),'',''),$text);
	$to="All"; $toa=""; $area="<input id='m_fido_newarea' type='text' size=20 value=''>";
	return write_message_okno('New area',$area,$from,$froma,$to,$toa,$p['SUBJ'],$text,0);
}
*/



// -- Uplink Echo | javascript:majax("module.php",{mod:"FIDO",a:"uplinkarea",arean:mya});
if($a=='uplinkarea') { AD(); $arean=RE0('arean'); $area=arean2area($arean); // Подписать эху на аплинка
		if($area=='netmail') idie("Netmail и так в порядке");

// EchoArea Pvt.Lleo fido/fidoecho/pvt.lleo -g A
// EchoMode Pvt.Lleo 2:5020/1519:l
	// Проверить conf/areas
		$file=$filehost."fido/".parse_config('includeareas');
		$pp=parse_config('route',1); $uplink=false;
		foreach($pp as $l) { $e=explode(' ',$l); if(c($e[1])=='*') { $uplink=c($e[0]); break; } }
		if($uplink===false) idie("В конфиге не найдена запись главного аплинка вида:<p><tt>Route x:xxxx/xxx *</tt>");

		if(($s=file_get_contents($file))===false) idie("Не найден файл `areas`!<br>".$file);
		if(!preg_match("/[\n\r]+\s*EchoArea\s+".preg_quote($area)."\s+[^\n\r]+/si","\n".$s)) idie("Что-то я не наблюдаю эхи <b>".h($area)."</b>");
		if(preg_match("/[\n\r]+\s*EchoMode\s+".preg_quote($area)."\s+/si","\n".$s)) idie("Нужная запись есть.");
		$stroka="EchoMode ".$area." ".$uplink.":l";
		$s=preg_replace("/([\n\r]+\s*EchoArea\s+".preg_quote($area)."\s+[^\n\r]+)/si","$1\n".$stroka,$s);
		if(file_put_contents($file,trim($s))===false) idie("Не удалось записать `areas`!<br>".$file);
		return "salert('Прописана строка:<p><b><tt>$stroka</tt></b>',10000);";
}


if($a=='delarea') { AD(); $arean=RE0('arean'); $area=arean2area($arean); // Убить эху
		if($area=='netmail') idie("А ты не охуел часом, Netmail удалять?");
	// 1. Убить у всех подписку
		msq("DELETE FROM $db_fidopodpiska WHERE `echonum`='$arean'");
	// 2. Убить все флажки у всех
		msq("DELETE FROM $db_fidomy WHERE `arean`='$arean'");
	// 3. Убить все мессаги в главной базе
		msq("DELETE FROM $db_fido WHERE `AREAN`='$arean'");
	// 4. Удалить имя из базы эх
		msq("DELETE FROM $db_fido_num WHERE `echonum`='$arean'");
	// 5. Удалить имя из файла conf/areas
		$file=$filehost."fido/".parse_config('includeareas');
		if(($s=file_get_contents($file))===false) idie("Не найден файл `areas`!<br>".$file);
		$o=preg_replace("/[\n\r]+\s*EchoArea\s+".preg_quote($area)."\s+[^\n\r]+/si",'',"\n".$s);
		$o=preg_replace("/[\n\r]+\s*EchoMode\s+".preg_quote($area)."\s+[^\n\r]+/si",'',"\n".$o);
		if($o!=$s) { if(file_put_contents($file,trim($o))===false) idie("Не удалось записать `areas`!<br>".$file); }

	return "
delete are[$arean];
lastaren=0; aren0=[]; for(var i in aren) { if(lastaren<0) lastaren=i; if(aren[i]!=$arean) aren0.push(aren[i]); esle lastaren=-1;
} aren=aren0; setpolozarea(lastaren);
clean('a$arean');
salert('Конференция <b>".strtoupper(h($area))."</b> удалена с ноды полностью<br>Не забудь ещё отписаться у аплинка!',10000);";
}


/*
ebasa[mya].i=0; - текущий номер в верхней строке экрана
ebasa[mya].b=0; - позиция бегунка
ebasa[mya].len=0; - нынешняя длина
ebasa[mya].st=0; - если N, то имеется еще N более новых записей, пока не закачанных
ebasa[mya].en=0; - если 1, то мы достигли конца архива
*/

if($a=='charea') {
//	if($GLOBALS['admin']) dier($_REQUEST);

	$arean=RE0('arean'); if(!$arean) $arean=area2arean(RE('area'),1); if(!$arean) idie("arean='$arean' area=".h($area)."");
	$area=RE('area'); if(!$area) $area=arean2area($arean);
	$id=RE('id');

	if($fido_point && empty($id)) { // последний непрочитанный
		$id=ms("SELECT * FROM $db_fidomy WHERE `point`='$fido_point' AND `arean`='$arean' AND `metka`='new' ORDER BY `id` DESC LIMIT 1","_1");
		$id=$id['id'];
	}

	return "
setmya($arean);
if(typeof ebasa[mya] == 'undefined') { ebasa[mya]=[]; ebasa[mya].i=0; ebasa[mya].b=0; ebasa[mya].len=0; ebasa[mya].st=0; ebasa[mya].en=0; }
".get_messages($area,$arean,$id)."
".($id?"ebasa[mya].st=".ms("SELECT COUNT(*) FROM $db_fido WHERE `AREAN`='$arean' AND `id`>'$id'","_l").";":'')."
echotype();
".(RE('nomsg')?'':"setHash('area:'+are[$arean]);")."
fidoselect('fidoarea');
".(RE('search')==''?'':"
var c=document.location.href;
document.location.href='#search_0';
document.location.href=c;
");
}
// 
if($a=='loadarea') {
	$arean=RE0('arean');
	$area=RE('area'); if(!$area) $area=arean2area($arean);
	$id=RE0('lastid');
	$pre=RE('pre')?1:0;
	$o=get_messages($area,$arean,$id,$pre);
	if($pre) $o.="echotype();";
	return $o;
}

} // ajax

//---------------------------------------------------------------

function msgn_all($arean,$all=0) { global $fido_point,$db_fido,$db_fidomy;
	if(!$fido_point||$all) return ms("SELECT COUNT(*) FROM $db_fido WHERE `AREAN`='$arean'","_l",0);

	if($GLOBALS['admin'] && 'netmail'==($area=arean2area($arean)) )
		return ms("SELECT COUNT(*) FROM $db_fidomy WHERE (`point`='$fido_point' OR `point`='1' OR `point`='0') AND `arean`='$arean'","_l",0);

	return ms("SELECT COUNT(*) FROM $db_fidomy WHERE `point`='$fido_point' AND `arean`='$arean'","_l",0);
}

function msgn_new($arean,$all=0) { global $fido_point,$db_fido,$db_fidomy;
if(!$fido_point||$all) return 0;
return ms("SELECT COUNT(*) FROM $db_fidomy WHERE `point`='$fido_point' AND `metka`='new' AND `arean`='$arean'","_l",0);
}

function get_messages($area,$arean,$id,$pre=0) { global $fido_node,$fido_point,$fido_myaddr,$fido_point,$db_fidomy,$db_fido,$fido_point,$fido_nmes;

$msq0="SELECT `DATETIME`,`BODY`,`id`,`SUBJ`,`FROMNAME`,`TONAME`,`FROMADDR`,`TOADDR` FROM $db_fido WHERE `AREAN`='$arean' ";
$msqid=($id?" AND `id`".($pre?'>':'<=')."'$id'":'')." ";
$msqord=($pre?"":" DESC")." LIMIT $fido_nmes";

	if($area=='netmail') {
		if(!$fido_point) return "salert('Netmail - только для поинтов станции',5000)";
		// получить емайл, но только свой или себе
		$pp=ms($msq0."AND (`FROMADDR`='$fido_myaddr' OR `TOADDR`='$fido_myaddr'"
// и админ пусть читает почту ноды /313 и /313.1
.($GLOBALS['admin']?"
 OR `FROMADDR`='".$fido_node."' OR `TOADDR`='".$fido_node."'
 OR `FROMADDR`='".$fido_node.".1' OR `TOADDR`='".$fido_node.".1'":'')
.")".$msqid."ORDER BY `id`".$msqord);
	} else {

	if($fido_point && RE('all')!=1) $pp=ms("SELECT i.`metka`,p.`DATETIME`,p.`BODY`,p.`id`,p.`SUBJ`,p.`FROMNAME`,p.`TONAME`,p.`FROMADDR`,p.`TOADDR`
 FROM $db_fidomy as i INNER JOIN $db_fido AS p ON i.`id`=p.`id`
 WHERE i.`point`='$fido_point' AND i.`arean`='$arean'
 ".($id?" AND i.`id`".($pre?'>':'<=')."'$id'":'')." ORDER BY i.`id`".$msqord);
	else $pp=ms($msq0.$msqid."ORDER BY `id`".$msqord); // получить из общей базы

// dier($_REQUEST);

	}

	$o=loadmsg($pp,$pre);
	if(!$pre && sizeof($pp)<$fido_nmes) $o.="ebasa[mya].en=1;"; // мы достигли конца, больше не запрашивать

//	$o.="salert('load: ".sizeof($pp)." all: '+ebasa[mya].len,2000);";

	return $o;
}
//----------------------------------------------------------------

function loadmsg($pp,$pre=0) { $eba='';
	include_once($GLOBALS['include_sys']."_obracom.php");
	foreach($pp as $p) {

	$toname=h(uw($p['TONAME'])); if($toname==$GLOBALS['fido_myname']) $toname="<span style='background-color:#fcc;'>".h($toname)."</span>"; else $toname=h($toname);
	$fromname=h(uw($p['FROMNAME']));
	$fromaddr=h(uw($p['FROMADDR']));
	$toaddr=($p['TOADDR']=='0'?'&nbsp;':h(uw($p['TOADDR'])));
	$subj=h(uw($p['SUBJ']));
	$body=obrabody1($p['BODY']);

	$body=str_replace('&gt;','|gt;|',$body); $body=AddBB($body); $body=str_replace('|gt;|','&gt;',$body);
	$body="\n$body\n"; $body=hyperlink($body);
	$body=trim($body,"\n\r\t ");

	$metka=($p['metka']=='new'?1:0);

	if(RE('search')) { $_GET['search']=RE('search'); // подсветка выделенных слов
		$body=search_podsveti_body($body);
		$fromname=search_podsveti_body($fromname);
		$fromaddr=search_podsveti_body($fromaddr);
		$toname=search_podsveti_body($toname);
		$toaddr=search_podsveti_body($toaddr);
		$subj=search_podsveti_body($subj);
	}
	$eba.=($pre?'unshift':'push').'id({id:'.$p['id'].',d:"'.$p['DATETIME'].'",f:"'.njsn($fromname).'",fa:"'.njsn($fromaddr)
		.'",t:"'.njsn($toname).'",ta:"'.njsn($toaddr)
		.'",s:"'.njsn($subj).'",b:"'.njsn($body).'",m:'.$metka.'});';
	}

return $eba;
}

// =====================================================================================
// =====================================================================================
// =====================================================================================
// =====================================================================================
// =====================================================================================
// =====================================================================================
// =====================================================================================
// =====================================================================================
function FIDO($e) {

STYLES("Фидо стили","
	.fidoa { font-size: 11px; cursor: pointer; color: blue;font-family: monospace;}
	.fidoa:hover { color: violet; font-family: monospace;}

	.fidm { border: 1px solid #ссс; font-family: monospace; font-size: 13px; cursor: pointer; color: blue;}
	.fidm:hover { border: 1px solid #cfcfcf; color: violet; font-family: monospace;}

	.fidome { border: 1px solid #333333; background-color: blue; }

.fidobody {
 white-space: pre-wrap; /* css-3 */
 white-space: -moz-pre-wrap; /* Mozilla */ 
 white-space: -pre-wrap; /* Opera 4-6 */ 
 white-space: -o-pre-wrap; /* Opera 7 */ 
 word-wrap: break-word; /* Internet Explorer 5.5+ */ 
font-family: monospace; font-size: 16px; font-color: #4F4F4F;
}
");

SCRIPTS("
var fido_point=".$GLOBALS['fido_point'].";
var omsg_new='".$GLOBALS['omsg_new']."';
var omsg_read='".$GLOBALS['omsg_read']."';
var fido_nmes=".$GLOBALS['fido_nmes'].";
var all=".(isset($_GET['all'])?1:0).";
var getsearch='".($_GET['search']?strtr($_GET['search'],"\"'","--"):'')."';
");
SCRIPT_ADD($GLOBALS['www_js']."fido.js");

$pan="<table width=100% border=0>";
	for($i=0;$i<$GLOBALS['fido_nmes'];$i++) $pan.="<tr id='pan".$i."'class='fidm' style='border: 1px solid transparent;'><td>".(!i?$panel:"&nbsp;")."</td></tr>";
$pan.="</table>";

$pixtolb=200;

if($GLOBALS['admin']) { $dupes=glob($GLOBALS['filehost']."fido/dupes/*"); $panel='- Admin
-- Erase Tables ('.sizeof($area).') | javascript:if(confirm("Охуел?")) majax("module.php",{mod:"FIDO",a:"admin_del_tables"})
-- Clean Dupes ('.sizeof($dupes).') | javascript:if(confirm("Охуел?")) majax("module.php",{mod:"FIDO",a:"admin_del_dupes"})
-- Copy Bundle | javascript:if(confirm("Охуел?")) majax("module.php",{mod:"FIDO",a:"admin_copy_file"})
-- Copy All | javascript:if(confirm("Охуел?")) majax("module.php",{mod:"FIDO",a:"admin_copy_allfiles"})
-- Delete Echo | javascript:if(confirm("Удалить эху "+are[mya]+"?")) majax("module.php",{mod:"FIDO",a:"delarea",arean:mya});
-- Uplink Echo | javascript:majax("module.php",{mod:"FIDO",a:"uplinkarea",arean:mya});
'.(is_file($GLOBALS['filehost']."fido/phfito.lock")?'DEL LOC FILE | javascript:if(confirm("Охуел?")) majax("module.php",{mod:"FIDO",a:"admin_clean_lock"})':'')
.'- Test | javascript:majax("module.php",{mod:"FIDO",a:"admin_test"})
'; } else $panel='';

$menu="
<table height='24' width='100%' background='".$GLOBALS['www_design']."silkway/silkway_menu.gif' border='0' cellpadding='0' cellspacing='0'><tr><td align='center'>{_MENU:

FIDO | http://lleo.aha.ru/fido
".($GLOBALS['fido_point']?"- все эхи | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"loadareas\",all:1})
- только мои | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"loadareas\",all:0})
- поинтлист | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"pointlist\"})
":"")."
$panel

".($GLOBALS['fido_myaddr']?
"ЭХА
- Пометить как прочитанные | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"allmsg_read\",arean:mya})
- Пометить как новые | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"allmsg_new\",arean:mya})
- Удалить все | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"allmsg_del\",arean:mya})
- Восстановить из базы | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"allmsg_restore\",arean:mya})

НАСТРОЙКИ<br>".$GLOBALS['fido_myaddr']." | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"settings_echo\"})
- Эхоподписка | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"settings_echo\"})
- Оформление | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"settings_podp\"})
"
:"ХОЧУ ПОИНТА! | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"joinme\"})")."

Toss | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"toss\"})

О ПРОЕКТЕ | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"about_help\"})
- движок ноды | javascript:majax(\"module.php\",{mod:\"FIDO\",a:\"about_help\"})
- движок сайта | http://lleo.me/blog/lleoblog

<input style='color: rgb(119, 119, 119);' size='7' value='поиск' onclick=\"majax('okno.php',{a:'fidosearch'})\" type='text'> | javascript:majax(\"okno.php\",{a:\"fidosearch\"})

_}</td></tr></table>";

// <div id='buka' style='border: 1px dotted red; color: green;'>test</div>
return $menu."<table width=100% border=0><td><div style=\"width: 100%; margin: 0 auto; text-align: left; padding: 0; position:relative;\">"

."<div style=\"float: left; width: ".$pixtolb."px; padding: 0; overflow-y: hidden; overflow: auto;\">
<div id='fidoarea' onclick=\"fidoselect(this.id)\" style='padding: 5px;'></div>
</div>"

."<div style=\"margin: 0 0 0 ".$pixtolb."px; padding: 0; border-left: #cfcfcf 2px solid; border-collapse:collapse;\">"

."<div id='echotags' onclick=\"fidoselect(this.id)\" style=\"padding-left: 10px; border: 3px dotted red;\">$pan</div>"

//."<div id='fidolist' onclick=\"fidoselect(this.id)\" style=\"padding-left: 10px; border: 3px dotted transparent;\">$panel</div>"

."<div style=\"height: 2px; width: 100%; border-top: #cfcfcf 2px solid;\"></div>"

."<div onclick=\"fidoselect('echotags')\" style=\"padding-left: 10px; border: 3px dotted transparent;\" id='fidomsg'></div>"

."</div><br class=q /></div></td></table>
";
}

function area2arean($area,$new=0) { global $areans,$db_fido_num; if(!isset($areans)) $areans=array();
// idie($area);
        if(isset($areans[$area])) return $areans[$area];
        $arean=ms("SELECT `echonum` FROM $db_fido_num WHERE `echo`='".e($area)."'","_l");
        if(!$arean && !$new) { msq_add($db_fido_num,array('echo'=>e($area))); $arean=msq_id(); }
        $areans[$area]=$arean; return $arean;
}

function arean2area($arean) { global $areans,$db_fido_num; if(!isset($areans)) $areans=array();
	$area=array_search($arean,$areans); if($area!==false) return $area;
	$area=ms("SELECT `echo` FROM $db_fido_num WHERE `echonum`='".intval($arean)."'","_l");
        if($area!==false) $areans[$area]=$arean; return $area;
}

function mojno_write($area,$toa) {
	if($GLOBALS['fido_point']) { // поинту
		if($area=='netmail') return; // Нетмайл можно точно
		$dostup=ms("SELECT `dostup` FROM ".$GLOBALS['db_fidopoints']." WHERE `point`='".$GLOBALS['fido_point']."'","_l");
		if($dostup=='writeall') return; // Разрешено писать во все эхи?
		if($dostup=='read') idie("Разрешено только читать конференции<br>и писать Нетмайл");
		$f=$GLOBALS['fidodir']."conf/disabled_write_area.txt"; if(!is_file($f)) return;
		$area=strtoupper($area);
		foreach(file($f) as $l) { if($area==trim(strtoupper($l))) idie("В конференцию <b>".h(strtoupper($area))."</b><br>писать запрещено правилами."); }
		return;
	} // простой посетитель: только нетмайл боссу
	if($area=='netmail' and $toa==$GLOBALS['fido_node']) return;
	idie("Можно написать только Netmail<br>и только на адрес сисопа: ".$GLOBALS['fido_node']);
}

function write_message_okno($zag,$area,$from,$froma,$to,$toa,
$subj,$text,$replyid,$dei=1) { mojno_write($area,$toa); return "
fido_reply_send=function(){ 

majax('module.php',{mod:'FIDO',a:'replytext',dei:$dei,
	area:vzyal('m_select_area'),from:'$from',
	froma:(idd('m_fido_froma')?vzyal('m_fido_froma'):'$froma'),
	to:(idd('m_fido_to')?idd('m_fido_to').value:\"$to\"),
	toa:(idd('m_fido_toa')?idd('m_fido_toa').value:\"$toa\"),
	subj:idd('m_fido_subj').value,
	text:idd('m_fido_text').value,
	replyid:'$replyid'});
helpc('fido_message_send',\"<fieldset><legend>FIDO: sending...</legend>Идет отправка...<img src='\"+www_design+\"img/ajax.gif'></fieldset>\");
};
	helpc('fido_message',\"<fieldset><legend>FIDO: $zag</legend>".njsn(
"<div><b><tt>AREA: <span alt='Выбрать другую эху' id='m_select_area' class=ll0 onclick=\"majax('module.php',{mod:'FIDO',a:'select_area',area:'".h($area)."'})\">".h(strtoupper($area))."</span></b></tt></div>
<div><b><tt>FROM: </b> $from ".($GLOBALS['admin']&&$GLOBALS['fido_point']!=0?
"<span alt='Другой адрес' id='m_fido_froma' class=ll0 onclick=\"majax('module.php',{mod:'FIDO',a:'select_froma',v:this.innerHTML})\">$froma</span>"
:$froma)."</tt></div>
<div><b><tt>TO:&nbsp;&nbsp; </b> $to $toa</tt></div>
<div><b><tt>SUBJ: </b><input id='m_fido_subj' type='text' size=80 value=\"".h($subj)."\"></div>
<div><textarea id='m_fido_text' cols='".$GLOBALS['fido_msg_cols']."' rows='".$GLOBALS['fido_msg_rows']."'>".$text."</textarea></div>
<div><input id='send' type='button' value='SEND' onclick='fido_reply_send()'></div>"
)."</fieldset>\");
	idd('m_fido_text').focus();
setkey('enter','ctrl',function(e){idd('send').click()},false);
";
}

// Ограничения по написанию: http://lleo.aha.ru/fido#area:n5020.bone|id:1208

function netmail_mojno(){ global $admin,$fido_myaddr,$fido_node;
	return "(`FROMADDR`='$fido_myaddr' OR `TOADDR`='$fido_myaddr'"
	// и админ, каким бы поинтом он ни был, всегда может читать нетмайл ноды
	.($admin?" OR `FROMADDR`='$fido_node' OR `TOADDR`='$fido_node'":'')
	.")";
}

function rescan_n($arean) { return "
zabil('omsga".$arean."',".msgn_all($arean).");
zabil('omsgn".$arean."',".msgn_new($arean).");
"; }

function parse_config($name,$all=0) {
	if(!isset($GLOBALS['config_phfito'])) {
		$configname="fido/conf/config"; $file=$GLOBALS['filehost'].$configname;
		if(($s=file_get_contents($file))===false) idie("Не найден файл конфиг PhFito: ".$configname);
		$GLOBALS['config_phfito']=$s;
	}
	$name=str_replace(" ",'\s',c(rtrim($name,'=')));

	if($all && preg_match_all("/[\n\r]+\s*".$name."[\s\=]+([^\n\r\#]+)/si","\n".$GLOBALS['config_phfito']."\n",$m)
	) return $m[1];
	if(preg_match("/[\n\r]+\s*".$name."[\s\=]+([^\n\r\#]+)/si","\n".$GLOBALS['config_phfito']."\n",$m)
	) return c($m[1]);
	idie("В конфиге PhFito нет переменной `$name`");
}

?>