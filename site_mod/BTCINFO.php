<?php
// курс биткоина

function BTCINFO($e){ if(!$GLOBALS['memcache']) return 'no-memcache';
    $cf=array_merge(array(
	'val'=>"USD",
	'floor'=>0
    ),parse_e_conf($e));

    $n='BTCinfo';

    if($cf['val']=="USD" && ($u=cache_get_raw('BTC'))) return $u;

    $x=cache_get($n); if($x===false) {
	include_once $GLOBALS['include_sys'].'_files.php';
	$x=fileget_timeout('https://blockchain.info/ticker',2); // 2 sec
	if($x===false) return 0;
        $x=(array)json_decode($x);

	$l=(array)$x[$cf['val']]; $l=floor($l['last']);
	logi('BTC.txt',date("Y-m-d H:i:s").": ".$l."\n");

	cache_set($n,$x,60);
    }

    if(!isset($x[$cf['val']])) return 'error val: '.h($cf['val']);
    $x=(array)$x[$cf['val']];
    $x=$x['last'];
    if($cf['floor']) $x=floor($x);
    return $x;
}
?>