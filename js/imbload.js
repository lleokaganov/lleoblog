
if(window.top !== window.self) { // если загружен в iframe
	var r=window.location.hash.split('|');
// if(r[0]=='#IMBLOAD'||r[0]=="#IMBLIST") {
	var IMBLOAD_TOP=r[1];
	var IMBLOAD_MYID=r[2];
	function listener(e){ if(e.origin!='http://'+IMBLOAD_TOP) return;
		zabil('buka',e.data);
	} if(window.addEventListener) window.addEventListener('message',listener,false); else window.attachEvent('onmessage',listener);

	if(r[0]=='#IMBLOAD') { // ресайзнуть окно
	window.top.postMessage('HH|'+IMBLOAD_MYID+'|'+getDocH(),'http://'+IMBLOAD_TOP);
	setTimeout("window.top.postMessage('HH|'+IMBLOAD_MYID+'|'+getDocH(),'http://'+IMBLOAD_TOP)",1000);
	}
	else if(r[0]=='#IMBLIST') {
	window.top.postMessage('IMBLIST|'+imblist,'http://'+IMBLOAD_TOP);
	}
// }
}

/*
function sen(s,i){ var d=idd(i).src.replace(/^(http\:\/\/[0-9a-z_\.\-]+).*$/gi,'$1'); idd(i).contentWindow.postMessage(s,d); }
*/