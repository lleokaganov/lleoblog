<?php /* простроение круговых диаграмм

text - заголовок графика
series - имя серии
all - общий максимум, по умолчанию 0 - вычислять автоматически
other - как назвать оставшуюся сущность, если она не указана при помощи * - по умолчанию 'other'
format - формат подсказки, по умолчнию: "<b>{point.name}</b>: {point.percentage:.1f} %"
template - формат имени, по умолчанию: "{name}"

{_GRAF:
1% По опросам респондентов в самых разных городах,
27% в среднем двадцать семь процентов лгут в анкетах от стыда.
30% Тридцать — лгут, поскольку надо.
20% Двадцать — просто мудаки.
* Остальное сам Левада заполняет от руки.
_}

---

{_GRAF:
text=Мой дневник посетили владельцы операционных систем
series=Посетителей
all=15000
template={name} ({n} из {all})

10232 Windows
813 MacOS
212 Linux
1123 Android
731 iOS
* неопределенные
_}



*/


$GLOBALS['grafn']=0;

function GRAF($e) { global $grafn;

$conf=array_merge(array(
'text'=>'',
'series'=>'',
'all'=>0,
'other'=>'other',
'format'=>"<b>{point.name}</b>: {point.percentage:.1f} %",
'template'=>"{name}"
),parse_e_conf($e));

$other=$conf['other'];

$summ=0; $perc=0; $dat=array();
foreach(explode("\n",$conf['body']) as $l) { if(c0($l)=='' || !strstr($l,' ')) continue;
    list($n,$name)=explode(" ",$l,2);
    if($n=='*') { $other=$name; continue; }
    if(strstr($n,'%')) { $perc=1; $n=str_replace("%",'',$n); } $n=1*c($n); // если хоть одно было указано в процентах - считать всё в процентах
    $summ+=$n; // суммируем все значения
    $dat[]="{name:'".mpers($conf['template'],array('name'=>$name,'n'=>$n,'all'=>$conf['all']))."',y:".$n."}";
}

$all=($perc?100:($conf['all']?$conf['all']:$summ)); // шо у нас за общий максимум

// if($GLOBALS['acc']=='lleo') return "all: $all, summ: $summ";

if($summ<$all) $dat[]="{name:'".mpers($conf['template'],array('name'=>$other,'n'=>($all-$summ),'all'=>$conf['all']))."',y:".($all-$summ)."}"; // если надо добавить остаток

$dat=implode(",",$dat);

$id="graf_".($grafn++);

$s="
$(function() {
    $('#".$id."').highcharts({
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
	title: { text: '".$conf['text']."' },
        tooltip: { pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>' },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '".$conf['format']."',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                }
            }
        },
        series: [{
            name: '".$conf['series']."',
            colorByPoint: true,
            data: [".$dat."]
        }]
    });
});
";

/*
{name:'Microsoft Internet Explorer',y:56.33},
{name:'Chrome',y:24.03, sliced: true, selected: true },
{name:'Firefox',y:10.38 },
{name:'Safari',y:4.77 },
{name:'Opera',y:0.91 },
{name:'Proprietary or Undetectable', y: 0.2 }
*/

SCRIPT_ADD($GLOBALS['wwwhost']."extended/graf/jquery-1.7.2.min.js");
SCRIPT_ADD($GLOBALS['wwwhost']."extended/graf/hs_highcharts.js");
SCRIPT_ADD($GLOBALS['wwwhost']."extended/graf/hs_exporting.js");
SCRIPT_ADD($GLOBALS['wwwhost']."extended/graf/hs_sand-signika.js");

return "<div id='".$id."' style='min-width: 310px; height: 400px; margin: 0 auto'></div>"."<script>".str_replace("\n",'',$s)."</script>";
}
?>