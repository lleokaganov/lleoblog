<?php /* Простой тестик

Данные состоят из двух блоков, разделенных строкой "---". Сперва мы перечисляем вопросы и варианты ответов (они начинаются с -). В варианте ответа сперва указываем число - вес этого ответа (это число не выводится, а нужно при подсчете). Затем через пробел сам ответ.

Во второй части мы перечисляем (с интервалом в пустую строку) вармианты ответов по нарастающей - по сумме набранных баллов. Первым указыавется число - от скольких набранных баллов выводить этот ответ. В первом варианте ответа должно всегда стоять 0: он выдается, если набрана сумма баллов от нуля, но меньеш числа, указанного в следующем варианте ответа.

Внимание! Пример теста ниже ответ здесь выдавать не будет, потому что мне лень вставлять туда сейчаас руками все скрипты, которые вставятся туда автоматически, когда вы будете его использовать.

{_SILK_TEST:
1. Любимое время суток?
- 0 утро
- 2 день
- 4 вечер
- 5 ночь

2. Любимое время года?
- 1 зима
- 5 весна
- 4 лето
- 3 осень

---

0 Вы неудачник, вы набрали всего {sum} баллов.

3 Вы робкий и неуверенный в себе человек.

8 Вы молодец.

10 Вы - супер! У вас баллов - аж {sum}!
_}

*/

SCRIPTS("SilkTest procedure","

function check_radio(e) { for(var i=0;i<e.length;i++) if(e[i].checked) return e[i].value; return 'undefined'; }

var silktest_ara;

function silktest(n,x,ara){
	var sum=0; for(var i=1;i<=x;i++) {
		var l=check_radio(document.getElementsByName('silktest_'+n+'_'+i));
		if(l=='undefined') {
			helps('silktest_error','<fieldset><legend>ошибка</legend>Пункт '+i+' не заполнен.<br>Заполните все пункты!</fieldset>');
			posdiv('silktest_error',-1,-1); return;
		} sum=(sum+1*l);
	}

	silktest_ara=ara; ajaxon(); setTimeout('silktest_print('+sum+')',1500);
}

function silktest_print(sum){ ajaxoff();
	for(var i in silktest_ara) { if(i>sum) break; var txt=silktest_ara[i].replace(/\{sum\}/gi,sum); }
	helps('silktest_otvet','<fieldset><legend>результаты</legend><div style=\"max-width:700px;width:700px;padding:20px; line-height:1.5em;\" align=justify>'+txt+'</div></fieldset>');
	posdiv('silktest_otvet',-1,-1);
}");

function SILK_TEST($e) { global $silktest_n; $silktest_n++;
	list($vopros,$otvet)=explode("\n---",$e,2);
	$vopr=get_vopross($vopros);
	$otv=get_vopross_simple($otvet);

	$g=0;
	$s=""; foreach($vopr as $v=>$p) {
		$s.="<p><b>".c($v)."</b><ul>";
		$gr="silktest_".$silktest_n."_".++$g;

		foreach($p as $x=>$l) {
			list($n,$t)=explode(' ',$l,2);
			$s.="<label><input name='$gr' type='radio' value='".intval(c($n))."'> ".c($t)."</label><br>";
		}
		$s.="</ul>";
	}
	$s.="<p><input type=button value='Получить результат' onclick=\"silktest('$silktest_n',".sizeof($vopr).",silktest_".$silktest_n."_ara)\">";

	$c="var silktest_".$silktest_n."_ara={"; foreach($otv as $l) {
		list($x,$txt)=explode(' ',$l,2); $x=intval($x); $txt=c($txt);
		$c.=intval($x).":'".str_replace(array("&","\\","'",'"',"\n","\r"),array("&amp;","\\\\","\\'",'&quot;',"\\n",""),c($txt))."', ";
	} $c=trim($c,' ,')."};";

	SCRIPTS("silktest_".$silktest_n."_DATA",$c);

	return $s;
}

function get_vopross($s) { // распознать голосовалку
        preg_match_all("/#+\n*([^#]+)/si","#".str_replace("\n\n","#",$s),$km);
        $vopr=array(); foreach($km[1] as $m) {
		$z=trim( preg_replace("/^([^\n]+)\n.*$/si","$1",$m) );
                preg_match_all("/\n+[\s\-".chr(151)."]+([^\n]+)/si",trim($m),$v);
                if($z && sizeof($v[1])) $vopr[$z]=$v[1];
        }
        return $vopr;
}

function get_vopross_simple($s) { preg_match_all("/#+\n*([^#]+)/si","#".str_replace("\n\n","#",$s),$km); return $km[1]; } // распознать ответы

?>