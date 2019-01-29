<?php // восстанавливаем unic

include "../config.php"; $autorizatio=false; include $include_sys."_autorize.php"; // сперва JsHttpRequest, затем autorize

if(!empty($mnogouser)) otprav('');

$up=RE('up');
$upo=RE('upo');
$num=RE0('num');
$i=(RE0('i')==1?'fc':'f5');

if(strstr($BRO,'blogtest')) otprav('');


$un=intval(substr($upo,0,strpos($upo,'-')));

if(!$un || !upcheck($upo) || getis_global($un)===false) { // пробовали восстановить, но данные неверны

logi('restore_unic-ERRPASS.txt',"\n $unic $num $i $up $upo");

otprav("
".$i."_save('up','');
unic_rest_flag=0;
salert(\"ќшибка при попытке восстановлени€ $i:<br>".h($upo)."\",1000);
");
}

// учтем посещение
if($num){ $e=$msqe; msq_add("dnevnik_posetil",array('unic'=>$unic,'url'=>$num)); $msqe=$e; }

logi('restore_unic.txt',"\n $unic $num $i $up $upo");

// otprav("salert('test',1000)");

otprav("
up='$upo'; realname=\"".$imgicourl."\";
fc_save('up',up); f5_save('up',up);
clean('loginobr_unic11');
helpc('work',\"<fieldset>¬осстановлен $imgicourl ($i)</fieldset>\");
setTimeout(\"c_save(uc,up,1);clean('work');zabilc('myunic',realname);\", 1000);
");

?>