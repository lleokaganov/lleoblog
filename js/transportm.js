// if(window.top !== window) alert('TRpansportm222!');
// if(window.top === window) alert('=TOP: '+mypage); else alert('NE TOP!');
// if(window.self === window) alert('=SELF: '+mypage);
// if(window.top !== window) alert('NE TOP!');

var idrename={};

// --- коды разрешенных операций ---
if(typeof message_func == 'undefined') var message_func={
'#':function(r){if(oldhash==window.location.hash) setHash(' ')},
'%23':function(r){if(oldhash==window.location.hash) setHash(' ')},
'default':function(r){ if(admin && aharu===1) alert(' GOPAL ['+aharu+'] default!'+print_r(r));
 return 1; }, // dier(r,'UNKNOWN')
'ZABIL':function(r){ if(ifx(r)) zabil(r.id,r.text); },

//        if(mnogouser) return sendm('send|idhelp=".$idhelp."|a=addtag|s='+h(s));

// 'addtag':function(r){ alert('ADD-TAG!!!!!!!!!!!'+r.s); },

'send':function(r){ if(window.top===window) sendm(r.a+"|s="+r.s,r.win); },

// 'HELP':function(r){ helps(r.id,r.txt,r.cls); },
'SETKEY':function(r){ try{eval("setkey("+r.s+")")}catch(e){if(admin && aharu===1)alert('setkey error!')}},
'helps':function(r){ helps(r.id,r.s,r.pos,r.cls); },
'helpc':function(r){ helpc(r.id,r.s); },
'ajaxoff':function(r){ ajaxoff(); },
'ohelpc':function(r){ ohelpc(r.id,r.z,r.s); },
'ohelp':function(r){ ohelp(r.id,r.z,r.s); },
'idie':function(r){ idie(r.s,r.t); },
'dier':function(r){ dier(r.s,r.t); },
'majax':function(r){ var a={}; for(var i in r) a[i]=r[i]; delete a.majax; majax(a.url,a); },
'RENAME_ID':function(r){ if(ifx(r) && idd(r.id+'_r')) { idrename[r.newid]=r.id+'_r'; mHelps_sort(r.id+'_r'); } },
'RESIZE':function(r){
    if(r.X||r.Y) { r.w=Math.max(r.w,1*getWinW()*r.X/100); r.h=Math.max(r.h,1*getWinH()*r.Y/100); }
    resize_id(r.id,Math.floor(r.w),Math.floor(r.h),1*r.c,1*r.stage);
},
'RESIZE0':function(r){resize_id(r.id,1,1,0)},
'RESIZENEED':function(r){ if(window.top!=window) resize_me(1,0,0,1); },
// 'CLEAN_NAME':function(r){if(ifx(r)) alert('clean:'+r.id) /*clean(r.id)*/ },
'CLEAN':function(r){if(ifx(r)){
	if(typeof(idrename[r.id])!='undefined') { clean('tenek'); clean(idrename[r.id]); clean(r.id); return; }
	if(idd(r.id)) return clean(r.id);
	clean(r.id);
}},
'CLOSE':function(r){
// clean(r.id);
// idd(r.id).style.border="20px dotted green"; alert("close ("+IMBLOAD_MYID+"): `"+r.id+"`");
clean(r.id);
},

'SALERT':function(r){salert(r.s,1*r.t)},
'setunc':function(r){ if(window.top!==window.self||!mnogouser)return;
		if(typeof(r.ux)!='undefined') { ux=r.ux; c_save(ux_name,ux,1); }
		uname=r.uname; realname=uname; zabilc('uname',uname); zabilc('myunic',uname);
	        salert("Login: "+uname,1000);
		// ifhelpc(xdom+'&upx='+r.upx,'xdomain','xdomain'); Ќ≈Ћ№«я ѕјЋ»“№ upx в опасный домен!
	        doclass('del_onlogon',function(e,s){clean(e)});
	        setTimeout("clean('work');clean('logz');clean('loginopenid');clean('loginobr_unic11');clean('userinfo');",1000);
		// alert('ux='+ux+'\n\nuname='+uname+'\n\nuxname='+ux_name);
	},
'WIN':function(r){ohelpc(h(r.id),h(r.head),h(r.text));setHash(' ');return 1;}
};


var lastmesd=''; function messageDaemon(e){
	var lmd=e.origin+':'+e.data;

//	alert((window.top===window?'TOP':'NE TOP: '+IMBLOAD_MYID)+"\n---\n"+lmd);

	if(lmd==lastmesd) return; { lastmesd=lmd; setTimeout("lastmesd='';",50); } // заебало 2 раза срабатывать
	doMessage(e.data,e.origin);
//	var em=e,tm=setTimeout(function(){clearTimeout(tm);doMessage(em.data,em.origin);},40);
} // слушалка message (e.origin - сайты)

if(ppmes()) { // если транспорт postMessage в браузере есть
	if(window.addEventListener) window.addEventListener('message',messageDaemon,false);
	else window.attachEvent('onmessage',messageDaemon);
} else { // если транспорта нет - работаем через hash
	var hashtime=0,hashtime_step=51,hashtime_max=1000;
	var oldhash=window.location.hash; // слушалка hash
	function hashDaemon(){ if(window.location.hash!=oldhash){ oldhash=window.location.hash;
		if(doMessage(oldhash.substring(1))!=7) hashtime=0; // если что-то произошло, ускоритьс€
		}
		if(hashtime<hashtime_max) hashtime+=hashtime_step;
		setTimeout(hashDaemon,hashtime);
	} setTimeout(hashDaemon,hashtime);
}

// --- а теперь сам обработчик событи€

function doMessage(s,origin) { if(typeof(s)!='string') return;
    s=s.replace(/\#$/gi,''); if(s==''||s==' '||s=='%20') return 7;

// alert("получил: "+IMBLOAD_MYID+'\norigin: '+origin+'\n\n'+s);
//	lovilka=lovilka+"# transport 98 doMessage: typeof(s)='"+typeof(s)+"'";

/*
if(typeof(s)=='object') dier(s);
try{ if(typeof(s)=='object') lovilka=lovilka+"# transport 98 print_r(Object)='"+print_r(s)+"'";
}catch(e) { lovilka=lovilka+"# transport 98 print_r(Object)=error"; }
*/
        var m=s.split(';'); if(m.length<2) m=s.split('|'); // как кому нравицо

		// стара€ система команд
		if(window.top === window.self) { // если € главное окно
			if(m[0]=='WW') idd(m[1]).style.width=(1*m[2]+15)+'px';
			if(m[0]=='HH') idd(m[1]).style.height=(1*m[2]+15)+'px';
			if(m[0]=='NO') clean(m[1]);
		}

	var r={}; for(var i in m) {
		var c='=',k=m[i].split(c); if(k.length<2){ c=':'; k=m[i].split(c); } // как кому нравицо
		if(k.length<2) r[k.shift()]='function'; else r[uhl(k.shift())]=uhl(k.join(c)); //.replace(/#%tZ#/g,';').replace(/#%rZ#/g,'|'));
	}

//	if(window.top !== window.self && (typeof r.MYID == 'undefined' || r.addr != IMBLOAD_MYID)) return; // не мое дело

//	dier(r,'мое дело!<br>'+mypage);
//	if(window.top !== window.self) return;
//	alert(s+'\n'+mypage); return;

	var k=0; for(var i in r) { if(r[i]=='function'&& message_func[i]) { k++; r.origin=origin; try{var rp=message_func[i](r)}catch(e){message_func['default'](e)} if(1===rp) return; } }
	if(!k) try{var rp=message_func['default'](r)}catch(e){}
	if(!ppmes()) setHash(' ');
	return;
}

//------- протоколы передачи -------
// postMessage ? это нова€ возможность стандарта HTML5, позвол€ет отсылать сообщени€ из одного окна в другое,
// при этом контент окон может быть с разных доменов. ѕримерна€ реализаци€
// targetWindow.postMessage(message,targetOrigin);
// targetWindow - окно куда шлЄм запроc
// message - сообщение
// targetOrigin - допускаетс€ указани€ '*', при этом домен может быть любой.

// window.top.postMessage('NO|'+IMBLOAD_MYID,'http://'+IMBLOAD_TOP);
// if(window.top !== window.self) { var r=window.location.hash.split('|');
// var IMBLOAD_ACT=r[0]; var IMBLOAD_TOP=r[1]; var IMBLOAD_MYID=r[2];
// if(IMBLOAD_ACT=='#IMBLOAD')
//	window.top.postMessage('HH|'+IMBLOAD_MYID+'|'+getDocH(),'http://'+IMBLOAD_TOP);
//	setTimeout("window.top.postMessage('HH|'+IMBLOAD_MYID+'|'+getDocH(),'http://'+IMBLOAD_TOP)",10000);
// page_onstart.push('raport_imbload()');

// --- функции операций ---
function resize_id(id,w,h,c,stage){ if(!id) return; w=1*w;h=1*h;
	var e=document.getElementById(id); if(typeof(e)=='undefined') return;
	// e.style.border='11px solid red'; alert('resize: '+id+' '+w+'x'+h+' c:'+c+' stage: '+1*stage+' pos:'+e.style.position);
	if(w) e.style.width=w+'px';
	var cid=idd(id+'_r')||idd(id.split('_')[0])||idd(id);
// alert(c);
	if(1*stage!==1) { if(c) posdiv(cid,0,0); sendm("RESIZENEED;#",id); } else { if(h) e.style.height=h+'px'; }
	if(c && e.style.position=='absolute') posdiv(cid,-1,-1);
//	if(c) { 
// posdiv(cid,0,0); setTimeout(function(){posdiv(cid,-1,-1)},10);
// }
}

function ifx(r){ return window.top===window && r.origin==xdomain?1:0; }

function setHash(h){ oldhash=h; window.location.hash=h; } //если нужно изменить хеш

function sendm(s,w){
    if(typeof(w)=='undefined') w=window.top; else if(typeof(w)=='string') { w=idd(w); if(w.tagName=='IFRAME') w=w.contentWindow; }
    if(ppmes()) return w.postMessage(s,'*'); // транспорт есть
    try{ if(w.location.hash.replace(/([\s\#]|\%20|\%34)+/g,'')=='') w.location.hash=s; else setTimeout("sendm(\""+s+"\")",500); }catch(e){ w.location.hash=s; }
}

function resize_me0(){ sendm("RESIZE0;id="+IMBLOAD_MYID+";#"); }
function resize_me(c,X,Y,stage){ // X,Y - минимальна€ величина в процентах общего окна
// alert('c='+c);
    // alert('Resizes resize_me: '+c+' '+X+' '+Y);
    // alert('res:'+getDocW()+"=="+getWinW()+"\n"+getDocH()+"=="+getWinH());
// alert("wh="+getDocW()+"x"+getDocH()+"\n"+getWinW()+"x"+getWinH());
    sendm("RESIZE;w="+getDocW()+";h="+getDocH()+";id="+IMBLOAD_MYID+";c="+(c?1:0)+(stage?';stage=1':'')+(X?';X='+X:'')+(Y?';Y='+Y:'')+"#");

//    setTimeout(function(){sendm("RESIZE;w="+getDocW()+";h=0;id="+IMBLOAD_MYID+";c="+(c?1:0));},10);
//    setTimeout(function(){sendm("RESIZE;w=0;h="+getDocH()+";id="+IMBLOAD_MYID+";c="+(c?1:0));},200);
    // sendm("RESIZE;w="+getDocW()+";id="+IMBLOAD_MYID+";c="+(c?1:0)+(X?';X='+X:'')+(Y?';Y='+Y:'')+"#");
    // setTimeout(function(){sendm("RESIZE;h="+getDocH()+";id="+IMBLOAD_MYID+";c="+(c?1:0)+(X?';X='+X:'')+(Y?';Y='+Y:'')+"#");},50);
}

function ppmes(){ return typeof(postMessage)=='function'?1:0; }

function hl(s){ if(s==undefined)return ''; return (''+s).replace(/\;/g,'@1@').replace(/\|/g,'@2@').replace(/\=/g,'@3@').replace(/\:/g,'@4@').replace(/\'/g,'@5@').replace(/\"/g,'@6@'); }
function uhl(s){ return (''+s).replace(/@1@/g,';').replace(/@2@/g,'|').replace(/@3@/g,'=').replace(/@4@/g,':').replace(/@5@/g,'\'').replace(/@6@/g,'\"'); }

// --- функции операций ---

//setTimeout("sendm('WIN;head=1;text=2;id=monti')",2000);
