<?php /* установка тэгов за пределами HEAD

Формируется нужный тэг в заголовке страницы:

{<b></b>_HEADD: meta name='author' content='Leonid Kaganov' _<b></b>}
{<b></b>_HEADD: link rel='openid.server' href='http://www.myopenid.com/server' _<b></b>}

*/

function HEADD($e) { $e=nekawa($e); $GLOBALS['_HEADD'][$e]=$e; return ''; }

?>