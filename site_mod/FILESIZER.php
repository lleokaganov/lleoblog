<?php /* FILESIZE

Размер файла. Имя задается от корня сайта.

{_FILESIZE: /design/ttf/PTC55F.ttf_}
*/

function FILESIZER($e) { $i=filesize(rpath($GLOBALS['filehost'].trim($e)));
	if($i<1024) return $i;
	if($i<1048576) return ceil($i/1024)."Kb";
	if($i<1073741824) return ceil($i/1048576)."Mb";
	return ceil($i/1073741824)."Gb";
}
?>