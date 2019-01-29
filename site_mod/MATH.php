<?php /* математика

Подсчет всякой всячины в числовых рядах

{_MATH: template=summ={summ} max={max} min={min} average={average} n={n}
10
11
12.4
10.325
1
3
_}
*/

function MATH($e) {
$c=array_merge(array(
'template'=>'summ={summ} max={max} min={min} average={average} n={n}',
'float'=>2 // цифр после запятой
 // average
),parse_e_conf($e));


foreach(array("|",',',' ',"\n") as $l) { if(strstr($c['body'],$l)) break; }
$sum=0; $k=0; $min=9999999999; foreach(explode($l,$c['body']) as $n) { $n=c($n); if($n=='') continue; $k++; $n=1*$n; $sum+=$n; if($min>$n) $min=$n; if($max<$n) $max=$n; }
return mpers($c['template'],array('n'=>$k,'min'=>$min,'max'=>$max,'summ'=>$sum,'average'=>round($sum/$k,$c['float'])));
}
?>