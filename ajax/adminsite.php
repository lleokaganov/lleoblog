<?php // Работа с переменными

include "../config.php";
if(isset($_GET['mjax'])||isset($_GET['lajax'])) $ajax=1;
include $include_sys."_autorize.php";

$a=RE("a"); ADH();

//=================================== album ===================================================================
if($a=='load') { ADMA();

    $sql=ms("SELECT `name` FROM `".$GLOBALS['db_site']."`".ANDC('WHERE')." ORDER BY `name`",'_a',0);

    $o="<div id='adminsitebl'>
<div id='adminsite0'>
<i class='knop e_filenew' title='New' onclick=\"majax('adminsite.php',{a:'new'})\"></i>
</div>";

	foreach($sql as $n=>$p) { $name=h($p['name']); if($name=='') $name="&lt;...&gt;";
		$o.="<div class='l' id=\"as_".$name."\" onclick=\"majax('adminsite.php',{a:'edit',n:'".$name."'})\">".$name."</div>";
	}
$o.="</div>";
	otprav("ohelpc('adminsite','AdminSite',\"".njs($o)."\");");
}
//========================
if($a=='del'){ ADMA(); $name=RE('name'); if($name=="&lt;...&gt;") $name='';
    msq("DELETE FROM `site` WHERE `name`='".e($name)."'".ANDC());
    otprav("clean('edit_text'); clean('as_".h($name)."'); salert('Delete',500);");
}
//========================
if($a=='save'){ ADH();

// salert('acn: ".$acn."',1000);

/*
// проверка на админа акаунта
// global $acn,$acc,$ADM,$unic,$admin,$mnogouser; $acn=1*RE0('acn'); $acc='';
if($mnogouser!=1) { $ADM=$admin; $acn=0; idie("ADM: ".$ADM); } // если одноюзерский
if(!$acn && $admin // если аккаунт 0 и админ
|| false!==($acc=ms("SELECT `acc` FROM `jur` WHERE `acn`='$acn' AND `unic`='$unic'","_l")) //
) { $ADM=1; idie("aaa:".$ADM); } else idie('You are not admi!!!n! acn:'.$acn." unic:".$unic);
*/

ADMA(); $name=RE('name');

$ara=array('text'=>str_replace("\r",'',RE('text')),'name'=>$name,'acn'=>$GLOBALS['acn']);
msq_add_update('site',arae($ara),"WHERE `name`='".e($name)."'".ANDC());

if(RE0('noclose')==1) otprav("salert('".LL('saved')."',500);");
else otprav("salert('".LL('saved')."',500); clean('edit_text');".($GLOBALS['mnogouser']?'':"
var d=\"as_".h($name)."\"; if(!idd(d)) {
mkdiv(d,\"".h($name)."\",'l',idd('adminsitebl'),idd('adminsite0')); otkryl(d);
idd(d).onclick=function(){ majax('adminsite.php',{a:'edit',n:'".$name."'}); };
}"));
}

//===================================================
if($a=='new') { ADMA();

otprav("ohelpc('fotoset','new name',\"Name: <input type='text' maxlength='128' id='newnamei' size='80' value='' onchange='newgo()'>"
." <input type='button' value='Edit' onclick='newgo()'>\");
idd('newnamei').focus();
newgo=function(){majax('adminsite.php',{a:'edit',n:idd('newnamei').value})};
");
}

//===================================================
if($a=='edit' && RE('n')!='') { ADMA(); $name=RE('n');
    $l=ms("SELECT `text` FROM `".$GLOBALS['db_site']."` WHERE `name`='".e($name)."'".ANDC(),'_l',0);

$s=mpers(str_replace(array("\n","\r","\t"),'',get_sys_tmp("edit.htm")),array(
    'acn'=>$acn,
    'num'=>'text',
    'name'=>$name,
    'W'=>intval($_GET['w']),
    'H'=>intval($_GET['h']),
    'editor_width'=>intval($editor_width),
    'editor_height'=>intval($editor_height),
//    'www_design'=>$www_design,
    'Body'=>$l,
    'mnogouser'=>$mnogouser,
    'ajax'=>'adminsite.php',
    'submit'=>'save')); otprav($s);
}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>