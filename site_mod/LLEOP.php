<?php /* произведения

{_LLEOP: [R,F] [+]2010 arhive/text.html Рассказ про заек _}
*/

STYLES("
.llR { color: red; }
.llF { color: #800080; }
.llLN { text-decoration: none; }
.llLN:hover { text-decoration: underline; }
");

//border: 1px dotted transparent; border: 1px dotted gray; 
// $GLOBALS['llcolors']=array('R'=>'red','F'=>'#800080');

function LLEOP($e) { $e=explode(' ',$e,4);
if(strlen($e[1])>4) { $a="<img src='/new.gif'>&nbsp;"; $e[1]=substr($e[1],1); } else $a='';
return "<span class='ll".$e[0]."'>".$e[1]."</span>&nbsp;".$a."<a class='llLN' href='".$e[2]."'>".$e[3]."</a>";
}

?>