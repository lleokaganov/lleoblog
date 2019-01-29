<?php // Вывод фотки в линке

include_once $GLOBALS['include_sys']."_foto.php"; // фотовывод

function FOTOLINK($e) { list($f,$l,$t)=explode(',',$e,3); $f=c($f); $l=c($l); $t=c($t);

	return "\n\n<div class=fotoa><a href='".h($l)."'><div class=fotot>".h($t)."</div>
<img src='{foto_www_preview}".h($f)."' hspace=5 vspace=5 border=0>
</a></div>";

}


?>