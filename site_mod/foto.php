<?php // Работа с[M@!- фотоальбомом

include "../config.php";
// if(!isset($_GET['mjax'])&&!isset($_GET['lajax'])) { require_once $include_sys."JsHttpRequest.php"; $JsHttpRequest =& new JsHttpRequest($wwwcharset); } else $ajax=1;
include $include_sys."_autorize.php"; // сперва JsHttpRequest, затем autorize

$usenames='html htm css txt text js';
function razre_ras($l){ return in_array(strtolower(substr(strrchr($l,'.'),1)),explode(' ',$GLOBALS['usenames'])); }

// idie('REMONT');

$hid=intval($_REQUEST["hid"]);
$a=RE('a'); ADH();

$lastphoto_file=$hosttmp."lastphoto.txt";

//=================================== album ===================================================================
if($a=='download') { ADMA(); $url=RE('url'); $num=RE0('num'); $r=RE0('r');

	if(!strstr($url,'://') || strstr($url,$httpsite)) idie('Error: '.h($url));
	if(false==($txt=ms("SELECT `Body` FROM `dnevnik_zapisi` WHERE `num`='".$num."'","_l",0))) idie("Error num: ".$num);
	if(!strstr($txt,$url)) idie("Error 1");

	if(is_ras_image($url)) { $ras='.'.explode_last('.',$url); $u=substr($url,0,strlen($url)-strlen($ras)); }
	else { $ras='.jpg'; $u=$url; }
	$u=preg_replace("/(https|http|ftp)\:\/\//si",'',$u);

	$i=(120-strlen($filehost));
	if(strlen($name)>$i) { $e=parse_url($u); $name=$e['host'].'-'.basename($e['path']); }
	$name=preg_replace("/[^0-9\.a-zA-Z\_\-]+/s","_",$u).$ras;

	    $url2=rpath(accd().date("Y/m/").$name);
	    if(!is_ras_image($url2)) $url2=trim($url2,'.').'.jpg'; // и добавить jpg если надо
	    $file=$filehost.$url2;

	if(strlen($file)>$i || is_file($file)) {
	    $name=rtrim(substr($name,0,($i-33)),'-_.').'_'.md5($name);

	    $url2=rpath(accd().date("Y/m/").$name);
	    if(!is_ras_image($url2)) $url2=trim($url2,'.').'.jpg'; // и добавить jpg если надо
	    $file=$filehost.$url2;

	}

	testdir(dirname($file));

	$r0r=RE0('r'); if($unic && ($r0r==0 || ($r0r==3 && !RE0('newsize')))) { // скачать просто
	    AD(); fileput($file,fileget($url));
	} else { // скопировать в tmp файл
	    $tmp=$hosttmp."foto_".md5($url2); if(is_file($tmp)) unlink($tmp); if(is_file($tmp)) idie('not delete!');
	    fileput($tmp,fileget($url));
	    require_once $include_sys."_fotolib.php"; $fotoset=loadset(); // обработать
	    if($r0r==3) obrajpeg($tmp,$file,RE0('newsize'),$fotoset['Q'],RE('sign'));
	    else obrajpeg($tmp,$file,$fotoset['X'],$fotoset['Q'],($r0r==1?$url:'')); //fotoset['logo']
	    unlink($tmp); // удалить
	}

	if(!is_file($file)) idie("Download error:<p><a href='".h($url)."'>".h($url)."</a>");

	$txt2=preg_replace("/(\{\_IMG\:\s*)".preg_quote($url,'/')."([\s\_$])/s","{_NO:".$url."_}"."$1".$wwwhost.$url2."$2",$txt);

	msq_update('dnevnik_zapisi',array('Body'=>e($txt2)),"WHERE `num`='$num'");

	otprav("zabil('foto_".md5($url)."',\"<center><img src='".h($wwwhost.$url2)."'></center>\")");

}

//=================================== album ===================================================================
if($a=='album') { ADMA();
$js=mpers(get_sys_tmp("foto.js"),array(
'opendir'=>'', // 2010/05';
'alb'=>albumdir('/'),
'www_design'=>$www_design
));
otprav_sb('tree.js',$js);
}

//=================================== editpanel ===================================================================
if($a=='createfile') { ADMA(); $dir=RE('dir'); if($dir=='/') $dir='';
$s=$wwwhost.accd()."<input onchange='createfile_go()' id='createfile_name' type='text' size='50' value=\"".h($dir)."\">&nbsp;<input type='button' value='Go' onclick='createfile_go()'>";
otprav("ohelpc('createfile','Create File',\"".njs($s)."\"); idd('createfile_name').focus();
createfile_go=function(){
	var l=idd('createfile_name').value,d=0;
	if(l.indexOf('.')<0 && confirm('Create folder?')) d=1;
	majax('foto.php',{a:'createfile_do',file:l,dir:d});
};
");
}
//--
if($a=='createfile_do') { ADMA(); $file=RE('file'); $f=rpath($filehost.accd().$file);
	if(is_file($f)||is_dir($f)) idie("Error! `".h($l)."` exist!");
	$s="clean('createfile');";
	if(RE0('dir')) { testdir($f); $s.="majax('foto.php',{a:'album'});"; }
	else { testdir(dirname($f)); $s.="treeid='".$file."'; majax('foto.php',{a:'edit_text',file:treeid});"; }
	otprav($s);
}

if($a=='uploadform') { ADMA(); $r=loadset(); $dir=$r['dir'];
$newd=date("Y/m"); $newd=($newd!=$dir && !is_dir($filehost.accd().$newd)?$newd:0);
$a=array('hid'=>$hid,'num'=>RE0($num),'wwwhost'=>$wwwhost,'dir'=>$dir,'id'=>$id,'idhelp'=>$idhelp,'www_design'=>$www_design,'newd'=>$newd);
otprav(mpers(str_replace(array("\n","\r","\t"),'',get_sys_tmp("foto_upload.htm")),$a));
}
//=================================== editpanel ===================================================================








if(isset($_REQUEST['onload'])) otprav(''); // все дальнейшие опции будут запрещены для GET-запроса

//=================================== работа с нодами альбома ===================================================================
if($a=='formfotoset') { ADMA(); $idhelp='fotoset';
	$r=loadset();
otprav("
helps('fotoset',\"<fieldset><legend>Настройки фото</legend>".njsn("
<form onsubmit=\"return send_this_form(this,'foto.php',{a:'formfotoset_save'})\">
<table>
<tr><td>ширина картинки:</td><td><input id='fotoset_X' size='4' type='text' name='X' value='".h($r['X'])."'>px</td></tr>
<tr><td>качество картинки:</td><td><input size='3' type='text' name='Q' value='".h($r['Q'])."'>%</td></tr>
<tr><td>ширина превью:</td><td><input size='4' type='text' name='x' value='".h($r['x'])."'>px</td></tr>
<tr><td>качество превью:</td><td><input size='3' type='text' name='q' value='".h($r['q'])."'>%</td></tr>
<tr><td>папка:</td><td>".$wwwhost.accd()."<input size='15' type='text' name='dir' value='".h($r['dir'])."'></td></tr>
<tr><td>подпись:</td><td><input size=25 type=text name='logo' value='".h($r['logo'])."'></td></tr>
</table>
<p><input type='submit' value='Save'></form>
")."</fieldset>\");
idd('fotoset_X').focus();
");
}
//--
if($a=='formfotoset_save') { ADMA();
	$r=array(); foreach(explode(' ',trim(RE('names'))) as $l) $r[$l]=RE($l);
	$r=updset($r);
	otprav("zabilc('kudafoto','".h($r['dir'])."'); clean('fotoset');");
}
//=================================== работа с нодами альбома ===================================================================
if($a=='albumgo') { ADMA(); $id=RE('id'); 
  otprav(albumdir($id)." treeallimgicon(treeicon);".(RE0('tog')?"treetoggleNode(idd('$id'),1);":''));
}
//=================================== setdir ===================================================================
if($a=='setdir') { AD(); otprav("helps('foto_$hid',\"<fieldset><legend>выбираем папку</legend>??</fieldset>\");"); }

if($a=='savedir') { AD(); $dir=RE("dir"); $dir=h(preg_replace("/\.+/s",'.',$dir));
	fileput($lastphoto_file,$dir);
	otprav("clean('foto'); majax('foto.php',{a:'uploadform'});");
}
//=================================== album-del ===================================================================
if($a=='filemove') { ADMA(); if(!sizeof($_REQUEST['sel'])) idie('Select files,<br>then select folder<br>and then press button.');
$ndir=array(); $dir=RE('dir'); $to=rpath($filehost.accd().$dir); foreach($_REQUEST['sel'] as $l=>$n) {
		$f=rpath($filehost.accd().$l); $t=rpath($to.'/'.basename($l));
		if(is_file($f)&&!is_file($t)) { rename($f,$t); $ndir[dirname($l)]=1; }
	}
	$ndir[$dir]=1; $s=''; foreach($ndir as $l=>$n) $s.="treereload('".rtrim($l,'/')."/');";
//	idie($s);
	otprav($s."treeremove();salert('Moved!',800);");
}

// ----
if($a=='filecopy') { AD(); if(!sizeof($_REQUEST['sel'])) idie('Select files,<br>then select folder<br>and then press button.');
$to=rpath($filehost.RE('dir')); foreach($_REQUEST['sel'] as $l=>$n) {
		$f=rpath($filehost.$l); $t=rpath($to.'/'.basename($l));
		if(is_file($f)&&!is_file($t)) copy($f,$t);
	}
	otprav("treeremove(); treereload(); salert('Copied!',800);");
}
// ----
if($a=='albumdel') { ADMA(); $s='';

	if(!sizeof(RE('sel'))) {
		otprav("if(confirm('Delete '+treefolder+' ?')) {
			treeselected={}; treeselected[treefolder]=1;
			majax('foto.php',{a:'albumdel',sel:treeselected});
		}");
	}

	foreach(RE('sel') as $l=>$n) { $f=rpath($filehost.accd().$l);
		$isf=is_file($f);
		if($isf) {
			$x=predir($f,'mic/'); if(is_file($x)) unlink($x);
			$x=predir($f,'pre/'); if(is_file($x)) unlink($x);
			unlink($f);
		} elseif(is_dir($f)) {
			if(!sizeof(glob($f.'/*'))) rmdir($f);
			else idie('Folder is not empty: <br>'.h($l)."<br>$f<br>".print_r(glob($f.'/*'),1));
		} else idie('Not found: '.h($l));

		if($isf&&is_ras_image($l)) $s.="clean('".$l."');"; // удалить фотку
		else $s.="var e=idd('".$l."');"
.($acc!=''&&is_file(rpath($filehost."binoniq/userdata/".$l)) // если есть такой же базовый - цвет серый
?"e.style.color='rgb(204,204,204)';" // иначе - удалить строку:
:"clean('".$l."');while(e.tagName!='LI'&&e.parentNode!=undefined)e=e.parentNode; e.parentNode.removeChild(e);"
// иначе удалить ноду
);
	}
	$s.="treeremove(); clean('fotooper');";
	otprav($s);
}
//==================================================================================================
if($a=='lostfoto') { AD();
	$num=intval(str_replace('editor','',RE('idhelp'))); if(!$num) idie('Error 0');
	$Date=ms("SELECT `Date` FROM `dnevnik_zapisi` WHERE `num`=".$num,"_l"); if($Date===false) idie('Error 1');
	list($y,$m,)=explode('/',$Date,3); if(!intval($y)||!intval($m)) idie('Error 2');
	$p=glob($filehost.$y."/".$m."/*.jpg");
	$s=''; foreach($p as $l) {
		preg_match("/([^\/]+)\.jpg$/si",$l,$e); $e=$e[1];
		if(!ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date` LIKE '".e($y)."/".e($m)."/%' AND `Body` LIKE '%".e($e)."%'","_l"))
		$s.="\\n{_FOTOM: $e _}";
	}

	otprav("
		var v=idd('editor".$num."_Body');
		if(v) v.value=v.value+'".$s."';
	");
}
//=================================== treeact ===================================================================

// Далее - процедуры фотообработки

require_once $include_sys."_fotolib.php";

//======================================
function refix_image_pre($s,$p=1) { // 0 - обычную 2 - мелкую 1 - обе
return ($p<2?preg_quote($s,'/'):'').($p>0?($p==1?'|':'').preg_quote(predir($s,'pre/'),'/'):''); }

function refix_image($img,$i,$fix,$p=1) { $i2=preg_replace("/^\d{4}\/\d{2}\//s",'',$i);
return "document.body.innerHTML=document.body.innerHTML.replace(/src=[\\'\\\"]*("
.refix_image_pre('/'.$GLOBALS['blogdir'].$i,$p)
.($i2==$i?'':'|'.refix_image_pre($i2,$p))
.'|'.refix_image_pre($img,$p)
.")[\\'\\\"]*/g,\"".$fix."\");";
}




if($a=='album_edit') { ADMA();

$p0=RE('p');

$js="

send_ftall=function(g,i){ if(i==undefined) i=0;
    var s='',e;
    for(;i<g;i++) {
    e=idd('ftarea".$p0."_'+i);
    if(e.value!=e.defaultValue) {
	zabil('ftd',vzyal('ftd')+' *');
	majax('editor.php',{a:'bigfotoedit_send',g:g,i:i,all:1,img:aft[i][0],num:".RE0('num').",i:i,p:".$p0.",txt:e.value}); break; }
    }
    if(i==g) clean('ftalledit');
};

clean('bigfoto');

var g=0,e,aft={},i,s='',w=idd('bigfostr').style.width; while(e=idd('bigfot".$p0."_'+g)) { aft[g]=[e,vzyal('bigfott".$p0."_'+g)]; g++; }

for(i=0;i<g;i++) s=s+\"<p><div>"
."<div><textarea id='ftarea".$p0."_\"+i+\"' style='height:300px;width:\"+w+\";'>\"+aft[i][1]+\"</textarea></div>"
."<img src='\"+aft[i][0]+\"'>\"+i+\"</div>\";

var bu=\"<input style='padding:50pt;' type='button' value='S E N D' onclick='send_ftall(\"+g+\")'>\";

s='<center>'+bu+'<p>'+s+\"<br><span id='ftd'></span><p>\"+bu+'</center>';

ohelpc('ftalledit','All Edit',s);
";



otprav($js);


//zabil('bigfoto')
// var g=i; while(idd('bigfot'+p+'_'+g)) g++;
// idie();
}


if($a=='options') { ADMA(); $img=RE('img');
otprav("zabil('bigfoto_opt',\"".njsn("
<i class='knop e_remove' title='Delete' onclick=\"if(confirm('Delete?')) majax('foto.php',{a:'fot_del',img:'".$img."'})\"></i>
&nbsp;<i class='knop e_rotate_left' title='270' onclick=\"if(confirm('Rotate 270?')) majax('foto.php',{a:'fot_rotate',degree:270,img:'".$img."'})\"></i>
&nbsp;<i class='knop e_rotate_right' title='90' onclick=\"if(confirm('Rotate 90?')) majax('foto.php',{a:'fot_rotate',degree:90,img:'".$img."'})\"></i>
&nbsp;<i class='knop e_blend' title='180' onclick=\"if(confirm('Rotate 180?')) majax('foto.php',{a:'fot_rotate',degree:180,img:'".$img."'})\"></i>
&nbsp;<i class='knop e_kontact_journal' title='Album Edit' onclick=\"majax('foto.php',{a:'album_edit',p:'".RE('p')."',num:num})\"></i>
")."\")");
}

if($a=='fot_del') { ADMA(); $hst=$httphost.accd(); $img=RE('img');
	if(strpos($img,$hst)!==0) idie("File `".h($img)."` not found!");
	$i=substr($img,strlen($hst)); $f=rpath($filehost.accd().$i);

	if(is_file($f)) {
                        $x=predir($f,'mic/'); if(is_file($x)) unlink($x);
                        $x=predir($f,'pre/'); if(is_file($x)) unlink($x);
                        unlink($f);
	}

	otprav(refix_image($img,$i,'[x]')."clean('bigfoto');salert(\"File ".hh($i)." deleted\",1000);");
}


if($a=='fot_rotate') { ADMA(); $hst=$httphost.accd(); $img=RE('img');
	if(strpos($img,$hst)!==0) idie("File `".h($img)."` not found!");
	$i=substr($img,strlen($hst)); $f=rpath($filehost.accd().$i);
	$degree=RE0('degree'); $fotoset=loadset();
	if(!is_file($f)) idie('Not found: '.h($i));
	if(!is_ras_image($img)) idie(h($i)." - is not image");
	rotatejpeg($f,$degree,$fotoset['Q']);
	$x=predir($f,'pre/'); if(is_file($x)) rotatejpeg($x,$degree,$fotoset['q']);

	otprav(
refix_image($img,$i,"src='".$img.'?'.filemtime($f)."'",0)
.refix_image($img,$i,"src='".predir($img,'pre/').'?'.filemtime($f)."'",2)
."salert(\"File ".hh($i)." rotated\",1000);");
}
//======================================

if($a=='createpreview') { ADMA();
	$id=preg_replace("/\.+/s",'.',RE('id'));
	$l=$filehost.accd().$id.'/';
		$m=array(); $p=glob($l.'*'); foreach($p as $x){ if(is_ras_image($x)) $m[basename($x)]=1; } // вот шо надо
		if(is_dir($l.'pre')) { $p=glob($l.'pre/*'); foreach($p as $x) unset($m[basename($x)]); }
	if(!sizeof($m)) idie('Error 04');

	$pre=$l.'pre'; if(!is_dir($pre)) { mkdir($pre); chmod($pre,0777); } // создать там папку для превьюшек

	$r=loadset(); // взять настройки
	foreach($m as $x=>$n) {
		obrajpeg($l.$x, $l.'pre/'.$x,$r['x'],$r['q']); // сделать превьюшку
		$s.="idd('".$id."/".$x."').src='".$wwwhost.accd().$id."/pre/".$x."';"; // заменить на экране превьюшками
	}
	otprav($s."clean('wait');");
}

//=================================== treeact ===================================================================
if($a=='delpre') { AD(); if(!$admin) idie('admin only!');
	$dir=$_REQUEST['dir']; if(strstr($dir,'..')) idie("Ошибка. Хакерствуем, бля?");

	if(!sizeof($_REQUEST['sel'])) { $sl=$filehost.$dir.'pre/'; $p=glob($sl.'*'); }
	else { $p=array(); foreach($_REQUEST['sel'] as $l=>$n) { if($n=='img') $p[]=$filehost.predir($l,'pre/'); } }

	$s=''; foreach($p as $x) if(is_ras_image($x)) { if(strstr($x,'..')) idie("Ошибка. Хакерствуем, бля?");
		if(is_file($x)){ $s.="idd('".predir_id($x,$filehost)."').className='knop e_foto';"; unlink($x); }
	}
	otprav($s."treeremove();clean('wait');");
}

if($a=='pre100x100') { AD(); if(!$admin) idie('admin only!');
	$rand=rand(0,100000);
	$fotoset=loadset();
	$dir=$_REQUEST['dir']; if(strstr($dir,'..')) idie("Ошибка. Хакерствуем, бля?");

	if(!sizeof($_REQUEST['sel'])) { $sl=$filehost.$dir.'pre/'; $p=glob($sl.'*'); }
	else { $p=array(); foreach($_REQUEST['sel'] as $l=>$n) { if($n=='img') $p[]=$filehost.predir($l,'pre/'); } }

	$s=''; foreach($p as $x) if(is_ras_image($x)) { if(strstr($x,'..')) idie("Ошибка. Хакерствуем, бля?");
		if(is_file($x)){
		$s.="var i='".predir_id($x,$filehost)."'; idd(i).src=idd(i).src.replace(/\\?.*?$/g,'')+'?".$rand."';";
		pre100x100($x,$fotoset['q']);
		}
	}
	otprav($s."treeremove();clean('wait');");
}

//=================================== rotate ===================================================================
if($a=='rotate') { ADMA(); $s=''; $degree=RE0('degree'); $rand=rand(0,100000); $r=loadset();
	foreach(RE('sel') as $l=>$n) { $f=rpath($filehost.accd().$l);
		if(!is_file($f)) idie('Not found: '.h($l));
		if(!is_ras_image($l)) idie(h($l).' - is not image!');
			rotatejpeg($f,$degree,$r['Q']);
			$x=predir($f,'pre/'); if(is_file($x)) rotatejpeg($x,$degree,$r['q']);
			$s.="idd('$l').src=idd('$l').src.replace(/\\?.*?$/g,'')+'?".$rand."';";
	}
	otprav($s."clean('wait');");
}
//=================================== treeact ===================================================================
if($a=='treeact') { ADMA();
	$l=preg_replace("/\.+/s",'.',RE('id'));

	if(stristr($l,'config.php')||stristr($l,'.htaccess')) AD(); // idie('Disable!');

if(is_ras_image($l)) {
	if(!is_file($filehost.accd().predir($l,'pre/'))) {
		otprav("if(confirm('Create previews for this folder?')) { helps('wait',\"<fieldset>workind... <img src=\"+www_design+\"img/ajax.gif></fieldset>\"); posdiv('wait',-1,-1); majax('foto.php',{a:'createpreview',id:'".h(dirname($l))."'});}");
	}
	otprav("var s='".$httphost.accd().$l."'; if(idd('bigfoto')&&idd('bigfotoimg').src==s) clean('bigfoto'); else bigfoto(s+'?".rand(0,10000)."')");
}


$file=rpath($filehost.$l);
if(!is_file($file)) $file=rpath($filehost."binoniq/userdata/".$l);
if(!is_file($file)) idie('File not found: '.$file);

else otprav("
starteditor=function(){majax('foto.php',{a:'edit_text',file:'".h($l)."'})};
setkey('esc','');
helpc('fotoset',\"<fieldset><legend><i class='knop e_kontact_journal'"
." onclick='starteditor()'></i> ".h($wwwhost.$l)."</legend><div title='Edit this (press `E`)'><table id='fotoset_c' style='width:\"+(getWinW()-100)+\"px'><tr><td>".njsn(str_replace('&nbsp;',' ',highlight_file($file,1)))."</td></tr></table></div></fieldset>\");
setkey(['E','У','у'],'',starteditor,false);
/*idd('fotoset_c').onclick=function(e){ e=e||window.event;clean('fotoset'); }*/
");
}
//=================================== editpanel ===================================================================
if($a=='edit_text') { ADMA(); $file=RE('file');
	$f=rpath($filehost.accd().$file);
	if(!is_file($f)) $f=rpath($filehost."binoniq/userdata/".$file);

	$fs=(is_file($f)?fileget($f):'');

otprav("
save_and_close=function(){save_no_close();clean('fotoset')};
save_no_close=function(){ if(idd('edit_text').value==idd('edit_text').defaultValue) return salert('".LL('save_not_need')."',500);
majax('foto.php',{a:'save_file',file:'".h($file)."',text:idd('edit_text').value});
idd('edit_text').defaultValue=idd('edit_text').value;
};
ohelpc('fotoset','Edit: ".h($wwwhost.$file)."',\"<table><tr><td>"
."<textarea style='width:\"+(getWinW()-100)+\"px;height:\"+(getWinH()-100)+\"px;' id='edit_text'>".h(njsn($fs))."</textarea>"
."<br><input title='".LL('ctrl+Enter')."' type='button' value='".LL('Save+exit')."' onclick='save_and_close()'> <input title='".LL('shift+Enter')."' type='button' value='".LL('Save')."' onclick='save_no_close()'>"
."</td></tr></table></fieldset>\");
idd('edit_text').focus();
setkey('esc','',function(e){ if(idd('edit_text').value==idd('edit_text').defaultValue || confirm('".LL('exit_no_save')."')) clean('fotoset'); },false,1);
setkey('enter','ctrl',save_and_close,false,1);
setkey('enter','shift',save_no_close,false,1);
setkey('tab','shift',function(){ti('edit_text','\\t{select}')},false,1);
");
}

if($a=='save_file'){ ADMA(); $s=RE('text'); $s=str_replace("\r",'',$s); $file=RE('file');
	$f=rpath($filehost.accd().$file);
	testdir(dirname($f));
	$js=''; if(!is_file($f)) $js.="treereload();";
	if($acc!=''&&!razre_ras($f)) idie("Name `".h($file)."` dedicated, sorry.<p>Use only: ".$usenames);
	fileput($f,$s);
	otprav($js."salert('".LL('saved')."',500)");
}

//=================================== editpanel ===================================================================
if($a=='upload') {

ADMA(); $num=RE0("num"); $s=''; $r=loadset();
	$ff_file=rpath($filehost.accd().$r['dir']).'/'; testdir($ff_file); 
	$ff_file_pre=rpath($filehost.accd().$r['dir']).'/pre/'; testdir($ff_file_pre);
	$ff_www=rpath($wwwhost.accd().$r['dir']).'/';
	$ff_www_pre=rpath($wwwhost.accd().$r['dir']).'/pre/';

// dier($_FILES);

	if(count($_FILES)>0) foreach($_FILES as $f) { if(gettype($f['name'])!='array') { foreach($f as $a=>$b) $f[$a]=array(0=>$b); }

foreach($f['name'] as $a=>$b) {
	if(is_uploaded_file($f["tmp_name"][$a])){ $l=h($f["name"][$a]);
		if(!is_ras_image($l)) idie("Это разве фотка?");
		if(substr($l,0,1)=='.') idie("Имя с точки?");
		if($l!=rpath($l)) { logi('hack.txt',"\n".date("Y-m-d H:i:s")." acc: `$acc` loadfoto name: `".h($l)."`"); idie("Чо, хакер, бля? Щас нахуй отсюда вылетишь."); }
		//--- фотоальбом Nokia ---
		if(preg_match("/^(\d\d)(\d\d)(\d{4})(\d+)\.jpg/si",$l,$m) && $m[3]."/".$m[2]==$r['dir']) $l=$m[1]."-".$m[4].".jpg";

		if(is_file($ff_file_pre.$l)) { $s.="<td><img onclick=\"foto('".$ff_www.$l."')\" src='".$fff_www_pre.$fl."'><div class=br><font color=red>".h($l)."</font></div></td>"; }
		else {
			if(!is_file($f["tmp_name"][$a])) idie("<font color=red>NOT FOUND: ".$f["tmp_name"][$a]."</font><br><pre>".nl2br(print_r($f,1))."<hr></pre>");
			obrajpeg($f["tmp_name"][$a],$ff_file.$l,$r['X'],$r['Q'],$r['logo']);
			obrajpeg($ff_file.$l,$ff_file_pre.$l,$r['x'],$r['q']);
			$s.="<td><img onclick=\"foto('".$ff_www.$l."')\" src='".$ff_www_pre.$l."'><div class=br>".h($l)."</div></td>";
			if($num) $ts.="\\n{_IMG: ".$l." _}";
	     	}
	}
}
}

	if($s=='') idie("Ошибка 2! ".nl2br(h(print_r($_FILES,1))));

	if($num && $ts!='') $ts="
		var v=idd('editor".$num."_Body'); if(v){
			v.value=v.value+'$ts';
			edit_polesend('Body',idd('editor".$num."_Body').value,".$num.");
		}
	";

otprav("
foto=function(f){
	helps('bigfoto',\"<img onclick=\\\"clean('winfoto')\\\" src='\"+f+\"'>\");
	idd('bigfoto').style.top = mouse_y+'px'; /*(getWinH()-imgy)/2+getScrollW()*/
	idd('bigfoto').style.left = (getWinW()-".$r['X'].")/2+getScrollH()+'px';
};

$ts

helps('foto_$hid',\"<table><tr align=center>".njsn($s)."</tr></table>\");
");

}

//==================================================================================================
/// убрать!
// function get_fotoset() { idie('EEEEEEEEEEEEEEERRRRRRRRRRRRRRRRRRRR get_fotoset!!!'); }

//===========================================================================================================
function albumdir($id) { global $acc,$filehost,$wwwhost,$www_design;
	$ud=accd();
	$dop=$filehost."binoniq/userdata/";

	$id=rtrim($id,'/').'/';
	$r=array();

	if($acc!='') { // добавим виртуальные файлы
		$templ=rpath($dop.$id).'/'; $a=glob($templ.'*'); $len2=strlen($dop);
		foreach($a as $n=>$l) { $r[substr($l,$len2)]=$l; }
	}

	// основные файлы
	$templ=rpath($filehost.$ud.$id).'/'; $a=glob($templ.'*'); $len=strlen($filehost.$ud);

	foreach($a as $n=>$l) $r[substr($l,$len)]=$l;

	$dirs=$imgs=$files=''; foreach($r as $n=>$l) { $bn=basename($l);
		$virt=($acc!=''&&substr($l,0,$len2)==$dop?1:0);

		if(is_dir($l)) { // все папки
			if($bn!='pre') $dirs.="['$n/','<div class=ll"
.($virt?' style="color:#88d"':'')
.">$bn</div>',1],";

// ."
		} else // все картинки
			if(is_ras_image($l)) {
			if(is_file(predir($l,'pre/'))) $pic=$wwwhost.$ud.predir($n,'pre/'); // pre preview
			else $pic=$www_design."e3/foto.png"; // no preview
			$imgs.="<img src=\"$pic\" onmouseover=\"treem(this)\" class=treef id=\"$n\"> ";

		} else { // все остальное

			 $files.="['$n','<div id=\"$n\" class=le"
.($virt?' style="color:#ccc"':'')
.">$bn</div>',0],";
		}

	}

//	salert('#$id',3000);

return "
	treeonLoaded([".$dirs.($imgs!=''?"['','".$imgs."',0],":'').rtrim($files,',')."],'$id');
	treeshowLoading(false,'$id');
";
}

function is_ras_image($l){ return in_array(explode_last('.',$l),array('jpg','jpeg','gif','JPG','JPEG','GIF','png','PNG')); }
function predir($l,$pre){ $bn=basename($l); $bd=dirname($l).'/'; if($bd=='./') $bd=''; return $bd.$pre.$bn; }
function predir_id($l,$fh){ return preg_replace("/^.{".strlen($fh)."}(.*?)\/[^\/]+(\/[^\/]+)$/si","$1$2",$l); }


/*
function textarea($s,$majax) {

return "save_and_close=function(){save_no_close();clean('fotoset')};
save_no_close=function(){ if(idd('edit_text').value==idd('edit_text').defaultValue) return salert('".LL('save_not_need')."',500);
majax('foto.php',{a:'save_file',file:'".h($file)."',text:idd('edit_text').value});
idd('edit_text').defaultValue=idd('edit_text').value;
};
ohelpc('fotoset','Edit: ".h($wwwhost.$file)."',\"<table><tr><td>"
."<textarea style='width:\"+(getWinW()-100)+\"px;height:\"+(getWinH()-100)+\"px;' id='edit_text'>".h(njsn($fs))."</textarea>"
."<br><input title='".LL('ctrl+Enter')."' type='button' value='".LL('Save+exit')."' onclick='save_and_close()'> <input title='".LL('shift+Enter')."' type='button' value='".LL('Save')."' onclick='save_no_close()'>"
."</td></tr></table></fieldset>\");
idd('edit_text').focus();
setkey('esc','',function(e){ if(idd('edit_text').value==idd('edit_text').defaultValue || confirm('".LL('exit_no_save')."')) clean('fotoset'); },false,1);
setkey('enter','ctrl',save_and_close,false,1);
setkey('enter','shift',save_no_close,false,1);
setkey('tab','shift',function(){ti('edit_text','\\t{select}')},false,1);
");
}
*/

idie("foto.php:$a");
?>