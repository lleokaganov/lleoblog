<?php // Окно для листания всяческого добра

include "../config.php"; include $include_sys."_autorize.php";

$a=RE('a'); ADH();

// =========== шо я не видел ============================= majax('okno.php',{a:'notseen',[day:30]})
if($a=='notseen') {  $day=RE0('day'); if(!$day) $day=30; $o=''; $acn=RE0('acn');

$pp=ms("SELECT `Access`,`Date`,`Header` FROM `dnevnik_zapisi` as d ".WHERE(
"`DateDate`>'".(time()-$day*86400)."'
 AND NOT EXISTS (SELECT `url` FROM `dnevnik_posetil` AS p WHERE `unic`='".$unic."' AND d.`num`=p.`url`)"
)
." ORDER BY `Date` DESC");

$dnext=$day+30; $dop=njsn("<div class=br><center><a href=\"javascript:majax('okno.php',{a:'notseen',day:".$dnext.",acn:$acn})\">за ".$dnext." дней</a></center></div>");

if($pp) {
$m=array(); foreach($pp as $p) $m[]=zamok($p['Access']).$p['Date']." - <a href='".get_link_($p['Date'])."'>".($p['Header']!=''?$p['Header']:'(...)')."</a>";
$o=implode('<br>',$m);
otprav("helpc('notseen',\"<fieldset><legend>мои непрочитанные заметки</legend>".njs($o)."<hr width=30%>".$dop."</fieldset>\");");
}
otprav("idie(\"все заметки за ".$day." дней прочитаны".$dop."\");");
}
// ======== rekomenda - листать базу rekomenda ===========
if($a=='rekomenda') {
	$nskip=max(0,RE0('nskip'));
	$nlim=20;
	$search=RE('search');

if(empty($n)) $topo=""; else { $se="LIKE '%".e($search)."%'"; $topo="WHERE `link` ".$se." OR `text` ".$se; }

$n=RE0("n"); if(!$n) $n=ms("SELECT COUNT(*) FROM $db_rekomenda $topo","_l");

$pp=ms("SELECT `datetime`,`link`,`text` FROM $db_rekomenda $topo ORDER BY `datetime` DESC LIMIT ".e($nskip).",".e($nlim));

mk_okno("<center><form onsubmit=\"return ajaxform(this,'okno.php',{a:'rekomenda'})\">
<input name='search' type='text' onchange=\"majax('okno.php',{a:'rekomenda',serch:this.value})\" size='40' value=\"".h($search)."\">"
."<input type='submit' value='search'></form></center><br>"
.pr_rekomenda($pp),"База rekomenda ($nlim с ".$nskip.", всего ".$n.")","a:'$a'"// ,id:'$id'"
);
}

// ======== fidosearch - поиск по базе FIDO ===========
if($a=='fidosearch') { $se=c(RE('search'));

	$GLOBALS['db_fid']="`fido`";
	$GLOBALS['db_fido']=$GLOBALS['db_fid'].".`fidoecho`";
	$GLOBALS['db_fido_num']=$GLOBALS['db_fid'].".`fidoecho_num`";

$type=RE("type");

$s="<center>поиск: <INPUT style='font-size: 12px; border: 1px solid #ccc' TYPE='text' id='oknom_search' value=\"".h($se)."\" SIZE='40' MAXLENGTH='160'> "
.selecto('oknom_search_type',$type,array(
'all'=>'all','body'=>'Message','subj'=>'Subj','from'=>'From','to'=>'To','fromaddr'=>'From addr','toaddr'=>'To addr'
),"class='r' id='oknom_search_type'")
." <INPUT style='font-size: 12px' TYPE=SUBMIT VALUE='go' onclick=\"majax('okno.php',{a:'fidosearch',search:idd('oknom_search').value,type:idd('oknom_search_type').value,lastmon:idd('oknom_search_lastmon').value})\">"
."<br>за месяцев: <input style='font-size: 12px; border: 1px solid #ccc' size='3' type='text' id='oknom_search_lastmon' value='".(RE0('lastmon')?RE0('lastmon'):1)."'>
</center><p>";

	if($se=='') { $jscripts="idd('oknom_search').focus();"; mk_okno($s,"ПОИСК"); }

	$nskip=max(0,RE0("nskip"));

	$nlim=10;

$ser="='".e($se)."'";
$sera="LIKE '%".e($se)."%'";
$seru="LIKE '%".e(wu($se))."%'";

// поиск в записях
$SR=array(); $q=preg_replace("/[^a-z]+/s","",RE("type"));
	if($q=='all' or $q=='body') $SR[]="`BODY` $seru";
	if($q=='all' or $q=='subj') $SR[]="`SUBJ` $seru";
	if($q=='all' or $q=='from') $SR[]="`FROMNAME` $seru";
	if($q=='all' or $q=='to'  ) $SR[]="`TONAME` $seru";
	if(             $q=='fromaddr') $SR[]="`FROMADDR` $ser";
	if(             $q=='toaddr') $SR[]="`TOADDR` $ser";
	if(!sizeof($SR)) idie("unknown type: ".h($type));
	$SEAR="FROM ".$GLOBALS['db_fido']." WHERE
(`RECIVDATE`> NOW()-INTERVAL ".(RE0('lastmon')?RE0('lastmon'):1)." MONTH)
AND (".implode(" OR ",$SR).")";
	//MSGID REPLYID `FROMNAME`,`TONAME`,`FROMADDR`,`TOADDR` RAZMER DATETIME RECIVDATE ATTRIB

   $GLOBALS['msq_charset']='utf8';
   msq("SET NAMES ".$GLOBALS['msq_charset']);
   msq("SET @@local.character_set_client=".$GLOBALS['msq_charset']);
   msq("SET @@local.character_set_results=".$GLOBALS['msq_charset']);
   msq("SET @@local.character_set_connection=".$GLOBALS['msq_charset']);

	$n=intval(ms("SELECT COUNT(*) ".$SEAR,"_l"));

if($n) {
	$pp=ms("SELECT `id`,`AREAN`,`SUBJ`,`BODY`,`FROMNAME`,`TONAME`,`FROMADDR`,`TOADDR`,`RECIVDATE` ".$SEAR." ORDER BY `RECIVDATE` DESC LIMIT ".intval($nskip).",".($nlim),"_a");

	foreach($pp as $p){ $txt=h(uw($p['BODY'])); $z=strlen($txt); $o=str_isearch2($se,$txt);
	$s.="<div style='background-color: #ccc'><b>".h(strtoupper(get_arenum0($p['AREAN'])))."</b> ".h(uw($p['FROMNAME']))." ".h($p['FROMADDR'])." (".date("Y/m/d H:i",strtotime($p['RECIVDATE']))."):
<a href='".$GLOBALS['httpsite']."/fido?all=1&search=".urlencode($se)."#area:".h(get_arenum0($p['AREAN']))."|id:".$p['id']."'>".(trim($p['SUBJ'])==''?'(---)':h(uw($p['SUBJ'])))."</a></div>";
	$otstup=30;
	$t=array(); foreach($o as $in=>$i) { if($in>10) { $t[]="<i><b>...и ещё подобных совпадений ".(sizeof($o)-10)."</b></i>"; break; }

	$start=($i>$otstup?$i-$otstup:0); if(isset($o[$in-1])&&$start<($o[$in-1]+strlen($se))) $start=$o[$in-1]+strlen($se);
	else { $k=0; while($k++<10&&$start!=0&&$txt[$start]!=" "&&$txt[$start]!="\n") $start--; }

	$end=(($i+strlen($se)+$otstup)<$z?$i+strlen($se)+$otstup:$z);  if(isset($o[$in+1])&&$end>($o[$in+1])) $end=$o[$in+1];
	else { $k=0; while($k++<10&&$end<$z&&$txt[$end]!=" "&&$txt[$start]!="\n") $end++; }

	$t[]=substr($txt,$start,$end-$start);
	} if(sizeof($t)) foreach($t as $l) $s.="<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".strtr($l,"\n"," ");
	}

} else $s.="<p class=r><i>ничего не найдено</i>";

mk_okno($s,"FIDO search '<b>".h($se)."</b>' from ".$nskip.($n?" (".$n.")":''),"a:'$a',type:'$q',lastmon:".RE0('lastmon'));
}

// ======== search - поиск по базе ===========
if($a=='search') { $se=c(RE('search'));

// $acn=intval(RE0('acn'));
ADMA(1);
// $adm=0;

$type=RE("type");
$n=RE0('n');

if($type=='name') { $a='unics'; $s=''; } else {

$s="<form onsubmit=\"return send_this_form(this,'okno.php',{a:'search',acn:".$acn."})\">"
."<center>поиск: <INPUT id='oknom_search' style='font-size: 12px; border: 1px solid #ccc' TYPE='text' name='search' value=\"".h($se)."\" SIZE='40' MAXLENGTH='160'> "
.selecto('oknom_search_type',RE("type"),array('zapisi'=>'в заметках','comm'=>'в комментариях','name'=>'в именах'),"name='type'")
." <input type='submit' value='Search'></center></form><p>";
if($se=='') { $jscripts="idd('oknom_search').focus();"; mk_okno($s,"ПОИСК ".(empty($acc)?'':" в аккаунте `".h($acc)."`")); }


$nskip=max(0,1*RE0("nskip"));

// поиск в записях
if($type=='zapisi') {
	$nlim=10; $SEAR="FROM `dnevnik_zapisi` ".WHERE("(`Body` LIKE '%".e($se)."%' OR `Header` LIKE '%".e($se)."%'"
.(1*$se==$se?" OR `num`='".e($se)."'":'').")".ANDC());
	$n=intval(ms("SELECT COUNT(*) ".$SEAR,"_l"));
if($n) {
	$pp=ms("SELECT `Access`,`Body`,`Header`,`Date`,`num` ".$SEAR." ORDER BY `Date` DESC LIMIT ".e($nskip).",".e($nlim),"_a");

if(!$ADM) include_once $include_sys."_onetext.php";

	foreach($pp as $p){
if(!$ADM) {
	$GLOBALS['article']=$p; $p['Body']=modules($p['Body']); $i=stristr($p['Body'],$se); // if(!$i) continue;

// пока не работает добивка до 10, да и нахуй оно надо
	while(!$i) {
		$p=ms("SELECT `Access`,`Body`,`Header`,`Date`,`num` ".$SEAR." ORDER BY `Date` DESC LIMIT ".($nlim+$nskip).",1","_1");
		if($p===false) { $i=0; break; }
		$nskip++;
		$GLOBALS['article']=$p; $p['Body']=modules($p['Body']); $i=stristr($p['Body'],$se);
	} if(!$i) continue;

}

$txt=h($p['Body']); $z=strlen($txt); $o=str_isearch2($se,$txt); //if(!sizeof($o)) continue;


	$s.="<br>".zamok($p['Access'])."<a href='".getlink($p['Date'])."?search=".urlencode($se)."'>".h($p['Date'].": ".$p['Header'])."</a>";
	$otstup=30;
	$t=array(); foreach($o as $in=>$i) { if($in>10) { $t[]="<i><b>...и ещё подобных совпадений ".(sizeof($o)-10)."</b></i>"; break; }

	$start=($i>$otstup?$i-$otstup:0); if(isset($o[$in-1])&&$start<($o[$in-1]+strlen($se))) $start=$o[$in-1]+strlen($se);
	    else { $k=0; while($k++<10&&$start!=0&&$txt[$start]!=" "&&$txt[$start]!="\n") $start--; }

	$end=(($i+strlen($se)+$otstup)<$z?$i+strlen($se)+$otstup:$z);  if(isset($o[$in+1])&&$end>($o[$in+1])) $end=$o[$in+1];
	    else { $k=0; while($k++<10&&$end<$z&&$txt[$end]!=" "&&$txt[$start]!="\n") $end++; }

	$t[]=substr($txt,$start,$end-$start);
	} if(sizeof($t)) foreach($t as $l) $s.="<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".strtr($l,"\n"," ");
	}
} else $s.="<p class=r><i>ничего не найдено</i>";

$_REQUEST['n']=$n;
mk_okno($s,"zapisi search '<b>".h($se)."</b>' from ".$nskip.($n?" (".$n.")":''),"a:'$a',type:'zapisi'");
}

// поиск в комментариях
elseif($type=='comm') { // поиск по комментариям
	$nlim=10; include_once $include_sys."_onecomm.php";
	$SEAR="FROM `dnevnik_comm` WHERE (`Text` LIKE '%".e($se)."%'".(1*$se==$se?" OR `id`='".e($se)."'":'').")".($podzamok?'':" AND `scr`='0'");

	$n=intval(ms("SELECT COUNT(*) ".$SEAR,"_l"));
// потом разобраться, может в комменты добавлять acn
	$s.=($n? pr_comments_("SELECT * ".$SEAR." ORDER BY `Time` DESC LIMIT $nskip,".($nlim)):"<center>ничего не найдено</center>");
	mk_okno($s,"comments search '<b>".h($se)."</b>' from ".$nskip.($n?" (".$n.")":''),"a:'$a',type:'comm'");
}


}}

// ======== hiscomment - все комментарии одного человека ===========
if($a=='hiscomment') {
	$id=RE0("id"); if(!$id) idie("unic = null!");
	$nskip=1*RE0("nskip");
	$nlim=10;
	include_once $include_sys."_onecomm.php";
// {capchakarma} {name}
	$s=pr_comments_("SELECT c.`id`,c.`unic`,c.`group`,c.`Name`,c.`Text`,c.`Parent`,c.`Time`,c.`whois`,c.`rul`,c.`ans`,
c.`golos_plu`,c.`golos_min`,c.`scr`,c.`DateID`,c.`BRO`,
u.`capchakarma`,u.`mail`,u.`admin`,u.`openid`,u.`realname`,u.`login`,u.`img`
FROM `dnevnik_comm` AS c
LEFT JOIN ".$GLOBALS['db_unic']." AS u ON c.`unic`=u.`id`
WHERE c.`unic`='".$id."'".($podzamok?'':" AND c.`scr`='0'")."
ORDER BY `Time` DESC LIMIT $nskip,".($nlim)
);
	mk_okno($s,"comments from user #".h($id)." from ".$nskip,"a:'$a',id:'$id'");
}

// ======== unics - листать базу посетителей ===========
if($a=='unics') {

	$nskip=1*RE0('nskip');

	$nlim=20;
$jscripts=($admin?"
chzamok=function(e,d){
	if(d=='user') var o='podzamok';
	else if(d=='podzamok') var o='user';
	else return;
	var unic=ecom(e).id.replace(/u+/,'');
	majax('okno.php',{a:'dostup',unic:unic,value:o})
};":'');

$search=RE('search');
$n=RE0('n');

if(1*$search) {
    $pp=array(0=>array('id'=>$search));
} else {

    if($search=='') $topo="WHERE (`login`!='' AND `password`!='') OR `openid`!=''";
    elseif($podzamok && $search=='podzamok') { $topo="WHERE `admin`='podzamok'"; $nlim=2000; }
    else {
	$se="LIKE '%".e($search)."%'";
	$topo="WHERE `login` $se OR `openid` $se OR `realname` $se OR `site` $se".($admin?" OR `mail` $se":"");
    }

    if(!$n) $n=ms("SELECT COUNT(*) FROM $db_unic $topo","_l");

    $pp=ms("SELECT `id`,`login`,`openid`,`realname`,`site`,`birth`,`time_reg`,`timelast`"
    .($podzamok?",`mail`,`admin`,`ipn`,`capchakarma`":'')
    ." FROM $db_unic $topo ORDER BY `time_reg` DESC LIMIT $nskip,".($nlim));

//    dier($pp);
}

mk_okno("<center><input id='search_unic' type='text'
onchange=\"majax('okno.php',{a:'unics',search:this.value})\"
size='40' value=\"".h($search)."\">"
."<input type='submit' value='search' onclick=\"majax('okno.php',{a:'unics',search:idd(search_unic).value})\"></center><br>"
.pr_unics($pp),"Зарегистрировавшиеся посетители ($nlim с ".h($nskip).", всего ".h($n).")","a:'$a',id:'$id'"
);
}

// if(RE('onload')) otprav(''); // все дальнейшие опции будут запрещены для GET-запроса

// ========================== hiscomment ================================

if($a=='dostup') { // смена доступа
        AD();
        $u=RE0('unic');
        $v=RE('value');
 	ms("UPDATE ".$GLOBALS['db_unic']." SET `admin`='".e($v)."' WHERE `id`='$u'","_l",0);
	$p=ms("SELECT * FROM $db_unic WHERE id='$u'","_1",0); $s=pr_unics0($p);
	otprav("
idd('u".$u."').style.backgroundColor='".($p['admin']=='user'?'transparent':$podzamcolor)."';
zabil(\"u".$u."\",\"".njs($s)."\"); 
");
}



function pr_unics($pp){ global $admin,$podzamok; $s="<table style='border-bottom: 1px dotted #ccc;'>";

$s.="<tr style='background-color:#CED;text-align:center;font-size:10px;'>".($podzamok?"<td>"
."<i class='e_podzamok'></i>"
."</td>":'')
.($admin?"<td>N</td>":'')."<td>unic</td><td>login</td><td>openid</td><td>realname</td>"
.($podzamok?"<td>email</td><td>site</td>":'')."</tr>";

$k=0; foreach($pp as $p) { $k++; $s.="<tr bgcolor='"
.($p['admin']=='user'?(($k%2)?"#E0E0E0":"#D0D0D0"):$GLOBALS['podzamcolor'])
."'".($admin?" id='u".$p['id']."'":'').">".pr_unics0($p)."</tr>"; }

return $s."</table>";
}

function pr_unics0($p){ global $podzamok,$admin;

if($admin) {
$p['N']=ms("SELECT COUNT(*) FROM `dnevnik_posetil` WHERE `unic`='".$p['id']."'","_l"); if($p['N']==0) $p['N']=' ';
}

return ($podzamok?"<td style='cursor:pointer;border:1ps dotted red;' onclick=\"chzamok(this,'".$p['admin']."')\">".zamok($p['admin'])."&nbsp;</td>":'')
.($admin?"<td class=br>".h($p['N'])."</td>":'')
."<td class=ll onclick=\"majax('login.php',{action:'getinfo',unic:".$p['id']."})\">".$p['id']."</td>"
."<td>".h($p['login'])."</td>"
."<td>".($p['openid']!=''?"<a href='".h(strtr($p['openid'],'@','.'))."'>".h($p['openid'])."</a>":'&nbsp;')."</td>"
."<td>".($p['realname']!=$p['login']?h($p['realname']):'&nbsp;')."</td>"

.($podzamok?""
."<td>".($p['mail']?"<a href=\"mailto:".h($p['mail'])."\">".h($p['mail'])."</a>":"&nbsp;")."</td>"
."<td>".($p['site']?"<a href=\"".h($p['site'])."\">".h($p['site'])."</a>":"&nbsp;")."</td>"
:'');

}





function pr_rekomenda($pp){ global $admin,$podzamok; $s="<table style='border-bottom: 1px dotted #ccc;'>";

$s.="<tr style='background-color:#CED;text-align:center;font-size:10px;'>".($podzamok?"<td>"
."<i class='e_podzamok'></i>"
."</td>":'')
."<td>text</td><td>link</td></tr>";

$k=0; foreach($pp as $p) { $k++; $s.="<tr bgcolor='"
// .(@$p['admin']=='user'?
.($k%2?"#E0E0E0":"#D0D0D0")
// :$GLOBALS['podzamcolor'])
."'"
// .($admin?" id='u".$p['id']."'":'')
.">".pr_rekomenda0($p)."</tr>"
; }

return $s."</table>";
}

function pr_rekomenda0($p){ global $podzamok,$admin;

// dier($p);

return 
// ($podzamok?"<td style='cursor:pointer;border:1ps dotted red;' onclick=\"chzamok(this,'".$p['admin']."')\">".zamok($p['admin'])."&nbsp;</td>":'').
"<td>".h($p['text'])."</td>"
."<td><a href=\"".h($p['link'])."\">".h($p['link'])."</a></td>";
}





// ========================== okno ================================

function mk_okno($s,$legend,$ar='') { global $nlim,$nskip,$a,$jscripts;

$search=RE('search');

	if($search!='') {
		$ar.=",search:'".h($search)."'"; // и поиск добавить, если был
		$s=search_podsveti_body($s); // подсветить найденное
	}

	$setkey='';

$n=RE0('n'); // $n=1000;

if($nskip!=0) {
	$m="majax('okno.php',{".$ar.",n:'$n',nskip:'".($nskip-$nlim)."'})";
	$prev="<span class=l onclick=\"$m\">&lt;&lt; предыдущие ".($nlim)."</span>";
	$setkey.='setkey("left","ctrl",function(e){'.$m.'},true);';
	$setkey.='setkey("4","",function(e){'.$m.'},true);';
} else $prev='';

if($nskip<($n-$nlim)) {
	$m="majax('okno.php',{".$ar.",n:'$n',nskip:'".($nskip+$nlim)."'})";
	$next="<span class=l onclick=\"$m\">следующие ".($nlim)." &gt;&gt;</span>";
	$setkey.='setkey("right","ctrl",function(e){'.$m.'},true);';
	$setkey.='setkey("7","",function(e){'.$m.'},true);';
} else $next='';
$prevnext="<p>".mk_prevnest($prev,$next);

// idie("prevnext=[$prevnext]  # prev: $prev next: $next nlim: $nlim nskip: $nskip n: $n");

otprav("helps('okno_$a',\"<fieldset><legend>$legend</legend>".njs($prevnext.$s.$prevnext)."</fieldset>\");
posdiv('okno_$a',-1,-1); $setkey
$jscripts");

}

// ========================== hiscomment ================================

// печать ленты комментариев
function pr_comments_($sql) { $s=''; $pp=ms($sql,"_a"); $colnewcom=sizeof($pp); if(!$colnewcom) return $s;
        $s.="<b>Комментариев: ".$colnewcom
.(($n==RE0('n'))?" из ".$n:'')
."</b>";
        $tmpDate='';

        foreach($pp as $p) { $d=$p['DateID'];
	if(!isset($p['imgicourl'])) $p=get_ISi($p);

        if($tmpDate!=$d) {
		$x=ms("SELECT `Date`,`Header` FROM `dnevnik_zapisi` WHERE `num`='".$d."'","_1");
                $s .= "<p><b><a href='".getlink($x['Date']).(RE('search')?"?search=".urlencode(RE('search')):'')."'>".$x['Date']." - "
                .($x['Header']!=''?$x['Header']:"(&nbsp;)")."</b></a>";
                $tmpDate=$d;
	}

        $level=($p['Parent']!=0?'1':'0');
        $s.= comment_one($p,0,$level);
        }
        return $s;
}

function str_isearch2($search,$s){ $o=array();
	$SEARCH=strtolower2_body($search); $S=strtolower2_body($s);
	$i=-1; while(($i=strpos($S,$SEARCH,++$i))!==false) $o[]=$i;
	return $o;
}


function get_arenum0($n) { global $areans; if(!isset($areans)) $areans=array();
        $s=array_search($n,$areans); if($s!==false) return $s;
        $s=ms("SELECT `echo` FROM ".$GLOBALS['db_fido_num']." WHERE `echonum`='".e($n)."'","_l");
        if($s!==false) $areans[$s]=$n; return $s;
}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>