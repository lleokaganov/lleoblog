<?php //Рубрикатор (c) Тема Павлов http://temapavloff.ru/blog/
include "../config.php"; include $include_sys."_autorize.php"; $a=RE('mode'); ADH();

//Запрашиваем всплывающее окошко со всеми рубриками
if($a=='get_cats') {
	$pp = ms("SELECT `name` FROM `dnevnik_cats` ORDER BY `name` ASC","_a");
	$s=''; foreach($pp as $p) { $s.="<a style=\"margin: 4px;\" href=".$httphost."blog?cat=".urlencode($p["name"]).">".$p["name"]."</a> "; }
	$man=($admin?"<p style=\'margin-top: 10px;\' align=\'right\'><span style=\'font-size: 8pt;\'  class=scr onclick=\"majax(\'cats.php\',{mode:\'manage\'})\">Управление</span></p>":'');
	$s="<fieldset><legend>Рубрикатор</legend>".$s.$man."</fieldset>";
	otprav("helps('cats', '".$s."');");
}

//Выполняем поиск по рубрикам (для редактора заметок)
if($a=='search_cats') {
	if(RE("text")=='') otprav("zabil('c_popup',''); zakryl('c_popup');");
	$pp = ms("SELECT `name` FROM `dnevnik_cats` ".WHERE("`name` LIKE '".e(RE("text"))."%'")." ORDER BY `name` DESC LIMIT 0,20","_a");
	$s=''; foreach($pp as $p) { $s.="<li onclick=\"idd('".RE("idhelp")."_cat').value=this.innerHTML;\">".$p["name"]."</li>"; }
	$s="<ul class=cat_its>$s</ul>";
	otprav("zabil('c_popup','".e($s)."'); otkryl('c_popup');");
}

//Управление рубриками
if($a=='manage') { AD(); otprav("helps('cats', '".manage()."');"); }

if($a=='del') { AD();
	msq_update('dnevnik_zapisi',array('cat'=>''),"WHERE `cat`='".e(RE("name"))."'");
	msq("DELETE FROM `dnevnik_cats` WHERE `name`='".e(RE("name"))."'");
	otprav("helps('cats', '".manage()."');");
}

function manage() {
	$pp = ms("SELECT `name` FROM `dnevnik_cats` ORDER BY `name` ASC","_a");
	$s=''; foreach ($pp as $p) { $s.="<span style=\"margin: 4px;\">".$p["name"]."<i class='e_remove.png' style=\"cursor:pointer;\" onclick=\"if(confirm(\'Точно удалить?\')) {majax(\'cats.php\',{mode:\'del\',name:\'".$p["name"]."\'})}\"></i></span>"; }
	return "<fieldset><legend>Рубрикатор</legend>$s</fieldset>";
}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>