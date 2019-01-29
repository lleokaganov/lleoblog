<?php

function LISTIH($e) { $cf=array_merge(array(
    'width'=>700
),parse_e_conf($e));

$s=c0($cf['body']);

$p=explode("\n",$s);
    if($p[1]=='') { // заголовок
	$p[0]="<p class=z>".$p[0]."</p>";
	$p[2]="{_CENTER:".$p[2];
    } else {
	$p[0]="{_CENTER:".$p[0];
    }
    if($p[sizeof($p)-2]=='') $p[sizeof($p)-1]="<p class=podp>".$p[sizeof($p)-1]."</p>"; // подпись
$s=implode("\n",$p);

return // "<table width=500 border=0><tr><td>".
"{_LISTIK: width=500\n".$s."_}_}"
// ."</td></tr></table>"
;
}
?>