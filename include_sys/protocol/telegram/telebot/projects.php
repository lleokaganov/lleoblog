<?php

$WWWmySCR="/dnevnik/include_sys/protocol/telegram/telebot/projects.php";
$HTTPmySCR="http://lleo.me".$WWWmySCR;
$FILEmySCR="/var/www/home".$WWWmySCR;
$GLOBALS['FILEmyDIR']=dirname($FILEmySCR);

function mkt($p) { if(!isset($GLOBALS['tmpl_pro'])) $GLOBALS['tmpl_pro']=fileget($GLOBALS['FILEmyDIR']."/tmpl_pro.htm"); return mpers($GLOBALS['tmpl_pro'],$p); }
function mksc($p) { if(!isset($GLOBALS['tmpl_sce'])) $GLOBALS['tmpl_sce']=fileget($GLOBALS['FILEmyDIR']."/tmpl_sce.htm"); return mpers($GLOBALS['tmpl_sce'],$p); }

if(!function_exists('h')) {

    include "../../../../config.php"; include $include_sys."_autorize.php"; ADH();
    $a=RE('a');

//    dier($GLOBALS);
    if($IS['admin']!='podzamok') idie("Нужен подзамочный доступ");
    // ADMA();

    if($a=='add_project') { otprav(mpers(fileget($GLOBALS['FILEmyDIR']."/tmpl_project.htm"),array('ajax'=>$HTTPmySCR,
	    'n'=>(intval(ms("SELECT COUNT(*) FROM `telezil_projects`","_l"))+1),
		    'project_name'=>'','mail'=>'','comment'=>'','project_id'=>0,'header'=>'New Project')));
    }

    if($a=='edit_project') { $project_id=RE0('project_id');
		$p=ms("SELECT `project_name`,`mail`,`comment` FROM `telezil_projects` WHERE `project_id`='".e($project_id)."'","_1",0);
		if(!$p || $_GLOBALS['msqe']!='') idie("Error MySQL: ".$_GLOBALS['msqe']);
		$ara=array_merge($p,array('ajax'=>$HTTPmySCR,
		    'n'=>$project_id,
		    'project_id'=>$project_id,
		    'header'=>'Edit Project'));
		otprav(mpers(fileget($GLOBALS['FILEmyDIR']."/tmpl_project.htm"),$ara));
    }

    if($a=='add_project_') {
        $name=c(RE('project_name'));
	$project_id=RE0('project_id');
	$mail=c(RE('mail')); if($mail!='' && !mail_validate($mail)) idie("ERROR: Wrong email format");

	$ara=array(
	    'project_name'=>$name,
	    'mail'=>$mail,
	    'comment'=>RE('comment')
	);

	if($project_id) { // edit
	    msq_update('telezil_projects',arae($ara),"WHERE `project_id`=".$project_id); if($_GLOBALS['msqe']!='') idie("Error MySQL: ".$_GLOBALS['msqe']);
	    $ara['project_id']=$project_id;
	    otprav("zabil('project_".$project_id."',\"".njsn(mkt($ara))."\");clean('editproject".$project_id."');");
	}

        if(intval(ms("SELECT COUNT(*) FROM `telezil_projects` WHERE `project_name`='".e($name)."'","_l",0))) idie("Проект с таким именем уже существует!");
        msq_add('telezil_projects',arae($ara)); if($_GLOBALS['msqe']!='') idie("Error MySQL: ".$_GLOBALS['msqe']);
	$id=msq_id();

	$x='project_'.$id;
	$ara['date']=date("Y-m-d H:i:s");
otprav("
mkdiv('$x',\"".njsn(mkt($ara))."\",'project tc1',idd('projects'),0);
otkryl('$x');
projcolors();
addEvent(idd('$x'),'mouseover',function(){ tr(this,1) });
addEvent(idd('$x'),'mouseout',function(){ tr(this,0) });
clean('".h(RE('window'))."');
salert('Project #".h($id)." created',600);
");
    }

    if($a=='del_project') { if(RE0('project_id')=='') idie('Error ID'); $project_id=RE0('project_id');
	if(ms("SELECT COUNT(*) FROM `telezil_scenary` WHERE `project_id`='".e($project_id)."'","_l")) idie('ERROR: Project not empty. Delete all scenarys first!');
        ms("DELETE FROM `telezil_projects` WHERE `project_id`=".$project_id,"_l",0); if($_GLOBALS['msqe']!='') idie("Error MySQL: ".$_GLOBALS['msqe']);
	otprav("clean('project_".$project_id."');projcolors();");
    }

    // ============================================

    if($a=='open_project') { $project_id=RE0('project_id');

		$project_name=ms("SELECT `project_name` FROM `telezil_projects` WHERE `project_id`='".e($project_id)."'","_l",0);

		if($project_name == '0') {
			$pp=ms("SELECT DISTINCT `project_id` FROM `telezil_projects`","_a",0); foreach($pp as $n=>$l) $pp[$n]=$l['project_id'];
			$pp=ms("SELECT * FROM `telezil_scenary` WHERE `project_id` NOT IN (".implode(',',$pp).")","_a",0);
		} else $pp=ms("SELECT * FROM `telezil_scenary` WHERE `project_id`='".e($project_id)."'","_a",0);

		$o="";
		foreach($pp as $p) $o.="<div id='scenary_".$p['i']."'>".mksc($p)."</div>";
		$o.="<div class=l onclick=\"mujax({a:'add_scenary',project_id:".$project_id."})\"><div class='e_list-add'></div> add new scenary</div>";
		otprav("zabil('scenarys_".$project_id."',\"".njsn($o)."\");");
		idie($o);
    }

    if($a=='add_scenary') {
	    $project_id=RE('project_id'); if(!$project_id) idie('project_id = 0');

	    $ara=array(
		'ajax'=>$HTTPmySCR,
		'n'=>(intval(ms("SELECT COUNT(*) FROM `telezil_scenary`","_l"))+1),
		'project_name'=>ms("SELECT `project_name` FROM `telezil_projects` WHERE `project_id`='".e($project_id)."'","_l"),
		'project_id'=>$project_id,
		'header'=>'New Scenary'
	    );

	    $plist=explode(" ","i date scenary_name"
." lz_webhook lz_url lz_login lz_password lz_group lz_user lz_lang lz_err_message"
." tg_webhook tg_API_id tg_API_key tg_name tg_info tg_image tg_err_message tg_wait_message"
." command_list keywords name_template banlist");
	    foreach($plist as $l) $ara[$l]='';

	    otprav(mpers(fileget($FILEmyDIR."/tmpl_scenary.htm"),$ara));
    }

    if($a=='edit_scenary') { $i=RE0('i');
		$p=ms("SELECT * FROM `telezil_scenary` AS E
LEFT JOIN `telezil_projects` AS P
ON E.`project_id`=P.`project_id`
WHERE E.`i`='".e($i)."'","_1",0);

		if(!$p || $_GLOBALS['msqe']!='') idie("Error MySQL 01: ".$_GLOBALS['msqe']);

		$ara=array_merge($p,array('ajax'=>$HTTPmySCR,
		    'n'=>$p['project_id'],
		    // 'project_id'=>$project_id,
		    'header'=>'Edit Project'));

		otprav(mpers(fileget($FILEmyDIR."/tmpl_scenary.htm"),$ara));
    }

    if($a=='add_scenary_') {
        $name=c(RE('scenary_name'));
	$i=RE0('i');

	$plist=explode(' ',"project_id scenary_name"
." lz_url lz_login lz_group lz_user lz_lang lz_err_message"
." tg_API_id tg_API_key tg_name tg_info tg_image tg_err_message tg_wait_message"
." command_list keywords name_template banlist");

	$ara=array('lz_pass'=>md5(RE('lz_password'))); foreach($plist as $l) $ara[$l]=RE($l);

	if($i) { // edit
	    msq_update('telezil_scenary',arae($ara),"WHERE `i`=".$i); if($_GLOBALS['msqe']!='') idie("Error MySQL: ".$_GLOBALS['msqe']);
	    $ara['i']=$i;
	    $ara=ms("SELECT * FROM `telezil_scenary` WHERE `i`=".$i,"_1",0);
	    otprav("zabil('scenary_".$i."',\"".mksc($ara)."\"); clean('editscenary".$i."');");
	} // New
	    if(intval(ms("SELECT COUNT(*) FROM `telezil_scenary` WHERE `scenary_name`='".e($name)."'","_l",0))) idie("Сценарий с таким именем уже существует!");
	    msq_add('telezil_scenary',arae($ara)); if($_GLOBALS['msqe']!='') idie("Error MySQL: ".$_GLOBALS['msqe']);
	    $ara['i']=$i=msq_id();
	    otprav("mujax({a:'open_project',project_id:'".$ara['project_id']."'}); clean('".RE('window')."');");
    }

    if($a=='del_scenary') {
	$i=RE0('i');
        ms("DELETE FROM `telezil_scenary` WHERE `i`=".$i,"_l",0); if($_GLOBALS['msqe']!='') idie("Error MySQL: ".$_GLOBALS['msqe']);
	otprav("clean('scenary_".$i."')");
    }



/*
-- --------------------------------------------------------
--
-- Структура таблицы `telezil_scenary`
--
CREATE TABLE IF NOT EXISTS `telezil_scenary` (
    `i` smallint(10) unsigned NOT NULL auto_increment COMMENT 'id сценария',
    `project_id` smallint(10) unsigned NOT NULL COMMENT 'id проекта, к которому он относится',
    `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время создания',
        `lz_url` varchar(256) COMMENT 'URL сервера',
        `lz_login` varchar(256) COMMENT 'login',
        `lz_pass` char(32) COMMENT 'Позорище',
        `lz_group` varchar(128) COMMENT 'Группа',
        `lz_user` varchar(128) COMMENT 'Пользователь',
        `lz_lang` varchar(5) COMMENT 'Язык по умолчанию',
        `lz_err_message` varchar(512) COMMENT 'Сообщение о недоступности Партнера',
    `tg_API_id` bigint(20) unsigned COMMENT 'ИД бота telegram_API_myid',
    `tg_API_key` varchar(45) COMMENT 'Ключ API бота telegram_API_key',
    `tg_name` varchar(32) COMMENT 'Имя бота',
    `tg_info` varchar(512) COMMENT 'Инфо бота',
    `tg_image` varchar(128) COMMENT 'УРЛ картинки бота',
    `tg_err_message` varchar(512) COMMENT 'Сообщение о недоступности Партнера',
    `tg_wait_message` varchar(512) COMMENT 'Текст на ожидании',
        `command_list` text COMMENT 'Список команд',
        `keywords` text COMMENT 'текст (действие)',
        `name_template` varchar(128) COMMENT 'Настройка формирования имени пользователя',
        `banlist` text COMMENT 'Бан-листы абонентов На основании user_id',
PRIMARY KEY (`i`),
KEY `project_id` (`project_id`)
) ENGINE=XtraDB default CHARSET=utf8 COMMENT='база телеграм-юзеров' ;
*/


















    idie('Error '.h($a));
}

// ===================================================================

SCRIPTS("

function mujax(ara) { majax('".$HTTPmySCR."',ara) }

function tr(e,i) {
    if(i) {
	e.setAttribute('oldcolor',e.style.backgroundColor);
	e.style.backgroundColor='#A88';
    } else {
	e.style.backgroundColor=e.getAttribute('oldcolor');
    }
}

function trc(e) { mujax({a:'open_project',project_id:myid(e)}); }

function findid(e,cls) { while(e && ( !e.tagName || !e.id || e.id.indexOf(cls)<0 ) ) e=e.parentNode; e=e.id; return e.substring(cls.length); }
function myid(e) { return findid(e,'project_'); }
function mysid(e) { return findid(e,'scenary_'); }

function projedit(e) { mujax({a:'edit_project',project_id:myid(e)}); }
function projdel(e) { if(confirm('Delete project?')) mujax({a:'del_project',project_id:myid(e)}); }

function scenedit(e) { mujax({a:'edit_scenary',i:mysid(e)}); }
function scendel(e) { if(confirm('Delete scenary?')) mujax({a:'del_scenary',i:mysid(e)}); }

function projcolors() { var p=getElementsByClass('project',idd('projects')); for(var i=0;i<p.length;i++) if(typeof(p[i])=='object') p[i].className='project tc'+(i&1); }

");


function pager($go,$count,$skip,$lines,$reverse=0,$maxpos=20) {
    $onpage=ceil($count/$lines);
    $mypage=$onpage-ceil(($count-$skip)/$lines);

    $kg=ceil(($maxpos-5)/2);

    $g1=$mypage-$kg;
    $g2=$mypage+$kg;

        if($g1<0) { $g2-=$g1; $g1=0; }
        if($g2>=$onpage) { $g1=max(0,$g1-($g2-$onpage)); $g2=$onpage; }


    $o=''; for($i=0;$i<$onpage;$i++) {
        if( $onpage < $maxpos
    || $i<=1 || $i >= ($onpage-2) // начало и конец
    || ( $i >= $g1 && $i <= $g2 ) // в зоне отображения
        ) {
            if( $onpage >= $maxpos &&  (
                ( $i==1 && $g1 > 2 ) // левая грань слилась
                || ( $i == ($onpage-2) && $g2 < $onpage-3 ) // правая грань слилась
            )) {
                $o.="<span alt='kg=$kg<br>g1=$g1<br>g2=$g2<br>onpage=$onpage<br>i=$i'>&nbsp;&nbsp;...&nbsp;&nbsp;</span> ";
                continue;
            }
            $ii=sprintf("%0".strlen($onpage)."d",$i+1); // ($reverse?$i+1:$onpage-$i));
            $o.=($i==$mypage ? "<div class=pagern>$ii</div> " : "<div class=pageri><a href='/kvant/datab/?".h($go)."lines=".h($lines)."&skip=".h($i*$lines)."'>".h($ii)."</a></div> ");
        }
    } return "<div class=pager>".$o."</div>";
}



//     padding: 3px; /* Поля вокруг содержимого ячеек */
//     border: 5px groove #ccc /* Граница между ячейками */


STYLES("RFID","

.ic { background-size:20px;padding-left:20px;background-position: 0 1px; }
option { padding-left: 20px; padding-top: 3px; padding-bottom: 3px; }
option:hover { background-color: #eee; }

.proj_elements { padding-left: 20px; }
.proj_time { margin:0 20px 0 20px;font-size:8px;display:inline; }
.proj_name { font-size:20px;font-weight:bold;margin:2px 0px 2px 20px; }
.proj_comment { font-size:10px;margin:2px 0px 2px 20px; }
.proj_scenarys { margin-left:60px; }

.scen_time { margin:0 20px 0 20px;font-size:8px;display:inline; }
.scen_name { display:inline;font-size:20px;font-weight:bold;margin:2px 0px 2px 20px; }


.tbl {
    border-collapse: collapse; /* Убираем двойные линии между ячейками */
    border-radius: 40px;
    width: 80%;
    max-width: 800px;
}

.tz {text-align:center;font-weight:bold;font-size:14px; }

.tc0,.tc1 {
    font-size:12px;
    border:10px solid #ccc;
    border-width: 10px;
    border-color: white;
    border-radius: 20px;

//     padding: 3px; /* Поля вокруг содержимого ячеек */
//     border: 50px groove red /* Граница между ячейками */
}
.tc0 { background-color:#EEA; }
.tc1 { background-color:#FAF; }

.et { font-size:12px; font-weight:bold; text-align:center; border-radius: 20px; }

.pager { margin-top:10px; }
.pageri a,.pagern a {text-decoration:none;}
.pageri,.pagern { margin: 2px 2px 2px 2px;
display:inline-block;
font-weight:bold;
font-size:12px;
cursor:pointer; border: 1px solid #888; padding:4px; border-radius: 20px 20px 20px 20px;
}
.pageri { background-color: #ccc; }
.pagern { background-color: none; }

.cb0,.cb1 { padding-left:10px; vertical-align:baseline; }
.cb1 { background:url(/KVANT/img/cancel1.png) no-repeat 0 8px; }
.cb0 { background:none; }

.seln { font-size:10px; display:none; }

");














$o="<center><div class='l' alt='нажать, чтобы добавить новый проект' onclick=\"mujax({a:'add_project'})\"><div class='e_yes'></div> добавить проект</div></center>";

$skip=RE0('skip');
$lines=1*RE0('lines'); if(!$lines) $lines=100;

$count=intval(ms("select COUNT(*) FROM `telezil_projects`","_l"));
if($skip===false) $skip=0;

$get=$_GET; foreach(array('skip','lines') as $i) if(isset($get[$i])) unset($get[$i]); $go=''; foreach($get as $n=>$i) $go.=$n."=".$i."&";
$pager=pager($go,$count,$skip,$lines);

$o.=$pager;

/*
$o.="<center>
<table class=tbl><tr class=tz>"
."<td><span alt='Project id'>id</span></td>"
."<td><span alt='Created time'>time</span></td>"
."<td><span alt='Project name'>name</span></td>"
."<td><span alt='Contact e-mail'>mail</span></td>"
."<td><span alt='Commentary'>comments</span></td>"
."</tr>";
*/
$o.="<center><table width=80% border=0><tr><td id='projects'>";

$pp=ms("SELECT * FROM `telezil_projects` ORDER BY `date` DESC LIMIT ".e($skip).",".e($lines),"_a");

// dier($pp);

foreach($pp as $i=>$p) {
    $o.="<div onmouseover='tr(this,1)' onmouseout='tr(this,0)' class='project tc".($i&1)."' id='project_".$p['project_id']."'>"
    .mkt($p)
    ."</div>";
}

$o.="</td></tr></table></center>";

return $o;


return $o="<center><div class='l' alt='нажать, чтобы добавить новый проект' onclick=\"mujax({a:'add_project'})\"><div class='e_yes'></div> добавить проект</div></center>

<center><div class='pop2' style='position:relative !important;z-index:0 !important;max-width:600px;text-align:justify;font-size:13px;background-color:#eee'>
<fieldset><legend>Admin</legend>

<form onsubmit=\"return ajaxform(this,'bollogin.php',{a:'save_card'})\">

<p><fieldset><legend>Свойства проекта</legend><table border=0>
<tr><td width=80%>Имя проекта</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Ид проекта</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>E-mail для уведомлений  </td><td><input type='text' name='bot_id'></td></tr>
</table></fieldset>

<p><fieldset><legend>Свойства сценария</legend><table border=0>
<tr><td width=80%>Имя сценария</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Ид сценария</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Партнер А</td><td><select>
<option>одно гумно</option>
<option>другое гумно</option>
<option>третье гумно</option>
</select></td></tr>
<tr><td>Партнер В</td><td><select>
<option>одно гумно</option>
<option>другое гумно</option>
<option>третье гумно</option>
</select></td></tr>
</table></fieldset>

<p><fieldset><legend>Свойства Партнера ЛЗ</legend><table border=0>
<tr><td width=80%>URL сервера</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>login</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Позорище</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Группа</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Пользователь</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Язык по умолчанию</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Сообщение о недоступности Партнера</td><td><input type='text' name='bot_id'></td></tr>
</table></fieldset>

<p><fieldset><legend>Свойства Партнера ТГ</legend><table border=0>
<tr><td width=80%>Global webhook path</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>ИД бота</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Ключ API бота</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Имя бота</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Инфо бота</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>УРЛ картинки бота</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Сообщение о недоступности Партнера</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Текст на ожидании</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Список команд<div class=br>/команда --- текст (действие)</div></td><td><textarea rows=5></textarea></td></tr>
<tr><td>Список ключевых слов<div class=br>/слово --- текст (действие)</div></td><td><textarea  rows=5></textarea></td></tr>
<tr><td>Список кнопок</td><td><input type='text' name='bot_id'></td></tr>
<tr><td>Настройка формирования имени пользователя{_SNOSKA:project_name+project_id+user_id+fullname+username_}</td><td><input type='text' value='project_name+project_id+user_id+fullname+username' name='bot_id'></td></tr>
<tr><td>Бан-листы абонентов На основании user_id</td><td><textarea rows=10></textarea></td></tr>
</table></fieldset>

<label>Расширенный дебаг <input type='checkbox' name='mail_comment' title='присылать' {?mail_comment:|1: checked|*:|?}></label>


<p><center><input onclick=\"majax('eeelogin.php',{a:'do_logout'})\" value=\"разлогиниться\" type=\"button\"></center>

style='font-weight:bold; font-size:16px'

<p><input id='sbmt' type=submit value='Сохранить'>
</form>

</div></fieldset>";

?>