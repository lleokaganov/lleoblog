<?php // СКРИПТ ГОЛОСОВАНИЯ v.2
/*

ЭТОТ МОДУЛЬ ПОКА НЕ РАБОТАЕТ!!!
И ДОКУМЕНТАЦИИ К НЕМУ ПОКА НЕТ!!!!


Вот такой код вставляется в дневник:

{_GOLOS2: ПРИДУМАЙТЕ_ИМЯ_ВАШЕГО_ГОЛОСОВАНИЯ:

1. Как вы думаете, что это?
-- Это голосование
-- Это проверка, как работает модуль голосования
-- Это проверка, как работает модуль, а потом будут объяснения

2. Как вы вообще сюда попали?
-- Зашел случайно
-- Подписан на здешний RSS
-- Да так, брожу, наблюдаю...

3. Нравится ли вам движок дневника?
-- По-моему пора закругляться: для теста достаточно и двух пунктов
-- Я шутник и член партии "Шаловливая Россия"

_}
*/


$GLOBALS['GOLOS_db_golosa']='golosovanie_golosa';
$GLOBALS['GOLOS_db_result']='golosovanie_result';

// обратились к этому скрипту напрямую при посте?
if(isset($_POST['golos_return'])) {
		include "../config.php";
		include $include_sys."_autorize.php";

		if($GLOBALS['IS']['capcha']!='yes') {
			include $include_sys."_antibot.php";
			if(!antibot_check($_POST["code"],$_POST["hash"])) idie("Цифры с картинки указаны неверно, вернитесь.");
		}

		die(post_code());
	}



function GOLOS2($e) { global $unic,$GOLOS_db_golosa,$GOLOS_db_result,$IP,$admin,$www_design,$wwwhost,$mypage,$antibot_C;

	list($golosname,$vopr)=explode(':',$e,2); $golosname=c(h($golosname)); $vopr=golos_chit($vopr);

	$golosoval=ms("SELECT COUNT(*) FROM `$GOLOS_db_golosa` AS a, `$GOLOS_db_result` AS r WHERE a.unic='".$unic."' AND a.golosid=r.golosid AND r.golosname='".e($golosname)."'","_l");

//	if($admin) $golosoval=false;

	// взять результаты
	if($golosoval) {
		$s=ms("SELECT `text`,`n` FROM `$GOLOS_db_result` WHERE golosname='".e($golosname)."'",'_1');
		$go=unserialize($s['text']); $nn=$s['n'];

		$k=($nn?(640/$nn):0); // вычислилить коэффициент array_sum($go[$n])
		$kp=($nn?(100/$nn):0);
	} else {
		$nn=intval(ms("SELECT `n` FROM `$GOLOS_db_result` WHERE golosname='".e($golosname)."'",'_l'));
	}

	if($admin) $s.=nl2br(golos_recalculate($golosname)).'<p>';

	$s="<p>Уже проголосовали: <b>".$nn."</b>";

	$n=0; foreach($vopr as $vop=>$var) { $n++;
		$s.="\n<p><b>$vop</b><br><ul>";
		foreach($var as $i=>$va) {
			if($golosoval) { // если голосовал
				$x=$go[$n][$i+1];
				$s .= "$va<br><img src=".$www_design."img/gol.gif style='height:14px !important' width=".floor($k*$x).">\n<span class=br><b>".floor($kp*$x)."%</b> (".intval($x).")</span><p>";
			} else { // если не голосовал
				$gr = "grad_".$n."-".($i+1);
				$s .= "\n<input id='".$gr."' name='".$golosname."_".$n."' type='radio' value='".($i+1)."'><label for='".$gr."'> $va</label><br>";
			}
		}
	$s.="</ul>\n";
	}

	$s="<center><table width=90% cellspacing=20><td align=left>".$s."</td></table></center>";

	if($golosoval) { $s .= "<p><center><b>спасибо, что проголосовали!</b></center>"; // если голосовал
	} else { // если НЕ голосовал

if($GLOBALS['IS']['capcha']!='yes') {
	include_once $GLOBALS['include_sys']."_antibot.php";
	$ca="<input type=hidden name=hash value='".antibot_make()."'>
<table><tr valign=center>
	<td>антиспам:</td>
	<td>".antibot_img()."</td>
	<td><input class=t size=".$antibot_C." type=text name=code></td>
</tr></table>"; } else $ca='';


$s="<form name='golos_".$golosname."' method=post action='".$wwwhost."site_mod/GOLOS2.php'>
<input type=hidden name=golosname value='".$golosname."'>
<input type=hidden name=golos_return value='".$mypage."'>
<input type=hidden name=vopr value='".sizeof($vopr)."'>
".$s."
".$ca."
<input type='submit' value='ПРОГОЛОСОВАТЬ'>
</form>";

}


//$article['Body'] = preg_replace("/\{golosovalka[^\}]*\}/si",$s,$article['Body']);
return $s;
}


//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================

function post_code() {
	$golosname=$_POST['golosname'];
	$vopr=intval($_POST['vopr']);
	if(!$vopr) idie('error n');


	$gol=array(); for($i=1;$i<=$vopr;$i++) { $g=intval($_POST[$golosname.'_'.$i]);
		if(!$g) // dier($_POST);
idie("Нельзя оставлять пункты невыбранными.<br>Пожалуйста вернитесь и сделайте выбор.");
		else $gol[$i]=$g;
	}

	if(!golos_update($golosname,$gol)) idie("Ошибка: вы уже голосовали!"); // добавить голос, если не голосовал такой
	golos_calculate($golosname,$gol); // пересчитать результат
	redirect($_POST['golos_return']);
}

//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================
//=======================================================================================

// записать в базу голосов новый голос
function golos_update($name,$gol) { global $GOLOS_db_golosa,$GOLOS_db_result,$unic;
	$golosid=ms("SELECT `golosid` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'","_l");
	if(!$golosid) { msq_add($GOLOS_db_result, array('golosname'=>e($name)) ); $golosid=msq_id(); } // если не было - завести
	return msq_add($GOLOS_db_golosa, array( 'unic'=>$unic,'golosid'=>$golosid,'value'=>e(serialize($gol)) ) );
}

// учесть новый голос (пересчитать результаты)
function golos_calculate($name,$gol) { global $GOLOS_db_result;
	$g=ms("SELECT `n`,`text` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'","_1",0); if($g===false) $g=array();
	$go=unserialize($g['text']); if($go===false) $go=array();
	foreach($gol as $i=>$j) $go[$i][$j]++; // добавить голос нынешнего человека
	msq_add_update($GOLOS_db_result,array( 'golosname'=>e($name),'n'=>(intval($g['n'])+1),'text'=>e(serialize($go)) ),'name');
}


// Провести контрольный пересчет (при заходе Админа)
function golos_recalculate($name) { global $GOLOS_db_golosa,$GOLOS_db_result;

	$summ=0; // число людей
	$go=array(); // для результатов нового подсчета
	$mes=''; // строка для служебных сообщений

	$golosid=ms("SELECT `golosid` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'","_l"); // есть ли такое голосования?
	if(!$golosid) { msq_add($GOLOS_db_result, array('golosname'=>e($name)) ); $golosid=msq_id(); } // если нет - создать

	$limit=1000; // обсчитывать порциями по 1000 штук
	$start=0; // начиная с порции 0
	$stop=0; while($stop++<1000) { // ограничение от зависания - 1000 раз по 1000 голосов (1 млн голосовавших?)
		$pp=ms("SELECT `value` FROM `$GOLOS_db_golosa` WHERE `golosid`='".e($golosid)."' LIMIT $start,$limit","_a",0);
		if(!sizeof($pp)) break;
		$start+=$limit;
		foreach($pp as $p) {
			$g=unserialize($p['value']); if($g===false) { $mes.=' error 1'; break; } // если формат неправильный - ошибка
			foreach($g as $i=>$v) $go[$i][$v]++; // учесть голос человека по каждому пункту
			$summ++; // счетчик людей +1
		}
	}


	$mmes=''; // строка для дополнительных служебных сообщений

	$p=ms("SELECT `n`,`text` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'",'_1',0);
	$go0=unserialize($p['text']); // результаты прежнего подсчета
	$summ0=$p['n']; // сумма прежнего подсчета

	if($summ!=$summ0) $mmes.="\nОПС! не сошлось число голосовавших: ".$summ0.", а правильно: ".$summ."\n";
	if(sizeof($go0)!=sizeof($go)) $mmes.="\nОПС! не равны суммы: в базе: ".sizeof($go0).", а правильно: ".sizeof($go)."\n";

	foreach($go as $i=>$g) {
	   if(sizeof($go0[$i])!=sizeof($g)) $mmes.="\n $i) не совпали голоса: ".sizeof($go0[$i]).", а надо: ".sizeof($g)."\n";
	   foreach($g as $k=>$l) if($go0[$i][$k]!=$l) $mmes.="\n $i($k): ".$go0[$i][$k]." != $l";
	}

	if($mmes=='') $mes .= "<font color=green>Пересчет: ВСЕ СОШЛОСЬ</font>";
	else { $mes.=$mmes;
		// перезаписать:
		$mes .= "<p><font color=red>UPDATE: "
		.msq_add_update($GOLOS_db_result,array( 'golosname'=>e($name),'n'=>e($summ),'text'=>e(serialize($go)) ),'golosname')
		."</font>";
	}
	
return $mes;
}


function golos_chit($s) { // распознать голосовалку
	preg_match_all("/#+\n*([^#]+)/si","#".str_replace("\n\n","#",$s),$km);
	$vopr=array(); foreach($km[1] as $k=>$mm) {
		$z=trim( preg_replace("/^([^\n]+)\n.*$/si","$1",$mm) );
		preg_match_all("/\n+[\s\-]+([^\n]+)/si",trim($mm),$vv);
		if($z && sizeof($vv[1])) $vopr[$z]=$vv[1];
	}
	return $vopr;
}

?>