<?php // Протоколы внешних соцсетей

include "../config.php"; include $include_sys."_autorize.php";


// idie('ReMONT');



$PUBL=1; // флаг, что публикация готовится

function addtgs() { global $postmode,$access_warning;
    if(!isset($postmode)) $postmode=RE('postmode');
    if(!isset($access_warning)) $access_warning=RE('access_warning');
    return ",postmode:'".$GLOBALS['postmode']."',access_warning:'".$GLOBALS['access_warning']."'";
}

function load_autopost(){
    $postmode=RE('postmode'); if(strstr($postmode,',')) list($postmode,)=explode(',',$postmode,2);
    $i='autopost'.(($i=$postmode)?'_'.$i:'');
    $s=loadsite($i);
    if(!empty($s)) return $s;
    idie("Коды для автопоста не установлены: создайте переменную `".h($i)."`");
}

include_once $include_sys.'protocol/protocols.php';

$fbkey_name='tmp.fb-access_code-';
$vkkey_name='tmp.vk-access_code-';
$yakey_name='tmp.ya-access_code-';

// ======= возвратка от FB ============
if(isset($_GET['code'])) {
    include_once $include_sys.'protocol/_protocol_patchs.php';

//-------------------------------------------------
if($_SERVER["HTTP_HOST"]!=$MYHOST) { list($acc,$acc2,)=explode('.',$_SERVER["HTTP_HOST"],3);
    if(!isset($mnogouser)||$mnogouser!==1|| false==($p=ms("SELECT `acn`,`unic` FROM `jur` WHERE `acc`='".e($acc)."' ORDER BY (`unic`='$unic') DESC LIMIT 1","_1")))
    { $acn=0; $ADM=0; }
    else { $acn=$p['acn']; $ADM=($unic==$p['unic']?1:0); if($ADM) $ttl=0; }
} else { $acc=''; $acn=0; $ADM=$admin; }
//-------------------------------------------------

// ======== yandex
if(isset($_GET['state'])) {
list($n,$num)=explode('|',$_GET['state']);
    $r=explode("\n",load_autopost()); list($net,$tmpl,$user,$AppID,$AppSecret)=explode(' ',$r[$n]);
    // account hack protect
    if(loadsite($GLOBALS['yakey_name'].$user)!='asc') die('Request error:'.$GLOBALS['yakey_name'].$user);

$s=curlpost("https://oauth.yandex.ru/token",array(
'grant_type'=>'authorization_code',
'code'=>$_GET['code'],
'client_id'=>$AppID,
'client_secret'=>$AppSecret
)); $e=(array)json_decode1($s);
if(!isset($e['access_token'])) die("<script>window.opener.clean('yalogin_".$n."');</script>Yandex login error: ".h($s));
savesite($GLOBALS['yakey_name'].$user,$e['access_token']);
$kname='ya';

} else {
// ========= facebook

$n=RE0('n'); $num=RE0('num');
    $r=explode("\n",load_autopost()); list($net,$tmpl,$user,$AppID,$AppSecret)=explode(' ',$r[$n]);

    // account hack protect
    if(loadsite($GLOBALS['fbkey_name'].$user)!='asc') die('Request error 1');

    // $returnurl=$httphost."ajax/protocol.php?n=".$n."&num=".$num;
    $returnurl=acc_link($GLOBALS['acc'])."ajax/protocol.php?n=".$n."&num=".$num; // должен быть блять тот же зачем-то

    $a=file_get_contents_https("https://graph.facebook.com/oauth/access_token?client_id=".$AppID
    ."&client_secret=".$AppSecret
    ."&code=".urlencode(RE('code'))
    ."&redirect_uri=".urlencode($returnurl));
    $key=preg_replace("/^access_token=([^\&]+).*$/si","$1",$a); if($key==$a) die("<script>window.opener.clean('fblogin_".$n."');</script>Facebook login error: ".h($a));
    savesite($GLOBALS['fbkey_name'].$user,$key);
    $kname='fb';
}

die("<html>
<head><script type='text/javascript' language='JavaScript' src='/js/main.js'></script></head>
<body><font color=green>Success</font>

<script>
window.opener.majax('protocol.php',{a:'post',num:'".$num."',n:'".$n."'".addtgs()."});
window.opener.clean('".$kname."login_".$n."');
window.opener.winp_".$n.".close();
</script>
");

// alert('sho za /js/main.js in /ajax/protocol.php?!');
//	    window.parent.majax('protocol.php',{a:'post',num:'".$num."',n:'".$n."',".addtgs()."});
//	    window.parent.clean('fblogin');
}
// ===================================================
// ===================================================
// ===================================================
// ===================================================
// ===================================================
// ===================================================
// ===================================================
// ===================================================
// ===================================================
// ===================================================

ADH();

$num=RE0('num'); $a=RE('a');

// =============================================================================
if($a=='vkcode') { ADMA(); $n=RE('n'); $code=preg_replace("/^.+#code=/si","",RE('code'));
    $r=explode("\n",load_autopost()); list($net,$tmpl,$api_id,$secret_key)=explode(' ',$r[$n]);
    include_once $GLOBALS['include_sys'].'protocol/_protocol_patchs.php';

    $a=file_get_contents_https("https://oauth.vk.com/access_token?client_id=".$api_id
    ."&client_secret=".$secret_key
    ."&code=".urlencode($code)
    //."&redirect_uri=".urlencode($returnurl)
    );

    $a=(array)json_decode($a);
    if(isset($a['error'])) idie("ERROR: ".$a['error']." =&gt; ".$a['error_description']);

    $access_token=$a['access_token']; $user_id=$a['user_id'];
    $key=$user_id."|".$access_token;
    savesite($GLOBALS['vkkey_name'].$api_id,$key);
    otprav("
	majax('protocol.php',{a:'post',num:'".$num."',n:'".$n."'".addtgs()."});
	clean('vklogin_".$n."');
    ");
}

// =============================================================================

if($a=='del') { ADMA(); $n=RE0('n'); $nett=RE('nett'); $nscr=$n;
    $one=(RE0('one')==1?1:0);
    list($net,$user)=explode(':',$nett);
    $fn='del_'.$net; if(!function_exists($fn)) idie("error protocol: ".h($net)." (".h($fn).")");
    if(($l=call_user_func($fn,RE0('i'),$one))=='OK') otprav("clean('autoposte_".h(RE0('i'))."');");
    if(preg_replace("/[\d ]+/si","",$l)!='') idie(h($l));
    $js=''; foreach(explode(' ',$l) as $i) $js.="clean('autoposte_".h($i)."');";
    otprav($js);
}

// =============================================================================
if($a=='dellist') { ADMA(); $i=RE0('i'); // удалить только из базы, на сайте не трогать
    msq("DELETE FROM `socialmedias` WHERE `i`='".$i."'".ANDC()); // удалить запись
    otprav("clean('autoposte_".$i."');");
}
// =============================================================================

if($a=='list') { ADMA(); // `type`='post' AND
$r=ms("SELECT `i`,`type`,`net`,`id` FROM `socialmedias` WHERE `num`='".$num."'".ANDC(),"_a",0);
if(!sizeof($r)) otprav("salert('empty',200);");
$js="var s='';";

$t="s=s+\"<div id='autoposte_{#i}'>"
."{?type:
post:
instagramm_foto:
*:Error: {#type}&nbsp;
?}"
."{?ifdel:
1:<i class='l e_remove' title='delete post {#url}' onclick=\\\"if(confirm('Delete?')) majax('protocol.php',{a:'del',i:'{#i}',nett:'{nett}',num:'{#num}',n:'{#n}'".addtgs()."})\\\"></i>
*:
?}"
."&nbsp<i title='delete in list' class='l e_list-remove' onclick=\\\"if(confirm('Delete in list?')) majax('protocol.php',{a:'dellist',i:'{#i}',n:'{#n}'".addtgs()."})\\\"></i>"
."&nbsp;{link}"
."{?nnn:
1:
*:<div style='display:inline' id='autoposte_{#i}_'>&nbsp;&nbsp;<i title='more {#nnn} objects for this post' onclick=\\\"majax('protocol.php',{a:'listplus',nett:'{nett}',num:'{#num}',i:'{#i}'".addtgs()."})\\\" class='e_expand_plus'></i>{#nnn}</div>
?}"

."</div>\";";




$instagram1=1;
$g=array(); foreach($r as $n=>$p) {
    if($instagram1 && $p['type']=='instagramm_foto') { $instagram1=0; $p['type']='post'; }
    if($p['type']!='post') continue;
    $nnn=0; foreach($r as $b=>$i) if($i['net']==$p['net']) { $nnn++; if($b!=$n) unset($r[$b]); }
    if(isset($r[$n])) { $r[$n]['nnn']=$nnn; $g[]=$r[$n]; unset($r[$n]); }
}

$r=array_merge($g,$r);

$s=''; foreach($r as $n=>$p) { list($net,$user)=explode(':',$p['net']);
    $fn=$net.'_url'; if(!function_exists($fn)) idie("error protocol: ".h($net)." (".h($fn).") ".$p['i']);
    $url=call_user_func($fn,$p['id'],$user,$p['type']);
    $link=(strstr($url,'://')?"<a href='".h($url)."'>".h($url)."</a>":"<font color=grey>".$p['net']." unknown url: `".h($url)."`</font>"
.nl2br(h(print_r($p,1)))
);
    $js.=mpers($t,array(
//    'postmode'=>RE('postmode'),
    'type'=>$p['type'],
    'i'=>$p['i'],
    'nnn'=>(isset($p['nnn'])?$p['nnn']:0),
    'ifdel'=>intval(function_exists('del_'.$net)),
    'num'=>$num,'nett'=>$p['net'],'net'=>$net,'n'=>$n,'link'=>$link,'user'=>$user,'url'=>$p['id'],'led'=>(call_user_func($net.'_screen')==0?'ledgreen.png':'ledred.png')));
}

// foreach($r as $n=>$i) if($i['type']=='post' && in_array($i['net'],$posted)) unset($r[$n]);
// dier($posted);

// $posted[]=$p['net'];

// idie(nl2br(h($js)));

otprav($js."ohelpc('autopost','Autopost to Social Media',s);");
}

// =========================================================================

if($a=='listplus') { ADMA(); // type`='post' AND

// idie(123);

$nett=RE('nett'); $num=RE0('num');
$r=ms("SELECT `i`,`type`,`net`,`id`,`url` FROM `socialmedias` WHERE `net`='".e($nett)."' AND `num`='".$num."'".ANDC(),"_a",0);
if(!sizeof($r)) otprav("salert('empty',200);");
$js="var s='';";
$t="s=s+\"<div id='autoposte_{#i}'>"
."<i style='display:{ifdel}' title='delete post {#url}' onclick=\\\"if(confirm('Delete?')) majax('protocol.php',{a:'del',one:1,i:'{#i}',nett:'{nett}',num:'{#num}',n:'{#n}'".addtgs()."})\\\" class='e_remove'>"
."&nbsp<i title='delete in list' onclick=\\\"if(confirm('Delete in list?')) majax('protocol.php',{a:'dellist',i:'{#i}',n:'{#n}'".addtgs()."})\\\" class='e_list-remove'>"
."&nbsp;{type}"
."&nbsp;{link}"
."</div>\";";

$s=''; foreach($r as $n=>$p) { list($net,$user)=explode(':',$p['net']);
$fn=$net.'_url'; if(!function_exists($fn)) idie("error protocol: ".h($net)." (".h($fn).")");
$url=call_user_func($fn,$p['id'],$user,$p['type']);

    $link=(strstr($url,'://')
&&($net!='instagramm'||!strstr($url,'error'))
?"<a title='id:".h($p['id'])."<p>".h($p['url'])."<br>".h($url)."' href='".h($url)."'>"
.h($p['url']==''?$p['id']:$p['url'])
."</a>":"<font color=grey>unknown url: `".h($url)."`</font>");

$link=preg_replace("/(https*\:\/\/[^\|\:]+\.(jpg|png|gif))/si","$1 <img src=$1 width=100>",$link);

$js.=mpers($t,array(
'ifdel'=>(function_exists('del_'.$net)?'inline':'none'),
'i'=>$p['i'],
'type'=>$p['type'],
'src'=>$p['url'],
// 'postmode'=>RE('postmode'),
'num'=>$num,'nett'=>$p['net'],'net'=>$net,'n'=>$n,'link'=>$link,'user'=>$user,'url'=>$p['id'],'led'=>(call_user_func($net.'_screen')==0?'ledgreen.png':'ledred.png')));
}
// otprav("ohelpc('autopost','Autopost to Social Media',s);");
// e.style.border='1px dotted gray';
otprav($js."
var e=idd('autoposte_".h(RE0('i'))."_');
e.style.marginLeft='50px';
e.style.display='block';
zabil(e,s);");

}


// =============================================================================
if($a=='posts') { ADMA();
$js="var s=''; apostn={};";
$t="s=s+\"<div style='white-space:nowrap' id='autopostr_{n}'><img src='".$www_design."img/ajaxm.gif'> {net} <b>{user}</b></div>\";
apostn['{n}']=1; setTimeout(\"majax('protocol.php',{a:'post',num:'{num}',n:'{n}'".addtgs()."});\",50);";

foreach(explode("\n",load_autopost()) as $a=>$l) { if(($l=c0($l))=='') continue;
    $r=explode(' ',$l,4);
    if('#'==substr($r[0],0,1)) continue; // закомментированные #строки пропускать
    $js.=mpers($t,array('num'=>$num,'n'=>$a,'net'=>$r[0],'template'=>$r[1],'user'=>$r[2],'data'=>$r[3]));
}

otprav($js."ohelpc('autopost','Autopost to Social Media',s);");
}
// =============================================================================
if($a=='post') { ADMA(); $n=RE0('n'); $r=explode("\n",load_autopost()); $r=explode(' ',c0($r[$n]));

// otprav("zabil('autopostr_".$n."','<font color=red>not found (`".RE('postmode')."`)</font>');");

    if(!$num) otprav("salert(\"".LL('ljpost:err0')."\",1000)"); // Сперва надо заметку сохранить!
    if(($p=ms("SELECT `Date`,`Header`,`Body`,`opt`,`Access`,`visible` FROM `dnevnik_zapisi` WHERE `num`='".$num."'".ANDC(),"_1",0))===false) idie(LL('ljpost:notfound')); // Такой заметки нет!
//    if($p['Access']=='admin'||$p['visible']!=1) idie(LL('twitter:notpost')); // админские и невидимые заметки не постим

addtgs(); // получить остальные данные
if($access_warning=='off') $p['Access']='all';

    include_once $include_sys."_onetext.php";
$admin=$ADM=$podzamok=$unic=0; // сбросить ВСЕ авторизационные переменные чтоб не показывать подзамки!!!
$p=mkzopt($p); $s=prepare_Body($p); // if(RE('Body')!==false) $p['Body']=RE('Body'); if(RE('Header')!==false) $p['Header']=RE('Header');
$s=preg_replace("/^(<br>|<p><br>|<p>)/si",'',$s);

// dier($GLOBALS['article']['SOCIALMEDIA']."<hr>".$p['Body']);

    // применить шаблон
    $templ=$r[1]; if(($t=loadsite($templ))===false) otprav("zabil('autopostr_".$n."','<font color=red>Template not found: `".h($templ)."`</font>');");
    $r['conf']=parse_e_conf($t); $t=$r['conf']['body'];

    $net=$r[0];

// if($net!='lj' || $r[2]!='admin') otprav("zabil('autopostr_".$n."','<font color=green>OK: ".h($net)."</font>');");

    $txt=preg_replace("/(<(p|br|dd|center)[>\s])/si","\n$1",$s); $txt=c(strip_tags($txt)); $txt=str_replace("\n"," ",$txt);

if(preg_match("/\{text(\d+)\}/si",$t,$m)) { // lj-cut
    $c=intval($m[1]); if(!$c) idie('lj-cut error: 0');
    $e=explode("<",$s);
    $i=0; $ll=''; foreach($e as $ni=>$l) { if($ni) $l='<'.$l; $ll.=$l; $i+=strlen($l);
    if(stristr($l,'<img') || stristr($l,'<iframe') || stristr($l,'<object')) $i+=500;
    if($i>$c) break; }
    $txt=substr($s,0,strlen($ll))."\n<lj-cut>".substr($s,strlen($ll));
    $t=str_ireplace($m[0],'{txt}',$t);
}

    $datas=array_merge($r,array('num'=>$num,'n'=>$n,'net'=>$net,'template'=>$r[1],'user'=>$r[2],'password'=>$r[3],
'url'=>getlink($p['Date']),'Header'=>$p['Header'],'Date'=>$p['Date'],
	'templ'=>$t,
	'text'=>$s,
	'txt'=>$txt
	));
    $datas['text']=mpers($t,$datas);
    $datas['p']=$p;

    $fn='autopost_'.$net;
    $GLOBALS['autopost_n']=$n; $GLOBALS['autopost_num']=$num; // шоб было
    if(false!==($i=RE('captcha_sid'))) { $GLOBALS['captcha_sid']=$i; $GLOBALS['captcha_key']=RE('captcha_key'); }

    if(!function_exists($fn)) otprav("zabil('autopostr_".$n."','<font color=red>Protocol <b>`".h($net)."`</b> not found</font>');");

    $nett=$net.':'.$r[2];

if(isset($article['SOCIALMEDIA_NO'])) { $e=$article['SOCIALMEDIA_NO'];
    $off=0; $e=(strstr($e,',')?explode(',',$e):array($e)); foreach($e as $l) { if(dalida($l,$nett)){$off=1;break;} }
    if(isset($article['SOCIALMEDIA_ONLY'])) { $e=$article['SOCIALMEDIA_ONLY'];
	$e=(strstr($e,',')?explode(',',$e):array($e)); foreach($e as $l) { if(dalida($l,$nett)){$off=0;break;} }
    }
    if($off) otprav("zabil('autopostr_".$n."','off');");
}

// otprav("zabil('autopostr_".$n."','".$net."@".$article['SOCIALMEDIA_NO']."');");
//idie($net);
// idie($access_warning." - ".$p['Access']);

if($p['Access']!='all' && (!isset($r['conf']['all']) || $r['conf']['all']!='allow')) $s="<b>$nett</b> error: page access not 'All' (".RE('postmode').")";
else { $GLOBALS['r']=$datas; $s=call_user_func($fn,$datas); }

// if(!i) setTimeout(\"clean('autopost');\",500);

otprav("zabil('autopostr_".$n."',\"".njsn($s)."\"); posdiv('autopost',-1,-1); resize_me(1);"
.(strstr($s,'<font color=red>')?'':"apostn['".$n."']=0;"
// ."alert('apostn[".$n."]='+apostn['".$n."']); "
)."
var i=1; for(var l in apostn) i+=apostn[l];
");
}


function dalida($l,$nett) {
    if(!strstr($l,'*')) return $l==$nett;
    $l=str_replace('*','',$l); if($l=='') return 1;
    return stristr($nett,$l);
}

// =============================================================================
idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>