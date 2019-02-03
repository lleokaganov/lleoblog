/*
var hashpage='".$GLOBALS['hashpage']."';
var wwwhost='".$GLOBALS['wwwhost']."';
var admin=".($GLOBALS['admin']?1:0).";
var mypage='".$GLOBALS['httpsite'].$GLOBALS['mypage']."';
var uc='".$GLOBALS['uc']."';
var www_js='".$GLOBALS['www_js']."';
var www_css='".$GLOBALS['www_css']."';
var wwwcharset='".$GLOBALS['wwwcharset']."';
var www_design='".$GLOBALS['www_design']."';
var www_ajax='".$GLOBALS['www_ajax']."';
var page_onstart=[];
*/

var lovilka='';
var alertmajax=0; // if(admin) setTimeout('var alertmajax=1',4000);
var mojaxsalt='';

if(user_opt('ani')) page_onstart.push("LOADS(www_css+'animate.css');wintempl_cls=wintempl_cls.replace(/animated/g,'');");
if(user_opt('er')) window.onerror=function(e,url,n) { // стукач об ошибках JS у пользователей
/*
    try{var s=document.documentElement.innerHTML.split(/\n/)[n-2];}catch(e){var s='{err}';}
    if(user_opt('er')) alert(url+"\nLine #"+n+" Error: "+e+"\n\n"+s);
*/

console.log('LL_ERROR:'+e+"|"+n+"|"+url+'|'+lovilka+'|'+s);

/*
// var a=new Image();a.src=www_ajax+'logjs.php?'+encodeURIComponent(e+"|"+n+"|"+url+'|'+lovilka+'|'+s);
*/
    return true;
}

var alertmajax=0; // if(admin) setTimeout('var alertmajax=1',4000);

// обращения, которые обязаны идти только через секретный xdomain
var ifrnames=[
'mailbox.php:mail',
'mailbox.php:newform',
'mailbox.php:answer',

'editor.php:editform',
'editor.php:tags',
'editor.php:settings_win',
'editor.php:editform_new',
'editor.php:newform',

'editor.php:xclipboard',

'adminsite.php:edit',
// 'adminsite.php:new',

'login.php:getinfo' /*,'foto.php:album'*/
];

//function basename(path) { return path.replace(/^.*[\/\\]/g,''); }
function c_save(n,v,d,p) {


d=window.navigator.userAgent.indexOf('NokiaE90')<0?d:0; // заебала Нокия не понимать куки для домена
if(v===false||v===null) return false; var N=new Date(); N.setTime(N.getTime()+(v==''?-1:3153600000000));
document.cookie=n+'='+encodeURIComponent(v)+';expires='+N.toGMTString()+';path='+(p==undefined?'/':p)+';'+(d!==0?'domain=.'+MYHOST+';':''); }

var zindexstart=100; // начало отсчета слоев для окон
var activid=false; // id активного окна
var hid=1; // счетчик окон
var mHelps={}; // массив для окон: id:[hotkey,zindex]
var hotkey=[]; // [code,(ctrlKey,shiftKey,altKey,metaKey),func]
var hotkey_def=[]; // хоткеи главного окна
var nonav=0; // отключить навигацию и буквенные хоткеи

if(window.top===window && mnogouser) page_onstart.push("if(ux=='c') ifhelpc(xdom,'xdomain','xdomain');");

//========================================================
if(typeof(hotkey_default)!='function') hotkey_default=function(){
hotkey=[];

// setkey('space','ctrl',function(e){alert('SPACE')},true); // test Ctrl+SPACE

setkey('esc','',function(e){ clean(isHelps())},true,1); // закрыть последнее окно
setkey('enter','ctrl',function(e){if(!isHelps()) helper_go()},true,1); // если не открыто окон - окно правки

if(adm) {
// setkey('x','alt',function(e){alert('Scroll W/H='+getScrollW()+'/'+getScrollH()+'\ndocument.compatMode='+document.compatMode+'\nwindow.opera'+window.opera+'\ngetWin W/H='+getWinW()+'/'+getWinH()+'\ngetWin W0/H0='+getWinW0()+'/'+getWinH0()+'\ngetDoc W/H='+getDocW()+'/'+getDocH());},false);
setkey(['E','У','у'],'',function(e){majax('editor.php',{a:'editform',num:num,comments:(idd('commpresent')?1:0)})},false); // редактор заметки
setkey(['N','Т','т'],'',function(e){majax('editor.php',{a:'newform',hid:++hid})},false); // новая заметка
// setkey(['A','Ф','ф'],'',function(e){if(idd('adminpanel').style.display=='block'){posdiv('adminpanel',0,0);zakryl('adminpanel')}else{otkryl('adminpanel');posdiv('adminpanel',-1,-1)}},false);
}

//setkey(['U','Г','г'],'',function(e){majax('login.php',{action:'openid_form'})},true); // личная карточка
setkey(['U','Г','г'],'',function(e){majax('login.php',{a:'getinfo'})},true); // личная карточка
// setkey(['D','В','в'],'',function(e){document.location.href=wwwhost;},true); // в блог
// setkey(['K','Л','л'],'',function(e){document.location.href=wwwhost+'comms';},true); // комментарии
// setkey(['right','7'],'',function(e){rel_redirect('NextLink')},true);
// setkey(['left','4'],'',function(e){rel_redirect('PrevLink')},true);
// setkey(['F5'],'',function(e){setTimeout("salert('Боже, да сколько же вас, верующих в силу кнопки F5?',4000)",50);},false);
// setkey(['A','Ф','ф'],'alt shift',function(e){keyalert=1;salert('Скан клавиш включен',1000);},false); // включение сканкодов
// setkey('up','ctrl',function(e){rel_redirect('UpLink')},true);
// setkey('down','ctrl',function(e){rel_redirect('DownLink')},true);
// setkey('home','ctrl',function(e){if(user_opt('n'))document.location.href='/'},true);
};

page_onstart.push("hotkey_default()");

//========================================================

keycodes={right:0x27,left:0x25,up:0x26,down:0x28,esc:0x1B,enter:0x0D,home:0x24,tab:9,del:46,F5:116,space:0x20,
'А':'1040','а':'1072','Б':'1041','б':'1073','В':'1042','в':'1074','Г':'1043','г':'1075','Д':'1044','д':'1076',
'Е':'1045','е':'1077','Ё':'1025','ё':'1105','Ж':'1046','ж':'1078','З':'1047','з':'1079','И':'1048','и':'1080',
'Й':'1049','й':'1081','К':'1050','к':'1082','Л':'1051','л':'1083','М':'1052','м':'1084','Н':'1053','н':'1085',
'О':'1054','о':'1086','П':'1055','п':'1087','Р':'1056','р':'1088','С':'1057','с':'1089','Т':'1058','т':'1090',
'У':'1059','у':'1091','Ф':'1060','ф':'1092','Х':'1061','х':'1093','Ц':'1062','ц':'1094','Ч':'1063','ч':'1095',
'Ш':'1064','ш':'1096','Щ':'1065','щ':'1097','Ъ':'1066','ъ':'1098','Ы':'1067','ы':'1099','Ь':'1068','ь':'1100',
'Э':'1069','э':'1101','Ю':'1070','ю':'1102','Я':'1071','я':'1103'};
keykeys={ctrl:8,shift:4,alt:2,meta:1};

function setkey(k,v,f,o,nav){ nav=nav?1:0; if(typeof(k)=='string') var k=[k]; for(var i in k) {
if(typeof(k[i])=='string') // какой-то немыслимый йобанный патч от prototype, который навешивает говна
setkey0(k[i],v,f,o,nav);
// и запомнить в массиве
k=cphash(hotkey); if(typeof(mHelps[activid])!='undefined') mHelps[activid][0]=k; else hotkey_def=k;
}
}

function setkey0(k,v,f,o,nav){ // повесить функцию на нажатие клавиши
k=(!isNaN(k) && k.length>1) ? k : keycodes[k] ? keycodes[k] : k.toUpperCase().charCodeAt();
        var e=0; for(var i in keykeys) if(v.indexOf(i)>=0) e+=keykeys[i];
for(var i in hotkey)if(hotkey[i][0]==k && hotkey[i][1]==e){ // если уже есть - изменить
if(f==undefined || f=='') delete hotkey[i]; else hotkey[i]=[k,e,f,o,nav];
return;
}
if(f==undefined || f=='') return; // если нет, и не задана функция, - просто выйти
if(e) hotkey.push([k,e,f,o,nav]); else hotkey.unshift([k,e,f,o,nav]); // иначе - задать
}

function rel_redirect(id){ var e=idd(id); if(user_opt('n') && e && e.href && !isHelps()) {
if(id=='PrevLink'){ var b=document.body,i=curX-startX; if(i<0)i=-i; b.style.left=i+'px'; setOpacity(b,0.5); }
else if(id=='NextLink'){ var b=document.body,i=curX-startX; if(i<0)i=-i; b.style.right=i+'px'; setOpacity(b,0.5); }
document.location.href=e.href; } }

function idd(id){ if(typeof(id)=='object') return id;
    if(typeof(document.getElementById(id))=='undefined') return false;
    return document.getElementById(id);
}
function zabil(id,text) { if(idd(id)) { idd(id).innerHTML=text; init_tip(idd(id)); } }

function doclass(cla,f,s,node) { var p=getElementsByClass(cla,node?node:document);
    for(var i in p) { if(typeof(p[i])!='undefined' && typeof(p[i].className)!='undefined') f(p[i],s); }
}

function ifclass(id,l){ return in_array(l,idd(id).className.split(' ')); }
function classAdd(id,l){ var e=idd(id).className.split(' '); e.push(l); idd(id).className=e.join(' '); }
function classDel(id,l){ var i,e=idd(id).className.split(' '); for(i in e) if(e[i]==l) delete e[i]; idd(id).className=e.join(' '); }
function zabilc(cla,s) { doclass(cla,function(e,s){e.innerHTML=s;},s); }
function vzyal(id) { return idd(id)?idd(id).innerHTML:''; }
function zakryl(id) { if(!idd(id)) return; idd(id).style.display='none'; if(id!='tip') zakryl('tip'); }
function otkryl(id) { if(idd(id)) idd(id).style.display='block'; }
function tudasuda(id) { if(idd(id)&&idd(id).style.display=='none') otkryl(id); else zakryl(id); }

function cphash(a) {
    var b={}; for(var i in a) {
    if(typeof(a[i])!='undefined'){
    if(typeof(a[i])=='object' && typeof(a[i]['innerHTML'])!='string') b[i]=cphash(a[i]); else b[i]=a[i];}
    }
    b.push=a.push; b.unshift=a.unshift; // йобаный патч!
    return b;
}

function cpmas(a) { var b=[]; for(var i=0;i<a.length;i++){
    if(typeof(a[i])!='undefined'){
    if(typeof(a[i])=='object' && typeof(a[i]['innerHTML'])!='string') b[i]=cphash(a[i]); else b[i]=a[i];}
} return b; }

function isHelps(){ var max=0,id=false; for(var k in mHelps){ if(mHelps[k][1]>=max){max=mHelps[k][1];id=k;} } return id; }// найти верхнее окно или false

/*
function print_r(a,n,skoka) {
    var s='',t='',i,v; if(!n)n=0; for(i=0;i<n*10;i++)t=t+' ';
    if(typeof(a)!='object') return a;
    for(var j in a){ if(typeof(j)=='undefined' || typeof(a[j])=='undefined') break;
	v=a[j];
	if(v!=null && !skoka && typeof(v)=='object' && typeof(v.innerHTML)!='string') v=print_r(v,n+1);
        s='\n'+t+j+'='+v+s;
    }
    return s;
}
*/


var print_r_id=0;
var print_rid={};

function printr_f(ev,e,i){ ev.stopPropagation();
    if(e.className!='ll') { e.innerHTML="[Object]"; e.className='ll'; return; }
    e.className=''; e.style.marginLeft='30px'; e.innerHTML='{\n'+print_r(print_rid[i],0,1)+'\n}\n';
}

function print_r(a,n,skoka) {
    var s='',t='',i,v; if(!n)n=0; for(i=0;i<n*10;i++)t=t+' ';
    if(typeof(a)!='object') return a;

    for(var j in a){ if(typeof(j)=='undefined' || typeof(a[j])=='undefined') break;
	v=a[j]; if(v!=null && !skoka && typeof(v)=='object' && typeof(v.innerHTML)!='string') v=print_r(v,n+1);
	if(v=='[object Object]') {
	    var z=(print_r_id++);
	    print_rid[z]=Object.assign({},v);
	    s='\n'+t+j+'='+"<div onclick=\"printr_f(event,this,'"+z+"')\" class=ll>[Object] {"+z+"}</div>" +s;
	} else s='\n'+t+j+'='+v+s;
    }

    return s;
}




function in_array(s,a){ for(var l in a) if(a[l]==s) return l; return false; }

clean=function(id) {
    if(typeof(id)=='object') {
        if(typeof(id.id)!='undefined'&&id.id!='') id=id.id; // если есть имя, то взять имя
        else { var t='tmp_'+(hid++); id.id=t; id=t; } // иначе блять присвоить
    }

    if(typeof(mHelps[id])!='undefined'){ // окно было
        delete(mHelps[id]); // удалить окно
        mHelps_sort(top); // пересортировать
        if(!isHelps()) { hotkey=cphash(hotkey_def); nonav=0; } // восстановить дефаулты
    }

    if(idd(id)) {
	var clen=function(){var s=idd(id); if(s)s.parentNode.removeChild(s);};
        if(typeof(idd(id).onanimationend)!='object' || in_array(id,['tenek','ajaxgif'])) { zakryl(id); setTimeout(function(){clen()},40); }
        else { if(1!=anim(idd(id),'zoomOut',clen)) setTimeout(function(){clen()},1500); }
    } else if(typeof(idrename)!='undefined'&&typeof(idrename[id])!='undefined') { clean(idrename[id]); }
    zakryl('tip');
};

var JSload={};

function mHelps_sort(top) { // сортировка окон по слоям возрастания с предлежащим окном тени

if(top=='salert') return;

var mam=[],k=zindexstart,id=0; for(var i in mHelps) mam.push([i,mHelps[i][1]]);
if(!mam.length){ clean('tenek'); hotkey=cphash(hotkey_def); activid=false;
bukadump();
return; }
mam.sort(function(i,j){return i[1]>j[1]?1:0});

for(var i=0;i<mam.length;i++){ id=mam[i][0];
if(id==top || !top && (i+1)==mam.length) continue;
mHelps[id][1]=k; idd(id).style.zIndex=k++;
} if(top) id=top;

if(typeof(mHelps[id])=='undefined') { clean('tenek'); return; }

if(typeof(document.body.style.pointerEvents)=='string') {
if(!idd('tenek')) { mkdiv('tenek','','tenek'); otkryl('tenek'); setOpacity(idd('tenek'),0.2); }
setTimeout("if(idd('tenek')){idd('tenek').style.height=getDocH()+'px';idd('tenek').style.width=getDocW()+'px';}",50);
idd('tenek').style.zIndex=k++;
}

//if(typeof(mHelps[id])=='undefined') {
//alert('id:'+id);
//alert(print_r(id));
//alert(print_r(mHelps));}
mHelps[id][1]=k; idd(id).style.zIndex=k;
hotkey=cphash(mHelps[id][0]);
activid=id;
bukadump();
}

function bukadump() { // отладочник
if(!idd('bukadump')) return;
var s='<hr>';
s+='<br>activid='+activid;
s+='<p>hotkey='+print_r(hotkey,0,0).replace(/\n/g,'<br>').replace(/ /g,'&nbsp;');
s+='<hr>';
zabil('bukadump',s);
}


var LOADES={};

function inject(src){ if(src.indexOf('://')<0) src=www_ajax+src; loadScr(src); }
function loadScript(src,f){ if(src.indexOf('://')<0) src=www_js+src; if(JSload[src]=='load') return; loadScr(src,f); }

function LOADS(u,f) { if(typeof(u)=='string') u=[u];
    var h,s;
    for(var i in u) { if(LOADES[u[i]]) continue;
     if(/\.css($|\?.+?$)/.test(u[i])) { s=document.createElement('link'); s.type='text/css'; s.rel='stylesheet'; s.href=u[i]; s.media='screen'; }
     else { s=document.createElement('script'); s.type='text/javascript'; s.src=u[i]; }
     s.setAttribute('orign',u[i]);
     // s.async=false;
    // s.onreadystatechange=
     s.onerror=function(e){ idie('Not found: '+e.target.getAttribute('orign')); };
     s.onload=function(e){var k=1,x=e.target.getAttribute('orign'); LOADES[x]=1; for(var i in u){ if(!LOADES[u[i]]){k=0;break;}} if(k){ ajaxoff(); if(f)f(x);}};
     h=document.getElementsByTagName('head').item(0);
     h.insertBefore(s,h.firstChild);
    }
    if(s) ajaxon();
    else if(f)f(1);
}

function loadScr(url,f){ if(LOADES[url]) return false;
    var s=document.createElement('script');
    s.setAttribute('type','text/javascript');
    s.setAttribute('src',url);
    // s.async=false;
    s.onreadystatechange=s.onload=function(){ var c=s.readyState; if(!c||/loaded|complete/.test(c)){ LOADES[url]=1; if(f)f(url); } };
    // IE crashes on using appendChild before the head tag has been closed.
    var head=document.getElementsByTagName('head').item(0);
    head.insertBefore(s,head.firstChild);
    ajaxon();
}

function loadStyle(url,f){ if(LOADES[url]) return false;
    var headID=document.getElementsByTagName('head')[0]; var s=document.createElement('link'); s.type='text/css'; s.rel='stylesheet'; s.href=url; s.media='screen';
    // s.async=false;
    s.onreadystatechange=s.onload=function(){ var c=s.readyState; if(!c||/loaded|complete/.test(c)){ LOADES[url]=1; if(f)f(url); } };
    headID.appendChild(s);
}


function loadScriptBefore(src,runtext){
    // alert('ScriptBefore was removed');
    if(JSload[src]=='load') return eval(runtext); if(JSload[src]) return; JSload[src]=runtext; loadScript(src); 
}

function loadCSS(src){ src=www_css+src;if(JSload[src]=='load') return; JSload[src]='load'; loadStyle(src); }

if(document.getElementsByClassName) getElementsByClass=function(classList,node){ return (node||document).getElementsByClassName(classList) };
else {
    getElementsByClass = function(classList, node) {
        var node = node || document, list = node.getElementsByTagName('*'),
        length = list.length, classArray = classList.split(/\s+/),
        classes = classArray.length, result = [], i,j;
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

// создать новый <DIV class='cls' id='id'>s</div> в элементе paren (если не указан - то просто в документе)
// есть указан relative - то следующим за relative, если указан 0 - то первым, иначе - последним
function mkdiv(id,s,cls,paren,relative){ if(idd(id)) { zabil(id,s); idd(id).className=cls; return; }
    var div=document.createElement('DIV'); div.className=cls; div.id=id; div.innerHTML=s; div.style.display='none';
    if(paren==undefined) paren=document.body;
    if(relative==undefined) paren.appendChild(div); // paren.lastChild
    else if(relative===0) paren.insertBefore(div,paren.firstChild);
    else paren.insertBefore(div,relative.nextSibling);
}

function posdiv(id,x,y) { // позиционирование с проверкой на вылет, если аргумент '-1' - по центру экрана
    var e=idd(id),W,w,H,h,DW,DH;
    e.style.position='absolute';
    if(e.style.display!='block') otkryl(id);
    W=getWinW(); H=getWinH(); w=e.clientWidth; h=e.clientHeight;
    if(x==-1) x=(W-w)/2+getScrollW();
    if(y==-1) y=(H-h)/2+getScrollH();
    DW=W-10; if(w<DW && x+w>DW) x=DW-w; if(x<0) x=0;
    if(y<0) y=0;
    e.style.top=y+'px'; e.style.left=x+'px';
    otkryl(id);
}

function center(id) { posdiv(id,-1,-1); }

function addEvent(e,evType,fn) {
    if(e.addEventListener) { e.addEventListener(evType,fn,false); return true; }
    if(e.attachEvent) { var r = e.attachEvent('on' + evType, fn); return r; }
    e['on' + evType] = fn;
}

function removeEvent(e,evType,fn){
    if(e.removeEventListener) { e.removeEventListener(evType,fn,false); return true; }
    if(e.detachEvent) { e.detachEvent('on'+evType, fn) };
}

function hel(s,t) { ohelpc('id_'+(++hid),(t==undefined?'':s),s); }
function helps_cancel(id,f) { getElementsByClass('can',idd(id))[0].onclick=f; }
function helpc(id,s) { helps(id,s); posdiv(id,-1,-1); }
function ohelpc(id,z,s) { helpc(id,mk_helpbody(z,s)); }
function ohelp(id,z,s) { helps(id,mk_helpbody(z,s)); }
function mk_helpbody(z,s) { return "<div class='fieldset'>"+(z==''?'':"<div class='legend'>"+z+"</div>")+"<div class='textbody'>"+s+"</div></div>"; }
function idie(s,t) { var e=typeof(s); if(e=='object') s="<pre style='max-width:"+(getWinW()-200)+"px'>"+print_r(s,0,1)+'</pre>';
if(t!=undefined) s=t+'<p>'+s;
var p=idd('idie'); if(p) { p=getElementsByClass('textbody',p)[0]; if(p) return p.innerHTML=p.innerHTML+'<hr>'+s; }
ohelpc('idie','Error type: '+e,s) }
dier=idie;

// var wintempl="<div class='corners'><div class='inner'><div class='content' id='{id}_body' align=left>{text}</div></div></div>";
// var wintempl_cls='popup';
// var wintempl_cls='pop2';
// var wintempl="<div id='{id}_body'>{s}</div><i id='{id}_close' title='Close' class='can'></i>";

function helps(id,s,pos,cls,wt) {

if(!idd(id)) {
if(!wt) wt=wintempl;
mkdiv(id,wt.replace(/\{id\}/g,id).replace(/\{s\}/g,s),wintempl_cls+(cls?' '+cls:''));
if(idd(id+'_close')) idd(id+'_close').onclick=function(e){clean(id)};
init_tip(idd(id));

// (c)mkm Вот рецепт локального счастья, проверенный в Опера10, ИЕ6, ИЕ8, FF3, Safari, Chrome.
// Таскать окно можно за 'рамку' - элементы от id до id+'_body', исключая body (и всех его детей).
var e_body=idd(id+'_body'); // За тело не таскаем
var hmov=false; // Предыдущие координаты мыши
// var hmov2=1; // тащим
var e=idd(id);

var pnt=e; while(pnt.parentNode) pnt=pnt.parentNode; //Ищем Адама

var mmFunc=function(ev) { ev=ev||window.event; if(hmov) {
	e.style.left = parseFloat(e.style.left)+ev.clientX-hmov.x+'px';
	e.style.top = parseFloat(e.style.top)+ev.clientY-hmov.y+'px';
	hmov={ x:ev.clientX, y:ev.clientY };
	if(ev.preventDefault) ev.preventDefault();
	return false;
    }
};

var muFunc=function(){ if(hmov){
    hmov=false;
    removeEvent(pnt,'mousemove',mmFunc);
    removeEvent(pnt,'mouseup',muFunc);
    e.style.cursor='auto';
    }
};

addEvent(e,'mousedown', function(ev){ if(hmov) return;

ev=ev||window.event;
var lbtn=(window.addEventListener?0:1); //Если ИЕ, левая кнопка=1, иначе 0
if(!ev.target) ev.target=ev.srcElement;
if((lbtn!==ev.button)) return; //Это была не левая кнопка или 'тело' окна, ничего не делаем
var tgt=ev.target;
while(tgt){
    if(tgt==e_body) return;
    if(tgt==e) break;
    tgt=tgt.parentNode;
};
//Начинаем перетаскивать
e.style.cursor='move';
// hmov2=0;
hmov={ x:ev.clientX, y:ev.clientY };
addEvent(pnt,'mousemove',mmFunc);
addEvent(pnt,'mouseup',muFunc);
if(ev.preventDefault) ev.preventDefault();
return false;
});
// ===========================================================================

++hid;

if(!pos) posdiv(id,mouse_x,mouse_y);

mHelps[id]=[cphash(hotkey),999999];

} else zabil(id+'_body',s);

hotkey=cphash(hotkey_def); // обнулить для окна все шоткеи
setTimeout("mHelps_sort('"+id+"');",10); // пересортировать
addEvent(idd(id),'click',function(){mHelps_sort(this.id); });

}

// координаты мыши
var mouse_x=mouse_y=0; 
document.onmousemove = function(e){ e=e||window.event;
  if(e.pageX || e.pageY) { mouse_x=e.pageX; mouse_y=e.pageY; }
  else if(e.clientX || e.clientY) {
    mouse_x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    mouse_y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }
try{e=idd('ajaxgif'); e.style.top=15+mouse_y+'px'; e.style.left=15+mouse_x+'px';}catch(e){}
};


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

function getScrollH(){ return document.documentElement.scrollTop || document.body.scrollTop; }
function getScrollW(){ return document.documentElement.scrollLeft || document.body.scrollLeft; }

function getWinW(){ return window.innerWidth || (document.compatMode=='CSS1Compat' && !window.opera ? document.documentElement.clientWidth : document.body.clientWidth); }
function getWinH(){ return window.innerHeight || (document.compatMode=='CSS1Compat' && !window.opera ? document.documentElement.clientHeight : document.body.clientHeight); }

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
function valid_up(l) { var u=('#'+l).replace(/^#(\d+)\-[0-9ABCDEF]{32}$/gi,"$1"); return isNaN(u)||u==0?false:l; }

var unic_rest_flag=0; function unic_rest(i) { return 0; // заебало! временно отключим!
/*
if(unic_rest_flag) return;
var upo=valid_up(i?fc_read('up'):f5_read('up')); // прочитать из одного или другого хранилища
if(up!=upo && upo!==false) { unic_rest_flag=1; return majax('restore_unic.php',{up:up,upo:upo,num:num,i:i}); } // восстановить!
if(up!='candidat') return (i?fc_save('up',up):f5_save('up',up));
*/
}
// page_onstart.push('unic_rest(0)'); // заебало! временно отключим!

/*
function fc_saveif(n,v){ if(fc_read(n)!=v) fc_save(n,v); }
var f5s=('localStorage' in window) && window['localStorage']!==null ? window['localStorage'] : false;
function f5_read(n){ var v=f5s?f5s[n]:''; return (v==''||v==null)?false:v; }
function f5_save(n,v) { return f5s?(f5s[n]=v):false; }
function f5_saveif(n,v){ if(f5_read(n)!=v) f5_save(n,v); }
*/

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
var komsel_n=0,komsel_v='';
var comnum=0;
if(typeof(commenttmpl)=='undefined') var commenttmpl='';
function kus(unic) { if(unic) majax('login.php',{a:'getinfo',unic:unic}); }// личная карточка
function kd(e) { if(confirm('Точно удалить?')) majax('comment.php',{a:'del',id:ecom(e).id}); } // del
function ked(e) { majax('comment.php',{a:'edit',comnu:comnum,id:ecom(e).id,commenttmpl:commenttmpl}); } // edit
function ksc(e) { majax('comment.php',{a:'scr',id:ecom(e).id,commenttmpl:commenttmpl}); } // screen/unscreen
function ko(e) { majax('comment.php',{a:'ans',id:ecom(e).id,commenttmpl:commenttmpl}); } // ans-0-1-undef
function rul(e) { majax('comment.php',{a:'rul',id:ecom(e).id,commenttmpl:commenttmpl}); } // rul-не rul
function ka(e) { e=ecom(e); majax('comment.php',{a:'comform',id:e.id,lev:e.style.marginLeft,comnu:comnum,commenttmpl:commenttmpl}); } // answer
function kpl(e) { majax('comment.php',{a:'plus',id:ecom(e).id,commenttmpl:commenttmpl}); } // +
function kmi(e) { majax('comment.php',{a:'minus',id:ecom(e).id,commenttmpl:commenttmpl}); } // -
function kl(e) { if(komsel_n!==0) idd(komsel_n).style.border=komsel_v;
komsel_n=ecom(e).id; komsel_v=idd(komsel_n).style.border;
idd(komsel_n).style.border='5px dotted red'; return true; } // link
function opc(e,num) { e=ecom(e); majax('comment.php',{a:'pokazat',dat:num,oid:e.id,lev:e.style.marginLeft,comnu:comnum,commenttmpl:commenttmpl}); } // показать
function ecom(e){while((e.id==''||e.id==undefined)&&e.parentNode!=undefined) e=e.parentNode; return e.id==undefined?0:e;}

function skm(e) { var i=ecom(e).id; hide_comm(i); comhif5(i,1); } // убрать коммент

function comhif5(i,z) { var n='hidcom'+num,r=f5_read(n); if(!r) r=[]; else r=r.split(',');
    if(z) { if(!in_array(i,r)) r.push(i); } else { var l=in_array(i,r); if(false!==r) r.splice(l,1); }
    f5_save(n,r.join(','));
}

function restore_comm(e) {
    e=e||window.event,i=e.target.id.replace(/scc_/g,''),s='scc_'+i;
// idie('@'+e.target.id);
    majax('comment.php',{a:'why_hidden_comm',e:e.target.id,unic:idd(i).getAttribute('unic')});
// otkryl(s); removeEvent(e.target,'click',restore_comm); comhif5(i,0);
}
function hide_comm(i) { i=i.replace(/scc_/g,''); var s='scc_'+i; if(!idd(s)) zabil(i,"<div id='"+s+"'>"+vzyal(i)+"</div>"); zakryl(s); setTimeout("addEvent(idd('"+i+"'),'click',restore_comm)",10); }

// bigfoto - заебался отдельно пристыковывать
// BigLoadImg("http://lleo.aha.ru/tmp/img.php?text="+Math.random());
// Два варианта вызова: либо модулем для серии фоток, либо без второго параметра просто bigfoto('somepath/file.jpg')
// <img style='border:1px solid #ccc' onclick="return bigfoto('/backup/kniga_big.gif')" src="/backup/kniga_small.gif">

var BigImgMas={},bigtoti=0,bigtotp=0;
function bigfoto(i,p){ if(typeof(i)=='object') i=i.href;
var Z=(p==undefined); var n=Z?i:i+','+p;
if(typeof(BigImgMas[n])=='undefined'){ if(!Z && !idd("bigfot"+p+"_"+i)) return false;
ajaxon(); BigImgMas[n]=new Image(); BigImgMas[n].src=Z?n:idd("bigfot"+p+"_"+i).href; }
if(!Z) { bigtoti=i; bigtotp=p; }
if(BigImgMas[n].width*BigImgMas[n].height==0) { setTimeout('bigfoto('+(Z ? '"'+n+'"' : n)+')',200); return false; }
ajaxoff();

if(Z) var tt="<div id='bigfostr' class=br>"+n+"</div>";
else {
var g=i; while(idd('bigfot'+p+'_'+g)) g++;
var tt=(g>1?(i+1)+" / "+g:'')+(idd('bigfott'+p+'_'+i)?"&nbsp; &nbsp; <div style='display:inline;' title='предыдущая/следующая: стрелки клавиатуры' id='bigfottxt'>"+vzyal('bigfott'+p+'_'+i)+'</div>':'');
if(tt!=''||admin) tt="<div id='bigfostr' class=r"+(admin?" title='Admin, click to edit!' onclick=\"majax('editor.php',{a:'bigfotoedit',num:"+vzyal('bigfotnum'+p)+",i:"+i+",p:"+p+"})\"":"")+">"+tt+"</div>";
}
var navl=Z?'':"<div id='bigfotol' style='position:absolute;top:0px;left:0px;'"+((!i)?'>':" title='prev' onclick='bigfoto(bigtoti-1,bigtotp)' onmouseover=\"otkryl('bigfotoli')\" onmouseout=\"zakryl('bigfotoli')\"><i id='bigfotoli' style='position:absolute;top:0px;left:3px;display:none;' class='e_DiKiJ_l'></i>")+"</div>";
var navr=Z?'':"<div id='bigfotor' style='position:absolute;top:0px;right:0px;'"+((g==i+1)?'>':" title='next' onclick='bigfoto(bigtoti+1,bigtotp)' onmouseover=\"otkryl('bigfotori')\" onmouseout=\"zakryl('bigfotori')\"><i id='bigfotori' style='position:absolute;right:3px;display:none;' class='e_DiKiJ_r'></i>")+"</div>";

helps('bigfoto',"<div style='position:relative'>"+(admin?"<div id='bigfoto_opt' style='position:absolute;display:inline;bottom:-18px;right:-5px'>\
<i class='knop e_finish' title='Options' onclick=\"majax('foto.php',{a:'options',img:'"+BigImgMas[n].src+"',p:'"+p+"',num:num})\"></i>\
</div>":'')+navl+"<img id='bigfotoimg' src='"+BigImgMas[n].src+"' onclick=\"clean('bigfoto')\">"+navr+"</div>"+tt,1);

//<img src='"+www_design+"lj/btn_prev.gif'></div>
// <img src='"+www_design+"lj/btn_prev.gif'>

var w=BigImgMas[n].width,h=BigImgMas[n].height,e=idd('bigfotoimg');
var H=(getWinH()-20); if(h>H && H>480) { w=w*(H/h); h=H; e.style.height=H+'px'; }
var W=(getWinW()-50); if(w>W && W>640) { h=h*(W/w); w=W; e.style.width=W+'px'; }
if(idd('bigfostr')) idd('bigfostr').style.width=w+'px';

if(!Z){
idd('bigfotol').style.width=idd('bigfotor').style.width=w/4+'px';
idd('bigfotol').style.height=idd('bigfotor').style.height=h+'px'; 
if(idd('bigfotoli')) idd('bigfotoli').style.top=(h-16)/2+'px';
if(idd('bigfotori')) idd('bigfotori').style.top=(h-16)/2+'px';
setkey(['left','4'],'',function(){bigfoto(bigtoti-1,bigtotp)},false);
setkey(['right','7'],'',function(){bigfoto(bigtoti+1,bigtotp)},false);
}
posdiv('bigfoto',-1,-1);
return false;
}

// tip

function init_tip(w) { if(!idd('tip')) {
mkdiv('tip','','b-popup bubble-node b-popup-noclosecontrol');
zabil('tip','<div class="b-popup-outer"><div class="b-popup-inner"><div id="rtip"></div><i class="i-popup-arr i-popup-arrtl"><i class="i-popup-arr-brdr-outer"><i class="i-popup-arr-brdr-inner"><i class="i-popup-arr-bg"></i></i></i></i><i class="i-popup-close"></i></div></div>');
}
    if(w.id=='tip') return;

    if(useropt.mat && (!w.id || '#'!=w.id.replace(/(editor|cm)\d+/g,'#'))) delmat(w===document?w.body:w); // и сюда же заодно всрём обработку мата

var attr,j,i,a,s,e,t,el=['a','label','input','img','span','div','textarea','area','i'];
for(j=0;j<el.length;j++){ t=el[j]; e=w.getElementsByTagName(t); if(e){ for(i=0;i<e.length;i++){ a=e[i];

if(t=='img' && user_opt('i')) { // для ошибки при загрузки картинок
    a.setAttribute('onerror','erimg(this)');
    a.setAttribute('src',a.getAttribute('src'));
} else if(t=='input'||t=='textarea') { // и отключить навигацию для INPUT и TEXTAREA
    if(a.onFocus==undefined) addEvent(a,'focus',function(){nonav=1});
}

    attr=a.getAttribute('title')||a.getAttribute('alt');



    if(attr=='play') {
	var za=a.innerHTML,url=za.split(' ')[0],text=za.substring(url.length+1),cls;
	if(text=='') text=url;
	if(/(mp3|ogg|wav|flac)$/.test(url)) { // mp3
	    cls='ll pla';
	    if(text.indexOf('<')<0) text="<img style='vertical-align:middle;padding-right:10px;' src='"+www_design+"img/play.png' width='22' height='22'>"+text;
	} else {
	    cls='ll plv';
	    if(text.indexOf('<')<0) text="<i style='vertical-align:middle;padding-right:10px;' class='e_play-youtube'></i>"+text;
	}
	a.className=cls;
	// addEvent(a,'click',function(){ changemp3x(url,text,this); });
	a.setAttribute('media-url',url);
	a.setAttribute('media-text',text);
	addEvent(a,'click',function(){ changemp3x('','',this); });
	// a.onclick="changemp3x('"+url+"','"+text+"',this);";
	zabil(a,text);
	a.style.margin='10px';
	tip_a_set(a,'Play Media');
    }


    else tip_a_set(a,attr);


}}}
}

function erimg(e){ e.onerror='';
tip_a_set(e,'image error<br>'+h(e.src));
e.src=www_design+'img/kgpg_photo.png';
}

function tip_pos(){ posdiv('tip',mouse_x-35,mouse_y+25); }

function tip_a_set(a,s) { if(s && a.onMouseOver==undefined) {
    a.setAttribute('tiptitle',s); a.removeAttribute('title'); a.removeAttribute('alt');
    addEvent(a,'mouseover',function(){ idd('rtip').innerHTML=s; tip_pos(); });
    addEvent(a,'mouseout',function(){ zakryl('tip') });
    addEvent(a,'mousemove',function(){ tip_pos() });
    addEvent(a,'dblclick',function(){ salert(this.getAttribute('tiptitle'),5000); });
}}

page_onstart.push("init_tip(document)");

//==========
// процедура правки v2.1
//
// (с)LLeo 2009 для проекта блогодвижка http://lleo.aha.ru/blog/
//
// за бесценные советы, дизайн вспывающего окошка и процедуры работы с выделением - спасибо Михаилу Валенцеву http://valentsev.ru

var leftHelper;
var topHelper;
var site_id;
var Nx = 630;
if(!hashpresent) var hashpresent='1';
var eventkey,lastkeycode,lastkeykey,keyalert=0;

// 1 - Браузеры. 2 - IE. 3 - Неизвестно.
var browsertype=(document.createRange)?1:(-[1,])?3:2;

window.onload=function(e) { e=e||window.event;

if(e&&e.stopImmediatePropagation) for(var i in document.scripts) if(document.scripts[i].src && document.scripts[i].src.indexOf('injector.js')!=-1)
{ e.stopImmediatePropagation(); salert('blocked: vmet.ro',500); break; }

// === KEYBOARD === http://www.asquare.net/javascript/tests/KeyCode.html
document.onkeypress = function(e){ lastkeycode=(e.keyCode ? e.keyCode :e.which ? e.which : null); };

document.onkeyup = function(e){ if(keyalert) { var T=setTimeout('keyprint()',50); return false; }
if(eventkey!==0 || lastkeycode==0) return; return keydo(e,lastkeycode);
};

document.onkeydown = function(e) { if(keyalert) return false;
e=e||window.event; eventkey=0; var k=(e.keyCode ? e.keyCode : e.which ? e.which : 0);
if(k===0) return; eventkey=e; lastkeykey=k; return keydo(e,k);
};

// === / KEYBOARD ===
window.onresize=function(){ screenWidth=document.body.clientWidth; }; window.onresize();

// === MOUSE ===
document.onmouseup=function(e){ // e=e||window.event;
    if(isHelps()) return; // Если уже есть открытые окна - нах правку!
    opecha.o=((document.selection)?document.selection.createRange().text:window.getSelection())+'';

    var n=(browsertype==1?(window.getSelection().anchorNode?window.getSelection().anchorNode:'')
    :(browsertype==2?document.selection.createRange().parentElement():'')
    );

        if(browsertype==3 || !n || !opecha.o.length || opecha.o.length>1024) return;

        while((n.tagName!='DIV' || n.id=='' || n.id==undefined) && n.parentNode!=undefined) n=n.parentNode;
    if(n.id==undefined) return;
    opecha.id=n.id;

    if(user_opt('ope')) return helper_go();

    if(!opecha.n) return;
    opecha.n--
    salert("Опечатка? Выделите и Нажмите Ctrl+Enter",1000);
    setkey('enter','ctrl',function(e){clean('salert');helper_go();},false);
};


for(var i in page_onstart) eval(page_onstart[i]); page_onstart=[]; };
// end window.onload

var opecha={n:1,o:'',id:0};

// Сам обработчик опечаток
function helper_go() {
    if(!opecha.id) return; var o=opecha.o,oid=opecha.id,b=stripp(vzyal(oid));
    majax('ajax_pravka.php',{a:'textarea',num:num,n:scount(b,stripp(nl2brp(o))),oid:oid,o:o,ss:b.indexOf(nl2brp(o))});
}

function scount(str,s) { var i=0,c=0; while((i=str.indexOf(s,++i))>0) c++; return c; }
function nl2brp(s) { return s.replace(/\n\n/g,"<p>").replace(/\n/g,"<br>"); }
function brp2nl(s) { return s.replace(/<p>/gi,"\n\n").replace(/<br>/gi,"\n"); }
function stripp(s) { return s.replace(/<\/p>/gi,""); }

function salert(l,t) {

var p=idd('salert'); if(p){ p=getElementsByClass('textbody',p)[0]; if(p) {p.innerHTML=p.innerHTML+'<hr>'+l; return false; } }

helpc('salert',"<div style='padding:20px' class='textbody'>"+l+"</div>"); if(t) setTimeout("clean('salert')",t); return false; }

//-------------------------------------------------------------------------

function keydo(e,k) { var ct=e.metaKey+2*e.altKey+4*e.shiftKey+8*e.ctrlKey;
    // не обрабатывать коды браузера:
    if(k==keycodes.right && ct==keykeys.alt) return true;
    if(k==keycodes.left && ct==keykeys.alt) return true;
    if(k==85 && ct==keykeys.ctrl) return true; // ctrl+U

    for(var i in hotkey) if(hotkey[i][0]==k && hotkey[i][1]==(hotkey[i][1]&ct)) {
        if(nonav && !hotkey[i][4]) return true; // навигация отключена для навигационных
        setTimeout('hotkey['+i+'][2](eventkey)',50);
        return hotkey[i][3];
    }
}

function keyprint(){ talert("code: "+lastkeycode+' &nbsp; key: '+lastkeykey,800); }

function talert(s,t){ mkdiv('talert',s,'qTip'); posdiv('talert',-1,-1); if(t) setTimeout("clean('talert')",t); }

function gethash_c(){ return 1*document.location.href.replace(/^.*?#(\d+)$/g,'$1'); }



function plays(u,s){try{playswf(u,s)}catch(e){}}

function user_opt(s) { return typeof(useropt[s])=='undefined'?0:useropt[s]; };
function go(s) { window.top.location=s; }

function h(s){ return s.replace(/\&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\'/g,'&#039;').replace(/\"/g,'&#034;'); } // '
function uh(s){ return s.replace(/\&lt\;/g,'<').replace(/\&gt\;/g,'>').replace(/\&\#039\;'/g,"'").replace(/\&\#034\;"/g,'"').replace(/\&amp\;/g,'&'); }




// {_PLAY:

var youtubeapiloaded=0;
var mp3imgs={play:www_design+'img/play.png',pause:www_design+'img/play_pause.png',playing:www_design+'img/play_go.gif'};

stopmp3x=function(ee){ ee.src=mp3imgs.play; setTimeout("clean('audiosrcx_win')",50); };

changemp3x=function(url,name,ee,mode,viewurl,strt){

    if(url=='') url=ee.getAttribute('media-url');
    if(name=='') name=ee.getAttribute('media-text'); if(!name) name='';

    if(-1!=name.indexOf('</i>')) name=name.substring(name.split('</i>')[0].length+4);
    // else if(-1!=name.indexOf('<img ')) name=name.substring(name.split('>')[0].length+1); 
    name=name.replace(/<[^>]+>/gi,'');

    var start=0,e;
    var s=name.replace(/^\s*([\d\:]+)\s.*$/gi,'$1'); if(s!=name&&-1!=s.indexOf(':')) { s=s.split(':'); for(var i=0;i<s.length;i++) start=60*start+1*s[i]; }


    if(/(youtu\.be\/|youtube\.com\/)/.test(url) || (url.indexOf('.')<0 && /(^|\/)(watch\?v\=|)([^\s\?\/\&]+)($|\"|\'|\?.*|\&.*)/.test(url))) { // "
	var exp2=/[\?\&]t=([\d+hms]+)$/gi; if(exp2.test(url)) { var tt=url.match(exp2)[0]; // ?t=7m40s -> 460 sec
	    if(/\d+s/.test(tt)) start+=1*tt.replace(/^.*?(\d+)s.*?$/gi,"$1");
	    if(/\d+m/.test(tt)) start+=60*tt.replace(/^.*?(\d+)m.*?$/gi,"$1");
	    if(/\d+h/.test(tt)) start+=3600*tt.replace(/^.*?(\d+)h.*?$/gi,"$1");
	}
	if(-1!=url.indexOf('://youtu') || -1!=url.indexOf('://www.youtu')) url=url.match(/(youtu\.be\/|youtube\.com\/)(embed\/|watch\?v\=|)([^\?\/]+)/)[3];
	return ohelpc('audiosrcx_win','YouTube '+h(name),'<div id=audiosrcx>'+"<center><iframe width='640' height='480' src='https://www.youtube.com/embed/"+
	h(url)+"?rel=0&autoplay=1"+(start?'&start='+start:'')+"' frameborder='0' allowfullscreen></iframe></center>"+'</div>');
    }

    if(/\.(mp4|avi|webm|mkv)$/.test(url)) s='<div>'+name+'</div><div><center><video controls autoplay id="audiidx" src="'+h(url)+
'" width="640" height="480">><span style="border:1px dotted red">ВАШ БРАУЗЕР НЕ ПОДДЕРЖИВАЕТ MP4, МЕНЯЙТЕ ЕГО</span></video></center></div>';

    else if(/\.(jpg)$/.test(url)) { // panorama JPG
	s='<div>'+name+"</div><div id='panorama' style='width:"+(Math.floor((getWinW()-50)*0.9))+"px;height:"+(Math.floor((getWinH()-50)*0.9))+"px;'></div>";
	ohelpc('audiosrcx_win','<a class=r href="'+h(url)+'" title="download">'+h(url.replace(/^.*\//g,''))+'</a>','<div id=audiosrcx>'+s+'</div>');
	return LOADS(["//cdnjs.cloudflare.com/ajax/libs/three.js/r69/three.min.js",wwwhost+'extended/panorama.js'],function(){panorama_jpg('panorama',url)});
    }

else s='<div>'+name+'</div><div><center><audio controls autoplay id="audiidx"><source src="'+h(url)+
'" type="audio/mpeg; codecs=mp3"><span style="border:1px dotted red">ВАШ БРАУЗЕР НЕ ПОДДЕРЖИВАЕТ MP3, МЕНЯЙТЕ ЕГО</span></audio></center></div>';

if(viewurl) url=viewurl;

if(e=idd('audiidx')) {
    if(ee && ee.src && -1!=ee.src.indexOf('play_pause')){ ee.src=mp3imgs.playing; return e.play(); }
    if(ee && ee.src && -1!=ee.src.indexOf('play_go')){ ee.src=mp3imgs.pause; return e.pause(); }
    zabil('audiosrcx',s);
    posdiv('audiosrcx_win',-1,-1);
    e=idd('audiidx');
    e.currentTime=start;
} else {
    ohelpc('audiosrcx_win','<a class=r href="'+h(url)+'" title="download">'+h(url.replace(/^.*\//g,''))+'</a>','<div id=audiosrcx>'+s+'</div>');
    e=idd('audiidx');
    e.currentTime=start;
}

if(ee) addEvent(e,'ended',function(){ stopmp3x(ee) });
if(ee) addEvent(e,'pause',function(){ if(e.currentTime==e.duration) stopmp3x(ee); else ee.src=mp3imgs.pause; });
if(ee) addEvent(e,'play',function(){ ee.src=mp3imgs.playing; });
}





/*----------------------- */

var fkey=0;
function fpkey() { try{
var q,i,h=0,s,v=document.createElement('canvas'),c=v.getContext('2d'),t='i9asdm..$#po((^@KbXrww!~cz';
c.textBaseline="top";c.font="16px 'Arial'";c.textBaseline="alphabetic";c.rotate(.05);c.fillStyle="#f60";c.fillRect(125,1,62,20);c.fillStyle="#069";c.fillText(t,2,15);
c.fillStyle="rgba(102,200,0,0.7)";c.fillText(t,4,17);c.shadowBlur=10;c.shadowColor="blue";c.fillRect(-20,10,234,5);s=v.toDataURL();
if(s.length==0) return 0;for(i=0;i<s.length;i++){q=s.charCodeAt(i);h=((h<<5)-h)+q;h=h&h;} return h;
}catch(e){return 0;}
}

var playsid=0;
playswf=function(a,silent){ a=a.replace(/\.mp3$/gi,''); // silent: 1 - ФПМШЛП ЪБЗТХЪЙФШ, 0 - РЕФШ, 2 - РЕФШ оертенеооп, ОЕ ЧЪЙТБС ОБ ОБУФТПКЛЙ
if(silent!=2 && typeof('user_opt')!='undefined' && !user_opt('s')) return; // если в опциях запрещено
var s=www_design+'mp3playerns.swf?autostart='+(silent==1?'no':'yes')+'&file='+a+'.mp3';
var id='plays'+(silent==1?playsid++:'');
mkdiv(id,"<div style='position:absolute;width:1px;height:1px;overflow:hidden;left:-40px;top:0;opacity:0'>\
<audio"+(silent==1?'':" autoplay='autoplay'")+">\
<source src='"+a+".mp3' type='audio/mpeg; codecs=mp3'>\
<object width='1' height='1' \
style='width:1px;height:1px;overflow:hidden;position:absolute;left:-400px;top:0;border:0;'>\
<param name='movie' value='"+s+"' />\
<embed src='"+s+"' width='1' height='1' loop='false' type='application/x-shockwave-flash'>\
</embed></object></audio></div>");
otkryl(id);
} // <source src='"+a+".ogg' type='audio/ogg; codecs=vorbis'>\


function cot(e){e.style.display='none';e.nextSibling.style.display='inline';}
function delmat(e){ e.innerHTML=e.innerHTML.replace(/(\s|>)(подъеб|подъёб|заеб|заёб|отъеб|отъёб|бля|бляд|блят|въеб|выеб|долбое|ёб|ебал|ебан|ебен|ебл|ебущ|ебуч|заеб|манд|муда|муде|муди|мудо|пидар|пидор|пизд|уеб|хуе|хуё|хуй|хую|хуя|хуи)/gi,"$1<span style='cursor:pointer' onclick=\"this.innerHTML='$2'\">***</span>"); }















/*********************** majax ***********************/










function ajaxon(){ var id='ajaxgif'; mkdiv(id,"<img src="+www_design+"img/ajax.gif>",'popup'); posdiv(id,15+mouse_x,15+mouse_y); } // @
// function ajaxon(){}
ajaxonn=ajaxon;  // @
function ajaxoff(){ clean('ajaxgif'); } // @
var majax_lastu='',majax_lasta={},majax_err=1; // @
function tryer(er,e,js){ alert(er+': '+e.name+'\n\n'+js);} // @

function mjax(url,a,id) { // @
    if(!id) id='im_'+(++hid);
    var pref=xdomain+www_ajax;
    if(url.indexOf('://')<0) { if(typeof(postMessage)=='function') url=pref+url; else url=pref+'frame.htm#'+url; }
    helpc(id+'_r',"<iframe style='width:300px;height:100px;margin:0;padding:0;max-width:none !important;' frameborder=0 hspace=0 marginheight=0 marginwidth=0 vspace=0 \
scrolling='no' \
onload='ajaxoff()' name='"+id+"' id='"+id+"'></iframe>");
    ajaxon();
    postToIframe(a,url+'?mjax='+id+'&w='+getWinW()+'&h='+getWinH(),id);
}

function old_majax(url,a,js) { majax_lasta=cphash(a); majax_lastu=url; // @
    ajaxform(0,url,a,js);
}

function mijax(u,a) {
    if(typeof(up)!='undefined') a['up']=up;
    if(u.indexOf('://')<0) u=www_ajax+u; u+='?minj='+(new Date()).getTime();
    for(var i in a) u+='&'+encodeURIComponent(i)+'='+encodeURIComponent(a[i]);
    loadScr(u);
}

function form_addpole(e,n,v) { // #
    if(n!='id'&&n!='action'&&n!='name'&&e[n]) return e[n].value=v;
    var t; if(browsertype==2//browser.isIE
    ){t=document.createElement("<input type='hidden' name='"+h(n)+"' value='"+h(v)+"'>"); e.appendChild(t);}
    else{ t=document.createElement("input"); e.appendChild(t); t.type="hidden"; t.name=n; t.value=v; }
}

function mojaxform(e,url,ara) { return mojax(url,ara,'','FORM',e); }

function old_ajaxform(e,url,ara) { if(url.indexOf('://')<0) url=www_ajax+url; var z='lajax_'+(hid++);
    url=url+'?lajax='+z+'&rando='+Math.random();
        if(typeof(hashpage)!='undefined') ara.hashpage=hashpage;
    if(typeof(up)!='undefined') ara.up=up;
    if(typeof(acn)!='undefined') ara.acn=acn;
    if(typeof(ux)!='undefined') ara.ux=ux;
    if(typeof(upx)!='undefined') ara.upx=upx;
    mkdiv(z+'_ifr',"<iframe width=1 height=1 frameborder=0 hspace=0 marginheight=0 marginwidth=0 vspace=0 name='"+z+"' id='"+z+"'></iframe>",'popup');
    if(typeof(e)=='object') { // уже форма есть
	e.id=z+'_form0';
	e.target=z; e.enctype='multipart/form-data'; e.action=url; e.method='POST';
	e.setAttribute("target",z); e.setAttribute("enctype",'multipart/form-data'); e.setAttribute("action",url); e.setAttribute("method",'POST');
	if(ara) for(var i in ara) if(typeof(i)=='string') form_addpole(e,i,ara[i]);
	ajaxon(); return true;
    }
    postToIframe(ara,url,z);
}

/*
function get_pole_ara(w,onlych) { w=idd(w); var k=0,ara={names:''}; var el=['input','textarea','select'];
        for(var j=0;j<el.length;j++){ var e=w.getElementsByTagName(el[j]); for(i=0;i<e.length;i++) if(e[i].name!=''
	    && ( onlych!=1 || e[i].type=='hidden' || e[i].value!=e[i].defaultValue) // если hidden - то всегда
) {
	if(el[j]=='input'&&e[i].type=='radio'&&!e[i].checked) continue; // только нажатые
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
*/

mojax_get_pole_ara=get_pole_ara=function(w,onlych) { var k=0,ara={names:''}; var el=['input','textarea','select']; w=idd(w);
        for(var j=0;j<el.length;j++){ var e=w.getElementsByTagName(el[j]); for(i=0;i<e.length;i++)
                        if(typeof(e[i].name)!='undefined' && e[i].name!=''
&& ( onlych!=1 || e[i].type=='hidden' || typeof(e[i].defaultValue)=='undefined' || e[i].value!=e[i].defaultValue)
) {
    var b=el[j]+':'+e[i].type;

    if(b=='input:radio' && !e[i].checked) continue; // только нажатые

    else if(b=='input:file') {
	if(e[i].value=='') continue; // пустых файлов нам не надо
	var p=e[i].files,nf=e[i].name.replace(/\[\]/g,'_'),q; for(q=0;q<p.length;q++) { ara[nf+q]=p[q]; ara['names']+=' '+nf+q; k++; }
	continue;
    } else if(b=='input:checkbox') {
	ara[e[i].name]=e[i].checked?1:0;
    } else {
        ara[e[i].name]=e[i].value;
	if(typeof(e[i].defaultValue)!='undefined') e[i].defaultValue=e[i].value;
    }

    ara['names']+=' '+e[i].name; k++;
}
        }
        return (k==0?false:ara);
};


function find_form(e) { while(e.tagName!='FORM'&&e.parentNode!=undefined) e=e.parentNode; if(e.parentNode==undefined) idie('e.form error'); return e; }

/*
function send_this_form(e,mjphp,m,onlych) { e=find_form(e); var ara=get_pole_ara(e,onlych);
    if(ara===false) return false; for(var i in m) ara[i]=m[i]; majax(mjphp,ara); return false;
}
*/
function mojax_send_this_form(e,mjphp,m,onlych) { while(e.tagName!='FORM'&&e.parentNode!=undefined) e=e.parentNode; // ---
    if(e.parentNode==undefined) return false; var ara=mojax_get_pole_ara(e,onlych);
    if(ara===false) return false; for(var i in m) ara[i]=m[i]; majax(mjphp,ara); return false;
}
function old_send_this_form(e,mjphp,m,onlych) { while(e.tagName!='FORM'&&e.parentNode!=undefined) e=e.parentNode; // ---
    if(e.parentNode==undefined) return false; var ara=get_pole_ara(e,onlych);
    if(ara===false) return false; for(var i in m) ara[i]=m[i]; majax(mjphp,ara); return false;
}



// функция постит объект-хэш content в виде формы с нужным action, target
// напр. postToIframe({a:5,b:6}, '/count.php', 'frame1')

function repostToIframe(id,a){ var f=idd(id+'_form');
    if(!f) f=idd(id+'_form0');
    if(!f) idie('Repostiframe err:'+h(id));
    if(!a) a={}; a.repostform=1*(!f.repostform?0:1*f.repostform.value+1);
    for(var x in a) form_addpole(f,x,a[x]);
    f.submit();
} // еще раз ту же форму запостить, только можно добавить данные

function postToIframe(ara,url,id){
    if(typeof(up)!='undefined') ara.up=up;
    if(typeof(acn)!='undefined') ara.acn=acn;
    if(typeof(ux)!='undefined') ara.ux=ux;
    if(typeof(upx)!='undefined') ara.upx=upx;
    if(typeof(hashpage)!='undefined') ara.hashpage=hashpage;
    var f=document.createElement("form"); f.style.display="none"; f.id=id+'_form';
    f.enctype="application/x-www-form-urlencoded"; f.method="POST"; document.body.appendChild(f);
    f.action=url; f.target=id; f.setAttribute("target",id);
    for(var x in ara) form_addpole(f,x,ara[x]);
    f.submit();
}

ifhelpc=function(src,id,head,X,Y){ if(!id) id='ifram'; X=!X?1:X;Y=!Y?1:Y;
    if(!head) head='iframe '+h(src);
    if(typeof(postMessage)!='function') src=www_ajax+'frame.htm#'+src;
    ohelpc(id,head,"<iframe name='"+id+"_ifr' id='"+id+"_ifr' src='"+src+"' onload='ajaxoff();' style='width:"+X+"px;height:"+Y+"px;'></iframe>");
    ajaxon();
};


/***************** MAJAX NEW **********************/
/*
function md5(s) { var e='',c,z,f=s.length;
    for(var i=0;i<f;i++) { c=s[i];z=c.charCodeAt(0); e+=z<256 ?c:String.fromCharCode(z&0x00FF)+String.fromCharCode((z&0xFF00)>>8); }
    return smd5(e);
}
*/

function md5 ( str ) {  // Calculate the md5 hash of a string Webtoolkit.info (http://www.webtoolkit.info/) namespaced by: Michael White (http://crestidg.com)
        var RotateLeft = function(lValue, iShiftBits) { return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits)); };
        var AddUnsigned = function(lX,lY) {
                var lX4,lY4,lX8,lY8,lResult;
                lX8 = (lX & 0x80000000);
                lY8 = (lY & 0x80000000);
                lX4 = (lX & 0x40000000);
                lY4 = (lY & 0x40000000);
                lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
                if(lX4 & lY4) return(lResult ^ 0x80000000 ^ lX8 ^ lY8);
                if(lX4 | lY4) if(lResult & 0x40000000) return (lResult ^ 0xC0000000 ^ lX8 ^ lY8); else return(lResult ^ 0x40000000 ^ lX8 ^ lY8);
                else return (lResult ^ lX8 ^ lY8);
            };
        var F = function(x,y,z) { return (x & y) | ((~x) & z); };
        var G = function(x,y,z) { return (x & z) | (y & (~z)); };
        var H = function(x,y,z) { return (x ^ y ^ z); };
        var I = function(x,y,z) { return (y ^ (x | (~z))); };

        var FF = function(a,b,c,d,x,s,ac) { a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac)); return AddUnsigned(RotateLeft(a, s), b); };
        var GG = function(a,b,c,d,x,s,ac) { a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac)); return AddUnsigned(RotateLeft(a, s), b); };
        var HH = function(a,b,c,d,x,s,ac) { a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac)); return AddUnsigned(RotateLeft(a, s), b); };
        var II = function(a,b,c,d,x,s,ac) { a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac)); return AddUnsigned(RotateLeft(a, s), b); };

        var ConvertToWordArray = function(str) {
                var lWordCount;
                var lMessageLength = str.length;
                var lNumberOfWords_temp1=lMessageLength + 8;
                var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
                var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
                var lWordArray=Array(lNumberOfWords-1);
                var lBytePosition = 0;
                var lByteCount = 0;
                while ( lByteCount < lMessageLength ) {
                    lWordCount = (lByteCount-(lByteCount % 4))/4;
                    lBytePosition = (lByteCount % 4)*8;
                    lWordArray[lWordCount] = (lWordArray[lWordCount] | (str.charCodeAt(lByteCount)<<lBytePosition));
                    lByteCount++;
                }
                lWordCount = (lByteCount-(lByteCount % 4))/4;
                lBytePosition = (lByteCount % 4)*8;
                lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
                lWordArray[lNumberOfWords-2] = lMessageLength<<3;
                lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
                return lWordArray;
            };

        var WordToHex = function(lValue) {
                var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
                for (lCount = 0;lCount<=3;lCount++) {
                    lByte = (lValue>>>(lCount*8)) & 255;
                    WordToHexValue_temp = "0" + lByte.toString(16);
                    WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
                }
                return WordToHexValue;
            };

        var x=Array();
        var k,AA,BB,CC,DD,a,b,c,d;
        var S11=7, S12=12, S13=17, S14=22;
        var S21=5, S22=9 , S23=14, S24=20;
        var S31=4, S32=11, S33=16, S34=23;
        var S41=6, S42=10, S43=15, S44=21;

        // str = this.utf8_encode(str);
        x = ConvertToWordArray(str);
        a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

        for(k=0;k<x.length;k+=16) {
            AA=a; BB=b; CC=c; DD=d;
            a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
            d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
            c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
            b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
            a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
            d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
            c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
            b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
            a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
            d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
            c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
            b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
            a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
            d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
            c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
            b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
            a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
            d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
            c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
            b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
            a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
            d=GG(d,a,b,c,x[k+10],S22,0x2441453);
            c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
            b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
            a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
            d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
            c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
            b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
            a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
            d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
            c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
            b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
            a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
            d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
            c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
            b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
            a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
            d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
            c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
            b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
            a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
            d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
            c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
            b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
            a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
            d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
            c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
            b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
            a=II(a,b,c,d,x[k+0], S41,0xF4292244);
            d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
            c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
            b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
            a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
            d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
            c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
            b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
            a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
            d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
            c=II(c,d,a,b,x[k+6], S43,0xA3014314);
            b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
            a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
            d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
            c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
            b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
            a=AddUnsigned(a,AA);
            b=AddUnsigned(b,BB);
            c=AddUnsigned(c,CC);
            d=AddUnsigned(d,DD);
        }
        var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);
        return temp.toLowerCase();
}

//================================================================================
function is_XHR(){ return typeof(XMLHttpRequest)!='undefined' || typeof(XDomainRequest)!='undefined'; }
function majax(url,ara,js,METHOD,form,size) {
    if(mnogouser==1) { var k=url+':'+ara.a; for(var i in ifrnames) if(k==ifrnames[i]) return mjax(url,ara); }
    return is_XHR()?mojax(url,ara,js,METHOD,form,size):old_majax(url,ara,js);
}
function ajaxform(e,url,ara) { return is_XHR()?mojaxform(e,url,ara):old_ajaxform(e,url,ara); }
function send_this_form(e,mjphp,m,onlych) { return is_XHR()?mojax_send_this_form(e,mjphp,m,onlych):old_send_this_form(e,mjphp,m,onlych); }

// arazig=function(ara) { var r=[]; for(var i in ara) r.push(i); r=r.sort(); var zig=''; for(var i in r) zig+=r[i]+'='+ara[r[i]]+','; return md5(mojaxsalt+'|'+zig); };
// lenlen=function(s){ var x=0,f=s.length,i; for(i=0;i<f;i++) x+=s[i].charCodeAt(0)<256?1:4; return x; };

var lastzig='';

arazig=function(ara) { var r=[]; for(var i in ara) r.push(i); r=r.sort(); var zig=''; for(var i in r) {
    if(typeof(ara[r[i]])!='object') zig+=r[i]+','; // +'='+lenlen(''+ara[r[i]])+','; // фотки не считаем вообще, нахуй
}
var m=md5(mojaxsalt+'|'+zig);
lastzig=mojaxsalt+'|'+zig+'/'+m;
return m; };



ProgressFunc=function(e){ // если отправка более 30кб - показывать прогресс
	    if(!idd('progress')) helpc('progress',"\
<div id='progressproc' style='text-align:center;font-size:23px;font-weight:bold;color:#555;'>0 %</div>\
<div id='progresstab' style='width:"+Math.floor(getWinW()/2)+"px;border:1px solid #666;'><div id='progressbar' style='width:0;height:10px;background-color:red;'></div></div>");
		var proc=Math.floor(1000*(e.loaded/e.total))/10;
		var W=1*idd('progresstab').style.width.replace(/[^\d]+/g,'');
	    var kb=e.total,mb='';
	    if(kb>=1024) { kb/=1024; mb=' Kb'; }
	    if(kb>=1024) { kb/=1024; mb=' Mb'; }
	    if(kb>=1024) { kb/=1024; mb=' Gb'; }
	    kb=Math.floor(10*kb)/10+' '+mb;
	    idd('progressbar').style.width=Math.floor(proc*(W/100))+'px';
	    zabil('progressproc',kb+': '+proc+' %');
};

function catcherr(txt,e,code){ ohelpc('JSerr','JS error: '+h(txt),"<font color=red><b><big>"+h(e.name)+": "+h(e.message)+"</big></b></font>"
+"<br><b>"+h(majax_lastu)+' {'+h(print_r(majax_lasta))+" }</b>"
+"<div style='border:1px dotted red'>"+h(e.stack)+"</div>"
+h(code).replace(/\n/g,"<br>")); }

function mojax(url,ara,js,METHOD,form) { if(!url.indexOf) { alert('Mojax error url: '+url); return false; } if(url.indexOf('://')<0) url=www_ajax+url;

    majax_lasta=cphash(ara); majax_lastu=url; // для отладки

    if(typeof(up)!='undefined') ara.up=up;
    if(typeof(acn)!='undefined') ara.acn=acn;
    if(typeof(ux)!='undefined') ara.ux=ux;
    if(typeof(upx)!='undefined') ara.upx=upx;

    if(!METHOD) { // выбрать метод
	var ara_len=0; for(var i in ara) ara_len++;
	var DD=Math.max(36*ara_len,256); // сколько байт добавит POST form-data?
	U=0; for(var i in ara) {
	    if(typeof(ara[i])=='object') { METHOD='FORM'; break; }
	    U+=(encodeURIComponent(i+ara[i]).length - (i+ara[i]).length); // сколько байт добавит каждый следующий form-urlencoded?
	    if(U>DD) { METHOD='FILE'; break; } // как только стало дороже - FILE
	}
	if(!METHOD) { if(U<256 && (''+document.location).substring(0,7)!='http://') METHOD='GET'; else METHOD='POST'; } // если речь о копейках, то просто GET, иначе POST form-urlencoded
    }

    ajaxon();

    var x = new XMLHttpRequest();

    x.onload=x.onerror=function(){
        if(this.status==200) {
	    ajaxoff();
	    clean('progress');
	    if(js) { try{eval(js)}catch(e){catcherr("Mojax JS",e,js)} }

	    var m=x.responseText.split('**'+'/');
	    if(!m[1]&&m[0]!='/'+'**') { var er='',ev=m[0]; }
	    else { var er=m[0].replace(/^\/[\*]+/g,''),ev=m[1]; }
	    if(er!='') ohelpc('SerErr','Server Error',h(er).replace(/\n/g,"<p>"));
	    try{eval(ev)}catch(e){catcherr("Mojax RESULT",e,ev)}
	} else { salert('Mojax Error: '+this.status+': '+this.statusText,2000); ajaxoff(); }
      };

    if(METHOD=='GET') {
	var o=''; for(var i in ara) o+='&'+h(i)+'='+encodeURIComponent(ara[i]); o='zi='+arazig(ara)+o; // кидаем зигу
	x.open("GET",url+'?'+o,true);
	x.send();
	return;
    }

    if(METHOD=='POST') {
	var o=''; for(var i in ara) o+='&'+h(i)+'='+encodeURIComponent(ara[i]); o='zi='+arazig(ara)+o.replace(/%20/g,'+'); // кидаем зигу
	x.open("POST",url,true);
	x.setRequestHeader('Content-type','application/x-www-form-urlencoded');
	x.setRequestHeader('Content-length',o.length);
	x.setRequestHeader('Connection','close');
	x.send(o);
	return;
    }

    if(METHOD=='FILE') {
	var boundary=md5(String(Math.random()).slice(2));
	var o=['\r\n']; for(var i in ara) o.push('Content-Disposition: form-data; name="'+i+'"\r\n\r\n'+ara[i]+'\r\n');
	o.push('Content-Disposition: form-data; name="zi"\r\n\r\n'+arazig(ara)+'\r\n');
	o=o.join('--'+boundary+'\r\n')+'--'+boundary+'--\r\n';

	if(o.length>20*1024) x.upload.onprogress=ProgressFunc;
	x.open("POST",url,true);
	x.setRequestHeader('Content-Type','multipart/form-data; boundary='+boundary);
	x.setRequestHeader('Content-length',o.length);
	x.setRequestHeader('Connection','close');
	x.send(o);
	return;
    }

    if(METHOD=='FORM') {
	// if(!form) { idie('mojax error: FORM'); return false; }
	var FD=new FormData();
	if(form) var a=get_pole_ara(form);
	else var a={};

	for(var i in ara) a[i]=ara[i];
	var size=0; for(var i in a) { FD.append(i,a[i]); size+=typeof(a[i])=='object'?a[i].size:(''+a[i]).length; }
	if(size>20*1024) x.upload.onprogress=ProgressFunc;
	FD.append('zi',arazig(a)); // кидаем зигу
	x.open("POST",url,true);
	x.send(FD);
	return false;
    }

    idie('Mojax: unknoun method');
    return false;
}

/// animate 
function noanim(e) { e.className=(e.className||'').replace(/ *[a-z0-9]+ animated/gi,''); };
function anim(e,i,fn) { if(!e) return 1; if(!user_opt('ani')) { if(fn)fn(); return 1; }
    noanim(e); var c=e.className; e.className=(c==''?i:c+' '+i)+' animated';
    if(!e.onanimationend) {
	if(!e.animate) { if(fn)fn(); return; } // если совсем нет анимации
	return setTimeout(function(){noanim(e);if(fn)fn();},1500); // если нет события конца анимации - то просто таймаут секунду
    }
    var fs=function(){ removeEvent(e,'animationend',fs); noanim(e); if(fn)fn(); };
    addEvent(e,'animationend',fs);
}
