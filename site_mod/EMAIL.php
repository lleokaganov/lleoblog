<?php /* модуль EMAIL


PS: Служебная переменная $GLOBALS['ANONS_count'] устанавливается равной числу выбранных постов.
Ее можно затем вывести командой {_PHPEVAL: $o=$GLOBALS['ANONS_count']; _}

*/

function EMAIL($e) {

$e=trim(str_ireplace(array("\nSubj:","\nSubject:","\nFrom:","\nTo:","\nDate:"),array("\nsubj=","\nsubj=","\nfrom=","\nto=","\ndate="),"\n".$e));

$cf=array_merge(array(
'type'=>0,
'to'=>'',
'from'=>'',
'subj'=>'',
'date'=>'',
'left'=>'50',
'right'=>'50',
'color'=>'#fffff0',
'head'=>'email',
'nomail'=>0,
'nofrom'=>0,
'noto'=>0,
'template'=>"<div style='margin-left:{left}px; margin-right:{right}px; border:1px solid red;'>"
."<div style='padding:20px; background-color:{color}; border-bottom: 1px solid red'>"
."<div><b><tt>Date: </tt></b>{date}</div>"
."<div><b><tt>From: </tt></b>{from}</div>"
."<div><b><tt>To: &nbsp; </tt></b>{to}</div>"
."<div><b><tt>Subj: </tt></b>{subj}</div>"
."</div>"
."<div style='padding:20px; background-color:{color}'>{body}</div></div>"
),parse_e_conf($e));

if($cf['type']==1) { $cf['color']='#fff0ff'; $cf['left']='100'; $cf['right']='0'; }

//#fffff0 #f0ffff #fffff0

$cf['from']=h($cf['from']);
$cf['to']=h($cf['to']);

if($cf['nomail']==1) $cf['noto']=$cf['nofrom']=1;
if($cf['nofrom']) $cf['from']=preg_replace("/\s*<*[^\s]+\@[^\s]+>*/s",'',$cf['from']);
if($cf['noto']) $cf['to']=preg_replace("/\s*<*[^\s]+\@[^\s]+>*/s",'',$cf['to']);

return mper($cf['template'],$cf);
}
?>