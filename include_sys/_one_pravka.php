<?php // Правки

function one_pravka($p,$answer='') { return "<div id=".$p['id']." class=po>"._one_pravka($p,$answer)."</div>"; }

function _one_pravka($p,$answer='') { global $admin;

$id=$p['id']; $prostynka = ''; $metka=$p['metka'];

if($answer=='') $answer=$p['Answer'];
if($answer!='') $answer="<div class=pct>$answer</div>";

//        $knopki .= "\n<div class=pkr onclick=\"ppo(this)\">test</div>";
        $knopki .= "\n<div class=pkr onclick='pd(this)'>да, конечно!</div>";
        $knopki .= "\n<div class=pkr onclick='pu(this)'>уговорили</div>";
        $knopki .= "\n<div class=pkr onclick='pdi(this)'>да и</div>";

        $knopki .= "\n<div class=pkl onclick='pe(this)'>EDIT</div>";
        $knopki .= "\n<div class=pkl onclick='pc(this)'>edit_c</div>";
        $knopki .= "\n<div class=pkl onclick=\"?a=ego&sc=".h($p['sc'])."\">его правки</div>";
        $knopki .= "\n<div class=pkl onclick='px(this)'>del</div>";
	$knopki .= "\n<div class=pkl onclick='pp(this)'>подробнее</div>";

        $knopki .= "\n<div class=pkg onclick='pz(this)'>так надо</div>";
        $knopki .= "\n<div class=pkg onclick='pg(this)'>грамотей</div>";
        $knopki .= "\n<div class=pkg onclick='pl(this)'>лень</div>";
        $knopki .= "\n<div class=pkg onclick='ps(this)'>спам</div>";
        $knopki .= "\n<div class=pkg onclick='pni(this)'>нет и</div>";

	$p=get_ISi($p,'#{id}');
	$Name=$p['imgicourl'];
	
/*
	$Name=h($p['login']);
	if($Name=='') $Name=h($p['Name']);
	if($Name=='') $Name=h($p['lju']);
	if($Name=='') $Name=h(substr($p['sc'],0,3));
*/

//	$page_author='LLeo';

	if($metka=='new') $modescr='pc'; elseif($metka=='submit') $modescr='pcy'; else $modescr='pcn';

if($p['stdprav']=='no value') $p['stdprav']="no value:\n".$p['text']."\n".$p['textnew'];

return "
<div class=pkk>".$knopki."</div>
<div class=$modescr>".(empty($p['img'])?'':"<img src='".h($p['img'])."' align=left>")."
	<div class=ptime>".$p['DateTime']."</div>
	<span class=pch>".$Name."</span>
	<div class=pcc>".str_replace("\n",'<br>',$p['stdprav'])."</div>
</div>
".$answer;

}

?>