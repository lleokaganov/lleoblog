<?php if(!function_exists('h')) { include_once("../config.php"); include_once($include_sys."_autorize.php"); }
// редактор заметок
if(!$admin) redirect($wwwhost."login/"); // посторонних - нахуй

if(isset($_POST["action"]) && $_POST["action"]=="Save") {
	$s=$_POST["Body"]; $s=str_replace("\r",'',$s); // вот это сразу, потому что ненавижу
        $opt=makeopt($_POST);
	msq_add_update('dnevnik_zapisi',arae(array(
			'Date'=>e($_POST["Date"]),
			'Header'=>$_POST["Header"],
			'Body'=>$s,
			'opt'=>ser($opt),
		)),"Date");

	$_GET['Date']=$_POST["Date"];
}

if(isset($_GET["Date"])) $Date=$_GET["Date"];
elseif(!empty($_SERVER['QUERY_STRING'])) $Date=$_SERVER['QUERY_STRING'];
else $Date='';
$Date=h(trim($Date," \t\r\n\\/"));
$Date=str_replace('.html','',$Date);

if($Date) $_POST=ms("SELECT * FROM `dnevnik_zapisi` WHERE `Date`='".e($Date)."'","_1",0);
else die("Date error: `".h($Date)."`");
// else $_POST=ms("SELECT * FROM `dnevnik_zapisi` ORDER BY `Date` DESC LIMIT 1","_1",0);
$_POST=mkzopt($_POST);

// выяснить о модулях
$inc=glob($filehost."template/*.html");
$ainc=array(); $ainc['']='- нет -'; foreach($inc as $l) { $l=preg_replace("/^.*?\/([^\/]+)\.html$/si","$1",$l); $ainc[$l]=$l; }


print '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru"><head>
<title>emergency editor</title>
<meta http-equiv="Content-Type" content="text/html; charset='.$wwwcharset.'" />
</head><body>'
.$msqe
."<form action='".$wwwhost."module/editor.php?".h($Date)."' name='formedit' method='POST'>
Data: <input type=text size=15 name='Date' value='".h($Date)."'>
<br>Header: <input type='text' name='Header' class='t' value='".$_POST["Header"]."' maxlength='255' size='60' style='width: 70%;'>
<br><textarea class='t' style='width: 100%;' id='BodyTextarea' name='Body' cols='60' rows='20'>".h($_POST["Body"])."</textarea>
<div align=right>шаблон дизайна: ".selecto('template',$_POST['template'],$ainc)."</div>
<br><input type=submit name='action' value='Save' style='padding:20pt'>
</form>"
.'</body></html>';

?>