<?php /* Вывести фотку через превьюшку

Указываем относительный адрес фотки на сайте. Предполагается, что она залита средствами движка, поэтому там же есть папка pre/, где лежит для этой фотки одноименная превьюшка.

{_FOTO: /blog/2010/05/LLeo_Vysotsky.jpg _}
*/

if(!isset($GLOBALS['bigfoto'])) $GLOBALS['bigfoto']=0;
if(!isset($GLOBALS['bigfotopart'])) $GLOBALS['bigfotopart']=0;

function FOTO($e) { global $bigfoto,$bigfotopart;
                list($img,$txt)=explode(" ",$e,2); $img=c($img); $txt=c($txt);

                if(!strstr($img,',')) $epre=preg_replace("/^(.*?)\/([^\/]+)$/si","$1/pre/$2",$img);
                else list($img,$epre)=explode(',',$img);
	return "<a id='bigfot".$bigfotopart."_".$bigfoto."' href=\"".h($img)."\" onclick='return bigfoto(".$bigfoto.",".$bigfotopart.")'><img src=\"".h($epre)."\" border=0></a>"
	.($GLOBALS['admin']?"<div style='display:none' id='bigfotnum".$bigfotopart."'>".$GLOBALS['article']['num']."</div>":'')
	."<div class=r id='bigfott".($bigfotopart++)."_".($bigfoto++)."'>".($txt!=''?$txt:'')."</div>";
}

?>