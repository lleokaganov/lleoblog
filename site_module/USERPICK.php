<?php

function USERPICK($e) {
$cf=array_merge(array(
    'template'=>"<div style=\"background:url('{jpg}');width:{W}px;height:{H}px;border-radius:15px;\"></div>"
),parse_e_conf($e));

    $g=glob($GLOBALS['filehost'].'design/userpick/*.jpg');
    $o='';
    foreach($g as $f) { $x=basename($f);
	list($W,$H,$itype)=getimagesize($f);
	$o.="\n\n<div class=r align=center><img src='".$GLOBALS['wwwhost']."design/userpick/".h($x)."' border=1>"
."<br>".h($x)."<br>".$W."x".$H."px"
."<div style='padding:5px;font-size:8px;border:1px solid #ccc;text-align:left;'>".h(mpers($cf['template'],array(
    'jpg'=>"{acc_link}design/userpick/".$x,
    'W'=>$W,
    'H'=>$H
    ))
)."</div>"
."</div>";
    }

return "{_BLOKI: WIDTH=200
".$o."
_}";
}

?>