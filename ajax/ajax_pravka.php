<?php

include "../config.php"; include $include_sys."_autorize.php";

include_once $include_sys."/_podsveti.php"; // процедура Diff - кривая сука, страшная, но работает
include_once $include_sys."/_one_pravka.php"; // процедура вывода окошка с одной правкой

$id=RE0('id'); $a=RE('action').RE('a'); ADH();

	if($a=='1') pravka_submit($id,pravka_answer(RE('answer'))); // правку принять
	if($a=='0') pravka_discard($id,pravka_answer(RE('answer'))); // правку отклонить

	if($a=='podrobno') pravka_showmore($id); // показать больше
	if($a=='edit') pravka_edit($id); // edit запрос формы редактирования
	if($a=='edit_txt') pravka_edit_txt($id); // edit-send - пришел отредактированный текст

	if($a=='edit_c') pravka_edit_c($id); // edit запрос формы редактирования коммента
	if($a=='edit_c_txt') pravka_edit_c_txt($id); // принять отредактированный answer

	if($a=='del') { if($GLOBALS['admin']) msq_del($GLOBALS['db_pravka'],array('id'=>$id)); pravka_otvet_e(); } // удалить говно из базы

	if($a=='opechatka') { // прием опечаток ПО ФАЙЛАМ от населения!
		$oid=RE('oid'); // ss es
		$data=RE('data'); // file#@arhive/no_humor/rosryba.htm
		$text=pravka_valitext(RE('text')); // чтоб было в глобале!
		$textnew=pravka_valitext(RE('textnew')); // чтоб было в глобале!
		pravka_priem($data,$text,$textnew);
	}

function nl2brp($s) { return str_replace(array("\n\n","\n"),array('<p>','<br>'),$s); }

	if($a=='textarea') { ADMA(1);
		$oid=RE('oid'); $o=RE('o'); $ss=RE0('ss');

		$n=RE0('n');
	        if($n>1) otprav("salert('Строк ".h($o)." в блоке '".h($oid)."' содержится ".$n."!<br>Попробуйте выделить более длинный кусок.',3000);");
		if($n<1) otprav('');

		$s=mpers(str_replace(array("\n","\r","\t"),'',get_sys_tmp("pravka.htm")),array(
				'adm'=>$ADM,
				'num'=>RE0('num'),
				'oid'=>$oid,
				'text'=>$o,
				'rows'=>page($o,50)
		    )
		);
		otprav("
	pravka_send=function(){
		majax('ajax_pravka.php',{a:'submit',oid:'".h($oid)."',es:".($ss+strlen(nl2brp($o))).",ss:".$ss.",textnew:idd('pravk').value,text:idd('pravk').defaultValue});
	};
	ohelp('opechatku','исправляем опечатки',\"".njsn($s)."\");
/*
	setkey('esc','',function(a,b){clean('opechatku')},true,1);
	setkey('enter','',function(a,b){pravka_send()},false,1);
	setkey('enter','ctrl',function(a,b){pravka_send()},false,1);
*/
	idd('pravk').focus();
");

	}

	if($a=='submit') { // прием опечаток от населения!
		ADMA(1);
		$oid=RE('oid'); // ss es
		if(substr($oid,0,1)=='a') $l='@dnevnik_comment@Answer@id@'.substr($oid,1);
		elseif(substr($oid,0,5)=='Body_') $l='@dnevnik_zapisi@Body@num@'.substr($oid,5);
		elseif(substr($oid,0,7)=='Header_') $l='@dnevnik_zapisi@Header@num@'.substr($oid,7);
		else $l='@dnevnik_comm@Text@id@'.$oid;
		$text=pravka_valitext(RE('text')); // чтоб было в глобале!
		$textnew=pravka_valitext(RE('textnew')); // чтоб было в глобале!

		pravka_priem($l,$text,$textnew);
	}

//	if($a == 'create') { idie('создать базу?!'); pravka_basa_create(); } // впервые: создать новую базу!

idie('Неясная команда: '.h($a));

//===================== важные процедуры работы с архивом ===============
/*
function pravka_validata($data) {
		$d=preg_replace("/[^0-9a-z\._\-\#\/\:]/si","",$data); if($d!=$data) pravka_otvet('fuck you');
		return $data;
}
*/

function filename_valid($f) { return rpath($f); } // на всякий случай шоб выше батьки по директориям не лезли

function pravka_valitext($s) {
	$s=h(urldecode($s));
	if(RE('hashpresent')==2) $s=strtr($s,'ABCEHKMOPTXaceopxy','АВСЕНКМОРТХасеорху'); // это мои личные беды
	return $s;
}

function pravka_basa_replace($data,$txt) { if(!$GLOBALS['admin']) return; // от кулхацкеров

if(isset($GLOBALS['rdonly'])) return;


        list($base,$table,$bodyname,$wherename,$whereid)=explode('@',$data);
	if($base=='file#') { // если правка предназначалась для файла, то $table - это его имя
		$i=file_put_contents($GLOBALS['host'].filename_valid($table),$txt); // записать исправления в файл
		chmod($GLOBALS['host'].filename_valid($table),0666);
		return $i; 
	} else { // правка для базы
		return ms("UPDATE ".($base!=''?"`".e($base)."`.":'')."`".e($table)."`
			SET `".e($bodyname)."`='".e($txt)."'
			WHERE `".e($wherename)."`='".e($whereid)."'","_l",0);
	}
}

function pravka_oldtxt($data) {
        list($base,$table,$bodyname,$wherename,$whereid)=explode('@',$data);
	if($base=='file#') { // если правка предназначалась для файла, то $table - это его имя
		if($txt=file_get_contents(rpath($GLOBALS['host'].filename_valid($table)) ) ) return $txt;
		pravka_otvet("Нет такого файла '".h($table)."'");
	} else { // правка для базы
		 return ms("SELECT `".e($bodyname)."` FROM ".($base!=''?"`".e($base)."`.":'')."`".e($table)."`
				WHERE `".e($wherename)."`='".e($whereid)."'","_l",0);
	}
}

//##################################################################
//##################################################################
// ВСЕ ОПЕРАЦИИ С БАЗАМИ ВЫНЕСТИ ОТДЕЛЬНО

function pravka_basa_add($data,$stdprav,$metka='new') { // добавить сигнал в базу (безопасное)
	if(!$GLOBALS['admin']) return;
	pravka_basa_add1($data,$stdprav,$metka);
}

function pravka_basa_add1($data,$stdprav,$metka='new') { // добавить сигнал в базу (опасное!)
global $text,$textnew,$login;
        $ara=array();
        $ara['stdprav']=e($stdprav);
        $ara['Date']=e($data);
//        $ara['lju']=e($GLOBALS['lju']);

	$ara['unic']=$GLOBALS['unic'];
	$ara['acn']=$GLOBALS['acn'];

/*
        $ara['sc']=e($GLOBALS['sc']);
        $ara['ipbro']=e($_SERVER['REMOTE_ADDR']."\n".$_SERVER['HTTP_X_FORWARDED_FOR']."\n".$_SERVER['HTTP_USER_AGENT']);
        $ara['Mail']=e(str_replace('mailto:','',$_COOKIE['CommentaryAddress']));
        $ara['Name']=e($_COOKIE['CommentaryName']);
        $ara['login']=e($login);
*/
        $ara['text']=e($text);
        $ara['textnew']=e($textnew);
        $ara['metka']=e($metka);
	msq_add($GLOBALS['db_pravka'],$ara); // забить в базу
}

function pravka_basa_p($id) {
	$p=ms("SELECT * FROM `".$GLOBALS['db_pravka']."` WHERE `id`='".e($id)."'",'_1',0);
	if($p['id']!=$id) pravka_otvet("ошибка номера!!!");

	if(!$GLOBALS['pravshort']) {
		$p['text']=pravka_bylo($p['stdprav']); // не пора ли упразднить эти поля?
		$p['textnew']=pravka_stalo($p['stdprav']); // не пора ли упразднить эти поля?
	}
	return $p;
}

function pravka_basa_metka($id,$metka,$answer) { if(!$GLOBALS['admin']) return;
if(isset($GLOBALS['rdonly'])) return;
	msq_update($GLOBALS['db_pravka'],array('metka'=>e($metka),'Answer'=>e($answer)),"WHERE `id`='".e($id)."'");
}

function pravka_basa_getmetka($data,$stdprav) { // получить метку для такого случая
	return ms("SELECT `metka` FROM `".$GLOBALS['db_pravka']."` WHERE `Date`='".e($data)."' AND `stdprav`='".e($stdprav)."'",'_l',0);
}


//======================================================================================================
function pravka_edit_c($id) { // editor комментариев
	$p=pravka_basa_p($id); // взять данные из базы правок
	if($p['Answer']=='') pravka_otvet_e($p);
	pravka_otvet_e($p,pravka_textarea($id,$p['Answer'],'edit_c_txt'));
}

function pravka_edit_c_txt($id) { // editor комментариев
	$p=pravka_basa_p($id);
	$p['Answer']=RE('answer');
	pravka_basa_metka($id,$p['metka'],e($p['Answer']));
	pravka_otvet_e($p);
}

function pravka_edit($id) { // editor
	$p=pravka_basa_p($id); // взять данные из базы правок
	$p['oldtxt'] = pravka_oldtxt($p['Date']); // взять исконный файл
	$stdprav = pravka_stdprav($p,200); // взять большой кусок
	$text = $p['metka']=='submit' ? pravka_stalo($stdprav) : pravka_bylo($stdprav);
	$n=substr_count($p['oldtxt'],$text); if($n != 1) {
unset($p['oldtxt']);
pravka_otvet_e($p,"
Не удается найти место: оно встречается ".intval($n)." раз.
<p>stdprav='".h($stdprav)."'
<p>text='".h($text)."'
<p>p='".nl2br(h(print_r($p,1)))."'
"); }
	pravka_textarea($id,$text,'edit_txt');
	pravka_otvet_e($p,pravka_textarea($id,$text,'edit_txt'));
}


function pravka_edit_txt($id) { // editor
	$textnew=str_replace('\r','',RE('answer'));
	$p=pravka_basa_p($id); // взять данные из базы правок
	$p['oldtxt'] = pravka_oldtxt($p['Date']); // взять исконный файл
	$stdprav = pravka_stdprav($p,200); // взять большой кусок
	if($p['metka']=='submit') $text=pravka_stalo($stdprav); else $text=pravka_bylo($stdprav);
	if(substr_count($p['oldtxt'],$text) != 1) pravka_otvet_e($p,'Не удается найти это место редактирования.');
	if($text == $textnew) pravka_otvet_e($p,"Не, ну а смысл? Предложите исправление.");
	$stdprav=std_pravka($textnew,$text,$p['oldtxt']); // вычислить стандартный кусок около правки
	if($GLOBALS['pravka_paranoid']) pravka_basa_add($p['Date'],$stdprav,'submit'); // параноидально записывать свои
	pravka_basa_replace($p['Date'],str_replace($text,$textnew,$p['oldtxt'])); // ПРИМЕНИТЬ ПРАВКУ ОТ АДМИНА
	$p['Answer'] .= '<i>Это место я отредактировал иначе:</i><p>'.str_replace("\n","\n<br>",$stdprav); $p['metka'] = 'discard';
	pravka_basa_metka($id,$p['metka'],$p['Answer']); // пометить как discard
	pravka_otvet_e($p); // выдать ответ
}


function pravka_showmore($id) { // показать больше
	$p=pravka_basa_p($id); // взять данные из базы правок
	$p['oldtxt'] = pravka_oldtxt($p['Date']); // взять исконный файл
	$p['stdprav'] = pravka_stdprav($p,500); // взять большой кусок
	pravka_otvet_e($p);
}

####################################################

function pravka_submit($id,$answer) { // принять правку
	$p=pravka_basa_p($id); // взять данные из базы правок
	if($p['metka']=='discard') $answer='Поразмыслив, решил принять: '.$answer; // расшифровать ответ
	$oldtxt=pravka_oldtxt($p['Date']); // взять исконный файл
	if(substr_count($oldtxt,$p['text'])!=1) $answer .= " Но это место уже исправлено!";
	else pravka_basa_replace($p['Date'],str_replace($p['text'],$p['textnew'],$oldtxt)); // СДЕЛАТЬ ПРАВКУ
	$p['Answer'] .= pravka_answer_n($answer,1); $p['metka'] = 'submit';
	pravka_basa_metka($id,$p['metka'],$p['Answer']); // пометить как submit
	pravka_otvet_e($p); // выдать ответ
}

function pravka_discard($id,$answer) { // отклонить правку
	$p=pravka_basa_p($id); // взять данные из базы правок
	$metkanew='discard';
	if($p['metka']=='submit') { // если была принята - вернуть
		$answer='Поразмыслив, решил отменить: '.$answer; // расшифровать ответ
		$text=pravka_bylo($p['stdprav']); // как было
		$textnew=pravka_stalo($p['stdprav']); // как стало
		$oldtxt=pravka_oldtxt($p['Date']); // взять исконный файл
		if(substr_count($oldtxt,$textnew)!=1) { $answer .= 'Отменить не удалось.'; $metkanew='submit'; }
		else pravka_basa_replace($p['Date'],str_replace($textnew,$text,$oldtxt)); // СДЕЛАТЬ ПРАВКУ
		}
	$p['Answer'] .= pravka_answer_n($answer,0);
	$p['metka'] = $metkanew;
	pravka_basa_metka($id,$p['metka'],$p['Answer']);
	pravka_otvet_e($p); // выдать ответ
}

###################################################

function pravka_priem($data,$text,$textnew) { // global $_RESULT; // прием опечаток от населения!

	$text=str_ireplace('&quot;','"',$text);
	$textnew=str_ireplace('&quot;','"',$textnew);

	$oldtxt=pravka_oldtxt($data); // взять исконный файл
	$nzamen=substr_count($oldtxt,$text); // сколько раз встречается этот фрагмент в тексте (надо, чтобы 1)
if($oldtxt == '') pravka_otvet("Ошибка какая-то. Нет такой записи в базе.");
if($text == $textnew) pravka_otvet("Не, ну а смысл? Предложите исправление.");
if($text == '') pravka_otvet("Выделите что-нибудь и исправьте.\nИначе какой смысл?");
if($nzamen == 0) {
if($GLOBALS['ADM']) {
	pravka_otvet("Ошибка. old:<p>'".h($text)."'<hr>new:<p>'".h($oldtxt)."'");
	if(preg_match("/\&[a-z0-9\#]+\;/si",$oldtxt,$m))
	pravka_otvet("Ошибка, словосочетание не найдено.\n\nАдмин! А попробуй-ка убрать из исходника значки типа '".h($m[0])."'
".(preg_match("/^[^@]*@site@[^@]+@[^@]+@(\d+)$/",$data,$m)?" в <a href='".$wwwhost."adminsite/?mode=one&edit=".$m[1]."'>записи базы номер #".$m[1]."</a>":'') );
} pravka_otvet("Ошибка, словосочетание не найдено.\n\nМожет, там верстка HTML попалась?");
}

if($nzamen > 1) pravka_otvet("Не, такое словосочетание встречается несколько раз.\nПопробуйте выделить отрывок побольше.");

	$stdprav=std_pravka($textnew,$text,$oldtxt); // вычислить стандартный кусок около правки


#	$stdprav=std_pravka(pravka_stalo($stdprav),pravka_bylo($stdprav),$oldtxt); // и еще раз вычислить, теперь с точностью до слова
# НЕ НАДО ВТОРОЙ РАЗ ВЫЧИСЛЯТЬ! ТЭГИ БЬЮТСЯ

	if(!$GLOBALS['ADM']) {
	$metka=pravka_basa_getmetka($data,$stdprav); // получить метку, если был такой случай
if($metka=='new') pravka_otvet("Такая правка уже записана\nи ждёт рассмотрения.");
if($metka=='discard') pravka_otvet("Такая правка уже предлагалась,\nно автор решил ее отклонить.\n\nФиг знает, почему он так решил.\nТупой наверно. И упёртый.");
if($metka=='submit') pravka_otvet("Хм... Такая правка уже предлагалась,\nи даже была благополучно принята.\nНепонятно, почему вы ее не видите на экране.\nМожет, это произошло секунду назад?\nПерегрузите-ка страницу...");
	pravka_basa_add1($data,$stdprav,'new');
//	pravka_otvet_nbody(podsvetih(podsveti($textnew,$text)));
	pravka_otvet_nbody($textnew);
	}

	if($GLOBALS['pravka_paranoid']) pravka_basa_add($data,$stdprav,'submit'); // параноидально записывать свои
	pravka_basa_replace($data,str_replace($text,$textnew,$oldtxt)); // ПРИМЕНИТЬ ПРАВКУ ОТ АДМИНА
	pravka_otvet_nbody($textnew);
}

//=============================== разные вспомогашки ==========================
function pravka_otvet_nbody($s) { global $oid; otprav("
var s=stripp(vzyal('".$oid."'));
zabil('".$oid."',s.substring(0,".RE('ss').")+nl2brp('".njsn($s)."')+s.substring(".RE('es').",s.length));
clean('opechatku');
");
}

function pravka_otvet_e($p=0,$ext=''){

// idie(h("zabil('".$GLOBALS['id']."',\"".($p===0?'':njsn(_one_pravka($p,$ext)))."\")"));

	otprav("zabil('".$GLOBALS['id']."',\"".($p===0?'':njsn(_one_pravka($p,$ext)))."\")");
}

function pravka_otvet($s) { idie($s); }


##############################################################################################################

function pravka_answer($answer) { // расшифровки стандартных ответов
$a=array(
	'da'=>'Ну конечно! Спасибо большое!',
	'ugovorili'=>'Хм... Вы полагаете? Ну... Пожалуй, да. Спасибо.',
	'zadumano'=>'Извините, но здесь надо было именно так.',
	'gramotei'=>'О, боже...',
	'len'=>'Ох, тут что-то у меня сомнения... Может, пусть будет типа "авторское" написание?',
	'inache'=>'Знаете, я подумаю об этом.',
	'spam'=>'Спамщиков - нахуй.'
);
foreach($a as $l=>$m) if($l==$answer) return $m;
return $answer;
}

function pravka_answer_n($answer,$n) { if($answer!='') return '<div class='.($n?'y':'n').'>'.e(h($answer)).'</div>'; }

function pravka_textarea($id,$text,$modescript) { $texth=h($text); $ide=$id."_e";
return "<table><tr>
<td><TEXTAREA id='$ide' class='t' cols='50' rows='".max(page($texth),3)."'>".$texth."</TEXTAREA></td>
<td valign=top>
<input value='SEND' class='t' onclick=\"pravka($id,'$modescript',idd('$ide').value)\" type='button'>
</td></tr></table>";
}

// тип работы с текстами - одну из этих переменных надо закомментировать!
//$arhdir=$_SERVER['DOCUMENT_ROOT'].'arhive/'; // имя директории, где тексты, если они в файлах (кодировка windows-1251)
//$arhbasa='dnevnik_zapisi'; // имя таблицы, где тексты, если они в MySQL (у меня - в поле 'Body' по ключу 'Data' вида 2004-01-14
//include_once($_SERVER['DOCUMENT_ROOT'].'/dnevnik/_msq.php'); msq_open('lleo','windows-1251'); // библиотека MySQL
//include_once($_SERVER['DOCUMENT_ROOT'].'/dnevnik/_autorize.php'); // библиотека авторизации админа $IS_EDITOR=true если админ
//include_once($_SERVER['DOCUMENT_ROOT'].'/sys/pravka/_podsveti.php'); // процедура Diff - кривая сука, страшная, но работает
//include_once($_SERVER['DOCUMENT_ROOT'].'/sys/pravka/_one_pravka.php'); // процедура вывода окошка с одной правкой
//include($_SERVER['DOCUMENT_ROOT'].'/sys/pravka/ajax_pravka_code.php'); // главная процедура - для универсальности отдельно

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>