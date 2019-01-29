<?php

function FRIENDS($e) {

$conf=array_merge(array(
'styles'=>"padding:5px 0 5px 0;margin:5px 0 5px 0;border:3px dotted red;",
'cleantime'=>1500,
'height'=>"3",
'page0'=>"",
'page1'=>"<p><center><input type='button' value='я уже прочел' onclick='imbload_time(nowtime)'> &nbsp; "
."<input type='button' value='день раньше' onclick='imbload_time(nowtime-86400)'>"
."<input type='button' value='3 дня' onclick='imbload_time(nowtime-86400*3)'>"
),parse_e_conf($e));

STYLES("friends","
.friends {".$conf['styles']."}
.imbsite {font-size:10px; color:#887755;}
");

$e=explode("\n",($conf['body']));
$roo=$_SERVER["HTTP_HOST"]; // trim($GLOBALS['blog_name']);

$f=array(); foreach($e as $l) { $l=trim($l,"/\n\r\t\'\" "); if($l=='') continue;
		if(strstr($l,'http://')) $l=substr($l,7);
		if(!isset($f[$l])) $f[$l]=$l;
        }

SCRIPTS("friendlist","
var friend=['".implode("','",$f)."'];
var lisen=[];
var nowtime=".time().";

function imbload_time(t) {
	f_save('friend_last_time',t);
	window.location='".$GLOBALS['mypage']."?'+t+'_".rand(0,32000)."';
}

function mk_imbload() {
	var t=1*f_read('friend_last_time');
	var ww=(getWinW()-57-6)+'px';
	for(var i in friend) { var id='imbload'+i;
		var l=friend[i].replace(/^([^\/]+).*?$/g,'\$1');
		if(!in_array(l,lisen)) lisen.push('http://'+l);
		var src='http://'+friend[i]+'/imbload?limit=2&time='+t+'#IMBLOAD|$roo|'+id;
		zabil('friends',vzyal('friends')+\"<div><a class='imbsite' href='http://\"+friend[i]+\"'>http://\"+friend[i]+\"</a></div>\\
<iframe width=\"+ww+\" height='".$conf['height']."' id='\"+id+\"' class='friends' src='\"+src+\"'></iframe>\");
	}
setTimeout('clean_imbload()',".$conf['cleantime'].");
}

function clean_imbload() {
	for(var i in friend) { var id='imbload'+i;
		if(idd(id)) {
			var h=1*idd(id).style.height.replace(/[^\\d]+/g,'');
			if(h<".$conf['height'].") clean(id);
		}
	}
}

if(window.top === window.self) {
function listener(e){ if(!in_array(e.origin,lisen)) return;
	var r=e.data.split('|');
	if(r[0]=='HH') { idd(r[1]).style.height=(1*r[2]+15)+'px'; return; }
	if(r[0]=='NO') { clean(r[1]); return; }
} if(window.addEventListener) window.addEventListener('message',listener,false); else window.attachEvent('onmessage',listener);
}

page_onstart.push('mk_imbload()');
");

return $conf['page0']."<div id='friends'></div>".$conf['page1'];
}
?>