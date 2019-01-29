<?php
/*
 * AntiBot - класс для создания специальной антиботовой картинки
 * изобржение с которой необходимо указать юзеру, для того, что бы
 * доказать, что он реальный юзер, а не злобный бот.
 * 
 * Фунцией GetPic создаётся сама картинка, получается её SID -
 * хэш, по которому можно потом проверить правильность
 * её расшифровки пользователем.
 *
 * Функция CheckCode($code) получает результат умозключений юзера
 * и проверяет - если такая картинка до этого создавалась
 * и её содержимое соответвует южидниям юзера, то ок -
 * возврщается TRUE и aqk картинки удаляется.
 * Инче возвертается FALSE.
 *
 * Написан класс Ненаглядовым Евгением aka Соziдатель
 * 26-го октября 2004 года для проекта FlirtCenter.com
 *
 * и чуток подправлен LLeo
 * ПАТАМУШТА Я БЛЯТЬ С ДЕТСТВА ТЕРПЕТЬ НЕ МОГУ ЭТО ЙОБАНОЕ ОБЪЕКТНОЕ ПРОГРАММИРОВАНИЕ
 * И ЭТИ СРАННЫЕ ОБЪЕКТНЫЕ КЛАССЫ, БЕЗ НИХ МИР ЧИЩЕ!
*/

function antibot_make($antibot_C=0) { global $antibot_pic, $antibot_H, $antibot_W, $antibot_add2hash, $antibot_file, $antibot_hash;
	if(!$antibot_C) $antibot_C=$GLOBALS['antibot_C'];

	// если нет GD - хуй вам, а не капчу
	if(!function_exists('imagecreatefromjpeg')) return '';

	$bgs=glob($antibot_pic."bg-*"); $um=imagecreatefromjpeg($bgs[rand(0,count($bgs)-1)]); // Выбор случайной подложки
	$iml=$antibot_C*(imagesx($um)/5); $im=imagecreate($iml,imagesy($um));
	for($x=0;$x<$iml;$x+=imagesx($um)) imagecopy($im,$um,$x,0,0,0,imagesx($um),imagesy($um));

	$h = round((imagesy($im)-$antibot_H)/2); // Средняя высота
	$w = round(imagesx($im)/$antibot_C); // Средняя ширина

	// Придумать строку символов и разместить их на лужайке
	$path=$antibot_pic."sum_"; $lpath=strlen($path); 
// $files=glob($path."*.png");
$files=glob($path."*.gif");
$n=count($files)-1; // узнать, какие символы есть
	$imS=array(); $sums=''; for($i=0; $i<$antibot_C; $i++) {
		$f=$files[rand(0,$n)]; // выбираем случайный символьный файл
		$l=substr($f,$lpath,1); // выясняем, что это за символ
		

		if(!isset($imS[$l])) 
// $imS[$l]=imagecreatefrompng($f); // если не было - подгружаем его картинку
$imS[$l]=imagecreatefromgif($f); // если не было - подгружаем его картинку
		if($imS[$l]===false) idie("Error antibot imagecreatefrompng(`".$f."`)".(!is_file($f)?' file not found!':' unknown error'));
		imagecopymerge($im,$imS[$l],($w*$i)+rand(0,$w-$antibot_W),rand(2,$h*2-2),0,0,18,20,40); // Расстановка по лужайке
		$sums.=$l; // добавить символ в строку
	}

// dier($imM);

// Заштрихуёвываем полосочкой - правая диагональ
//	$color = ImageColorAllocate($im, 200, 200, 200);
//	for($i=0; $i<=round(ImageSX($im)/7); $i++) { $x = $i*7; ImageLine($im, $x, 0, $x-ImageSY($im), ImageSY($im), $color); }

//	// Заштрихуёвываем полосочкой - горизонталь
	$imT=imagecreate(1,1); imagefill($imT,1,1,imagecolorallocate($imT,0,0,0));
	// штриховка по горизонтали и вертикали
	$t=rand(4,15); for($i=round(imagesy($im)/$t);$i>=0;$i--) imagecopymerge($im,$imT,0,$i*$t,0,0,imagesx($im),1,20);
	$t=rand(4,15); for($i=round(imagesx($im)/$t);$i>=0;$i--) imagecopymerge($im,$imT,$i*$t,0,0,0,1,imagesy($im),20);

	$antibot_hash = md5($sums.$antibot_add2hash);

	$nam=$antibot_file.$antibot_hash.".jpg";
	if(!imagejpeg($im,$nam)) { // сохраняем картинку
		testdir($GLOBALS['antibot_file']); if(!imagejpeg($im,$nam)) // сохраняем картинку снова
		idie("Ошибка! Не могу сохранить картинку в директорию \"".$antibot_file."\", проверьте, создана ли она, и установлены ли права записи?");
	} filechmod($nam);

	$GLOBALS['antibot_imW'] = imagesx($im);
	$GLOBALS['antibot_imH'] = imagesy($im);
	imagedestroy($im);
	return $antibot_hash;
}

/* провести проверку - совпадает ли код с хэшем и есть ли такая картинка */
function antibot_check($code, $hash) {
	// если нет GD - хуй вам, а не капчу
	if(!function_exists('imagecreatefromjpeg')) return true;

	$code = preg_replace("/[^0-9a-z]/si","",$code); // убрать посторонние символы, кроме цифр (и букв)
	$hash2=md5($code.$GLOBALS['antibot_add2hash']);
	$f = $GLOBALS['antibot_file'].$hash2.".jpg";
	if($hash==$hash2 and is_file($f)) { unlink($f); return true; }
	if(is_file($f)) unlink($f); return false;
}

/* получить красиво оформленый HTML тэг только что созданой картинки. <img src="URL" width=WIDTH height=HEIGHT border=0> */
function antibot_img() {
return "<img src='".$GLOBALS['antibot_www'].$GLOBALS['antibot_hash'].".jpg' width=".$GLOBALS['antibot_imW']." height=".$GLOBALS['antibot_imH']." alt='captcha' border=0>";
}

/* удалить старые картинки, которые были созданы более часа назад. */
function antibot_del() { $old = time()-$GLOBALS['antibot_deltime']; $deleted = 0;
	$p=glob($GLOBALS['antibot_file']."*.jpg"); if($p===false or !sizeof($p)) return "антиботовых картинок нет";
	foreach($p as $f) if(filemtime($f)<$old) { unlink($f); $deleted++; }
	return "Антиботовые картинки, удалено: ".$deleted;
}

?>