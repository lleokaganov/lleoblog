<?php // Отображение всего фотоальбома или избранных

//STYLE_ADD("http://lumestudio.ru/design/lume/css/lightbox.css media='screen'");
SCRIPT_ADD("/design/lume/js/prototype.js");
SCRIPT_ADD("/design/lume/js/scriptaculous.js?load=effects,builder");
SCRIPT_ADD("/design/lume/js/lightbox.js");

STYLES("блоки design.ru","
.thmbns {margin: margin: -3em 0 0 -2em; text-align:center;}
.thmbn {text-decoration:none; display: -moz-inline-box; display:inline-block; vertical-align:top; text-align:left; margin:3em 0 0 2em;}
.thmbn .rth {float:left;}
"); // width:210px; 

function FOTOS2($e) { // list($e,$s)=explode(':',$e,2); $e=c($e);
	$WW=210;
	$pp=explode("\n",$e);
	$s=''; foreach($pp as $p) { $p=c($p); if($p=='') continue;

		list($img,$txt)=explode(" ",$p,2); $img=c($img); $txt=c($txt);

			if($img=='WIDTH') {
				$WW=intval($txt);
				if(!$WW) return "<b>Неверное значение WIDTH в модуле FOTOS</b>";
				continue;
			}

                if(!strstr($img,'/')) {
                        list($y,$m,)=explode('/',$GLOBALS['article']['Date'],3); if($y*$m) $img=$GLOBALS['wwwhost'].$y.'/'.$m.'/'.$img;
                }

		$epre=preg_replace("/^(.*?)\/([^\/]+)$/si","$1/pre/$2",$img);

		$s.="<ins class='thmbn' style='width: ".$WW."px'><div class='rth' style='width:".$WW."px'>"
//		."<a href='".h($img)."' onclick='return bigfoto(this)'><img src='".h($epre)."' border=0></a>"
		."<div class='photo'><a href='".h($img)."' rel='lightbox[things]' title='".h($txt)."'><img src='".h($epre)."' /></a></div>"
		."<div class=r>".($txt!=''?$txt:'')."</div>"
		."</div></ins>";
	}
	return "<br class=q><div class=thmbns>$s</div>";
}

?>