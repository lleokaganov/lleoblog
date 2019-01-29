<?php

function installmod_init(){ return "Clean old Tables"; }


function installmod_do() { global $o,$skip,$allwork,$delknopka,$lim,$msqe; $starttime=time(); $lim=100;

	$GLOBALS['starttime']+=600; // 10 мин

	$act=RE('act');

	if(empty($act)) otprav("

iwait=function(){ helpc('wait',\"<div style='padding:50px'>wait...</div>\"); };

oknof1=function(){ iwait();
majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',allwork:0,time:0,skip:0"
.",days:idd('days').value,act:'view'});
};

oknof2=function(){ iwait();
majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',allwork:0,time:0,skip:0"
.",days:vzyal('noday'),act:'delete'});
};

oknof3=function(){ iwait();
majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',allwork:0,time:0,skip:0"
.",days2:idd('days2').value,act:'clean_posetil'});
};

helpc('okno',\"".njsn("<fieldset><legend>Clean table `unic` from old anonymous</legend>
<table>
<tr><td>MySQL `dnevnik_posetil`:</td><td id='npos'>".ms("SELECT COUNT(*) FROM `dnevnik_posetil`","_l",0)."</td></tr>
<tr><td>older <input type='text' id='days2' value='90' size='5'> days:</td><td>
<input type='button' value='Clean' onclick='oknof3()'></td></tr>
<tr><td colspan=2>&nbsp;</td></tr>"

.($GLOBALS['db_unic']=="unic"? // только своя база
(msq_table("unic")?
"<tr><td>MySQL `dnevnik_comm`:</td><td>".ms("SELECT COUNT(*) FROM `dnevnik_comm`","_l",0)."</td></tr>
<tr><td>MySQL `dnevnik_plusiki`:</td><td>".ms("SELECT COUNT(*) FROM `dnevnik_plusiki`","_l",0)."</td></tr>
<tr><td>MySQL `unic`:</td><td id='nunic'>".ms("SELECT COUNT(*) FROM `unic`","_l",0)."</td></tr>
<tr><td>anonymous older <input type='text' id='days' value='90' size='5'> days:</td><td>
<span id='nodel'></span>
<input type='button' value='View' onclick='oknof1()'></td></tr>
":"<tr><td colspan=2 align=center><i>no table `unic`</i></td></tr>")
:
"<tr width=50%><td colspan=2>

<table width=500><tr><td><font color=green><small>Базу <b>".$GLOBALS['db_unic']."</b> можно чистить только с родного акаунта.
Не забудь прописать в его config.php через пробел все базы, которые используют его таблицу unic:
<br>\$db_unic_bases=\"dnev lleoaharu\";</small></font></td></tr></table>

</td></tr>"
)
."</table></fieldset>")."\");");

//<b><span id='noday'></span></b>
	// далее аякс

$days=RE0('days');

$t=(time()-86400*$days);
$WER_DEL_OLD="`openid`='' AND `password`='' AND `opt`='' AND `capchakarma`<2
AND `mail` NOT LIKE '!%' AND `tel` NOT LIKE '!%'

AND `id` NOT IN (SELECT `unic` FROM `dnevnik_posetil` WHERE `date`>$t)
AND `id` NOT IN (SELECT `unic` FROM `dnevnik_comm`)
AND `id` NOT IN (SELECT `unic` FROM `dnevnik_plusiki`)
";

// блять, а если две базы использует? $db_unic_bases=\"dnev lleoaharu\"; 
if(isset($GLOBALS['db_unic_bases'])) foreach(explode(' ',$GLOBALS['db_unic_bases']) as $l)
if(c($l)!='') $WER_DEL_OLD.="
AND `id` NOT IN (SELECT `unic` FROM `$l`.`dnevnik_posetil` WHERE `date`>$t)
AND `id` NOT IN (SELECT `unic` FROM `$l`.`dnevnik_comm`)
AND `id` NOT IN (SELECT `unic` FROM `$l`.`dnevnik_plusiki`)
";

// otprav("idie(\"".njs(nl2br($WER_DEL_OLD))."\");");

	if($act=='view') {
		$a=ms("SELECT COUNT(*) FROM `unic` WHERE $WER_DEL_OLD","_l",0);
		otprav("clean('wait');
zabil('nunic','<b>".ms("SELECT COUNT(*) FROM `unic`","_l",0)."</b>');
zabil('nodel',\"<b>$a</b> <input type='button' value='DELETE' onclick='oknof2()'> &nbsp; \");
");
	}

	if($act=='delete') { if($GLOBALS['db_unic']!="unic") otprav("idie('Error: \$db_unic!=`unic`');");

		$a=ms("SELECT COUNT(*) FROM `unic` WHERE $WER_DEL_OLD","_l",0);
		$a1=ms("DELETE FROM `unic` WHERE $WER_DEL_OLD","_l",0);

		otprav("clean('wait');
salert('Deleted: $a',3000);
zabil('nunic','<b>".ms("SELECT COUNT(*) FROM `unic`","_l",0)."</b>');
zabil('nodel','');
");
	}


if($act=='clean_posetil') {
		$days2=RE0('days2');
		$time=time();

		$allwork=RE0('allwork');
			if(!$allwork) $allwork=ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `date`<".($time-86400*$days2),"_l",0);

		$skip=RE0('skip');
		$module=RE('module');

//----------------
$WER="WHERE `date`<".($time-86400*$days2)." LIMIT 5000";

// =========================
$cikl=0; while((time()-$time)<2 && $skip<=$allwork) {
//                usleep(1000);

	$pp=ms("SELECT `unic`,`url` FROM `dnevnik_posetil` $WER","_a",0); if(!empty($GLOBALS['msqe'])) break;

	$r=array(); foreach($pp as $p) { if($p['unic']*$p['url']) $r[$p['url']]++; }

	foreach($r as $n=>$l) {
		msq("UPDATE `dnevnik_zapisi` SET view_counter=view_counter+$l WHERE `num`='$n'"); if(!empty($GLOBALS['msqe'])) break;
        }

	msq("DELETE FROM `dnevnik_posetil` $WER"); if(!empty($GLOBALS['msqe'])) break;

	$skip+=sizeof($pp);

	$cikl++;
}
// =========================

        if($skip>=$allwork) otprav("clean('wait');clean('percent');");
// 		$skip+=500;
//----------------
                otprav("
	var z=(idd('percent')?0:1);
helps('percent',\"<fieldset><legend>$module:$act &nbsp; $skip/$allwork [$cikl] &nbsp; \"+parseInt((100/$allwork)*$skip)+"
."\"% <span class='timet'></span></legend><div style='width:\"+(getWinW()/2)+\"px;'>"
."<div style='width:\"+(((getWinW()/2)/$allwork)*$skip)+\"px;height:16px;background:red;'>"
."</div></div>"
.($GLOBALS['msqe']==''?'':"<div>".$GLOBALS['msqe']."</div>")
."</fieldset>\");
	if(z) posdiv('percent',-1,-1);
dodo('$module',$allwork,$time,$skip,{act:'$act',days2:'$days2'});
");


	}

}

?>