<?php

date_default_timezone_set("Etc/GMT-3"); header_remove('X-Powered-By');

if(!function_exists('h')) { // если аякс
    include "../config.php";
    include $include_sys."_autorize.php"; $ajax=1;
    AD();
    $a=RE('a');
    if($a='del'){
	$p=trim(RE('ids')); if(strstr($p,' ')) $p=explode(' ',$p); else $p=array($p);
	$o=''; foreach($p as $i) {
	    $o.="clean('tr".$i."');";
	    ms("DELETE FROM `kvant` WHERE `i`='".e($i)."'"); if($msqe!='') idie($msqe);
	}
	otprav("idd('seln').style.display='none';TBN=0;TBSEL={};".$o);
    }
    idie("Wrong: ".h($a));
} else {

// STYLE_ADD("/design/ico.css");

STYLE_ADD("/KVANT/css/pal.css");

STYLES("RFID","

.rfid {
    display:inline-block;
    margin:1px;
    font-weight:bold;
    font-size:10px;
    padding:2px 2px 2px 2px;
    border-radius: 40px 40px 40px 40px;
    border:2px solid black;
}


.et { font-size:12px; font-weight:bold; text-align:center; border-radius: 20px; }


/*
.etp".(0x05)." { background-color: #DDF; } .etp5:after { content:\" \\2798\"; }
.etp".(0x06)." { background-color: #FDD; } .etp6:after { content:\" \\279A\"; }
*/

.etp".(0x05)." { background-color: #00BFFF } .etp".(0x05).":after { content:\" RF_IN \"; }
.etp".(0x06)." { background-color: #FFDAB9 } .etp".(0x06).":after { content:\" RF_OUT\"; }
.etp".(0x0A)." { background-color: #FFF5EE } .etp".(0x0A).":after { content:\" US_IN \"; }
.etp".(0x0B)." { background-color: #ccccff } .etp".(0x0B).":after { content:\" US_OUT\"; }
.etp".(0x10)." { background-color: #66ff66 } .etp".(0x10).":after { content:\" TPRH  \"; }
.etp".(0x12)." { background-color: #ffcc99 } .etp".(0x12).":after { content:\" CODUST\"; }
.etp".(0x55)." { background-color: #cfcfcf } .etp".(0x55).":after { content:\" PING  \"; }

.etp".(0x23)." { background-color: #999999 } .etp".(0x23).":after { content:\" SND_OFF\"; }
.etp".(0x20)." { background-color: #00cfcf } .etp".(0x20).":after { content:\" BTN_EARL\"; }
.etp".(0x21)." { background-color: #bb7777 } .etp".(0x21).":after { content:\" POW_ON\"; }
.etp".(0x24)." { background-color: #EE9999 } .etp".(0x24).":after { content:\" SN_A_ALM\"; }
.etp".(0x26)." { background-color: #33FF33 } .etp".(0x26).":after { content:\" RD_SEND\"; }
.etp".(0x28)." { background-color: #BBFFBB } .etp".(0x28).":after { content:\" SN_OPRT\"; }
.etp".(0x2E)." { background-color: #BB5555 } .etp".(0x2E).":after { content:\" ALM_LGT\"; }
.etp".(0x2A)." { background-color: #FF5555 } .etp".(0x2A).":after { content:\" SN_TMOT\"; }
.etp".(0x2C)." { background-color: #22EEEE } .etp".(0x2C).":after { content:\" SN_A_DL\"; }
.etp".(0x2F)." { background-color: #22EEEE } .etp".(0x2F).":after { content:\" PAUSED\"; }
.etp".(0x2D)." { background-color: #22EEEE } .etp".(0x2D).":after { content:\" ARMED\"; }
.etp".(0x22)." { background-color: #22EEEE } .etp".(0x22).":after { content:\" WT_ARM\"; }


.rf0 { background-color: #CEE; }
.rf1 { background-color: #ECE; }
.rfi,.rfc { padding: 3px 20px 3px 20px; }
.rfi { min-width: 50px; text-align:right; }
.rfc { min-width: 100px; text-align:right; }

.tz {text-align:center;font-weight:bold;font-size:12px;}
.tc0,.tc1 {font-size:10px;}
.tc0 { background-color:#EEE; }
.tc1 { background-color:#FFF; }

.pager { margin-top:10px; }

.pageri a,.pagern a {text-decoration:none;}

.pageri,.pagern { margin: 2px 2px 2px 2px;
display:inline-block;
font-weight:bold;
font-size:12px;
cursor:pointer; border: 1px solid #888; padding:4px; border-radius: 20px 20px 20px 20px;

}

.pageri { background-color: #ccc; }
.pagern { background-color: none; }


.cb0,.cb1 { padding-left:10px; vertical-align:baseline; }
.cb1 { background:url(/KVANT/img/cancel1.png) no-repeat 0 8px; }
.cb0 { background:none; }

.seln { font-size:10px; display:none; }

TD { text-align:right; }

");

SCRIPTS("
TBSEL={};
TBN=0;


function tbl_delete(){
    var o=''; for(var i in TBSEL) if(TBSEL[i]==1) o+=' '+i;
    majax('http://pripyachka.com/KVANT/data.php',{a:'del',ids:o});
}


function tablego(){

    doclass('cb0',function(e){
	    var id=e; while(id && !id.id) id=id.parentElement;
	    id=id.id.replace(/^tr/,'');
	    e.onclick=function(){
		if(TBSEL[id]) { TBSEL[id]=0; e.className='cb0'; TBN--; }
		else { TBSEL[id]=1; e.className='cb1'; TBN++; }

		var seln=idd('seln');
		seln.style.display=(TBN?'block':'none');
		seln.value='Delete: '+TBN;
	    };
	});
};
page_onstart.push(\"tablego();opecha.n=0;clean('adminpanelka');\");

");


/*
function pager($go,$count,$skip,$lines,$reverse=0,$maxpos=20) {
    $onpage=ceil($count/$lines);
    $mypage=$onpage-ceil(($count-$skip)/$lines);
    $o=''; for($i=0;$i<$onpage;$i++) {
	    $ii=sprintf("%0".strlen($onpage)."d",$i+1); // ($reverse?$i+1:$onpage-$i));
	    if($i==$mypage) $o.="<div class=pagern>$ii</div> ";
	    else $o.="<div class=pageri><a href='/kvant/data/?".h($go)."lines=".h($lines)."&skip="
// .(($reverse?$i:$onpage-$i)*$lines)
.h($i*$lines)
."'>".h($ii)."</a></div> ";
    } return "<div class=pager>".$o."</div>";
}
*/


function pager($go,$count,$skip,$lines,$reverse=0,$maxpos=20) {
    $onpage=ceil($count/$lines);
    $mypage=$onpage-ceil(($count-$skip)/$lines);

    $kg=ceil(($maxpos-5)/2);

    $g1=$mypage-$kg;
    $g2=$mypage+$kg;
//      if($i>$onpage-$kg-2) $g1--;
//      if($i>=$onpage-$kg-2) $g1--;

        if($g1<0) { $g2-=$g1; $g1=0; }
        if($g2>=$onpage) { $g1=max(0,$g1-($g2-$onpage)); $g2=$onpage; }


    $o=''; for($i=0;$i<$onpage;$i++) {
        if( $onpage < $maxpos
    || $i<=1 || $i >= ($onpage-2) // начало и конец
    || ( $i >= $g1 && $i <= $g2 ) // в зоне отображения
        ) {
            if( $onpage >= $maxpos &&  (
                ( $i==1 && $g1 > 2 ) // левая грань слилась
                || ( $i == ($onpage-2) && $g2 < $onpage-3 ) // правая грань слилась
            )) {
                $o.="<span alt='kg=$kg<br>g1=$g1<br>g2=$g2<br>onpage=$onpage<br>i=$i'>&nbsp;&nbsp;...&nbsp;&nbsp;</span> ";
                continue;
            }
            
            $ii=sprintf("%0".strlen($onpage)."d",$i+1); // ($reverse?$i+1:$onpage-$i));
            $o.=($i==$mypage ? "<div class=pagern>$ii</div> " : "<div class=pageri><a href='/kvant/datab/?".h($go)."lines=".h($lines)."&skip=".h($i*$lines)."'>".h($ii)."</a></div> ");
        }
    } return "<div class=pager>".$o."</div>";
}



// if(isset($_GET['ping'])) { print "Ping OK"; }



$sn=1*RE0('n');
$skip=RE0('skip');
$lines=1*RE0('lines'); if(!$lines) $lines=100;

    if($_GET['mode']=='rfid') $WHERE="WHERE `sensor_id` IN (10,11,12,13,14,15) AND `event_type` IN (5,6)";
    elseif($_GET['mode']=='TPRH') $WHERE="WHERE `event_type`='16'";
    else $WHERE=($sn?"WHERE `sensor_id`='".e($sn)."'":'');

$count=intval(ms("select COUNT(*) FROM `kvant` ".$WHERE,"_l"));
if($skip===false) $skip=0;

// $skip=floor((($count-$skip)/$lines))*$lines; // idie($skip.'/'.$count);


$o="<center><b>Информация сенсора №".h($sn?$sn:'ALL')
." [$count записей]"
."</b>";

$get=$_GET; foreach(array('skip','lines') as $i) if(isset($get[$i])) unset($get[$i]);
$go=''; foreach($get as $n=>$i) $go.=$n."=".$i."&"; // $go=rtrim($go,'&');

$pager=pager($go,$count,$skip,$lines);

$o.=$pager;

// .selecto('',$sn,$bass,"name style='font-size:17px;' onchange=\"for(var i=0;i<this.length; i++)if(this.options[i].selected&&1*this.options[i].value){top.window.location='/data?info&n='+this.options[i].value;break;}\"")

//."<div>время: ".date("Y/m/d H:i:s")."</div>"
//."<input type=button value='Start' onclick=\"GONE=1;loadScr('/KVANT/pong.php?'+LASTTIME);setTimeout('timeo()',2000);clean(this);\" style='padding:0px;'>"
//."<div class=r><p>доступ страницы: ".zamok($GLOBALS['article']['Access'])."</div>"

// return $o='<hr>@@@';

if($_GET['mode']=='rfid') $o.="<p><table border=1 cellspacing=0 cellpadding=5 style='padding:2px;border:1px solid #111' bid=tbl><tr class=tz>"
."<td><span alt='номер записи'>i</span></td>"
."<td><span alt='время сервера'>time</span></td>"
."<td><span alt='номер сенсора 10-200'>sn</span></td>"
."<td><span alt='тип события, зарегистрированного датчиком'>e-type</span></td>"
."<td><span alt='номер RFID 1-64 тыс из базы'>RFID</span></td>"
."<td><span alt='секунд между двумя последними событиями датчика'>ago</span></td>"
."</tr>";

else $o.="<p><table border=1 cellspacing=0 cellpadding=5 style='padding:2px;border:1px solid #111' bid=tbl><tr class=tz>"
."<td><span alt='номер записи'>i</span></td>"
."<td><span alt='время сервера'>time</span></td>"
."<td><span alt='номер устройства 10-200'>base</span></td>"
."<td><span alt='номер сенсора 10-200'>sn</span></td>"
."<td><span alt='номер пакета 0-16777215 от устройства'>№pac</span></td>"
."<td><span alt='тип датчика, вызвавшего событие'>s-type</span></td>"
."<td><span alt='тип события, зарегистрированного датчиком'>e-type</span></td>"
."<td><span alt='кольцевой номер посылки'>№par</span></td>"
."<td><span alt='номер RFID 1-64 тыс из базы'>RFID</span></td>"
."<td><span alt='секунд между двумя последними событиями датчика'>ago</span></td>"
."<td><span alt='температура *100 (40.9 = 4090) -500..+1000'>T°</span></td>"
."<td><span alt='влажность 24% tinyint(3)'>Hum</span></td>"
."<td><span alt='давление'>P</span></td>"
."<td><span alt='запыленность, всех пылинок'>Z/all</span></td>"
."<td><span alt='запыленность, крупных пылинок'>Z/big</span></td>"
."<td><span alt='CO, ppm'>CO</span></td>"
."<td><span alt='CO<sub>2</sub>, ppm'>CO<sub>2</sub></span></td>"
."<td><span alt='вольтаж, X*100-150 перед заносом в базу'>V</span></td>"
."<td><span alt='версия прошивки датчика, вызвавшего событие'>Soft</span></td>"
."<td><span alt='сила радиосигнала -68'>pwr</span></td>"
."<td><span alt='сила wifi, %'>wifi</span></td>"
."<td><span alt='флаги разные'>flg</span></td>"
// ."<td><span alt='эпоха 1'>e1</span></td>"
// ."<td><span alt='эпоха 2'>e2</span></td>"
."</tr>";

// $link="/kvant/data?info&n=$sn&start=";

$pp=ms("SELECT * FROM `kvant` ".$WHERE." ORDER BY `datetime` DESC LIMIT ".e($skip).",".e($lines),"_a");

foreach($pp as $i=>$p) {

$RFD=sprintf("%03d",$p['RFID']);

if($_GET['mode']=='rfid') $o.="<tr class='tc".($i&1)."' id='tr".$p['i']."'>"
    ."<td class='cb0'>".sprintf("%07d",$p['i'])."</td>"
    ."<td>".h(date("Y-m-d H:i:s",$p['datetime']))."</td>"
    ."<td><a href=/kvant/data?n=".h($p['sensor_id'])." target=_blank>".h($p['sensor_id'])."</a></td>"
    ."<td>".h($p['event_type'])."</td>"
    ."<td><div class='rfid pal".$RFD."'>".$RFD."</div></td>"
    ."<td>".h($p['sec_ago'])."</td>"
    ."</tr>";

else $o.="<tr class='tc".($i&1)."' id='tr".$p['i']."'>"
    ."<td class='cb0'>"
//	."<input type='checkbox' class='css-checkbox' id='cb".$i."'>"
//	."<label for='cb".$i."' class='css-label lite-x-red'>".sprintf("%07d",$p['i'])."</label>"
    .sprintf("%07d",$p['i'])
    ."</td>"
    ."<td>".h(date("Y-m-d H:i:s",$p['datetime']))."</td>"
    ."<td>".h($p['base_id'])."</td>"
    ."<td><a href=/kvant/data?n=".h($p['sensor_id'])." target=_blank>".h($p['sensor_id'])."</a></td>"
    ."<td>".h($p['packet_num'])."</td>"
    ."<td>".h($p['unit_type'])."</td>"
    ."<td class='et etp".h($p['event_type'])."'>".h($p['event_type'])."</td>"
    ."<td>".h($p['parcel_num'])."</td>"
    ."<td>".($p['RFID']>1?"<div class='rfid pal".$RFD."'>".$RFD."</div>":'')."</td>"
    ."<td>".h($p['sec_ago'])."</td>"
    ."<td>".h($p['temperature']/100)."</td>"
    ."<td>".h($p['humidity'])."</td>"
    ."<td>".h($p['pressure']/10)."</td>"
    ."<td>".h($p['dust_all'])."</td>"
    ."<td>".h($p['dust_big'])."</td>"
    ."<td>".h($p['CO']/10)."</td>"
    ."<td>".h($p['CO2'])."</td>"
    ."<td>".h(($p['voltage']+150)/100)."</td>"
    ."<td>".h($p['soft'])."</td>"
    ."<td>".h(($p['signal_power']?'-'.$p['signal_power']:0))."</td>"
    ."<td>".h($p['wifi_power'])."</td>"
    ."<td>".h($p['flags'])."</td>"
//    ."<td>".h($p['epoch1'])."</td>"
//    ."<td>".h($p['epoch2'])."</td>"
    ."</tr>";

}

$o.="</table>"
."<p><input class='ik' type=button style='display:none' onclick='tbl_delete()' value='delete: 0' id='seln'>"
.$pager
."</center>\n\n\n<br>";

return $o;
}
?>