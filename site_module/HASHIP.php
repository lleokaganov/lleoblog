<?php // распознавалка водяных знаков

AD();

function HASHIP_ajax() { $s=RE('text');
	include $GLOBALS['include_sys']."_hashdata2.php"; hashinit();
	$s=nl2br(datahash($s));
	if(preg_match("/ u(\d+) t(\d+) /s",$s,$m)) {
		$s="<div>".date("Y-m-d H:i:s",$m[2])
		." <a href=\"javascript:majax('login.php',{action:'getinfo',unic:".$m[1]."})\">user:".$m[1]."</a>"
		."</div><hr>".$s;
	}

	return "helpc('water-meta',\"<fieldset><legend>water-meta</legend>".njsn($s)."</fieldset>\");";
}

function HASHIP($e) {

SCRIPTS("scr_hash","
function dohash(){
	var s=idd('hasharea').value;
	if(s=='') { alert('Error: empty text'); return; }
	majax('module.php',{mod:'HASHIP',text:s});
}
");

return "<center>Input text:"
."<p><textarea onclick='nokey()' id='hasharea' style='border: 1px solid #330000;' name='text' cols='80' rows='20'></textarea>"
."<br><input onclick='dohash();return false;' style='border: 1px solid #330000;' type='submit' value='Do'>"
."</center>";
}
?>