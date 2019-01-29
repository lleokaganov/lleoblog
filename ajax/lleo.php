<?php // разный мусор

include "../config.php"; include $include_sys."_autorize.php"; $a=RE('a'); ADH();

if($a=='jstest') { AD();
    ms("GETEL AS=`sdsd`");
    otprav("var e=1;var sede=5;var ccc=4;var p=8;");
}

if($a=='pinghome') { // AD();

$ctx=stream_context_create( array('http' => array('timeout' => 2) ) );
$res=@file_get_contents("http://home.lleo.me/dyndns/index.php?time=".time(),false,$stx);

if('Error 404 - 1'==$res) $s="<font color=green>home.lleo.me - работает</font>";
else $s="<font color=red>home.lleo.me не отвечает"
// .nl2br(h(substr($res,0,500)))
."</font>";

$s="<div style='margin-bottom:-16px;'><i><ul><li>$s</li></ul></i></div>";

otprav("zabil('home.lleo.me',\"".njsn($s)."\")");

// idie("home.lleo.me недоступен!<p>".nl2br(h(substr($res,0,1024))));
// else otprav("salert('<font color=green>home.lleo.me</font>',500)");
// idie($s);otprav("salert('ok',200);");
}













//========================================================================================================================
function parsehtm($q,$s) {
    $s=preg_quote($s,'/');
//    $s=preg_replace("\\\?\\\?\\\?([^\?]+)\\\?\\\?\\\?",'[^]',$s);
    $s=str_replace('\\<','<',$s); $s=str_replace('\\>','>',$s);
    $s=str_replace(array("\n","\r","\t"," "),'\\s*',$s);
    $s=preg_replace("/[\'\"]/s","[\\\"\\']*",$s);
    $s=str_replace('\*\*\*',"([^\\n<>]+)",$s);
//idie(h($s));
    if(!preg_match("/".$s."/si",$q,$m)) return false;
    return $m[1];
}
//========================================================================================================================
//========================================================================================================================

if($a=='aliexpress-price') { AD(); $i=RE('i'); $num=RE0('num');

$s=file_get_contents('http://www.aliexpress.com/snapshot/'.$i.'.html');
// fileput('test',$s);
// $s=file_get_contents('test');

$date=parsehtm($s,'<div class="switch-site-tip-text">This is a snapshot of the product taken when the order was placed at ***<h2>'); $date=date("Y-m-d",strtotime(trim($date)));
$date2=date("Y-m-d H:i:s");

$price=parsehtm($s,'<span class="currency">US $</span><span class="value">***</span>');
$new=trim(parsehtm($s,'<a href="***">View current product</a>'),"\n\r\t \"\'");
// <a href="http://www.aliexpress.com/item/1pcs-High-Quality-USB-2-0-Mic-Speaker-Audio-Headset-Microphone-3-5mm-Jack-Converter-Sound/784665622.html">View current product</a>
if($new!==false) { $s2=file_get_contents($new);
$price2=parsehtm($s2,'itemprop="price">***</span>');
if($price2!==false && $price!==false && $price2!=$price) $rr="<div style='color:red'>\$".h($price2)." <span class=br>".h($date2)."</span></div>";
// idie($s2);
} else $rr="<span class=br>".h($date2)."</span>";

// idie(nl2br(h($rr)));

$pcs=(preg_match("/(\d+)\s*pieces\s*\/\s*lot/si",$s,$c)?$c[1]:0);

if($rr!='' || ($date!==false && $price!=$false)) {
$s=ms("SELECT `Body` FROM `dnevnik_zapisi` WHERE `num`='".$num."'",'_l',0);
if(!preg_match("/\n"
."\s*([^\|\n]+)\s*\|" //0 m1
."\s*([^\|\n]+)\s*\|" //1 m2
."\s*".$i."\s*\|" //2 m3
."\s*([^\|\n]+)\s*\|" //3 m4
."\s*([^\n]+)\s*\n" //4 m5
."/s",$s,$m)) idie('err');


$m[3]=str_replace('.jpg_50x50.jpg','.jpg_200x200.jpg',$m[3]);

if(!$pcs) $pcs=(preg_match("/(\d+)\s*pcs/si",$m[4],$c)?$c[1]:0);

$z=preg_replace("/<\/a>.+$/s",'',$m[4])."</a><div style='font-size:20pt'>\$".h($price)
.($pcs?" (за ".$pcs." шт)":'')." <span class=br>".h($date)."</span>".$rr."</div>";

//idie($z);

$s=str_replace($m[0],"\n\n".trim($m[1])." | ".trim($m[2])." | ".$i." | ".trim($m[3])." | ".$z."\n\n",$s);

msq_update('dnevnik_zapisi',arae(array('Body'=>$s)),"WHERE `num`='".$num."'");

// dier($m[0]);

if($msqe!='') idie($msqe);

// idie(h($m[4])."<hr>".h($z));
// Drop Shipping 10pcs 125Khz RFID Proximity ID Token Tag Key Keyfobs Chain 2363$2.74(за 10 шт)2014-05-28</a><div style='font-size:20pt'>$2.74<br>(за 10 шт)<p><div class=br>1970-01-01</div></div><div style='color:red'>$2.74 <span class=br>(2014-08-27)</span></div>

// idie(vzyal('info_".h($i)."'));
// 'bodyz',vzyal('bodyz').replace(/".h(preg_quote($m[4],'/'))."/g,\"".$z."\"));

otprav("
zabil('info_".h($i)."',\"<a href='http://www.aliexpress.com/snapshot/".h($i).".html'>".$z."</a>\");
salert('ok',200);
");
} else idie('false!');

}




//======= llog.php ========================
if($a=='llog') {
/*
$file=rpath($filehost."log/ajax/".RE('f').".txt"); testdir(dirname($file)); $o=RE('o');
$i=fopen($file,"a+"); fputs($i,"

------ ".date("Y-m-d h:i:s")." -------
".$o); fclose($i); chmod($file,0666);
*/
otprav("salert('save',100)");
}
//======= /llog.php ========================



//======= lleo_camera.php ========================
if($a=='lleo_camera') {
    $f=glob("/var/www/home/camera/*.swf");
    $s='';
    $last=0; $lastf=''; foreach($f as $l) {
	$t=filemtime($l);
	// $s.="$l:$t\n";
	if((time()-$t)>600) { unlink($l); } //$s.="delete file: $l\n";
	if($t>$last) { $last=$t; $lastf=$l; }
    }

if($lastf!='') {
	$l="http://lleo.me/home/camera/".basename($lastf);
	$x=320; $y=240;
	$s="<object classid='clsid:D27CDB6E-AE6D-11cf-96B8-444553540000' wmode='transparent' height='$y' width='$x'>"
."<param name='wmode' value='transparent'><param name='movie' value='$l'>"
."<embed type='application/x-shockwave-flash' src='$l' wmode='transparent' height='$y' width='$x'>"
."</object>";

$s="zabil('myswfplay',\"".$s."\"); setTimeout(\"majax('http://lleo.me/ajax/lleo_camera.php',{})\",10000);";
}

$_RESULT["modo"]=$s;
$_RESULT["status"]=true;
exit;
}
//======= /lleo_camera.php ========================


//======= lleo.php ========================
if($a=='lleo') {
function llog2($s) { global $aharu,$IP,$BRO,$MYPAGE;
        if(!$aharu) return;
        logi('autorizaAJAX.txt',"\n".h($s." ".$IP." | ".$BRO." | ".$MYPAGE));
}
llog2("Ajax: ".RE("s")." ");
otprav("");
}
//======= /lleo.php ========================

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>