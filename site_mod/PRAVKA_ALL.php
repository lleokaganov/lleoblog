<?php // ЖЖ определялка

include_once($GLOBALS['include_sys'].'_podsveti.php');

function zapodsveti($s,$from,$to) {
        $i=strpos($s,$from); if(!$i) return $s;
        return substr($s,0,$i).podsvetih(podsveti($to,$from)).substr($s,$i+strlen($from));
}

function PRAVKA_ALL($e) { global $article;
	$in="@dnevnik_zapisi@Body@Date@".$article['Date'];
	$sql = ms("SELECT * FROM `".$GLOBALS['db_pravka']."` WHERE `Date`='".e($in)."' ORDER BY `DateTime`",'_a',0);
	foreach($sql as $p) $e=zapodsveti($e,$p['text'],'<span class=search>'.$p['textnew'].'</span>');
	return $e;
}

?>