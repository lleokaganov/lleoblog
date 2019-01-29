loadCSS('openid.css');

var logintext='';
var lastpolename;
var lastpolevalue;
var polesend_go=0;

function login_validate(p,n) { var l=p.value;

	var e=l.replace(/http:/gi,''); if(e!=l) {
		zabil('openidotvet','<div class=e>без http://, пожалуйста</div>'); polese(p); return e;
	}

	var e=l.replace(/[^0-9a-z\-\_\.\/\~\=\@]/gi,''); if(e!=l) {
		zabil('openidotvet','<div class=e>—имволы и русские буквы нельз€!</div>'); polese(p); return e;
        }

	if(n) {
		var e=l.replace(/[^0-9a-z\-\_]/gi,''); if(e!=l) {
			zabil('openidotvet','<div class=o>логинимс€ по openid</div>'); zakryl('openidpass'); polese(p); return l;
	        }

        	otkryl('openidpass'); logintext=l;
	}

	polese(p); return l;
}


function polese(p) { lastpolename=p.name; lastpolevalue=p.value; }

function mail_validate(p) { polese(p); var l=p.value; return l; }
function site_validate(p) { polese(p); var l=p.value; return l; }
function realname_validate(p) { polese(p); var l=p.value; return l; }

function setbirth(y,m,d) { var e=idd('birth'); e.value=y.value+'-'+m.value+'-'+d.value; polesend(e); }

function login_go(mylog,mypas) {
        zabil('openidotvet','<div class=o>идет соединение</div>');
	majax('login.php',{ 'action': 'openid_logpas', 'rpage': mypage, 'mylog': mylog, 'mypas': mypas });
	return false;
}

function openid_go(mylog) {
	if(mylog.replace(/\./g,'')==mylog) zabil('openidotvet','<div class=e>разве ж это openid?</div>');
	else { zabil('openidotvet','<div class=o>идет соединение</div>');
	majax('login.php',{ 'action': 'openid_logpas', 'rpage': mypage, 'mylog': mylog });
	}
	return false;
}

function polesend(p) { polesend_go=1; setTimeout("polesend_go=0;", 500); return polesend0(p.name,p.value); }

function polesend_all() { setTimeout("polesend_all_time();", 100); return false; }

function polesend_all_time() { if(polesend_go) return;
//	alert('\npolesend_go: '+polesend_go + '\nlastp: '+lastp); //+'\nlastpolevalue: '+lastpolevalue);
//	alert('\npolesend_go: '+polesend_go + '\nlastp: '+lastp); //+'\nlastpolevalue: '+lastpolevalue);
	if(lastpolename=='openid') return openid_go(lastpolevalue);
	return polesend0(lastpolename,lastpolevalue);
}

function polesend0(name,value) {
	zabil('openidotvet','<div class=o>'+name+': '+value+'</div>');
	majax('login.php',{'action': 'polesend', 'name': name, 'value': value});
	return false;
}

// ============================  последн€€ строка скрипта должна быть всегда такой: ========================
var src='openid_editform.js'; ajaxoff(); var r=JSload[src]; JSload[src]='load'; if(r && r!='load') eval(r);