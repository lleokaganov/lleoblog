#!/usr/bin/php

<?php

exit;

$x=0;
$P=array();

for($i=0;$i<128;$i+=30) {
	for($j=0;$j<128;$j+=30) {
	    for($k=0;$k<128;$k+=30) { $P[]="'#".strtoupper(sprintf("%02x%02x%02x",$i,$j,$k))."'"; }
	}
}

shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);

$o=''; foreach($P as $i=>$l) $o.="\n".$l.",";

$o="song_colors=[ /* all: ".sizeof($P)." */\n".trim($o,"\n,")."];";

file_put_contents("colors.js",$o);
die(nl2br($o));

?>