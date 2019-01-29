(function(){

/***************************************/
var EG_ROOT='http://lleo.me/dnevnik/design/ejik/tarakan/';
/***************************************/

function getWinW(){ return window.innerWidth?window.innerWidth : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth; }
function getWinH(){ return window.innerHeight?window.innerHeight : document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight; }
function getScrollH(){ return (document.documentElement.scrollTop || document.body.scrollTop); }
function getScrollW(){ return (document.documentElement.scrollLeft || document.body.scrollLeft); }

function idd(id){ if(typeof(id)=='object') return id;
    if(typeof(document.getElementById(id))=='undefined') return false;
    return document.getElementById(id);
}

function zabil(id,text) { if(idd(id)) { idd(id).innerHTML=text; } }
function zakryl(id) { if(!idd(id)) return; idd(id).style.display='none'; }
function otkryl(id) { if(idd(id)) idd(id).style.display='block'; }

function posdiv(id,x,y) { // позиционирование с проверкой на вылет, если аргумент '-1' - по центру экрана
    var e=idd(id),W,w,H,h,DW,DH;
    if(e.style.display!='block') otkryl(id);
    W=getWinW(); H=getWinH(); w=e.clientWidth; h=e.clientHeight;
    if(x==-1) x=(W-w)/2+getScrollW();
    if(y==-1) y=(H-h)/2+getScrollH();
    DW=W-10; if(w<DW && x+w>DW) x=DW-w; if(x<0) x=0;
    if(y<0) y=0;
    e.style.top=y+'px'; e.style.left=x+'px';
    otkryl(id);
}



function mkdiv(id,s,cls,paren,relative){ if(idd(id)) { zabil(id,s); idd(id).className=cls; return; }
    var div=document.createElement('DIV');
    div.className=cls; div.id=id; div.innerHTML=s; div.style.display='none';
    if(paren==undefined) paren=document.body;
    if(relative==undefined) paren.appendChild(div); // paren.lastChild
    else if(relative=='first') paren.insertBefore(div,paren.firstChild);
    else paren.insertBefore(div,relative.nextSibling);
}

var EG_B=20;
var EG_W=120+EG_B,EG_H=84+EG_B;
var EG_NAME='egw_';

var eg_povx=0.001;
var eg_povy=0.001;

var eg_x=EG_B+Math.random()*(getWinW()-EG_W-EG_B);
var eg_y=EG_B+Math.random()*(getWinH()-EG_H-EG_B);
var eg_kursx=Math.random()<0.5?1:-1;
var eg_kursy=Math.random()<0.5?1:-1;
var eg_i=0,eg_last=0,eg_raz='';
var eg_alredy={};

function eg_img(mas){ var egm=[]; for(var i in mas) { var l=mas[i];
if(typeof eg_alredy[l]!='undefined'){ egm.push(eg_alredy[l]); }
else { eg_alredy[l]=eg_last;

  mkdiv(EG_NAME+eg_last,'<img src=\''+EG_ROOT+l+'\' onclick=\'eg_click()\' onmouseover=\'eg_click()\'>');

  var ee=idd(EG_NAME+eg_last);
    ee.style.overflow='visible';
    ee.style.top='100px';
    ee.style.left='100px;';
    ee.style.position='absolute';
    ee.style.zIndex='9';

  egm.push(eg_last++);
}}return egm;}

var eg_sleep=100; var eg_speed=5;


var eg_ld=eg_img(['tarakan_l.gif']);
var eg_rd=eg_img(['tarakan_r.gif']);
var eg_lu=eg_img(['tarakan_lu.gif']);
var eg_ru=eg_img(['tarakan_ru.gif']);
var eg_xr=eg_img(['tarakap_r.gif','tarakap_r.gif','tarakap_r.gif', 'tarakap_r.gif','tarakap_r.gif','tarakap_r.gif','tarakap_r.gif','tarakap_r.gif']);
var eg_xl=eg_img(['tarakap_l.gif','tarakap_l.gif','tarakap_l.gif', 'tarakap_l.gif','tarakap_l.gif','tarakap_l.gif','tarakap_l.gif','tarakap_l.gif']);
var eg_yl=eg_img(['tarakap_l.gif','tarakap_l.gif','tarakap_l.gif', 'tarakap_l.gif','tarakap_l.gif','tarakap_l.gif','tarakap_l.gif','tarakap_l.gif']);
var eg_yr=eg_img(['tarakap_r.gif','tarakap_r.gif','tarakap_r.gif', 'tarakap_r.gif','tarakap_r.gif','tarakap_r.gif','tarakap_r.gif','tarakap_r.gif']);

eg_last=0;

function eg_click() { if(eg_raz!='') return false; eg_kursx=-1*eg_kursx; eg_raz='x'; return false; }

function eg_addkurs() {
  if(Math.random()<eg_povx) { eg_kursx=-1*eg_kursx; return 'x'; }
  if(Math.random()<eg_povy) { eg_kursy=-1*eg_kursy; return 'y'; }

  eg_x=eg_x+(eg_speed*eg_kursx);
  if(eg_x<EG_B) { eg_x=EG_B; eg_kursx=1; return 'x'; }
  if(eg_x>(getWinW()-EG_W)) { eg_x=getWinW()-EG_W; eg_kursx=-1; return 'x'; }

  eg_y=eg_y+(eg_speed*eg_kursy);
  if(eg_y<EG_B) { eg_y=EG_B; eg_kursy=1; return 'y'; }
  if(eg_y>(getWinH()-EG_H)) { eg_y=getWinH()-EG_H; eg_kursy=-1; return 'y'; }

  return '';
}

function egibegi(){
    if(eg_raz==''){ eg_raz=eg_addkurs(); if(eg_raz!=''){eg_i=-1;return egibegi();}
    var mas=(eg_kursy==1?(eg_kursx==1?eg_rd:eg_ld):(eg_kursx==1?eg_ru:eg_lu));
    } else if(eg_raz=='x') var mas=(eg_kursx==1?eg_xr:eg_xl);
    else var mas=(eg_kursx==1?eg_yr:eg_yl);

    if(++eg_i>=mas.length){ eg_i=0; if(eg_raz!=''){eg_raz='';return egibegi();} }

    zakryl(EG_NAME+eg_last);
    eg_last=mas[eg_i];
    posdiv(EG_NAME+eg_last,eg_x+getScrollW(),eg_y+getScrollH());
    setTimeout(egibegi,eg_sleep);
} egibegi();

})();