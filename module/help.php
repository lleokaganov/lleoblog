<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй
// сравни - чи срав, чи ни

//	ini_set("display_errors","1");
//	ini_set("display_startup_errors","1");
//	ini_set('error_reporting', E_ALL); // включить сообщения об ошибках

$_PAGE = array('design'=>file_get_contents($GLOBALS['host_design']."plain.html"),
'header'=>"HELP",
'title'=>"Примеры работы тэгов Binoniq",
'www_css'=>$www_css,
'www_design'=>$www_design,
'admin_name'=>$admin_name,
'httphost'=>$httphost,
'wwwhost'=>$wwwhost,
'wwwcharset'=>$wwwcharset,
'signature'=>$signature
);

function hertam($s) {
    $s=h($s);
    $s=str_replace(array('{','}'),array('&#123;','&#125;'),$s);
    $s=c0($s);
    return nl2br($s);
}

$s="<div style='margin-left:10%;margin-right:10%'>";

$k=0;

    $inc=array(); foreach(explode(' ',



'B BC STIH GRAF'








) as $l) $inc[]=$GLOBALS['filehost']."site_mod/".$l.".php";
    foreach(glob($GLOBALS['filehost']."site_mod/*.php") as $l) if(!in_array($l,$inc)) $inc[]=$l;



 $ainc=array(); foreach($inc as $l) { if($k++ > 7) break;
//	echo " `$l` ";
	$t=file_get_contents($l);
	$l=preg_replace("/^.*?\/([^\/]+)\.php$/si","$1",$l);

	if(preg_match("/\/\*(.*?)\*\//si",$t,$m)) { $head=$prim=$t='none';
		$t=c($m[1]);
		if(preg_match("/^([^\n]+)\n(.*?)$/si",$t,$m)) { $head=$m[1]; $t=c($m[2]); }
		if(preg_match("/(.*?)\n([^\n]*\{\_.*?)$/si",$t,$m)) { $t=c($m[1]); $prim=c($m[2]); } /*}*/

	$s.="<p class=z>".h($l)."<br><span class=r>".$head."</span></p>";

	$s.="<p class=r>".$t."</p>";

    if(strstr($prim,"---")) $pr=explode("---",$prim); else $pr=array($prim);
    foreach($pr as $prim) $s.="".$prim."
<p class=r>{_cut: [показать код]
<p>{_BC: @pre
".hertam($prim)."_}
_}";

    }
}

$s=modules($s);

print $s;


// die($s."</div>");

?>