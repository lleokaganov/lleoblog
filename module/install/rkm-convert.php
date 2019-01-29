<?php

// Эта функция возвращает false, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.
function installmod_init() {
    if(!is_file($GLOBALS['filehost']."hidden/log/rkm1.log")) return false; // только для отладчика
return "RKM-convert"; }

// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать, allwork - общее количество (измерено ранее), $o - то, что кидать на экран.
function installmod_do() { global $o,$skip,$allwork,$delknopka; $starttime=time();

    $RKM=fileget($GLOBALS['filehost']."hidden/log/rkm1.log"); $RKM=explode("\n\n",$RKM);


	while((time()-$starttime)<2 && $skip<$allwork) {
	    $ara=array();
	    $R=$RKM[$skip]; $R=trim($R);
	    $R=explode("\n",$R);


#    [0] => 2013-08-28 15:01:19 
    $x=$R[0]; if(!preg_match("/^(\d\d\d\d\-\d\d\-\d\d \d\d\:\d\d\:\d\d)\s+/s",$x,$m)) {
	    $o.="<div><font color=red>"."Error Time 1: ".h($x)."<p>".nl2br(h(print_r($R,1)))."</font></div>";
	    $skip++;
	    continue;
    }
    $x=strtotime($m[1]); if(1*$x==0) idie("Error Time 2: ".h($x)."<p>".nl2br(h(print_r($R,1)))); $ara['Time']=$x;

#    [1] => info: IP: 19.6.6.0/22 eerrer
    $n='info'; $x=$R[1]; if(!preg_match("/^".$n."\:\s+(.+)$/s",$x,$m)) idie("Error ".$n."::"."<p>".nl2br(h(print_r($R,1)))); $x=$m[1]; $ara[$n]=$x;

#    [2] => link: http://lleo.me/dnevnik/2011/02/09.html
    $n='link'; $x=$R[2]; if(!preg_match("/^".$n."\:\s+(.+)$/s",$x,$m)) idie("Error ".$n."::"."<p>".nl2br(h(print_r($R,1)))); $x=$m[1]; $ara[$n]=$x;

#    [3] => IP: 19.2.6.51 19.2.1.51
    $n='IP'; $x=$R[3]; if(!preg_match("/^".$n."\:\s+([\d\.]+) ([\d\.]+)$/s",$x,$m)) { $o.="<div><font color=red>Error ".$n.":: ".h(print_r($R[3],1))."</font></div>"; $x1=$x2=0; }
    else {
	$x1=ip2ipn($m[1]); if(1*$x1==0) idie("Error ip1");
        $x2=ip2ipn($m[2]); if(1*$x2==0) idie("Error ip2");
	if($x1==$x2) $x2=0;
    }
    $ara['IPN']=$x1;
    $ara['IPN2']=$x2;

#    [4] => BRO: Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0
    $n='BRO'; $x=$R[4]; if(!preg_match("/^".$n."\:\s+(.+)$/s",$x,$m)) idie("Error ".$n."::"."<p>".nl2br(h(print_r($R,1)))); $x=$m[1]; $ara[$n]=$x;

#    [5] => REF: http://lleo.me/dnevnik/2011/02/07.html
    $n='REF'; $x=$R[5]; if(!preg_match("/^".$n."\:\s*(.*)$/s",$x,$m)) idie("Error ".$n."::"."<p>".nl2br(h(print_r($R,1)))); $x=$m[1]; $ara[$n]=$x;

#    [6] => unic: 3595699 acn: 0 acc: 
    $n='unic'; $x=$R[6]; if(!preg_match("/^unic\:\s+(\d+)\s+acn\:\s+(\d+)/s",$x,$m)) idie("Error ".$n."::"."<p>".nl2br(h(print_r($R,1))));
    $x=1*$m[1]; if(!$x && $m[1]!='0') idie("Error unic: ".h($R[6])); $ara['unic']=$x;
    $x=1*$m[2]; if(!$x && $m[2]!='0') idie("Error acn: ".h($R[6])); $ara['acn']=$x;

// num: int(10) unsigned NOT NULL


	    $o.=" * "; //<p><pre>".nl2br(h(print_r($ara,1)))."</pre>";
		$skip++;
		usleep(1000);
	}

/*

Array
(
    [0] => 2013-08-28 15:01:19 
    [1] => info: IP: 194.226.116.0/22 RSNET-2  Сеть органов госвласти
    [2] => link: http://lleo.me/dnevnik/2011/02/07.html
    [3] => IP: 194.226.116.51 194.226.116.51
    [4] => BRO: Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0
    [5] => REF: http://lleo.me/dnevnik/2011/02/09.html
    [6] => unic: 3595699 acn: 0 acc: 
)

Array
(
    [0] => 2013-08-28 15:01:19 
    [1] => info: IP: 194.226.116.0/22 RSNET-2  Сеть органов госвласти
    [2] => link: http://lleo.me/dnevnik/2011/02/09.html
    [3] => IP: 194.226.116.51 194.226.116.51
    [4] => BRO: Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0
    [5] => REF: http://lleo.me/dnevnik/2011/02/07.html
    [6] => unic: 3595699 acn: 0 acc: 
)

 id: int(10) unsigned NOT NULL auto_increment
Time: int(11) unsigned NOT NULL default '0'
IPN: int(10) unsigned NOT NULL
IPN2: int(10) unsigned NOT NULL
BRO: varchar(1024) NOT NULL
unic: int(10) unsigned NOT NULL
link: varchar(2048) NOT NULL
REF: varchar(2048) NOT NULL
acn: int(10) unsigned NOT NULL
num: int(10) unsigned NOT NULL
info: varchar(256) NOT NULL

2013-08-26 11:56:00 
info: IP: 194.226.116.0/22 RSNET-2  Сеть органов госвласти
link: http://lleo.me/dnevnik/2012/04/13_ps.html
IP: 194.226.116.51 194.226.116.51
BRO: Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0
REF: http://lleo.me/dnevnik/2012/04/15_1.html
unic: 3595699 acn: 0 acc: 

2013-08-26 11:56:05 
info: IP: 194.226.116.0/22 RSNET-2  Сеть органов госвласти
link: http://lleo.me/dnevnik/2012/04/15_1.html
IP: 194.226.116.51 194.226.116.51
BRO: Mozilla/5.0 (Windows NT 5.1; rv:23.0) Gecko/20100101 Firefox/23.0
REF: http://lleo.me/dnevnik/2012/04/13_ps.html
unic: 3595699 acn: 0 acc: 

*/

	$o.=" ".$skip."(".sizeof($RKM).") all=$allwork   ";
	if($skip<$allwork) return $skip;
	$delknopka=1;
	return 0;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
function installmod_allwork() {

    $RKM=fileget($GLOBALS['filehost']."hidden/log/rkm1.log"); $RKM=explode("\n\n",$RKM);

    return sizeof($RKM);
    }

?>