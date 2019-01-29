loadCSS('commentstyle.css');

function kus(u) { if(u) majax('login.php',{action:'getinfo',unic:u}); }// лична€ карточка автора
function kd(e) { if(confirm('“очно удалить?')) majax('comment.php',{a:'del',id:ecom(e).id}); } // удалить комментарий
function ked(e) { majax('comment.php',{a:'edit',id:ecom(e).id}); } // редактировать комментарий
function ksc(e) { majax('comment.php',{a:'scr',id:ecom(e).id}); } // скрыть-раскрыть
function rul(e) { majax('comment.php',{a:'rul',id:ecom(e).id}); } // rul-не rul
function ka(e) { e=ecom(e); majax('comment.php',{a:'comform',id:e.id,lev:e.style.marginLeft,comnu:comnum}); } // ответить

function kpl(e) { majax('comment.php',{a:'plus',id:ecom(e).id}); } // плюсик
function kmi(e) { majax('comment.php',{a:'minus',id:ecom(e).id}); } // minus

function opc(e) { e=ecom(e); majax('comment.php',{a:'pokazat',oid:e.id,lev:e.style.marginLeft,comnu:comnum}); } // показать

function ecom(e) {
        while( ( e.id == '' || e.id == undefined ) && e.parentNode != undefined) e=e.parentNode;
        if(e.id == undefined) return 0; return e;
}

// ============================  последн€€ строка скрипта должна быть всегда такой: ========================
var src='comm.js'; ajaxoff(); var r=JSload[src]; JSload[src]='load'; if(r && r!='load') eval(r);