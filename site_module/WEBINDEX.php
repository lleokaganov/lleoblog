<?php

//===============================================================================

/* "setTimeout(\"salert(\\\"wait ".$n
."<p>".nl2br(dumpdb($GLOBALS['db_wi_ip']))
."<p>".nl2br(dumpdb($GLOBALS['db_wi_ipd']))
."<p>".nl2br(dumpdb($GLOBALS['db_wi_link']))
."<p>".nl2br(dumpdb($GLOBALS['db_wi_bro']))
."<p>".nl2br(dumpdb($GLOBALS['db_wi_ipn']))
."<p>".nl2br(dumpdb($GLOBALS['db_wi_ajax']))
."\\\",3500);" */


function dumpdb($db) { $pp=ms("SELECT * FROM ".$db,"_a",0);
	$s='<h2>--- '.h($db).' ---</h2>'; foreach($pp as $p) { $s.="\n";
	    foreach($p as $n=>$l) { if($n=='ipn'||$n=='IPN'||$n=='ipn_user') $l=$l." ".ipn2ip($l); $s.=" ".h($n).": ".h($l); }
	}
    return $s;
}

// $GLOBALS['memcache']=false;

function WEBINDEX_ajax() { $a=RE('a'); // запрос
    if($a=='ask') {
	if(($n=RE0('n'))>1) return "if(idd('ktosledit')) zabil('ktosledit','<img src=http://lleo.me/dnevnik/img/true.png> <b><font color=green>похоже, провайдер адреса не сливает (IP: <b>".$GLOBALS['IP']."</b>)</b></font>');";
	if(($q=wi_getflag())==false) return "setTimeout(\"salert('www',1000);majax('module.php',{mod:'WEBINDEX',a:'ask',n:".(++$n)."});\",4000);";

	wi_delflag();
	$bro=substr($q['BRO'],0,999);
	// сбросить следилки
	if(!ms("SELECT COUNT(*) FROM ".$GLOBALS['db_wi_bro']." WHERE `bro`='".e($bro)."'",'_l',0)) msq_add($GLOBALS['db_wi_bro'],arae(array('bro'=>$bro,'count'=>1)));
	else msq("UPDATE ".$GLOBALS['db_wi_bro']." SET count=count+1 WHERE `bro`='".e($bro)."'"); // увеличить счетчик

	$ipn=$q['IPN'];
	if(!ms("SELECT COUNT(*) FROM ".$GLOBALS['db_wi_ipn']." WHERE `ipn`='".e($ipn)."'",'_l',0)) msq_add($GLOBALS['db_wi_ipn'],arae(array('ipn'=>$ipn,'count'=>1)));
	else msq("UPDATE ".$GLOBALS['db_wi_ipn']." SET count=count+1 WHERE `ipn`='".e($ipn)."'"); // увеличить счетчик

	// удалить старые
	msq("DELETE FROM ".$GLOBALS['db_wi_ajax']." WHERE `time`<'".(time()-1000)."'");
	msq("DELETE FROM ".$GLOBALS['db_wi_link']." WHERE `time`<'".(time()-1000)."'");

	return "if(idd('ktosledit')) zabil('ktosledit',\"<img src='http://lleo.me/dnevnik/img/false.png' align=left> Вот гадство! Ваш провайдер <b>".h($q['net'])."</b> (IP: <b>".$GLOBALS['IP']."</b>) сливает все ваши посещения роботу <b>".h(ipn2ip($ipn)." ".h($bro))."</b>"
// .($GLOBALS['admin']?"<p><hr>"
//."<p>".nl2br(dumpdb($GLOBALS['db_wi_ip']))
//."<p>".nl2br(dumpdb($GLOBALS['db_wi_ipd']))
//."<p>".nl2br(dumpdb($GLOBALS['db_wi_link']))
//."<p>".nl2br(dumpdb($GLOBALS['db_wi_bro']))
//."<p>".nl2br(dumpdb($GLOBALS['db_wi_ipn']))
//."<p>".nl2br(dumpdb($GLOBALS['db_wi_ajax']))
//:'')
."\");";
    }

    if($a=='flush') { AD();
	msq("TRUNCATE TABLE ".$GLOBALS['db_wi_ip']);
	msq("TRUNCATE TABLE ".$GLOBALS['db_wi_ipd']);
	msq("TRUNCATE TABLE ".$GLOBALS['db_wi_link']);
	msq("TRUNCATE TABLE ".$GLOBALS['db_wi_bro']);
	msq("TRUNCATE TABLE ".$GLOBALS['db_wi_ipn']);
	msq("TRUNCATE TABLE ".$GLOBALS['db_wi_ajax']);
	unlink($GLOBALS['filehost']."site_module/__wi.log");
	return "salert(\"cleaned!<br>".$GLOBALS['msqe']."\");";
    }
}
//===============================================================================

function wilog($s){ return; $g=fopen($GLOBALS['filehost']."site_module/__wi.log","a+"); fputs($g,"\n".$s); fclose($g); }

function WEBINDEX($set) { global $IPN,$IP;

    if(!isset($GLOBALS['db_wi_ip'])) return ''; // нет баз - просто тихо выйти

// ========== обработчик страницы 404 =========

if($set=='404') {
//    if($GLOBALS['IP']!='79.165.179.127' && $GLOBALS['IP']!='92.242.35.54') return; // если не наши IP - вообще уходим

    if(false===($ipn=wi_loadlink($_SERVER['REQUEST_URI']))) return ''; // ищем нынешний линк - с какого ipn он был задан
    if($ipn==$IPN) return ''; // это сам и был


    // иначе изучим говно c ip и пометим в статистике

    // если не наш webindex, то попишем в логи
    if($GLOBALS['IP']!='92.242.35.54' && !stristr($GLOBALS['BRO'],'webindex')) { // если не наши IP - вообще уходим
	if(!isset($GLOBALS['wi_logfile'])
 || strstr("|".$IP,'|66.249.') // и Гугль с плавабющими IP и роботами
) return;
	$g=fopen($GLOBALS['filehost'].$GLOBALS['wi_logfile'],"a+"); fputs($g,"\n".$GLOBALS['IP']." ".$GLOBALS['BRO']
.($ipn==$IPN?' THE SAME':" (ipn: ".ipn2ip($ipn).")")
); fclose($g);
	return;
    }



    if(($r=wi_read($ipn))) { // если такая сетка уже зашкворена
	msq("UPDATE ".$GLOBALS['db_wi_ipd']." SET count=count+1 WHERE `i`='".e($r['i'])."'"); // увеличить счетчик
	// wilog("зашкворена! msqe: ".$GLOBALS['msqe']);
    } else { // если новенькая сетка
	if(!($r=wi_whois(ipn2ip($ipn)))) return ''; // // получить данные об IP не удалось
	wi_save($r['from'],$r['to'],$r['net'],$r['country']);
//        wilog("новенькая! msqe: ".$GLOBALS['msqe']);
    }
    // пометить флаг для аякса
    wi_setflag($ipn,$r['net']);
//    wilog("метим флаг `".$ipn."` msqe: ".$GLOBALS['msqe']);
    return '';
}


// =========== проверялка на странице ===========

// msq("DELETE FROM ".$GLOBALS['db_wi_link']." WHERE `ipn`='".$IPN."'");

    $link=wi_link(); $k=0; while($k++<100 && true!==wi_savelink($link) ) { $link=wi_link(); } // пометить линк

//     $o='';
    if($set!='1') return "<iframe src='".$link."' style='position:absolute;width:1px;height:1px;overflow:hidden;left:-40px;top:0;opacity:0'></iframe>"; // если тайная следилка - просто вернуть iframe
    return "<script>windsc=function(i){i=1*vzyal('windsc');if(i){zabil('windsc',i-1);setTimeout('windsc()',1000);}}; page_onstart.push(\"setTimeout('windsc()',1000);setTimeout(\\\"majax('module.php',{mod:'WEBINDEX',a:'ask',n:0});\\\",500);\");</script>";

//     "<input type=button onclick=\"majax('module.php',{mod:'WEBINDEX',a:'flush'});\" value='F L U S H'>"
//    $o.="<script>page_onstart.push(\"setTimeout(\\\"majax('module.php',{mod:'WEBINDEX',a:'ask',n:0});\\\",500);\");</script>";
//    if($GLOBALS['admin']) $o.="<div style='border:1px dotted green'>".$GLOBALS['msqe']."</div>";
    // if($GLOBALS['admin']) $o.="<div style='border:1px dotted #ccc'>".nl2br(h(fileget($GLOBALS['filehost']."site_module/__wi.log")))."</div>";
/*
    if($GLOBALS['admin']) $o.="<p><hr>"
    ."<p>".nl2br(dumpdb($GLOBALS['db_wi_ip']))
    ."<p>".nl2br(dumpdb($GLOBALS['db_wi_ipd']))
    ."<p>".nl2br(dumpdb($GLOBALS['db_wi_link']))
    ."<p>".nl2br(dumpdb($GLOBALS['db_wi_bro']))
    ."<p>".nl2br(dumpdb($GLOBALS['db_wi_ipn']))
    ."<p>".nl2br(dumpdb($GLOBALS['db_wi_ajax']));
*/
/*
if($GLOBALS['admin']) { $o.="<blockquote style='border: 1px dashed rgb(255,0,0); padding: 20px; margin-left: 50px; margin-right: 50px; background-color: rgb(255,252,223);'>";
    foreach(ms("SELECT `net`,`count` FROM ".$GLOBALS['db_wi_ipd']) as $x) { $o.="<div>".h($x['net'])." (".h($x['count']).")</div>"; }
}
*/
//    ."<div style='border:1px dotted red>".$GLOBALS['msqe']."</div>"
// return $o;

}

// ==================================================================================================

function wi_link() { $b=$GLOBALS['wwwhost'];
    return $b.ifnoem(vybmas('         dnevnik dnevnik dnevnik dnevnik dnevnik dnevnik dnevnik dnevnik blog issue news page bloger blogger user post public arhive arh base content',vybmas('   s')),'/')
.vybmas(' 00 01 02 03 04 05 06 07 08 09 10 11 12 13 14 15','/')
.vybmas(' 01 02 03 04 05 06 07 08 09 10 11 12','/')
.vybmas('01 02 03 04 05 06 07 08 09 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31')
.'.'.vybmas('htm htm htm htm html html html shtml shtml shtml shtml php php php php');
}
function ifnoem($s,$l) { return (empty($s)?'':$s.$l); }
function vybmas($s,$l='') { if(!strstr($s,' ')) return $s; $e=explode(' ',$s); $e=$e[rand(0,sizeof($e)-1)]; if($e=='') return ''; return $e.$l; }


function wi_setflag($ipn,$net) { global $IPN,$BRO,$memcache;
    if($memcache) { $z="wi_".$ipn;
	    if(memcache_get($memcache,$z)) return false; // если такое было
	    memcache_set($memcache,$z,$IPN."\n".$net."\n".$BRO,0,600); return true;
    }
    if(msq_add($GLOBALS['db_wi_ajax'],arae(array('ipn_user'=>$ipn,'IPN'=>$IPN,'net'=>$net,'BRO'=>$BRO,'time'=>time())))) return true;
    return false;
}

function wi_getflag() { global $IPN,$memcache;
    if($memcache) { $z="wi_".$IPN;
	if(($p=memcache_get($memcache,$z))) { $p=explode("\n",$p,3); return array('IPN'=>$p[0],'net'=>$p[1],'BRO'=>$p[2]); } // если было
	return false;
    }
    if(!($p=ms("SELECT `IPN`,`net`,`BRO` FROM ".$GLOBALS['db_wi_ajax']." WHERE `ipn_user`='".e($IPN)."'","_1",0))) return false;
    return $p;
}

function wi_delflag() { global $IPN,$memcache;
    if($memcache) { $z="wi_".$IPN;
	memcache_set($memcache,$z,false,0,1); memcache_delete($memcache,$z);
	return;
    }
    msq("DELETE FROM ".$GLOBALS['db_wi_ajax']." WHERE `ipn_user`='".e($IPN)."'");
    return;
}


function wi_savelink($link) { global $IPN,$memcache;
    if($memcache) { $link="wi_".$link; $z="wi_ipn_".$IPN;
	    if(memcache_get($memcache,$link) 
/*|| memcache_get($memcache,$z)*/) {
//		wilog("wi_savelink: $link present!");
return false; } // если такое было
	    memcache_set($memcache,$link,$IPN,0,600);
		wilog("wi_savelink: set $link = $IPN");
	    memcache_set($memcache,$z,1,0,600);
		wilog("wi_savelink: set $z = 1");
	    return true;
    }
    if(ms("SELECT COUNT(*) FROM ".$GLOBALS['db_wi_link']." WHERE `ipn`='".$IPN."'","_l",0)) return true; // если с этии IPN же работаем
    if(msq_add($GLOBALS['db_wi_link'],arae(array('link'=>$link,'ipn'=>$IPN,'time'=>time())))) return true;
    return false;
}

function wi_loadlink($link) { global $IPN,$memcache;
    if($memcache) { $link="wi_".$link;
	if(($l=memcache_get($memcache,$link))) return $l; // если было
	return false;
    }
    if(!($l=ms("SELECT `ipn` FROM ".$GLOBALS['db_wi_link']." WHERE `link`='".e($link)."'","_l",0))) return false;
    return $l;
}

function wi_whois($ip) {
        // $a=strtr(exec('whois '.escapeshellcmd($ip).' | tr "\n" "\001"'),"\001","\n");
	$a=''; exec("whois ".escapeshellcmd($ip),$a); $a=implode("\n",$a);
        $a=preg_replace("/^.*\ninetnum:\s+/si","\ninetnum: ",$a); // очистить до
        $a=preg_replace("/\nroute:\s.*$/si","",$a); // очистить после
	if(!preg_match("/\ncountry:\s+([a-z][a-z])\n/si",$a,$m)) return false; $country=strtoupper($m[1]);
	$net=''; if(preg_match("/netname: ([^\n]+)\n/si",$a,$m)) $net=c($m[1]);
	// if($country!='RU') return false;
	if(preg_match("/inetnum: (\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s+\-\s+(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/si",$a,$m)) { $from=ip2ipn($m[1]); $to=ip2ipn($m[2]); }
	else return false;
	return array('net'=>$net,'country'=>$country,'from'=>$from,'to'=>$to);
}

function wi_read($ipn) { return ms("SELECT d.`i`,d.`net` FROM `wi_ip` as g LEFT JOIN `wi_ipd` as d ON d.`i`=g.`i` WHERE ".$ipn.">=g.`from` AND ".$ipn."<=g.`to` LIMIT 1","_1"); }

function wi_save($from,$to,$net,$country) {
    // если есть такой IP
    if(ms("SELECT COUNT(*) FROM ".$GLOBALS['db_wi_ip']." WHERE ".$from.">=`from` AND ".$to."<=`to`","_l")) { $GLOBALS['msqe'].="error wi_ip base double"; return; }
    // есть ли такая сетка
    $i=ms("SELECT `i` FROM ".$GLOBALS['db_wi_ipd']." WHERE `net`='".e($net)."'","_l");
    if($i==false) { msq_add($GLOBALS['db_wi_ipd'],arae(array('net'=>$net,'count'=>1,'country'=>$country))); $i=msq_id(); } // такой сетки нет, создать
    return msq_add($GLOBALS['db_wi_ip'],array('i'=>$i,'from'=>$from,'to'=>$to));
}
?>