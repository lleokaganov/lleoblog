<?php /* сноска в виде открывающегося окна

Эот модуль позволяет вставлять сноски в виде распахивающегося окна.

Верстка очередного фердипюкса{_SNOSKA: это слово употребляют, чтобы не произносить "творчество", ибо глупо_} потребовала модуля сносок.

Темплейт можно поменять командой:
{_SNOSKA:TEMPLATE=<span onclick="hel('{text}')" style='text-decoration:blink; position:relative;top:-5pt;left:1pt; 
vertical-align:text-top; font-weight:bold; font-size:60%; color:blue; cursor:pointer;' title="{txt}">{n}</span>_}

*/

/*
SCRIPTS("snoska","

function snoska(e,n){
	helps('snoska','<div style=br>сноска <b>'+n+'</b>:</div><div style=\"margin: 20px;\">'+e+'</div>');
//        posdiv('snoska',-1,0);
	addEvent(idd('snoska'),'click', function(){clean('snoska');});
	return false;
}

");
*/

$GLOBALS['snoska_n']=1;
$GLOBALS['snoska_template']="<span onclick=\"hel('{text}')\" style='text-decoration:blink; position:relative;top:-5pt;left:1pt; vertical-align:text-top; font-weight:bold; font-size:60%; color:blue; cursor:pointer;' title=\"{txt}\">{n}</span>";

function SNOSKA($s) {
    if(strstr($s,'TEMPLATE=')) { list(,$l)=explode('TEMPLATE=',$s,2); $GLOBALS['snoska_template']=$l; return; }

    $ss=str_replace(array("'",'"',"\n","\r"),array('&quot;','&quot;',"<br>",''),$s);
    $s="<div style='max-width:500px;text-align:justify;'>".$ss."</div>";
	if(isset($GLOBALS['rssmode'])) $s=strip_tags(str_replace("<br>"," ",$s)); // для RSS без тэгов
	return mpers($GLOBALS['snoska_template'],array('text'=>$ss,'txt'=>$s,'n'=>($GLOBALS['snoska_n']++)));
/*
	return "<span onclick=\"hel('".$ss."')\" style='text-decoration:blink; position:relative;top:-3pt; vertical-align:text-top; font-weight:bold; font-size:60%; color:blue; cursor:pointer;' title=\"".$s."\">"
.(1*$GLOBALS['snoska_n']?($GLOBALS['snoska_n']++):$GLOBALS['snoska_n'])
."</span>";
*/
}
?>