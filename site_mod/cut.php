<?php /* Спрятать под кат

Если текст короткий (в нем нет переводов строки и тэгов) - вместо него по ходу строки появится [...], если длинный (абзацы с переводами строк) - появится фраза [показать&nbsp;спрятанное] по центру.
При нажатии вместо этого появится то, что было скрыто.

Если страницы вызвана с GET-параметром ?showall - то cut не вставляется, текст открыт.

Фразу можно задать самостоятельно, указав ее в начале в квадратных скобках.

<script>function cut(e,d){e.style.display='none';e.nextSibling.style.display=d;}</script>
<style>.cut{cursor:pointer;color:blue;text-align:center;}.cut:hover{text-decoration:underline;}</style>

Вся в полосках антилопа, без полосок только {_cut:жопа_}.
Покупает тушь для глаз хитрый дядька {_cut:пидарас_}.
Непорочны и чисты завелись в кишках {_cut:глисты_}.
Словно речка воду льет, {_cut:[попробуйте угадать сами]пьяный под окном блюет_}.

*/

SCRIPTS("cut","function cut(e){e.style.display='none'; e=e.nextSibling; e.style.display=e.tagName=='DIV'?'block':'inline';}");
STYLES("cut",".cut,.cutnc{cursor:pointer;color:blue;}.cut{text-align:center}.cut:hover,.cutnc:hover{text-decoration:underline;}");

function cut($e) {

$conf=array_merge(array(
// 'otl'=>0,
// 'center'=>0,
'if'=>'',
'txt'=>"[...]",
'text'=>"[показать&nbsp;спрятанное]",
'template_div'=>"<div class='{cut}' onclick='cut(this)'>{click}</div><div style='display:none'>{text}</div>",
'template_span'=>"<span class='{cut}' onclick='cut(this)'>{click}</span><span style='display:none'>{text}</span>",
'template'=>''
),parse_e_conf($e)); $e=$conf['body'];

    if(stristr($e,'#nocenter#')) { $cut='cutnc'; $e=str_ireplace('#nocenter#','',$e); } else $cut='cut';

    if(preg_match("/^\s*\[(.*?)\]([^\]].*?)$/si",$e,$m)) { $e=c($m[2]); $click=$m[1]; }

    if(isset($_GET['showall']) || ($conf['if']!=''&&isset($GLOBALS[$conf['if']])) ) return $e;


    if(isset($GLOBALS['PUBL'])) $conf['template_div']=$conf['template_span']="[ ТЕКСТ ПОД КАТОМ: Доступен только в оригинальной заметке на сайте ]";

    if($conf['template']!='') $tmpl=$conf['template'];
    else {
	if( strstr($e,"\n")
	||stristr($e,'<p')
	||stristr($e,'<div')
	||stristr($e,'<center')
	) { $tmpl=$conf['template_div']; if(!isset($click)) $click=$conf['text']; }
	else { $tmpl=$conf['template_span']; if(!isset($click)) $click=$conf['txt']; }
    }

    return mpers($tmpl,array('cut'=>$cut,'text'=>$e,'click'=>$click));
}

/*

	if(stristr($e,'#nocenter#')) { $cut='cutnc'; $e=str_ireplace('#nocenter#','',$e); } else $cut='cut';

	if(preg_match("/^\s*\[(.*?)\]([^\]].*?)$/si",$e,$m)) { $e=c($m[2]); $text=$m[1]; }

if(isset($_GET['showall'])) return $e;

	if(strstr($e,"\n")||stristr($e,'<p')||stristr($e,'<div')||stristr($e,'<center')) { if(!isset($text)) $text="[показать&nbsp;спрятанное]"; $tag="div"; $display="block"; }
	else { if(!isset($text)) $text="[...]"; $tag="span"; $display="inline";	}

// if($GLOBALS['admin']) return h("<$tag class=".$cut." onclick=\"cut(this,'$display')\">$text</$tag><$tag style='display:none'>$e</$tag>");
	return "<$tag class=".$cut." onclick=\"cut(this,'$display')\">$text</$tag><$tag style='display:none'>$e</$tag>";

*/

?>