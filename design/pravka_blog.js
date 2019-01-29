// процедура правки v2.0
//
// (с)LLeo 2009 для проекта блогодвижка http://lleo.aha.ru/blog/
//
// за бесценные советы, дизайн вспывающего окошка и процедуры работы с выделением - спасибо Михаилу Валенцеву http://valentsev.ru
//
// забудьте подсоединять процедуру pins.js - она давно в main.js


// var aaa='bodyz_123'; alert( aaa + ' = ' + aaa.indexOf('brodyz_') );

var opecha;
var opechanew;
var opecha_id;
var opecha_id_go;
var leftHelper;
var topHelper;
var site_id;
var textarea_cols=40;
var Nx = 630;
var helper_napomni=2;

var editoshibka=0;

window.onload = function() {

//message = document.getElementById('message');
//helperItem = idd('helper');

screenWidth = document.body.clientWidth; window.onresize = function() { screenWidth = document.body.clientWidth; }

// === MOUSE ===

document.onmouseup = function(e) { if(!e) e = window.event;
	opecha=(document.selection) ? document.selection.createRange().text : window.getSelection(); opecha += '';

  // 1 - Браузеры. 2 - IE. 3 - Неизвестно.
// var testRange = (document.createRange) ? 1 : (idd('message') && idd('message').createTextRange && idd('message').createTextRange() != undefined) ? 2 : 3;
var testRange = (document.createRange) ? 1 : (-[1,]) ? 3 : 2;

        switch(testRange) { // Браузеры
            case 1: if(window.getSelection().anchorNode) testSelection = window.getSelection().anchorNode; break;
            case 2: var testSelection = document.selection.createRange().parentElement(); break; // IE
	    }
        if(testRange != 3 && testSelection && opecha!='') {           // Поиск автора выделенного текста.
            while( ( testSelection.tagName != 'DIV' || testSelection.id == '' || testSelection.id == undefined ) 
			&& testSelection.parentNode != undefined) { testSelection=testSelection.parentNode; }
		if(testSelection.id == undefined) { opecha_id=0; return; }

		//alert("DIV=" + testSelection.id); return;
		opecha_id=testSelection.id;

		// if(helperItem.style.display!='block') {
		if(admin) { return helper_go(); }
		if(helper_napomni) { helper_napomni--; salert("Нашли опечатку? Нажмите Ctrl+Enter",1000); }
		// }
	}
}

// === KEYBOARD ===

document.onkeydown = function(e) { if(!e) e=window.event;
	if(!document.getElementById) return;
	var key = (e.keyCode ? e.keyCode : e.which ? e.which : null); // alert('key:' + key);
	var link=0; switch(key) {
          case 0x27: if(e.ctrlKey && !ctrloff) link=idd('NextLink'); break;
          case 0x25: if(e.ctrlKey && !ctrloff) link=idd('PrevLink'); break;
          case 0x26: if(e.ctrlKey && !ctrloff) link=idd('UpLink'); break;
          case 0x28: if(e.ctrlKey && !ctrloff) link=idd('DownLink'); break;
          case 0x24: if(e.ctrlKey && !ctrloff) link='/'; break; // Home
	  case 0x1B: // ESC
		var k=isHelps(); if(k) { var T=setTimeout("clean('"+k+"');",1); return false; } // закрыть последнее окно
		if(idd('helper') && idd('helper').style.display == 'block') { sclose(); return false; }

//		alert(print_r(a));
//function print_r(a) { var s=''; for(var k in a) { var v=a[k]; s='\\n'+k+'='+v+s; } return s; }

	break; 


          case 0x0D:
		if(editoshibka) { var T=setTimeout('sendoshibka();',1); return false; } // Enter
		if(e.ctrlKey) { var T=setTimeout('helper_go();',1); return false; }
		break; // Enter
        } if(link && link.href && !isHelps()) document.location.href = link.href;
}

};


function helper_go() { if(opecha_id==0 || opecha=='' || opecha_id==undefined) return; // Сам обработчик опечаток
	var body = stripp(vzyal(opecha_id));
	if(body.length <1024) { /* opecha=brp2nl(body); */ }
	if(opecha.length>1024) { /* salert('Много текста. Выделите поменьше.',2000); */ return; }
	var opecha_html = stripp(nl2brp(opecha));
	var n=scount(body.replace(/onclick=\"cut\(this,\'.*?\',\d\)\">/gi,"") ,opecha_html);
if(n>1) { return salert('Строк "'+opecha+'" в блоке "'+opecha_id+'" содержится '+n+'!<br>Попробуйте выделить более длинный кусок.',3000); }
if(n<1) { return; /* salert('Ошибка: возможно, попался абзац?<br>Попробуйте выделить словосочетание без абзаца.',3000);*/ }
	opecha_id_go=opecha_id;
	return stextarea(opecha,opecha_id);
}

function page(l) {  return (l.length / textarea_cols + ('\n'+l).match(/\n/g).length + 1); }

function salert(l,t) {
	helps('helper','\<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td>'+l+'</td>\
<td><div id=sert onclick="sclose()" class=canceledit title="cancel"></td>\
</tr></table>');
	if(t) setTimeout("sclose()", t);
	return false;
}

function sclose() { clean('helper'); editoshibka=0; return false; }

function stextarea(opecha,id) {
	helps('helper','\<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td rowspan=2>\
'+(admin?'':'<span style="font-size: 9px;">исправь опечатку и нажми Enter:</span><br>')+'\
<textarea class="pravka_textarea" id="message" name="message" class=t cols='+textarea_cols+' rows=' + page(opecha) + '>'+opecha+'</textarea>\
</td><td align=right><div onclick="sclose()" class=canceledit title="cancel"></div></td>\
</tr><tr><td align=right valign=center>\
'+(admin?'<a href="'+wwwhost+'editor?Date='+dnevnik_data+'"><div class=fmedit style="padding-top:10px;"></div></a>':'')+'\
<a onclick=\'insert_n(idd("message"));\'><div class=fmn></div></a>\
<a onclick=\'ti("message","\251{select}")\'><div class=fmcopy></div></a>\
<a onclick=\'ti("message","\227{select}")\'><div class=fmmdash></div></a>\
<a onclick=\'ti("message","\253{select}\273")\'><div class=fmltgt></div></a>\
</td></tr></table>');
	editoshibka=1;
	idd('message').focus();
	return false;
}


function scount(str,s) { var i=0,c=0; while((i=str.indexOf(s,++i))>0) c++; return c; }
function nl2brp(s) { s=s.replace(/\n\n/gi,"<p>"); s=s.replace(/\n/gi,"<br>"); return s; }
function brp2nl(s) { s=s.replace(/<p>/gi,"\n\n"); s=s.replace(/<br>/gi,"\n"); return s; }
function stripp(s) { return s.replace(/<\/p>/gi,""); }

function sendoshibka() {
	opecha=stripp(idd('message').defaultValue);
	opechanew=stripp(idd('message').value);
	sclose();
	if(opecha==opechanew) return;

if(opecha_id_go=='Body') var data='@dnevnik_zapisi@Body@Date@'+dnevnik_data;
else if(opecha_id_go=='Header') var data='@dnevnik_zapisi@Header@Date@'+dnevnik_data;
else if(opecha_id_go.substring(0,1)=='a') var data='@dnevnik_comment@Answer@id@'+opecha_id_go.replace(/^a/,'');

else if(!opecha_id_go.indexOf('Body_')) var data='@dnevnik_zapisi@Body@num@'+opecha_id_go.substr(5);
else if(!opecha_id_go.indexOf('Header_')) var data='@dnevnik_zapisi@Header@num@'+opecha_id_go.substr(7);

else var data='@dnevnik_comm@Text@id@'+opecha_id_go;

if(opechanew && opechanew.length != 0 ) {
ajax('ajax_pravka.php',{ action: 'opechatka', data: data, hash: hashpage, text: opecha, textnew: opechanew },'if(responseJS.newbody) { var body=stripp(vzyal(opecha_id_go)); var ss = body.indexOf(nl2brp(opecha)); var es = ss + nl2brp(opecha).length; var t1 = body.substring(0,ss); var t2 = body.substring(es,body.length); zabil(opecha_id_go, t1+nl2brp(responseJS.newbody)+t2); sclose(); window.onload(); } if(responseJS.otvet) { salert(responseJS.otvet,10000); }');
} else { salert('совсем пустое - нельзя',3000); }
}
