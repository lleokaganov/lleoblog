<?php /* Меню 'а ля mary kay'

Этот модуль был сделан по просьбе одного сайта. При наведении мышкой на ссылки меняется картинка и описание.

Формат заиси прост: через разделитель | в каждой строке указываем:

просто имя картинки, лежащей в фотоальбоме, либо полный урл (определит само) | заголовок | линк | подробное описание

<script>function menukay(i,img,text) { idd('img'+i).src=img; zabil('text'+i,text); }</script>

{_MENUKAY:

http://lleo.aha.ru/blog/photo/1.jpg | Вить гнездо | /popa/gnezdo/index.html | Вить гнездо - древнейшее искусство, доставшееся нам от пернатых предков. Гнездо защищает от холода в морозы и эстетически привлекательно. Наша уникальная методика позволит каждой женщине легко свить гнездо из ткани или туалетной бумаги.

http://lleo.aha.ru/blog/photo/2.jpg | Спать на рабочем месте | /postel/podushka | Здоровому организму необходим ежедневный сон. Для сна нужна подушка, но где ее взять в офисе? О том, как сделать из ладоней подушку, расскажет наш раздел 'Постельное белье своими руками'.

http://lleo.aha.ru/blog/photo/3.jpg | Камни в позвоночнике | /medical/super/spina | Ломит спину? Чешется между лопатками? По исследованиям британских ученых, около 50% современных женщин страдают от камней в позвоночнике. Палочка-чесотка позволит избавиться от камней без операции за одно занятие!

_}

*/


function MENUKAY($e) { global $menykay_n;

$menykay_n++;

SCRIPTS("MenyKay procedure"," function menukay(i,img,text) { 

if(img.indexOf('/')) idd('img'+i).src=img; else idd('img'+i).src='".$GLOBALS['foto_www_small']."'+img;

zabil('text'+i,text); } ");

$m=explode("\n",$e); foreach($m as $n=>$l) { if(c($l)=='') { unset($m[$n]); continue; }
	list($img,$txt,$link,$text)=explode('|',$l,4);
	$m[$n]=array('img'=>c($img),'txt'=>c($txt),'link'=>c($link),'text'=>c($text));
}


$s=''; foreach($m as $p) {
  $s.="<p><a onmouseover=\"menukay('".$menykay_n."','".$p['img']."','".njs($p['text'])."')\" href='".$p['link']."'>".$p['txt']."</a>";
}

return "<center><table><tr valign=center>"
."<td><img id='img".$menykay_n."' src='".(strstr($m[0]['img'],'/')?$m[0]['img']:$GLOBALS['foto_www_small'].$m[0]['img'])."' hspace=5 vspace=5 border=0></td>"
."<td>{_CENTER:".$s."_}</td></tr></table>"
."<div align=justify id='text".$menykay_n."'>".$m[0]['text']."</div></center>";

}



?>