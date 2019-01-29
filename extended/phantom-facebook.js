var page=require('webpage').create(); // подключили браузер
// page.settings.userAgent = "Mozilla/5.0 (Windows NT 6.1; rv:6.0) Gecko/20100101 Firefox/6.0";
// "Opera/9.80 (J2ME/MIDP; Opera Mini/6.5.26955/27.1407; U; en) Presto/2.8.119 Version/11.10";
page.settings.userAgent = "Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:51.0) Gecko/20100101 Firefox/51.0";
page.viewportSize = {width: 1024,height: 600};
var fs=require('fs'); // подключили файловую систему
var system=require('system');
var args=system.args;
//--------------------------------------------
// phantom.injectJs('./FB.js');
// phantom.injectJs('./tricks.js');

pageopen=function(url){ llog("page.open: "+url); page.open(url); };

phantom.onError = function(msg, trace) {
  var msgStack = ['PHANTOM ERROR: ' + msg];
  if (trace && trace.length) {
    msgStack.push('TRACE:');
    trace.forEach(function(t) {
      msgStack.push(' -> ' + (t.file || t.sourceURL) + ': ' + t.line + (t.function ? ' (in function ' + t.function +')' : ''));
    });
  }
    err(msgStack.join('\n'));
  // console.error(msgStack.join('\n')); phantomexit(1);
};

// то, что нам поможет

llog=function(s,deb){ if(deb==undefined) deb=DEBUG; if(deb) { console.log(s);

    if(LOGFILE) { fs.write(LOGFILE,LOGFILE_s+s+"\n",'a+'); LOGFILE_s=''; }
    else LOGFILE_s+=s+"\n";

 } };
err=function(s){ llog("ERROR: "+s,1); phantomexit(1); }
bye=function(s){ saveshot('bye'); llog("DONE: "+s,1); phantomexit(0); }
phantomexit=function(i){ // setTimeout(function(){phantom.exit();},0);
phantom.exit(i);
}

var LOGFILE=false;
var LOGFILE_s='';
var STAGE='go';
var SHOT=0; // номер скриншота
saveshot=function(s){ if(!DEBUG) return;
	// llog(page.url);
	var i=''+(SHOT++); while(i.length<3) i='0'+i;
	if(!FB.SHOTDIR) err("SHOTdir not set!");
	var file=FB.SHOTDIR+'/'+i+"_STAGE_"+STAGE;
	if(s) file+='_'+s;
        page.render(file+".png");
        fs.write(file+".htm",page.url+"\n\n"+page.content,'w');
};


var WF=60; // сколько циклов делать
var WS=1000; // по сколько милисекунд
// var WFS='';
var WSlast='',WScountlast=0;
var WFF=function(){llog('FUNC');};
waitfo=function(s,f) { WFS=s; WFF=f; setTimeout(function(){waitf(1)},WS); }
waitf=function(n) { if(!n) n=1;
    if(DEBUG) { llog('Wait #'+n+'/'+WF); saveshot('waitfor_'+n); }
    // не дождались
    if(++n>WF) err('timeout');

    var l=page.url+"\n\n"+page.content;
    if(l!=WSlast) { WSlast=l; WScountlast=0; } // сборосить счетчик
    else { if(++WScountlast > 10) err('nochange-timeout'); } // если страница не менялась 10 секунд - тоже таймаут

    setTimeout(function(){waitf(n)},WS);
}; waitfo('chegosego');


tam=function(s){ return page.evaluateJavaScript("function(){ return document.querySelector(\""+s+"\")==null?0:1; }"); };
h=function(s){ return s.replace(/\&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\'/g,'&#039;').replace(/\"/g,'&#034;').replace(/\n/g,'\\n'); };  // '
hn=function(s){ return s.replace(/\n/g,'\\n').replace(/"/g,'\\"'); } //'
// function uh(s){ return s.replace(/\\n/g,'\n')replace(/\&lt\;/g,'<').replace(/\&gt\;/g,'>').replace(/\&\#039\;'/g,"'").replace(/\&\#034\;"/g,'"').replace(/\&amp\;/g,'&'); }

var p={
    value: function(x,s){
	if(!tam(x)) err("p.value: not found: "+x);
	var e="function(){ var e=document.querySelector(\""+x+"\"); if(e==null) return -1; e.value=\""+hn(s)+"\"; return 1; }";
	if(-1==page.evaluateJavaScript(e)) err("Error -1 ("+x+","+hn(s)+")");
    },

    ischecked: function(x){ return page.evaluateJavaScript("function(){ var e=document.querySelector(\""+x+"\"); if(e==null) return -1; return e.checked; }"); },

    checked: function(x,s){
	var e="function(){ var e=document.querySelector(\""+x+"\"); if(e==null) return -1; e.checked="+hn(s)+"; }";
	if(-1==page.evaluateJavaScript(e)) err("Error p.checked -1 ("+x+","+hn(s)+")");
    },

    click: function(x){
	if(!tam(x)) err("p.click: not found: "+x);
	var e="function(){ var e=document.querySelector(\""+x+"\"); if(e==null) return -1; "
// +"var event=new Event('click'); e.dispatchEvent(event);"
+"if(!e.click){ return 'click:'+e.click+' tag:'+e.tagName+' id:'+e.id+' class:'+e.className+' innerHTML:'+e.innerHTML; } e.click(); return 1; /*'!!! e.click='+e.click;*/ }";
//	saveshot('do-click');
	e=page.evaluateJavaScript(e);
//	saveshot('posle-click');
	// if(-1==e) err("Error -1 ("+x+")");
	return e;
    }
};

var ADDTIME=0;

function waitFor(testFx, onReady, timeOutMillis) {
    var maxtimeOutMillis = timeOutMillis ? timeOutMillis : 6000,
        start = new Date().getTime(),
        condition = false,
        interval = setInterval(function() {
            if( (new Date().getTime() - start < (maxtimeOutMillis+ADDTIME)) && !condition ) condition = (typeof(testFx) === "string" ? eval(testFx) : testFx());
            else {
                if(!condition) err("'waitFor:' timeout");
                else { typeof(onReady) === "string" ? eval(onReady) : onReady(); clearInterval(interval); }
            }
        }, 250);
};


//--------------------------------------------
var DEBUG=1;



tamc=function(tag,href,s,act){ return page.evaluateJavaScript("function(){ var e,o=[],r=document.getElementsByTagName('"+tag+"'); if(r){ for(var i=0;i<r.length;i++){"
+"var ga=r[i].getAttribute('"+href+"');if(ga && -1!=ga.indexOf(\""+s+"\"))"
// +"if(r[i]['"+href+"'] && -1!=r[i]['"+href+"'].indexOf(\""+s+"\"))"
+"{e=r[i];"
+(act=='click'?"e.click(); return 1;"
:act=='submit'?"e.querySelector(\"input[type='submit'],button[type='submit']\").click(); return 1;"
:act=='get'?"return e['"+href+"'];"
:act=='getall'?"o.push(e['"+href+"']);"
:act=='getallinner'?"o.push([e['"+href+"'],e.innerHTML]);"
:"return 1;"
)+"} }} return (o.length?o:0); }");};

var FB={

PRIVACY: "Public", // "Friends"
// EMAIL:false,
// PASS:false,
// ACTION:false,
// ID:false,
// MESSAGE:false,
// SAVETO: false,
SHOTDIR:false,
IMGS:[],
IMGcount:0,
// DOMAIN:false,
TRYLOGIN:0,

login:function(){ llog('Login...'); STAGE='login';
	p.value("input[name='email']",FB.EMAIL);
	p.value("input[name='pass']",FB.PASS);
	p.click("input[name='login']");
},


GROUPLEAVE: function(){ llog('Leave group: "'+FB.GROUPNAME+'"'); STAGE='GroupLeave';
    pageopen("https://www.facebook.com/groups/?category=manage");
},


GROUPPOST: function(){ llog('Del group: "'+FB.GROUPNAME+'"');
    FB.PRIVACY=false; pageopen(FB.GROUPLINK);
	waitFor(
	    function(){ return tam("input[name='login'],input[name='view_post']"); },
	    function(){ if(tam("input[name='login']")) return; STAGE='postGROUP'; FB.POST(); },6000
	);
},


GROUPLIST: function(){ llog('Get groups list...'); pageopen("https://m.facebook.com/groups/?seemore");
	waitFor(
	    function(){ return tamc('a','href','/groups/','getall');  },
	    function(){
		var p=tamc('a','href','/groups/','getallinner');
		var o='',k=0; for(var i in p) { if(p[i][0]==p[i][0].replace(/\/groups\/\d+\?/g,'')) continue;
		    llog("GROUP_FINDED: "+p[i][0]+' | '+p[i][1]);
		    o+=p[i][0]+'|'+p[i][1]+"\n";
		    k++;
		}
		if(FB.FILEGROUPLIST) { fs.write(FB.FILEGROUPLIST,o,'w'); bye("Group list: "+FB.FILEGROUPLIST); }
		bye("GROUPS: "+k);
	    }
	);
},


EDIT:function(){ llog('Edit ['+FB.ID+']...');
    if(!FB.MESSAGE.length) err('Message empty or not found');
    if(!FB.ID.length) err('ID not defined');
    if(!FB.DOMAIN) err('Domain not defined');

    STAGE='OpenPostForEdit';
    pageopen("https://m.facebook.com/"+FB.DOMAIN+"/posts/"+FB.ID); // ждать
    waitFor(
	function(){ return tamc('a','href','/edit/post/dialog/?') || -1==page.content.indexOf("<form"); },
	function(){
	    if(-1==page.content.indexOf("<form")) err('deleted or not found');
	    var url=tamc('a','href','/edit/post/dialog/?','get'); if(!url) err('Error url');
	    STAGE='edit'; pageopen(url);
	    waitFor(
		function(){ return tamc('form','action','/edit/post/write/'); },
		function(){ STAGE='EditSubmit'; p.value("textarea",FB.MESSAGE); tamc('form','action','/edit/post/write/','submit'); },6000
	    );
	},6000
    );
},


DEL:function(){ llog('Deleting ['+FB.ID+']...');
    if(!FB.ID.length) err('ID not defined');
    if(!FB.DOMAIN) err('Domain not defined');
    STAGE='OpenPostForDel';
    if(-1==page.url.indexOf('facebook.com')) { // не открыт фейсбук пока
	pageopen("https://m.facebook.com/"+FB.DOMAIN+"/posts/"+FB.ID); // ждать
    }
    waitFor(
	function(){ return tamc('a','href','/delete.php?') || -1==page.content.indexOf("<form"); },
	function(){
	    var url=tamc('a','href','/login.php?','get'); if(url) {
		ADDTIME=10000; // добавим еще время на логин
		STAGE='login'; return pageopen(url); // return FB.login();
	    }
	    if(-1==page.content.indexOf("<form")) bye('Already Deleted: id='+FB.ID);
	    var url=tamc('a','href','/delete.php?','get'); if(!url) err('Error url');
	    STAGE='delete'; pageopen(url);
	}
    ,6000
    );
},


TAKE: function(){ llog('Take...');
    STAGE='TAKE';
    pageopen("https://m.facebook.com/home.php");
},


/*
findmyid: function(){ return page.evaluateJavaScript("function(){"
+"var e=document.querySelector(\"div[id='u_0_1']\"); if(!e) return 0; e=e.getAttribute('data-ft'); if(!e) return 0;"
+"e=e.replace(/^\{\&quot;top_level_post_id\&quot;\:\&quot;(\d+)\&quot;\}$/g,'$1');"
+"if(e.replace(/\d+/g,'')=='') return e; return 0;}");},
*/

POST:function(){ llog('Posting...');
    if(!FB.MESSAGE.length) err('Message empty or not found');
    if(!FB.DOMAIN) err('Domain not defined');

    if(-1==page.url.indexOf('facebook.com')) { // не открыт фейсбук пока
        FB.IMGScount=0; pageopen("https://m.facebook.com/home.php");
	waitFor(
	    function(){ return tam("input[name='login'],input[name='view_post']"); },
	    function(){ if(tam("input[name='login']")) { llog("Login need"); return; } STAGE='postFB'; FB.POST(); },6000
	);
	return;
    }

    if(FB.IMGScount<FB.IMGS.length) { STAGE='foto1'; return p.click("input[name='view_photo']"); } // добавить фото

    STAGE="DoPOST";
    p.value("textarea[name='xc_message']",FB.MESSAGE);
    if(FB.PRIVACY) p.value("input[name='view_privacy']",FB.PRIVACY);
    llog("view_post.click()");
    p.click("input[name='view_post']");

/*
Это про старое:
<div role="article" class="bj bl ed ee" data-ft="{&quot;qid&quot;:&quot;
6390960591697558789&quot;,&quot;mf_story_key&quot;:&quot;-1609720236908008325&quot;,&quot;top_level_post_id&quot;:&quot;111473426045369&quot;}" id="u_0_1">

Это про новое:
<div role="article" class="bm bo dz ea" data-ft="{&quot;top_level_post_id&quot;:&quot;111474309378614&quot;}" id="u_0_1">

                                                 {"qid":"6390969100091926140","mf_story_key":"3692443094333864273","top_level_post_id":"111509199375125"}


Это про новое с фотками:


    waitFor(
	function(){
	    return FB.findmyid()
//    return tam('div',id)
// return -1==page.content.indexOf('<abbr>Just now</abbr>')||-1==page.content.indexOf('<abbr>Только что</abbr>')?0:1;

}, // пока не появится новый пост
	function(){
	    if(!FB.PRIVACY) bye("Наверно отправлено");


//		var l=page.content.split('<abbr>Just now</abbr>')[1]; l=l.split('<a ')[1]; l=l.split('</a>')[0];
//	    if(-1!=l.indexOf('postid%3D')) l=l.split('postid%3D')[1].split('%')[0];
//	    else if(-1!=l.indexOf('&amp;ci=')) l=l.split('&amp;ci=')[1].split('&')[0];
//	    else err("Error link!");

	    var l=FB.findmyid(); if(!l) err("no id");
	    bye("posted w/o fotos: https://www.facebook.com/"+FB.DOMAIN+"/posts/"+l);
	},
	30000
    );
*/
},

GO:function(func){ STAGE='start'; if(!FB.ACTION.length) err('ACTION not found'); if(FB[FB.ACTION]) FB[FB.ACTION](); else err('Unknown action: '+FB.ACTION); }

};

//---------------------------------------------
page.onLoadFinished=function(status){
    if(STAGE=='login'&&status=='fail') return;
    if(STAGE=='delete'&&status=='fail') return;

    llog('OnLoad: '+STAGE+' status: '+status+" ("+page.url+")"); saveshot('ONLOAD');

    if(-1!=page.url.indexOf('&login_try_number=') && 1*page.url.split('&login_try_number=')[1].split('&')[0] >1) err("loop login!");

    if(STAGE=='login'&&status=='success') { if(FB.TRYLOGIN++) err("wrong login!");
	if(tam("a[href='/r.php']")) err("login account");
	if(tam("a[href='/recover/initiate/']")) err("login password");
	// if(tam("div.v")) err("login v");
	if(tam("input.bl")) err("login bl");
	llog("Login: OK"); STAGE='loginDone'; return FB.GO();
    }

    if(status=='success' && STAGE=='TAKE') bye("TAKE");

    if(STAGE=='GroupLeaveOK' && status=='success') { bye("Group leave - DONE!"); }
    if(STAGE=='GroupLeave' && status=='success') {
	var a=FB.GROUPLINK.replace(/^.*?\/groups\/(\d+).*?$/g,"$1");
	llog('GroupLeave:success: `'+a+'`');
	var e=page.evaluateJavaScript("function(){ var o,e,r=document.getElementsByTagName('a'); if(r){ for(var i=0;i<r.length;i++){ if("
		    +"-1!=r[i].href.indexOf(\"/groups/"+a+"\")"
		    +"|| (r[i].getAttribute('data-hovercard') && -1!=r[i].getAttribute('data-hovercard').indexOf(\"id="+a+"\") )"
	    +") {e=r[i];"
	    // ищем верхний LI
	    +"var stop=20; while(--stop && e.tagName!='LI') e=e.parentNode; if(!stop) return o;"
	    // просматриваем все <a>
	    +"r=e.getElementsByTagName('a'); if(r){ for(var i=0;i<r.length;i++){ o+=' '+i+':'+r[i].href; if(r[i].href=='"+page.url+"#') { o+=' @@@ '; r[i].click(); } }}"
	    +"} }} return o; }")
        if(e==null) bye('Group leaved already?');

    waitFor(
	function(){ return tamc('a','ajaxify','/ajax/groups/membership/leave.php?') },
	function(){
		tamc('a','ajaxify','/ajax/groups/membership/leave.php?','click'); llog('Click leave');
		waitFor(
		    function(){
	    return page.evaluateJavaScript("function(){ var r=document.getElementsByTagName('form');"
		+"if(r)for(var i=0;i<r.length;i++){  if(-1!=r[i].action.indexOf('/ajax/groups/membership/leave.php')) {"
		+"return r[i].parentNode.style.opacity==1?1:0;"
		+"} } return 0; }");
		    },
		    function(){
			p.click("input[name='prevent_readd']");
			tamc('form','action','/ajax/groups/membership/leave.php?','submit');
			return STAGE='GroupLeaveOK';
		    },6000
		);
	},10000
    );
    }

    if(status=='success' && tam("input[name='login']")) return FB.login();

    if(STAGE=='delete' && status=='success') {
	    if(tamc('form','action','/delete.php?','submit')) return;
	    bye("Delete success: id="+FB.ID);
    }

    if(STAGE=='EditSubmit' && status=='success') {  bye("Edited success: id="+FB.ID);  }

    if(STAGE=='foto1') { llog("Foto1: OK");
	if(status=='fail') { return; /*STAGE='start'; return FB.GO();*/ }
	var img=FB.IMGS[FB.IMGScount++]; // >=IMGS.length)
	STAGE='fotonext'; page.uploadFile("input[type='file']",img);
	p.click("input[name='add_photo_done']");
	return;
    }

    if(STAGE=='fotonext') { llog("Fotonext: OK");
	if(FB.IMGScount>=FB.IMGS.length) { STAGE='postFBfoto'; return FB.POST(); }
	STAGE='foto1'; return setTimeout(function(){p.click("input[type='image']");},500);
    }

    if(STAGE=='postGROUP' && status=='success') { if(FB.GROUPRETURN) bye("posted?");
	if(-1!==page.content.indexOf('<abbr>Just now</abbr>')||-1!==page.content.indexOf('<abbr>Только что</abbr>')) bye("Posted in GROUP");
	err("ne tak post");
    }

// DoPOST status: success (https://m.facebook.com/home.php?s=100015481354390&sstr=111531606039551&stype=s&postid=111531606039551&gfid=AQAxGK8QpZVYJ2_2&_rdr)
    if(STAGE=='postFBfoto'||STAGE=="DoPOST") { llog("PostFotopost: OK"); // STAGE='postend';
	    var idis=['&postid=','status_fbid=','status_id=','&id=','?photo_id=','%3Fphoto_fbid%3D/%26']; for(var i in idis) { var c=idis[i],end='&';
	    // php?s=100015481354390&sstr=115957495596962&stype=s&postid=115957495596962&g
	    // or/?return_uri=%2Fwritephototag.php%3Fphoto_fbid%3D111757232683655%26phot
		if(-1!=c.indexOf('/')) { c=c.split('/'); end=c[1];c=c[0]; } // игра с разделителями
		if(-1==page.url.indexOf(c)) continue;
		var l=page.url.split(idis[i])[1].split(end)[0];
		if(!FB.DOMAIN) err('Domain not defined');
		if(FB.SAVETO) fs.write(FB.SAVETO,"https://m.facebook.com/"+FB.DOMAIN+"/posts/"+l+"\n",'a+');
		bye("Posted success: https://m.facebook.com/"+FB.DOMAIN+"/posts/"+l);
	    }
//	llog("Post click()");
	if(tam("input[name='done']")) { llog("(name=done).click"); p.click("input[name='done']"); }
	else if(tam("a[href *='stype=phss']")) { llog("(stype=phss).click"); return p.click("a[href *='stype=phss']"); }
	else if(FB.GROUPRETURN && status=='success') bye("posted?");
    }

// ========================= следилка за говносообщениями

    var i=page.evaluate(function(){ var e=document.querySelector("div[id='m_home_notice']"); if(e==null) return 0;
	    var o='',sp=e.getElementsByTagName('span'); if(sp.length){for(var i=0;i<sp.length;i++) o+=sp[i].innerHTML+"\n";} else o=e.innerHTML;
	    return o;
    }); if(i) err("WARNING: "+i); // <div class="bi bj j bk" id="m_home_notice">




};


// START FB
c0=function(s) { return s.replace(/^[\'\"\s]+(.*?)[\'\"\s]+$/g,"$1") }; // '

llog("PHANTOM: "+args.join(' '));

// err('use');

if(args.length===1) err('USE: POST|DEL|EDIT email="my@login.email" pass="mypassword" FILE1 FILE2 ...');
for(var i=1;i<args.length;i++) { var a=args[i];

	if(-1!=a.indexOf('=')) { var l=a.split('='); var b=c0(a.substring(l[0].length+1)); a=c0(l[0]);
	    if(b=='-') b=system.stdin.readLine();

	    if(a=='debug') { llog("debug: "+b); DEBUG=b; continue; }
	    if(a=='saveto') { llog("save success to file: "+b); FB.SAVETO=b; continue; }
	    if(a=='domain') { llog("domain: "+b); FB.DOMAIN=b; continue; }
	    if(a=='shotdir') { llog("shotdir: "+b); FB.SHOTDIR=b; continue; }
	    if(a=='logfile') { llog("logfile: "+b); LOGFILE=b; continue; }

	    if(a=='email') { llog("login: "+b); FB.EMAIL=b; if(!FB.DOMAIN && -1==b.indexOf('@')) { llog("domain: "+b); FB.DOMAIN=b; } continue; }
	    if(a=='pass') { llog("password: *****"); FB.PASS=b; continue; }
	    if(a=='id') { llog("id: "+b); FB.ID=b; continue; }

	    if(a=='group') {  l=b.split('|'); FB.GROUPLINK=l[0]; FB.GROUPNAME=l[1]; llog("grouplink: "+FB.GROUPLINK+"\ngroupname: "+FB.GROUPNAME); continue; }
	    if(a=='groupfile') { llog("groupfile: "+b); FB.FILEGROUPLIST=b; continue; }
	    if(a=='groupreturn') { llog("groupreturn: "+b); FB.GROUPRETURN=b; continue; }
	    if(a=='grouplink') { FB.GROUPLINK=b; llog("grouplink: "+FB.GROUPLINK); continue; }
	    if(a=='groupname') { FB.GROUPNAME=b; llog("groupname: "+FB.GROUPNAME); continue; }

	    err("ARG: error argument: "+a+" = "+b);
	}

	if(FB[a]) { llog("action: "+a); FB.ACTION=a; continue; }

	// FILE
	if(a!='-' && !fs.isFile(a)) err('Not file: '+a);
	if(a=='-'||-1!=a.indexOf('.txt')) { llog("message: "+a); if(a=='-') FB.MESSAGE=system.stdin.readLine().replace(/\\n/g,"\n"); else FB.MESSAGE=fs.read(a); 
llog("ZAMETKA: "+FB.MESSAGE);
continue; }
	llog("image: "+a); FB.IMGS.push(a); continue;

}


llog(''); FB.GO();
