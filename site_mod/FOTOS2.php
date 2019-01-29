<?php // Отображение всего фотоальбома или избранных

STYLE_ADD($GLOBALS['wwwhost']."slimbox/slimbox2.css");
SCRIPT_ADD("http://ajax.googleapis.com/ajax/libs/jquery/1.4/jquery.min.js");
SCRIPT_ADD($GLOBALS['wwwhost']."slimbox/slimbox2.js");

function FOTOS2($e) {
	$e=str_replace('WIDTH ','WIDTH=',$e); // для совместимости со старым говном
        $conf=array_merge(array('WIDTH'=>210),parse_e_conf($e));

 $pp=explode("\n",$e); $s=''; foreach($pp as $p) { $p=c($p); if($p=='' or strstr($p,'=')) continue;

	list($img,$txt)=explode(" ",$p,2); $img=c($img); $txt=c($txt);
	if($img=='WIDTH') { $WW=intval($txt); if(!$WW) return "<b>Неверное значение WIDTH в модуле FOTOS</b>"; continue; }

	if(!strstr($img,'/')) {
	list($y,$m,)=explode('/',$GLOBALS['article']['Date'],3); if($y*$m) $img=$GLOBALS['wwwhost'].$y.'/'.$m.'/'.$img;
	}

	$epre=preg_replace("/^(.*?)\/([^\/]+)$/si","$1/pre/$2",$img);
	$s.="\n\n<a href=\"".h($img)."\" title=\"".h($txt)."\" rel='lightbox-cats'><img src=\"".h($epre)."\" border='0' /></a>";
  }
  return "{_BLOKI: WIDTH=".$conf['WIDTH']."\n\n".$s."_}";
}

?>