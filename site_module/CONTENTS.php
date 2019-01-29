<?php // Отображение статьи с каментами - дата передана в $Date

if($GLOBLAS['ttl']) $GLOBLAS['ttl']=60*10;

// $GLOBALS['admin']=$GLOBALS['podzamok']=$GLOBALS['adm']=$GLOBALS['ADM']=0;

function CONTENTS($e) { global $admin,$podzamok,$count;
$conf=array_merge(array('n'=>'60'),parse_e_conf($e));
$GLOBALS['contents_n']=$conf['n'];

$opt=array(
	''=>"по дате",
	'rating'=>"по посещениям",
//	'mudoslov'=>"все нецензурные",
//	'nemudoslov'=>"все цензурные",
//	'mudoslov_rating'=>"рейтинг нецензурных",
//	'nemudoslov_rating'=>"рейтинг цензурных"
);
if($podzamok) $opt=array_merge($opt,array('invis'=>"подзамочные заметки",'count'=>"счетчики посещений"));
if($admin) $opt=array_merge($opt,array('invis_adm'=>"совсем скрытые заметки"));

$o="<p><center><p>Найти: <INPUT type='text' style='color: #777777' size='7' value='поиск' onclick=\"majax('okno.php',{a:'search'})\">
<p><FORM METHOD=get ACTION='".$GLOBALS['mypage']."'>"
."<i>Показать:</i> ".selecto('mode',$_GET['mode'],$opt,
" onchange='for(var i=0;i<this.length;i++)if(this.options[i].selected){top.window.location=\"?mode=\"+this.options[i].value;break;}'")
."</form></center>";

// if($ADM && ) $o.="<center><p class=br><a href=\"javascript:majax('editor.php',{a:'newform',hid:hid,Access:'all',template:'blank',Header:'заглавная страница'})\">\">создать index.htm</a></center>";

//===========================================================
$g=@$_GET['mode'];
$count=@intval($_GET['count']);
if(!$count&&$g=='count') $count=1;


// $sss="SELECT z.`num`,z.`Date`,z.`Header`,z.`view_counter` as `count`,z.`Access` FROM `dnevnik_zapisi` as z ";

function swhe($s) { global $count; return str_replace("{whe}",$s,
"SELECT z.`num`,z.`Date`,z.`Header`,z.`view_counter` as `count`,z.`Access`,count(*) as `count2`
FROM `dnevnik_zapisi` as z"
.($GLOBALS['podzamok']&&$count?" left join `dnevnik_posetil` as r on z.`num`=r.`url`":"")
." {whe}
group by z.`num` "); }

if($g=='') $o.=pr_zapisi(swhe(WHERE(andcc()))."ORDER BY z.`Date` DESC LIMIT ".$GLOBALS['contents_n'],true);
if($GLOBALS['podzamok'] && $g=='count') $o.=pr_zapisi(swhe(WHERE(andcc()))."ORDER BY z.`Date` DESC LIMIT ".$GLOBALS['contents_n'],true);
if($g=='more') $o.=pr_zapisi(swhe(WHERE(andcc()))."ORDER BY z.`Date` DESC");
if($g=='rating') {
    $o.=pr_zapisi_rating( // swhe(WHERE(" @@@@@@ ".andcc()))
    swhe(WHERE(andcc()." AND `DateDatetime`>".mktime(0,0,0,03,01,2010)))
    ."ORDER BY `count` DESC");
}
if($admin && $g=='invis_adm') $o.=pr_zapisi(swhe(WHERE(andcc()." AND `Access`='admin'"))."ORDER BY z.`Date` DESC");
if($podzamok && $g=='invis') $o.=pr_zapisi(swhe(WHERE(andcc()." AND `Access`='podzamok'"))."ORDER BY z.`Date` DESC");

if($g=='mudoslov') $o.=pr_zapisi(swhe(WHERE(andcc()." AND (".mudos('LIKE','OR').")"))." ORDER BY z.`DATE` DESC");
if($g=='mudoslov_rating') $o.=pr_zapisi_rating(swhe(WHERE(andcc()." AND (".mudos('LIKE','OR').")")) );
if($g=='nemudoslov') $o.=pr_zapisi(swhe(WHERE(andcc()." AND (".mudos('NOT LIKE','AND').")"))." ORDER BY z.`DATE` DESC");
if($g=='nemudoslov_rating') $o.=pr_zapisi_rating(swhe(WHERE(andcc()." AND (".mudos('NOT LIKE','AND').")")));
// if(substr($g,0,3)=='st:') { $o.=pr_zapisi($sss."WHERE `Header` LIKE '".e(substr($g,3))."%' ORDER BY `Date` DESC"); }
return $o;
}

function andcc() { return ($GLOBALS['mnogouser']!=1?'1=1':"z.`acn`='".$GLOBALS['acn']."'"); }

function pr_zapisi($sq,$more=false) { global $count,$colnewcom,$contents_n;
	return pr_zapisi_($sq)
	.($more && ($colnewcom >= $contents_n)?"<p><a href='".$GLOBALS['mypage']."?mode=more".h($count?"&count=1":'')."'>показать больше &gt;&gt;</a>":'');
}

function mudos($like,$or) {
	$ara=file($GLOBALS['host_design'].'mudoslov.txt');
	$a=''; foreach($ara as $m) { $m=trim($m); if($m!='') $a.="z.`Body` $like '%".$m."%',"; }
	return str_replace(',',"\n".$or." ",trim($a,','));
}

//=========================================================================================================
function pr_zapisi_($sq) { global $count,$numos,$colnewcom;

	$s=''; $year=0;
	$sql=ms($sq,"_a"); $s.=$GLOBALS['msqe'];
	$colnewcom=sizeof($sql); if(!$colnewcom) return $s;
	$s.="<h2>Заметок найдено: ".$colnewcom."</h2>";
	$s.="<ul>";

	if($_GET['mode']=='count') unset($_GET['mode']);
	unset($_GET['count']);
	$get=getget();
//	$get=rtrim(str_replace("mode=count","?&",$get));

	foreach($sql as $p) {

$panel=($GLOBALS['ADM']?"<i class='knop e_kontact_journal' onclick=\"majax('editor.php',{a:'editform',num:".$p['num']."})\"></i>&nbsp;":'');

			// $p["count"]+=$p['count2'];
			// $p["counter"]=get_counter($p);
			$Date=$p["Date"];
			$head=($p["Header"]?" - ".h($p["Header"]):"");

		if(preg_match("/^(\d\d\d\d)\/(\d\d)\/(\d\d.*)$/si",$Date,$m)) { $Y=$m[1]; $M=$m[2]; $D=$m[3];
			if($Y!=$year) { if($year) $s.="</ul>"; $s .= "<h2>".$Y." год</h2><ul>"; $year = $Y; }
			$detail=($numos?" ".$numos++.". ":'');
			$z=$detail."<a href='".getlink($Date).$get."'>$M-$D"
.($count?" (".($p["count"]+$p['count2']).")":'')
// /* lleo */ .(!$GLOBALS['podzamok']?'':" (".$p["count"]."+".$p['count2']."=".($p["count"]+$p['count2']).")")
." ".$head."</a>";
			$z = zamok($p['Access']).$z;
			$s.="\t<li>".$panel.$z."</li>";

		} else {
			$z=$detail."<a href='".getlink($Date).$get."'>".$Date
.($count?" (".($p["count"]+$p['count2']).")":'')
// /* lleo */ .(!$GLOBALS['podzamok']?'':" (".$p["count"]."+".$p['count2']."=".($p["count"]+$p['count2']).")")
." ".$head."</a>";
			$z = zamok($p['Access']).$z;
			$s.="<br>".$panel.$z;
		}
	}

	$s .= "</ul>";
	return $s;
}

//=========================================================================================================
function pr_zapisi_rating($l) {	$pp=ms($l,"_a");
	$ray=array(); foreach($pp as $n=>$p) { $c=$p['count']+$p['count2']; $pp[$n]["counter"]=$c; $ray[$n]=$c; } arsort($ray);
	
	$s = $GLOBALS['msqe'];
	$s .= "<h2>Записей ".sizeof($pp)." (сортировка по числу посетителей)</h2>";
	if($GLOBALS['old_counter']) $s.="<font color=red size=1>Учтите, что с февраля 2010 в движке подсчитываются не показы заметки, а реальные посетители, это число в несколько раз меньше, поэтому в рейтинге участвуют только заметки с марта 2010.</font>";
	$s .= "<p><table>";

	$get=getget(); $i=0; foreach($ray as $n=>$l) { $p=$pp[$n];
		$s.= "<tr><td align=right>".(++$i).".</td><td><b>".$l."</b></td>"
		."<td><small>".zamok($p['Access'])."<a href='".getlink($p['Date']).$get."'>"
		.$p['Date'].($p["Header"]?" - ".h($p["Header"]):"")
		."</a></small></td></tr>";
	}

	$s.="</table>";
	return $s;
}

//=========================================================================================================

function getget() { $ge=$_GET;
	if(isset($ge['mode']) && $ge['mode']=='more') unset($ge['mode']);
	if(!sizeof($ge)) return '';
	$s="?"; foreach($ge as $a=>$b) $s.=urlencode($a)."=".urlencode($b)."&";
	return trim($s,"&");
}

?>