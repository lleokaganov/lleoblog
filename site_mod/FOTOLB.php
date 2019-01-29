<?php // Отображение всего фотоальбома или избранных

//STYLE_ADD("http://lumestudio.ru/design/lume/css/lightbox.css media='screen'");
SCRIPT_ADD("/design/lume/js/prototype.js");
SCRIPT_ADD("/design/lume/js/scriptaculous.js?load=effects,builder");
SCRIPT_ADD("/design/lume/js/lightbox.js");

function FOTO2($e) { // list($e,$s)=explode(':',$e,2); $e=c($e);
	$epre=preg_replace("/^(.*?)\/([^\/]+)$/si","$1/pre/$2",$e);
	// $epre=str_replace('-image-','-thumb-',$e);
	return "<div class='photo'><a href='".$e."' rel='lightbox[things]'><img src='".h($epre)."' /></a></div>";
}

?>