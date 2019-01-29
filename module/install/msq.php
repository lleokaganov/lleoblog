<?php

function msq_all_tables() { $t=array(); $p=ms("SHOW TABLES","_a"); foreach($p as $l) foreach($l as $l1) $t[$l1]=$l1; return $t; }
function msq_info_table($table,$n='') { $a=ms("show table status where name='".e($table)."'",'_1'); return ($n==''?$a:$a[$n]); }
function msq_table_polya($ta) { $s=parse_msqtxt($GLOBALS['msq_txt'],$ta); preg_match_all("/\n\s*`([^`]+)`/si",$s,$m); return '`'.implode('`,`',$m[1]).'`'; }


/*
        $s="DELETE FROM $tb WHERE $a $u";
// if(msq_field($table,$pole)===false) msq("ALTER TABLE `".$table."` ADD `".$pole."` ".$s
msq_field($tb,$pole) // проверить, существует ли такое поле в таблице $tb
 // проверить, существует ли такая таблица
msq_index($tb,$index) // проверить, существует ли такой индекс
// изменить поле в таблице	function msq_change_field($table,$pole,$s)
// добавить поле таблицы	function msq_add_field($table,$pole,$s)
// удалить поле из таблицы	function msq_del_field($table,$pole)
// добавить ИНДЕКС в таблицу	function msq_add_index($table,$pole,$s)
// удалить ИНДЕКС из таблицы	function msq_del_index($table,$pole)
// создать таблицу		function msq_add_table($table,$s)
// удалить таблицу		function msq_del_table($table,$text)
*/

// Эта функция возвращает 0, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.

//$GLOBALS['msq_txt']=$GLOBALS['filehost']."module/upgrade/sql.txt";
$GLOBALS['msq_txt']=$GLOBALS['filehost']."module/upgrade/*.sql";


function installmod_init(){ $o=parse_mytables(); return $o; }
// [Update_time]

function msqtableknop($a,$table,$pole='',$new='',$old='') {
	$at=array('del_field'=>'red','add_field'=>'green','change_field'=>'blue','add_table'=>'green');

	$onclick="majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."'"
.",act:'$a'"
.",table:'$table'"
.($pole==''?'':",pole:'$pole'")
.($new==''?'':",new:'".njs($new)."'")
// .($old==''?'':",old:'$old'")
."})";

	$s='' // "<blink><span style='color:red; font-size:12px; text-decoration:bold;'>*</span></blink> "
	."<input style='color:".$at[$a].";text-decoration:bold' type='button' value='$a' onclick=\"$onclick\">";
	if($new!='') {
		if($old=='') { $s.=" <tt><b>`$pole`:</b> $new</tt>"; }
		else { $s="<table><tr><td valign='center'>$s &nbsp; <b>`$pole`</b>: </td><td><tt><s>$old</s><br>$new</tt></td></tr></table>"; }
	} else $s.=" <tt><b>`$pole`</b></tt>";
	return "<div id='msqch_$a_$table_$pole'>".$s."</div>";
}

// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать (изначально 0), allwork - общее количество (измерено ранее), $o - то, что кидать на экран.
function installmod_do() { global $msqe;
	$a=RE('act');
	$table=RE('table');
	$pole=RE('pole');
	$new=RE('new');
	$old=RE('old');

	// idie('#');

	if($a=='add_field') {
		if(msq_pole($table,$pole)) idie("Error: `$pole` exist in table `$table`!");
		msq_add_pole($table,$pole,$new);
		if($msqe!='') idie("Error:<p>$msqe");
		otprav("salert('added',800); clean('msqch_$a_$table_$pole')");
	}

	if($a=='change_field') { //idie('#2');
		if(($r=msq_pole($table,$pole))==$new) idie("Error: `$pole` already set:<br>$new");
		msq_change_pole($table,$pole,$new);
		if($msqe!='') idie("Error:<p>$msqe");
		otprav("salert('changed',800); clean('msqch_$a_$table_$pole');if(idd('okno')) clean('okno');");
	}

	if($a=='del_field') {
		if(!msq_pole($table,$pole)) idie("Error: `$pole` not found!");
		msq_del_pole($table,$pole);
		if($msqe!='') idie("Error:<p>$msqe");
		otprav("salert('deleted',800); clean('msqch_$a_$table_$pole')");
	}

	if($a=='edit_field') {
		if(!($s=msq_pole($table,$pole))) idie("Error: `$pole` not found!");
		otprav("
helpc('okno',\"<fieldset><legend>Edit fileld `".h($pole)."` in `".h($table)."`</legend>"
."<input type='text' id='edit_pole' value=\\\"".h($s)."\\\" size='".strlen($s)."'>"
."<input type='button' value='submit' onclick=\\\""
."majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'change_field',table:'$table',pole:'$pole',re:1,new:idd('edit_pole').value})"
."\\\"></fieldset>\");
");
	}

	if($a=='create_table') {
		if(msq_table($table)) idie("Error: `$table` exist!");
		$s=parse_msqtxt($GLOBALS['msq_txt'],$table);
		msq($s); if($msqe!='') idie("Error:<p>$msqe");
		otprav("salert('created ".$table."',1000);zabil('msqmktb_".$table."',\"".njsn(parse_mytables($table))."\");");
	}

	if($a=='delete_table') {
		if(!msq_table($table)) idie("Error: `$table` not exist!");
		msq_del_table($table); if($msqe!='') idie("Error:<p>$msqe");
		otprav("salert('deleted',1000);clean('msqmktb_$table');");
	}

	if($a=='backup_table') { $table2=$table.'_old';

		if(msq_table($table2)) msq("RENAME TABLE `$table2` TO `$table2"."_"
		.strtr(msq_info_table($table2,'Update_time')," :-","___")."`");

		$s=parse_msqtxt($GLOBALS['msq_txt'],$table);
//		msq_del_table($table2);
		$s=str_replace("EXISTS `$table` (","EXISTS `$table2` (",$s);
 		msq($s);
//		idie(nl2br($s));

//	idie(msq_table_polya($table));


		msq("INSERT INTO `$table2` (".msq_table_polya($table).") SELECT "
.msq_table_polya($table)." FROM `$table`");
		if($msqe!='') idie("Error:<p>$msqe");

		otprav("
salert('backuped',1000);
clean('".RE('id')."');
zabil('msqmktb_$table',\"".njsn(parse_mytables($table))."\");
zabil('_msq_nevbaze_ost_',\"".njsn(parse_mytables('_msq_nevbaze_ost_'))."\");
");
	}

	if($a=='restore_table') { $table2=$table.'_old';
		$s=parse_msqtxt($GLOBALS['msq_txt'],$table);
		msq_del_table($table); msq($s);
		msq("INSERT INTO `$table` SELECT * FROM `$table2`");
		if($msqe!='') idie("Error:<p>$msqe");
		otprav("salert('restored',1000); zabil('msqmktb_$table',\"".njsn(parse_mytables($table))."\");");
	}


	if($a=='restore_table2') { $table2=$table; $table=array_shift(explode('_old',$table));
		$s=parse_msqtxt($GLOBALS['msq_txt'],$table); msq_del_table($table); msq($s);
		msq("INSERT INTO `$table` SELECT * FROM `$table2`");
		if($msqe!='') idie("Error:<p>$msqe");
		otprav("salert('restored',1000); zabil('msqmktb_$table',\"".njsn(parse_mytables($table))."\");");
	}



	idie('do!');

//	$delknopka=1;
//	return 0;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
// Пользуясь случаем, тут можно что-то сделать полезное - например, очистить таблицу для будущего заполнения
function installmod_allwork() { return 0; }

//======================================================================================
// похвастаться успешной установкой
// function admin_pohvast() { return "<center><div id='soobshi'><input type=button value='Похвастаться успешной установкой' onclick=\"zabil('soobshi','<img src=http://lleo.aha.ru/blog/stat?link=".$GLOBALS['httphost'].">')\"></div></center>"; }
//======================================================================================


// Получить структуру таблиц движка из файла
function parse_msqtxt($txt,$ta='') {
	$s=''; foreach(glob($txt) as $l) $s.="\n\n\n".fileget($l); // взять список баз на создание

	$s=preg_replace("/AUTO_INCREMENT=\d+/si","AUTO_INCREMENT=0",$s); // поправить сбитый автоинкремент
	$s=preg_replace("/\n-[^\n]+/si","","\n".$s); // убрать строки комментариев
	$s=preg_replace("/\n{2,}/si","\001",trim($s)); $a=explode("\001",$s); // разобрать
	$t=array();
	foreach($a as $l) { // создание таблиц
		$l=c($l); if(!preg_match("/CREATE TABLE[^\n\`\(]+\`([^\`]+)\`/si",$l,$m)) continue;
		$table=$m[1];
			if($table==$ta) return $l;
		$t[$table]=array();
			$lta=explode("\n",$l); // поля таблицы
			foreach($lta as $lt) {
				$lt=trim($lt,"\n\r\t ,");
				$lt=preg_replace("/[ ]+/s"," ",$lt);
				$lt=preg_replace("/\s*COMMENT\s+[\'\"][^\'\"]+[\'\"]$/si","",$lt);
				$lt=preg_replace("/\s+default\s+\'\'/si","",$lt);
				$lt=preg_replace("/([ ])CURRENT_TIMESTAMP/si","$1'CURRENT_TIMESTAMP'",$lt);
				$lt=preg_replace("/\s+on update 'CURRENT_TIMESTAMP'/si","",$lt);
				if(preg_match("/^\`([^\`]+)\`/s",$lt,$mtmp)) { $t[$table][$mtmp[1]]=trim($lt); }
			} // SQL error: Unknown column 'DateDatetime' in 'where clause'
		// $t[$table]['COUNT(*)']=ms("SELECT COUNT(*) FROM `$table`","_l"); // число элементов
	}
	return $t;
}

//=============================================
function parse_mytables($ta='') { $o=''; // $i="<i class='e_";

	if($ta==''||$ta=='_msq_nevbaze_ost_') $rr=msq_all_tables();

//dier(parse_msqtxt($GLOBALS['msq_txt']));

	foreach(parse_msqtxt($GLOBALS['msq_txt']) as $tab=>$arr) {
		if($ta!='' && $ta!='_msq_nevbaze_ost_' && $tab!=$ta) continue;

		if($ta==''||$ta=='_msq_nevbaze_ost_') {
			$o.="<div id='msqmktb_$tab'>";
			if(isset($rr[$tab])) unset($rr[$tab]);
		//	if(isset($rr[$tab.'_old'])) unset($rr[$tab.'_old']);
		}

		if($ta=='_msq_nevbaze_ost_') { $o.='</div>'; continue; }

		if(!msq_table($tab)) { $o.="<input style='color:red;text-decoration:bold:font-size:10px;padding:10px;' type='button' value='Create_Table'"
." onclick=\"majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'create_table',table:'$tab'})\""
."><i class='e_expand_plus' id='msqPlus_".h($tab)."'></i> <b><big>`$tab`</big></b>"; if($ta!='') return $o; $o.='</div>'; continue; } // создать

		$g=''; $arr_ok=$arr_del=$arr_add=$arr_change=array();

		$pp=ms("SHOW COLUMNS FROM ".e($tab)."","_a",0); // взять все поля таблицы

		foreach($pp as $p) { $pole=$p['Field'];

			if($p['Extra']=='on update '.$p['Default']) $p['Extra']='';
			$str = trim("`$pole` ".$p['Type']." "
				.($p['Null']=='NO'?"NOT NULL ":"")
				.($p['Default']!=''?"default '".$p['Default']."' ":"")
				.($p['Extra']!=''?$p['Extra']." ":""));

			$str=preg_replace("/timestamp\(\d+\)/si","timestamp",$str);

			$str2 = substr($str,strlen($pole)+3);

			$strl=strtolower($str);
			$arrpolel=strtolower($arr[$pole]);

			if(!isset($arr[$pole])) $arr_del[$pole]=$str2; else { unset($arr[$pole]);
			    if($arrpolel==$strl
				|| preg_replace("/ not null/s","",$arrpolel)==$strl
				|| preg_replace("/ default '[^']+'/s"," not null",$strl)==$arrpolel
				|| (preg_match("/^`.+` text$/s",$strl) && preg_match("/^`.+` varchar\(\d+\)/s",$arrpolel))
				|| str_replace(" not null default 'current_timestamp'",'',$arrpolel)==$strl
			    ) $arr_ok[$pole]=$str2; // равно
			    else {  // изменить
				$ept=substr($arrpolel,strlen($pole)+3);
				if($ept!=strtolower($str2)) $arr_change[$pole]=array($str2,$ept); else $arr_change[$pole]=array('ERROR: ['.$str2.']','['.$ept.']');
			    }
			}
		}

		foreach($arr as $pole=>$str) { $arr_add[$pole]=substr($str,strlen($pole)+3); } // и подсчитать те, что надо добавить

		//---

		if(sizeof($arr_ok)) {
			$s=''; foreach($arr_ok as $n=>$v) $s.="<div>"
."<span title='Edit $n' style='color: #bbb; font-size:8px; cursor:pointer;'"
." onclick=\"majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'edit_field',table:'$tab',pole:'$n'})\""
."><i class='e_kontact_journal'></i></span> "
."<b>".h($n).":</b> ".h($v)
."</div>";
			$g.="<div id='msqtok_$tab' style='display:none;'>$s</div>";
		}
		if(sizeof($arr_add)) { $s=''; foreach($arr_add as $n=>$v)
			$s.="<div>".msqtableknop('add_field',$tab,$n,$v)."</div>"; $g.="<div>$s</div>";
		}
		if(sizeof($arr_change)) { $s=''; foreach($arr_change as $n=>$k) $s.="<div>".msqtableknop('change_field',$tab,$n,$k[1],$k[0])."</div>";
			$g.="<div>$s</div>";
		}
		if(sizeof($arr_del)) { $s=''; foreach($arr_del as $n=>$v)
			$s.="<div>".msqtableknop('del_field',$tab,$n)."</div>"; $g.="<div>$s</div>";
		}

$o.="<div>
<span style='color: #779933; font-size:18px; text-decoration: bold; cursor:pointer;'"
.(sizeof($arr_ok)?" onclick=\"tudasuda('msqtok_".h($tab)."'); var e=idd('msqplus_".h($tab)."').className;idd('msqplus_".h($tab)."').className=(e.indexOf('e_expand_plus')<0?e.replace(/e_expand_minus/g,'e_expand_plus'):e.replace(/e_expand_plus/g,'e_expand_minus'))\"":'')."><i class='e_expand_plus' id='msqplus_".h($tab)."'></i>".h($tab)
." (".ms("SELECT COUNT(*) FROM `".e($tab)."`","_l").")</span>" // число элементов
." <i class='e_redo' id='msqBackup_$tab'"
." onclick=\"/*if(confirm('Backup $tab to ".$tab."_old?'))*/majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'backup_table',table:'$tab',id:this.id})\""
." alt='Backup Table'></i>"
." <i class='e_remove'"
." onclick=\"if(confirm('Delete $tab?'))if(confirm('Really delete `$tab`?!'))majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'delete_table',table:'$tab'})\""
." title='Delete Table'></i>"
.(msq_table($tab.'_old')?" <i class='e_document-revert'"
." onclick=\"if(confirm('restore $tab?'))majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'restore_table',table:'$tab'})\""
." title='Restore from ".$tab."_old<br>count: "
.ms("SELECT COUNT(*) FROM `".e($tab.'_old')."`","_l")
." data: ".msq_info_table($tab,'Update_time')."'></i>":'')
."<div style='font-size:16px;color:black;margin: 0 0 0 20px'>$g</div>"
."</div>"
;

/*
$o.="<div>
<span style='color: #779933; font-size:18px; text-decoration: bold; cursor:pointer;'"
.(sizeof($arr_ok)?" onclick=\"tudasuda('msqtok_$tab')\"":'')."><i class='e_expand_plus'></i>".h($tab)
." (".ms("SELECT COUNT(*) FROM `".e($tab)."`","_l").")</span>" // число элементов
." <span"
." onclick=\"if(confirm('Backup $tab to ".$tab."_old?'))majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'backup_table',table:'$tab'})\""
." title='Backup Table' style='margin: 0 0 0 20px; color: #ccc; font-size:8px; text-decoration: bold; cursor:pointer;'>[Backup]</span>"
." <span"
." onclick=\"if(confirm('Delete $tab?'))if(confirm('Really delete `$tab`?!'))majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'delete_table',table:'$tab'})\""
." title='Delete Table' style='margin: 0 0 0 10px; color: #ccc; font-size:8px; text-decoration: bold; cursor:pointer;'>[Delete]</span>"
.(msq_table($tab.'_old')?" <span"
." onclick=\"if(confirm('restore $tab?'))majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'restore_table',table:'$tab'})\""
." title='Restore from ".$tab."_old' style='margin: 0 0 0 20px; background-color: #aaffaa; color: black; font-size:8px; text-decoration: bold; cursor:pointer;'>[Restore: ".ms("SELECT COUNT(*) FROM `".e($tab.'_old')."`","_l")." ".msq_info_table($tab,'Update_time')."]</span>"
:'')
."<div style='font-size:16px;color:black;margin: 0 0 0 20px'>$g</div>"
."</div>"
;

*/

// [Update_time]

if($ta!=$tab) $o.="</div>"; else return $o;

	}

$e=''; foreach($rr as $tab) {
	$e.="<div id='msqmktb_$tab' style='color: #779933; font-size:12px;'>$tab";

	if(strstr($tab,'_old')) { $t2=array_shift(explode('_old',$tab));
	$e.=" <i class='e_document-revert'"
	." onclick=\"if(confirm('Restore $t2?'))majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'restore_table2',table:'$tab'})\""
	." alt='Restore $t2 from $tab<br>count: "
	.ms("SELECT COUNT(*) FROM `".e($tab)."`","_l")
	." data: ".msq_info_table($tab,'Update_time')."'></i>"
	; }

	$e.=" <i class='e_remove'"
	." onclick=\"if(confirm('Delete $tab?'))majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'delete_table',table:'$tab'})\""
	." alt='Delete Table'></i>"
	."</div>";
}

if($ta=='_msq_nevbaze_ost_') return $e;
$e="<div id='_msq_nevbaze_ost_'>".$e."</div>";

//dier($rr); // foreach($rr as $l)

return "<table><tr valign='top'><td width='50%'><i>Main tables:</i><br>$o</td><td>&nbsp; &nbsp;</td><td width='50%'><i>Other tables:</i><br>$e</td></tr></table>";
}

?>