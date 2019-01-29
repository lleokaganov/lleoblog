<?php

function installmod_init(){ 
if(!$GLOBALS['aharu']) return false; // только для отладчика
return "Удалить опции заметок"; }
function installmod_do() { global $o,$skip,$allwork,$delknopka,$lim,$msqe; $starttime=time(); $lim=100;

	$name=c(RE('name'));
	$value=c(RE('value'));

$aska="oknof=function(){ majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',allwork:0,time:0,skip:0"
.",name:idd('del_name').value"
.",value:idd('del_value').value"
."});
};
helpc('okno',\"<fieldset><legend>Del option from `dnevnik_zapisi_opt`</legend>"
."<table><tr><td>name:</td><td><input type='text' id='del_name' value=\\\"".h($name)."\\\" size=20></td></tr>"
."<tr><td>value:</td><td><input type='text' id='del_value' value=\\\"".h($value)."\\\" size=20></td></tr>"
."</table><input type='button' value='submit' onclick='oknof()'>"
."</fieldset>\");";

	if(empty($name)) otprav($aska);

	$pp=ms("SELECT `num`,`value` FROM `dnevnik_zapisi_opt` WHERE `name`='".e($name)."'".($value!=''?" AND `value`='".e($value)."'":''),"_a",0);
	if($pp===false || !sizeof($pp)) otprav("salert('Not found!')");

	if($value!='') {
		msq("DELETE FROM `dnevnik_zapisi_opt` WHERE `name`='".e($name)."'".($value!=''?" AND `value`='".e($value)."'":''));
		otprav("salert('Deleted!',2000)");
	}

otprav("salert('name: $name<br>count:".sizeof($pp)."')");
}

?>