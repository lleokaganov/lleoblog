<?php

if(!function_exists('h')) { // post-запрос
    if(!isset($_POST['a'])) die('Error 404');
    set_time_limit(60*10); ini_set('max_execution_time',60*10); // максимальное врем€ исполнени€ 10 минут
    include "../config.php"; include $include_sys."_autorize.php"; $ajax=1;

    $er="ERROR ".$GLOBALS['host'].": ";
    if(RE('pass')!=$GLOBALS['SLONPLAY_DAT_CONVERT_SERVICE_PASSWORD']) die($er."Error password");

    $a=RE('a');

    if($a=='test') {
	die("OK 123");
    }

    if($a=='mp3dat') {
	if(!isset($_FILES['file1'])) return die($er."File not uploaded");
	$name=$_FILES['file1']['name'];
	$MP3file=$_FILES['file1']['tmp_name'];
	$DATfile=rpath($MP3file.'.dat');
	if(is_file($DATfile)) return die($er."File exists: ".h($DATfile));
	rename($MP3file,$MP3file.".mp3");
	unset($o); exec("audiowaveform -i \"".escapeshellcmd(rpath(h($MP3file))).".mp3\" -b 8 -z 512 -o \"".escapeshellcmd(rpath(h($DATfile)))."\" 2>&1",$o);
	if(!empty($o) && stristr($o[0],'audiowaveform: not found')) die($er."audiowaveform not found");
	if(is_file($DATfile)) die(file_get_contents($DATfile));
	die($er.implode('<br> - ',$o));
    }

    die($er."Error command `".h($a)."`");
}

function errfile_diagnoz($f,$q="ERROR: Problem with file") { // что-то пошло не так, давайте сделаем точную диагностику
	$d=dirname($f); $hf=h($f); $hd=h($d); $err=array("<b><i>".nl2br(h($q))."</i></b>");
	if(is_dir($d)) $err[]="OK: dir present: `$hd` permissions: <b>".substr(decoct(fileperms($d)),-4)."</b>";
	else {
		$err[]="ERROR: dir not found `$hd`";
		if(is_file($hd)) $err[]="ERROR: it is file!";
		elseif(!is_dir(dirname($d))) $err[]="ERROR: parent dir `".h(dirname($d))."` not exist too!";
	}
	// проверим папку
	touch($f); if(!is_file($f)) $err[]="Error: file not creatable: `$hf`"; else { $err[]="OK: file createble: `$hf`"; unlink($mf); }
	$err="<div>".implode("</div><div>",$err)."</div>";
	return str_ireplace(array("<div>Error:","<div>OK:"),array("<div><font color=red>ERROR:</font>","<div><font color=green>OK:</font>"),$err);
}



function tagtolat($s) { $s=strtr($s,
'абвгдеЄжзийклмнопрстуфхцчшщыьъэю€јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„Ўўџ№ЏЁёя ',
'abvgdeejzijklmnoprstufhccssiqqeuqABVGDEEJZIJKLMNOPRSTUFXCCSSIQQEUQ-');
    $s=preg_replace("/[^a-z0-9\-]+/si","_",$s);
    return $s;
}


function mp3info($l) {
    unset($q); exec("mp3info -x -p \""
        ."genre:%g".'\n'
//        ."file:%F".'\n'
        ."title:%t".'\n'
        ."year:%y".'\n'
        ."size:%k".'\n'
        ."artist:%a".'\n'
        ."comment:%c".'\n'
        ."album:%l".'\n'
        ."Track:%n".'\n'
        ."Stereo:%o".'\n'
//        ."Padding:%p".'\n'
        ."bad:%b".'\n'
        ."Sample:%q".'\n'
        ."layer:%L".'\n'
        ."mpeg:%v".'\n'
        ."Bitrate:%r".'\n'
//        ."time:%m:%s".'\n'
        ."time:%S\""
    ." \"".escapeshellcmd($l)."\"",$q); $qs=implode("\n",$q);

    if(stristr($qs,'mp3info: not found')) idie("You need install <b>mp3info</b> on your server!<br>USE: <i>sudo apt-get install mp3info</i>");

// idie("FILE: ".h($l)."<p>".nl2br(h($qs)));

    if(empty($q)) return array(); // '#ERROR';
    $e=array(); foreach($q as $i=>$c) { list($a,$b)=explode(':',$c,2); $e[$a]=$b; }
	$e['size']=$e['size']*1024; // .' Mb';
	$e['mpeg']=(floor($e['mpeg']))."/".$e['layer']; unset($e['layer']);
	$e['Stereo']=$e['Stereo']=='mono'?0:1;
	if($e['Bitrate']=='Variable') $e['Bitrate']='V';
    return $e;
}

function dur2time($x) { $x=strtr($x,'.',':');
	$h=$m=$s=0; $a=@explode(':',$x); $i=sizeof($a);
	if($i==3) list($h,$m,$s)=$a;
	elseif($i==2) list($m,$s)=$a;
	elseif($i==1) $s=$a[0];
	else idie("Error timestart `".h($x)."`");
	return $h*3600+$m*60+1*$s;
}

function time2dur($x) {
	$h=floor($x/3600);
	$m=floor(($x-$h*3600)/60);
	$s=$x-$h*3600-$m*60;
	return sprintf("%02d:%02d:%02d",$h,$m,$s);
}

$GLOBALS['slonplay_programm']='avconv';






function get_one_module($p,$mod,$full=0) { $s=$p['Body']; $mod=str_replace(' ','\s+',preg_quote($mod,'/'));
    if(!preg_match("/(".$mod."[^\n]+\n\s*)(.*)$/si",$s,$m) || sizeof($m)>3) return false;
    $c=$m[2];
	$c=str_replace(array('{_','_}'),array(chr(1),chr(2)),$c);
	$c0='';
	while($c0!=$c) { $c0=$c; $c=preg_replace("/\001([^\002]*)\002/s","{_$1_}",$c); }
	// $c=$mod.$c;
	preg_match("/[^\001\002]*/s",$c,$mm); $c=$mm[0];
	$c=str_replace(array(chr(1),chr(2)),array('{_','_}'),$c);
    if($full) return $m[1].$c.'_}';
    return $c;
}

function cut_the_text($s,$n) { $s=c0($s);
    $i=strpos($s,'{_'); if($i!==false) $s=substr($s,0,$i);
    $i=strpos($s,"\n"); if($i!==false) $s=substr($s,0,$i);
    if(strlen($s)<$n*1.5) return $s;
    $max=0; foreach(explode(' ',"? ! . ' \"") as $c) { $i=strpos($s,$c); if($i<$n*1.5) $max=max($max,$i); }
    $max=max(20,$max);
    return substr($s,0,$max);
}




function SLONPLAY_ajax() {
    $a=RE('a');

if($a=='make_dat') {
    $MP3=RE('MP3');
    $num=RE0('num');
    $stage=RE('stage');
    $DAT=preg_replace("/\.mp3$/si",'.dat',$MP3);
    $DATfile=rpath($GLOBALS['host'].ltrim($DAT,'/'));
    if(is_file($DATfile)) return "clean('songeditor');majax('module.php',{mod:'SLONPLAY',a:'editor',num:'".h($num)."',mp3:'".h($MP3)."'});";

    // ≈сли файла нету пока

    if(isset($GLOBALS['SLONPLAY_DAT_CONVERT_SERVICE_SERVER'])) { // ='http://home.lleo.me';

	if($stage===false) return "zabil('songeditor_cv',vzyal('songeditor_cv')+\"<p>Working with host <b>".$SLONPLAY_DAT_CONVERT_SERVICE_SERVER."</b>"
	." <img src='".$GLOBALS['www_design']."img/ajax.gif'>\"); majax('module.php',{mod:'SLONPLAY',a:'make_dat',num:$num,MP3:'".h($MP3)."',stage:0});";

	if($stage==0) {
	    include $GLOBALS['include_sys']."_files.php";
	    $MP3file=rpath($GLOBALS['host'].ltrim($MP3,'/')); if(!is_file($MP3file)) idie('File not found: '.h($MP3file));

	idie($GLOBALS['SLONPLAY_DAT_CONVERT_SERVICE_SERVER']."/site_module/SLONPLAY.php");

	    $d=POST_file($MP3file,$GLOBALS['SLONPLAY_DAT_CONVERT_SERVICE_SERVER']."/site_module/SLONPLAY.php",array(
		'a'=>'mp3dat','pass'=>$GLOBALS['SLONPLAY_DAT_CONVERT_SERVICE_PASSWORD']
	    ));
	    if(substr($d,0,5)=='ERROR'||stristr($d,"ERROR: ")) idie("POST-server Error: ".h($d));
	    if(stristr($d,'<html')) idie("POST-server HTML-Error: ".h($d));
	    if(is_file($DATfile)) idie("ERROR: file exist ".h($DATfile));
	    fileput($DATfile,$d);
	    if(!is_file($DATfile)) idie("ERROR: error write file ".h($DATfile));
	    return "clean('songeditor_cv');majax('module.php',{mod:'SLONPLAY',a:'editor',num:'".h($num)."',mp3:'".h($MP3)."'});";
	}

    } else { // € сам себе и небо и луна

	$MP3file=$GLOBALS['filehost'].rpath(ltrim($MP3,'/')); if(!is_file($MP3file)) idie('File not found: '.h($MP3file));

	if(preg_match("/\.MP3$/s",$MP3file)) { // ≈баный трансл€тор не понимает заглавные MP3
	    $MP3fileMP3=$MP3file;
	    $MP3file=preg_replace("/\.mp3$/si",'.mp3',$MP3file);
	    rename($MP3fileMP3,$MP3file);
	    if(!is_file($MP3file)) idie("Can't rename `".h($MP3fileMP3)."` to `".h($MP3file)."`");
	    idie("‘айл `".h($MP3fileMP3)."` был переименован в `".h($MP3file)."`<br>потому что дебильный трансл€тор audiowaveform не понимает им€ файлы .MP3 заглавными буквами.<p>Ќадо исправить заметку и повторить.");
	}

	$DATfile=preg_replace("/\.mp3$/si",'.dat',$MP3file);
	if(is_file($DATfile)) return idie("File exists: ".h($DATfile));

//	idie("audiowaveform -i \"".rpath(h($MP3file))."\" -b 8 -z 512 -o \"".rpath(h($DATfile))."\"");

//	rename($MP3file,$MP3file.".mp3");
	unset($o); exec("audiowaveform -i \"".escapeshellcmd(rpath(h($MP3file)))."\" -b 8 -z 512 -o \"".escapeshellcmd(rpath(h($DATfile)))."\" 2>&1",$o);

// dier($o);


	if(!empty($o) && stristr($o[0],'audiowaveform: not found')) idie($er."audiowaveform not found");
	if(!is_file($DATfile)) idie("Can't create file: ".h($DATfile)."<p>".h($o[0]));
	idie('OK');
    }

    return "zabil('songeditor_cv',vzyal('songeditor_cv')+\"<p>Working <img src='".$GLOBALS['www_design']."img/ajax.gif'>\")";
}

if($a=='editor') {
    $MP3=RE('mp3');
    $num=RE0('num'); $p=ms("SELECT * FROM `dnevnik_zapisi` WHERE `num`='".e($num)."'".ANDC(),'_1',0);
    $txt=c0(get_one_module($p,'{_SLONPLAY: '.$MP3));

	$rzd='<!-- SLONPART -->';
	$r=preg_replace("/\n\s*(\!*)\s*(\d+:\d+)/si",$rzd."$1"."$2","\n".$txt);
	$a=array();
	foreach(explode($rzd,$r) as $x) { $x=c0($x); if($x=='') continue;
		if(!preg_match("/^(\!*)([\d\:]+)(\s*\-\s*[\d\:]+|\s*\Ч\s*[\d\:]+|)(.*?)$/si",$x,$m)) { continue; } // return "ERROR: #05".h($x);
		$a[]=array('from'=>c0($m[2]),'to'=>ltrim($m[3],"\t -Ч"),'text'=>$m[4]);
	}
	foreach($a as $n=>$x) {
	    $a[$n]['txt']=trim(str_replace(array('"',"\n"),array("\\\"","\\n"),$x['text'])," \t\r"); // cut_the_text($x['text'],50));
	    $a[$n]['f']=dur2time($x['from']);
	    $t=$x['to']; if(empty($t)) {
	    if(isset($a[$n+1])) $t=$a[$n+1]['from']; else { $t=mp3info($GLOBALS['host'].ltrim($MP3,'/')); $a[$n]['t']=$t['time']+5; continue; }
	    } $a[$n]['t']=dur2time($t);
        } // расставить недостающие времена
	$SEG=array(); foreach($a as $n=>$x) $SEG[]="{startTime:".$x['f'].",endTime:".$x['t'].",editable:true,color:song_colors[".$n."],labelText:\"".$x['txt']."\"}";
    $DAT=preg_replace("/\.mp3$/si",'.dat',$MP3);
    $DATfile=rpath($GLOBALS['host'].ltrim($DAT,'/'));
    if(!is_file($DATfile)) return "helpc('songeditor',\"".njsn("ƒл€ работы редактора придетс€ создать к нашему MP3 специальный DAT-файл."
."<br>Ёто может зан€ть несколько минут. ‘айл будет расположен р€дом с mp3:"
."<dd>".h($MP3).""
."<dd><u>".h($DAT)."</u>"
."<div id='songeditor_cv'></div>"
."<p><input type=button value='Start!' onclick=\"majax('module.php',{mod:'SLONPLAY',a:'make_dat',num:'".(1*$num)."',MP3:'".h($MP3)."'})\">"
)."\");";

$JS="

LOADS('".$GLOBALS['wwwhost']."extended/waveform/jquery.js',function(src) {

LOADS([
'".$GLOBALS['wwwhost']."extended/waveform/peaks.css',
'".$GLOBALS['wwwhost']."extended/waveform/style.css',
'".$GLOBALS['wwwhost']."extended/waveform/lodash.compat.js',
'".$GLOBALS['wwwhost']."extended/waveform/KineticJS.js',
'".$GLOBALS['wwwhost']."extended/waveform/EventEmitter.js',
'".$GLOBALS['wwwhost']."extended/waveform/waveform-data.all.min.js',
'".$GLOBALS['wwwhost']."extended/waveform/peaks.min.js?'+Math.random(),
'".$GLOBALS['wwwhost']."extended/waveform/colors.js?'+Math.random(),
'".$GLOBALS['wwwhost']."extended/waveform/DO.js?'+Math.random()
],function(src){sound_DO([".implode(",",$SEG)."],'".h($DAT)."');
});


});";
$o="
<div id='peaks-buka'></div>
<input type='hidden' id='peaks-num' value='".h($num)."'>
<input type='hidden' id='peaks-mp3' value=\"".h($MP3)."\">

<div id='peaks-container'></div>
<div class='peaks-control'>
  <div class='peaks-audio'>
    <audio id='peaks-audio' controls=controls><source src='".h($MP3)."' type='audio/mpeg'>Bad Browser.</audio>
  </div>
  <button id='songbt-zoomin' class='button'>Zoom In</button>
  <button id='songbt-zoomout' class='button'>Zoom Out</button>
  <button id='songbt-segment' class='button'>Create Segment</button>
    <input type=text id='segtext' size=50 style='display:none;text-align:left;' class='button'>
  † † † † † † †
  <button id='songbt-save' class='button'>Save</button>
</div>
";
return $JS."helpc('songeditor','');idd('songeditor').style.width=(getWinW()-50)+'px';helpc('songeditor',\"".njsn($o)."\");";
}


 ADMA(); // sudo apt-get install libav-tools

if($a=='save_segments') {
    $num=RE0('num');
    $MP3=RE('mp3');
    $text=RE('text');
    $p=ms("SELECT * FROM `dnevnik_zapisi` WHERE `num`='".e($num)."'".ANDC(),'_1',0);
    $txt=get_one_module($p,'{_SLONPLAY: '.$MP3,1);
    list($zag,)=explode("\n",$txt,2);
    $s=$zag."\n\n".$text."\n_}";
    $s=str_replace($txt,$s,$p['Body']);
    ms("UPDATE `dnevnik_zapisi` SET `Body`='".e($s)."' WHERE `num`='".e($num)."'".ANDC(),'_l',0);
    otprav("salert('saved',100)");
//  idie(nl2br($Body));
}


if($a=='fragment') { // расписать фрагменты MP3
    $mane=h(RE('mane'));
    $mp3=h(RE('mp3')); // $mp3=RE('mp3');
    $webdir=preg_replace("/\.mp3$/si",'',$mp3);

    $r=RE('txt'); if(empty($r)) return "clean('$mane');salert('Done',500);";
    $r=@explode('<br>',$r);
	$new=$r[0]; unset($r[0]);
	$s=implode('<br>',$r);

// idie($mp3);

	if(!preg_match("/^.*?\-fragment\-([\d\.]+)\-(.*)\.mp3$/si",$new,$m)) idie("Error file: ".h($new));
	$from=dur2time($m[1]);
	if($m[2]!='') $to=dur2time($m[2]); else idie("Error TO=0"); // $to=time2dur(mp3info($ff.'/'.$mp3)['time']+5);

	$dur=$to-$from; if($dur<=0) idie("Error: отрицательна€ длительность `$dur` ".h($new)." ($to-$from)"
."<p>mp3=`".h($mp3)."`"
."<br>mane=`".h($mane)."`"
."<br>new=`".h($new)."`"
."<br>s=`".h($s)."`"
);

	$ff=rtrim($GLOBALS['host'],'/');
	if(!is_file($ff.$webdir.'/'.$new)) {
	    if(!is_dir($ff.$webdir)) testdir($ff.$webdir); if(!is_dir($ff.$webdir)) return "\nERROR: не могу создать папку ".h($ff.$webdir)."\n";

	    $q=" -i \"".escapeshellcmd($ff.$mp3)."\" -ss ".time2dur($from)." -t ".time2dur($dur)." -acodec copy \"".escapeshellcmd($ff.$webdir.'/'.$new)."\" 2>&1";
	    $otv=''; exec($GLOBALS['slonplay_programm'].$q,$otv); $otv=implode("\n",$otv);
	    if(stristr($otv,$GLOBALS['slonplay_programm'].': not found')) {
		$GLOBALS['slonplay_programm']=($GLOBALS['slonplay_programm']=='avconv'?'ffmpeg':'avconv');
		$otv=''; exec($GLOBALS['slonplay_programm'].$q,$otv); $otv=implode("\n",$otv);
		if(stristr($otv,$GLOBALS['slonplay_programm'].': not found')) idie("Error: you need install <b>avconv</b> or <b>ffmpeg</b> on your server!<br>USE: <i>sudo apt-get install libav-tools</i>");
	    }
	    if(!is_file($ff.$webdir.'/'.$new)) idie(errfile_diagnoz($ff.$webdir.'/'.$new,$q."\n\n==>".$otv."\n\n"));

	}
	return "ohelpc('$mane','подготовка файлов mp3',\"<img src='".$GLOBALS['www_design']."img/ajaxm.gif'>†".njsn($s)."\");
		majax('module.php',{mod:'SLONPLAY',a:'fragment',mp3:\"$mp3\",mane:'$mane',txt:\"".njsn($s)."\"});";
}

if($a=='delfragment') { // удалить расписанные фрагменты MP3
    $dir=rtrim($GLOBALS['host'],'/').preg_replace("/\.mp3$/si",'',RE('mp3'));
    if(!is_dir($dir)) return ''; // idie("ERROR: folder not found `".h($dir)."`");
    exec("rm -f \"".escapeshellcmd($dir)."\"/*.mp3"); rmdir($dir);
    if(!is_dir($dir)) return "salert('Done',500);";
    idie("ERROR: не удалось удалить папку `".h($dir)."`");
}

idie('Unknown a='.h($a));
}

if(function_exists('STYLES')) STYLES("
.pla {display:block !important;padding:10px 0px 0px 0px !important;}
audio {width:500px;margin:30px;}
");


function SLONPLAY_ITEM($x,$cf) { // $preslon='';
	$ff=rtrim($GLOBALS['host'],'/');
	$mp3=$x['mp3'];
	$webdir=preg_replace("/\.mp3$/si",'',$mp3);
	if($x['to']=='') { $to=mp3info($ff.'/'.$mp3); $to=$to['time']+5; $to=time2dur($to); } else $to=time2dur(dur2time($x['to']));
	$from=time2dur(dur2time($x['from']));
	$new=basename($webdir)."-fragment-".strtr($from.'-'.$to,':','.').'.mp3';

    if($GLOBALS['ADM']) { // если админ, просмотреть аудиофрагменты
	if(!is_file($ff.$webdir.'/'.$new)) $GLOBALS['SLONPLAYFILES'].=$new."\n";
    }

    $Date='';
    $GLOBALS['SLON_DELTAG']=array();
    $GLOBALS['SLON_ADDTAG']=array();

	// «апрет наследование тэгов {notag:—инельникова, ваывывапвап, апрпвар}
	$x['text']=preg_replace_callback("/\{notag\:([^\}]{2,120})\}/s",function($t){$GLOBALS['SLON_DELTAG'][]=$t[1];return '';},$x['text']);
	$x['text']=preg_replace_callback("/\{([^\}]{2,120})\}/s",function($t){$GLOBALS['SLON_ADDTAG'][]=$t[1];return '';},$x['text']);

	if(sizeof($GLOBALS['SLON_ADDTAG'])) { // если есть разметка
	    $Date='part/'.basename($webdir).'/'.strtr($from."-".$to,':','.');

    // добавить тэги
    $atag=gettags();
    // к ним добавить
    $slon_add=array_merge($GLOBALS['SLON_ADDTAG'],array($x['name'],'part',"autocreate".$GLOBALS['num']));
    foreach($slon_add as $k) { if(strstr($k,',')) idie("Error: tags can't content `,`!"); if(false===array_search($k,$atag)) $atag[]=$k; }
    // у них удалить
    $GLOBALS['SLON_DELTAG'][]='raw';
    foreach($GLOBALS['SLON_DELTAG'] as $k) { if(false!==($k=array_search($k,$atag))) unset($atag[$k]); }

	if(RE('a').RE('asave')=='submit'.'0') {
	    if(!isset($GLOBALS['SLONDEL'])) { // сперва удалить все
		foreach(ms("SELECT `num` FROM `dnevnik_tags` WHERE `tag`='".e('autocreate'.$GLOBALS['num'])."'".ANDC(),"_a",0) as $l) zametka_del($l['num']);
		$GLOBALS['SLONDEL']=1;
	    }

	    $num=intval(ms("SELECT `num` FROM `dnevnik_zapisi` WHERE `Date`='".e($Date)."'",'_l',0));
	    $num=zametka_save(array(
		'num'=>$num,
		'Date'=>$Date,
		'Body'=>"\n{"."_MP3: ".$webdir.'/'.$new."|mp3_}\n\nфрагмент ".$from."Ч".$to.($x['to']==''?' END':'')."\n\n".c0($x['text']),
		'Header'=>$x['name'].' - '.$from."Ч".$to,
		'Access'=>'all',
		'tags'=>implode(',',$atag),
		'opt'=>array('template'=>'slon')
	    ));

	    // насоздавать јЌќЌ—ќ¬
// dier($GLOBALS['SLON_ADDTAG']);
	foreach($GLOBALS['SLON_ADDTAG'] as $k) {
	    $Date='tag/'.tagtolat($k);
	    $ms="SELECT COUNT(*) FROM `dnevnik_zapisi` as z INNER JOIN `dnevnik_tags` as t ON t.`num`=z.`num` WHERE z.`Date`='".e($Date)."' AND t.`tag`='".e($k)."'";
	    if(!ms($ms,"_l",0)) {
		    $num=zametka_save(array(
			'num'=>0,
			'Date'=>$Date,
			'Body'=>"{_SLONANONS: ".$k." _}",
			'Header'=>$k,
			'Access'=>'all',
			'tags'=>"slon,main,tag,".$k,
			'opt'=>array('template'=>'slon')
		    ));
	    }
	}


	}
    }

    if($cf['main']==1) $fromto=$from."Ч".$to.($x['to']==''?' END':'')." "; else $fromto=$cf['songname'];

    $s=$x['text'];

    if(strstr($s,'[dur]')) $s=str_replace('[dur]',time2dur(dur2time($to)-dur2time($from)),$s); // продолжительность

    if(strstr($s,"\n\n")) {
	list($a,$t)=explode("\n\n",$s,2);
	return "{"."_PLAY: ".$webdir.'/'.$new." ".$fromto.c0($a)." _}".($Date==''?"\n":" <a href='/$Date'>/$Date</a>\n")."\n".c0($t);
    }

    return (strlen($s)<200?
"{"."_PLAY: ".$webdir.'/'.$new." ".$fromto.c0($s)." _}"
:"\n{"."_PLAY: ".$webdir.'/'.$new." фрагмент ".$fromto." _}".($Date==''?"\n":" <a href='/$Date'>/$Date</a>\n")."\n".c0($s)
);
}


//==========================================

/*
function fizicolors(){
    $x=0;
    $P=array();

    for($i=0;$i<128;$i+=20) {
	for($j=0;$j<128;$j+=20) {
	    for($k=0;$k<128;$k+=20) { $P[]="#".strtoupper(sprintf("%02x%02x%02x",$i+128,$j+128,$k+128)); }
	}
    }

    shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);shuffle($P);
    $o=''; foreach($P as $i=>$l) $o.="\n.pal".sprintf("%03d",$i)." ".$l;

    $o=trim($o);
// file_put_contents("pal.css",$o);
// die(nl2br($o));
}
*/












function SLONPLAY($e){ $GLOBALS['SLONPLAYFILES']=''; $o=''; $mp3='';

$cf=array_merge(array(
'songname'=>'',
'main'=>1
),parse_e_conf($e)); $e=$cf['body'];


	if(!preg_match("/^\s*([^\n]+\.mp3[^\n]+)\n\s*(.*)$/si",$e,$m)) return "<b>ERROR: Ќе найдена метка файла MP3, вы должны указать файл в первой же строке</b>";
	list($mp3,$name)=@explode(' ',$m[1],2);
	$mpf=rpath($GLOBALS['host'].ltrim($mp3,'/'));
	if($cf['main']==1 && !is_file($mpf)) return "<font color=red>ERROR: File not found: ".h($mpf)."</font>";

	// link
	$m[2]=preg_replace_callback("/\[([^\]]+)\s([0-9a-z\:\/\%\!\#\.\,\:\-\_\+\=\~]+)\]/si",function($t){return "<a alt=\"ссылка: ".h($t[2])."\" href='".h($t[2])."'>".$t[1]."</a>";},$m[2]);

	$rzd='<!-- SLONPART -->';
	$r=preg_replace("/\n\s*(\!*)\s*(\d+:\d+)/si",$rzd."$1"."$2","\n".$m[2]);

	$a=array();
	foreach(explode($rzd,$r) as $x) { $x=c0($x); if($x=='') continue;
		if(!preg_match("/^(\!*)([\d\:]+)(\s*\-\s*[\d\:]+|\s*\Ч\s*[\d\:]+|)(.*?)$/si",$x,$m)) { $o.=$x; continue; }
		$a[]=array('imp'=>$m[1],'name'=>$name,'mp3'=>$mp3,'from'=>c0($m[2]),'to'=>ltrim($m[3],"\t -Ч"),'text'=>$m[4]);
	}

	foreach($a as $n=>$x) {
	    if(empty($x['to'])&&isset($a[$n+1])) $x['to']=$a[$n]['to']=$a[$n+1]['from'];
	    $o.=SLONPLAY_ITEM($x,$cf); // важное
	} // расставить недостающие времена


// dier($a);


	if($GLOBALS['ADM']&&$GLOBALS['SLONPLAYFILES']!='') {
	    $s=c0($GLOBALS['SLONPLAYFILES']);
	    $s=str_replace("\n","<br>",$s);
	    $mane=h("SLONPLAY_".basename($mp3));
	    $GLOBALS['_SCRIPT'][$mane]="
		page_onstart.push(\""
		."ohelpc('".$mane."','подготовка ".h($mp3)."',\\\"<img src='".$GLOBALS['www_design']."img/ajaxm.gif'>†".njsn($s)."\\\");"
		."majax('module.php',{mod:'SLONPLAY',a:'fragment',mp3:'".h($mp3)."',mane:'".$mane."',txt:\\\"".njsn($s)."\\\"});"
		."\");";
	    // $o="<hr>".h("majax('module.php',{mod:'SLONPLAY',a:'fragment',txt:\\\"".njsn($s)."\\\"});")."<hr>".$o;
	}

    if($cf['main']==1) $o="<p class=name>".h($name)."</p>\n\n"
.(1||$GLOBALS['ADM']?"<div style='vertical-align:top;display:inline-block;align:center;position:relative;'>"
."<div style='display:inline-block;'>{_MP3:".$mp3."|mp3_}</div>"
."<div style='display:inline-block;'>"
."<span class='ll' onclick=\"majax('module.php',{mod:'SLONPLAY',a:'delfragment',mp3:'".h($mp3)."'});\">Clean</span>"
."<p><img alt='Fragments Editor' src='".$GLOBALS['www_design']."img/settings48.png' onclick=\"majax('module.php',{mod:'SLONPLAY',a:'editor',num:'".h($GLOBALS['article']['num'])."',mp3:'".h($mp3)."'});\">"
."</div>"
."</div>"
:"{_MP3:".$mp3."|mp3_}").$o;

    return $o;
}
?>