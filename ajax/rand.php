<?php // случайное число

include "../config.php"; include $include_sys."_autorize.php";

otprav("helps('random','Случайное число <b>".rand(RE("min"),RE("max"))."</b>, вот!');");

?>