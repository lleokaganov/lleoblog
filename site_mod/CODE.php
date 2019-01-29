<?php /* Для постинга кода

Полезно при постингах кода.

{_code:
if(1) {
    echo 'numer 1!!!';
}
_}

*/

function CODE($e) {
    $cf=array_merge(array('lang'=>'php'),parse_e_conf($e));

	if($cf['mode']=='php') {
	    $e=highlight_string("<?php\n".$cf['body']."\n?>",1);

	    $e=preg_replace("/^(<code><span style=\"color\: \#000000\">)\s*<span style\=\"color\: \#0000BB\">\&lt;\?php<br \/><\/span>/s",'$1',$e);
	    $e=preg_replace("/<span style=\"color\: \#0000BB\">\?\&gt;<\/span>\s*(<\/span>\s*<\/code>)$/s",'$1',$e);
// <br /></span>

//	    $e=h($e);
	} else {
	$e=str_replace(
	    array('{@','@}','{','}',"\n","\xBB","\xAB",' ')
	    ,array('{_','_}','&#123;','&#125;','<br>','&quot;','&quot;',"\xA0")
	,h($cf['body']));
	}

        return "<div align=left><tt>".$e."</tt></div>";
}

?>