<?php

/*


*/







//    'Date'=>$modp,'Header'=>$modp,'Body'=>'{_'.$modp.':_}',
// 'Access'=>'all','DateUpdate'=>0,'num'=>0,'DateDatetime'=>0,'DateDate'=>0,
// 'opt'=>'a:3:{s:8:"template";s:5:"blank";s:10:"autoformat";s:2:"no";s:7:"autokaw";s:2:"no";}',
// 'view_counter'=>0




    $GLOBALS['article']['opt']='a:3:{s:8:"template";s:7:"rasskaz";s:10:"autoformat";s:2:"pd";s:7:"autokaw";s:2:"no";}';


function TAG($e) {

    $tag=h($_SERVER['QUERY_STRING']);

    return "<p class=name>".$tag."</p>
{_CENTER:<div class=r><ol>{_ANONS:
template = <p class=name><a href='{link}'>{Header}</a></p><p>{Body}<p>
days = 999999999
mode = blog
tags = ".$tag."
sort = update
length = 0
media=2
limit = 999999
_}</ol></div>_}";
}

?>