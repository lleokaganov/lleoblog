<?php /* Таблица

Быстрое создание таблиц

{_TABLE:
название | номер | цена | количество
штатиф | 1 | 28.6 | 12121
коробка | 2 | 25.6 | 12ыва121
рукомойник | 3 | 24.6 | 12ывы121
швабра | 4 | 28.6 | 12121
персонал | 5 человек| 27 | 12121
_}
*/

function TABLE($e) {
	$s="<center><table border=1 cellspacing=0 cellpadding=10>";
	$p=explode("\n",$e);
		foreach($p as $l) { $l=c($l); $s.="<tr valign=top align=left><td>".preg_replace("/\s*\|\s*/s","</td><td align=right>",$l)."</td></tr>"; }
	return $s."</table></center>";
}

?>