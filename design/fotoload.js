/* AJAX IFRAME METHOD (AIM) http://www.webtoolkit.info/ */

AIM = {

frame : function(c,idza) {

        var n = 'f' + Math.floor(Math.random() * 99999);
        var d = document.createElement('DIV');
        d.innerHTML = '<iframe style="display:none" src="about:blank" id="'+n+'" name="'+n+'" onload="AIM.loaded(\''+idza+'\',\''+n+'\')"></iframe>';
        document.body.appendChild(d);

        var i = document.getElementById(n);
        if (c && typeof(c.onComplete) == 'function') { i.onComplete = c.onComplete; }
        return n;
},

form : function(f, name) { f.setAttribute('target', name); },

submit : function(idza, f, c) { AIM.form(f, AIM.frame(c,idza));
        if (c && typeof(c.onStart) == 'function') { return c.onStart(idza); } else { return true; }
},

loaded : function(idza,id) {
        var i = document.getElementById(id);
        if (i.contentDocument) { var d = i.contentDocument;
        } else if (i.contentWindow) { var d = i.contentWindow.document;
        } else { var d = window.frames[id].document; }
        if (d.location.href == "about:blank") { return; }
        if (typeof(i.onComplete) == 'function') { i.onComplete(idza,d.body.innerHTML); }
}

}


var fototabn=1;

function mkload(i) {
document.getElementById("foton"+i).innerHTML = '\n\n\
<div id="fotor'+i+'" class=fotoe>\
<fieldset><legend>загрузка фото</legend>\
<form name="'+i+'" action="'+wwwhost+'ajax_loadfoto.php" method="post" enctype="multipart/form-data" \
 onsubmit="return AIM.submit('+i+', this, {\'onStart\' : startCallback, \'onComplete\' : completeCallback})">\
        <input type=hidden name="id" value="'+i+'" />\
<p>Название: <input type="text" name="name" />\
доступ: <select name="Access"><option value="all" selected="selected">всем</option><option value="podzamok">избранным</option><option value="admin">никому</option></select>        <br><input type="file" name="file" onchange="document.getElementById(\'fotos'+i+'\').click();" />\
       <br><input id="fotos'+i+'" type="submit" value="SUBMIT" />\
</form>\
</fieldset>\
</div>\
<div id="foton'+(i+1)+'"></div>';
}


function startCallback(idza) {
        document.getElementById('fotor'+idza).style.background = '#cce';
        document.getElementById('fotos'+idza).disabled = true;
        mkload(fototabn++);
        return true;
}

function completeCallback(idza,response) {

if(response=='present') {
	response="Уже есть!";
	setTimeout('document.getElementById("fotor'+idza+'").innerHTML = ""', 3000);
}

//        document.getElementById('fotor'+idza).innerHTML = response;
	if(response.substring(0,1)!='<') { document.getElementById('fotor'+idza).innerHTML = response; }
	else {
	        document.getElementById('fotor'+idza).innerHTML = '';
	        document.getElementById('polefoto').innerHTML = response+document.getElementById('polefoto').innerHTML;
	}
}

// mkload(0);
