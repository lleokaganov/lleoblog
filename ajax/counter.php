<?php // случайное число

$memcache=function_exists('memcache_connect'); if(!$memcache) exit;
$memcache=memcache_connect('localhost',ini_get('memcache.default_port'));

$num=$_GET['num'];
$ask=intval($_GET['ask']);
$old=intval($_GET['old']);

$c=intval(memcache_get($memcache,'count_'.$num));

header('Content-Type: application/x-javascript');

$r="setTimeout(\"inject('counter.php?num=".$num."&ask=".(++$ask)."&old=".$c."')\",5000);";

if(!$old||$c==$old) die("
doclass('counter',function(e,s){e.style.color=s},'red');
setTimeout(\"doclass('counter',function(e,s){e.style.color=s},'black');\",500);".$r);

die("zabilc('counter','$c');mkdiv('poshel_$ask',\"<object width=1 height=1><param name='movie' value='http://lleo.aha.ru/na/swf/poshel.swf' /><param name='loop' value='false' /><embed src='/na/swf/poshel.swf' width='1' height='1' loop='false' type='application/x-shockwave-flash'></embed></object>\");otkryl('poshel_$ask');".$r);

?>