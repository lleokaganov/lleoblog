<?php // [calendar] - квадратный календарик

function CALENDAR($e) { global $article;
        return ($article["Prev"].$article["Next"]!=''?get_Calendar($article["Year"], $article["Mon"], $article["Day"]):'');
}

function get_Calendar($year, $mon, $day = false) { global $admin, $wwwhost, $months, $podzamok; $s = "";

//	$year=

        if(intval($year)==0) return '';

        $ttl=($admin?0:$GLOBALS["ttl"]*10); // для календаря - десятикратное время пребывания в кэше

        $m = mktime(1, 1, 1, $mon, 1, $year); // старт месяца
        $k = date("w",$m)-1; if($k<0) $k=6; // день недели первого числа месяца
        $end = date("t",$m); // дней в этом месяце
        $now = date("Y/m/d"); // сегодняшняя дата

        // выбрать существующие заметки месяца
$sql = ms("SELECT `DateDate`,`Date`,`Access` FROM `dnevnik_zapisi` ".WHERE("`DateDate`>='".$m."' AND
`DateDate`<'".($m+($end-1)*86400)."'")." ORDER BY `DateDate`","_a",$ttl);

        $a=array(); foreach($sql as $p) { $i=intval(substr($p['Date'],8,2)); $a[$i]=array($p['Access'],$p['Date'],++$a[$i][2]); }

        $Prev=$sql[0]['Prev']; if($Prev!='') $Prev="<a href='".$wwwhost.$Prev.".html'>&lt;&lt;</a>";
        elseif($admin) $Prev="<a href='".$wwwhost.date("Y/m",$m-60*60*24)."'>&lt;&lt;</a>";
        $Next=$sql[sizeof($sql)-1]['Next']; if($Next!='') $Next="<a href='".$wwwhost.$Next.".html'>&gt;&gt;</a>";
        elseif($admin) $Next="<a href='".$wwwhost.date("Y/m",$m+$end*60*60*24)."'>&gt;&gt;</a>";

$s .= "<table border=0 cellspacing=0 cellpadding=1>
<tr><td class=cld_top>".$Prev."</td><td colspan=5 align=center class=cld_top>".$months[intval($mon)]." ".intval($year)
."</td><td align=right class=cld_top>".$Next."</td></tr>
<tr><td class=cld_days>ПН</td><td class=cld_days>ВТ</td><td class=cld_days>СР</td><td class=cld_days>ЧТ</td>"
."<td class=cld_days>ПТ</td><td class=cld_red><b>СБ</b></td><td class=cld_red><b>ВС</b></td></tr>";

        if($k) { $s.="<tr>"; for($i=0;$i<$k;$i++) $s.="<td class=".($i>4?"cld_red":"cld").">&nbsp;</td>"; } // проставить пустые клетки

        for($i=1; $i<=$end; $i++) {
                if(!$k) $s .= "<tr>";
                $d=sprintf("%04d/%02d/%02d",$year,$mon,$i);
                $style=($d==$now?" style='background-color: #FFFFa0; border: red solid 1px;'":'');
                $di=$i;
                if(!($x=$a[$i][0])) { if($admin) $di="<a class=cld_ed href='".$wwwhost."editor/?Date=".urlencode($d)."'>".$i."</a>";
                } else {
                        if($x=='podzamok') $di="<s>".$di."</s>";
                        elseif($x=='admin') $di="<s><i>".$di."</i></s>";
                        if($a[$i][2]>1) $di="<b>$di</b>";
                        $di="<a href='".$wwwhost.$a[$i][1].".html'>".$di."</a>";
                }
                $s .= "<td class=".($k>4?"cld_red":"cld").$style.">".$di."</td>";
                if($k==6) $s .= "</tr>"; if(++$k>6) $k=0;
        }

return $s."</table>";
}


?>