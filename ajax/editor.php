<?php // Редактор заметки

include "../config.php"; include $include_sys."_autorize.php";

//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################


if(RE('ver')==1) { // поддержка flat
    function erflat($s) { die("errmsg\n".$s."\nsuccess\nFAIL\n"); }
    header("Content-Type: text/plain; charset=utf-8");
    foreach($_REQUEST as $i=>$l) $_REQUEST[$i]=uw($l);

//    file_put_contents('___epa.txt',print_r($_REQUEST,1));

 if(empty($flatlogin)||empty($flatpassword)) erflat('FLAT not set');
    if(RE('user')!=$flatlogin || RE('password')!=$flatpassword) { sleep(3); erflat('wrong Login/Password'); }

 $mode=RE('mode');
 if($mode=='postevent' || $mode=='editevent') {
        $p=array();
	$p['Header']='DELETE-'.RE('subject');
        $p['Body']='###############'.RE('event');

	$Date=RE('year').'/'.RE('mon').'/'.RE('day').'_'.RE('hour').'_'.RE('min');

        $opt=array();
	foreach($_POST as $i=>$l) {
		$t='lleoopt_'; if(substr($i,0,strlen($t))==$t) $opt[substr($i,strlen($t))]=$l;
		$t='lleo_'; if(substr($i,0,strlen($t))==$t) $p[substr($i,strlen($t))]=$l;
        }
	$p['opt']=$opt;
	$tag=RE('prop_taglist');

 if($mode=='postevent') {
    // save new
    $d=date("Y/m/d"); // $d='2016/01/22';
    if(strstr($p['Date'],'_')) { list(,$i)=explode('_',$p['Date'],2); $d.='_'.$i; }
    $i=$d; $k=0; while($k<100 && 1==ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date`='".e($d)."'".ANDC(),"_l",0)) $d=$i.'_'.(++$k);
    $p['Date']=$d;
    $t=getmaketime($d);
        $p['DateUpdate']=time();
        $p['DateDate']=$t[0];
        $p['DateDatetime']=$t[1];
    	msq_add('dnevnik_zapisi',arae($p)); if($msqe) erflat('MySQL: '.$msqe);
    	$num=msq_id();
	if($tag!='') tags_save($tag); // и тэги дописать
    die("itemid\n".$num."\n"."url\n".getlink($p['Date'])."\n"."success\nOK\n");
 }
 if($mode=='editevent') {
    if($p['Header'].$p['Body']=='') { // delete
	if(!zametka_del($num)) erflat('Delete Unknown Error');
	die("success\nOK\n");
    }
    if(!($num=RE0('itemid'))) erflat('itemid=0');
    msq_update('dnevnik_zapisi',arae($p),"WHERE `num`='".$num."'".ANDC()); if($msqe) erflat('MySQL: '.$msqe);
    die("itemid\n".$num."\n"."url\n".getlink($p['Date'])."\n"."success\nOK\n");
 }
 }
}


// onclick="if(confirm('Delete?')) majax('protocol.php',{a:'del',i:'7548',nett:'lj:admin',num:'3225',n:'0',postmode:'',access_warning:'')"

//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################
//###################################################################################################################################

include_once $include_sys."_onetext.php";

$autosave_count = 200; // 128; // через сколько нажатий кнопки автозапись

$num=RE0('num'); $idhelp='editor'.$num; $a=RE('a'); ADH();

if($a=='KES') {
    if(($s=ms("SELECT `Body` FROM `dnevnik_zapisi` WHERE `num`='".$num."'".ANDC(),"_l"))===false) idie('Num not found'); // Такой заметки нет!
    if(!strstr($s,'{_KES:_}')) idie("Not allower to view"); // А там не было тэга
    $s=highlight_string($s,true);

    $s=preg_replace("/(\{\_[a-z0-9\-\_]+)\_\}/si","<b><font color=green>$1_<span></span>}</font></b>",$s);

    $s=preg_replace("/\{\_[a-z0-9\-\_]+\:/si","<font color=green><b>$0</b>",$s);
    $s=str_replace("_}","<b>_}</b></font>",$s);
    otprav("helpc('KES',\"".njs($s)."\");");
}














// ========================================== find img ==================================================================
if($a=='findimg_form') { ADMA();
    if(!isset($GLOBALS['findimg_microsoft_key'])) idie("Ошибка: движок не зарегистрирован в АПИ поискового сайта microsoft!
<br>В config.php должен быть прописан ключ вида \$findimg_microsoft_key='645782364586256342';
<br>Полезные ссылки:
<br><a href='https://msdn.microsoft.com/en-us/library/mt712546.aspx'>https://msdn.microsoft.com/en-us/library/mt712546.aspx</a>
<br><a href='https://www.microsoft.com/cognitive-services/en-US/subscriptions'>https://www.microsoft.com/cognitive-services/en-US/subscriptions</a>");

$s="<center>Ключевые слова: <input id='txtimg' size=30 type='text' onchange=\"idd('goimg').click();\"> &nbsp; <input id='goimg' type=button value='Искать' onclick=\"majax('editor.php',{a:'findimg',q:idd('txtimg').value})\"></center>"
."<div id='findim'></div><p>";

        $dop=rtrim($filehost."binoniq/".accd(),'/');
otprav("ohelpc('findimg','подбор картинок',\"".njsn($s)."\");idd('txtimg').focus();");

}

if($a=='findimg') { ADMA();
    $q=RE('q');
// API: https://msdn.microsoft.com/en-us/library/mt712546.aspx
// API: https://www.microsoft.com/cognitive-services/en-US/subscriptions
    $KEY=$GLOBALS['findimg_microsoft_key'];
    $s='';

    $url="https://api.cognitive.microsoft.com/bing/v5.0/images/search?q=".urlencode(wu($q)); // ."&license=Public&color=White&imageType=Clipart"
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array("Ocp-Apim-Subscription-Key: ".$KEY));
    $o=curl_exec($ch); curl_close($ch);

    $p=(array)json_decode($o); 

// dier($p);

/*
           [0] => stdClass Object
                (
                    [name] => Rest вЂє Р‘Р»РѕРі вЂє РЎР»Р°Р±РѕСѓРјРёРµ Рё РѕС‚РІР°РіР°
                    [webSearchUrl] => https://www.bing.com/cr?IG=CA9A8DD339014D49BA352B5FF7D8672B&CID=2F3C073DF
                    [thumbnailUrl] => https://tse3.mm.bing.net/th?id=OIP.QageDUfc4DNfak3XlHIhYwEsDF&pid=Api
                    [datePublished] => 2016-11-14T01:39:00
                    [contentUrl] => https://www.bing.com/cr?IG=CA9A8DD339014D49BA352B5FF7D8672B&CID=2F3C073DF8A
                    [hostPageUrl] => https://www.bing.com/cr?IG=CA9A8DD339014D49BA352B5FF7D8672B&CID=2F3C073DF8
                    [contentSize] => 259956 B
                    [encodingFormat] => jpeg
                    [hostPageDisplayUrl] => https://www.drive2.ru/b/1208432
                    [width] => 838
                    [height] => 552
                    [thumbnail] => stdClass Object
                        (
                            [width] => 300
                            [height] => 197
                        )

                    [imageInsightsToken] => ccid_QageDUfc*mid_AD82A8D418E6040E13582E8116D81C18CD29F0DD*simid_608
                    [imageId] => AD82A8D418E6040E13582E8116D81C18CD29F0DD
                    [accentColor] => A53C26
                )
*/


    if(!isset($p['value']) || empty($p['value'])) $s="<center>Images not found for `".h($q)."`</center>";
    else foreach($p['value'] as $i=>$e) { $e=(array)$e;

    $url=h(urldecode(preg_replace("/^.+\&r=([^\&]+).+$/si","$1",$e['contentUrl'])));
    $link=h(urldecode(preg_replace("/^.+\&r=([^\&]+).+$/si","$1",$e['hostPageUrl'])));
//    dier($e,h($url));


	$s.="<div style='display:inline-block;padding:10px;'><img onload=\"center('findimg')\" src='".h($e['thumbnailUrl'])."'"
// ." alt=\"<img src='".h($e['contentUrl'])."'>\""
." onclick=\"dofot('".$url."')\""
."><div align=center class=br><a target=_blank href='".$link."'>".h($e['width']."x".$e['height'])."</a>"
// ." ".h(uw($e['name']))
."</div>"
// ." ".h(uw($e['name']))
."</div>"
;
    }
    otprav("
dofot=function(url){ var s='\\n\\n{_IMG: '+h(url)+' _}\\n\\n';
if(mnogouser) majax('editor.php',{a:'xclipboard',text:s});
else { l_save('clipboard_text',s); l_save('clipboard_mode','plain'); }


clean('findimg'); };
zabil('findim',\"".njsn($s)."\");idd('txtimg').focus();");
}

if($a=='xclipboard') { ADMA();
    $s=htmlspecialchars_decode(RE('text'));
    $s=str_replace('{myfiles}',$wwwhost."userdata/".$acc,$s);
    otprav("var s=\"".njsn($s)."\";l_save('clipboard_text',s); l_save('clipboard_mode','plain'); clean(IMBLOAD_MYID+'_r');");
}

//=================================== load ===================================================================
// <div id='buka' class='ll' onclick="majax('editor.php',{a:'load',id:this.id,Date:'2011/11/02'})">click</div>
if($a=='load') {
	if($num) { $x='num'; $v=$num; } else { $x='Date'; $v=e(RE('Date')); }
	if(strstr($v,'#')) list($v,$aname)=explode('#',$v,2);

if(($p=ms("SELECT `Date`,`Body`,`Header`,`opt` FROM `dnevnik_zapisi` ".WHERE("`$x`='$v'"),"_1"))===false) idie(LL('ljpost:notfound').h(" ".$num." ".RE('Date'))); // Такой заметки нет!
	$p=mkzopt($p);

if(!empty($aname)) {
	$quick=RE0('quick');
	if(!$quick) $p['Body']=prepare_Body($p); // если quick=0 (по умолчанию) - то обработать до

	/// БЛЯТЬ ХУЙ ЗНАЕТ ПОЧЕМУ НЕ РАБОТАЕТ ПЕРВЫЙ ВАРИАНТ СТРОКИ:
//	if(preg_match("/<a\s+name=[\'\"]*".preg_quote($aname)."[\'\"]*>(.*?)(<a\s+name=|$)/si",$s,$m)) $s=$m[1];
	if(preg_match("/<a\s+name=[\'\"]*".preg_quote($aname)."[\'\"]*>(.*?)<a\s+name=/si",$p['Body'].'<a name=',$m))
		$p['Body']=$m[1]; else idie('Error reading #'.h($aname));

	if($quick) $p['Body']=prepare_Body($p); // если quick=1 - обработать после
	
} else $p['Body']=prepare_Body($p);

//	idie(h($s));
	otprav("
zabil('".RE('id')."',\"".njs($p['Body'])."\");
".(($idhead=RE('idhead'))!=''?"zabil('".RE('idhead')."',\"".$p['Header']."\");":'')."
");
}
//=================================== ljpost ===================================================================
// - - - - -
if($a=='ljpost') { ADMA();
	if(empty($admin_ljuser) or empty($admin_ljpass)) idie(LL('ljpost:notlogpas'));
	if(!$num) otprav("salert(\"".LL('ljpost:err0')."\",1000)"); // Сперва надо заметку сохранить!
	if(($p=ms("SELECT `Date`,`Body`,`Header`,`opt`,`Access` FROM `dnevnik_zapisi` WHERE `num`='$num'","_1",0))===false)
		idie(LL('ljpost:notfound')); // Такой заметки нет!
	$p=mkzopt($p);
		if(RE('Body')!==false) $p['Body']=RE('Body');
		if(RE('Header')!==false) $p['Header']=RE('Header');
	$s=prepare_Body($p);

	// применить шаблон
	if(($t=ms("SELECT `text` FROM `site` WHERE `name`='ljpost_template'","_l"))!==false)
		$s=str_replace(array('{text}','{url}'),array($s,getlink($p['Date'])),$t);

	if(isset($server_matka)) {
otprav("
ohelpc('ljpost_post','LJ-post','<iframe width=500 height=200 id=iframeljpost name=iframeljpost></iframe>');
postToIframe({a:'ljpost',ljuser:\"".$admin_ljuser."\",ljpass:\"".$admin_ljpass."\",Header:\"".njsn($p['Header'])
."\",s:\"".njsn($s)."\"},'".$server_matka."/ajax/editor.php','iframeljpost');
");
}
	include_once $include_sys."protocol/lj/lj.php"; // ето моя библиотечка ljpost
$opt=array('prop_opt_noemail'=>1);
// доступ

if($p['Access']=='admin') otprav("salert('Security: `admin only` - not for autopost!',2000);");
if($p['Access']=='all') $opt['security']='public'; else { $opt['security']='usemask'; $opt['allowmask']=0; }
// тэги
$tags=implode(',',gettags($num));


$opt['prop_taglist']=wu($tags);
//вообще документация: http://www.livejournal.com/doc/server/ljp.csp.proplist.html
$flat=(empty($admin_flat)?"http://www.livejournal.com/interface/flat":$admin_flat); // http://lj.rossia.org/interface/flat

	if(($ljurl=ms("SELECT `url` FROM `socialmedia` WHERE `num`='$num' AND `net`='lj'".ANDC(),"_l",0))!==false) {
    list($item,)=explode('|',$ljurl,2); $anst='editdone';
    $lj=LJ_edit($admin_ljuser,$admin_ljpass,$item,wu($p['Header']),wu($s),$opt,$flat);

	} else {
    $lj=LJ_post($admin_ljuser,$admin_ljpass,wu($p['Header']),wu($s),$opt,$flat); $anst='postdone';
	}
	if($lj['success']!='OK') dier($lj,"<p><br>".LL('ljpost:error')."<br>Date: ".date("Y-m-d- H:i:s")); // Ошибка!
	// а если всё в порядке
	msq_add('socialmedia',arae(array('acn'=>$acn,'num'=>$num,'net'=>'lj','url'=>$lj['itemid'].'|'.$lj['url'])));
	idie(LL('ljpost:'.$anst,$lj['url']),LL('ljpost:hsuccess'));
}
//=================================== nocomment ===================================================================
if($a=='nocomment') { ADMA();
	if(($po=ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='$num'".ANDC(),"_1",0))===false) idie('false');
	$po=mkzopt($po); $l=$po['Comment_write'];
	if($po['Comment_write']=='off') { $src='e_ledgreen'; $po['Comment_write']='on'; } else { $src='e_ledred'; $po['Comment_write']='off'; }
	msq_update('dnevnik_zapisi',array('opt'=>e(ser($po))),"WHERE `num`='$num'");
	otprav("idd('knopnocomment_".$num."').className='$src'");
}
//=================================== tags ===================================================================
if($a=='tags') { ADMA();
	$p=explode(',',RE("mytags")); $tag=array(); foreach($p as $l) { $l=c($l); if($l!='') $tag[$l]=1; }
	$t=''; foreach(ms("SELECT DISTINCT `tag` FROM `dnevnik_tags` WHERE 1=1".ANDC(),"_a",0) as $l) { $l=$l['tag'];
		$t.="<span".($tag[$l]!=1?'':" style='color:grey'")." class=l onclick='addtag(this)'>$l</span>, ";
	} $t=trim($t,', '); if($t=='') otprav('');

otprav("
addtag=function(e){ var s=e.innerHTML;
	if(mnogouser) { return sendm('send|win=".RE('win')."|a=addtag|s='+h(s)); }

	var t=idd('tags_".$idhelp."'),a=t.value.replace(/^[\\s,]+|[\\s,]+$/g,'').replace(/\\s*,\\s*/gi,',').split(',');

	var p=in_array(s,a); if(p!==false) { a.splice(p,1); e.style.color='blue'; } else { a.push(s); e.style.color='grey'; }
	a.sort(); t.value=a.join(', ').replace(/^[\\s,]+/g,'');
};

helpc('alltags_".$idhelp."',\"<fieldset id='commentform'><legend>Тэги заметки ".$num."</legend>".njsn($t)."</fieldset>\");
");
}

//=================================== help ===================================================================
if($a=='help') { ADMA();
	$mod=RE("mod"); $mod=str_replace('..','',$mod);
	$modfile=$filehost."site_mod/".$mod.".php";
	$s=file_get_contents($modfile);

	if(!preg_match("/\/\*(.*?)\*\//si",$s,$m)) idie("Для модуля <b>$mod</b> еще не написано справки, пинайте автора.");
	$s=c($m[1]);
	if(preg_match("/^([^\n]+)\n(.*?)$/si",$s,$m)) { $head=$m[1]; $s=c($m[2]); }
	if(preg_match("/(.*?)\n([^\n]*\{\_.*?)$/si",$s,$m)) { $s=c($m[1]); $prim=c($m[2]); }


	include_once $include_sys."_modules.php";
	$prim2=modules($prim);

	idie("<table width=600><td><center><b>$head</b></center><p>".nl2br($s)."
<p><i>например:</i><p>".nl2br(h($prim))."
<p><i>и получаем:</i><div style='border: 1px dashed #ccc'>".nl2br($prim2)."</div>

</td></table>","about: ".$mod.".php");
}

//=================================== loadhelp ===================================================================
if($a=='loadhelp') { $name=RE('name'); ADMA();
	include $file_template."system/help.php";
	include_once $include_sys."_modules.php";
	$s=modules($s);
	otprav("helps('editor-help',\"<fieldset id='commentform'><legend>Справка: редактор</legend><div style='width: 750px'>".njs($s)."</div></fieldset>\");");
}
//=================================== loadhelp ===================================================================

if($a=='bigfotoedit') { AD(); $i=RE0('i'); $p=RE0('p'); otprav("
send_bigfotoedit=function(){majax('editor.php',{a:'bigfotoedit_send',img:idd('bigfot".$p.'_'.$i."').href,num:".RE0('num').",i:$i,p:$p,txt:idd('message').value})};

helps('opechatku',\"<table border='0' cellspacing='0' cellpadding='0'><tr valign=top><td rowspan=2>"
."<textarea class='pravka_textarea' id='message' class='t' cols='50' rows='3'>\""
."+vzyal('bigfottxt').replace(/<br>/gi,'\\n').replace(/<p>/gi,'\\n\\n').replace(/&quot;/gi,'\\\"').replace(/&lt;/gi,'<')"
.".replace(/&gt;/gi,'>')+\"</textarea>"
."<br><input type='button' style='font-size:6px;' value='Ctrl+Enter' onclick='send_bigfotoedit()'>"
."</td></tr><tr><td align=right valign=center>"
."<div class=fmn onclick=\\\"insert_n(idd('message'));\\\"></div>"
."<div class=fmcopy onclick=\\\"ti('message','\\251{select}')\\\"></div>"
."<div class=fmmdash onclick=\\\"ti('message','".chr(151)."{select}')\\\"></div>"
."<div class=fmltgt onclick=\\\"ti('message','\\253{select}\\273')\\\"></div></td></tr></table>\");
helps_cancel('opechatku',function(){clean('opechatku')});
idd('message').focus();
setkey('enter','ctrl',send_bigfotoedit,false,1);
");
}

if($a=='bigfotoedit_send') { AD(); $img=RE('img'); $num=RE0('num');
	$txt=str_replace(array("\n",'"'),array('<br>','&quot;'),RE('txt'));
	$body=ms("SELECT `Body` FROM `dnevnik_zapisi` WHERE `num`='$num'","_l",0);
		if(substr_count($body,$img)!=1) { $img=substr($img,strlen($httpsite));
		if(substr_count($body,$img)!=1) { $img=array_pop(explode('/',$img));
		if(substr_count($body,$img)!=1) idie('IMG not found'); }}

//	file_put_contents('__oldbody.txt',$body);

	$body=preg_replace("/(\s*".preg_quote($img,'/').")(.*?)(\n|_\})/s","$1 ".c($txt)."$3",$body);

	msq_update('dnevnik_zapisi',array('Body'=>e($body)),"WHERE `num`='$num'");

    if(RE0('all')) $s="send_ftall(".RE0('g').",".(RE0('i')+1).");";
    else $s="var txt=\"".njs($txt)."\";
zabil('bigfottxt',txt);
zabil('bigfott".RE0('p')."_".RE0('i')."',txt);
clean('opechatku');";
	otprav($s);
// idie("img: $img www: ".$txt."<hr>".nl2br(h($body)));
}





// === test ===
if($a=='test') { AD();

/*
$s='';
	if(count($_FILES)>0) foreach($_FILES as $FILE) if(is_uploaded_file($FILE["tmp_name"])) {

        	$fname=h($FILE["name"]);
		$s.="<p> LOADED: $fname";

	} else { $s.=print_r($_FILES,1); }
*/

	idie("<pre>".nl2br(h(print_r($_FILES,1)))."</pre>");

}



//=================================== editpanel ===================================================================
if($a=='foto') { ADMA();

// <script>onload = function() { tree("root") }</script>
// <p>My photo <span onclick='tree(\"root\")'>albums</span>:

$s="<div id='ooo'></div>
<ul class='Container' id='root'>
  <li class='Node IsRoot IsLast ExpandClosed'>
    <div class='Expand'></div>
    <div class='Content'>photo</div>
    <ul class='Container'>
    </ul>
  </li>
</ul>
";

otprav(	"
	loadScript('tree.js');
	loadCSS('tree.css');
	helps('foto',\"<fieldset id='commentform'><legend>фотоальбом</legend><div  style='width: 750px'>".njs($s)."</div></fieldset>\");
	tree('root');
");

}
//=================================== editpanel ===================================================================
if($a=='findreplace') { ADMA(); $id=RE('id');

$js="

var e=idd('$id'),ee=e.value.substring(e.selectionStart,e.selectionEnd);

idd('findreplace_fro').value=ee; idd('findreplace_rep').value=ee; idd('findreplace_rep').focus();

findreplace_view=function(i){
	if(i!=idd('findreplace_c').checked) { zabil('findreplace_f','FIND:'); zabil('findreplace_t','REPLACE:'); }
	else { zabil('findreplace_f','Find:'); zabil('findreplace_t','Replace:'); }
};

findreplace_go=function(){
idd('$id').value=idd('$id').value.replace(new RegExp(idd('findreplace_fro').value,'g'+(idd('findreplace_c').checked?'i':'')),idd('findreplace_rep').value);
clean('findreplace');
};";


	$s="<table border=0><tr><td id='findreplace_f' style='width:5em;'>Find:</td><td><input id='findreplace_fro' type='text' value='' size=50></td></tr>
<tr><td id='findreplace_t'>Replace:</td><td><input id='findreplace_rep' type='text' value='' size=50></td></tr>
<tr><td><input id='findreplace_c' onmouseover='findreplace_view(1)' onmouseout='findreplace_view(0)' title='Case sensitive' type='checkbox'></td>
<td><input type='button' value='Go' onclick='findreplace_go()'></td></tr>
</table>";
	otprav("ohelpc('findreplace','Find/Replace',\"".njs($s)."\");".$js);
}

//=================================== move ===================================================================
if($a=='savemove') { ADMA();
    $New=RE('DateNew');
    $Old=RE('DateOld');
    if($New=='') idie("Неверная дата!");
    if(preg_match("/[^a-z0-9_-\/]+/si",$New)) idie("Неверное имя: '".h($New)."'");
    if($New==$Old) idie("Одинаковые?");
    if(intval("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date`='".e($New)."'".ANDC(),"_l",0)) idie("Заметка `".h($New)."` уже существует!");
    if(0==($num=intval(ms("SELECT `num` FROM `dnevnik_zapisi` WHERE `Date`='".e($Old)."'".ANDC()." LIMIT 1","_l",0)))) idie("Error: num=0!");
    $t=getmaketime($New);
    msq_update('dnevnik_zapisi',arae(array('Date'=>e($New),'DateUpdate'=>time(),'DateDate'=>$t[0],'DateDatetime'=>$t[1])),"WHERE `num`='".e($num)."'".ANDC());
    redirect(getlink($New)); // на нее и перейти
}
// ===================
if($a=='move') { ADMA(); $Date=RE('Date');
$s="<input type='hidden' id='move_DateOld' name='DateOld' value='".h($Date)."'><span style='border: 1px dotted #ccc'>".h($Date)."</span>
&mdash; <input class=t type='text' id='move_DateNew' name='DateNew' value='".h($Date)."' maxlength='128' size='20'>
<input type=submit value='Move' onclick=\"majax('editor.php',{a:'savemove',DateOld:idd('move_DateOld').value,DateNew:idd('move_DateNew').value})\">";
$s="helps('move',\"<fieldset id='commentform'><legend>Перенос заметки ".h($p['Date'])."</legend>".njsn($s)."</fieldset>\");
idd('move_DateNew').focus();";
otprav($s);
}
//=================================== fileimport ===================================================================

if($a=='fileimport') { AD(); $file=RE('id'); $Date=$file;

	// взять файл
	if(!is_file($filehost.$file)) otprav('');
	$s=file_get_contents($filehost.$file);

	// подогнать кодировку
	$cp=preg_replace("/^.*<meta\shttp-equiv=[\'\"]Content-Type[\'\"][^>]+charset=([0-9a-z\-]+).*$/si","$1",$s);

	if($cp!=$s && $cp!=$wwwcharset && $cp!='') $s=iconv($cp,$wwwcharset."//IGNORE",$s);

	// убрать говны
	$s=trim(str_replace("\r","",$s));
	$s=preg_replace("/<html.*?>/si","",$s);
	$s=preg_replace("/<body.*?>/si","",$s);

	// попробовать найти заголовок
	if(($Header=preg_replace("/^.*<title>([^<>\n]+)<\/title>.*$/si","$1",$s))==$s) $Header=$wwwhost;
	$s=preg_replace("/<title>.*?<\/title>/si","",$s);

	$s=str_ireplace(array("</html>","</body>","</head>","<head>"),"",$s);

	$opt=array("autoformat"=>"no","template"=>"blank");

// своя обработка





//	idie("codepage='$cp'");
//    [Access] => all
//    [opt] => a:5:{s:12:"Comment_view";s:3:"off";s:7:"autokaw";s:2:"no";s:10:"autoformat";s:2:"no";s:8:"template";s:5:"blank";s:13:"Comment_media";s:3:"all";}

	if(($c=ms("SELECT `count` FROM `lleo`.`site_count` WHERE `lang`='".e($wwwhost.$file)."'",'_l',0))===false) $c=0;

	$p=array(
//		'acn'=>$acn,
		'view_counter'=>$c,
		'Date'=>$Date,
		'Header'=>$Header,
		'Body'=>$s,
//		'num'=>0,
		'opt'=>ser($opt)
	);

	msq_add('dnevnik_zapisi',arae($p));
	$num=ms("SELECT `num` FROM `dnevnik_zapisi` WHERE `Date`='".e($Date)."'","_l",0);
	if(!$num) idie("Error!");
	$p['num']=$num; $idhelp='editor'.$num; 
	// переименовать файл в *.old
	rename($filehost.$file,$filehost.$file.'.old');

        edit_textarea($p,RE("clo")===false?'':"clean('".e(RE("clo"))."');");
}
//=================================== новую заметку ===================================================================
/*
if($a=='test_textarea') {
	$num=0; $idhelp='editor0';
	edit_textarea(
		array('Header'=>'заголовок новой заметки','Body'=>'','num'=>0,'acn'=>'9999999')
	);
}
*/

if($a=='newform') { if(RE('acn')!==false) $acn=RE0('acn'); ADMA();
	$Date=RE('Date'); if(empty($Date)) $Date=date("Y/m/d");
	$i=0; while(ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date`='".e($Date)."'".ANDC(),"_l",0)!=0)
	    $Date=date("Y/m/d").'_'.++$i; //.sprintf("%02d", ++$i)

// idie($Date);
	// $hid=RE('hid');
	$num=0; $idhelp='editor0';
	edit_textarea(
		array('Header'=>'','Body'=>'','num'=>0,'acn'=>$acn,'Date'=>$Date),
		RE("clo")===false?'':"clean('".e(RE("clo"))."');"
	);
}
//=================================== запросили форму ===================================================================
/*
if($a=='editform_new') { ADMA();
	$loc=rpath(substr(RE('loc'),strlen($httphost)));
	if(($p=ms("SELECT `num` FROM `dnevnik_zapisi` WHERE `Date`='".e($loc)."'".ANDC(),"_l",0))!==false) idie("Already exist: ".h($loc));
	if(is_file($site_module.strtoupper($loc).".php")) $Body='{_'.strtoupper($loc).':_}'; else $Body='';
	$p=array('Access'=>'admin','DateUpdate'=>time(),'Date'=>e($loc),'Body'=>$Body,'acn'=>$acn);
	msq_add('dnevnik_zapisi',$p);
	$num=msq_id();
	$a='editform';
}
*/


if($a=='editform') { ADX(); ADMA();
	if($num) $p=ms("SELECT * FROM `dnevnik_zapisi` WHERE `num`='$num'".ANDC(),"_1",0);
	else { $p=ms("SELECT * FROM `dnevnik_zapisi` WHERE `Date`='".e(RE('Date'))."'".ANDC(),"_1",0); $num=$p['num']; }
	if($p===false) idie("Отсутствует заметка #$num ".h(RE('Date'))
."<p><div class='ll' onclick=\"majax('editor.php',{a:'editform_new',loc:window.location.href})\">Создать?</div>");
	edit_textarea($p);
}
//====================================
function edit_textarea($p,$majax='') { global $Date,$num,$acn,$zopt_a,$autosave_count,$admin_ljuser,$admin_ljpass,$mnogouser;

$Date=$p['Date']; // if(empty($Date)) { $Date=RE('Date'); if(empty($Date)) $Date=date("Y/m/d"); }

$tags=implode(', ',gettags($num));

$ara=array(
	'Date'=>$Date,
	'autosave_count'=>$autosave_count,
	'acn'=>$acn,
	'num'=>$num,
	'W'=>(isset($_GET['w'])?intval($_GET['w']):0),
	'H'=>(isset($_GET['h'])?intval($_GET['h']):0),
	'editor_width'=>intval($GLOBALS['editor_width']),
	'editor_height'=>intval($GLOBALS['editor_height']),
	'fidouser'=>(1==ms("SELECT COUNT(*) FROM `site` WHERE `name`='fido'".ANDC(),"_l")?1:0),
	'ljuser'=>(!empty($admin_ljuser)&&!empty($admin_ljpass)?1:0),
	'twitteruser'=>(1==ms("SELECT COUNT(*) FROM `site` WHERE `name`='twitter'".ANDC(),"_l")?1:0),
	'Body'=>$p['Body'],
	'mnogouser'=>$mnogouser,
	'Header'=>$p['Header'],
	'adminset'=>njsn(ADMINSET($p)),
	'mopt'=>1, // $mopt,
	'ext'=>'',
	'tags'=>$tags
);

    $ara=array_merge($p,$ara);

// достать файл шаблона
$s=get_sys_tmp("editor_".rpath(RE('editor')).".htm");
$s=nort($s);
$s=str_replace("</form>","<div style='display:inline;' id='edit_ext{num}'>{ext}</div></form>",$s);

// и дописать переменные, если были указаны, типа
$a=array_merge(explode(' ','Access tags Date Header Body visible'),array_keys($zopt_a));
foreach($a as $l) { if(false===($x=RE($l))) continue;
    if(isset($ara[$l])) $ara[$l]=h($x); else $ara['ext'].="<input type='hidden' name='".h($l)."' value='".h($x)."'>";
}

$ara['ext'].="<input type='button' onclick=\\\"majax('protocol.php',{a:'posts',num:'{num}'})\\\" value='Social Media'>";
$ara['ext'].="<input type='button' onclick=\\\"majax('protocol.php',{a:'list',num:'{num}'})\\\" value='List SM'>";

otprav(mpers($s,array_merge(makeopt(unser($p['opt']),1),$ara)));
}

//----------- autopost panel --------------
if($a=='autopost_panel') { AD();
	$opt=unser(ms("SELECT `opt` FROM `dnevnik_zapisi` WHERE `num`='$num'","_l",0));
	$opt2=mkzopt($opt); ksort($opt2);
	foreach($opt as $n=>$l) unset($opt2[$n]);
	otprav("zabil('".$idhelp."_extautopost',\"<br><fieldset><legend>autopost</legend>Under constructions</fieldset><p>\");");
}
//----------- setting panel --------------
if($a=='settings_win') { ADMA(); //panel
	$aga=ms("SELECT `visible`,`opt` FROM `dnevnik_zapisi` WHERE `num`='$num'".ANDC(),"_1",0);

$optmy=unser($aga['opt']);

// dier($optmy);

$s=''; $r=array(); foreach($zopt_a as $n=>$vv) { $rr=array();
$rr['v']=(isset($optmy[$n])?$optmy[$n]:$vv[0]); // мое или дефолтное
$rr['def']=$vv[0]; // само дефолтное
$rr['vals']=$vv[1]; // все значения через пробел
$rr['txt']=LL('zopt:'.$n); // текстовое пояснение
$rr['comdef']='';
$coms=array();

    if($n=='include' && $optmy[$n]=='') continue;

	if($n=='template') { // выяснить о модулях
		$inc=glob($filehost."template/*.html"); $ainc=array(); foreach($inc as $l) { $l=preg_replace("/^.*?\/([^\/]+)\.html$/si","$1",$l); $ainc[$l]=$l; }
		$rr['vals']=implode(' ',$ainc);
		$rr['input']=selecto('template',$rr['v'],array_merge(array('default'=>'&mdash;'),$ainc),"class='r' onchange='ch_edit_pole(this)' name");
	} else { $m=explode(' ',$vv[1]);
		if($m[0]=='s') $rr['input']="<input type='text' maxlength='".$vv[2]."' size='".min($vv[2],64)."' name='".$n."' class='r' onchange='ch_edit_pole(this,$num)' value=\\\"".h($optmy[$n]?$optmy[$n]:'')."\\\">"
." &nbsp; ".(strlen($vv[0])<1?h($vv[0]):"<span class='ll' alt=\\\"".h($vv[0])."\\\">default</span>");
		else {
			$a=array(); foreach($m as $j) { $coms[]=$a[$j]=LL('zopt:'.$n.':'.$j); }
			$rr['input']=selecto($n,($rr['v']==$rr['def']?'default':$rr['v']),array_merge(array('default'=>'&mdash;'),$a),"class='r' onchange='ch_edit_pole(this)' name");
			$rr['comdef']=" &nbsp; ".LL('zopt:default')." &laquo;".LL('zopt:'.$n.':'.$vv[0])."&raquo;";
		}
	}

$rr['txts']=implode('|',$coms);
$r[$n]=$rr;
$s.="<div><span alt='".($n)."'>".$rr['txt']."</span> ".$rr['input']." ".$rr['comdef']."</div>";
unset($optmy[$n]);
}

if(sizeof($optmy)) foreach($optmy as $n=>$v) { $r[$n]=array('def'=>'','vals'=>'','comdef'=>'','txts'=>'',
'v'=>$v,'txt'=>"extended '".$n."'",
'input'=>"<input type='text' maxlength='256' size='64' name='".$n."' class='r' onchange='ch_edit_pole(this,$num)' value=\\\"".h($v)."\\\">");
$s.="<div>".$rr['txt']." ".$rr['input']."</div>";
}

$m=array(); foreach($r as $n=>$vv) $m[]=$n.":{"
."v:\"".njs($vv['v'])."\","
."def:\"".njs($vv['def'])."\","
."vals:\"".njs($vv['vals'])."\","
."txt:\"".njs($vv['txt'])."\","
."comdef:\"".njs($vv['comdef'])."\","
."txts:\"".njs($vv['txts'])."\"}";
$m="var zopt={".implode(',',$m)."};";

// idie(h($m));
// dier($r);

	otprav(nort(mpers(get_sys_tmp("edit_options.htm"),array(
		'acn'=>$p['acn'],
		'num'=>$num,
		'visible'=>$aga['visible'],
		's'=>$s,
		'zopt'=>$m
	))));
}
//----------- setting panel --------------

if($a=='ch_dostup') { ADMA(); /*global $admincolors;*/ // смена доступа к заметке
	$d=array_pop(explode('/',RE('d')));
	foreach($admincolors as $n=>$l) { if($l[1]==$d) { $k=$admincolors[(++$n)%3];
		msq_update('dnevnik_zapisi',array('Access'=>$k[0]),"WHERE `num`='$num'".ANDC());
		if($k[0]=='all') { $pad=0; $col='transparent'; } else { $pad=10; $col=$GLOBALS['podzamcolor']; }
		otprav("
doclass('".$num."_adostup',function(e){e.className='".$k[1]." ".$num."_adostup'});
var e=idd('Body_".$num."'); if(e){ e.style.padding='".$pad."pt'; e.style.backgroundColor='".$col."'; }
		");
		}
	}
	idie("error: ".h(RE('d')));
}
//=================================== удаление заметки ===================================================================
if($a=='delete') { ADMA();
	if(!zametka_del($num)) idie('Fuxk!');
	redirect(acc_link($acc,''));
}
//=================================== запросили форму ===================================================================

if($a=='submit') { ADMA(); $e=explode(' ',trim(RE('names'))); unset($e['asave']);

	if($num) $p=ms("SELECT * FROM `dnevnik_zapisi` WHERE `num`='$num'".ANDC(),"_1",0); else $p=array('opt'=>'');
	$Date=isset($p['Date'])?$p['Date']:RE('Date'); if(!$num&&$Date=='') otprav(''); // idie('Blank date!');
	$opt=unser($p['opt']);
	foreach($opt as $n=>$l) if(!isset($zopt_a[$n])) unset($opt[$n]); // удалить некондиционные метки

        $p1=array();
	$tags=false;

	foreach($e as $name) { $val=str_replace("\r",'',RE($name));
		if(strstr($name,'[]')||substr($name,0,4)=='file') continue;
		if(in_array($name,array('names','up','ux','hashpage','autopost'))) continue;

		if($name=='tags') { $tags=RE($name); continue; }
		if(isset($zopt_a[$name])) { // опция
			if($val=='default') $val=$zopt_a[$name][0]; // дефолтная, потом нахуй вычистим
			$opt[$name]=$val;
			continue;
		/*
			if($val=='default' or $zopt_a[$name][1]=='s' && ( c($val)=='' or $val==$zopt_a[$name][0]) // дефолтная строка
			) { if(isset($opt[$name])) unset($opt[$name]); }
			else $opt[$name]=$val;
			continue;
		*/
		}
		if($name=='Body') { // Body

// =========== ЗАГРУЗКА ФОТОК ==========
$dt=preg_replace("/[^a-z0-9\_\-\/]+/si",'',$Date);

if(count($_FILES)>0) {

    function zameni_image($tag,$pe,$i,$wf,$val) { global $zmnf;
	$from="[IMAGE".$pe[1][$i].":".$pe[2][$i]."]";
	$to="{_".$tag.": ".$wf." _}";
	if(strstr($val,$from)) return str_replace($from,($zmnf=$to),$val); // если есть такое имя - заменить
	if(isset($zmnf) && strstr($val,$zmnf)) return str_replace($zmnf,($zmnf=$zmnf."\n".$to),$val); // иначе если есть прошлая замена - добавить ниже нее
	idie("editor.php upload error: `".h($from)."` (`".h($zmnf)."`)");
    }

    require_once $include_sys."_fotolib.php";

    preg_match_all("/\[IMAGE(\d+)\:([^]]+)\]/s",$val,$pe);
	$pe[3]=array(); foreach($pe[2] as $n=>$l) $pe[3][$n]=basename(str_replace("\\","/",trim($l))); // патчим йобаное Хромовское C:/fakepath/КАРИТНКА.JPG

    foreach($_FILES as $f) { if(gettype($f['name'])!='array') foreach($f as $a=>$b) $f[$a]=array(0=>$b);

    foreach($f['name'] as $a=>$b) {

	if(!is_uploaded_file($f["tmp_name"][$a])) continue;
    // $l=h($f["name"][$a]);
    $l=$f["name"][$a];
    // найти расширение
    $ras=explode_last('.',$l);
    if(!in_array($ras,array('jpg','jpeg','gif','JPG','JPEG','GIF','png','PNG'
,'wav','mp3','WAV','MP3','ogg','OGG'))) idie("*.".h($ras)." - это разве фотка или mp3?");
    if(substr($l,0,1)=='.') idie("Имя с точки?"); if($l!=rpath($l)) idie("Чо, хакер, бля?");
    $i=array_search($l,$pe[3]); // if($i===false || $i===NULL) continue; // если такой фотки не указано - вообще игнорировать
    // выбрать имя
    if($l==preg_replace("/[^0-9a-z\.\-\_]+/si",'@',$l)) $fsave=$l; else {
	    $fotnum=0; while(file_exists(rpath($GLOBALS['filehost'].accd().$dt.'/'.($fsave=($fotnum++).'.'.$ras)))) {}
    }

    $df=rpath($GLOBALS['filehost'].accd().$dt.'/'.$fsave);
    $wf=$wwwhost.accd().$dt.'/'.$fsave;

    testdir(dirname($df)); if(!is_dir(dirname($df))) {
$o=''; $x=''; foreach(explode('/',dirname($df)) as $l) { $x.=$l.'/'; $o.="<br>".h($x)." ".(is_dir(rtrim($x,'/'))?substr(sprintf('%o',fileperms(rtrim($x,'/'))),-4):'not found'); }
idie("Error: не удается создать папку ".h(dirname($df)).$o);
}

    if(!is_file($f["tmp_name"][$a])) idie("<font color=red>NOT FOUND: ".h($f["tmp_name"][$a])."</font><br><pre>".nl2br(print_r($f,1))."<hr></pre>");

    if(in_array($ras,array('jpg','jpeg','gif','JPG','JPEG','GIF','png','PNG'))) {
	obrajpeg($f["tmp_name"][$a],$df,$GLOBALS['foto_res_small'],$GLOBALS['foto_qality_small'],''); $val=zameni_image('IMG',$pe,$i,$wf,$val); // 640,85
    } elseif(in_array($ras,array('wav','mp3','ogg','WAV','MP3','OGG'))) {
	// fileput($df,file_get_contents($f["tmp_name"][$a])); $val=zameni_image('MP3',$pe,$i,$wf,$val);
	fileput($df,file_get_contents($f["tmp_name"][$a]));
	$val=zameni_image('PLAY',$pe,$i,$wf." ".preg_replace("/\.(mp3|wav|ogg)$/si",'',$l),$val);
    }

    if(!is_file($df)) idie("Error: что-то пошло не так с файлом ".h($df));
}}

}
// =========== / ЗАГРУЗКА ФОТОК ==========
			$val=preg_replace("/[ \t]+\n/s","\n",$val); // нахуй пробелы до конца строки
			$po=mkzopt($p); if($po["autokaw"]!="no") $val=ispravkawa($val); // если разрешено обработать кавычки и тире
		}
		if($name=='el-select') continue; // elrte-patch
	$p1[$name]=$val;

	}  // "foreach $e as $name" END

	if(!RE0('asave')) $p1['DateUpdate']=time();
	$p1['opt']=ser(cleanopt($opt)); // опции

foreach($p as $n=>$l) if(isset($p1[$n])) { if($p1[$n]==$l) unset($p1[$n]); else $p[$n]=$p1[$n]; }

	// save
	if($num) {
	    foreach($p as $i=>$l) { if($i!='opt' && !in_array($i,$e)) unset($p[$i]); } // лишней хуйней базу не апдейтить
	    if(isset($p['Date']) && c0($p['Date'])=='') idie("Error: Date=zero");

//	    dier($p,'wwwwwwwwwww');

	    if(sizeof($p)) msq_update('dnevnik_zapisi',arae($p),"WHERE `num`='$num'".ANDC()); if($msqe) idie($msqe);
	    if($tags!==false) save_tags($tags); // и тэги дописать
	} else { //== новую заметку =====
//		if($num==0) { put_last_tmp($val); otprav(''); } else { del_last_tmp(); } // сохранять в tmp текст для новых
		$d=c($p1['Date']);
		if(empty($d)) otprav("");

		if(preg_match("/[^0-9a-z\-\_\.\/]+/si",$d) or empty($d) ) {
			$d=preg_replace("/[^0-9a-z\-\_\.\/]+/si",'',$d);
			otprav("idd('".$idhelp."_Date').value=\"".$d."\"; salert(\"".njs(LL('Editor:wrong_data',$httphost))."\");");
		}
		$t=getmaketime($d);
		if(0!=ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date`='".e($d)."'".ANDC(),"_l",0)) {
			$r=0; while(0!=ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date`='".e($d.'_'.(++$r))."'".ANDC(),"_l",0)){}
			otprav("var a=idd('".$idhelp."_form').Date,b=\"".h($d.'_'.$r)."\"; if(a.value)a.value=b;if(a.innerHTML)a.innerHTML=b; salert(\"".LL('Editor:new_exist',array(getlink($d),getlink($d.'_'.$r)))."\",2000);");
		}
		$p1=array_merge($p1,array('acn'=>$acn/*,'Access'=>'admin'*/,'DateUpdate'=>time(),'DateDate'=>$t[0],'DateDatetime'=>$t[1]));
		msq_add('dnevnik_zapisi',arae($p1)); if($msqe) idie($msqe);

		$GLOBALS['num']=msq_id();
		if($tags!==false) save_tags($tags); // и тэги дописать

		otprav_save("","window.top.location='".getlink($d)."';");
		//== новую заметку =====
	}

//	$s="zabil('buka',vzyal('buka')+'".implode('|',$e)."<br>');";
	$s=(RE0('asave')>1?"":"clean('".$idhelp."_options');");
//	if(RE0('onlysettings'))

// dier($p1);

if(false!=($tmpl=RE('pagetmpl'))) {
	$f=rpath($GLOBALS['filehost']."template/module/".$tmpl.".php");
	if(is_file($f)) include_once $f;
}

	if(in_array('Body',$e)) { $p=mkzopt($p); $s.="zabil('Body_$num',\"".njs(onetext($p))."\");"; }
	if(in_array('Header',$e)) $s.="zabil('Header_$num',\"".njs($p['Header'])."\");";
	otprav_save($s,"clean('".$idhelp."');");
}
// -------------------
function otprav_save($s1,$s2) { global $num; $ap='';



    if(RE0('autopost')&&1*$num) { // ох блять и беда мне с этим автопостом
	$p=ms("SELECT `DateDate`,`Access` FROM `dnevnik_zapisi` WHERE `num`='".e($GLOBALS['num'])."'".ANDC(),"_1",0);

/*
	idie("p['Access']=".$p['Access']." time()-p['DateDate']=".(abs(time()-$p['DateDate']))." F:".($p['Access']=='all' && abs(time()-$p['DateDate']) < 7*24*60*60)
."ABS: ".abs(time()-$p['DateDate'])
." 7*24=".(7*24*60*60)
." INTVAL: ".((time()-$p['DateDate']) < 7*24*60*60 ? 1:0)
);
*/

	if($p['Access']=='all' && ((time()-$p['DateDate']) < 7*24*60*60) && loadsite('autopost')!==false) $ap="majax('protocol.php',{a:'posts',num:'".$num."'});"; // 60*60*24*7 последняя неделя  ;

// idie(h($s1.(RE0('asave')?"salert(\"".LL('saved')."\",100);":$s2).$ap));

    }
    otprav($ap.$s1.(RE0('asave')?"salert(\"".LL('saved')."\",100);":$s2));
}

// -------------------

function save_tags($s) { ADMA(); tags_save($s); }

/*
function pokaji_opt($opt,$def=1) { global $num,$zopt_a; $s=''; $i=0;
	foreach($opt as $n=>$v) { if(!isset($zopt_a[$n])) continue; $l=$zopt_a[$n];
		if($def) $val=($v!=$l[0]?$v:'default'); else $val=$v;
	$s.=($i++?"<br>":'').LL('zopt:'.$n)." : ";

	if($n=='template') {
		// выяснить о модулях
		$inc=glob($GLOBALS['filehost']."template/*.html"); $ainc=array('default'=>'&mdash;'); foreach($inc as $l) { $l=preg_replace("/^.*?\/([^\/]+)\.html$/si","$1",$l); $ainc[$l]=$l; }
		$s.=selecto('template',$val,$ainc,"class='r' onchange='ch_edit_pole(this,$num)' name");
	} else { $m=explode(' ',$l[1]);
		if($m[0]=='s') $s.="<input type='text' maxlength='".$l[2]."' size='".min($l[2],64)."' name='".$n."' class='r' onchange='ch_edit_pole(this,$num);' value=\\\"".h(isset($opt[$n])?$opt[$n]:'')."\\\">";
		else {
			$a=array('default'=>'&mdash;'); foreach($m as $j) $a[$j]=LL('zopt:'.$n.':'.$j);
			$s.= selecto($n,$val,$a,"class='r' onchange='ch_edit_pole(this,$num)' name");
			$s.= " &nbsp; ".LL('zopt:default')." &laquo;".LL('zopt:'.$n.':'.$l[0])."&raquo;";
		}
	}
	}
return $s;
}
*/

//====================================
if($a=='clean_html') { ADMA();
	$s=RE('text');
	$s=str_replace("\r",'',$s);
	$s=preg_replace("/ +\n/s","\n",$s);

	$t=get_sys_tmp("editor_clean_html.txt"); $t=str_replace("\r",'',$t); $t=explode("\n",$t);
	foreach($t as $n=>$l) { if(''==strtr($l,"\t\r ",'') || !strstr($l,'|')) continue;
		list($a,$b)=explode('|',$l);

		$a=str_replace("\\".'n',"\n",$a); // enter

		$a=preg_quote($a,'/');
		$a=str_replace('\*','([^<>]*?)',$a);
		$a=preg_replace("/[\'\"]/s","[\\'\\\"]*",$a);
		$a=preg_replace("/\s+/s","\\s+",$a);

		$b=preg_replace("/\{(\d+)\}/s","$"."$1",$b);

		$k=100; $s1=''; while($s1!=$s && --$k){ $s1=$s; $s=preg_replace("/".$a."/s",$b,$s); }
	}

	$s=str_replace("<p>&nbsp;\n","<p>\n",$s);
	$s=str_replace("<br>&nbsp;\n","<br>\n",$s);
	$s=str_replace("\n<br>","\n",$s);
	$s=str_replace("\n<p>","\n",$s);
	$s=str_replace("<br>","\n",$s);
	$s=str_replace("<p>","\n",$s);

	$s=str_replace("&laquo;",chr(171),$s);
	$s=str_replace("&raquo;",chr(187),$s);
	$s=str_replace("&mdash;",chr(151),$s);
	$s=str_replace("&ndash;",chr(151),$s);

	otprav("
idd('".$idhelp."_Body').value=\"".njsn($s)."\";
salert('done',500);
");
//	idie(nl2br(h($o.$s)));
}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>