var opecha_id;
var site_id;
var textarea_cols=40;

window.onload = function() {

document.onkeydown = NavigateThrough;

  message = document.getElementById('message');
  helperItem = document.getElementById('helper');
  screenWidth = document.body.clientWidth;

  // 1 - Браузеры. 2 - IE. 3 - Неизвестно.
  testRange = (document.createRange) ? 1 : (message.createTextRange && message.createTextRange() != undefined) ? 2 : 3;

 document.onmouseup = function(e) { if(!e) e = window.event;

	var opecha=(document.selection) ? document.selection.createRange().text : window.getSelection(); opecha += '';

          switch(testRange) { // Браузеры
            case 1: if(window.getSelection().anchorNode) testSelection = window.getSelection().anchorNode; break;
            case 2: var testSelection = document.selection.createRange().parentElement(); break; // IE
	    }


          if(testRange != 3 && testSelection && opecha!='') {           // Поиск автора выделенного текста.
//	    helperItem.style.display = 'none';
            while( ( testSelection.tagName != 'DIV' || testSelection.id == '' || testSelection.id == undefined ) && testSelection.parentNode != undefined) {
              testSelection=testSelection.parentNode; }


if(testSelection.id != undefined) { 

// alert("DIV=" + testSelection.id);

helper_pos(e);

opecha_id=testSelection.id;
var body = document.getElementById(opecha_id).innerHTML;

if(body.length <1024) { opecha=brp2nl(stripp(body)); } else {
	var opecha_html = stripp(nl2brp(opecha));
	if(opecha.length>1024) { salert('Много текста. Выделите поменьше.',2000); return; } // 256
body=stripp(body);
var n=scount(body,opecha_html);
if(n>1) { salert('Cтрок "'+opecha+'" в блоке "'+opecha_id+'" содержится '+n+'!<br>Попробуйте выделить более длинный кусок.',3000); return; }
if(n<1) { 
//	alert(opecha_html+"\n\nне найдена:\n\n"+body);
	salert('Ошибка. В блоке "'+opecha_id+'" такая строка не найдена:<p>'+opecha_html,3000); return; }
}

	stextarea(opecha,opecha_id);


} else {

// *********** site **************
// *********** site **************
// *********** site **************
// *********** site **************
// *********** site **************
// *********** site **************
// *********** site **************
// *********** site **************
// *********** site **************

		document.getElementById('helper_body').innerHTML='';
		helperItem.style.display = 'none';
		// поиск более широкого поля
		var body2=brp2nl(stripp(document.body.innerHTML));
                var ss = body2.indexOf(opecha);
                var es = ss + opecha.length;
		for(i=ss; i>0 && i>(ss-400) && body2.substring(i-1,i)!='>' ;i--) { }
		for(j=es; j<body2.length && j<(es+400) && body2.substring(j,j+1)!='<' ;j++) { }
		opecha = body2.substring(i,j);
		// поиск более широкого поля

		var body="<!--"+page_id+"-->"+document.body.innerHTML.replace(/\n/g,' ')+"<!--/"+page_id+"-->";
		var opecha_html = stripp(nl2brp(opecha)).replace(/\n/g,' ');
		if(scount(body,opecha_html)!=1) { /* alert(7); */ return; }

		helper_pos(e);

		//      1           2          3                     4
		r = /^(.*)\<\!\-\-(\d+)\-\-\>(.+?)\<\!\-\-\/\2\-\-\>(.*)$/g

		site_id=page_id;

		var stop=100; while(--stop) {
			if(body==body.replace(r,"")) { break; }// не найдено
			if(scount(body.replace(r,"$1"),opecha_html)) { body=body.replace(r,"$1$4"); }
			else if(scount(body.replace(r,"$3"),opecha_html)) { site_id=body.replace(r,"$2"); body=body.replace(r,"$3"); }
			else if(scount(body.replace(r,"$4"),opecha_html)) { body=body.replace(r,"$1$4"); }
		}

	opecha_id='site';


	stextarea(opecha,site_id);

	}

    }
}

};






function helper_pos(e) { // Позиция курсора мыши
        if(e.pageX || e.pageY) {
          leftHelper = e.pageX;
          topHelper = e.pageY;
        } else {
          leftHelper = e.clientX + (document.documentElement.scrollLeft || document.body.scrollLeft) - document.documentElement.offsetLeft;
          topHelper = e.clientY + (document.documentElement.scrollTop || document.body.scrollTop) - document.documentElement.offsetTop;
        }
// Проверка на вылет за ширину экрана. Пришлось ввести отдельную переменную, при определении ширины внутри функции проявляется странный глюк в FF3
	var Nx = 330;
	topHelper = topHelper - Nx/2;
	leftHelper = leftHelper - Nx/2
    if(leftHelper < 0) leftHelper = 0;
    if(topHelper < 0) topHelper = 0;
    if(leftHelper + Nx > screenWidth ) leftHelper = screenWidth - Nx;

        helperItem.style.top = topHelper + 'px'; // - 50 
        helperItem.style.left = leftHelper + 'px'; //- 5 
}



function page(l) {  return (l.length / textarea_cols + ('\n'+l).match(/\n/g).length + 1); }

function salert(l,t) { 
	document.getElementById('helper_body').innerHTML='\
<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td>'+l+'</td>\
<td><div id=sert onclick="sclose()" class=canceledit title="cancel"></td>\
</tr></table>';
	helperItem.style.display = 'block';
	document.getElementById('sert').focus();
	if(t) setTimeout("sclose()", t);
}

function sclose() { document.getElementById('helper_body').innerHTML=''; helperItem.style.display = 'none'; }


function stextarea(opecha,id) { 
	document.getElementById('helper_body').innerHTML='\
<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td rowspan=2>\
<textarea class="pravka_textarea" id="message" name="message" class=t cols='+textarea_cols+' rows=' + page(opecha) + '>'+opecha+'</textarea>\
</td><td align=right><div onclick="sclose()" class=canceledit title="cancel"></div></td>\
</tr><tr><td align=right valign=center>\
'+(admin?'<a href="'+wwwhost+'adminsite?mode=one&edit='+id+'"><div class=fmedit style="padding-top:10px;"></div></a>':'')+'\
<a onclick=\'insert_n(document.getElementById("message"));\'><div class=fmn></div></a>\
<a onclick=\'pins(document.getElementById("message"),"\251","");\'><div class=fmcopy></div></a>\
<a onclick=\'pins(document.getElementById("message"),"\227","");\'><div class=fmmdash></div></a>\
<a onclick=\'pins(document.getElementById("message"),"\253","\273");\'><div class=fmltgt></div></a>\
</td></tr></table>';
	helperItem.style.display = 'block';
	document.getElementById('message').focus();
}




window.onresize = function() { screenWidth = document.body.clientWidth; }

function scount(str,s) { var i=0,c=0; while((i=str.indexOf(s,++i))>0) c++; return c; }
function nl2brp(s) { s=s.replace(/\n\n/gi,"<p>"); s=s.replace(/\n/gi,"<br>"); return s; }
function brp2nl(s) { s=s.replace(/<p>/gi,"\n\n"); s=s.replace(/<br>/gi,"\n"); return s; }
function stripp(s) { return s.replace(/<\/p>/gi,""); }

function NavigateThrough (event) { if(!document.getElementById) return;
    if(window.event) event=window.event;

	var keycode = (event.keyCode ? event.keyCode : event.which ? event.which : null);
//	alert('key:' + keycode);
	if(keycode == 27 && helperItem.style.display == 'block') { sclose(); return false; }
	if(keycode == 13 && helperItem.style.display == 'block') { var T=setTimeout('sendoshibka();',1); return false; } // через 1 мc 

	if(event.ctrlKey) { var link = null;
	
        switch (keycode) {
// ".($_COOKIE['ctrloff']!='off'?"
//            case 0x27: link=document.getElementById('NextLink'); break;
//          case 0x25: link=document.getElementById('PrevLink'); break;
//            case 0x26: link=document.getElementById('UpLink'); break;
//            case 0x28: link=document.getElementById('DownLink'); break;
// ":"")."
//            case 0x24: href='/'; break; // Home
//            case 0x0D: 

//роиск НАТО блока! Не позволит Бог видеть самым первым блогом Лебедева блог!
// Это англичанка гадит! Это полный бред! Мы в стране не Тёмы ради тянем интернет! О

        }   // if(link && link.href) document.location.href = link.href;



    }
}


function sendoshibka() {

	var opecha=stripp(document.getElementById('message').defaultValue);
	var opechanew=stripp(document.getElementById('message').value);

	if(opecha==opechanew) { salert("",1); return; }

	document.getElementById('helper_body').innerHTML=' &nbsp; '; // отправляю

if(opecha_id!='site') {
	var kuda=document.getElementById(opecha_id);
	var body=stripp(document.getElementById(opecha_id).innerHTML);
	var data='@site@text@id@'+opecha_id;
} else {
	var kuda=document.body;
	var body=document.body.innerHTML;
	var data='@site@text@id@'+site_id;
}


  if(opechanew && opechanew.length != 0 ) {
    JsHttpRequest.query(ajax_pravka, {
        action: 'opechatka',
        data: data,
        hash: hashpage,
        text: opecha, textnew: opechanew },
    function(responseJS, responseText) {
        if(responseJS.newbody) {
                var ss = body.indexOf(nl2brp(opecha));
                var es = ss + nl2brp(opecha).length;
                var t1 = body.substring(0,ss); // текст перед
                var t2 = body.substring(es,body.length); // текст после
                kuda.innerHTML = t1 + nl2brp(responseJS.newbody) + t2;
		document.getElementById('helper_body').innerHTML='';
		document.getElementById('helper').style.display = 'none';
		window.onload();
        }
        if(responseJS.otvet) { 
		salert(responseJS.otvet,10000);
//		document.getElementById('helper_body').innerHTML = responseJS.otvet; // + "<p>" + document.getElementById('helper_body').innerHTML;
		}
    },true);
  } else { salert('совсем пустое - нельзя',3000); }
}





function insert_n(ctrl) {
var pp=GetCaretPosition(ctrl);
var es = ctrl.selectionEnd; // определяем координаты курсора
var ss = ctrl.selectionStart;
var txt1 = ctrl.value.substring(0,ss); // текст перед
var txt2 = ctrl.value.substring(es,ctrl.value.length); // текст после
var o=txt1.replace(/\s+$/,'') + "\n" + txt2.replace(/^\s+/,'');
/*
var i=0,j=0; while((i=o.indexOf("\n",++i))>0) {
	//	j=i;
	//	while((j.indexOf("\n",++j))>0) {}
	//	if(j-i>3) { o=o.substring(0,i)+o.substring(j,o.length); }

	alert('@');
	}
//o=o.replace(/$/gm,"\001");
//o=o.replace(/\001{3,}/g,"\n\n");
//o=o.replace(/\001/g,"\n");
*/
ctrl.value = o;
setCaretPosition(ctrl, pp);
}


function ins(ctrl,i) {
var pp=GetCaretPosition(ctrl);
var es = ctrl.selectionEnd; // определяем координаты курсора
var ss = ctrl.selectionStart;
var txt1 = ctrl.value.substring(0,ss); // текст перед
var txt2 = ctrl.value.substring(es,ctrl.value.length); // текст после
ctrl.value = txt1 + i + txt2;
setCaretPosition(ctrl, pp);
}

function pins(ctrl,i,j) {
var pp=GetCaretPosition(ctrl);
var es = ctrl.selectionEnd; // определяем координаты курсора
var ss = ctrl.selectionStart;
var txt1 = ctrl.value.substring(0,ss); // текст перед
var txt2 = ctrl.value.substring(es,ctrl.value.length); // текст после
var txt3 = ctrl.value.substring(ss,es); // текст между
ctrl.value = txt1 + i + txt3 + j + txt2;
setCaretPosition(ctrl, pp);
}

function pins2(ctrl,i1,i2,j) {
var pp=GetCaretPosition(ctrl);
var es = ctrl.selectionEnd; // определяем координаты курсора
var ss = ctrl.selectionStart;
var txt1 = ctrl.value.substring(0,ss); // текст перед
var txt2 = ctrl.value.substring(es,ctrl.value.length); // текст после
var txt3 = ctrl.value.substring(ss,es); // текст между
ctrl.value = txt1 + i1 + txt3 + i2 + txt3 + j + txt2;
setCaretPosition(ctrl, pp);
}

var scrollTop = 0;

function GetCaretPosition (ctrl) { var CaretPos = 0; // IE Support
if(document.selection) { ctrl.focus (); var Sel = document.selection.createRange (); Sel.moveStart ('character', -ctrl.value.length);
CaretPos = Sel.text.length; } // Firefox support
else if(ctrl.selectionStart || ctrl.selectionStart == '0') { CaretPos = ctrl.selectionStart; } scrollTop = ctrl.scrollTop;
return (CaretPos);
}

function setCaretPosition(ctrl,pos) {
if(ctrl.setSelectionRange){ ctrl.focus(); ctrl.setSelectionRange(pos,pos); }
else if(ctrl.createTextRange){ var range = ctrl.createTextRange();
range.collapse(true); range.moveEnd('character',pos); range.moveStart('character',pos); range.select(); }
ctrl.scrollTop = scrollTop;
}
