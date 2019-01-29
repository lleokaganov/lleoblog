<?php // СКРИПТ ГОЛОСОВАНИЯ v.2
/*

ЭТОТ МОДУЛЬ ПОКА НЕ РАБОТАЕТ!!!
И ДОКУМЕНТАЦИИ К НЕМУ ПОКА НЕТ!!!!


Вот такой код вставляется в дневник:

{_ GOLOS: ИМЯ_ВАШЕГО_ГОЛОСОВАНИЯ:

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

// обратились к этому скрипту напрямую при посте?

$GLOBALS['GOLOS_db_golosa']='golosovanie_golosa';
$GLOBALS['GOLOS_db_result']='golosovanie_result';

function GOLO($e) { global $unic, $GOLOS_db_golosa,$GOLOS_db_result, $sc,$IP,$admin,$wwwhost,$mypage,$antibot_C;

	list($golosname,$vopr)=explode(':',$e,2); $golosname=c(h($golosname)); $vopr=golos_chit($vopr);

	$golosoval=ms("SELECT COUNT(*) FROM `$GOLOS_db_golosa` AS a, `$GOLOS_db_result` AS r WHERE a.unic='".$unic."' AND a.golosid=r.golosid AND r.golosname='".e($golosname)."'","_l");

//	if($admin) $golosoval=false;

	// взять результаты
	if($golosoval) {
		$s=ms("SELECT `text`,`n` FROM `$GOLOS_db_result` WHERE golosname='".e($golosname)."'",'_1');
		$go=unserialize($s['text']); $nn=$s['n'];
	}

	$s=''; if($admin) $s.=nl2br(golos_recalculate($golosname)).'<p>';

	$k=($nn?(640/$nn):0); // вычислилить коэффициент array_sum($go[$n])
	$kp=($nn?(100/$nn):0);

	$n=0; foreach($vopr as $vop=>$var) { $n++; $gr="golos_".$GLOBALS['article']['num']."_".$n;

	$s.="\n<p><b>$vop</b><br><ul>";

		foreach($var as $i=>$va) {

			if($golosoval) { // если голосовал
				$x=$go[$n][$i+1];
				$s .= "$va<br><img src=".$GLOBALS['www_design']."e/gol.gif width=".floor($k*$x)." height=14>\n<span class=br><b>".floor($kp*$x)."%</b> (".intval($x).")</span><p>";
			} else { // если не голосовал
				$s .= "\n<label><input name='".$gr."' type='radio' value='".($i+1)."'> $va</label><br>";
			}
		}

	$s.="</ul>\n";

	}


	$s="<center><table width=90% cellspacing=20><td align=left>".$s."</td></table></center>";


	if($golosoval) { // если голосовал

		$s = "<p>Проголосовали <b>$nn</b> человек:".$s."<p><center><b>спасибо, что проголосовали!</b></center>";
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

	if(!golos_update($golosname,$gol)) idie("Ошибка: вы уже голосовали!");
	golos_calculate($golosname,$gol);
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


function golos_update($name,$gol) { global $GOLOS_db_golosa,$GOLOS_db_result,$unic;
	$golosid=ms("SELECT `golosid` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'","_l");
	if(!$golosid) { msq_add($GOLOS_db_result, array('golosname'=>e($name)) ); $golosid=msq_id(); }
	return msq_add($GOLOS_db_golosa, array( 'unic'=>$unic,'golosid'=>$golosid,'value'=>e(serialize($gol)) ) );
}

function golos_calculate($name,$gol) { global $GOLOS_db_result;
	$g=ms("SELECT `n`,`text` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'","_1",0); if($g===false) $g=array();
	$n=$g['n'];
	$go=unserialize($g['text']); if($go===false) $go=array();
	foreach($gol as $i=>$j) $go[$i][$j]++; // добавить голос нынешний
	msq_add_update($GOLOS_db_result,array( 'name'=>e($name),'n'=>e(++$n),'text'=>e(serialize($go)) ),'name');
}



function golos_recalculate($name) { global $GOLOS_db_golosa,$GOLOS_db_result;

	$limit=1000;
	$start=0;

	$summ=0;
	$go=array();

	$mes='';

	$golosid=ms("SELECT `golosid` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'","_l");
	if(!$golosid) { msq_add($GOLOS_db_result, array('golosname'=>e($name)) ); $golosid=msq_id(); }

	$ct=0; while($ct++<100) {
		$pp=ms("SELECT `value` FROM `$GOLOS_db_golosa` WHERE `golosid`='".e($golosid)."' LIMIT $start,$limit","_a",0);
		if(!sizeof($pp)) break;
		$start+=$limit;
		foreach($pp as $p) {
			$g=unserialize($p['value']); if($g===false) { $mes.=' error 1'; break; }
			$summ++;
			foreach($g as $i=>$v) $go[$i][$v]++;
		}
	}

	$p=ms("SELECT `n`,`text` FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'",'_1',0);
	$go0=unserialize($p['text']); $summ0=$p['n'];

//	dier($go);

	$mmes='';

	if($summ!=$summ0) $mmes.="\nОПС! не сошлось число голосовавших: ".$summ0.", а правильно: ".$summ."\n";
	if(sizeof($go0)!=sizeof($go)) $mmes.="\nОПС! не равны суммы: в базе: ".sizeof($go0).", а правильно: ".sizeof($go)."\n";

	foreach($go as $i=>$g) {
	   if(sizeof($go0[$i])!=sizeof($g)) $mmes.="\n $i) не равны суммы: в базе: ".sizeof($go0[$i]).", а правильно: ".sizeof($g)."\n";
	   foreach($g as $k=>$l) if($go0[$i][$k]!=$l) $mmes.="\n $i($k): ".$go0[$i][$k]." != $l";
	}

	if($mmes=='') $mes .= '<font color=green>Пересчет: ВСЕ СОШЛОСЬ</font>'; else {
	$mes.=$mmes;
	if($GLOBALS['admin']) {
	$mes .= '<p><font color=red>UPDATE! '.
	msq_add_update($GOLOS_db_result,array( 'golosname'=>e($name),'n'=>e($summ),'text'=>e(serialize($go)) ),'golosname')
	.'</font>';

//	idie($GLOBALS['msqe']);
//	dier( ms("SELECT * FROM `$GOLOS_db_result` WHERE `golosname`='".e($name)."'",'_1',0) );
//	dier(array( 'golosname'=>e($name),'n'=>e($summ),'text'=>e(serialize($go)) ));

	}

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