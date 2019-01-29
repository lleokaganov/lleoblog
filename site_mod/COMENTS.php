<?php /* Отображение статьи с каментами - дата передана в $Date

В случае отключенных комментариев есть возможность посылать говноедов на копию заметки в соцсети. В этом случае в
темплейте надо написать обращение типа такого, где указать протокол и идентификатор соцсети, а также темплейт,
например:



{_COMENTS:
socialmedia=lj:lleo:lj
#или socialmedia=autopost чтобюы найти тэг {_NO:autopost:flat:http://... url_}
off_socialmedia=<p>Комментарии к этой заметке на моем сервере отключены, надеюсь на понимание. Но вы можете пойти и <a href='{0}?mode=reply#add_comment'>оставить комментарий в моем журнале ЖЖ</a>. Учтите, что лично я там комментарии не читаю, но это дискуссионная зона для желающих обсудить.
_}

*/

/*
		SCRIPTS("page_onstart.push(\"var c=gethash_c(); if(c){ if(idd(c)) kl(idd(c)); else majax('comment.php',{a:'loadpage_with_id',page:0,id:c,dat:".$article['num']."});}"
."var r=f5_read('hidcom'+num); if(r) { r=r.split(','); for(var i in r) {if(r[i]) hide_comm(r[i]);} }"
."var r=f5_read('ueban'); if(r) { r=r.split(',');"
."var s=[],p=idd(0).getElementsByTagName('DIV'),i,j,u; if(p){for(i=0;i<p.length;i++){ u=1*p[i].getAttribute('unic'); if(!u) continue; if(!s[u]) s[u]=[]; s[u].push(p[i].id); }"
."for(i in r) { u=s[r[i]]; if(u) for(j in u) if(!isNaN(u[j])) hide_comm(u[j]); }"
."}}"
."\");");
*/

function COMENTS($e) { global $article, $podzamok, $load_comments_MS, $enter_comentary_days, $N_maxkomm, $idzan;

$conf=array_merge(array(
'ostalnye'=>LL('comm:ostalnye'), // остальные
'off'=>LL('comm:off'), //<p>Комментарии к этой заметке сейчас отключены, надеюсь на понимание.
'friends_only'=>LL('comm:friends_only'), //<p>К этой заметке оставить комментарий могут только друзья (например, ты).
'login_only'=>LL('comm:login_only'), //<p>К этой заметке оставить коментарий могут только залогиненные. Залогиниться можно <span class='ll' {majax}>здесь</span>
'login_only_done'=>LL('comm:login_only_done'), //<p>К этой заметке оставить коментарий могут только залогиненные. Залогиниться можно <span class='ll' {majax}>здесь</span>
'disabled'=>LL('comm:disabled'), //<p>Комментарии к этой заметке автоматически отключились, потому что прошло больше {1} дней или число посещений превысило {2}. Но если что-то важное, вы всегда можете написать мне письмо: <a href=mailto:{mail}>{mail}</a>
'disabled_login'=>LL('comm:disabled_login'), //<p>Комментарии к этой заметке были поначалу разрешены только залогиненным, но автоматически отключились и они, потому что прошло больше {1} дней или число посещений превысило {2}. Но если что-то важное, вы всегда можете написать мне письмо: <a href=mailto:{mail}>{mail}</a>
'screen'=>LL('comm:screen'), //<p>Комментарии к этой заметке скрываются - они будут видны только вам и мне.
'screen_nofriend'=>LL('comm:screen_nofriend'), //<p>Комментарии к этой заметке скрываются, но у друзей (у тебя) они будут открыты.
'comment_this'=>LL('comm:comment_this'), //<div id='commpresent' class='l' style='font-weight: bold; margin: 20pt; font-size: 16px;' {majax}>Оставить комментарий</div>
'future'=>LL('comm:future'), //<blockquote style='border: 3px dotted rgb(255,0,0); padding: 2px;'><font size=2>Заметка датирована будущим числом, и это просто значит, что прошлые дни заняты, а материал хотелось разместить.</font></blockquote>
'page'=>LL('comm:page'), //<div style='margin: 50px;'>{0}</div>
'button'=>LL('comm:button'), //<input TYPE='BUTTON' VALUE='Комментарии{dopload}: {podzamok?|открытых|} {idzan}' {majax}>
'nobutton'=>LL('comm:nobutton'), //Комментарии{dopload} {podzamok?|открытых|} {idzan}:
's'=>LL('comm:s'), //<div class=r style='margin: 50px;'>{0}</div>
'pro'=>LL('comm:pro'), //<div id=0>{0}<div></div></div>
'nocomments'=>LL('comm:nocomments'), //<p class=z>комментариев нет или они все скрыты
'itogo'=>LL('comm:itogo'), //<center><p class=br>всего комментариев: {nmas}</p>{u?<p>показаны только открытые комментарии - <span {majax}>показать все</a>||}</center>
'k'=>LL('comm:k'), //<input type='button' value='{n}' {majax}{u? disabled='disabled'||}>
'addprevnext'=>1 // 1- показывать превнекст внизу
),parse_e_conf($e));

//===================================
// как быть с комментариями?
$s='';

$dopload="";

$comments_form=true; // выводить форму приема комментариев
$comments_knopka=false; // выводить кнопку подкачки комментариев
$comments_list=false; // грузить простыню комментариев
$comments_screen=true;

$pro='';

	get_counter($article); // установить значение счетчика, если не было

$comments_timed=(
		$article["counter"] > $N_maxkomm // Превышение количества посещений
		|| $article["DateTime"] < time()-86400*$enter_comentary_days // Слишком старая заметка
		?true:false);

switch($article["Comment_view"]) { // Comment_view enum('on', 'off', 'rul', 'load', 'timeload')
	case 'on': $comments_knopka=false; $comments_list=true; break;
	case 'off': $comments_knopka=false; $comments_form=false; $comments_list=false; break;
	case 'rul': $comments_knopka=true; $comments_list=true; $load_comments_MS=" AND `rul`='1'";
$dopload=$conf['ostalnye']; // " остальные";
break;
	case 'load': $comments_knopka=true; $comments_list=false; break;
	case 'timeload': $comments_knopka=$comments_timed; $comments_list=!$comments_timed; break;
	}

switch($article["Comment_write"]) { // Comment_write enum('on', 'off', 'friends-only', 'login-only', 'timeoff', 'login-only-timeoff')
	case 'on': $comments_form=true; break;
	case 'off': $comments_form=false;
	    if(isset($conf['socialmedia'])) {

		    if(strstr($article['Body'],'{_NO:autopost:')) {
			$url=site_validate(preg_replace("/^.*\{\_NO\:autopost\:[a-zA-Z]+\:(.+?)\_\}.*$/s","$1",$article['Body']));
		        $s.=mpers($conf['off_socialmedia'],array($url)); // "Комментарии отключены";
			break;
		    }

	list($net,$user)=explode(':',$conf['socialmedia'],2);
	if(false!==($l=ms("SELECT `id` FROM `socialmedias` WHERE `num`='".$article['num']."' AND `net`='".e($conf['socialmedia'])."'".ANDC(),"_l"))
) {
    include_once $GLOBALS['include_sys'].'protocol/protocols.php';
    $fn=$net.'_url'; if(!function_exists($fn)) return "<font color=red>COMMENT: error protocol: ".h($net)." (".h($fn).")</font>";
    $url=call_user_func($fn,$l,$user);
    $s.=mpers($conf['off_socialmedia'],array($url)); // "Комментарии отключены";
} else $s.=$conf['off'];
} else $s.=$conf['off']; // "Комментарии отключены";
break;
	case 'friends-only': $comments_form=$podzamok; if($podzamok)
$s.=$conf['friends_only']; // "оставить комментарий могут друзья";
break;
	case 'login-only': $comments_form=$GLOBALS['IS']['loginlevel']==3?true:false;

$s.=mpers($comments_form?$conf['login_only_done']:$conf['login_only']
,array('0'=>$GLOBALS['IS']['imgicourl'],'majax'=>"onclick=\"ifhelpc('".$GLOBALS['httphost']."login','logz','Login')\""));
// "<p>К этой заметке оставить коментарий могут только залогиненные. Залогиниться можно здесь";
break;
	case 'timeoff': $comments_form=!$comments_timed; if(!$comments_form)
$s.=mpers($conf['disabled'],array('1'=>$enter_comentary_days,'2'=>$N_maxkomm,'mail'=>$GLOBALS['admin_mail']));
// "Комментарии отключились, потому что больше ".$enter_comentary_days." дней или посещений ".$N_maxkomm.". можете написать mailto";
break;
	case 'login-only-timeoff': $comments_form=($login?!$comments_timed:false); if(!$comments_form)
$s.=mpers($conf['disabled_login'],array('1'=>$enter_comentary_days,'2'=>$N_maxkomm,'mail'=>$GLOBALS['admin_mail']));
// "Комментарии были разрешены залогиненным, но отключились и они
break;
	}

switch($article["Comment_screen"]) { // Comment_screen  enum('open', 'screen', 'friends-open')
	case 'open': $comments_screen=false; break;
	case 'screen': $comments_screen=true; if($comments_form)
$s.=$conf['screen']; // "будут видны только вам и мне";
break;
	case 'friends-open': $comments_screen=!$podzamok; if($comments_form && $podzamok)
$s.=$conf['screen_nofriend']; // "у друзей (у тебя) они будут открыты.
break;
	}

if(strstr($_SERVER["HTTP_USER_AGENT"],'Yandex') || $GLOBALS['IP']=='78.110.50.100') { // роботу Яндекса
	$comments_form=false; // принимать комментарии - не надо (зачем Яндексу оставлять комментарии?)
	$comments_knopka=false; // простыню комментариев - выдавать с заметкой (Яндекс не умеет нажимать кнопку, а хотел бы индексировать)
	$comments_list=true;
	}

//===================================

if($comments_form) { // РАЗРЕШЕНО ОСТАВИТЬ КОММЕНТАРИЙ
	$s.= mpers($conf['comment_this'],array('majax'=>"onclick=\"majax('comment.php',{a:'comform',id:0,lev:0,comnu:comnum,dat:".$article['num']."});\"")); // Оставить комментарий
	if ( $article["DateTime"] > time() ) $s.=$conf['future']; // Заметка датирована будущим числом
}

	$idzan=get_idzan($article['num']);
if($idzan) { // если вообще есть комментарии
	if($comments_list) { // грузить простыню изначально
		if(isset($conf['comment_tmpl'])) $GLOBALS['comment_tmpl']=c($conf['comment_tmpl']);
		include_once $GLOBALS['include_sys']."_onecomm.php";
		$pro=load_comments($article,$conf['addprevnext']);
		SCRIPTS("page_onstart.push(\"var c=gethash_c(); if(c){ if(idd(c)) kl(idd(c)); else majax('comment.php',{a:'loadpage_with_id',page:0,id:c,dat:".$article['num']."});}"
."var r=f5_read('hidcom'+num); if(r) { r=r.split(','); for(var i in r) {if(r[i]) hide_comm(r[i]);} }"
."var r=f5_read('ueban'); if(r) { r=r.split(',');"
."var s=[],p=idd(0).getElementsByTagName('DIV'),i,j,u; if(p){for(i=0;i<p.length;i++){ u=1*p[i].getAttribute('unic'); if(!u) continue; if(!s[u]) s[u]=[]; s[u].push(p[i].id); }"
."for(i in r) { u=s[r[i]]; if(u) for(j in u) if(!isNaN(u[j])) hide_comm(u[j]); }"
."}}"
."\");");
	} elseif($comments_knopka) { // подгружать по кнопке
		$pro=mpers($conf['page'],array(get_comm_button($article['num'],$dopload,$comments_knopka)) );
		SCRIPTS("page_onstart.push(\"var c=gethash_c(); if(c) majax('comment.php',{a:'loadpage_with_id',page:0,id:c,dat:".$article['num']."});\");");
	}
}

return ($s!=''?mpers($conf['s'],array($s)):'').mpers($conf['pro'],array($pro))
.($GLOBALS['admin']?"".$GLOBALS['msqe']."":'')
;

}
?>