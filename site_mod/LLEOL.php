<?php /* произведения

{_LLEOL: текст _}
*/

function LLEOL($e) {
	$conf=array_merge(array(
'color'=>"red",
'H'=>"45",
'W'=>"40",
'size'=>"1",
'template'=>"<table height='{H}' cellspacing='0' cellpadding='0' border='0'><tr>"
."<TD BGCOLOR='{color}'><img src='/t' width='{size}'></td><td><img src='/t' width='{W}' height='{size}'></td></tr></table>"
	),parse_e_conf($e));
return mpr('template',$conf);
}
?>