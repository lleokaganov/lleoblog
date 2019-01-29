<?php /* Кросспост в соцсети
{_SHARE:
url=http://lleo.me/dnevnik/contents
head=Заголовок моего поста
text=Вот такой текстик у меня
img=http://lleo.me/dnevnik/design/userpick_germany2015.jpg
_}
*/

STYLE_ADD($GLOBALS['www_css'].'share.css');

$GLOBALS['SHARE_NET']=array(
'fb'=>"<a target=_blank alt='{repost}Facebook' class='shr shr_fb' href=\"".'https://www.facebook.com/sharer.php?src=sp&u={wurl:url}'."\"></a>",
'lj'=>"<a target=_blank alt='{repost}Livejournal' class='shr shr_lj' href=\"".'https://www.livejournal.com/update.bml?subject={wurl:head}&event={wurl:url}%0A{wurl:text}'."\"></a>",
'vk'=>"<a target=_blank alt='{repost}VKontakte' class='shr shr_vk' href=\"".'http://vk.com/share.php?url={wurl:url}&title={wurl:head}&description={wurl:text}'.($q['img']!=''?'&image={wurl:image}':'')."\"></a>",
'tw'=>"<a target=_blank alt='{repost}Twitter' class='shr shr_tw' href=\"".'https://twitter.com/intent/tweet?status={wurl:head}%20{wurl:url}'."\"></a>",
'ok'=>"<a target=_blank alt='{repost}Одноклассники' class='shr shr_ok' href=\"".'https://connect.ok.ru/dk?st.cmd=WidgetSharePreview&st.shareUrl={wurl:url}'."\"></a>",
'g+'=>"<a target=_blank alt='{repost}Google+' class='shr shr_gp' href=\"".'https://plus.google.com/share?url={wurl:url}'."\"></a>",
'vb'=>"<a target=_blank alt='{repost}Viber' class='shr shr_vb' href=\"".'viber://forward?text={wurl:head}%20{wurl:url}'."\"></a>",
'wa'=>"<a target=_blank alt='{repost}WhatsApp' class='shr shr_wa' href=\"".'whatsapp://send?text={wurl:head}%20{wurl:url}'."\"></a>"
);

function SHARE($e='') { $q=array_merge(array(
'url'=>getlink($GLOBALS['article']['Date']),
'template'=>'',
'head'=>$GLOBALS['article']['Header'],
'text'=>$GLOBALS['article']['Header'], // $GLOBALS['article']['Body'],
'img'=>'',
'net'=>"fb,vk,tw,ok,lj,g+,vb,wa",
'repost'=>'репост в '
),parse_e_conf($e));

// dier($q);

if(!empty($q['template'])) $q['head']=mpers($q['template'],$q);
$s=''; foreach(explode(',',$q['net']) as $l) $s.=$GLOBALS['SHARE_NET'][$l];
return mpers($s,$q);
}
?>