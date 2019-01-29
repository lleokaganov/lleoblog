<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй

// Deprecated: preg_replace(): The /e modifier is deprecated, use preg_replace_callback instead in /var/www/home/dnevnik/include_sys/_podsveti.php on line 24

$GLOBALS['start_tag'] = array('',"<span class=p1>","<span class=p2>");
$GLOBALS['end_tag'] = array('',"</span>","</span>");

$GLOBALS['stage']=array(); foreach($GLOBALS['start_tag'] as $l) $GLOBALS['stage'][]=str_replace('/','\/',$l); // для прега
$GLOBALS['etage']=array(); foreach($GLOBALS['end_tag'] as $l) $GLOBALS['etage'][]=str_replace('/','\/',$l); // для прега


// ЭТО КАКАЯ-ТО ЖОПА, Я ДАЖЕ БОЮСЬ ТУТ ВСЕРЬЕЗ КОПАТЬСЯ, ОТЛАДИЛ КОГДА-ТО, РАБОТАЕТ - И ЛАДНО

function podsvetih($txt) {
	$a=array("<span style='color:gray; text-decoration:line-through;'>","<span style='background-color:#FFC8C8;'>");
	$b=array('<span class=p1>','<span class=p2>');
	return str_replace($b,$a,$txt);
}

function remas($txt) {
	$specs=str_split(' !_'.chr(151).',.:;-+?()/\\\'"'."\n");
	//explode('w',' w!w_w'.chr(151).'w,w.w:w;w-w+w?w(w)w/w\\w\'w"w'."\n");

	foreach($specs as $c) $txt=str_replace($c,"\1".$c,$txt);
//	$txt=preg_replace("/(&lt;.+?&gt;)/e",'nehtml("$1")',$txt);
	$txt=preg_replace_callback("/\&lt\;.+?\&gt\;/si",'nehtml1',$txt);
	return($txt);
}

function nehtml1($s) {
// die('###'.$s);
return str_replace("\1",'',$s[0]); }

// function nehtml($s) { return str_replace("\1",'',$s); }


function podsveti($oldtxt,$newtxt,$modr='') { global $start_tag,$end_tag,$PODSV_do,$PODSV_po;

if($oldtxt=='' || $newtxt==$oldtxt) return $newtxt;
if($newtxt=='') return $oldtxt;

        if($modr=='') {
                $oldmas=explode("\1",remas($oldtxt));
                $newmas=explode("\1",remas($newtxt));
        } else {
                $oldmas=explode($modr,$oldtxt);
                $newmas=explode($modr,$newtxt);
        }

	$diff=PHPDiff($newmas,$oldmas);

$newbuf=$s=''; $o=$n=$oldi=0; $max=sizeof($diff);

for($i=0;$i<$max;$i++) { $di=$diff[$i];
	if($oldi!=$di) { $s .= $end_tag[$oldi].$start_tag[$di]; } $oldi=$di; // закрыть прошлый тэг, если тэг менялся
	if(!$di) { $s .= $oldmas[$o++]; $n++; } // 0=оба без изменений
	elseif($di==1) $s .= $newmas[$n++]; // str_replace('-','_',$newmas[$n++]); // 1=вычеркнут (и минус пометить от зачеркиваний)
	else $s .= $oldmas[$o++]; //str_replace('-','_',$oldmas[$o++]); // 2=вставлен (и минус пометить от зачеркиваний)
} $s .= $end_tag[$oldi];

$PODSV_do=$PODSV_po='';
$j=$max; while($diff[--$j]==0) { } // найти место, где начинаются последние нули
$i=0; while($diff[$i]==0 && $i<$j) $PODSV_do.=$newmas[$i++];
$sn=sizeof($newmas); for($i=$sn-($max-$j)+1;$i<$sn;$i++) $PODSV_po.=$newmas[$i];

return($s);
}


function PHPDiff($t1,$t2) {	// $old,$new) 

//   # split the source text into arrays of lines
//   $t1 = explode("\n",$old);
   $x=array_pop($t1);
   if ($x>'') $t1[]="$x\n\\ No newline at end of file";
//   $t2 = explode("\n",$new);
   $x=array_pop($t2);
   if($x>'') $t2[]="$x\n\\ No newline at end of file";


//   # build a reverse-index array using the line as key and line number as value
//   # don't store blank lines, so they won't be targets of the shortest distance
//   # search
   foreach($t1 as $i=>$x) if($x>'') $r1[$x][]=$i;
   foreach($t2 as $i=>$x) if($x>'') $r2[$x][]=$i;

   $a1=0; $a2=0;   // start at beginning of each list
   $actions=array();

//   # walk this loop until we reach the end of one of the lists
   while($a1<count($t1) && $a2<count($t2)) {
//     # if we have a common element, save it and go to the next
     if($t1[$a1]==$t2[$a2]) { $actions[]=0; $a1++; $a2++; continue; }

//     # otherwise, find the shortest move (Manhattan-distance) from the
//     # current location
     $best1=count($t1); $best2=count($t2);
     $s1=$a1; $s2=$a2;
     while(($s1+$s2-$a1-$a2) < ($best1+$best2-$a1-$a2)) {
       $d=-1;
       foreach((array)@$r1[$t2[$s2]] as $n)
         if ($n>=$s1) { $d=$n; break; }
       if ($d>=$s1 && ($d+$s2-$a1-$a2)<($best1+$best2-$a1-$a2))
         { $best1=$d; $best2=$s2; }
       $d=-1;
       foreach((array)@$r2[$t1[$s1]] as $n)
         if ($n>=$s2) { $d=$n; break; }
       if ($d>=$s2 && ($s1+$d-$a1-$a2)<($best1+$best2-$a1-$a2))
         { $best1=$s1; $best2=$d; }
       $s1++; $s2++;
     }
     while ($a1<$best1) { $actions[]=1; $a1++; }  // deleted elements
     while ($a2<$best2) { $actions[]=2; $a2++; }  // added elements
  }

//  # we've reached the end of one list, now walk to the end of the other
  while($a1<count($t1)) { $actions[]=1; $a1++; }  // deleted elements
  while($a2<count($t2)) { $actions[]=2; $a2++; }  // added elements

  return $actions;
}

//=============================== разные вспомогашки ==========================
//*********************************************************************************************************************************
// "_podsveti.php"; // процедура Diff - кривая сука, страшная, но работает

function otido($txt,$a,$b) { $n=strlen($txt); if($a<0) $a=0; if($b>$n) $b=$n; if($b<=$a) return ''; return substr($txt,$a,$b-$a); }

function vhtm($txt) { return str_replace(array('&amp;','&quot;','&#039;','&lt;','&gt;'),array('&','"','\'','<','>'),$txt); }

function std_pravka($textnew,$text,$oldtxt,$dopo=50) { global $PODSV_do,$PODSV_po;
	$podsvetka=podsveti($textnew,$text);
	if(($pos=strpos($oldtxt,$text))===false) return 'no value';
	$pos+=strlen($PODSV_do); // нашли позицию ИЗМЕНЕННОГО куска
	$do=h(otido($oldtxt,$pos-$dopo,$pos));
	$pos2=$pos + strlen($text) - strlen($PODSV_do.$PODSV_po); // нашли конец ИЗМЕНЕННОГО куска
	$po=h(otido($oldtxt,$pos2,$pos2+$dopo));
	$pod = strlen($podsvetka)-strlen($PODSV_do.$PODSV_po); // длина ИЗМЕНННОГО куска с тэгами
	$psd=substr($podsvetka,strlen($PODSV_do),$pod); // вырезали ИЗМЕНЕННЫЙ кусок
	return $do.$psd.$po;
}

function pravka_bylo($txt) { global $stage,$etage;
	$txt=preg_replace("/".$stage[2]."[^<>]+".$etage[2]."/si","",$txt);
	$txt=preg_replace("/".$stage[1]."([^<>]+)".$etage[1]."/si","$1",$txt);
	return vhtm($txt);
}

function pravka_stalo($txt) { global $stage,$etage;
	$txt=preg_replace("/".$stage[2]."([^<>]+)".$etage[2]."/si","$1",$txt);
	$txt=preg_replace("/".$stage[1]."[^<>]+".$etage[1]."/si","",$txt);
	return vhtm($txt);
}

function pravka_pomen($txt) { global $stage,$etage,$start_tag,$end_tag;
	$f=array($start_tag[2],$start_tag[1],'##starttag#',$end_tag[2],$end_tag[1],'##endtag#');
	$t=array('##starttag#',$start_tag[2],$start_tag[1],'##endtag#',$end_tag[2],$end_tag[1]);
		$txt=str_replace($f,$t,$txt); // поменять было и стало местами
return preg_replace("/".$stage[2]."([^<>]+)".$etage[2].$stage[1]."([^<>]+)".$etage[1]."/si",
$start_tag[1]."$2".$end_tag[1].$start_tag[2]."$1".$end_tag[2],$txt); // да и передвинуть местами чтоб читалось лучше
}

function pravka_stdprav($p,$n) { global $start_tag,$end_tag;
	$stdprav=std_pravka($p['textnew'],$p['text'],$p['oldtxt'],$n);
	if($stdprav=='no value') { $stdprav=pravka_pomen(std_pravka($p['text'],$p['textnew'],$p['oldtxt'],$n)); }
	return $stdprav;
}
//*********************************************************************************************************************************
?>