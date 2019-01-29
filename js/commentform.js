loadCSS('commentform.css');

function cm_mail_validate(p) { var l=p.value; return l; }

function cmsend_edit(t,comnu,id) { majax('comment.php',{a:'editsend',text:t['txt'].value,comnu:comnu,id:id,commenttmpl:commenttmpl}); return false; }

function cmsend(t,comnu,id,dat,lev) { var ara={a:'comsend',comnu:comnu,id:id,dat:dat,lev:lev,commenttmpl:commenttmpl};
	if(t['mail']) ara['mail']=t['mail'].value;
	if(t['nam']) ara['name']=t['nam'].value;
	if(t['txt']) ara['text']=t['txt'].value;
	if(t['capcha']) ara['capcha']=t['capcha'].value;
	if(t['capcha_hash']) ara['capcha_hash']=t['capcha_hash'].value;
	majax('comment.php',ara);
	return false;
}

// ============================  последн€€ строка скрипта должна быть всегда такой: ========================
var src='commentform.js'; ajaxoff(); var r=JSload[src]; JSload[src]='load'; if(r && r!='load') eval(r);