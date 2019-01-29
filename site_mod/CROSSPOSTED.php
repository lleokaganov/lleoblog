<?php // גûגמהטע ןמסעû ג סמצטאכלוהטא

function CROSSPOSTED($e) { global $ADM,$article,$www_design; $num=$article['num'];

// return "num: `$num`".
// dier($article);

$conf=array_merge(array(
'template'=>"<div align=right><table border=0><tr valign=top><td class=br><i>posted:&nbsp;</i></td><td class=br align=left>{tmpl}</td></tr></table></div>",
'tmpl'=>"<div><i>{adminkeys}<a href='{#link}'>{#link}</a></i></div>",
'adminkeys'=>"<i style='display:{ifdel}' title='delete post {#url}' class='e_remove' onclick=\\\"if(confirm('Delete?')) majax('protocol.php',{a:'del',nett:'{nett}',num:'{num}',n:'{n}'})\\\"></i>"
."&nbsp<i title='delete in list' class='e_list-remove' onclick=\\\"if(confirm('Delete in list?')) majax('protocol.php',{a:'dellist',nett:'{nett}',num:'{num}',n:'{n}'})\\\"></i>"
."&nbsp;"
),parse_e_conf($e));

// $r=ms("SELECT `net`,`url` FROM `socialmedia` WHERE `num`='".e($num)."'".ANDC(),"_a",0); if(!sizeof($r)) return "";

$r=ms("SELECT `net`,`id` FROM `socialmedias` WHERE `num`='".e($num)."' AND `type`='post'".ANDC(),"_a",0); if(!sizeof($r)) return "";

$js="var s='';";

include_once $GLOBALS['include_sys'].'protocol/protocols.php';

$s=''; foreach($r as $n=>$p) { list($net,$user)=explode(':',$p['net']);
$fn=$net.'_url'; if(!function_exists($fn)) continue; // idie("error protocol: ".h($net)." (".h($fn).")");
$url=call_user_func($fn,$p['id'],$user,'post');
$link=(strstr($url,'://')?$url:"unknown url: ".h($url)." ".h($p['id']));

$a=array('num'=>$num,'nett'=>$p['net'],'net'=>$net,'n'=>$n,'link'=>$link,'user'=>$user);

$a['adminkeys']='';

$s.=mpers($conf['tmpl'],$a);
}

return mpers($conf['template'],array_merge($a,array('tmpl'=>$s)));
}
?>