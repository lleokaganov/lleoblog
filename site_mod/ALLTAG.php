<?php /* все страницы с этим тэгом

{_ALLTAG: гребаный стыд _}

*/

function ALLTAG($e) {
    if($e=='') { $e=gettags()[0]; }
    if(empty($e)) return;
    return "{_CENTER:<i><b>Все страницы по теме &laquo;".h($e)."&raquo;:</b></i>
{_ANONS:
template = <div style='margin-left:50pt'>{Y}-{M}-{D}: <a href='{link}'>{Header}</a></div>
tags = ".h($e)."
days = 0
_}_}";
}
?>