// ==UserScript==
// @name kirillica
// @namespace none
// @description kirillica
// @include *
// @version 1.0
// @homepage http://lleo.me
//
// (c) lleo

(function() {

function addEvent(element,eventName,fn) {
if(element.addEventListener) element.addEventListener(eventName, fn, false);
else if(element.attachEvent) element.attachEvent('on' + eventName, fn);
}

addEvent(window,'load',function() { // return;

function repfun(t0,t1,s) { if(t1==''
|| -1!=t1.indexOf('<script')
|| -1!=t1.indexOf('<SCRIPT')
|| -1!=t1.indexOf('<style')
|| -1!=t1.indexOf('<STYLE')
|| -1!=t1.indexOf('<meta')
|| -1!=t1.indexOf('<META')
) return t0;

s=s.replace(/[\u0430\u0410]/gi,'<i class=kir_A></i>'); //А
s=s.replace(/[\u0431\u0411]/gi,'<i class=kir_B></i>'); //Б
s=s.replace(/[\u0432\u0412]/gi,'<i class=kir_V></i>'); //В
s=s.replace(/[\u0433\u0413]/gi,'<i class=kir_G></i>'); //Г
s=s.replace(/[\u0434\u0414]/gi,'<i class=kir_D></i>'); //Д
s=s.replace(/[\u0435\u0415]/gi,'<i class=kir_JAT></i>'); //Е
s=s.replace(/[\u0436\u0416]/gi,'<i class=kir_ZH></i>'); //Ж
s=s.replace(/[\u0437\u0417]/gi,'<i class=kir_Z></i>'); //З
s=s.replace(/[\u0438\u0418]/gi,'<i class=kir_H></i>'); //И
s=s.replace(/[\u0439\u0419]/gi,'<i class=kir_H></i>'); //Й
s=s.replace(/[\u043a\u041a]/gi,'<i class=kir_K></i>'); //К
s=s.replace(/[\u043b\u041b]/gi,'<i class=kir_L></i>'); //Л
s=s.replace(/[\u043c\u041c]/gi,'<i class=kir_M></i>'); //М
s=s.replace(/[\u043d\u041d]/gi,'<i class=kir_N></i>'); //Н
s=s.replace(/[\u043e\u041e]/gi,'<i class=kir_O></i>'); //О
s=s.replace(/[\u043f\u041f]/gi,'<i class=kir_P></i>'); //П
s=s.replace(/[\u0440\u0420]/gi,'<i class=kir_R></i>'); //Р
s=s.replace(/[\u0441\u0421]/gi,'<i class=kir_S></i>'); //С
s=s.replace(/[\u0442\u0422]/gi,'<i class=kir_T></i>'); //Т
s=s.replace(/[\u0443\u0423]/gi,'<i class=kir_U></i>'); //У
s=s.replace(/[\u0444\u0424]/gi,'<i class=kir_F></i>'); //Ф
s=s.replace(/[\u0445\u0425]/gi,'<i class=kir_X></i>'); //Х
s=s.replace(/[\u0446\u0426]/gi,'<i class=kir_C></i>'); //Ц
s=s.replace(/[\u0447\u0427]/gi,'<i class=kir_CH></i>'); //Ч
s=s.replace(/[\u0448\u0428]/gi,'<i class=kir_SH></i>'); //Ш
s=s.replace(/[\u0449\u0429]/gi,'<i class=kir_CSH></i>'); //Щ
s=s.replace(/[\u044a\u042a]/gi,'<i class=kir_ERH></i>'); //Ъ
s=s.replace(/[\u044b\u042b]/gi,'<i class=kir_JI></i>'); //Ы
s=s.replace(/[\u044c\u042c]/gi,'<i class=kir_ER></i>'); //Ь
s=s.replace(/[\u044d\u042d]/gi,'<i class=kir_JE></i>'); //Э
s=s.replace(/[\u044e\u042e]/gi,'<i class=kir_JU></i>'); //Ю
s=s.replace(/[\u044f\u042f]/gi,'<i class=kir_JA></i>'); //Я
s=s.replace(/ /gi,' &nbsp; '); //Я
return t1+s;
}

document.body.innerHTML=document.body.innerHTML.replace(/(<[^>]+>)([^<>]+)/gi,repfun);
});

})();