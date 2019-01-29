<?php /* lj-user

был в гостях у {_LJ:a_young_}
*/

function LJ($e) { $e=h($e);
	if(substr($e,0,1)=='_') return "<a href=http://users.livejournal.com/$e>$e</a>";
	return "<a href=http://".$e.".livejournal.com>$e</a>";
}
?>