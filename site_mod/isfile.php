<?php /* וסכט וסעü פאיכ - עמ ןונגמו, וסכט םוע - געמנמו

Ýעמע {_isfile: site_mod/isfile.php | <a href='/site_mod/isfile.php'>פאיכ וסעü</a> | פאיכא םוע _}
Ýעמע {_isfile: site_mod/isfile111111.php | <a href='/site_mod/isfile111111.php'>פאיכ וסעü</a> | פאיכא םוע _}
*/

function isfile($e) { list($file,$text1,$text0)=explode('|',$e,3);
    $file=ltrim(h($file),'/'); $f=rpath($GLOBALS['filehost'].$file);
    return str_ireplace('{file}','/'.$file,(is_file($f)?$text1:$text0));
}
?>