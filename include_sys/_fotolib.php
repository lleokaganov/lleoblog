<?php // Работа с фото

//==================================================================================================
function imagecreatetruecolor_addalpha($X,$Y,$itype) {
	$img2=imagecreatetruecolor($X,$Y); if($itype==1 or $itype==3) { // if PNG or GIF
	imagealphablending($img2,false); imagesavealpha($img2,true);
	imagefilledrectangle($img2,0,0,$X,$Y,imagecolorallocatealpha($img2,255,255,255,127));
	} return $img2;
}

$foto_rash=array(1=>'gif',2=>'jpg',3=>'png');

function openimg($from,$itype) {
	switch($itype) {
		case 2: return imagecreatefromjpeg($from);
		case 1: return imagecreatefromgif($from);
		case 3: return imagecreatefrompng($from);
		default: return false; // "Unknown image (#".$itype."): ".h($from);
	}
}

function closeimg($img2,$from,$itype,$q) {
	switch($itype){
		case 2: imagejpeg($img2,$from,$q); break;
		case 1: imagegif($img2,$from); break;
		case 3: imagepng($img2,$from,9); break;
	} imagedestroy($img2);
}

function pre100x100($from,$degree,$q=90,$X=100,$Y=100) {
	list($W,$H,$itype)=getimagesize($from); $img1=openimg($from,$itype);
	$img2=imagecreatetruecolor_addalpha($X,$Y,$itype);
	imagecopy($img2,$img1,0,0,($W-$X)/2,($H-$Y)/2,$X,$Y);
	closeimg($img2,$from,$itype,$q); imagedestroy($img1);
}

function rotatejpeg($from,$degree,$q=90) {
	list($W,$H,$itype)=getimagesize($from); $img1=openimg($from,$itype);
	$img2=rotateImg($img1,$itype,$degree); // $img2=imagerotate($img1,180,0);
	closeimg($img2,$from,$itype,$q); imagedestroy($img1);
}

function rotateImg($img,$itype,$degree) {
$w=imagesx($img); $h=imagesy($img);
switch($degree){
case 90: $new=imagecreatetruecolor_addalpha($h,$w,$itype); for($x=0;$x<$w;$x++) for($y=0;$y<$h;$y++) imagesetpixel($new,$h-1-$y,$x,imagecolorat($img,$x,$y)); break;
case 270: $new=imagecreatetruecolor_addalpha($h,$w,$itype); for($x=0;$x<$w;$x++) for($y=0;$y<$h;$y++) imagesetpixel($new,$y,$w-$x-1,imagecolorat($img,$x,$y)); break;
case 180: $new=imagecreatetruecolor_addalpha($w,$h,$itype); for($x=0;$x<$w;$x++) for($y=0;$y<$h;$y++) imagesetpixel($new,$w-$x-1,$h-$y-1,imagecolorat($img,$x,$y)); break;
case 0: return $img;
} return $new;
}

function obrajpeg($from,$to,$X=150,$q=80,$s='',$r=10) {
// set_time_limit(0);
	list($W,$H,$itype)=getimagesize($from);
// :getimagesizefromstring($from));
	$img1=openimg($from,$itype);
	if($img1===false) return false;
	$img2=obrajpeg_sam($img1,$X,$W,$H,$itype,$s,$r);
	closeimg($img2,$to,$itype,$q); imagedestroy($img1);
	return true;
}


function obrajpeg_sam($img1,$X,$W,$H,$itype,$s='',$r=10) {
	if($X<max($H,$W)) { $Y=floor($X*min($H,$W)/max($W,$H));
		if($H>$W) list($X,$Y)=array($Y,$X); // если ориентирована вертикально
			$img2=imagecreatetruecolor_addalpha($X,$Y,$itype);
			imagecopyresampled($img2,$img1,0,0,0,0,$X,$Y,$W,$H);
	} else { $X=$W; $Y=$H;
		if(isset($GLOBALS['foto_replace_resize'])){ // принудительно пережимать фотки?
			$img2=imagecreatetruecolor_addalpha($X,$Y,$itype);
			imagecopyresampled($img2,$img1,0,0,0,0,$X,$Y,$W,$H);
		} else $img2=$img1;
	}
	if($s!='') pic_podpis($img2,$X,$Y,$s,$r);
	return $img2;
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

?>