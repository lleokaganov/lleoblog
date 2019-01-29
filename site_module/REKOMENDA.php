<?php //рекоменда

if(!function_exists('h')) { include_once "../config.php"; include_once $include_sys."_autorize.php"; }
if(!isset($GLOBALS['db_rekomenda'])) $GLOBALS['db_rekomenda']='rekomenda';

if(isset($_GET['p'])) {
	if(!isset($GLOBALS['rekomenda_pass'])) $GLOBALS['rekomenda_pass']='modetime';
	if($_GET['p']!=$GLOBALS['rekomenda_pass']) { header("Content-Type: image/png"); die(file_get_contents("../design/not_found.gif")); }

	$text=h(preg_replace("/[\n\r\t ]+/si"," ",uw($_GET['t'])));
	$link=h($_GET['l']);
	if($link!='') {
	        if($text=='') $text=$link;
	        msq_add_update($GLOBALS['db_rekomenda'],array('link'=>e($link),'text'=>e($text)),'link');
	}

//	idie('WWW3');

header("Content-Type: image/png"); die(file_get_contents("../design/re.png"));
}


function REKOMENDA_ajax() { ADMA(); $a=RE('a'); $n=RE0('n'); // авторизация по логину-паролю

if($a=='edit') {
	$p=ms("SELECT `link`,`text` FROM ".$GLOBALS['db_rekomenda']." WHERE `n`='".$n."'","_1",0);
	return "ohelpc('rekomenda_edit','Rekomenda item #".$n."',\"".njsn("

<form onsubmit=\"return send_this_form(this,'module.php',{mod:'REKOMENDA',a:'edit_',n:".$n."})\">
<input type='text' name='link' size='80' value=\"".h($p['link'])."\">
<br><textarea name='text' cols=60 rows=4>".h($p['text'])."</textarea>
<br><input type='submit' value='Save'></form>

")."\")";
}

if($a=='edit_') {
	$text=trim(preg_replace("/[\n\r\t ]+/s"," ",RE('text')));
	$link=trim(preg_replace("/[\n\r\t ]+/s"," ",RE('link')));
	if($link!='') {
		msq_update($GLOBALS['db_rekomenda'],array('text'=>e($text),'link'=>e($link)),"WHERE `n`='".$n."'");
		return "
			clean('rekomenda_edit');
			var i='rekomendat_".$n."';
			idd(i).href=\"".h($link)."\";
			zabil(i,\"".h($text)."\");
		";
	} $a=='del';
}

if($a=='del') {
	msq("DELETE FROM ".$GLOBALS['db_rekomenda']." WHERE `n`='".$n."'");
	return "clean('rekomenda_".$n."'); salert('Deleted!',600);";
}


}
//------------------------------------

function REKOMENDA($e) {
$conf=array_merge(array(
'day'=>3,
'admin_knop'=>"<i title='Delete link!' class='e_remove' onclick=\"if(confirm('Delete?')) majax('module.php',{mod:'REKOMENDA',a:'del',n:{id}})\"></i>&nbsp;"
."<i title='Edit link!' class='e_kontact_journal' onclick=\"majax('module.php',{mod:'REKOMENDA',a:'edit',n:{id}})\"></i>&nbsp;",
'template_no'=>"<div class='br l' style='padding:2px 15px 2px 15px' onclick=\"majax('okno.php',{a:'rekomenda'})\">архив понравившихся мне ссылок</div>",
'template_id'=>"<div id='rekomenda_{id}'>{admin_knop}{date} <a id='rekomendat_{id}' href='{link}'>{text}</a></div>",
'template'=>"<div style='font-size:13px; font-weight:bold; border:1px dashed #ccc; margin: 0 10pt 10pt 10pt; padding: 10pt;'>Страницы, которые привлекли мое внимание за последние дни, рекомендую:<br>{s}<div class='br l' onclick=\"majax('okno.php',{a:'rekomenda'})\">архив ссылок</div></div>"
),parse_e_conf($e));


	if($GLOBALS['article']['Date']=='REKOMENDA') {
	$s="
		<script>page_onstart.push(\"majax('okno.php',{a:'rekomenda'})\");</script>
		<a href=\"javascript:majax('okno.php',{a:'rekomenda'})\">база понравившихся ссылок, кликать сюда</a>
	";

	if($GLOBALS['admin']) {
		$l="javascript:var".'%20'."o=(document.selection)?document.selection.createRange().text:window.getSelection();q=document.body;q.innerHTML='<div".'%20'."style=position:absolute;z-index:99999;>"
."<img".'%20'."src=".$GLOBALS['httphost']."site_module/REKOMENDA.php"
."?p=".$GLOBALS['rekomenda_pass']
."&l='+encodeURIComponent(location)+'&t='+encodeURIComponent(''+o)+'></div>'+q.innerHTML;void(0);";

		$s.="<p>Админ! Создай в Firefox кнопку, где вместо url пропиши <a href=\"".$l."\">такую ссылку</a>:
<br><center><textarea cols=80 rows=5>".h($l)."</textarea></center>

<p>Выделяй несколько слов текста на любой странице мышкой и нажимай эту кнопку в браузере - ссылка появится
в блоге как рекомендованная.
";
	}
		return $s;
	}

//---------------

	$pp=ms("SELECT `n`,`link`,`text`,`datetime` FROM ".$GLOBALS['db_rekomenda']." WHERE `datetime`>(NOW()-INTERVAL ".e($conf['day'])." DAY) ORDER BY `datetime` DESC");

	if(sizeof($pp)) { $s=''; foreach($pp as $p) {
			list($date,)=explode(' ',$p['datetime']);
			$s.=mper($conf['template_id'],array(
				'id'=>h($p['n']),
				'link'=>h($p['link']),
				'date'=>h($date),
				'text'=>h($p['text']),
				'admin_knop'=>($GLOBALS['ADM']?mper($conf['admin_knop'],array('id'=>h($p['n']))):'')
			));

	} return mper($conf['template'],array('s'=>$s));
	} return $conf['template_no'];
}

?>