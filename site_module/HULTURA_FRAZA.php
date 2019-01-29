<?php // Содержание дневника

if(!isset($GLOBALS['hultura_fraza'])) {
    $r=file($GLOBALS['filehost']."fraza.txt");
    $GLOBALS['hultura_fraza_all']=array(); foreach($r as $l) { $l=c0($l); if($l=='') continue; $GLOBALS['hultura_fraza_all'][]=$l; }
    $GLOBALS['hultura_fraza']=$GLOBALS['hultura_fraza_all'];
}

function HULTURA_FRAZA($e) {
    if(!sizeof($GLOBALS['hultura_fraza'])) $GLOBALS['hultura_fraza']=$GLOBALS['hultura_fraza_all'];

    $i=rand(0,sizeof($GLOBALS['hultura_fraza'])-1);
    $s=$GLOBALS['hultura_fraza'][$i];

    $r=array(); foreach($GLOBALS['hultura_fraza'] as $x=>$l) if($x!=$i) $r[]=$l;
    $GLOBALS['hultura_fraza']=$r;

    return $s;
}

?>