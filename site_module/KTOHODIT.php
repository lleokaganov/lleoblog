<?php

/* Кто ходит в гости по утрам

template={4}<i alt='написать сообщение' onclick="majax('mailbox.php',{a:'newform',unic:{0}})" class='e_kmail'></i><span class=ll onclick="majax('login.php',{a:'getinfo',unic:{0}})">{3}</span> <a href={1}>{2}</a>

0 - unic
1 - link
2 - header
3 - user name
4 - min:sec

*/

function KTOHODIT_ajax(){ $time_last=RE0('t'); $time=time();

    if($time<$time_last||($time-$time_last > 120)) return "salert('Timeout Error');";

$pp=ms("SELECT p.`url`,p.`unic`,p.`date`,u.`capchakarma`,u.`mail`,u.`admin`,u.`openid`,u.`realname`,u.`login`,u.`img`,u.`time_reg`,u.*,z.`Header`,z.`Date`
FROM `dnevnik_posetil` AS p LEFT JOIN ".$GLOBALS['db_unic']." AS u ON p.`unic`=u.`id` LEFT JOIN `dnevnik_zapisi` AS z ON z.`num`=p.`url`"
." WHERE p.`date`>".$time_last." LIMIT 100","_a",0);

if(sizeof($pp)) {
    $a=array(); foreach($pp as $p) { $p=get_ISi($p,"{name}"); $a[]="\"".njsn(
		$p['unic'].'|' // 0
		.get_link_($p['Date']).'|' // 1
		.$p['Date'].' - '.($p['Header']==''?'[...]':$p['Header']).'|' //2
		.$p['imgicourl'].'|' //3
		.date('i:s',$p['date']) //4
	    )."\"";
    }
}
    return "ktn(".$time.",[".implode(',',$a)."]);";
}



function KTOHODIT($e) {

$c=array_merge(array(
'sound'=>$GLOBALS['www_design']."sound/gogo.mp3",
'text'=>"<div id='ktt'></div>", // 'text'=>"<center><table border=0 cellspacing=0 cellpadding=3 id='ktt'></table></center>",
'interval'=>"5", // интервал в секундах
'node'=>'DIV', // 'node'=>'TR',
'template'=>
    "{4}<i alt='написать сообщение' onclick=\"majax('mailbox.php',{a:'newform',unic:{0}})\" class'e_kmail'></i>"
    ." <span class=ll onclick=\"majax('login.php',{a:'getinfo',unic:{0}})\">{3}</span> <a href={1}>{2}</a>"
),parse_e_conf($e));


SCRIPTS("ktohodit","
function ktn(time,a){

if(a.length) {
    var i,e,o,j,s,p; for(i in a) { s=\"".njsn($c['template'])."\";
    e=document.createElement('".$c['node']."');
    p=a[i].split('|');
    for(j in p) s=s.replace(new RegExp('\\\{'+j+'\\\}','g'),p[j]);
    e.innerHTML=s; o=idd('ktt'); o.insertBefore(e,o.firstChild); init_tip(o);
    }

    ".(empty($c['sound'])?'':"playswf('".$c['sound']."');")."
}

setTimeout(\"majax('module.php',{mod:'KTOHODIT',t:\"+time+\"});majax('mailbox.php',{a:'mail'});\",".(1000*$c['interval']).");
}

page_onstart.push(\"ktn(".time().",[])\");
");

return $c['text'];
}

?>