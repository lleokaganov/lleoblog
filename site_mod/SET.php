<?php /* задать переменную, которую после можно будет использовать вызовом по имени

{_SET:
imya=Василий Иваныч
var123=приветик
_}

{_SET:imya3_}, {_SET:var123_}!

*/

function SET($e) { global $article;
    // запрос ранее установленной переменной
    if(false===strpos($e,'=')) { $e=c($e); return (isset($article['VAR'])&&isset($article['VAR'][$e])?$article['VAR'][$e]:''); }
    // установка переменной
    $c=parse_e_conf($e); $article['VAR']=(isset($article['VAR'])?array_merge($article['VAR'],$c):$c); return '';
}
?>