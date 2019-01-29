<?php /* все буквы заглавные

Все буквы текста становятся заглавными (средствами PHP, а не CSS - чтобы сохранились при выделении и копировании).

Смотрите кинофильм {_ZAG:Веселые клоуны - 2_}.
*/

function ZAG($e) {
    if(!function_exists('mb_strtoupper')) return $e;
    return mb_strtoupper($e,$GLOBALS['wwwcharset']);
}
?>