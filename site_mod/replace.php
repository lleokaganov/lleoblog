<?php /* заменить

{_replace: *|<img src='/design/ico/ico-lleo.png'>| Раз * два * три! _}
*/

function replace($e) {
    list($from,$to,$text)=explode('|',$e,3);
    return str_replace($from,$to,$text);
}
?>