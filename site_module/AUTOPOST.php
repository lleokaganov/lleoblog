<?php

$GLOBALS['SLEEPsec']=3*60*1000; // 3 мин
$GLOBALS['$SLEEPbdi']=20*60; // 20 мин

function isok($x,$num) {
    if($x['OK']=='OK') return;
    $e="WARNING: "; if(substr($x['res'],0,strlen($e))==$e) { $s=substr($x['res'],strlen($e));
    otprav("var e=idd('autopostr_".$num."'); e.style.border='3px solid red'; zabil(e,\"".njsn(h($s))."\"); gopost(".(RE0('n')).");");
    }
    dier($x);
}

function AUTOPOST_ajax() { ADMA(); $a=RE('a'); $nnet=RE0('nnet'); $num=RE0('num');
    $cf=load_autopost_net($nnet);

    if($a=='del') {
	include_once $GLOBALS['include_sys']."protocol/".preg_replace("/[^a-z]/si",'',$cf['net']).".php";
	$fn=$cf['net']."_delete"; if(!function_exists($fn)) idie("Error protocol: ".h($cf['net'])." (".h($fn).")");
	$x=call_user_func($fn,$cf,$num);
	if($x['OK']=='OK') return "idd('c".$num."').style.display=''; zabil('autopostr_".$num."','$num');";
	dier($x);
    }

    if($a=='apost') {
    $link_id=ms("SELECT `id` FROM `socialmedias` WHERE `type`='post' AND `num`='".e($num)."' AND `net`='".e($cf['nett'])."'".ANDC(),'_l',0);
    include_once $GLOBALS['include_sys']."protocol/".preg_replace("/[^a-z]/si",'',$cf['net']).".php";
    if($link_id!=false) {
	$fn=$cf['net']."_link"; if(!function_exists($fn)) idie("Function not exist: `".h($fn)."`");
	$link=call_user_func($fn,$cf,$link_id);
	$color='grey';
    } else { // запостить
	// exit;
	$p=ms("SELECT * FROM `dnevnik_zapisi` WHERE `Access`='all' AND `num`='".e($num)."'","_1");
	$fn=$cf['net']."_posting"; if(!function_exists($fn)) idie("Function not exist: `".h($fn)."`");
	$x=call_user_func($fn,$cf,$p); isok($x,$num);
	$link=$x['link'];
	$color='green';
    }
	$s=$num;
	if(function_exists($cf['net']."_delete")) $s.=" <i alt='delete' class='e_remove' onclick=\"if(confirm('Delete?'))majax('module.php',{mod:'AUTOPOST',a:'del',num:'".h($num)."',nnet:'".$nnet."'})\"></i>";
	$s.=" <a href='".h($link)."'>".h($link)."</a>";

	return "var e=idd('autopostr_".$num."'); e.style.border='3px solid ".$color."'; idd('c".$num."').style.display='none'; zabil(e,\"".njsn($s)."\");"
	."setTimeout('gopost(".(RE0('n')+1).")',".$GLOBALS['SLEEPsec'].");"
;
    }
    idie("Error action: ".h($a));

}

function load_autopost_net($nnet){
    $r=explode("\n",load_autopost()); if(!isset($r[$nnet])) idie('Net not in list: '.h($nnet)); $r=explode(' ',$r[$nnet]);
    $cf=array('net'=>$r[0],'user'=>$r[2],'nett'=>$r[0].':'.$r[2],'template'=>$r[1]);
    if($r[0]=='lj') { $cf['pass']=$r[3]; $cf['flat']=(isset($r[3])?$r[3]:''); }
    elseif($r[0]=='facebook') { $cf['API1']=$r[3]; $cf['API2']=$r[4]; }
    elseif($r[0]=='yandex') { $cf['API1']=$r[3]; $cf['API2']=$r[4]; }
    elseif($r[0]=='twitter') { $cf['API1']=$r[3]; $cf['API2']=$r[4]; $cf['API3']=$r[5]; $cf['API4']=$r[6];}
    elseif($r[0]=='vk') $cf['API']=$r[4];
    elseif($r[0]=='instagramm') $cf['pass']=$r[3];
    elseif($r[0]=='fb') { $cf['domain']=(isset($r[4])?$r[4]:$cf['user']); $cf['pass']=$r[3]; }
//    elseif($r[0]=='fbnoid') { $cf['domain']=$r[4]; $cf['pass']=$r[3]; }
    return $cf;
}
function load_autopost(){
    $postmode=RE('postmode'); if(strstr($postmode,',')) list($postmode,)=explode(',',$postmode,2);
    $i='autopost'.(($i=$postmode)?'_'.$i:'');
    $s=loadsite($i);
    if(!empty($s)) return $s;
    idie("Коды для автопоста не установлены: создайте переменную `".h($i)."`");
}

/*
function selecto($n,$x,$a,$t='name') { if($x==='0'||intval($x)) $x=intval($x);
<------>$s="<select ".$t."='".$n."'>";
<------>foreach($a as $l=>$t) $s.="<option value='$l'".($x===$l?' selected':'').">".$t."</option>";
<------>return $s."</select>"; }

*/


function AUTOPOST($e) { ADMA(); // только админ

SCRIPTS("
var apostn={};

var gopost_n=false;
var gopost_ara=[];

sledilka=function(old,count,k){ k++;
    salert('bdim: '+old+' / '+count,800);
    if(gopost_n===false) { alert('ne bdim'); return; }
    if(old==gopost_n) { count++; if(count>".$GLOBALS['$SLEEPbdi'].") gopost(old); }
    else { old=gopost_n; count=0; }
    setTimeout('sledilka('+old+','+count+','+k+');',1000);
};

gopost_start=function(){ gopost_ara=[]; doclass('cb',function(e){if(e.checked!=true)return; gopost_ara.push(e.id);});
    if(gopost_ara.length==0) {
	doclass('cb',function(e){e.checked=true;gopost_ara.push(e.id)});
	//return salert('Nothing to do!<br>Select something please.',5000);
    }

    if(idd('net').value=='') {
	idd('net').value=12;
	// return salert('Select SocialNetwork!',5000);
    }

gopost_n=0; sledilka(0,0,0);
gopost(0); };

gopost=function(n){ if(!gopost_ara[n]) { gopost_n=false; return idie('done'); } var num=gopost_ara[n].replace(/^c/g,'');
    idd('autopostr_'+num).style.border='3px solid magenta';
    var e=idd('autopostr_'+num);
	e.style.border='0px';
	e.style.borderRadius='7px 7px 7px 7px';
	zabil(e,\"<img src='".$GLOBALS['www_design']."img/ajaxm.gif'> \"+vzyal(e));

    gopost_n=n; majax('module.php',{mod:'AUTOPOST',a:'apost',num:num,n:n,nmax:gopost_ara.length,nnet:idd('net').value});
};


");


$pp=ms("SELECT `Date`,`Header`,`Access`,`num` FROM `dnevnik_zapisi` ".WHERE("`DateDatetime`!=0 AND `Access`='all'").ANDC()." ORDER BY `Date`","_a"); // DESC

$o='';

$tmpl="<tr id='z{num}'><td><span id='autopostr_{num}'>{num}</span> <label><input class='cb' id='c{num}' type='checkbox'>{#Date}</label> - <a href='{link}'>{#Header}</a></td></tr>";

foreach($pp as $p) {
    if(c0($p['Header'])=='') $p['Header']='(...)';

    $o.=mpers($tmpl,array_merge($p,array('link'=>getlink($p['Date'],$GLOBALS['acn']))));
}





$ja=array(''=>'--- select ---'); foreach(explode("\n",load_autopost()) as $a=>$l) { if(($l=c0($l))=='') continue; $r=explode(' ',$l,4); if('#'==substr($r[0],0,1)) continue; // #строки пропускать
    $nett=$r[0].":".$r[2];
    $ja[$a]=$r[0].":".$r[2]; // mpers($t,array('num'=>$num,'n'=>$a,'net'=>$r[0],'template'=>$r[1],'user'=>$r[2],'data'=>$r[3]));
}


return "<div id='autopost'></div>
<center><table width=80% border=0 cellspacing=0 cellpadding=2>"
."<tr><td><span class=l onclick=\"doclass('cb',function(e){e.checked=true})\">select</span> / <span class=l onclick=\"doclass('cb',function(e){e.checked=false})\">unselect</span>"
." Post to: ".selecto("net",'---',$ja,"id='net' name")." <input type=button value='GO' onclick='gopost_start()'>"
."</td></tr>"
.$o."</table></center>";

}

?>