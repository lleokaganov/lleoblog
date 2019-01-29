<?php /* Беседы в чате

{_CHAT:
Привет!
 Ну привет...
Чо делаешь?
 Телек смотрю.
 А что?
Сам дурак!
_}

*/

function CHAT($e) {
	$cf=array_merge(array('WIDTH'=>500,'COLOR'=>'white'),parse_e_conf($e));

STYLES("Чаты",'
.chatfr,.chatto { position:relative; border-radius:15px; padding:8px 20px; font-family:"Helvetica Neue"; font-size:16px; font-weight:normal; margin-bottom:8px;}

.chatfr:before,.chatto:before,.chatfr:after,.chatto:after { content:""; position:absolute; bottom:-2px; height:20px; }
.chatfr:before,.chatto:before { transform:translate(0,-2px); }
.chatfr:after,.chatto:after { width:26px; transform:translate(-30px,-2px); background: '.($cf['COLOR']?'#F0F0EA':'white').'; }

.chatfr { color: white; background: #0B93F6; float: right; }
.chatfr:before { z-index:-1; right:-7px; border-right:20px solid #0B93F6; border-bottom-left-radius: 16px 14px; }
.chatfr:after { z-index:1; right:-56px; border-bottom-left-radius:10px; }

.chatto { color: black; background: #E5E5EA; float: left; }
.chatto:before { z-index: 2; left:-7px; border-left:20px solid #E5E5EA; border-bottom-right-radius: 16px 14px; }
.chatto:after { z-index: 3; left:4px; border-bottom-right-radius:10px; }
');

	$e=explode("\n\n",($cf['body'])); if(sizeof($e)<2) $e=explode("\n",($cf['body']));
	$s=''; foreach($e as $l) { if($l=='') continue; $s.="<div class='".(' '==substr($l,0,1)?'chatto':'chatfr')."'>".c($l)."</div><div class=q></div>"; }
	return $s; // "<center>".$s."</center>";

}
