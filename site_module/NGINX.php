<?php // инструмент просмотра логов ошибок сервера (если они настроены в папке hidden/nginx/error.log)

function NGINX_ajax() { AD(); $a=RE('a');
//    idie('ajax');
    if($a=='load') { $z=RE0('z'); $file=$GLOBALS['filehost'].rpath(RE('file'));
	if(!is_file($file)) return "logstop=1;idie(\"LogFile not found: ".h($file)."\");";
	$size=filesize($file);

	if(!$z || $z>$size || ($size-$z)>10*1024) { $z=$size-min($size,1024); }

	$s=file_get_contents($file,false,NULL,$z,2048);
	$z+=strlen($s);

	$s=str_replace("\r",'',$s);

	$grep=RE('grep');
	$invert=RE0('invert');

	$s=explode("\n",$s);

	if(!empty($grep)) {

// idie($grep."/".$invert);

$r=array(); foreach($s as $l) { if($invert) { if(!strstr($l,$grep)) $r[]=$l; } else { if(strstr($l,$grep)) $r[]=$l; } } $s=$r; }

	krsort($s);

	return "lastsize=".$z.";var s=vzyal('buka'); if(s.length>100*1024) s=''; zabil('buka',\"".njsn(nl2br(h(implode("\n",$s))))."\"+s);";
//	return "salert('size: ".h($size)."',500);";
    }
}
//------------------------------------

function NGINX($e) {
$c=array_merge(array(
'accessfile'=>'hidden/nginx/access.log',
'errorfile'=>'hidden/nginx/error.log'
),parse_e_conf($e));

	$file=$GLOBALS['filehost'].rpath($c['errorfile']);
	if(!is_file) return "File not found: ".h($file);

SCRIPTS("
var lastsize=0;
var logstop=0;
var error_logfile=\"".h($c['errorfile'])."\";
var access_logfile=\"".h($c['accessfile'])."\";
var logfile=error_logfile;

newlog=function(){
    if(!logstop) majax('module.php',{mod:'NGINX',a:'load',z:lastsize,file:logfile,grep:idd('grep').value,invert:idd('invert').checked?1:0});
    setTimeout('newlog()',1000);
}; page_onstart.push(\"ajaxon=ajaxoff=function(){};nonav=1;newlog();\");
");

	return "<div>LogFile: ".h($file)." size: ".filesize($file)."</div>
<div>Grep: <input id='grep' type='text' size=50 value=''> invert: <input id='invert' type='checkbox'>"
." <input type='button' value='PAUSE' onclick=\"if(this.value=='PAUSE'){logstop=1;this.value='RUN';}else{logstop=0;this.value='PAUSE';}\">"
." <input type='button' value='clean' onclick=\"zabil('buka','');\">"
." <input type='button' value='error' onclick=\"zabil('buka','');lastsize=0;if(this.value=='error'){logfile=access_logfile;this.value='access';}else{logfile=error_logfile;this.value='error';}\">"
."</div>
<div id='buka' class=r></div>";

}

?>