<?php /* только дл€ подзамочных друзей

≈сли страницу открыл посетитель, с доступом 'podzamok' - выдаетс€ текст перед разделителем |, если обычный 'user' - то текст после |.

{_is_podzamok: »сходники тут: http://10.8.0.1/rrr.zip | »сходники решил пока не выкладывать. _}
{_is_podzamok:  ак много набежало идиотов! | _}

*/

function is_podzamok($e) {
        list($a,$b)=(strstr($e,'|')?explode('|',$e,2):array($e,''));

	if(!$GLOBALS['podzamok']) return c($b);
	$a=c($a);
	if(strstr($a,"\n")) return "<div style=\"background-color:".$GLOBALS['podzamcolor']."\">"
    ."<i class='e_podzamok'></i>&nbsp;"
    .$a."</div>";

	return "<span style=\"background-color:".$GLOBALS['podzamcolor']."\">$a</span>";

}

?>