<?php

/*
$GLOBALS['SLEEPsec']=3*60*1000; // 3 мин
$GLOBALS['$SLEEPbdi']=20*60; // 20 мин

function isok($x,$num) {
    if($x['OK']=='OK') return;
    $e="WARNING: "; if(substr($x['res'],0,strlen($e))==$e) { $s=substr($x['res'],strlen($e));
    otprav("var e=idd('autopostr_".$num."'); e.style.border='3px solid red'; zabil(e,\"".njsn(h($s))."\"); gopost(".(RE0('n')).");");
    }
    dier($x);
}
*/

function uidie($s) { otprav("clean('krut');zanstart=0;idie(\"".njsn($s)."\")"); }
function udier($r) { otprav("clean('krut');zanstart=0;idie(\"".njsn(nl2br(h(print_r($r,1))))."\")"); }


function FBGROUP_ajax() { ADMA(); $a=RE('a'); // $nnet=RE0('nnet'); $num=RE0('num');
	$user=RE('user');
	$pass=RE('pass');
	$text=RE('text');

	if($GLOBALS['unic']==4 && $user=='') { $cf=load_autopost_net(0,'fb:leokaganov'); $user=$cf['user']; $pass=$cf['pass']; }

	if(empty($user)) uidie('Empty USER (email or nickname)');
	if(empty($pass)) uidie('Empty PASSWORD');

	include_once $GLOBALS['include_sys']."protocol/fb.php";

    if($a=='clean') { $x=fb_run('CLEAN'); if($x['OK']!='OK') udier($x); otprav("clean('krut');zanstart=0;clean('mclean');"); }

    if($a=='leave') {
	$groupname=h(RE('groupname')); $grouplink=h(RE('grouplink')); preg_match("/\.com\/groups\/(\d+)/s",$grouplink,$id); $id=$id[1];
	$x=fb_run('GROUPLEAVE',$user,$pass,'',array('groupname'=>$groupname,'grouplink'=>$grouplink)); if($x['OK']!='OK') udier($x);
        otprav("clean('krut');zanstart=0;clean('gr".$id."');");
    }

    if($a=='message') {
	$groupname=h(RE('groupname')); $grouplink=h(RE('grouplink')); preg_match("/\.com\/groups\/(\d+)/s",$grouplink,$id); $id=$id[1];

	if(empty($text)) return "clean('krut');zanstart=0; if(idd('text')) salert('Message empty',600); else zabil('buka',\"<textarea id='text' style='margin:20px;width:100%;min-width:600px;min-height:300px;height:80%'>"
.njsn($GLOBALS['unic']==4?
"Я зашел на главную страницу Фейсбука, и Фейсбук мне написал, что советует стать участником трех групп, в том числе этой. Ну, раз сам Фейсбук советует, я конечно подписался. Но не понимаю пока, о чем здесь говорят. Фесйбук ведь зря не посоветует, верно?"
:"Меня подписали без моего ведома на эту группу. Хочу сказать, что я не остаюсь в группах, куда меня подписали без моего ведома. Отписываюсь."
)."</textarea>\"+vzyal('buka'));";

	$x=fb_run('GROUPPOST',$user,$pass,'',array('groupname'=>$groupname,'grouplink'=>$grouplink,'text'=>$text));
	$log=$x['log'];
	return "clean('krut');zanstart=0;"
.($GLOBALS['unic']==4?"zabil('log',\"".njsn(nl2br(h($log)))."\");":'')
."zabil('gr$id',vzyal('gr$id')+\" @<i class='e_ledgreen'></i>\");";
    }

    if($a=='list') {
	$x=fb_run('GROUPLIST',$user,$pass,''); if($x['OK']!='OK') {
	    udier($x);
	} $log=$x['log'];
	preg_match_all("/\nGROUP_FINDED\: ([^\s]+) \| ([^\n]+)/s",$log,$m);
	$tmpl="<div id='gr{#id}'>"
	    ."<input type='button' value='Отписаться'"
	    ." onclick=\"zanaves({a:'leave',groupname:'{#name}',grouplink:'{#link}'})\"> &nbsp; "
	    ."<input type='button' value='Зафигачить сообщение'"
	    ." onclick=\"zanaves({a:'message',groupname:'{#name}',grouplink:'{#link}',text:idd('text')?idd('text').value:''})\"> &nbsp; "
	    ."<a href='{#link}'>{#name}</a></div>";
	$o=''; if(sizeof($m[1]))foreach($m[1] as $i=>$l) { preg_match("/\.com\/groups\/(\d+)/s",$l,$id); $o.=mpers($tmpl,array('link'=>$l,'name'=>$m[2][$i],'id'=>$id[1])); }
	return "clean('krut');zanstart=0;fbuser='$user';fbpass='$pass';otkryl('mclean');doclass('forlog',function(e){clean(e)});zabil('buka',\"".njsn($o)."\");";
    }

    uidie("Error action: ".h($a));

}


function load_autopost_net($nnet,$nett=false){
    $r=explode("\n",load_autopost());
	if($nett!=false) { $nnet=-1; foreach($r as $nn=>$x) { if(empty($x))continue; $x=explode(' ',$x); if($nett==$x[0].':'.$x[2]) { $nnet=$nn; break; }  } if($nnet===-1) uidie("Not in list: ".h($nett)); }
    if(!isset($r[$nnet])) uidie('Net not in list: '.h($nnet)); $r=explode(' ',$r[$nnet]);
    $cf=array('net'=>$r[0],'user'=>$r[2],'nett'=>$r[0].':'.$r[2],'template'=>$r[1]);
    if($r[0]=='lj') { $cf['pass']=$r[3]; $cf['flat']=(isset($r[3])?$r[3]:''); }
    elseif($r[0]=='facebook') { $cf['API1']=$r[3]; $cf['API2']=$r[4]; }
    elseif($r[0]=='yandex') { $cf['API1']=$r[3]; $cf['API2']=$r[4]; }
    elseif($r[0]=='twitter') { $cf['API1']=$r[3]; $cf['API2']=$r[4]; $cf['API3']=$r[5]; $cf['API4']=$r[6];}
    elseif($r[0]=='vk') $cf['API']=$r[4];
    elseif($r[0]=='instagramm') $cf['pass']=$r[3];
    elseif($r[0]=='fb') { $cf['domain']=(isset($r[4])?$r[4]:$cf['user']); $cf['pass']=$r[3]; }
    return $cf;
}
function load_autopost(){
    $postmode=RE('postmode'); if(strstr($postmode,',')) list($postmode,)=explode(',',$postmode,2);
    $i='autopost'.(($i=$postmode)?'_'.$i:'');
    $s=loadsite($i);
    if(!empty($s)) return $s;
    uidie("Коды для автопоста не установлены: создайте переменную `".h($i)."`");
}



function FBGROUP($e) { ADMA(); // только админ


SCRIPTS("
var zanstart=0;
var fbuser,fbpass;
zanaves=function(ara) {
    if(zanstart) return salert('wait',500);

    mkdiv('krut',\"<img src=\"+www_design+\"img/ajax.gif width=300 height=300>\",'popup'); posdiv('krut',-1,-1);
/*    zanstart=1; */
    zabil('log','');
    ara.mod='FBGROUP'; ara.user=fbuser||idd('fbuser').value; ara.pass=fbpass||idd('fbpass').value;
    majax('module.php',ara);
};");



return "<div id='mmain'><center><table border=0 cellspacing=0 cellpadding=2>
<tr class='forlog'><td>Facebook логин (email или ник):</td><td><input type=text id='fbuser' value=''></td></tr>
<tr class='forlog'><td>Facebook пароль:</td><td><input type=password id='fbpass' value=''></td></tr>
<tr class='forlog'><td colspan=2><input type=submit value='Получить список групп' onclick=\"zanaves({a:'list'})\"></td></tr>
<tr><td colspan=2><div style='margin-top:30px;' id='buka'></div></td></tr>
</table></center>

<div style='margin-top:30px;font-size:9px;' id='log'></div>
<div id='mclean'><center><input type='button' value='Разлогиниться и убрать все следы' onclick=\"zanaves({a:'clean'})\"></center></div>
</div>";
}

?>