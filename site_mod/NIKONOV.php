<?php // ќтображение всего фотоальбома или избранных

function NIKONOV($e) {

$c=array_merge(array(
'time'=>86400*8,
'tag'=>0, // 0 - search по Header, 1 - search по tag
'search'=>"\"majax('search.php',{a:'header',search:'{domain}'})\"",
'broken'=>"<p><font color=red><i>≈сли вы видите этот текст здесь целиком, значит, специально настронный робот определил, что сайт"
." <a href='{url}'>{domain}</a>, дл€ которого написан этот материал, оп€ть сломалс€. —егодн€шн€€ причина - {case}."
." Ёто происходит регул€рно потому, что так уж устроен тот сайт и его админы.</i></font>",
'nemalo'=>"<p><div style='border: 1px dotted black; margin-left:15%; margin-right:15%; padding:10pt; font-size: 12pt;'>"
."Ётот текст написан дл€ проекта <a href='http://{domain}'>{domain}</a>, где € веду авторскую колонку. ¬ообще дл€ {domain} € написал немало подобных "
."материалов, <span class=l onclick={search}>вот их полный список</span></div>",
'continue'=>"<p><center><a href='{url}'>...читать полный текст: {url}</a></center>"
),parse_e_conf($e));

// dier($c);

    if($c['tag']) $c['search']="\"majax('search.php',{a:'tag',tag:'{domain}'})\"";

	list($url,$text)=explode("\n",ltrim($c['body'],"\n"),2); $c['url']=c($url); $text=c($text);

	if(isset($GLOBALS['nikonov_no_epilog'])) return $text; // ≈сли установлен спецфлажок - вернуть целиком.
	$a=parse_url($url); $a=explode('.',$a['host']); $c['domain']=h($a[sizeof($a)-2].'.'.$a[sizeof($a)-1]);

	if( time() > (strtotime(substr($GLOBALS['article']['Date'],0,10)) + $c['time']) ) return $text.mper($c['nemalo'],$c); // если больше недели - не замен€ть

	$flag=$GLOBALS['hosttmp'].rpath($domain).".flag"; if(file_exists($flag)) { $c['case']=fileget($flag); return $text.mper($c['broken'],$c); }

	return mper($c['continue'].$c['nemalo'],$c);
}

?>