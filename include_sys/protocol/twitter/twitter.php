<?php
include_once $GLOBALS['include_sys'].'protocol/twitter/twitter.class.php';
include_once $GLOBALS['include_sys'].'protocol/_protocol_patchs.php';

function twitter_post($k1,$k2,$k3,$k4,$txt,$url='') {
    $twitter = new Twitter($k1,$k2,$k3,$k4);
    try { $s = $twitter->send($txt.' '.$url); // you can add $imagePath as second argument
    } catch (TwitterException $e) { return array(0,'Error: ' . $e->getMessage()); }
//    return nl2br(h(print_r($s,1)));
    $url='https://twitter.com/lleokaganov/status/'.$s->id_str;
    return array(1,$url,nl2br(h(print_r($s,1))));
}
?>