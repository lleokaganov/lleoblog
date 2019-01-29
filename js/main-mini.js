// обращения, которые обязаны идти только через общее окно

var mainnames=[
'mailbox.php:answer',
'foto.php:album',
/*
'adminsite.php:save',
'adminsite.php:del',
*/

// 'protocol.php:post',

'editor.php:findimg_form',

'login.php:do_login',
'login.php:do_logout',
'login.php:getinfo',
'editor.php:tags',
'editor.php:move',
'editor.php:settings_win'
];

var alertmajax=0;

var activid=false; // id активного окна
var hid=1; // счетчик окон
var mHelps={}; // массив для окон: id:[hotkey,zindex]
var hotkey=[]; // [code,(ctrlKey,shiftKey,altKey,metaKey),func]
var hotkey_def=[]; // хоткеи главного окна
var nonav=0; // отключить навигацию и буквенные хоткеи

if(mnogouser) page_onstart.push("if(ux=='c') { ifhelpc(xdom,'xdomain','xdomain'); }");

//========================================================
// заплатки:

function posdiv(id,x,y) {}
function c_read(v) { return false; }
function c_save(v,s) { return false; }


function uneval(s) { switch(typeof(s)) {
  case 'string': s="'"+s+"'";
  case 'object': { var l='['; for(var i in s) l=l+"'"+s[i]+"',"; s=l.replace(/,$/g,'')+']'; }
  case 'function': s=''+s;
} return s.replace(/[\n\t\r ]+/g,' ');
}

function setkey(k,v,f,o,nav){ return;
//	alert(hl(uneval(k)+','+uneval[v]+','+uneval[f]+','+(o?'true':'false')+','+(nav?1:0)));
//	return;
//  sendm("SETKEY;s="+hl(s));
/*
 var s='';
  if(typeof(k)=='string') s=s+"'"+k+"'";
  else { s=s+"["; for(var i in k) { s=s+"'"+k[i]+"',"; } s=s.replace(/,$/g,'')+']'; }
  s=s+",'"+v+"',"+(''+f).replace(/[\n\t\r ]+/g,' ')+','+(o?'true':'false')+','+(nav?1:0);
  sendm("SETKEY;s="+hl(s));
*/
}
//========================================================
// 1 - Браузеры. 2 - IE. 3 - Неизвестно.
function browser(){ return (document.createRange) ? 1 : (-[1,]) ? 3 : 2; }

// function edd(e){ return typeof(e)=='string'?idd(e):e; }

function idd(id){ if(typeof(id)=='object') return id;
        if(typeof(document.getElementById(id))=='undefined') return false;
        return document.getElementById(id);
}
function zabil(id,text) { if(idd(id)) { idd(id).innerHTML=text; return init_tip(idd(id)); } sendm("ZABIL;id="+hl(id)+";text="+hl(text)); }

function doclass(cla,f,s) { var p=getElementsByClass(cla);
for(var i in p) { if(typeof(p[i])!='undefined' && typeof(p[i].className)!='undefined') f(p[i],s); } }

function zabilc(cla,s) { doclass(cla,function(e,s){e.innerHTML=s;},s); }

function vzyal(id) { return idd(id)?idd(id).innerHTML:''; }
function zakryl(id) { if(!idd(id)) return; idd(id).style.display='none'; if(id!='tip') zakryl('tip'); }
function otkryl(id) { if(idd(id)) idd(id).style.display='block'; }

function cphash(a) { 
var b={}; for(var i in a) {
	if(typeof(a[i])!='undefined'){
		if(typeof(a[i])=='object' && typeof(a[i]['innerHTML'])!='string') b[i]=cphash(a[i]); else b[i]=a[i];}
} return b; }

function cpmas(a) { var b=[]; for(var i=0;i<a.length;i++){
		if(typeof(a[i])!='undefined'){
		if(typeof(a[i])=='object' && typeof(a[i]['innerHTML'])!='string') b[i]=cphash(a[i]); else b[i]=a[i];}
} return b; }

// var oknon=0; if(oknon) return 1; 
function isHelps(){ // найти верхнее окно или false
	var max=0,id=false; for(var k in mHelps){ if(mHelps[k][1]>=max){max=mHelps[k][1];id=k;} } return id;
}

function print_r(a,n,skoka) {
	var s='',t='',i,v; if(!n)n=0; for(i=0;i<n*10;i++)t=t+' ';
	for(i in a){v=a[i]; if(!skoka && typeof(v)=='object' && typeof(v['innerHTML'])!='string') v=print_r(v,n+1); s='\n'+t+i+'='+v+s;}
	return s;
}

function in_array(s,a){ var l; for(l in a) if(a[l]==s) return l; return false; }

function clean(id) {
	if(typeof(id)=='object') {
		if(typeof(id.id)!='undefined'&&id.id!='') id=id.id; // если есть имя, то взять имя
		else { var t='tmp_'+(hid++); id.id=t; id=t; } // иначе блять присвоить
	}
	if(idd(id) && id!=document.body.id) { zakryl(id); setTimeout("var s=idd('"+id+"'); if(s) s.parentNode.removeChild(s);",40); }
	// иначе передать наверх
	else { sendm("CLEAN;id="+id+";iddo="+IMBLOAD_MYID+";#"); }
	zakryl('tip');
}

var JSload={};

function loadScript(src,f){ if(src.indexOf('://')<0) src=www_js+src; if(JSload[src]=='load') return; loadScr(src,f); }

function loadScr(src,f){ if(f){if(!f.s)f.s=[];if(!f.n)f.n=0;}
	var s=document.createElement('script');
	s.setAttribute('type','text/javascript');
	s.setAttribute('charset', wwwcharset);
	s.setAttribute('src',src);
	if(f) { s.async=false;
		s.onreadystatechange=s.onload=function(){ var c=s.readyState;
                if(!f.s[src]&&(!c||/loaded|complete/.test(c))){f.s[src]=1;f.n++;f(src);}
	};}
	// IE crashes on using appendChild before the head tag has been closed.
	var head=document.getElementsByTagName('head').item(0);
	head.insertBefore(s, head.firstChild);
	ajaxon();
}

function loadScriptBefore(src,runtext){
	if(JSload[src]=='load') return eval(runtext); if(JSload[src]) return; JSload[src]=runtext; loadScript(src);
}

function loadStyle(src,f){
        var headID = document.getElementsByTagName('head')[0];
        var s = document.createElement('link');
        s.type = 'text/css';
        s.rel = 'stylesheet';
        s.href = src;
        s.media = 'screen';
	if(f) { s.async=false;
		s.onreadystatechange=s.onload=function(){
		var c=s.readyState; if(!f[src]&&(!c||/loaded|complete/.test(c))){f[src]=1;f(src);}
	};}
        headID.appendChild(s);
}

function loadCSS(src){ src=www_css+src; if(JSload[src]=='load') return; JSload[src]='load';
        loadStyle(src);
        ajaxon();
}

if(document.getElementsByClassName) getElementsByClass=function(classList,node){
return (node||document).getElementsByClassName(classList)};
else {
    getElementsByClass = function(classList, node) {
	        var node = node || document, list = node.getElementsByTagName('*'),
	        length = list.length, classArray = classList.split(/\s+/),
	        classes = classArray.length, result = [], i,j;

//	        for(i = 0; i < length; i++) { list[i].className='r'; alert(i+': '+list[i].className); }
//	alert('#'+print_r(list[16]));
//	return;

	        for(i = 0; i < length; i++) {
	            for(j = 0; j < classes; j++) {
	                if(list[i].className.search('\\b' + classArray[j] + '\\b') != -1) { // alert(1);
	                    result.push(list[i]);
	                    break;
	                }
	            }
	        }
	        return result;
    };
}

function addEvent(e,evType,fn) {
	if(e.addEventListener) { e.addEventListener(evType,fn,false); return true; }
	if(e.attachEvent) { var r = e.attachEvent('on' + evType, fn); return r; }
	e['on' + evType] = fn;
}

function removeEvent(e,evType,fn){
	if(e.removeEventListener) { e.removeEventListener(evType,fn,false); return true; }
	if(e.detachEvent) { e.detachEvent('on'+evType, fn) };
}

function ifid(id) { return firsthelp&&id!=IMBLOAD_MYID && id!=document.body.id; }

// function helps_cancel(id,f) { getElementsByClass('can',idd(id))[0].onclick=f; }
function helpc(id,s) { if(ifid(id)) return sendm("helpc;id="+hl(id)+";s="+hl(s));
helps(id,s); posdiv(id,-1,-1);
}
function ohelpc(id,z,s) { if(ifid(id)) return sendm("ohelpc;id="+hl(id)+";z="+hl(z)+";s="+hl(s));
helpc(id,"<fieldset><legend>"+z+"</legend>"+s+"</fieldset>");
}
function ohelp(id,z,s) { if(ifid(id)) return sendm("ohelp;id="+hl(id)+";z="+hl(z)+";s="+hl(s));
helps(id,"<fieldset><legend>"+z+"</legend>"+s+"</fieldset>");
}
function idie(s,t) { if(ifid('idie')) return sendm("idie;s="+hl(s)+";t="+hl(t));
var e=typeof(s); if(e=='object') s="<pre style='max-width:"+(getWinW()-200)+"px'>"+print_r(s)+'</pre>'; if(t!=undefined) s=t+'<p>'+s; ohelpc('idie','Error type: '+e,s)
} dier=idie;

var firsthelp=0;

function helps(id,s,pos,cls) {
	if(ifid(id)) return sendm("helps;id="+hl(id)+";s="+hl(s)+";pos="+hl(pos)+";cls="+hl(cls));
	document.body.id=id; // document.write(s);
	zabil(id,s);
	if(IMBLOAD_MYID!=id) { sendm("RENAME_ID;newid="+hl(id)+";id="+IMBLOAD_MYID); }
	resize_me(1);
	firsthelp=1;
}

// tip

function init_tip(w) { var tip_x=-35,tip_y=25; if(!idd('tip')) {
mkdiv('tip','','b-popup bubble-node b-popup-noclosecontrol');
zabil('tip','<div class="b-popup-outer"><div class="b-popup-inner"><div id="rtip"></div><i class="i-popup-arr i-popup-arrtl"><i class="i-popup-arr-brdr-outer"><i class="i-popup-arr-brdr-inner"><i class="i-popup-arr-bg"></i></i></i></i><i class="i-popup-close"></i></div></div>');
}
	var a,s,e; var el=['a','label','input','img','span','div'];
	for(var j=0;j<el.length;j++){ e=w.getElementsByTagName(el[j]); if(e){ for(var i=0;i<e.length;i++){ a=e[i];
		s=a.getAttribute('title')||a.getAttribute('alt');
		if(s && a.onMouseOver==undefined){ a.setAttribute('tiptitle',s);
		a.removeAttribute('title'); a.removeAttribute('alt');
		addEvent(a,'mouseover',function(){ idd('rtip').innerHTML=this.getAttribute('tiptitle'); posdiv('tip',mouse_x+tip_x,mouse_y+tip_y); } );
		addEvent(a,'mouseout',function(){ zakryl('tip') } );
		addEvent(a,'mousemove',function(){ posdiv('tip',mouse_x+tip_x,mouse_y+tip_y) } );
		}
	}}}
init_nonav(w); // и отключить навигацию для INPUT и TEXTAREA
}

function init_nonav(w) { // отключить навигацию для всех INPUT и TEXTAREA в w
	var a,e; var el=['input','textarea'];
	for(var j=0;j<el.length;j++){ e=w.getElementsByTagName(el[j]); if(e){ for(var i=0;i<e.length;i++){ a=e[i];
		if(a.onFocus==undefined) addEvent(a,'focus',function(){nonav=1});
	}}}
}

page_onstart.push("init_tip(document)");

function mkdiv(id,s,cls,paren,relative){ if(idd(id)) { zabil(id,s); idd(id).className=cls; return; }
        var div=document.createElement('DIV');
        div.className=cls; div.id=id; div.innerHTML=s; div.style.display='none';
        if(paren==undefined) paren=document.body;
        if(relative==undefined) paren.appendChild(div); // paren.lastChild
        else paren.insertBefore(div,relative.nextSibling);
}

//==========





















// координаты мыши
var mouse_x=mouse_y=0;
document.onmousemove = function(e){ if(!e) e=window.event;
  if(e.pageX || e.pageY) { mouse_x=e.pageX; mouse_y=e.pageY; }
  else if(e.clientX || e.clientY) {
    mouse_x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    mouse_y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }
	try{e=idd('ajaxgif'); e.style.top=15+mouse_y+'px'; e.style.left=15+mouse_x+'px';}catch(e){}
};

function ajaxon(){ var id='ajaxgif'; mkdiv(id,"<img src="+www_design+"img/ajax.gif>",'popup'); posdiv(id,15+mouse_x,15+mouse_y);}
function ajaxoff(){ clean('ajaxgif'); }

//function ajax(name,value,func) { ajaxon(); if(name.indexOf('://')<0) name=www_ajax+name;JsHttpRequest.query(name,value,function(responseJS,responseText){if(responseJS.status){ajaxoff();eval(func);}},true);}

var majax_lastu='',majax_lasta,majax_err=1; // =0

function tryer(er,e,js){
//if(!(e instanceof SyntaxError)) throw e;
alert(er+': '+e.name+'\n\n'+js);}

function mjax(url,a,id) {
		if(!id) id='im_'+(++hid);
//		if(!head) head='mjax-'+hid;
		if(url.indexOf('://')<0) url=www_ajax+url;
//		a.mjax=1;
	if(typeof(postMessage)!='function') src=www_ajax+'frame.htm#'+src; //+'?adr=iframe_binoniq#framel';

	helpc(id+'_r',"<iframe style='border: 0' onload='ajaxoff()' name='"+id+"' id='"+id+"' width='"+Math.floor(getWinW()/4)+"' height='"+Math.floor(getWinH()/4)+"'></iframe>");

//	ohelpc(id+'_r',head,"<iframe style='border: 0' onload='ajaxoff()' name='"+id+"' id='"+id+"' width='"+Math.floor(getWinW()/4)+"' height='"+Math.floor(getWinH()/4)+"'></iframe>");
//	return;
	ajaxon();
	postToIframe(a,url+'?mjax='+id+'&w='+getWinW()+'&h='+getWinH(),id);
//	ajaxoff();
// src='"+url+"' onload='ajaxoff()'
//	postToIframe({a:5,b:6}, '/count.php', 'frame1');
}


function sendm_a(a){var o=[]; for(var i in a) o.push(i+'='+hl(a[i])); return o.join('|'); }

function majax(url,a,js,sm){ // alert('majax!');

        if(mnogouser==1 || sm!=undefined) {
                var k=url+':'+a.a; for(var i in mainnames) if(k==mainnames[i]) return sendm("majax|url="+hl(url)+"|"+sendm_a(a));
	}

	majax_lasta=cphash(a); majax_lastu=url;
        if(typeof(up)!='undefined') a.up=up;
	if(typeof(hashpage)!='undefined') a.hashpage=hashpage;
        if(typeof(ux)!='undefined') a.ux=ux;
        if(typeof(upx)!='undefined') a.upx=upx;
        if(typeof(acn)!='undefined') a.acn=acn;

/*
	if(typeof(JsHttpRequest)=='undefined') {
		var u=xdomain+www_design+'JsHttpRequest.js';
		loadScr(u); // alert(u+' not found');
	}
*/

	// отладочная хня
//	if(alertmajax==1) alert(url+':'+a.a+"\n\n"+print_r(a));



ajaxform(0,url,a);

/*
	ajax(url,a,"if(responseJS.modo){ if(majax_err) eval(responseJS.modo); else {try{eval(responseJS.modo)}catch(e){tryer('majax error',e,responseJS.modo)}}\
"+(js==undefined?'':"try{eval(\""+js+"\")}catch(e){tryer('majax post-js error',e,\""+js+"\")}")+"\
}");
*/

}

// 1 - Браузеры. 2 - IE. 3 - Неизвестно.
var browsertype=(document.createRange)?1:(-[1,])?3:2;

function ajaxform(e,url,ara) { if(url.indexOf('://')<0) url=www_ajax+url; var z='lajax_'+(hid++);
url=url+'?lajax='+z+'&rando='+Math.random();
mkdiv(z+'_ifr',"<iframe width=1 height=1 frameborder=0 hspace=0 marginheight=0 marginwidth=0 vspace=0 name='"+z+"' id='"+z+"'></iframe>",'popup');
if(typeof(hashpage)!='undefined') ara.hashpage=hashpage;
if(typeof(upx)!='undefined') ara.upx=upx;
if(typeof(acn)!='undefined') ara.acn=acn;
if(typeof(e)=='object') { // уже форма есть
    e.target=z; e.enctype='multipart/form-data'; e.action=url; e.method='POST';
    e.setAttribute("target",z); e.setAttribute("enctype",'multipart/form-data'); e.setAttribute("action",url); e.setAttribute("method",'POST');
    if(ara) for(var i in ara) if(typeof(i)=='string') form_addpole(e,i,ara[i]);
	if(typeof(hashpage)!='undefined') form_addpole(e,'hashpage',hashpage);
        if(typeof(upx)!='undefined') form_addpole(e,'upx',upx);
        if(typeof(acn)!='undefined') form_addpole(e,'acn',acn);
    ajaxon(); e.submit();
    return true;
}
postToIframe(ara,url,z);
}


function form_addpole(e,n,v) { // v=v.replace(/\r/g,''); // нахуй эти возвраты кареток
if(/*n!='Date'&&*/n!='id'&&n!='action'&&n!='name'&&e[n]) return e[n].value=v;
var t; if(browsertype==2/*browser.isIE*/){t=document.createElement("<input type='hidden' name='"+h(n)+"' value='"+h(v)+"'>"); e.appendChild(t);}
else{ t=document.createElement("input"); e.appendChild(t); t.type="hidden"; t.name=n; t.value=v; }
}

function mijax(u,a) { a['up']=up; if(u.indexOf('://')<0) u=www_ajax+u; u+='?minj='+(new Date()).getTime();
	for(var i in a) u+='&'+encodeURIComponent(i)+'='+encodeURIComponent(a[i]);
	loadScr(u);
}

function setOpacity(e,n) { var o=getOpacityProperty(); if(!e || !o) return;
	if(o=='filter') { n *= 100; // Internet Exploder 5.5+
	// Если уже установлена прозрачность, то меняем её через коллекцию filters, иначе добавляем прозрачность через style.filter
		var oAlpha = e.filters['DXImageTransform.Microsoft.alpha'] || e.filters.alpha;
		if(oAlpha) oAlpha.opacity=n;
		else e.style.filter += 'progid:DXImageTransform.Microsoft.Alpha(opacity='+n+')'; // чтобы не затереть другие фильтры +=
	} else e.style[o]=n; // Другие браузеры
}

function getOpacityProperty() {
	if(typeof(document.body.style.opacity)=='string') return 'opacity'; // CSS3 compliant (Moz 1.7+, Safari 1.2+, Opera 9)
	else if(typeof(document.body.style.MozOpacity)=='string') return 'MozOpacity'; // Mozilla 1.6 и младше, Firefox 0.8 
	else if(typeof(document.body.style.KhtmlOpacity)=='string') return 'KhtmlOpacity'; // Konqueror 3.1, Safari 1.1
	else if(document.body.filters && navigator.appVersion.match(/MSIE ([\d.]+);/)[1]>=5.5) return 'filter'; // IE 5.5+
	return false;
}

function getScrollH(){ return (document.documentElement.scrollTop || document.body.scrollTop); }
function getScrollW(){ return (document.documentElement.scrollLeft || document.body.scrollLeft); }

function getWinW(){ return window.innerWidth?window.innerWidth : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth; }
function getWinH(){ return window.innerHeight?window.innerHeight : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight; }
function getDocH(){ return document.compatMode!='CSS1Compat' ? document.body.scrollHeight : document.documentElement.scrollHeight; }
function getDocW(){ return document.compatMode!='CSS1Compat' ? document.body.scrollWidth : document.documentElement.scrollWidth; }


// --- процедуры pins ---
function insert_n(e) { var v=e.value;
var t1=v.substring(0,e.selectionStart); // текст перед
var t2=v.substring(e.selectionEnd,v.length); // текст после
var pp=GetCaretPosition(e);
e.value=t1.replace(/\s+$/,'') + "\n" + t2.replace(/^\s+/,'');
setCaretPosition(e,pp);
}

function ti(id,tmpl) {
var e=idd(id); var v=e.value; var ss=e.selectionStart; var es=e.selectionEnd;
var s=tmpl.replace(/\{select\}/g,v.substring(ss,es));
GetCaretPosition(e); e.value=v.substring(0,ss)+s+v.substring(es,v.length); setCaretPosition(e,ss+s.length);
e.selectionStart=ss; e.selectionEnd=ss+s.length;
}

var scrollTop=0;

function GetCaretPosition(e) { var p=0; // IE Support
if(document.selection){ e.focus(); var s=document.selection.createRange(); s.moveStart('character',-e.value.length); p=s.text.length; } // Firefox support
else if(e.selectionStart || e.selectionStart=='0') p=e.selectionStart;
scrollTop=e.scrollTop; return p;
}

function setCaretPosition(e,p) {
if(e.setSelectionRange){ e.focus(); e.setSelectionRange(p,p); }
else if(e.createTextRange){ var r=e.createTextRange(); r.collapse(true); r.moveEnd('character',p); r.moveStart('character',p); r.select(); }
e.scrollTop = scrollTop;
}

//======================================== jog
var jog=false,f5s=false;

c_read=function(n) { var a=' '+document.cookie+';'; var c=a.indexOf(' '+n+'='); if(c==-1) return false; a=a.substring(c+n.length+2);
return decodeURIComponent(a.substring(0,a.indexOf(';')))||false; };
fc_read=fc_save=function(n,v){ return false; };
f_read=function(n){ return f5_read(n)||c_read(n); };
f_save=f5_save=l_save=function(k,v){ return window.localStorage&&window.localStorage.setItem?window.localStorage.setItem(k,v):false; };
f5_read=l_read=function(k){ return window.localStorage&&window.localStorage.getItem?window.localStorage.getItem(k):false; };
l_del=function(k){ return window.localStorage&&window.localStorage.removeItem?window.localStorage.removeItem(k):false; };

time=function(){ return new Date().getTime(); };

// comments

//==========
window.onload = function() {
// === KEYBOARD === http://www.asquare.net/javascript/tests/KeyCode.html
document.onkeypress = function(e){ lastkeycode=(e.keyCode ? e.keyCode :e.which ? e.which : null); };

document.onkeyup = function(e){ if(keyalert) { var T=setTimeout('keyprint()',50); return false; }
if(eventkey!==0 || lastkeycode==0) return; return keydo(e,lastkeycode);
};

document.onkeydown = function(e) { if(keyalert) return false;
if(!e) e=window.event; eventkey=0; var k=(e.keyCode ? e.keyCode : e.which ? e.which : 0);
if(k===0) return; eventkey=e; lastkeykey=k; return keydo(e,k);
};

// === KEYBOARD ===

window.onresize=function(){
	screenWidth=document.body.clientWidth;
//	alert(screenWidth); TUT eeeeeeeeeeeeeeeeeee
	//??? if((getWinW()-getDocW())<15) mHelps['Wscroll']=1; else delete(mHelps['Wscroll']);
};

window.onresize();

// for(var i in page_onstart) eval(page_onstart[i]); page_onstart=[];
};

function page(l) { return (l.length / textarea_cols + ('\n'+l).match(/\n/g).length + 1); }

function salert(l,t) { sendm("SALERT;t="+t+";s="+hl(l)); }

// sendm("ZABIL;id="+hl(id)+";text="+hl(text));
// helpc('salert',"<div style='padding:20px; border: 1px dotted #cccccc'>"+l+"</div>"); if(t) setTimeout("clean('salert')",t); return false;

function scount(str,s) { var i=0,c=0; while((i=str.indexOf(s,++i))>0) c++; return c; }
function nl2brp(s) { s=s.replace(/\n\n/gi,"<p>"); s=s.replace(/\n/gi,"<br>"); return s; }
function brp2nl(s) { s=s.replace(/<p>/gi,"\n\n"); s=s.replace(/<br>/gi,"\n"); return s; }
function stripp(s) { return s.replace(/<\/p>/gi,""); }

function keydo(e,k) { var ct=e.metaKey+2*e.altKey+4*e.shiftKey+8*e.ctrlKey;
	// не обрабатывать коды браузера:
	if(k==keycodes.right && ct==keykeys.alt) return true;
	if(k==keycodes.left && ct==keykeys.alt) return true;
	if(k==85 && ct==keykeys.ctrl) return true; // ctrl+U

	for(var i in hotkey) if(hotkey[i][0]==k && hotkey[i][1]==(hotkey[i][1]&ct)) {

		// setTimeout("var z=hotkey["+i+"]; alert('key:'+z[0]+' nav:'+z[4]+' nonav: '+nonav+'\\nfun:'+z[2])",50);

	 	if(nonav && !hotkey[i][4]) return true; // навигация отключена для навигационных

		setTimeout('hotkey['+i+'][2](eventkey)',50);
		return hotkey[i][3];
	}
}

function keyprint(){ talert("code: "+lastkeycode+' &nbsp; key: '+lastkeykey,800); }

function gethash_c(){ return 1*document.location.href.replace(/^.*?#(\d+)$/g,'$1'); }

function get_pole_ara(w,onlych) { var k=0,ara={names:''}; var el=['input','textarea','select'];
w=idd(w);
        for(var j=0;j<el.length;j++){ var e=w.getElementsByTagName(el[j]); for(i=0;i<e.length;i++)
                        if(typeof(e[i].name)!='undefined' && e[i].name!=''
                        && ( onlych!=1 || typeof(e[i].defaultValue)=='undefined' || e[i].value!=e[i].defaultValue)
                        ) {
                                if(el[j]=='input'&&e[i].type=='file') {
                                        if(e[i].value=='') continue; // пустых нам не надо
                                        ara[e[i].name]=e[i];
                                } else {
                if(el[j]=='input'&&e[i].type=='checkbox') ara[e[i].name]=e[i].checked?1:0;
		else {
			if(in_array(e[i].name,['names','hashpage','a','asave','up','acn','ux','upx'])) continue; // служебные к повтору запрещены
                        ara[e[i].name]=e[i].value;
                        if(typeof(e[i].defaultValue)!='undefined') e[i].defaultValue=e[i].value;
                }
                                }
			ara['names']+=' '+e[i].name; k++;
                        }
        }
        return (k==0?false:ara);
}


function find_form(e) { while(e.tagName!='FORM'&&e.parentNode!=undefined) e=e.parentNode; if(e.parentNode==undefined) idie('e.form error'); return e; }

function send_this_form(e,mjphp,m,onlych) { e=find_form(e); var ara=get_pole_ara(e,onlych);
if(ara===false) return false; for(var i in m) ara[i]=m[i]; majax(mjphp,ara); return false;
}

// функция постит объект-хэш content в виде формы с нужным action, target
// напр. postToIframe({a:5,b:6}, '/count.php', 'frame1')
function postToIframe(ara,url,id){
    var f=document.createElement("form"); f.style.display="none"; f.id=id+'_form';
    f.enctype="application/x-www-form-urlencoded"; f.method="POST"; document.body.appendChild(f);
    f.action=url; f.target=id; f.setAttribute("target",id);
    for(var x in ara) form_addpole(f,x,ara[x]);
    f.submit();
}

// ----------
ifhelpc=function(src,id,head){ if(!id) id='ifram';
	if(!head) head='iframe '+h(src);
	if(typeof(postMessage)!='function') src=www_ajax+'frame.htm#'+src; //+'?adr=iframe_binoniq#framel';
	ohelpc(id,head,"<iframe name='"+id+"_ifr' src='"+src+"' onload='ajaxoff()' width='"+Math.floor(8*getWinW()/10)+"' height='"+Math.floor(8*getWinH()/10)+"'></iframe>");
	ajaxon();
};

function go(s) { window.top.location=s; }

function h(s){ return s.replace(/\&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\'/g,'&#039;').replace(/\"/g,'&#034;'); }
function uh(s){ return s.replace(/\&lt\;/g,'<').replace(/\&gt\;/g,'>').replace(/\&\#039\;'/g,"'").replace(/\&\#034\;"/g,'"').replace(/\&amp\;/g,'&'); } //'

function catcherr(txt,e,code){ ohelpc('JSerr','JS error: '+h(txt),"<font color=red><b><big>"+h(e.name)+": "+h(e.message)+"</big></b></font>"
// +"<br><b>"+h(majax_lastu)+' {'+h(print_r(majax_lasta))+" }</b>"
+"<div style='border:1px dotted red'>"+h(e.stack)+"</div>"
+h(code).replace(/\n/g,"<br>")); }
