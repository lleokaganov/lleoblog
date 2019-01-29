var www_ajax='http://lleo.me/ajax/';
var www_design='http://lleo.me/design/';

function idd(id) { return document.getElementById(id); }
function zabil(id,text) { if(idd(id)) idd(id).innerHTML=text; }
function vzyal(id) { return idd(id)?idd(id).innerHTML:''; }
function zakryl(id) { idd(id).style.display='none'; }
function otkryl(id) { idd(id).style.display='block'; }
function clean(id) { if(idd(id)) { zakryl(id); setTimeout("var s=idd('"+id+"'); if(s) s.parentNode.removeChild(s);", 40); }}

// создать новый <DIV class='cls' id='id'>s</div> в элементе paren (если не указан - то просто в документе)
// есть указан relative - то следующим за relative, инае - просто последним
function mkdiv(id,s,cls,paren,relative){ if(idd(id)) { idd(id).innerHTML=s; idd(id).className=cls; return; }
        var div=document.createElement('DIV'); div.className=cls; div.id=id; div.innerHTML=s; div.style.display='none';
        if(paren==undefined) paren=document.body;
    var r = relative==undefined ? 0 : relative.nextSibling; // paren.lastChild
    if(r) paren.insertBefore(div,r); else paren.appendChild(div);
}

function posdiv(id,x,y) { // позиционирование с проверкой на вылет, если аргумент '-1' - по центру экрана
        var e=idd(id);
        var W=getWinW(); var H=getWinH();
        var w=e.clientWidth; var h=e.clientHeight;
    if(x==-1) x=(W-w)/2+getScrollW();
    if(y==-1) y=(H-h)/2+getScrollH();
    var DH=W-10; if(w<DH && x+w>DH) x=DH-w; if(x<0) x=0; 
    DH=getDocH()-10; if(h<DH && y+h>DH) y=DH-h; if(y<0) y=0;
        e.style.top=y+'px'; e.style.left=x+'px';
    otkryl(id);
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


function helps(id,s,pos) { s=s+"<div onclick=\"clean('"+id+"')\" class='can' title='cancel'></div>";

if(!idd(id)) {
    mkdiv(id,"<div class='corners'><div class='inner'><div class='content' id='"+id+"_body' align=left>"+s+"</div></div></div>",'popup');
// (c)mkm Вот рецепт локального счастья, проверенный в Опера10, ИЕ6, ИЕ8, FF3, Safari, Chrome.
// Таскать окно можно за 'рамку' - элементы от id до id+'_body', исключая body (и всех его детей).
var e_body=idd(id+'_body'); // За тело не таскаем
var hmov=false; // Предыдущие координаты мыши
var e=idd(id);
var pnt=e; while(pnt.parentNode) pnt=pnt.parentNode; //Ищем Адама

var mmFunc=function(ev) { if(!ev) ev=window.event;
    if(hmov) {
	e.style.left=parseFloat(e.style.left)+ev.clientX-hmov.x+'px';
	e.style.top=parseFloat(e.style.top)+ev.clientY-hmov.y+'px';
	hmov={x:ev.clientX,y:ev.clientY};
	if(ev.preventDefault) ev.preventDefault();
	return false;
    }
};

var muFunc=function(){
if(hmov){ hmov=false; removeEvent(pnt,'mousemove',mmFunc); removeEvent(pnt,'mouseup',muFunc);
e.style.cursor='auto'; }
};

addEvent(e,'mousedown', function(ev){ if(hmov) return;
    if(!ev) ev=window.event;
    var lbtn=(window.addEventListener?0:1); //Если ИЕ, левая кнопка=1, иначе 0
    if(!ev.target) ev.target=ev.srcElement;
    if((lbtn!==ev.button)) return; //Это была не левая кнопка или 'тело' окна, ничего не делаем
    var tgt=ev.target;
    while(tgt){ if(tgt==e_body) return; if(tgt==e) break; tgt=tgt.parentNode; };
    //Начинаем перетаскивать
    e.style.cursor='move';
    hmov={x:ev.clientX,y:ev.clientY}; addEvent(pnt,'mousemove',mmFunc); addEvent(pnt,'mouseup',muFunc);
    if(ev.preventDefault) ev.preventDefault();
    return false;
});
// ===========================================================================
} else zabil(id+'_body',s);
}

// координаты мыши
var mouse_x=mouse_y=0; 
document.onmousemove = function(e){ if(!e) e=window.event;
  if(e.pageX || e.pageY) { mouse_x=e.pageX; mouse_y=e.pageY; }
  else if(e.clientX || e.clientY) {
    mouse_x = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.clientLeft;
    mouse_y = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.clientTop;
  }
};

function ajaxon(){ var id='ajaxgif'; mkdiv(id,"<img src="+www_design+"img/ajax.gif>",'popup'); 
//posdiv(id,mouse_x,mouse_y);
}
function ajaxoff(){ clean('ajaxgif'); }

function getScrollH(){ return (document.documentElement.scrollTop || document.body.scrollTop); }
function getScrollW(){ return (document.documentElement.scrollLeft || document.body.scrollLeft); }

function getWinW(){ return window.innerWidth?window.innerWidth : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth; }
function getWinH(){ return window.innerHeight?window.innerHeight : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight; }
function getDocH(){ return document.compatMode!='CSS1Compat' ? document.body.scrollHeight : document.documentElement.scrollHeight; }

var bigfoto_onload=1; function bigfoto_pos(){ ajaxoff(); otkryl('bigfoto');
var e=idd('bigfotoimg'),w=e.width,h=e.height;
var H=(getWinH()-50); if(h>H && H>480) { w=w*(H/h); h=H; e.style.height=H+'px'; }
var W=(getWinW()-80); if(w>W && W>640) { h=h*(W/w); w=W; e.style.width=W+'px'; }
posdiv('bigfoto',-1,-1);
}

function bigfoto(e){ ajaxon(); bigfoto_onload=1; var s=(e.href == undefined ? e : e.href);
// setTimeout("if(bigfoto_onload) {alert(9); bigfoto_pos();}", 5000);
helps('bigfoto',"<center><img style='max-width:100;max-height:100;' id='bigfotoimg' onclick=\"clean('bigfoto')\" onload=\"bigfoto_onload=0;bigfoto_pos()\" src='"+s+"'><div class=r><a href='"+s+"'>"+s+"</a></div></center>");
return false;
}
