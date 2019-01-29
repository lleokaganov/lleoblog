<?php

// http://x.lleo.me/blog/ajax/autoriz.php?x=1&upx=0-be18f0ae6a4eebfe074f875d04d944cbe27d0623&uname=%3Cinput+tiptitle%3D%27Login%27+value%3D%27anonymous%27+type%3D%27button%27+onclick%3D%22majax%28%27login.php%27%2C%7Baction%3A%27do_login%27%7D%29%22%3E

if(!empty($_GET['x'])) { $autorizatio=1;
		include "../config.php";
		include $include_sys."_autorize.php";

	$MYHOST=substr($httpsite,7); $x_domain=$xdomain.'.'.$MYHOST; // вычислили x.lleo.me
	if($_SERVER["HTTP_HOST"]!=$x_domain) die("domain: ".$_SERVER["HTTP_HOST"]); // запретили загрузку с иных доменов

	// $x_domain="path=/xserver";

	if(!empty($_GET['upx'])) { // если upx задан
		$upx=$_GET['upx']; if($upx!='logout'&&!upx_check($upx)) die('err');
		if(!isset($_GET['uname'])) retdat(h($upx)); // только установить его и всё
		retdat(h($upx),u2unic($upx),$_GET['uname']);
	}

	$upx=$_COOKIE['upx']; // взять секретную куку

	if(upx_check($upx) && ($unc=u2unic($upx))!=0 ) { // если прошла проверку
		$IS=getis($unc); // выбрать данные по unc
		retdat('',$unc,(!empty($IS['imgicourl'])?$IS['imgicourl']:'#'.$unc));
	}
	$unc=upx_new_unic(); // иначе завести новый unic
	$upx=upx_set($unc);
	retdat($upx,$unc,"#".$unc);
}

function retdat($upx='',$unc='',$uname=''){
 die("<html><body>
<script type='text/javascript' language='JavaScript' src='".$GLOBALS['www_js']."transportm.js'></script>
<script>
var IMBLOAD_MYID='autoriz';
var o='CLOSE|id=xdomain|time=".time()."';
".($unc===''?'':"o=\"setunc|ux=".uxset($unc)."|uname=".hl($uname)."\"+'|'+o;")."
".($upx===''?'':"set_upx=function(n,v){ var N=new Date(); N.setTime(N.getTime()+(v==''?-1:3153600000000)); document.cookie=n+'='+encodeURIComponent(v)+';expires='+N.toGMTString()+';'; }; set_upx('upx','".$upx."');")."

document.write('<font color=green>'+o+'</font><br>');
sendm(o);
</script>xdomain
<hr><pre>
upx=`$upx`
unc=`$unc`
ux=`".($unc===''?'':uxset($unc))."`
uname=`$uname`
</pre>

</body></html>");
}

include "../config.php"; include $include_sys."_autorize.php";
$a=RE('a');

//--------------- fkeys ----------------
if($a=='fkey') { $k=RE('fkey'); if($unic && $k) {
    msq_add(issor('db_fkey','fkey'),arae(array('fkey'=>$k,'unic'=>$unic,'bro'=>$BRO)));
    $msqe='';
} otprav(''); }
//--------------- fkeys ----------------


// нарушена авторизация ux=0
if($a==0) { // a=ux, он 0
	if(!$admin) otprav(''); // пока

	if(!$unic) otprav("salert('unic=0',15000);");

	$ux=uxset($unic);
	otprav("
		c_save('".$ux_name."','".$ux."0');
		salert('new ux=`".$ux."`',15000);
	");
}

// $o.=strtoupper(base_convert(rand(0,100000).rand(0,100000),10,36));



// uxcheck($ux)
// uxset($unc)
// upx_check($upx)
// upx_set($unc)

// function hl($s) {        return $s; }


function upx_getis($unc) {
        if(($is=ms("SELECT * FROM ".$GLOBALS['db_unic']." WHERE `id`='$unc'","_1",0))!==false) {
                $is=get_ISi($is);
                // if($is['admin']=='podzamok' || $is['admin']=='admin') $is['imgicourl']=zamok('podzamok').$is['imgicourl'];
        }
        return $is;
}

function upx_new_unic() { global $db_unic,$IPN,$msqe;
	$unc=0;	if(msq_add($db_unic,array('ipn'=>$IPN,'time_reg'=>time()))===false){trevoga("DB2 ADD FALSE!!!!"); return 0;}
	$unc=msq_id(); if(!$unc){trevoga("msq_insert_id2():".$unc);die('unc=0 '.$msqe);}
	return $unc;
}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>