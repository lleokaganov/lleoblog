<?php

include "../config.php";
if(!function_exists('iconv')) include $include_sys."iconv.php";
// include $include_sys."_autorize.php";

$text=h(preg_replace("/[\n\r\t ]+/si"," ",uw($_GET['t'])));

$link=h($_GET['l']);
$date=intval($_GET['d']);


if(isset($_GET['m'])) { $mode=h($_GET['m']);
//	header("Content-Type: image/png"); die(file_get_contents("../re.png"));

$s="<script>
function lleoblogpanel_f5_sup(){ return ('localStorage' in window) && window['localStorage'] !== null; }
function lleoblogpanel_f5_save(n,v){ if(!lleoblogpanel_f5_sup()) return false; window['localStorage'][n]=v;return lleoblogpanel_f5_read(n)==v?true:false; }
function lleoblogpanel_f5_read(n){ if(!lleoblogpanel_f5_sup()) return false; var v=window['localStorage'][n]; return v==undefined ? '' : v; }

//if(lleoblogpanel_f5_read('clipboard_mode')!='') alert('mode='+lleoblogpanel_f5_read('clipboard_mode'));
//if(lleoblogpanel_f5_read('clipboard_link')!='') alert('link='+lleoblogpanel_f5_read('clipboard_link'));
//if(lleoblogpanel_f5_read('clipboard_text')!='') alert('text='+lleoblogpanel_f5_read('clipboard_text'));

lleoblogpanel_f5_save('clipboard_mode',\"".$mode."\");
lleoblogpanel_f5_save('clipboard_link',\"".$link."\");
lleoblogpanel_f5_save('clipboard_text',\"".$text."\");
</script>";

die("<html><body bgcolor=red>&nbsp;$s</body></html>");
}

$s="link: <a href='$link'>$link</a><br>$text";
$close="<div onclick='lleoblogpanel_clean(\\\"lleoblogpanel\\\")' style='cursor:pointer;color:blue;position:absolute;top:3px;right:3px;'>close</div>";

$s="<input style='font-size:14px' id='lleoblogpanel_link' size=50 value='$link'> <input id='lleoblogpanel_date' size=3 value='3'>
<br><textarea style='font-size:14px' id='lleoblogpanel_text' cols=64 rows=".max(4,page($text,50)).">$text</textarea>
<br><input type=button value='Copy link' onclick='lleoblogpanel_send(this)'>
 <input type=button value='Matom!' onclick='lleoblogpanel_send(this)'>
 <input type=button value='Readability' onclick='lleoblogpanel_send(this)'>
 <input type=button value='Black&White' onclick='lleoblogpanel_send(this)'>
 <input type=button value='href' onclick='lleoblogpanel_send(this)'>
 <input type=button value='aliexpress' onclick='lleoblogpanel_send(this)'>
";

$s=str_replace("\n","",$s);

die(

(strstr($link,'trade.aliexpress.com')?"setTimeout(\"lleoblogpanel_send({value:'aliexpress'})\",50);":''). // сразу тогда запустить

"function lleoblogpanel_send(e) {

	if(e.value=='aliexpress') {

	    var c,p,s=document.body.innerHTML;
	    /* link */ var link=document.location.href;
	    /* num */ c=s.match(/<dd class=\"order-no\">(\d+)<\/dd>/i); var num=(c!=null?c[1]:'NOT');
	    /* trk */ var trk='';

	    p=s.match(/class=\"logistics-name\"[^>]*>[^<>]+<\/span>\s*<\/td>\s*<td class=\"no\">([^\s<>])+/gi);
	    if(p!=null) for(var i in p) { if(!isNaN(i)) {
	        c=p[i].match(/<td class=\"no\">([^\s<>]+)/i); var trk=trk+(c!=null?c[1]:'NOT')+' ';
	    }} if(trk=='') trk=\"NOT-YET\";

	    /* items */
	    var r=[]; p=s.match(/<td class=\"baobei\">[^$]+?<\/td>/gi);

	    if(p!=null) for(var i in p) { if(!isNaN(i)) {
	        c=p[i].match(/<a class=\"baobei-name\"[^>]+>([^<>]+)</i); var ctxt=(c!=null?c[1]:'NOT'+i);
	        c=p[i].match(/<a class=\"baobei-name\"[^>]+href=\"([^\"\">]+)\"/i); var curl=(c!=null?c[1]:'NOT'+i);
	        c=p[i].match(/<img src=\"([^\"\">]+)\"/i);

 	    if(c==null) alert(lleoblogpanel_print_r(p[i]));
	    var cimg=(c!=null?c[1]:'NOT'+i);
	        var k=1; for(var ii in r) if(r[ii]['txt']&&r[ii]['txt']==ctxt) k=0;
	        if(k) r[i]={txt:ctxt,url:curl,img:cimg};
	    } }

	    var o='\\n'; for(var i in r) { if(!isNaN(i)) {
		var url=r[i].url.match(/(\\d+)\\.html/)[1];
		var img=r[i].img.match(/\\/UT8(.+)$/)[1];
		o+='\\n'+trk+(i>0?' ('+i+')':'')+' | '+num+' | '+url+' | '+img+' | '+r[i].txt+'\\n';
	    }}

	return lleoblogpanel_zab(o);
	}

	if(e.value=='Black&White') {
		var elems = document.getElementsByTagName('*');
		for(var i=0;i<elems.length;i++) {
			elems[i].style.backgroundColor = '#fff';
			elems[i].style.backgroundImage = '';
			elems[i].style.color = '#000';
		}
	lleoblogpanel_closeiframe(); return;
	}

	if(e.value=='href') {
		var s='';
		var pp=document.getElementsByTagName('*');
		for(var i=0;i<pp.length;i++) {
			var l=pp[i].href; if(l && l.replace(/javascript\:/g,'')==l) s=s+'\\n'+l;
		}
	return lleoblogpanel_zab(s);
	}

	if(e.value=='Matom!') {
		lleoblogpanel_loadScript('mat.user.js');
	lleoblogpanel_closeiframe(); return;
	}

	if(e.value=='Readability') {
		readStyle='style-newspaper';readSize='size-medium';readMargin='margin-wide';_readability_script=document.createElement('script');_readability_script.type='text/javascript';_readability_script.src='http://lab.arc90.com/experiments/readability/js/readability.js?x='+(Math.random());document.documentElement.appendChild(_readability_script);_readability_css=document.createElement('link');_readability_css.rel='stylesheet';_readability_css.href='http://lab.arc90.com/experiments/readability/css/readability.css';_readability_css.type='text/css';_readability_css.media='all';document.documentElement.appendChild(_readability_css);_readability_print_css=document.createElement('link');_readability_print_css.rel='stylesheet';_readability_print_css.href='http://lab.arc90.com/experiments/readability/css/readability-print.css';_readability_print_css.media='print';_readability_print_css.type='text/css';document.getElementsByTagName('head')[0].appendChild(_readability_print_css);
	lleoblogpanel_closeiframe(); return;
	}

var link='".$httphost."ajax/m.php?m='+encodeURIComponent(e.value)+'&d='+encodeURIComponent(lleoblogpanel_idd('lleoblogpanel_date').value)+'&l='+encodeURIComponent(lleoblogpanel_idd('lleoblogpanel_link').value)+'&t='+encodeURIComponent(lleoblogpanel_idd('lleoblogpanel_text').value);
var q=lleoblogpanel_idd('lleoblogpanelc');
q.innerHTML=\"<iframe src='\"+link+\"' width='10' height='10' onload='lleoblogpanel_closeiframe()'></iframe>\"+q.innerHTML;
lleoblogpanel_closeiframe(); return;
}

function lleoblogpanel_zab(o) {
	lleoblogpanel_idd('lleoblogpanel_text').value=o;
	var e=lleoblogpanel_idd('lleoblogpanel_text'); e.focus(); e.select();
	return;
}

function lleoblogpanel_loadScript(src){ src='".$httphost."js/'+src;
        var s = document.createElement('script');
        s.setAttribute('type', 'text/javascript');
        s.setAttribute('charset', '".$wwwcharset."');
        s.setAttribute('src', src);
        var head = document.getElementsByTagName('head').item(0);
        head.insertBefore(s, head.firstChild);
}

function lleoblogpanel_mkdiv(id,cont,cls,paren){ if(lleoblogpanel_idd(id)) { lleoblogpanel_idd(id).innerHTML=cont; lleoblogpanel_idd(id).className=cls; return; }
        var div=document.createElement('DIV'); div.className=cls; div.id=id; div.innerHTML=cont; div.style.display='none';
        if(paren==undefined) paren=document.body; paren.insertBefore(div,paren.lastChild);
}

function lleoblogpanel_closeiframe() { setTimeout('lleoblogpanel_zakryl(\"lleoblogpanel\")',500); }
function lleoblogpanel_idd(id) { return document.getElementById(id); }
function lleoblogpanel_zabil(id,text) { lleoblogpanel_idd(id).innerHTML = text; }
// function lleoblogpanel_vzyal(id) { return lleoblogpanel_idd(id).innerHTML; }
function lleoblogpanel_zakryl(id) { lleoblogpanel_idd(id).style.display='none'; }
function lleoblogpanel_otkryl(id) { lleoblogpanel_idd(id).style.display='block'; }
function lleoblogpanel_print_r(a,n,skoka) { var s='',t='',i,v; if(!n)n=0; for(i=0;i<n*10;i++)t=t+' '; if(typeof(a)!='object') return a; 


for(var j in a){ if(typeof(j)=='undefined' || typeof(a[j])=='undefined') break; 

v=a[j]; if(v!=null && !skoka && typeof(v)=='object' && typeof(v.innerHTML)!='string') v=lleoblogpanel_print_r(v,n+1); 

if(typeof(v)!='function') s='\\n'+t+j+'='+v+s;

} return s; }


function lleoblogpanel_getScrollH(){ return (document.documentElement.scrollTop || document.body.scrollTop); }
function lleoblogpanel_getScrollW(){ return (document.documentElement.scrollLeft || document.body.scrollLeft); }
function lleoblogpanel_getWinW(){ return window.innerWidth?window.innerWidth : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth; }
function lleoblogpanel_getWinH(){ return window.innerHeight?window.innerHeight : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight; }
function lleoblogpanel_getDocH(){ return document.compatMode!='CSS1Compat' ? document.body.scrollHeight : document.documentElement.scrollHeight; }
function lleoblogpanel_clean(id){ if(lleoblogpanel_idd(id)){ lleoblogpanel_zakryl(id); 
// setTimeout(\"var s=lleoblogpanel_idd('\"+id+\"'); if(s) s.parentNode.removeChild(s);\",40); 
}}

function lleoblogpanel_posdiv(id){ lleoblogpanel_otkryl(id);
var e=lleoblogpanel_idd(id),W=lleoblogpanel_getWinW(),H=lleoblogpanel_getWinH(),w=e.clientWidth,h=e.clientHeight;
var x=(W-w)/2+lleoblogpanel_getScrollW(),y=(H-h)/2+lleoblogpanel_getScrollH();
var DH=W-10; if(w<DH && x+w>DH) x=DH-w; if(x<0) x=0;
    DH=lleoblogpanel_getDocH()-10; if(h<DH && y+h>DH) y=DH-h; if(y<0) y=0;
e.style.top=y+'px'; e.style.left=x+'px';
}

var s=\"$s\";

if(!lleoblogpanel_idd('lleoblogpanel'))
document.body.innerHTML+=\"<div id='lleoblogpanel' style='position:absolute;z-index:99999;border:20px solid black;padding: 20px; background-color: rgb(255,252,223); text-align:justify;'>$close<div id='lleoblogpanelc'>\"+s+\"</div></div>\";
else { lleoblogpanel_zabil('lleoblogpanelc',s); lleoblogpanel_otkryl('lleoblogpanel'); }
lleoblogpanel_posdiv('lleoblogpanel',-1,-1);
");

function uw($s) { return iconv("utf-8","windows-1251//IGNORE",$s); }

function h($s) { return htmlspecialchars($s); }
function page($l,$c=50) { $m=explode("\n",$l); $i=0; foreach($m as $t) if(strlen($t)<$c) $i++; else $i=$i+1+(floor(strlen($t)/$c)); return($i); }

?>