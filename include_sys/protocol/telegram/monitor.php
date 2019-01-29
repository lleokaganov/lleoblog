<?php

// date_default_timezone_set("Etc/GMT-3");
header_remove('X-Powered-By');

function pr_td($pp,$time,$tab=1) { $o=''; if(sizeof($pp)) foreach($pp as $p) { $l=$p['sensor_id'];
    if(!isset($p['count'])) $p['count']='';

    $tim=$time-strtotime($p['time']); // промежуток времени от последнего измерения
    if($tim<(2*60+10)*60) $lampa='green'; // 2 часа и 10 минут
    elseif($tim< (2*60*60*24)) $lampa='yellow'; // 2 дня
    else $lampa='red';

    $art=array(
	h($p['time']),
	h($p['bot']),
	h($p['chat']),
	($p['user']?"<b>".h($p['name'])."</b>":"<font color=green>оператор</font>"),
	uw(	!$p['user']?"<font color=green>".h($p['text'])."</font>":h($p['text'])	),
	h($p['count']),
	"<span class=tm data-from=".$tim."></span>");

    if($tab) $o="newtr(['<i class=e_bird16></i>','".implode("','",$art)."']);".$o;
    else $o="<tr><td><i class=e_led".$lampa."></i></td><td>".implode("</td><td align=right>",$art)."</td></tr>".$o;
    }

    return $o;
}


// =========================================================================================================================================

$time=time();
$Q=$_SERVER['QUERY_STRING']; if(strstr($Q,'?')) { $Q=explode('?',$Q); $Q=$Q[sizeof($Q)-1]; } $Q=1*$Q;

if($Q) { // если аякс

    include "patch.php"; msq_connect();

//<------>    if(! intval(ms("SELECT COUNT(*) FROM `telezil_messages` WHERE `bot`='".e($BOT)."' AND `user`=0 AND `chat`='".e($chat)."'",'_l')) ) {

    $o=pr_td(
	ms("SELECT * FROM `telezil_messages`
AS m LEFT JOIN `telezil_users` AS u ON m.`user`=u.`id`
WHERE `time`>'".$Q."' ORDER BY `time` DESC LIMIT 50"),$time,1); // взять все новые
    $play=($o==''?'':"playswf('http://pripyachka.com/design/sound/bbm_tone.mp3');");
    die($o."if(typeof(GONE)=='undefined'||GONE==1){ ".$play."setTimeout(\"loadScr('/include_sys/protocol/telegram/monitor.php?".$time."')\",".(10000)."); ajaxoff();"."}");

} else { if(!function_exists('h')) die('ERROR SCRIPT NOT FOUND'); // не аякс




// dier(ms("SELECT * FROM `telezil_users`","_1"));








    $r=ms("SELECT DISTINCT `chat` FROM `telezil_messages`","_a");
    $e=array(); $dat=array(); foreach($r as $l) { $l=$l['chat'];
        $dat[$l]=ms("SELECT m.bot,m.chat,m.user,m.time,m.text
,u.date,u.nick,u.name
FROM `telezil_messages` AS m
LEFT JOIN `telezil_users` AS u ON m.`user`=u.`user`
WHERE m.chat='".e($l)."' ORDER BY m.time DESC LIMIT 1","_1");

    if($GLOBALS['msqe']!='') idie($GLOBALS['msqe']);

        $dat[$l]['count']=ms("SELECT COUNT(*) FROM `telezil_messages` WHERE `chat`='".e($l)."'","_l");
        $e[$l]=$dat[$l]['time'];
    } arsort($e);

// dier($dat);

    $tab=''; foreach($e as $l=>$t) $tab.=pr_td(array($dat[$l]),$time,0);

SCRIPTS("
GONE=0;
LASTTIME=".$time.";

function newtr(a) {
    var e=document.createElement('TR');
    e.className='tc';
    var o='',i; for(i in a) { o+='<td'+(i>2?' align=right':'')+'>'+a[i]+'</td>'; } e.innerHTML=o;
    o=idd('tbl');
    o.insertBefore(e,o.firstChild.nextSibling);
}

function datefrom(t) { var i,k,o='';
    i=60*60*24*365; k=Math.floor(t/i); if(k>0) return 'лет '+k;
    i=60*60*24*30; k=Math.floor(t/i); if(k>0) return 'месяцев '+k;
    i=60*60*24; k=Math.floor(t/i); if(k>0) return 'дней '+k;
    i=i/24; k=Math.floor(t/i); if(k>0) o+=(k>9?'':'0')+k+':'; t-=k*i;
    i=i/60; k=Math.floor(t/i); if(o!=''||k>0) o+=(k>9?'':'0')+k+':'; t-=k*i;
    o+=(t>9?'':'0')+t;
    return o;
}

function timeo(){
    doclass('tm',function(e){
	var t=1*e.getAttribute('data-from');
	e.setAttribute('data-from',t+1);
	zabil(e,datefrom(t));
    });
    if(GONE==0) return;
    setTimeout('timeo()',1000);
}

page_onstart.push('timeo()');
");


$o="<center><input type=button value='Start' onclick=\"GONE=1;loadScr('/KVANT/monitor.php?'+LASTTIME);setTimeout('timeo()',2000);clean(this);\" style='padding:30px;'>
<div class=r><p>доступ страницы: ".zamok($GLOBALS['article']['Access'])."</div>
</center>

<center><p>&nbsp;<p>&nbsp;<table border=1 cellspacing=0 cellpadding=5 style='padding:2px;border:1px solid #111'><tbody id=tbl><tr>
<td class=tz><span alt='номер'>i</span></td>
<td class=tz><span alt='время'>time</span></td>
<td class=tz><span alt='бот'>bot</span></td>
<td class=tz><span alt='чат'>chat</span></td>
<td class=tz><span alt='имя'>name</span></td>
<td><span alt='текст'>текст</span></td>
<td class=tz><span alt='сообщений'>сообщений</span></td>
<td class=tz><span alt='тому назад'>AGO</span></td>
</tr>".$tab."</tbody></table></center>";

}

?>