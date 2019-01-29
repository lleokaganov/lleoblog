<?php // Содержание дневника

function HULTURACOMMENT($e) {
    $conf=array_merge(array('author'=>'anonym',),parse_e_conf($e));

$s=c0($conf['body']);

    return "<div style='margin-top:5px;margin-left:99px;'>"
    ."<img alt='".$conf['author']."' src='/img/c/".$conf['author'].".png' onclick=\"majax('mailbox.php',{a:'newform',unic:'"
.array_search($conf['author'],$GLOBALS['hultura_users'])."',quote:'> ".h(njsn($s))."'})\">&nbsp; "
.$s
."</div>";

}

?>