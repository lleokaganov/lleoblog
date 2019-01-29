<?php /* DAT

Удобное создание сущностей по темплейту. В каждой строке параметры разделяются |, первый
параметр называется {0}, второй {1} и т.д. Каждая строка заменяется на темплейт.

Опция {n} заменяется на порядковый номер данного элемента, опции вида {n2}, {n3} и т.п. - на порядковый номер с N нулей в начале (001, 0001)

{_DAT: template=\n<p>{n2}. <a href='/dnevnik/{1}.html'>{1} ? {2}</a><br>{@MP3: http://lleo.me/audio/f5/{0}@}
facebook.mp3	| 2011/10/17 | Ода социальным сетям
konoplya.mp3	| 2011/10/03 | Ода газетным новостям
china.mp3	| 2011/09/26 | Ода про рис и репу
shlagbaum.mp3	| 2011/09/19 | Ода шлагбауму
_}
*/

function SELECT($e) {
    $c=array_merge(array(
	    'select'=>''
    ),parse_e_conf($e));
    // $c['template']=str_replace("\\n","\n",$c['template']);
    // $s.=mper($c['template'],$a);

	$sels=0; $r=array(); foreach(explode("\n",$c['body']) as $l) { if(empty($l))continue;
		$sel=0;
		if(strstr($l,'|')) {
		    list($value,$text)=explode('|',$l,2);
		    if(strstr($text,'|')) { list($text,)=explode('|',$text,2); $sel=1; $sels++; }
		} else { $value=$text=$l; }
		$r[]=array($value,$text,$sel);
	}
	if($sels>1) return "<font color=red>SELECT: Error selected; ".$sels."</font>";
	if(!$sels) $r[0][2]=1;

	$o=''; foreach($r as $p) $o.="<option value=\"".$p[0]."\" ".($p[2]?" selected":'').">".$p[1]."</option>";
	return "<select".($c['select']==''?'':" ".$c['select']).">".$o."</select>";
}
?>