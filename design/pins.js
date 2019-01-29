// процедуры pins

function insert_n(e) { var v = e.value;
var t1 = v.substring(0,e.selectionStart); // текст перед
var t2 = v.substring(e.selectionEnd,v.length); // текст после
var pp=GetCaretPosition(e);
e.value=t1.replace(/\s+$/,'') + "\n" + t2.replace(/^\s+/,'');
setCaretPosition(e, pp);
}

/*
function pns(e,i,j) { alert(1); pins(idd(e),i,j); }
function pns2(e,i1,i2,j) { pins2(idd(e),i1,i2,j); }

function ins(e,i) {
var t1 = e.value.substring(0,e.selectionStart); // текст перед
var t2 = e.value.substring(e.selectionEnd,e.value.length); // текст после
var pp=GetCaretPosition(e);
e.value = t1 + i + t2;
setCaretPosition(e, pp);
}

function pins(e,i,j) { var v = e.value;
var pp=GetCaretPosition(e);
var es = e.selectionEnd; // определяем координаты курсора
var ss = e.selectionStart;
var t1 = v.substring(0,ss); // текст перед
var t2 = v.substring(es,v.length); // текст после
var t3 = v.substring(ss,es); // текст между
e.value = t1 + i + t3 + j + t2;
setCaretPosition(e, ss+(i+j+t3).length);
e.selectionStart=ss+i.length;
e.selectionEnd=ss+(i+t3).length;
}

function pins2(e,i1,i2,j) { var v = e.value;
var pp=GetCaretPosition(e);
var es = e.selectionEnd; // определяем координаты курсора
var ss = e.selectionStart;
var t1 = v.substring(0,ss); // текст перед
var t2 = v.substring(es,v.length); // текст после
var t3 = v.substring(ss,es); // текст между
var val=t1 + i1 + t3 + i2 + t3 + j + t2;
e.value = val;
setCaretPosition(e, ss+val.length);
e.selectionStart=ss;
e.selectionEnd=ss+val.length;
}
*/

function ti(id,tmpl) {
var e=idd(id); var v=e.value; var ss=e.selectionStart; var es=e.selectionEnd;
var s=tmpl.replace(/\{select\}/,v.substring(ss,es));
GetCaretPosition(e); e.value = v.substring(0,ss)+s+v.substring(es,v.length); setCaretPosition(e,ss+s.length);
e.selectionStart=ss; e.selectionEnd=ss+s.length;
}

var scrollTop = 0;

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
