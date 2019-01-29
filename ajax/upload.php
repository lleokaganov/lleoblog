<?php // Работа с фотоальбомом

include "../config.php";
include $include_sys."_autorize.php";

idie('upload.php - cho za??');

// file_put_contents('_upload.txt',print_r($_FILES,1).print_r($_POST,1).print_r($_SERVER,1));
// phpinfo();

$simple_auth_passfrase="jeLezny FelIKS? razMAZNya!!!";
if($_POST['password']!=md5($simple_auth_passfrase.$_POST['login'])) { sleep(5); idie("Password error!"); }

//echo "<pre>";
// $file_to_upload = array('file'=>"@C:\\Apache\\htdocs\\file.txt");
//$admin=$_POST['login'];

$fileset=$foto_file_small."_fotoset.dat";
$fotoset=get_fotoset();

//dier($fotoset);

$foto_small=trim($fotoset['dir'],'/').'/'; $foto_file_small=$filehost.$foto_small; $foto_www_small=$wwwhost.$foto_small;
        if(!is_dir($foto_file_small)) { mkdir($foto_file_small); chmod($foto_file_small,0777); }
$foto_preview=trim($fotoset['dir'],'/').'/pre/'; $foto_file_preview=$filehost.$foto_preview; $foto_www_preview=$wwwhost.$foto_preview;
        if(!is_dir($foto_file_preview)) { mkdir($foto_file_preview); chmod($foto_file_preview,0777); }

$s='';

if(count($_FILES)>0) foreach($_FILES as $FILE) if(is_uploaded_file($FILE["tmp_name"])) { $fname=h($FILE["name"]);

        if(!preg_match("/\.jpe*g$/si",$fname)) idie("Это разве фотка?");
        if(preg_match("/^\./si",$fname)) idie("Имя с точки?");
        if(strstr($fname,'..')) idie("Ошибка. Хакерствуем, бля?");

        //--- фотоальбом Nokia ---
        if(preg_match("/^(\d\d)(\d\d)(\d{4})(\d+)\.jpg/si",$fname,$m) && $m[3]."/".$m[2]==$fotoset['dir']) {
                $fname=$m[1]."-".$m[4].".jpg";
        }
        //--- фотоальбом Nokia ---

        if(is_file($foto_file_small.$fname)) { $s.="<br>present: '".$foto_www_small.$fname."'";
	} else {
                obrajpeg($FILE["tmp_name"],$foto_file_small.$fname,$fotoset['X'],$fotoset['Q'],$fotoset['logo']);
                obrajpeg($foto_file_small.$fname,$foto_file_preview.$fname,$fotoset['x'],$fotoset['q']);
                $s.="<br>loaded: '".$foto_www_small.$fname."'";
	}
	
	if($s=='') idie("Error 2! ".nl2br(h(print_r($_FILES,1))));
	print $s;
}

//idie($s."###");

//==================================================================================================

function obrajpeg($from,$to,$X=150,$q=80,$s,$r=10) { // set_time_limit(0);
        $img1=ImageCreateFromJpeg($from); $W=ImagesX($img1); $H=ImagesY($img1);
        if($X<$H) { $Y=$X*$H/$W;
                $img2=ImageCreateTrueColor($X,$Y);
                ImageCopyResampled($img2,$img1,0,0,0,0,$X,$Y,$W,$H);
        } else { $X=$W; $Y=$H; $img2=$img1; }

	if($s!='')  pic_podpis($img2,$X,$Y,$s,$r); 

        ImageJpeg($img2, $to, $q);
        ImageDestroy($img2);
        ImageDestroy($img1);
}


function pic_podpis($img,$w,$h,$s,$fs=20,$font) { 
	if($font=='') $font=$GLOBALS['foto_ttf'];
//	die("<p>font: ".$font." text:".$s);
	$s=wu($s);
	$rez=imagettfbbox($fs,0,$font,$s); $x=$w-$rez[4]-$fs/4; $y=$h-$rez[3]-$fs/4; // координаты текста
// каким цветом $black/$white ?
$c=(imagecolorat($img,$x,$y)>imagecolorallocate($img,127,127,127)?imagecolorallocate($img,0,0,0):imagecolorallocate($img,255,255,255));
	imagettftext($img,$fs,0,$x,$y,$c,$font,$s);
}

function get_fotoset() { global $fileset;
	$fotoset=unserialize(file_get_contents($fileset)); if($fotoset===false) $fotoset=array();
	if(!intval($fotoset['X'])) $fotoset['X']=$foto_res_small;
	if(!intval($fotoset['Q'])) $fotoset['Q']=$foto_qality_small;
	if(!intval($fotoset['x'])) $fotoset['x']=$foto_res_preview;
	if(!intval($fotoset['q'])) $fotoset['q']=$foto_qality_preview;
	if(!isset($fotoset['dir'])||$fotoset['dir']=='') $fotoset['dir']='photo';
	if(!isset($fotoset['logo'])) $fotoset['logo']=$foto_logo;
	return $fotoset;
}

?>