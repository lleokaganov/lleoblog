<?php /* вставка из файла итли запуск, если файл .php)
(только для рутовых аккаунтов)

Имя дается абсолютным. Если первый символ в имени файла не / - то подставить корень веб-папки.

Через пробел для вставляенмых файлов (все, кроме php) можно указать преобразование кодировки двумя символами:
	uw: UTF-8 - Windows-1251
	uk: UTF-8 - KOI8-R

	wu: Windows-1251 - UTF-8
	wk: Windows-1251 - KOI8-R

	kw: KOI8-R - Windows-1251
	ku: KOI8-R - UTF-8

Идентично:

{_INCLUDE: template/system/unic.htm uw _}

{_INCLUDE: /var/www/dnevnik/template/system/unic.htm _}
*/

function INCLUD($e) { if(($ur=onlyroot(__FUNCTION__.' '.h($e),1))) return $ur; // только для рутового аккаунта
    $uw=''; if(strstr($e,' ')) list($e,$uw)=explode(' ',$e);

    if('/'!=substr($e,0,1)) $e=$GLOBALS['filehost'].$e;
    if(is_file($e)) {
	if(getras($e)=='php') { $o=''; include_once rpath($e); return $o; }

	if($uw=='') return fileget($e);
	if($uw=='uw') return uw(fileget($e));
	if($uw=='wu') return wu(fileget($e));
	if($uw=='wk') return wk(fileget($e));
	if($uw=='kw') return kw(fileget($e));
	if($uw=='ku') return ku(fileget($e));
	if($uw=='uk') return uk(fileget($e));
	// return "<>";

    }
    return '<hr><font color=red>Include not found: `'.h($e)."`</font>";
}
?>