<?php // INSTALL

function gglob($x) { $x=glob($x); return(gettype($x)=='array'?$x:array()); }

function js_reload() { return "window.location='".$GLOBALS['httphost']."install?reboot='+Math.random();"; }
function jdie($s) { die("/*start_js_code*/".$s); }

include_once $GLOBALS['include_sys']."_files.php"; // операции с файлами

$GLOBALS["installname"]='install'; // имя этого файла

ini_set("display_errors","0"); ini_set("display_startup_errors","0");

if(!empty($_SERVER['QUERY_STRING'])&&!strstr($_SERVER['QUERY_STRING'],'=')) {
	$file=urldecode($_SERVER['QUERY_STRING']);
	$i=0; if(strstr($file,'|')) { $i=1; $file=trim($file,'|'); }

	if(is_vetofile($file)) die("ERROR: Veto");
	$fhost=realpath($GLOBALS['filehost'].$file);
	if(!file_exists($fhost)) die("ERROR: File not found: ".h($file));
	$s=obrabotal_file($file,file_get_contents($file));
	if($i) {  header("Content-Type: text/html; charset=".$GLOBALS['wwwcharset']); highlight_string($s); exit; } // на экран
	Exit_SendFILE($file,$s); // в файл
}

//--------------------------------------------------------------------------------
// ФУНКЦИИ УПДЕЙТОВ

$GLOBALS['selectjs']="
i_toggle_visible_d=1;
/*
i_ser
i_url
i_pack
*/

i_dir=function(e){ e=e.firstChild.innerHTML; return e=='/'?'':e };
i_div=function(e){ return e.lastChild.getElementsByTagName('DIV') };
i_tr=function(e){ return idd('i_selectfiles').getElementsByTagName('TR') };

i_fil=function(e){ return e.innerHTML.replace(/<[^>]*>/g,'') };

igp=function(e){ return i_dir(e.parentNode.parentNode.parentNode)+i_fil(e.parentNode); };

i_srav=function(e){ e=igp(e); mijax(i_ser+'ajax/midule.php',{mod:'INSTALL',a:'install_far_cmp',url:i_url,file:e}); };
i_view=function(e){ e=igp(e); mijax(i_ser+'ajax/midule.php',{mod:'INSTALL',a:'install_far_view',url:i_url,file:e}); };
i_miew=function(e){ e=igp(e); majax('module.php',{mod:'INSTALL',a:'edit_file',file:'".$GLOBALS['filehost']."'+e}); };

i_toggle_visible=function(){ for(var tr=i_tr(),i=0;i<tr.length;i++){ for(var p=i_div(tr[i]),z=g=p.length,j=0;j<g;j++){
			if(i_toggle_visible_d && !i_tst(p[j])) { p[j].style.display='none'; z--; }
			else p[j].style.display='block';
		} tr[i].style.display=(i_toggle_visible_d && !z)?'none':'block';
	}
i_toggle_visible_d=i_toggle_visible_d?0:1;
};

i_get_selected=function(){ for(var s='',tr=i_tr(),i=0;i<tr.length;i++){
	for(var dir=i_dir(tr[i]),p=i_div(tr[i]),j=0;j<p.length;j++){ if(i_tst(p[j])) s='\\n'+dir+p[j].innerHTML+s; }
 } return s;
};

i_submit=function(){ inst_MAS_DEL=[]; inst_MAS_UPD=[]; inst_MAS_NON=[];
  for(var c,f,s='',tr=i_tr(),i=0;i<tr.length;i++){ for(var dir=i_dir(tr[i]),p=i_div(tr[i]),j=0;j<p.length;j++){
			if(dir=='config.php:') {
				f=p[j].innerHTML.replace(/^\\\$([^\s\=]+)\s*=\s*(.*?)$/g,'$1=$2');
				var inp=p[j].getElementsByTagName('INPUT');
				if(inp.length==1) f=f.replace(/<input.*?>/gi,inp[0].value);
			} else f=i_fil(p[j]);

			/*if(dir=='config.php:') { f=f.replace(/^\\\\$/g,''); alert(f); }*/
		f=dir+f;
		if(i_tst(p[j])) {
			c=p[j].className.split(' ')[0];
			if(c=='iUPD'||c=='iADD') inst_MAS_UPD.push(f);
			else if(c=='iDEL') inst_MAS_DEL.push(f);
			else ohelpc('errError option','Error option','Error option: `'+c+'` / '+f);
	  	} else { f=f.replace(/^(config\\.php\\:[^=]+)\s*=.+$/g,'$1'); inst_MAS_NON.push(f); }
	}
 }
i_process();
};

i_selectall=function(){ for(var z=7,tr=i_tr(),i=0;i<tr.length;i++){
  for(var p=i_div(tr[i]),j=0;j<p.length;j++){ if(z==7) z=i_tst(p[j]); i_chan(p[j],z); }
} if(!z && !i_toggle_visible_d) i_toggle_visible();
};

i_find=function(id){
		if(id.indexOf('config.php:')>=0) id=id.replace(/^([^\s\=]+).*?$/g,'$1');
	for(var v,tr=i_tr(),i=0;i<tr.length;i++){ for(var dir=i_dir(tr[i]),p=i_div(tr[i]),j=0;j<p.length;j++){
		v=i_fil(p[j]);
			if(dir=='config.php:') v=v.replace(/^\\\$([^\s\=]+).*?$/g,'$1');
		if(id==dir+v) return p[j];
	}
} alert('not find: `'+id+'`'); return 0;
};

go_install=function(id){ var x,d,itit={iDEL:'del',iADD:'add new',iUPD:'update'};
	for(var tr=i_tr(),i=0;i<tr.length;i++){ d=tr[i].firstChild;
		d.onclick=function(){i_chand(this)}; d.setAttribute('title','Invert selected');
		for(var p=i_div(tr[i]),j=0;j<p.length;j++){
			if(itit[p[j].className]) p[j].setAttribute('title',itit[p[j].className]);
			p[j].onclick=function(e){e=e.target.tagName;if(e!='INPUT'&&e!='IMG'&&e!='I')i_chan(this,i_tst(this))};
		}
	}
i_toggle_visible(); posdiv(id,-1,-1);
};

i_chand=function(e){ for(var c=7,p=i_div(e.parentNode),i=0;i<p.length;i++) { if(c==7) c=i_tst(p[i]); i_chan(p[i],c); }};
i_tst=function(e){ var c=e.className.split(' '); if(c.length!=1) return (c[1]=='iOK'?true:false); return (c[0]=='iYES'?true:false); };
i_chan=function(e,i){ var c=e.className.split(' '); e.className=c.length!=1?c[0]+(i?' iSS':' iOK'):(i?'iNON':'iYES'); };

inst_MAS_UPD=[]; inst_MAS_DEL=[]; inst_MAS_NON=[];

i_process=function(){
	if(inst_MAS_NON.length) return majax('module.php',{mod:'INSTALL',a:'install_update_NON',d:inst_MAS_NON.join('\\n'),mode:'post',pack:i_pack});
	if(inst_MAS_DEL.length) return majax('module.php',{mod:'INSTALL',a:'install_update_DEL',file:inst_MAS_DEL[0],mode:'post'});
	if(inst_MAS_UPD.length) return majax('module.php',{mod:'INSTALL',a:'install_update_UPD',file:inst_MAS_UPD[0],mode:'post'});
	clean('install2');
};
";

function UPDATE_file($name,$temp) {

	$f=rpath($GLOBALS['filehost'].$name);
	if(is_vetofile($name)) return "Disabled file: ".h($name); // veto?
	backupfile($f); // забэкапить старый файл
	testdir(dirname($f)); // создать папки, если надо
        move_uploaded_file($temp,$f); filechmod($f);

	if(getras($f)=='css' && !empty($GLOBALS['www_design'])) {
		$s=file_get_contents($f);
		//---------------------------- если чо надо поменять -------------
		$s=preg_replace("/url\([\'\"]*[^\s\'\"\)]+\/design\/(.*?)[\'\"]*\)/si",'url('.$GLOBALS['www_design']."$1)",$s);
		$s=preg_replace("/\@charset\s[\'\"][^\s\'\"]+[\'\"]*/si",'@charset "'.$GLOBALS['wwwcharset'].'"',$s);
		$s=str_replace('{www_design}',$GLOBALS['www_design'],$s);
		//----------------------------------------------------------------
		fileput($f,$s);
	}

	return 1; //dirname($f)."|$f| name: $name data: ".strlen($data)." bytes";
}

function UPDATE_testkey($key){ // безопасность: проверка ключа инсталляции
	$f=$GLOBALS['filehost'].'hidden/binoniq/instlog/install_key.php'; if(!is_file($f)) return 0;
	$k=file_get_contents($f); unlink($f);
	$k=preg_replace("/^.+?\"([0-9a-z]{40})\".+?$/si","$1",$k);
	return ( empty($k) || $k != $key ? 0:1);
}

function UPDATE_select($rrr,$pack) { $r=unserialize($rrr); // return "<pre>".print_r($r,1);

	$s="<input type='button' onclick='i_submit(this)' value='INSTALL'>"
."&nbsp; &nbsp; <span class='ll r' onclick='i_toggle_visible();'>Hide/Show</span>"
."&nbsp; &nbsp; <span class='ll r' onclick='i_selectall()'>select</span>";
	$otstup=''; $lastdir='';

	// 1. рассортировать данные
	$Uconf=array(); // тут будут конфиговые переменные
	$Ulang=array(); // тут будут языковые переменные
	$Ufile=array(); // тут будут файлы
	foreach($r as $n=>$l) { list($file,$val)=explode(' ',$l,2); unset($r[$n]);
		if(strstr($file,':')) { // конфиг или язык
			list($tt,$ff)=explode(':',$file,2);
			if($tt=='config') { $Uconf[$ff]=$val; continue; }
			if($tt=='lang') { $Ulang[$ff]=$val; continue; }
		}
		$Ufile[$file]=$val;
	}

$obnovle=0;

//return "<pre>".print_r($Uconf,1)."</pre>";

// взять мою ветошь
$veto=unserialize(file_get_contents($GLOBALS['filehost']."hidden/binoniq/instlog/veto.my")); if(empty($veto)) $veto=array(); // на всякий случай

//=========================================================
	function vtoinput($t){ return $t[1]."<input type='text' value=\"".$t[2]."\" size='".(strlen($t[2])?strlen($t[2]):1)."'>".$t[3]; }

	$con=file_get_contents('config.php'); preg_match_all("/\n\s*".'\$'."([0-9a-z\_\-\[\'\"\]]+)\s*\=\s*([^\n]+)/si",$con,$m);
	$con=array(); foreach($m[1] as $i=>$n) $con[$n]=$m[2][$i]; // все наши
	$s.="<table><tr valign=top><td class='iDIR iOK'>config.php:</td><td class='iT'>"; // заголовок
	foreach($Uconf as $n=>$v) { if(isset($con[$n])) { unset($con[$n]); continue; }
			$v=h($v);
			$v=preg_replace_callback("/^([\'\"])([^\'\"]*)([\'\"];)/s","vtoinput",$v);
			$v=preg_replace_callback("/^([\'\"]*)(\d+)([\'\"]*;)/s","vtoinput",$v);
			$s.="<div class='iADD ".(in_array('config.php:'.$n,$veto)?'iSS':'iOK')."'>$".$n." = $v</div>"; // добавить
	} foreach($con as $n=>$l) $s.="<div class='iDEL ".(in_array('config.php:'.$n,$veto)?'iSS':'iOK')."'>$".$n."=".h($l)."</div>"; // удалить
	unset($con);
	$s.="</td></tr></table>";
//=========================================================
	// 3. Что с файлами?
	$DDDIR=array();

	$ruf=get_dfiles_r($pack);

	foreach($Ufile as $f=>$d) {
		$fdir=($d!='0 0'?dirname($f).'/':$f); if($fdir=='./') $fdir='/'; // имя папки
		if(!isset($DDDIR[$fdir])) $DDDIR[$fdir]=array(); // создать такую папку
		if($d=='0 0') continue;

		if(!isset($ruf[$f])) { // если такого у нас не было В СООТВЕТСТВУЮЩЕМ ПАКЕТЕ
			$fh=$GLOBALS['filehost'].$f;
			if(!is_file($fh)) $o='iADD'; // добавить
			else { // если есть файл
				list(,$d1)=explode(' ',$d,2);
				if(calcfile_md5($fh,getras($f))!=$d1) $o='iADD'; // добавить
				else $o='';
			}
		} else {
			list(,$d1)=explode(' ',$d,2); list(,$d2)=explode(' ',$ruf[$f],2); // не сравнивать время!
			if($d1==$d2) $o=''; // если тот же - ОК
			else $o='iUPD'; // U если не тот - обновить
			unset($ruf[$f]); // в любом случае удалить
		}
		if($o!='') $DDDIR[$fdir][basename($f)]=$o;
	}

	// собрать все удаляемые
	foreach($ruf as $f=>$d) { // и оставшиеся вне пакета поудалять
		$fdir=($d!='0 0'?dirname($f).'/':$f); if($fdir=='./') $fdir='/'; // имя папки
		if(!isset($DDDIR[$fdir])) $DDDIR[$fdir]=array(); // создать такую папку
		if($d=='0 0') continue;
		$DDDIR[$fdir][basename($f)]='iDEL';
	}

	// и напечатать

	foreach($DDDIR as $dir=>$val) if(sizeof($val)) {
		$s.="<table><tr valign=top><td class='iDIR iOK'>".h($dir)."</td><td class='iT'>";
		foreach($val as $n=>$o) {

	    if($o=='iUPD') $q="<i style='margin-right:15px' onclick='i_srav(this)' class='e_kontact_journal'></i>";
	elseif($o=='iADD') $q="<i style='margin-right:15px' onclick='i_view(this)' class='e_kontact_journal'></i>";
	elseif($o=='iDEL') $q="<i style='margin-right:15px' onclick='i_miew(this)' class='e_kontact_journal'></i>";
	else $q="($o)";

		$s.="<div class='".$o.' '.(in_array($dir.$n,$veto)?'iSS':'iOK')."'>".$q.$n."</div>"; $obnovle++;
	    }
	    $s.="</td></tr></table>";
	}

//=========================================================
	if(!$obnovle) return false;
	return "<div id='i_selectfiles'>$s</div>";
}


//----------------------------
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST
// POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST POST

if(sizeof($_POST)!=0 && !empty($_POST['post_act'])) { $a=$_POST['post_act'];

	ob_clean();

// die('alert(1);');

	if(!UPDATE_testkey($_POST['key'])) jdie("ohelpc('install2','post',\"Error key\");"); // безопасность: ключ инсталляции

if($a=='check_pack') { // выбор файлов для инсталляции
	$p=strtr($_POST['pack'],'+',' ');
	$s=UPDATE_select(urldecode($_POST['ara']),$p);
	if($s===false) jdie("salert('Nothing to do!',500);");
	jdie($GLOBALS['selectjs']."
i_ser='".$GLOBALS['httphost']."';
i_url='".urldecode($_POST['url'])."';
i_pack='$p';
ohelpc('install2','post',\"".njsn($s)."\");
go_install('install2');");
}

if($a=='update_file') { // выбор файлов для инсталляции
	$name=urldecode($_POST['file']);
	if(count($_FILES)!=1) jdie("alert('Error transfer - files: ".count($_FILES)); // файлов не пришло
	$s=''; foreach($_FILES as $f) {
		if(!is_uploaded_file($f["tmp_name"])) jdie("alert('Server ".$GLOBALS['httphost']." say: file not uploaded by POST-protocol! Check nginx/apache settings! `".h($f["name"])."` as `".h($f["tmp_name"])."`')"); // ошибка файла
		if($f['error']!=0) jdie("alert('Error upload: ".h($f["error"])."')"); // ошибка файла
		$s.=UPDATE_file($name,$f["tmp_name"]);
	}
	if($s!=1) jdie("ohelpc('file_install2','post',\"".njsn($s)."\");");
	jdie("var s=inst_MAS_UPD.shift(); s=i_find(s); if(s!==0) s.parentNode.removeChild(s); i_process();");
}


// idie('1');
	$a=$_POST;
//	idie($_POST['ara']);
	if(isset($a['ara'])) $a['ara']=unserialize(urldecode($a['ara']));
	if(count($_FILES)>0) {
		foreach($_FILES as $n=>$FILE) if(is_uploaded_file($FILE["tmp_name"])){
		$a["file: `$n`"]=$FILE;
		}
		// idie('Files: '.count($_FILES));
	}
	dier($a);

} // ВСЕ, ЗДЕСЬ КОНЧАЮТСЯ ЗАПРОСЫ POST!!!!








function INSTALL_ajax() { $a=RE('a');

global $IP,$admin_unics,$unic;

//======================= MIJAX от внешнего сервера - БЕЗ АДМИНСКОГО ЛОГИНА! =====================
// СЕРВЕР-МАТКА


if($a=='install_far_cmp') { // запрос POST - ЭТО ПРОИСХОДИТ УЖЕ на чужом сервере-матке
	$file=rpath(RE('file')); if(is_vetofile($file)) return "alert('Disabled file: ".h($file)."')"; // veto?
	$file_my=file_get_contents(RE('url').$GLOBALS["installname"].'?'.urlencode($file)); // скачать ЕГО файл
		if(substr($file_my,0,6)=='ERROR:') return "alert('".h($file_my)."')"; // veto?
	$file_ser=file_get_contents(rpath($GLOBALS['filehost'].$file));
	$file_ser=obrabotal_file($file,$file_ser);
		include_once $GLOBALS['include_sys']."_podsveti.php"; // процедура вывода окошка с одной правкой
//		$s=highlight_string(podsveti($file_my,$file_ser),1);
//		$s=podsveti(h($file_my),h($file_ser),"\n");
		$s=podsveti(h($file_my),h($file_ser));
	return "idie(\"".njsn(nl2br($s))."\");";
}

if($a=='install_far_view') { // запрос POST - ЭТО ПРОИСХОДИТ УЖЕ на чужом сервере-матке
	$file=rpath(RE('file')); if(is_vetofile($file)) return "alert('Disabled file: ".h($file)."')"; // veto?
	$file=RE('url').$GLOBALS["installname"].'?'.urlencode($file);

	$ras=getras($file);
	if(in_array($ras,array('png','gif','jpg','jpeg'))) $s="<img src='".h($file)."'>";
	else {
	    $s=file_get_contents($file); // скачать ЕГО файл
	    if(empty($s)) return "salert('empty file',800);";
	    $s=highlight_string($s,1);
	}
	return "ohelpc('view',\"".$file."\",\"".njsn($s)."\");";
}

if($a=='install_update_far') { // запрос POST - ЭТО ПРОИСХОДИТ УЖЕ на чужом сервере-матке
	$file=RE('file'); $fhost=rpath($GLOBALS['filehost'].$file);
	if(is_vetofile($file)) return "alert('Disabled file: ".h($file)."')"; // veto?
	if(empty($fhost) || !is_file($fhost)) return "alert('File not found: ".h($file)."')";
        return POST_file($fhost,RE('url').$GLOBALS["installname"],array('post_act'=>'update_file','file'=>$file,'key'=>RE('key')));
}

if($a=='install_far_check') { // запрос POST - ЭТО ПРОИСХОДИТ УЖЕ на чужом сервере-матке
	$pack=trim(RE('pack')); $r=get_pack_r($pack);
	return POST_file('',RE('url').$GLOBALS["installname"],array('post_act'=>'check_pack','url'=>$GLOBALS['httphost'],'pack'=>$pack,'key'=>RE('key'),'ara'=>serialize($r)));
}

// прислать по-бырому список доступных пакетов на этой станции - СЕРВЕР-МАТКА:
if($a=='install_get_packs') { // выслать список пакетов
	$packs=explode(' ',trim(RE('pack')));
	$dir=$GLOBALS['filehost'].'hidden/binoniq/instlog/'; $pacdir=$dir.'instpack/';
	$ft=$dir."all_md5.tmp"; $lasttime=(is_file($ft)?date("Y-m-d h:i:s",filemtime($ft)):"- no -");
	$s="<div class=r>Server: <b>".$GLOBALS['httphost']."</b>"
."<br>Admin: <a title='mail:&nbsp;".$GLOBALS['admin_mail']
.(isset($GLOBALS['admin_mobile'])?"<br>mob:&nbsp;".$GLOBALS['admin_mobile']:'')
."' href='mailto:".$GLOBALS['admin_mail']."'>".$GLOBALS['admin_name']."</a>"
."<br>Last update: <b>".$lasttime."</b></div><p>";
	foreach(get_my_packlist() as $l) $s.="<div><input class='cb' name=\"$l\" type='checkbox'".(in_array($l,$packs)?' checked':'').">$l</div>";
	return "zabil('epacks',\"".njsn($s)."\")";
}

AD();
//=========================================================================


// dier($_REQUEST,1);

if($a=='testmod') { // проверка модуля
	$m=RE('module');
		$mod=$GLOBALS['host_module']."install/".$m; include_once($mod);
		$r=installmod_init();
		if($r!=strtr($r,"\n<>",'---')) $s="
			clean('module__$m');
			zabil('mesto_otvet',vzyal('mesto_otvet')+\"<hr color='red'>".njsn($r)."\");
		";
		else {
			$s='';
			if($r!==false) $s.= "zabil('module__$m',\"<p><input type=button style='font-size:8px;' value='$r' onclick=\\\"dodo('$m',0,0,0)\\\">\");";
			else $s.="clean('module__$m');";
		}
	$s.="check_mod_do();";
	return $s;
}

if($a=='do') { // запуск модуля
	global $skip,$allwork,$time,$o,$delknopka,$script; $o=$delknopka=$script='';
	$time=RE0('time'); $skip=RE0('skip'); $allwork=RE0('allwork');
	$m=RE('module'); $mod=$GLOBALS['host_module']."install/".$m; include_once($mod);
	if(installmod_init()===false) return "clean('module__$m'); salert('not nessesary',2000);";
	if(!$allwork) { $allwork=(function_exists('installmod_allwork')?installmod_allwork():0); }
        $r=installmod_do();
                $script=(empty($script)?'':$script);
			if(intval($r)==0 and $r!==0) $o=$r;
	                $o=($o==''?'':"zabil('mesto_otvet',\"".njs($o)."\");");
        	        $delknopka=(isset($delknopka)?"clean('module__$m');":'');
                if($r===0) return $script."clean('percent');".$o.$delknopka;
                if(intval($r)==0) return $script."clean('percent');".$o.$delknopka;
                return $script.$o."
var z=(idd('percent')?0:1);
helps('percent',\"<fieldset><legend>$m &nbsp; &nbsp; \"+parseInt((100/$allwork)*$skip)+\"% <span class='timet'></span></legend><div style='width:\"+(getWinW()/2)+\"px;'><div style='width:\"+(((getWinW()/2)/$allwork)*$skip)+\"px;height:16px;background:red;'></div></div></fieldset>\");
if(z) posdiv('percent',-1,-1);
dodo('$m',$allwork,$time,$r);
";
}


//------------ для формы editfile ------------------
if($a=='edit_file'){ $file=RE('file'); return "save_and_close=function(){save_no_close();clean('editor')};
save_no_close=function(){ if(idd('edit_text').value==idd('edit_text').defaultValue) return salert('save_not_need',500);
majax('module.php',{mod:'INSTALL',a:'save_file',file:\"".njs($file)."\",text:idd('edit_text').value});
idd('edit_text').defaultValue=idd('edit_text').value;
};

ohelpc('editor','Edit: ".h($file)."',\"<table><tr><td>"
."<textarea style='width:\"+(getWinW()-100)+\"px;height:\"+(getWinH()-100)+\"px;' id='edit_text'>"
.h(njsn(file_get_contents($file)))."</textarea>"
."<br><input title='ctrl+Enter' type='button' value='Save+exit' onclick='save_and_close()'> <input title='shift+Enter' type='button' value='Save' onclick='save_no_close()'>"
."</td></tr></table>\");
idd('edit_text').focus();

setkey('esc','',function(e){ if(idd('edit_text').value==idd('edit_text').defaultValue || confirm('exit no save?')) clean('editor'); },false);
setkey('enter','ctrl',save_and_close,false);
setkey('enter','shift',save_no_close,false);
setkey('tab','shift',function(){ti('edit_text','\\t{select}')},false);
";
}
if($a=='save_file'){ fileput(RE('file'),RE('text')); return "salert('saved',500)"; }

//------------ login ------------------

if($a=='add_admins') { // добавить админов
	return "ohelpc('add_admins','Add Admins',\"".njsn("You must edit your config.php: add new admin unic to \$admin_unics='4,27,12345';"
."<br>Всё остальные дыры нахуй убраны, у нас есть одна система авторизации, всё привязано к ней. Хочешшь быть админом - логинься и прописывай свой номер в \$admin_unics в конфиге.")."\")";
}

if($a=='ch_users') { // сбросить пароль
return "ohelpc('ch_users','Change users',\"".njsn("
<form onsubmit=\"return send_this_form(this,'module.php',{mod:'INSTALL',a:'ch_users_'})\">
<div id='ch_users_unicline'>unic: <input title='User`s unic number like: 12345' type='text' size='10' name='unic' value=''></div>
<div id='ch_users_adminpass'>Admin's password: <input title='Are you really admin?<br>Enter you password here.' type='text' size='15' name='pass' value=''></div>
<div id='ch_users_o'></div>
<br><input type='submit' value='Go'></form>")."\")";
}

if($a=='ch_users_') { // сбросить пароль 2
	AD();
	$pass=c(RE('pass'));

	if($GLOBALS['IS']['password']=='' || $GLOBALS['IS']['password']!=md5($pass.$GLOBALS['hashlogin'])) {
		if($GLOBALS['IS']['password']=='') return "salert('No password in your card!',2000)";
		if($pass=='') return "salert('Where the password?!',2000)";
		sleep(5); return "clean('oldpass'); salert('Wrond password!',4000);";
	}

	$u=RE0('unic'); if(!$u) return "salert('Wrong unic number!',5000)";
	if(false==($p=ms("SELECT * FROM ".$GLOBALS['db_unic']." WHERE `id`='".$u."'",'_a',0))) return "salert('Unic ".$u." not found!',5000)";
	$p=$p[0];

if(($l=RE('newlogin'))!==false) { // выполняем

    if($l!=$p['login']) { // изменить логин
	if(0!=ms("SELECT COUNT(*) FROM ".$GLOBALS['db_unic']." WHERE `login`='".e($l)."'",'_l',0)) return "salert('ERROR! Login <b>".h($l)."</b> exist!',5000)";
	msq_update($GLOBALS['db_unic'],arae(array('login'=>$l)),"WHERE `id`='".$u."'");
	$p['login']=$l;
    }

    if(RE('cleanmailopenid')==1) { // сбросить openid
	msq_update($GLOBALS['db_unic'],arae(array('openid'=>'')),"WHERE `id`='".$u."'");
    }

    if(RE('cleanmail')==1) { // сбросить все емайл
	msq_update($GLOBALS['db_unic'],arae(array('mail'=>'','mailw'=>'')),"WHERE `id`='".$u."'");
    }

    if(RE('cleantel')==1) { // сбросить все tel
	msq_update($GLOBALS['db_unic'],arae(array('tel'=>'','telw'=>'')),"WHERE `id`='".$u."'");
    }

    $l=RE('newpassword'); if($l!='') { // изменить пароль
	if(''==$p['login']) return "salert('Login is empty. Set the login!',5000)";
	$i=md5($l.$GLOBALS['hashlogin']);
	msq_update($GLOBALS['db_unic'],arae(array('password'=>$i)),"WHERE `id`='".$u."'");
    }
return "salert('Done!',2000); clean('ch_users');";
}


$o="<input type='hidden' name='editunic' value='1'>";

if($p['img']!='') $o.="<img src='".h($p['img'])."' align=right>";

$o.="<div class=ll onclick=\"majax('login.php',{a:'getinfo',unic:'".$u."'})\">открыть</div>";

if($p['openid']!='') $o.="<p><input name='cleanopenid' onclick=\"var i=idd('openidrem'); i.style.display=(i.style.display=='none'?'inline':'none')\" type=checkbox> remove <a id='openidrem' href='".h($p['openid'])."'>".h($p['openid'])."</a>";
if($p['mail']!='') $o.="<p><input name='cleanmail' onclick=\"var i=idd('mailrem'); i.style.display=(i.style.display=='none'?'inline':'none')\" type=checkbox> remove <a id='mailrem' href='mailto:".h(get_workmail($p))."'>".h(get_workmail($p))."</a>";
if($p['tel']!='') $o.="<p><input name='cleantel' onclick=\"var i=idd('telrem'); i.style.display=(i.style.display=='none'?'inline':'none')\" type=checkbox> remove <b>".h($p['tel'])."</b>";

$o.="<p><input onclick=\"var i=idd('newpassword'); i.style.display=(i.style.display=='none'?'inline':'none')\" type=checkbox> new password <input id='newpassword' style='display:none' name='newpassword' type=text size=10 value=''>";
$o.="<p>login: <input type=text size=10 name='newlogin' value='".h($p['login'])."'>";

return "
zabil('ch_users_unicline',\"unic: <b>".$u."</b><input type='hidden' name='unic' value='".$u."'>\");
zabil('ch_users_adminpass',\"<input type='hidden' name='pass' value='".h($pass)."'>\");
zabil('ch_users_o',\"".njsn($o)."\");
";
}



 //$admin_unics='';



//------------ install ------------------
function fileget_save($file,$s='') {
	$a=explode('/',$file); $file=array_pop($a); $dir=implode('/',$a)."/";
	if(($o=file_get_contents($dir.$file))===false || $o==''&&$s!='') {
		testdir($dir); fileput($dir.$file,$s);
		if(file_get_contents($dir.$file)!=$s) idie("Cann't save: ".h($dir.$file)."<p>Check permissions.");
		return $s;
	} return $o;
}


$maj="majax('module.php',{mod:'INSTALL',a:";
$dir=$GLOBALS['filehost'].'hidden/binoniq/instlog/';


if($a=='install') { // инсталляция

	$serv=fileget_save($dir."servers.txt","http://lleo.me/blog Beta
http://lleo.me/dnevnik Stable
http://lleo.me Super Stable
http://binoniq.net Server Stable");

	$select_serv=fileget_save($dir."server.my","http://lleo.me/blog\n+basic");

// ЗЕНОНЗАЕБАЛ!!!!!11
if( ($l=str_replace('lleo.aha.ru','lleo.me',$serv)) != $serv) { fileput($dir."servers.txt",$l); $serv=$l; }
if( ($l=str_replace('lleo.aha.ru','lleo.me',$select_serv)) != $select_serv) { fileput($dir."server.my",$l); $select_serv=$l; }
// ЗЕНОНЗАЕБАЛ!!!!!11

	$select_serv=explode("\n",$select_serv);

	$o=array(); foreach(explode("\n",$serv) as $l) { $l=trim($l,"\n\r\t "); if($l=='') continue;
		list($ser,$ver)=explode(' ',$l,2); $o[$ser]=$ver.': '.$ser;
	}

	$s="server: ".selecto('servs',$select_serv[0],$o,"onchange=\"zabil('epacks','');"
."mijax(this.value+'/ajax/midule.php',{mod:'INSTALL',a:'install_get_packs',pack:'".implode(' ',get_my_packlist())."'});"
."\" id")."
<p><input type='button' value='Check Update' onclick='servselect(this)'>"

."<p><input type='button' id='expert_knop' onclick=\"majax('module.php',{mod:'INSTALL',a:'expert_options_panel'})\" alt='Other options<br>(expert mode)' value='Settings'>"


."<div id='epacks' style='margin: 20px;'>".implode(' ',get_my_packlist());


function migrate_veto($f_from,$f_to) {
    if(is_file($f_from)) {
	$mg=@file_get_contents($f_to)."\n\n# -------- adding /".$f_from."\n --------- ".@file_get_contents($f_from);
	$mg=explode("\n",$mg); $mr=array(); foreach($mg as $l) { $l=trim($l," \n\r\t"); if($l=='' || (!in_array($l,$mr) && !in_array($l."/",$mr))) $mr[]=$l; }
	$mg=implode("\n",$mr);
	file_put_contents($f_to,$mg);
	unlink($f_from);
    }
}
$ff=$GLOBALS['filehost']."update_veto_files.txt"; // touch($ff);
if(is_file($ff)) migrate_veto($ff,$GLOBALS['filehost']."hidden/binoniq/instlog/system_veto.txt");
$ff=$GLOBALS['filehost']."update_veto_my_files.txt"; if(is_file($ff)) unlink($ff);

unset($select_serv[0]);
foreach($select_serv as $l) { $w=substr($l,1);
	$s.="<div><input class='cb' name=\"$w\" type='checkbox'".($l[0]=='+'?' checked':'').">$w</div>";
}
$s.="</div>";
	$s.="<div id='mypan' style='position:relative;font-size: 14px; margin: 20px; padding: 20px;'>"
."<p>installed:<div id='mypacks' style='padding-left:50px;'>".get_my_pack()."</div></div>";

	return "
servselect=function(e){ var s='',e=getElementsByClass('cb');
	for(var i=0;i<e.length;i++) s+=' '+(e[i].checked?'+':'-')+e[i].name;
	if(s=='') { alert('Select packet'); return; }
	$maj'install_check',s:idd('servs').value,pack:s});
};
ohelpc('install','Select server',\"".njsn($s)."\");";
}

if($a=='expert_options_panel') { // панель опций

$s="<input type='button' value='Clean *.old' onclick=\"$maj'install_clean',s:idd('servs').value})\">
<input type='button' value='Back' onclick=\"$maj'install_back',s:idd('servs').value})\">
<input type='button' value='TEST' onclick=\"$maj'install_test',s:idd('servs').value})\">
<i class='e_filenew' title='Create my inctallpack!' onclick=\"majax('module.php',{mod:'INSTALL',a:'install_edit_pack',name:''})\" style='margin-left: 20px;'></i>
";

$xi=gglob($dir."*.txt"); foreach($xi as $l) { $l0=trim(basename($l));
    $c=$l0;
	if($c=='system_dir.txt') $c='Список файлов и папок, относящихся к движку на моем сервере';
	elseif($c=='system_veto.txt') $c='Файлы и папки движка на моем хостинге, которые я не хочу показывать тем, кто желает обновить свой движок с моего сайта';
	elseif($c=='veto_my.txt') $c='Список моих файлов движка, которые я запретил обновлять';
	$s.="<div alt='".$c."' class='l' onclick=\"majax('module.php',{mod:'INSTALL',a:'edit_file',file:'$l'})\">$l0</div>";
}

return "

zabil('mypan',\"<div style='width:100%;'>".njs($s)."</div>\"+vzyal('mypan'));
zabil('mypacks',\"".njs(get_my_pack(0))."\");
clean('expert_knop');
";
}


if($a=='install_edit_pack') { // форма редактирования пакета или создания нового (name='')

	$name=RE('name');

	$p=array(); if($name!='' && ($r=file($dir."instpack/".$name.".pack"))!==false) {
		foreach($r as $l) { $m=explode(' ',$l); $p[$m[0]]=array($m[1],$m[2]); }
	}

	//-----

	$s=''; $lastdir=''; foreach(get_dfiles() as $l) { list($file,$ftime,$fkey)=explode(' ',$l,3);
		$fhost=$GLOBALS['filehost'].$file; // физический файл
		$fname=basename($file); // его имя
		$fdir=($ftime.$fkey!='00'?dirname($file).'/':$file); if($fdir=='./') $fdir='/'; // имя папки
		if($fdir!=$lastdir) { $s.=($s==''?'':"</td></tr></table>")."<table><tr><td class='iDIR iOK'>$fdir</td><td>"; $lastdir=$fdir; }
			if($ftime.$fkey=='00') continue;
			$s.="<div class='".(isset($p[$file])?'iYES':'iNON')."'>".$fname."</div>";
	}

	$s="<div id='i_selectfiles'>$s".($s!=''?'</td></tr></table>':'')."</div>";
	//-----

$subm="<input type='button' value='Save' onclick='i_packsave()'>"
."&nbsp; &nbsp; <span class='ll' onclick=\"i_selectall()\">select</span>"
."&nbsp; &nbsp; <span class='ll' onclick=\"i_toggle_visible()\">show/hidden</span>"
."&nbsp; &nbsp; <i class='e_remove' title='Delete' onclick=\"packdel()\"></i>";

	return $GLOBALS['selectjs'].($name==''?"i_toggle_visible_d=0;":'')."

packdel=function(){ if(confirm('Delete pack `".$name.".pack`?')) majax('module.php',{mod:'INSTALL',a:'install_pack_del',name:idd('newpack_name').value}); };
i_packsave=function(){ majax('module.php',{mod:'INSTALL',a:'install_pack_save',s:i_get_selected(),name:idd('newpack_name').value}); };

ohelpc('pack','Edit pack: $name',\"".njsn(
($name==''?"<b>name: </b><input type='text' value='' size='10' maxlength='20' id='newpack_name'>":
"<input type='hidden' value='$name' id='newpack_name'>")
.$subm
."<div id='packs'><tt>$s</tt></div>$subm")."\"); go_install('pack');";
}


if($a=='install_pack_del') { // удаление пакета
	$name=RE('name'); unlink($dir."instpack/".$name.".pack");
	return "clean('pack'); zabil('mypacks',\"".njsn(get_my_pack(0))."\"); salert('Pack <b>$name</b> deleted!',1000);";
}

if($a=='install_pack_save') { // приемка создания нового пакета majax('module.php',{mod:'INSTALL',a:'install_pack_save',s:s,name:idd('newpack_name').value});
	$name=preg_replace("/[^0-9a-z\_\-\.]+/s",'',strtolower(RE('name'))); if(empty($name)) return "idd('newpack_name').value='$name'; idie('Name error! Only: 0-9a-z_-.');";
	$s=''; $r=get_dfiles_r(); foreach(explode("\n",trim(RE('s'),"\n")) as $l) {
		if(isset($r[$l])) $time_md5=$r[$l];
		else { $ras=getras($l); $time_md5=filemtime($l)." ".calcfile_md5($l,$ras); }
		$s.="$l $time_md5\n";
	}
	if($s=='') return "salert('Empty pack!',1000);";
	testdir($dir."instpack"); fileput($dir."instpack/".$name.".pack",$s);
	return "clean('pack'); zabil('mypacks',\"".njsn(get_my_pack(0))."\"); salert('Pack <b>$name</b> saved!',1000);";
}

// принять запрос на инсталляцию пакетов
if($a=='install_check') { // инсталляция - ЭТО ПРОИСХОДИТ ЕЩЕ НА СОБСТВЕННОМ СЕРВЕРЕ
	$ser=RE('s'); $pack=RE('pack');
	$e=explode(' ',$pack); $w=array(); foreach($e as $l){ if($l[0]=='+') $w[]=substr($l,1); }
	fileput($dir."server.my",$ser.strtr($pack,' ',"\n"));
	// делаем запрос на сервер-матку
	return "mijax('".$ser."/ajax/midule.php',{mod:'INSTALL',a:'install_far_check',url:'".$GLOBALS['httphost']."',pack:'".implode(' ',$w)."',key:'".createkey()."'})";
} // А ВОТ И ОН - СЕРВЕР-МАТКА

// подготовлено решение об инсталляции
if($a=='install_update_NON') { // NON - пометить файлы отмеченные как

	$f=$dir."veto.my";
	if(($s=file_get_contents($f))!==false) { $s=unserialize($s);
		$r=get_dfiles_r(RE('pack')); // взять все файлы для этих пакетов
		foreach($s as $n=>$l) { $l=trim($l); if(isset($r[$l])) unset($s[$n]); } // позбрасывать все для этих пакетов
	} else $s=array();
	$s=array_merge($s,explode("\n",RE('d'))); // добавить новые

	foreach($s as $n=>$l) {
$l=preg_replace("/^(config\.php\:[^=]+)\s*=.*$/s","$1",$l);
$l=trim($l,"\n\r\t");
$s[$n]=trim($l,"\n\r\t");
}

	fileput($f,serialize($s));

	return "for(var i in inst_MAS_NON){ var s=i_find(inst_MAS_NON[i]); if(s!==0) s.parentNode.removeChild(s); } inst_MAS_NON=[]; i_process();";
}

if($a=='install_update_DEL') { // DEL - удалить 1 файл
	$file=html_entity_decode(RE('file'));
		if(preg_match("/^(config\.php)\:([^\:\=]+)\=(.+?)$/s",$file,$m)) { config_del($m[2]);
		return "var s=inst_MAS_DEL.shift(); s=i_find(s); if(s!==0) s.parentNode.removeChild(s); i_process();";
		}
	$f=$GLOBALS['filehost'].$file;
	if(is_file($f)) { backupfile($f); unlink($f); } elseif(is_dir($f)) rmdir($f); else idie('Not found: '.h($f));
	return "var s=inst_MAS_DEL.shift(); s=i_find(s); if(s!==0) s.parentNode.removeChild(s); i_process();";
}

if($a=='install_update_UPD') { // UPD - обновить 1 файл
	$file=html_entity_decode(RE('file'));
		if(preg_match("/^(config\.php)\:([^\:\=]+)\=(.+?)$/s",$file,$m)) { config_add($m[2],$m[3]);
		return "var s=inst_MAS_UPD.shift(); s=i_find(s); if(s!==0) s.parentNode.removeChild(s); i_process();";
		}
	return "mijax('".getmatka()."/ajax/midule.php',{mod:'INSTALL',a:'install_update_far',url:'".$GLOBALS['httphost']."',key:'".createkey()."',file:'$file'})";
}


/***********/
if($a=='install_cmpfile') { // сравнить два файла PHP
	$file=html_entity_decode(RE('file'));
	return "mijax('".getmatka()."/ajax/midule.php',{mod:'INSTALL',a:'install_get_far_file',key:'".createkey()."',file:'$file'})";
}
//====================================================================


if($a=='install_test') {
	$r=getpack('basic',array());
	return("idie(\"path: ".implode('<br>',$r)."\");");
}


}
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//function sr($s){ return "<font color=red>$s</font>"; }
//function sg($s){ return "<font color=green>$s</font>"; }

// высчитать кс файла со всеми вычетами и проверками
function calcfile_md5($l,$ras) { $o=file_get_contents($l);
// obrafile
	if(in_array($ras,array('php','css','js'))) {
	    $o=preg_replace("/[\n\r]+\/\*\s*lleo\s*\*\/[^\n\r]+/si","",$o);
	    $o=preg_replace("/\/\*\s*lleo\:\s*\*\/.*?\/\*\s*\:lleo\s*\*\//si","",$o);
	}
	if($ras=='pack') $o=preg_replace("/((^|\n)[^ ])+.+?$/s","$1",$o);
	if($ras=='css') {
	        $o=preg_replace("/url\([\'\"]*[^\s\'\"\)]+[\'\"]*\)/si",'#',$o);
        	$o=preg_replace("/\@charset\s[\'\"][^\s\'\"]+[\'\"]*/si",'#',$o);
	        $o=str_replace('{www_design}','#',$o);
	}
	return md5($o);
}

// взять данные по пакету $pack (если ALL - то просканировать всё) и добавить к массиву $e
function getpack($pack,$e) { global $filehost; $save=0;
	$dir=$filehost."hidden/binoniq/instlog/instpack/"; testdir($dir); // проверить папку для кэшиков
	if($pack='ALL') $r=get_dfiles(); // подсчитать суммы
	else if(is_file($dir.$pack.".pack")) { $r=array();  $s=file($dir.$pack.".pack");
		foreach($s as $l) { list($name,$time,$md5)=explode(' ',trim($l));
			$l=$filehost.$name; if(!is_file($l)) { $save=1; continue; } // файл был удален
			$tim=filemtime($l); if($time!=$tim) { $save=1; $md5=calcfile_md5($l,getras($l)); } // исправить
			$r[]="$name $tim $md5";
		}
	}
	if($save) fileput($dir.$pack.".pack",implode("\n",$r)); // сохранить пакет, если были изменения
	foreach($r as $n=>$l) { if(in_array($l,$e)) unset($r[$n]); } // выкинуть дубли
	return array_merge($e,$r);
}




function is_vetofile($f) { global $vetomas; // если не загружен еще был $vetomas - загрузить
	if(!isset($vetomas)) { $vetomas=array(); $fv=$GLOBALS['filehost']."hidden/binoniq/instlog/system_veto.txt";
if(!file_exists($fv)) fileput($fv,"config.php
log/
tmp/
user/
hidden/
design/e3/pre/
design/e2/pre/
binoniq/ico/
fido/
"); // по умолчанию

if(($s=file($fv))!==false) foreach($s as $l) { $l=trim($l); if($l!='' && substr($l,0,1)!='#') $vetomas[]=$l; } }
	$f=trim(rpath($f),'/'); // почистить
		if(($ix=explode('/',$f)) && array_pop($ix)=='config.php.tmpl') return 0;
	foreach($vetomas as $l) { if(strtolower(substr($f,0,strlen($l)))==strtolower($l)) return 1; }
	return 0;
}

// ПОЛУЧИТЬ МАССИВ ПО ВСЕМ ФАЙЛАМ ДВИЖКА (которые разрешены в system_dir.txt)
function get_dfiles() { global $stop,$md5mas,$filehostn,$filehost,$allmd5change; $stop=10000;
	if(!isset($filehostn)) $filehostn=strlen($filehost);
	$dir=$GLOBALS['filehost']."hidden/binoniq/instlog/"; testdir($dir);
	// взять $md5mas - массив данных по всему движку
	$md5mas=array(); $allmd5change=1; if(($s=@file_get_contents($dir."all_md5.tmp"))!==false) { $allmd5change=0; $md5mas=unserialize($s); }
	// взять $all - массив данных по всему движку

	$all=array();
    if(!is_file($dir."system_dir.txt")) fileput($dir."system_dir.txt","binoniq
config.php.tmpl
index.php
minstall.php
ajax
css
design
fido
include_sys
js
module
site_mod
site_module
template");

	$s=file($dir."system_dir.txt"); foreach($s as $i=>$l) $s[$i]=trim($l);

foreach($s as $l) { $l=trim($l,"\n\r\t "); if($l!='' && substr($l,0,1)!='#') $all[]=$l; }
	// обработать по одному
	$r=array(); $k=0; $ko=''; foreach($all as $l) {
	    $ix=get_dfiles2($l);
	    if(gettype($ix) !== 'array') die("alert('".gettype($ix)."')");
	    $r=array_merge($r,$ix);
	}

	// подзаписать изменения, если были
	if($allmd5change) {
	    fileput($dir."all_md5.tmp",serialize($md5mas));
	    if(@file_get_contents($dir."all_md5.tmp")!=serialize($md5mas)) die("alert('File save deprecated: ".$dir."all_md5.tmp"." Change permissions!')");
	}

	return $r;
}

function get_dfiles2($files) { global $stop,$md5mas,$filehostn,$filehost,$allmd5change; if(!--$stop) die('stop error');
	$r=array(); $a=rpath($filehost.$files);
	    if(is_file($a)) $a=array($a);
	    elseif(is_dir($a)) {
		$l=$a; $a=gglob($a."/*");
		    if($a===false) idie("KOLOKOLL: files: `$files` glob: `$l/*` filehost.files: `".$filehost.$files."` is_file: ".intval(is_file($filehost.$files)));
		$h=$l."/.htaccess"; if(is_file($h)) $a[]=$h;
		if(!sizeof($a)) return array(c(substr($l,$filehostn))."/ 0 0"); // была пустая папка
	    } else return array();

	// сперва окучить файлы
	foreach($a as $n=>$l) { if(is_dir($l)) continue; $name=c(substr($l,$filehostn));
		$ras=getras($l); if(!is_vetofile($name) && $ras!='old' && $ras!='off' && substr($ras,0,6)!='old---') { $time=filemtime($l);
			if(isset($md5mas[$name]) && $md5mas[$name][0]==$time) $md5=$md5mas[$name][1]; // без изменений
			else { $md5=calcfile_md5($l,$ras); $md5mas[$name]=array($time,$md5); $allmd5change=1; }
			$r[]="$name $time $md5";
		}
	        unset($a[$n]);
	}
	// затем окучить папки
        foreach($a as $l) { if(!is_vetofile($l)) { $name=c(substr($l,$filehostn)); $r=array_merge($r,get_dfiles2($name)); } }
        return $r;
}

function get_dfiles_r($pack='') { // взять файлы в удобном формате
	$r=array(); foreach(explode(' ',$pack) as $p) {
		foreach(getpack($p,array()) as $l) { list($f,$time,$md5)=explode(' ',$l,3); $r[$f]=$time." ".$md5; }
	} return $r;
}
//=========================================================================

// РАБОТА С КОНФИГОМ

// добавить в конфиг
function config_add($name,$value){ if(($s=config_get())===false) return $s;
    $value=str_replace("\\'","'",$value);
    $value=str_replace("\\\"",'"',$value);
	$str="\$".$name.'='.((strstr($value,'"')
||strstr($value,"'")
||strstr($value,"array(")
||preg_match("/^\d+\;/s",$value))?$value:'"'.$value.'";');
	return config_put(preg_replace("/\n\s*\?>\s*$/s","\n".$str." // added ".date("Y-m-d")."\n?>",$s));
}
// удалить из конфига
function config_del($name){ if(($s=config_get())===false) return $s;
	return config_put(preg_replace("/\n(\s*[\$]".$name."\s*=[^\n]+)/s","\n// deleted ".date("Y-m-d").": $1",$s));
}
// изменить в конфиге (если не было - то добавить)
function config_change($name,$value){
	if(!(
		preg_match("/^\d+$/s",$value) // если это голые цифры
		or stristr($value,'array(') // или массив
		or strstr($value,'"') // или там уже есть кавычки
	)) $value='"'.$value.'"'; // если всего того нет, то дописать кавычки

if(($s=config_get())===false) return $s;
	if(!isset($GLOBALS[$name])) return config_add($name,$value);
	return config_put(preg_replace("/([\n\r]+\s*[\$]".$name."\s*=\s*)[\'\"][^\'\"]*[\'\"]\s*;([^\n]*)/si","\${1}".$value.";$2",$s));
}
function config_get(){ $f=config_name(); if(($s=file_get_contents($f))===false) return false; return $s; }
function config_put($s){ $f=config_name(); fileput($f,$s); }
function config_name(){ global $ajax,$filehost;
	if(isset($filehost)) return $filehost."config.php";
	if($ajax) return "../config.php";
	return "config.php";
}

// сгенерировать hash-строку
function rando($x,$y){ $s='';
//	$k=10; while((--$k)&&!strlen($s)){ if(($g=fopen("/dev/random","rb"))===false) break; $s=fgets($g); fclose($g); }
	if(!strlen($s)) { // /dev/random не сработал, вернуть традиционным образом
		list($t,)=explode(" ",microtime()); mt_srand($t+mt_rand()); $a=mt_rand(0,$y)+$t;
	} else { for($f=1,$a=$j=0;$j<min(strlen($s),3);$j++,$f*=256) $a+=ord($s[$j])*$f; }
	return $x+$a%($y-$x);
}

function hash_generate(){
	$A='ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz01234567890';
	for($s='',$i=0,$n=strlen($A);$i<128;$i++) $s.=$A[rando(0,$n)]; return $s;
}

// РАБОТА С ТАБЛИЦАМИ

// изменить поле в таблице
function msq_change_pole($table,$pole,$s){ if(msq_pole($table,$pole)!==false) msq("ALTER TABLE `".$table."` CHANGE `$pole` `$pole` $s"); }
// добавить поле таблицы
function msq_add_pole($table,$pole,$s){ if(msq_pole($table,$pole)===false) msq("ALTER TABLE `".$table."` ADD `".$pole."` ".$s." NOT NULL"); }
// удалить поле из таблицы
function msq_del_pole($table,$pole){ if(msq_pole($table,$pole)!==false) msq("ALTER TABLE `".$table."` DROP `".$pole."`"); }
// добавить ИНДЕКС в таблицу
function msq_add_index($table,$pole,$s){ if(msq_pole($table,$pole)!==false && !msq_index($table,$pole))
msq("ALTER TABLE `".$table."` ADD INDEX `".$pole."` ".$s);
// ALTER TABLE `site` ADD PRIMARY KEY(`name`)
}
// удалить ИНДЕКС из таблицы
function msq_del_index($table,$pole){ if(msq_index($table,$pole)) msq("ALTER TABLE `".$table."` DROP INDEX `".$pole."`"); }
// создать таблицу
function msq_add_table($s){ msq($s); }
// удалить таблицу
function msq_del_table($table){ if(msq_table($table)) msq("DROP TABLE `".$table."`"); }

//======================================================================================
// похвастаться успешной установкой
function admin_pohvast() { return "<center><div id=soobshi><input type=button value='Похвастаться успешной установкой' onclick=\"document.getElementById('soobshi').innerHTML = '<img src=http://lleo.me/blog/stat?link={httphost}>';\"></div></center>"; }

//======================================================================================
// логины админа
function admin_login() {

if(!$GLOBALS['admin']) return nl2br("You are not admin! Your unic = <b>".(1*$GLOBALS['unic'])."</b>\n"

.(0==1*$GLOBALS['unic']
?"\n1) Try to reload this page once for update unic-cookie."
:"2) Who are you ".$GLOBALS['IS']['name']."?"
    .($GLOBALS['IS']['login']==''?" Your login is blank":'')
    .($GLOBALS['IS']['password']==''?" Your password is blank":'')
." Do you have another login? Try to <span class=ll onclick=\"majax('login.php',{a:'do_login'})\" value=\"login\" type=\"button\">login</span>
\n3) May be you are owner and just installed engine? Edit <i>\$admin_unics='';</i> in <i>config.php</i> - add your id: <input type=text size=20 value=\"\$admin_unics='".($GLOBALS['unic']?$GLOBALS['unic']:'')."';\">"

    .($GLOBALS['IS']['loginlevel']<3?"\nAfter installation don't forget add information in your <span class='ll' onclick=\"majax('login.php',{a:'getinfo'})\">card</span>":'')

)

."\n\n<hr>\n\nНужен админский доступ. Твой unic=<b>".(1*$GLOBALS['unic'])."</b>"
.(0&&0==1*$GLOBALS['unic']
?"\n1) Для начала попробуй один раз обновить эту страницу чтобы получить нормальный номер куки."
:"\n2) Далее попробуем понять, кто ты, пользователь ".$GLOBALS['IS']['name']."?"
    .($GLOBALS['IS']['login']==''?" У тебя не установлен логин.":'')
    .($GLOBALS['IS']['password']==''?" У тебя не установлен пароль.":'')
." Может, у тебя есть другой, нормальный аккаунт? Попробуй <span class=ll onclick=\"majax('login.php',{a:'do_login'})\" value=\"login\" type=\"button\">залогиниться</span>
\n3) А еще бывает, что ты - владелец сервера и только что установил новый движок.
Тогда необходимо создать базы и отредактировать переменную <i>\$admin_unics='';</i> в файле <i>config.php</i>, вот так: <input type=text size=20 value=\"\$admin_unics='".($GLOBALS['unic']?$GLOBALS['unic']:'')."';\">
Туда, кстати, через запятую можно дописывать номера админов."
    .($GLOBALS['IS']['loginlevel']<3?"\nА потом, после успешной инсталляции, все-таки заполни свою админскую карточку, чтобы не терять доступ: <span class='ll' onclick=\"majax('login.php',{a:'getinfo'})\">card</span>":'')
));

    $au=(empty($GLOBALS['admin_unics'])?array():
	(strstr($GLOBALS['admin_unics'],',')?explode(',',$GLOBALS['admin_unics']):array($GLOBALS['admin_unics']))
    );

    $oo=array(); if(!empty($au)) {
	$P1=ms("SELECT * FROM ".$GLOBALS['db_unic']." WHERE `id` IN (".e(implode(',',$au)).")","_a",0);

	$P=array(); foreach($P1 as $x) $P[$x['id']]=$x;

	foreach($au as $i) {
		if(!isset($P[$i])) $oo[]="unknown:".$i;
		else { $is=get_ISi($P[$i],'{id}'); $oo[]="<span class=ll alt='Unic = ".$i."' onclick=\"majax('login.php',{a:'getinfo',unic:".$i."})\">".$is['imgicourl']."</span>"; }
	}

    }

return "<input type='button' value='INSTALL' onclick=\"majax('module.php',{mod:'INSTALL',a:'install'})\">
<input type='button' value='Change users' onclick=\"majax('module.php',{mod:'INSTALL',a:'ch_users'})\">
".($GLOBALS['unic']!=$P1[0]['id']?'':sizeof($oo)." admins: ".implode(',',$oo)) // для первого админа
.(!intval($GLOBALS['unic'])
    ?"<br><font color=red>ERROR: unic=0</font>"
    :(in_array($GLOBALS['unic'],$au)
	?''
	:"<br><font color=red>You need add your unic <b>".$GLOBALS['unic']."</b> in config.php:<br><dd>\$admin_unics=\"<b>".$GLOBALS['unic']."</b>"
	    .($GLOBALS['admin_unics']==''?'':"<b>,</b>".$GLOBALS['admin_unics'])
	    ."\";</font>"
    )
);

}

//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
//=========================================================================
function INSTALL($e) { $s=$im='';


if($GLOBALS['admin']) {

$GLOBALS['article']['template']='blank';


STYLES("mod","
.iDIR,.iYES,.iNON,.iDEL,.iUPD,.iADD { cursor:pointer; clear:left;float:left; }
.iNON {color: #aaa}
.iDEL {color: red}
.iYES,.iUPD {color: green}
.iADD {color: rgb(0,255,0)}
.iNON,.iSS {text-decoration:line-through}
.iNON:before,.iNON:after,.iSS:before,.iSS:after {content:' '}
.iYES,.iOK {text-decoration:none}

.iDIR {font-weight: bold; float:left; valign:top; }
.iT {float:left;margin-top:20pt;}

.p1 { color: #3F3F3F; text-decoration: line-through; background: #DFDFDF; } /* вычеркнутый */
.p2 { background: #FFD0C0; } /* вставленный */

");

        $upgrade=gglob($GLOBALS['host_module']."install/*.php");
        foreach($upgrade as $l) { $xi=explode('/',$l); $m=array_pop($xi);
		$im.="'$m',";
		$s.="<div class='mod' id='module__$m'>".$m."</div>";
	}

SCRIPTS("mod","
var install_modules_n=0;
function check_mod_do() { if(typeof install_modules[install_modules_n] == 'undefined') { install_modules_n=0; return; }
	var m=install_modules[install_modules_n++];
	zabil('module__'+m,'<img src='+www_design+'img/ajax.gif>'+vzyal('module__'+m));
	majax('module.php',{mod:'INSTALL',a:'testmod',module:m});
}
var install_modules=[".trim($im,',')."];

var timestart;
function dodo(module,allwork,time,skip,aram) {
	if(skip) {
		var timenow = new Date();
		var t=timenow.getTime()-timestart.getTime();
		var e=parseInt((t/skip)*allwork)-t;
		zabilc('timet',' &nbsp; &nbsp; &nbsp; осталось: '+pr_time(e)+' сек');
	} else { timestart = new Date(); }
	var ara={mod:'INSTALL',a:'do',module:module,allwork:allwork,time:time,skip:skip};
	if(typeof(aram)=='object') for(var i in aram) ara[i]=aram[i];
	majax('module.php',ara);
}

function pr_time(t) { var N=new Date(); N.setTime(t); var s=pr00(N.getUTCSeconds());
	if(N.getUTCMinutes()) s=pr00(N.getUTCMinutes())+':'+s;
	if(N.getUTCHours()) s=pr00(N.getUTCHours())+':'+s;
	return s;
} function pr00(n){return ((''+n).length<2?'0'+n:n)}


page_onstart.push('check_mod_do()');

");

}

return "<table width=100% style='border: 1px dotted red'>
<tr valign=top>
	<td>
		
		<div id='mesto_module'>$s</div>
	</td>
	<td width='100%'><div id='mesto_otvet'>".admin_login()."</div></td>
</tr></table>";

}

//==================================================================================================
function getconf($l){ $r=array(); $a=file($l); unset($a[0]); unset($a[sizeof($a)]);
	foreach($a as $l) { $l=trim($l);
		if($l=='' || preg_match("/^\s*(#|\/\/)/s",$l)) continue; // если это комментарий
		$per=preg_replace("/^\s*".'\$'."([a-z0-9\_\-\[\'\"\]]+).*?$/si","$1",$l); if($per==$l) continue;
		$r[]="config:$per ".preg_replace("/^\s*".'\$'."[a-z0-9\_\-\[\'\"\]]+\s*\=\s*(.*?)$/si","$1",$l);
	}
	return $r;
}

// обработка языка
function getlang($f){ $la=$GLOBALS['filehost'].'binoniq/lang/'; $nla=strlen($la); if(substr($f,0,$nla)!=$la) return array();
		$la=substr($f,$nla); $la=substr($la,0,strlen($la)-5);
		$r=array(); foreach(file($f) as $l) { $l=trim($l,"\n\r\t "); if(!strstr($l,"\t")) continue;
		list($per,$val)=explode("\t",$l,2);
		$r[]="lang:".$la.":$per ".trim($val,"\r\n\t ");
	}
	return $r;
}
//==================================================================================================

function get_my_pack($i=1) { $p=get_my_packlist(); if(!sizeof($p)) return 'not found'; $s='';
foreach($p as $w) $s.=($i?"<div>":"<div class='l' onclick=\"majax('module.php',{mod:'INSTALL',a:'install_edit_pack',name:'$w'})\">").h($w)."</div>";
return $s;
}

function get_my_packlist() { $pd=$GLOBALS['filehost'].'hidden/binoniq/instlog/instpack/'; if(!is_dir($pd)) return array();
	$p=gglob($pd."*.pack");
	if(empty($p)) { // migrate?
	    $p=gglob($GLOBALS['filehost']."binoniq/instlog/instpack/*.pack"); if(empty($p)) return array();
	    foreach($p as $n=>$l) rename($l,$pd.basename($l)); // migrate!
	    $p=gglob($pd."*.pack");
	}
	foreach($p as $n=>$l) $p[$n]=basename($l,'.pack'); return $p;
}

function createkey() { $key=sha1(hash_generate()); // сформировать ключ
	fileput($GLOBALS['filehost']."hidden/binoniq/instlog/install_key.php",'<?php die("Error 404"); $key="'.$key.'"; ?>');
	return $key;
}

function getmatka(){ $s=file($GLOBALS['filehost']."hidden/binoniq/instlog/server.my"); return trim($s[0]); } // текущий сервер

function get_pack_r($pack='') {
	$r=array(); foreach(explode(' ',$pack) as $l) $r=getpack($l,$r); // взять все указанные пакеты
	$o=$r; foreach($o as $n=>$l) { list($l,)=explode(' ',$l,2); $url=$GLOBALS['filehost'].$l;
		if($l=='config.php.tmpl') { $r=array_merge(getconf($url),$r); } // обработать конфиг
		if(getras($l)=='lang') { $r=array_merge(getlang($url),$r); unset($r[$n]); } // обработать язык, сам не слать
	} return $r;
}

function backupfile($f) { if(is_file($f) && substr(getras($f),0,6)!='old---') rename($f,$f.".old---".date("Y-m-d_h-i-s")); }

function obrabotal_file($file,$s) {
    if(in_array(getras($file),array('php','js','css'))) {
	    $s=preg_replace("/[\n\r]+\/\*\s*lleo\s*\*\/[^\n\r]+/si","",$s);
	    $s=preg_replace("/\/\*\s*lleo\:\s*\*\/.*?\/\*\s*\:lleo\s*\*\//si","",$s);
    }
    return $s;
}

?>