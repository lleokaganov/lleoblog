<?php

function getdr($dir) { $dr=rpath($GLOBALS['filehost'].accd()."tmp/upload-doc/".basename($dir)); testdir($dr); return $dr; }

function do_stat($file) { allowfile($file);

    set_time_limit(200);
    ini_set('max_execution_time',200);

    exec("catdoc -s cp1251 -d cp1251 -w \"".escapeshellcmd($file)."\"",$o); $o=implode("\n",$o); $do=floor(strlen($o));
    $fcon=dirname($file).'/config.txt'; if(!is_file($fcon)) {
	$default="max=800000" //.$c['max']
	."\nwidth=150" // .$c['width']
	."\nstart=".date("Y-m-d")
	."\nfinish=".date("Y-m-d",time()+60*60*24*1) // 1 месяц по умолчанию
	."\ntemplate=сделано {do} из {max} {percent}%<br><div><div style='display:block;width:{width}px;border:1px solid #ccc;'><div style='display:block;width:{x}px;height:5px;background-color:red;border:1px solid #ccc;'></div></div></div>";  //.$c['template']);
	fileput($fcon,$default); if(!is_file($fcon)) idie("Create error: `".h($fcon)."`");
    }
    include_once $GLOBALS['include_sys']."_modules.php";
    $c=parse_e_conf(fileget($fcon));

    $stat=mpers($c['template'],array(
	'time'=>time(), // для кэша картинок
	'width'=>$c['width'],
	'max'=>$c['max'],
	'do'=>$do,
	'x'=>min($c['width'],floor(($c['width']/$c['max'])*$do)),
	'percent'=>floor((100/$c['max'])*$do) // min(100,floor((100/$c['max'])*$do))
    ));

    $dir=dirname($file);

    // сделать статистику
    fileput(getdr($dir).'/stat.htm',$stat);

    // сделать картинки
    unlink(getdr($dir).'/stat.gif');
    unlink(getdr($dir).'/stat_small.gif');
    make_statpic($dir,$c['max'],isset($c['start'])?$c['start']:0,$c['finish'],isset($c['days'])?$c['days']:0,$do);

}

function find_dolast($dir) {
    if(is_file($dir)) return $dir;
    $t=$x=false; foreach(glob($dir.'/*.doc') as $l) if(filemtime($l)>$t) { $t=filemtime($l); $x=$l; } if($x!=false) return $x;
    idie('Files not found in `'.h($dir).'`');
}


function BACKUP_ajax(){ $a=RE('a'); ADMA();

if($a=='pic') { $f=RE('file'); $file=rpath($GLOBALS['filehost'].'/hidden/'.accd().'upload-doc/'.$f);
    $dir=dirname($file);
    include_once $GLOBALS['include_sys']."_modules.php";
    $c=parse_e_conf(fileget($dir."/config.txt"));
//    dier($c);
    make_statpic($dir,$c['max'],isset($c['start'])?$c['start']:0,$c['finish'],isset($c['days'])?$c['days']:0);

    $web=rpath($GLOBALS['wwwhost'].accd()."tmp/upload-doc/".basename($dir));

    idie("<img src='".$web."/stat.gif?".time()."'>
<p>`$web`
<p><img src='".$web."/stat_small.gif?".time()."'>");
}


if($a=='view') { $f=RE('file'); $dir=rpath($GLOBALS['filehost'].'/hidden/'.accd().'upload-doc/'.$f);
    $x=find_dolast($dir); allowfile($x); exec("catdoc -s cp1251 -d cp1251 -w \"".escapeshellcmd($x)."\"",$o); $o=implode("\n",$o);
    idie(str_replace("\n","<p class=d>",ispravkawa(h($o))),"View last file `".h($f).".doc`");
}


if($a=='calculate') { $f=RE('file'); $dir=rpath($GLOBALS['filehost'].'/hidden/'.accd().'upload-doc/'.$f);
    $f=find_dolast($dir);
do_stat($f);
// ."<div id='set_".h(basename($dir))."'>".fileget($f)."</div>";
// idie('set_'.h(basename($dir)));
// fileget(
//    $dr=rpath($GLOBALS['filehost'].accd()."tmp/upload-doc/".basename($dir)); testdir($dr);

    otprav("zabil('set_".h(basename($dir))."',\"".njsn(fileget(rpath(getdr($dir)."/stat.htm")))."\");");
    idie('OK');
}

idie('error unknown');

}






function BACKUP($e) { global $admin,$acc,$acn,$ADM,$IS,$httphost;

if(count($_FILES)>0) { // пришли файлы
    if(RE('password')!=$GLOBALS['uploadpassword']) ADMA();

    $fname=''; foreach($_FILES as $f) {
	    if(!is_uploaded_file($f["tmp_name"])) die('File not uploaded!');
	    $fname=h($f["name"]); break;
    } if($fname=='') die('File not loaded!');
    $date=date("Y-m-d_H-i-s");
    $md5=md5_file($f["tmp_name"]);
    $fn=preg_replace("/^(.+)\.(doc|docx|txt|text)$/si","$1/$1-".$date.".$2",$fname);
    if($fn==$fname) die("Wrong file (only DOC or TEXT format)");
    $file=rpath($GLOBALS['filehost'].'/hidden/'.accd().'upload-doc/'.$fn);

    $journal=dirname($file)."/journal.txt";

    $ras=getras($file);
    if($ras=='doc') { exec("catdoc -s cp1251 -d cp1251 -w \"".escapeshellcmd($f["tmp_name"])."\"",$o); $o=implode("\n",$o); $size=strlen($o); unset($o); }
    else die("unknown format: ".h($ras)."\n");

    foreach(explode("\n",fileget($journal)) as $x) { if(!strstr($x,'|')) continue;
	list($date1,$size1,$md51)=explode('|',$x);
	if($md5==$md51 && $size1==$size) die("File already exist `".h($x)."`\n");
    }

    $dir=dirname($file); testdir($dir); if(!is_dir($dir)) die("Can't create dir: `".h($dir)."`");
    $htaccess=$dir."/.htaccess"; if(!is_file($htaccess)) fileput($htaccess,'not allowed for Apache too');

    $fj=fopen($journal,"a+"); fputs($fj,"\n".$date."|".$size."|".$md5); fclose($fj);
    rename($f["tmp_name"],$file); if(!is_file($file)) die("Can't save file `".h($file)."`\n");

    do_stat($file);
    die("OK\nSaved ".$size." byte: ".basename($file)."\n");
}

ADMA();

/////////// просто запрос
$dir=rpath($GLOBALS['filehost'].'/hidden/'.accd().'upload-doc');
$o='';
foreach(glob($dir."/*") as $l) { if(!is_dir($l)) continue;
    $f=getdr($l).'/stat.htm';
    if(!is_file($f)) continue;
    $dir=h(basename($l));
    $filenames=array(); foreach(glob($l."/*.doc") as $e) {
	$e=substr($e,strlen($GLOBALS['filehost']."hidden/upload-doc/"));
	list($dt,)=explode('.',basename($e),2); $dt=preg_replace("/^(\d\d\d\d\-\d\d\-\d\d)_(\d\d)\-(\d\d)\-(\d\d)$/si","$1 $2:$3:$4",$dt);
	$fff=dirname($l).'/'.$e;
	$sz=floor(filesize($fff)/1024)."&nbsp;Kb";
	$filenames[]=
        "<i class='knop e_kontact_journal' onclick=\"majax('module.php',{mod:'BACKUP',a:'view',file:'".h($e)."'})\"></i>"
        ." &nbsp; <i class='knop e_redo-ltr' onclick=\"majax('module.php',{mod:'BACKUP',a:'pic',file:'".h($e)."'})\"></i>" //http://lleo.me/dnevnik/design/e3/redo-ltr.png
        ." &nbsp; <a href='{_SECRET_FILE:".$e."_}'>".basename($dir).".doc</a> ".$dt." &nbsp;($sz)";
    }

    $o.="<p><b>".explode_last('/',$l).".doc</b> "
.(is_file(getdr($l).'/stat.gif')?"<img align=right src='".$GLOBALS['wwwhost'].(substr(getdr($l).'/stat.gif',strlen($GLOBALS['filehost'])))."?".time()."'>":'0') // .getdr($l).'/stat.gif'
.(is_file(getdr($l).'/stat_small.gif')?"<img hspace=10 align=right src='".$GLOBALS['wwwhost'].(substr(getdr($l).'/stat_small.gif',strlen($GLOBALS['filehost'])))."?".time()."'>":'0') // .getdr($l).'/stat.gif'
."&nbsp; <i class='knop e_kr_invert' onclick=\"majax('module.php',{mod:'BACKUP',a:'calculate',file:'".$dir."'})\"></i>"
."&nbsp; <i class='knop e_kontact_journal' onclick=\"majax('module.php',{mod:'BACKUP',a:'view',file:'".$dir."'})\"></i>"
."&nbsp; <i class='knop e_system' onclick=\"majax('foto.php',{a:'edit_text',file:'hidden/".accd()."upload-doc/".$dir."/config.txt'})\"></i>"
."<div style='padding-left:50px' class=r>{_cut:[файлов: ".sizeof($filenames)."]".implode("<br>",$filenames)."<br>&nbsp;_}</div>"
."<div alt='".$f."' id='set_".$dir."'>".fileget($f)."</div>"
."<br class=q />"
;
}
return $o;

}



























//===============================================================================================

function make_statpic($dir,$obem1,$start,$finish,$srok1,$do) {
    if(empty($finish)||empty($obem1)) return;

    global $img,$Height,$Width,$colors,$Xstart,$Ystart,$Xmno,$Ymno,$FONT,$srok,$obem,$kalendar;
    $dr=getdr($dir); // rpath($GLOBALS['filehost'].accd()."tmp/upload-doc/".basename($dir)); testdir($dr);
    $gif_big=$dr."/stat.gif";
    $gif_small=$dr."/stat_small.gif";
    $journal=$dir."/journal.txt";
    $t=time();
    $days=60*60*24;
    if(!$srok1) $srok1=floor((strtotime($finish)-strtotime($start))/$days);
    if(!$start) $start=date("Y-m-d",strtotime($finish)-$days*$srok1); //." 01:00:00";
    $obem=floor($obem1/1024);
    $Xmno=3; // множитель значений по X
    $Ymno=1; // множитель значений по Y

    // если проебаны сроки
    if(strtotime($finish)<$t) { $srok=ceil( ($t-strtotime($start))/$days )-1; } else { $srok=$srok1; }

    $kalendar=$nado=$done=$dones=array();

    foreach(file($journal) as $l) { if(!strstr($l,'|')) continue;
	list($d,$n,$md5)=explode('|',$l); list($d,)=explode('_',$d,2); $n=intval(floor($n/1024));
	if($d!=''&&$n!='') $dones[$d]=max($dones[$d],$n);
    }

    // ОПИСЫВАЕМ ЦВЕТА
    $f=255; $a=127; $b=63; $colors=array(
	array($f,0,0),array(0,0,$f),array(0,$f,0),array($f,$f,0),array($f,0,$f),array(0,$f,$f),array($a,0,$a),array(0,$a,$a),array($f,$a,0),
	array($b,$a,$f),array($f,$a,$a),array($a,0 ,$f),array(0 ,$b,0),array($a,$a,0),array($a,$b,0),array($a,$a,$a),array(0,0,0)
    );

//    $FONT=$GLOBALS['filehost']."design/ttf/MTCORSVA.TTF"; // подгрузить русский шрифт TTF для печати текста в картинке (должен быть!!!)
    $FONT=$GLOBALS['filehost']."design/ttf/ARIAL.TTF"; // подгрузить русский шрифт TTF для печати текста в картинке (должен быть!!!)


    // СМОДЕЛИРОВАТЬ ПРОЦЕССЫ ===========================
    $nowdat=date("Y-m-d");
    $lastn=0; $lasti=0; for($i=0;$i<=$srok1;$i++) {
	$dat=date("Y-m-d",strtotime($start)+$i*$days);
	if($dat==$nowdat) $nowi=$i;

	$kalendar[$i]=$dat;
	$nado[$i]=$i*($obem/$srok1);

	if(isset($dones[$dat])||$dat==$nowdat) {
	    for($j=$lasti;$j<$i;$j++) $done[$j]=$lastn;
	    $lastn=(isset($dones[$dat])?$dones[$dat]:$lastx);
	    $lasti=$i+1;
	    $lastx=$dones[$dat];
	    $done[$i]=$lastn;
	}
    }

    // дополнить просроченный срок
    if($srok1<$srok) for($i=$srok1+1;$i<=$srok;$i++) {
	$dat=date("Y-m-d",strtotime($start)+$i*$days);
	if($dat==$nowdat) $nowi=$i;

	$kalendar[$i]=$dat;
	$nado[$i]=$obem;

	if(isset($dones[$dat])||$dat==$nowdat) {
	    for($j=$lasti;$j<$i;$j++) $done[$j]=$lastn;
	    $lastn=(isset($dones[$dat])?$dones[$dat]:$lastx);
	    $lasti=$i+1;
	    $lastx=$dones[$dat];
	    $done[$i]=$lastn;
	}
    }

    unset($dones);
    $rez=array(); for($i=0;$i<=$srok;$i++) $rez[$i]=array(floor($done[$i]),floor($nado[$i]));
    unset($nado); unset($done);


//===================================================
    openpic($do); // инициализировать картинку
    $las=statistic_type1($rez); // иначе - то вывести график формата 2 (заливка)
    pr(basename($dir).".doc",30,24,10,16);
    pr(date("Y-m-d H:i:s"),0,0,0,6);
    pr('',0,0,0,10);
    prc(8); pr("    готово");
    prc(15); pr("    надо");

    // обозначить финишную точку
    $color=getcolor(16,$img);

    $X=$Width-$Xstart;
    $x=$Width-$Xstart*2;

    $Y=$Height-$Ystart;
    $y=max($rez[$las[2]][1],$rez[$las[2]][0])*$Ymno;


// dier($rez);
    imagesetthickness($img,1); // установить толщину линий 3
    imageellipse($img,$las[0],$las[1],6,5,$color);

    for($i=1;$i<=$y;$i+=6) imageline($img,$las[0],$Y-$i,$las[0],$Y-$i-3, $color);
    for($i=1;$i<=$x;$i+=6) imageline($img,$X-$i,$las[1],$X-$i-3,$las[1], $color);

    $www=119;
    $img2=imagecreatetruecolor($www,$www); // инициализировать картинку
    $x=$las[0]-$www*(3/4); if($x<0) $x=0; elseif(($x+$www)>$Width) $x=$Width-$www;
    $y=$las[1]-$www*(1/4); if($y<0) $y=0; elseif(($y+$www)>$Height) $y=$Height-$www;
    imagecopy($img2,$img,0,0,$x,$y,$www,$www);

    imagegif($img2,$gif_small); imagedestroy($img2);
    imagegif($img,$gif_big); imagedestroy($img);

// die("`$dir | $obem1 | $start | $finish | $srok1 `");

}


// КОНЕЦ КОДА, ДАЛЬШЕ ИДУТ ВСПОМОГАТЕЛЬНЫЕ ПОДПРОГРАММЫ

//=======================================================================================
//======================================================================================
//======================================================================================
// Инициализация графики
//======================================================================================
//======================================================================================
//======================================================================================
function openpic($do) { global $Width,$Height,$Xstart,$Ystart,$img,$FONT;
    global $srok,$obem,$kalendar,$Xmno,$Ymno;

    // создаем картинку
    $Xmax=$srok; // максимальное значение по X
    $Ymax=max($obem,floor($do/1024)); // максимальное значение по Y
    $Xstart=20; // отступы с краев по X
    $Ystart=23; // отступы с краев по Y
    $Xshag=5; // шаг линовки оси X
    $Xshag2=$Xmno; // шаг мелкой линовки оси X
    $Yshag=25; // шаг линовки оси Y
    $Yshag2=$Ymno; // шаг мелкой линовки оси Y
    $Width=$Xmax*$Xmno+$Xstart*2; // ширина картинки
    $Height=$Ymax*$Ymno+$Ystart*2; // высота картинки

    $img=imagecreatetruecolor($Width,$Height); // инициализировать картинку

    // определяем цвета и линуем
    $bg=imagecolorallocate($img,255,255,255); imagefill($img,0,0,$bg);
    $grid=imagecolorallocate($img,225,205,249); imagesetstyle($img, array($bg,$grid));
    imagegrid($img, $Width, $Height,10,IMG_COLOR_STYLED); // расчерчиваем линиями как в тетрадке
    // строгий черный цвет для осей координат
    $ff=imagecolorallocate($img,0,0,0);
    // Рисуем ось X со всеми финтифлюшками
    $Xend=$Width-$Xstart;
    $Yend=$Height-$Ystart;
    imageline($img,$Xstart,$Yend,$Xend,$Yend,$ff); // линия оси X
    imageline($img,$Xstart,$Ystart-2,$Xstart,$Yend,$ff); // линия оси Y
    $fontsize=8; // размер фонта
    // мелкая линовка по X
    if($Xshag2>1) for($i=0;$i<$Xmax;$i++) { $x=$Xstart + $i*$Xshag2; $y=$Height-$Ystart; imageline($img,$x,$y,$x,$y+2,$ff); }
    // крупная линовка по X с цифрами осей
    for($i=0;$i<=$Xmax;$i+=$Xshag) { $x=$Xstart + $i*$Xmno; $y=$Height-$Ystart;
	$p=preg_replace("/^.*\d\d\d\d-\d\d-(\d\d).*$/si","$1",$kalendar[$i]);
	imagettftext($img, $fontsize, 0, $x-( strlen($p)/4 * $fontsize ), $y+($fontsize*2),$ff,$FONT,$p); //$Xos[$i]
	imageline($img,$x,$y,$x,$y+5,$ff);
    }
    // мелкая линовка по Y
    if($Yshag2>1) for($i=0;$i<$Ymax;$i++) { $x=$Xstart; $y=$Height-$Ystart-$i*$Yshag2; imageline($img,$x,$y,$x-2,$y,$ff); }
    // крупная линовка по Y с цифрами осей
    for($i=0;$i<=$Ymax+3;$i+=$Yshag) { $x=$Xstart; $y=$Height-$Ystart-$i;
	$p=$i;
	imagettftext($img, $fontsize, 0, $x-8-( strlen($p)/2 * $fontsize ), $y+($fontsize/2),$ff,$FONT,$p); //$Xos[$i]
	imageline($img,$x,$y,$x-5,$y,$ff);
    }

   // инициализация аппарата печати текста
    global $text_rx,$text_ry,$text_size,$text_color;
    $text_rx=0;
    $text_ry=0;
    $text_size=10;
    $text_color=imagecolorallocate($img, 150, 10, 160);
}
// ========================================================================================
// ========================================================================================
// ========================================================================================
// ========================================================================================

// эта процедура линует изображение как в тетрадке
function imagegrid($img, $w, $h, $s, $color) {
    imagesetthickness($img, 1); // установить толщину линий 1
    for($iw=1; $iw<$w/$s; $iw++){ imageline($img, $iw*$s, 0, $iw*$s, $h, $color); }
    for($ih=1; $ih<$h/$s; $ih++){ imageline($img, 0, $ih*$s, $w, $ih*$s, $color); }
}

//======================================================================================
//======================================================================================
//======================================================================================
// ВЫВОД РЕЗУЛЬТАТА - ТИП 1 (Графиками)
//======================================================================================
//======================================================================================
function statistic_type1($rez) { global $img,$Height,$Width,$colors,$Xstart,$Ystart,$Xmno,$Ymno;
    imagesetthickness($img,$Xmno); // установить толщину линий 3
	for($n=1;$n>=0;$n--) {
	if(!$n) $nc=8; else $nc=15;
	$color = imagecolorallocate($img, $colors[$nc][0], $colors[$nc][1], $colors[$nc][2]);
	$X=$Xstart+$Xmno/2;
	$Y=$Height-$Ystart;
	for($end=sizeof($rez),$k=0;$k<$end;$k++) {
		$y=$rez[$k][$n]; if($y) {
			$x=$k*$Xmno;
			$y=$rez[$k][$n]*$Ymno;
			$xlast=$X+$x;
			$ylast=$Y-$y;
		 	imageline($img,$X+$x,$Y-$y,$X+$x,$Y-1, $color);
			}
		}
	}
return array(floor($xlast),floor($ylast),floor(($xlast-$X)/$Xmno),floor(($Y-$ylast)/$Ymno));
}

// напечатать строку текста $s
// также можно указать координаты X,Y, цвет и размер шрифта - эта информация сохранится на будущее тоже
function pr($s,$text_x,$text_y,$text_c,$text_s,$rotate) { global $img,$FONT;
	global $text_rx,$text_ry,$text_size,$text_color;
	if(!$text_x) $text_x=$text_rx;
	if(!$text_y) $text_y=$text_ry;
	if(!$text_c) $text_c=$text_color;
	if(!$text_s) $text_s=$text_size;
		imagettftext($img, $text_s, $rotate, $text_x, $text_y, $text_c, $FONT, wu($s));
	$text_ry=$text_y+$text_s;
	$text_rx=$text_x;
	$text_color=$text_c;
	$text_size=$text_s;
}

// напечатать квадратик заданного цвета
function prc($n,$text_x,$text_y) { global $img,$colors;
	global $text_rx,$text_ry,$text_size;
	$color = imagecolorallocate($img, $colors[$n][0], $colors[$n][1], $colors[$n][2]);
	if(!$text_x) $text_x=$text_rx;
	if(!$text_y) $text_y=$text_ry;
	$text_r=$text_size-4;
	$str=array($text_x,$text_y, $text_x+$text_r,$text_y, $text_x+$text_r,$text_y-$text_r, $text_x,$text_y-$text_r);
		imagefilledpolygon($img,$str,4,$color);
	$text_rx = $text_x;
	$text_ry = $text_y;
}

function getcolor($n,$img) { global $colors; return imagecolorallocate($img, $colors[$n][0], $colors[$n][1], $colors[$n][2]); }

?>