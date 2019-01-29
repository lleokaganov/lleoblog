<?php // отладка
include "../config.php"; include $include_sys."_autorize.php";
ADH();


/*
	$file=rpath($filehost."log/ajax/".RE('f').".txt");
	testdir(dirname($file));
	$o=RE('o');

	$i=fopen($file,"a+"); fputs($i,"

------ ".date("Y-m-d h:i:s")." -------
".$o); fclose($i); chmod($file,0666);
*/
otprav("salert('save',100)");

?>