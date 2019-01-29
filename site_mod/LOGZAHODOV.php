<?php /* жирный текст

Указанный текст выделяется жирным (заключается в тэг &lt;b&gt;).
Никакого сакрального смысла в этом модуле нет, просто он был сделан первым ради теста.

{_B:жирный_} текст
*/

function LOGZAHODOV($e) {

	$is=$GLOBALS['IS']; unset($is['password']);

	$s="\n\n".date("Y-m-d H:i:s")." ".$GLOBALS['mypage']."
".$GLOBALS['IP']." ".$GLOBALS['BRO']."
".print_r($is,1);

	logi("LOGZAHODOV_".preg_replace("/[^a-z0-9_\.]+/si","_",$GLOBALS['article']['Date']).".txt",$s);

	return "";

}
?>