<?php /* ¬ыбегающее сообщение

»з угла в центр (дл€ привлечени€ внимани€) выбегает сообщение с указанным текстом.
<script> function vcenter(id){ var e=idd(id).style; e.zIndex=(e.zIndex+1); var x = parseFloat(e.left) + 60; var y = parseFloat(e.top) + 50; if(x > getWinW()/2 || y > getWinH()/2) return; e.left = x+'px'; e.top = y+'px'; setTimeout(\"vcenter('\"+id+\"')\", 100); }</script>

{_MESSAGE: ѕоздравлени€! ¬ладелец дневника внес вас в список друзей! _}

*/

function MESSAGE($e) { global $message_n;
	$message_n++;

SCRIPTS("vcenter","

function vcenter(id) {
	var e=idd(id).style;
	var x = parseFloat(e.left) + 60;
	var y = parseFloat(e.top) + 50;
	if(x > getWinW()/2 || y > getWinH()/2) return;
	e.left = x+'px';
	e.top = y+'px';
	setTimeout(\"vcenter('\"+id+\"')\", 100);
}

");

return "<script>helps('message_".$message_n."',\"<fieldset><legend>message</legend><div style='text-align: justify;'>".njs(nl2br($e))."</div></fieldset>\");vcenter('message_".$message_n."');</script>";

}

?>