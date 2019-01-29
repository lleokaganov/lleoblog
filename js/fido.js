var fido_lastid=0;
var fido_lasti=0;
// var fido_lastare=0;
var fido_kluge=0;
var fido_scroll=0;
var fido_blok='';
var areasmode=0;
var lastaren=0;

var ebasa={};
var mya='';

function ksort(a) {
	var m={}; var k=[]; for(var i in a) { if(!isNaN(i)) k.push(i); else m[i]=a[i]; } k.sort;
	for(var i=0;i<k.length;i++) m[k[i]]=a[k[i]];
	if(a['length'] !== undefined) a['length']=k.length; return m;
}

function msgc(i,n) { msg(i); n=fido_nmes-n; ebasa[mya].b=n; begunok(n); }

function echotype() {

	try{

var aa=ebasa[mya],n=fido_nmes;
        for(var i in aa) { if(isNaN(i) || i<aa.i) continue;
		var a=aa[i];
		var lam="<img id='omsg"+i+"' src='"+(a.m?omsg_new:omsg_read)+"'>&nbsp;";
		var s='<td onclick="msgc('+i+','+n+')">'+lam+(1+1*i+aa.st)+') '+a.s+'</td><td>'+a.f+'</td><td>'+a.fa+'</td><td>'+a.t+'</td><td>'+a.ta+'</td>';
                zabil('pan'+(fido_nmes-n),s);
                if(!--n) break;
        } while(n) zabil('pan'+(fido_nmes-(n--)),'<td colspan=7>&nbsp;</td>');
	try{begunok(aa.b);}catch(e){tryer('begunok error',e,'')}

	try{msg(aa.i+aa.b);}catch(e){tryer('msg error',e,'')}


}catch(e){tryer('echotype error',e,'')}

}

function begunok(x){ for(var i=fido_nmes-1;i>-1;i--) idd('pan'+i).style.backgroundColor= x!=i ? 'transparent' : '#CADFEF'; }


function areamove(n) { 
	if(fidoblok!='fidoarea') return; // 'fidomsg','fidoarea','echotags'
	var x=1*lastaren+1*n; if(x<0||x>=aren.length) return; 
	setpolozarea(x);
	setTimeout('timeproc('+(++timeo)+',"charea('+mya+')")',100);
}

function setpolozarea(x){
	for(var i=aren.length-1;i>=0;i--) {
		var e=idd('a'+aren[i]); if(e) e.style.backgroundColor=(i==x?'#CADFEF':'transparent');
	}
//	var e=idd('a'+mya); if(e) e.style.backgroundColor='transparent';
	lastaren=x; mya=aren[x];
//	e=idd('a'+mya); if(e) e.style.backgroundColor='#CADFEF';
}

function charea(arean,js){
	if(typeof js=='undefined') majax('module.php',{mod:'FIDO',a:'charea',arean:arean,all:areasmode});
	else majax('module.php',{mod:'FIDO',a:'charea',arean:arean,all:areasmode},'echotype()');
	setmya(arean);
}

function setmya(arean) { if(mya==arean) return;
	mya=arean; for(var i in aren) if(mya==aren[i]) { lastaren=i; setpolozarea(i); break; }
}


function begunok_up(){ var b=ebasa[mya].b;
// salert('en: '+ebasa[mya].en+' len: '+ebasa[mya].len +' st:'+ebasa[mya].st,500);
/*
ebasa[mya].i=0; - текущий номер в верхней строке экрана
ebasa[mya].b=0; - позици€ бегунка
ebasa[mya].len=0; - нынешн€€ длина
ebasa[mya].st=0; - если N, то имеетс€ еще N более новых записей, пока не закачанных
ebasa[mya].en=0; - если 1, то мы достигли конца архива
*/

	if(ebasa[mya].st!=0 && !ebasa[mya].i && !b) {
		var lastid=ebasa[mya][0].id;
//		majax('module.php',{mod:'FIDO',a:'loadarea',arean:mya,lastid:lastid,pre:1},'begunok_up()');
		majax('module.php',{mod:'FIDO',a:'loadarea',arean:mya,lastid:lastid,pre:1,all:areasmode});
		return;
	}
	if(!b) {
		if(typeof ebasa[mya][ebasa[mya].i-1] != 'undefined') { ebasa[mya].i--; echotype(); }
		else { if(!ebasa[mya].i) fidoselect('fidoarea'); }
		return;
	} else { b--; ebasa[mya].b=b; begunok(b); msg(ebasa[mya].i+b); }
	fidoselect('echotags');
}

function begunok_down(){ var b=ebasa[mya].b;
// salert('en: '+ebasa[mya].en+' len: '+ebasa[mya].len +' II:'+(ebasa[mya].i+2+fido_nmes),2000);
	if(!ebasa[mya].en && (ebasa[mya].i+2+fido_nmes) > ebasa[mya].len) {
		var lastid=ebasa[mya][ebasa[mya].len-1].id;
		majax('module.php',{mod:'FIDO',a:'loadarea',arean:mya,lastid:lastid,all:areasmode});
		return;
	}

	if(b==fido_nmes-1) {
		if(typeof ebasa[mya][ebasa[mya].i+fido_nmes] != 'undefined') { ebasa[mya].i++; echotype(); }
		else { if(ebasa[mya].en) fidoselect('fidoarea'); }
		return; 
	} else if(b<(ebasa[mya].len-1)) { b++; ebasa[mya].b=b; begunok(b); msg(ebasa[mya].i+b); }
	else { return fidoselect('fidoarea'); }
	fidoselect('echotags');
}


var knop=" &nbsp; &nbsp; <span class='fidoa' onclick='msg_reply()'>reply</span> &nbsp; \
<span class='fidoa' onclick='msg_new()'>new</span> &nbsp; \
<span class='fidoa' onclick='msg_kludge()'>kludge</span>";

if(admin) knop=knop+" &nbsp; <span class='fidoa' onclick='msg_admindel()'>del</span>";














function msg(i) { if(typeof ebasa[mya][i] == 'undefined') { zabil('fidomsg','&lt;empty&gt;'+knop); return; }

try {
	var a=ebasa[mya][i];

var txt=(fido_kluge?a.b:a.b.replace(/<font color=red>.*?<\/font><br>/g,''));
txt=txt.replace(/  /g,' &nbsp;');

} catch(e) { alert("ero 0"); }




try {
	var s='<div><tt>AREA: '+are[mya]+' &nbsp; &nbsp; '+a.d+' #'+a.id+'</tt>'+knop+'</div>\
<div><tt>FROM: '+a.f+' &nbsp; '+a.fa+'</tt></div>\
<div><tt>TO&nbsp;&nbsp;: '+a.t+' &nbsp; '+(a.ta!='0'?a.ta:'')+'<tt></div>\
<div><tt>SUBJ: '+a.s+'</tt></div>\
<hr><div class="fidobody">'+txt+'</div><hr>';

} catch(e) { alert("ero 1"); }

try {
	zabil('fidomsg',s);

} catch(e) { alert("ero 2"); }


try {
	setHash('area:'+are[mya]+'|id:'+a.id);
//	idd('fidomsg').focus();
	fido_lastid=a.id; fido_lasti=i; ebasa[mya][i].i=i;

} catch(e) { alert("ero 1"); }



try {
	// пометить как прочитаное
	if(fido_point && a.m!=0) {
		majax('module.php',{mod:'FIDO',a:'omsg_read',id:a.id,arean:mya});
		idd('omsg'+i).src=omsg_read;
	} a.m=0;

} catch(e) { alert("ero 4"); }


}

var timeo=0; function timeproc(t,s){ if(t!=timeo) return; eval(s); }

var fidobloki=['fidomsg','fidoarea','echotags'];
var fidoblok;

function fidoselect(id) {
	for(var i in fidobloki) { var x=fidobloki[i];
		if(x==id) { fido_blok=id; idd(x).style.border='3px dotted red'; }
		else { idd(x).style.border='3px dotted transparent'; }
	}
	fidoblok=id;
}

function msg_reply(e) {
if(!fido_lastid) return; majax('module.php',{mod:'FIDO',a:'reply',area:are[mya],id:fido_lastid});
}

function msg_del(e) {
if(!fido_lastid || !fido_point || areasmode) return; majax('module.php',{mod:'FIDO',a:'msg_del',arean:mya,id:fido_lastid});
}

function msg_admindel(e) {
if(!fido_lastid || !confirm('”далить из базы?')) return;
majax('module.php',{mod:'FIDO',a:'msg_admindel',area:are[mya],id:fido_lastid});
}

function msg_new(e) {
if(typeof are[mya] == 'undefined') return salert('Ќе выбрана эха',1000);
majax('module.php',{mod:'FIDO',a:'newmsg',area:are[mya],id:(fido_lastid?fido_lastid:0)});
}

function msg_kludge(e) {
if(!fido_lastid) return; fido_kluge=(fido_kluge?0:1);
msg(fido_lasti);
}

function mykeys() {
setkey('tab','',function(e){ fidoselect(fidoblok=='fidoarea'?'echotags':'fidoarea'); },false);
setkey(['M','№','ь'],'',msg_kludge,true);
setkey(['R',' ','к'],'',msg_reply,true);
setkey(['N','“','т'],'',msg_new,true);
setkey(['left','4'],'',begunok_up,false);
setkey(['right','7'],'',begunok_down,false);
setkey('up','',function(){areamove(-1)},false);
setkey('down','',function(){areamove(1)},false);
setkey('del','',msg_del,false);
}


// function pushid(m) { if(ebasa[mya].filter(function(x){return x.id==m.id;}).length == 0) { ebasa[mya].push(m); ebasa[mya].len++; } }
// function unshiftid(m) { if(ebasa[mya].filter(function(x){return x.id==m.id;}).length == 0) { ebasa[mya].unshift(m); ebasa[mya].len++; } }

function pushid(m) { var a=ebasa[mya],id=m.id; for(var i in a) { if(id==a[i].id) return; } ebasa[mya].push(m); ebasa[mya].len++; }
function unshiftid(m) { var a=ebasa[mya],id=m.id; for(var i in a) { if(id==a[i].id) return; }
ebasa[mya].unshift(m); ebasa[mya].len++;
ebasa[mya].i++;
if(ebasa[mya].st) ebasa[mya].st--;
}

//anchor-навигаци€
function implode(c,arr) { return ((arr instanceof Array)?arr.join(c):arr ); }

var oldhash='';
var functs={};
function setHash(h){ oldhash=h; window.location.hash=h; } //если нужно изменить хеш

//functs['ajax'] = function(param) { param.shift(); return;
//	var mod = param[0]; param.shift();
//	if(param.length) eval("majax('"+mod+".php',{"+implode(',',param)+"})");
//};

functs['area'] = function(param) {
	var ara={mod:'FIDO'};
	for(var i in param) {
		var m=param[i].split(':'); if(m.length!=2) return; // правильность параметров
		if( m[0]!='area' && m[0]!='id' && m[0]!='msgid') return; // допустимые имена
		if( m[1]!=m[1].replace(/[^0-9a-z\.\$]/gi,'') ) return; // допустимые символы значений
		ara[m[0]]=m[1];
	}

	ara['a']='charea';
	if(ara['id']||ara['msgid']) { ara['nomsg']=1; if(getsearch!='') ara['search']=getsearch; }

	if(typeof are != 'undefined') majax('module.php',ara);
	// бл€ть а вот теперь ебатьс€ не переебатьс€...
	var s=[]; for(var i in ara) s.push(i+':\''+ara[i]+'\'');
	majax('module.php',{mod:'FIDO',a:'loadareas',allif:ara['area'],all:1},"majax('module.php',{"+implode(',',s)+",all:areasmode});");
};

function onChangeHash() {
	var hash=window.location.hash.substring(1);
	if(hash!=oldhash) { oldhash=hash; selAction(); }
	setTimeout(onChangeHash,200);
}

function selAction() {
	var param = window.location.hash.split('|');
	var action = param[0].substring(1); param[0]=action;
	var action = action.split(':')[0];
	if(functs[action]) functs[action](param);
}

page_onstart.push('onChangeHash()');


page_onstart.push("hotkey_reset=mykeys; hotkey_reset();"); // переписать хоткеи
page_onstart.push("helper_go=function(){}; helper_napomni=1000;"); // отключить систему правки
page_onstart.push("zabil('fidoarea','<img src='+www_design+'img/ajax.gif>');");
if(window.location.hash=='') {
	page_onstart.push("setTimeout(\"majax('module.php',{mod:'FIDO',a:'toss'})\",50)");
	page_onstart.push("setTimeout(\"majax('module.php',{mod:'FIDO',a:'loadareas'})\",100)");
}