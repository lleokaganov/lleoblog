<?php // тестовое решение

include "../config.php"; include $include_sys."_autorize.php";

$ajax=2;
header('Content-Type: text/html; charset='.$GLOBALS['www_charset']);

$_REQUEST=array(); foreach($_GET as $n=>$l) $_REQUEST[$n]=$l;

$mod=preg_replace("/[^a-z\_]/si",'',RE('mod'));

$file=$site_mod.$mod.'.php'; if(!file_exists($file)) {
	$file=$site_module.$mod.'.php'; if(!file_exists($file)) idie("Module not found: $mod");
} include_once($file);

if(!function_exists($mod.'_ajax')) idie("Function not found: ".$mod."_ajax");
otprav("ajaxoff();".call_user_func($mod.'_ajax'));

?>