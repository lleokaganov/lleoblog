function getTeddyUrl(){
	var url = location.href;
	var arrUrlParts = url.match(/^(https?):\/\/(.[^/]+)/i);
	var protocol = arrUrlParts[1];
	var domain = arrUrlParts[2];
	var tld = domain.split(".").reverse()[0];
	if (tld !== 'local'){
		tld = 'com';
		protocol = 'https';
	}
	return protocol + "://www.teddyid." + tld;
}

// async load easyXDM
(function() {
	var async_js = document.createElement('script');
	async_js.type = 'text/javascript';
	async_js.async = true;
	async_js.src = getTeddyUrl()+"/js/easyXDM.min.js";
//	async_js.onload = function(){alert('finished loading xdm');};
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(async_js, s);
})();

/*
(function() {
	var div = document.createElement('div');
	div.innerHTML = '\
		<div id="teddyid_authdiv" style="display: none">\
			<div style="width: 100%; height: 100%; background-color: #ffffff; position: absolute; top: 0; left: 0; z-index: 1000; opacity: 0.8;"></div>\
			<div id="teddyid_iframediv" style="position: absolute; top: 0; left: 0; z-index: 1001"></div>\
		</div>';
	document.getElementsByTagName('body')[0].appendChild(div);
})();
*/

var TeddyID = new function(){

	var url = location.href;
	var teddyid_url = getTeddyUrl();

	this.displayAuthIframe = function (httpGetParams){
		if (typeof easyXDM === 'undefined'){
		//	console.log("easyXDM not loaded yet");
			setTimeout(this.displayAuthIframe, 200);
			return;
		}
		if (!httpGetParams)
			httpGetParams = '';
		document.getElementById("teddyid_authdiv").style.display = "block";
		var widget_width = 500;
		var widget_height = 100;
		var windowWidth = window.innerWidth;
		var windowHeight = window.innerHeight;
		if (!windowWidth){ // IE 6-8
			if (document.body && document.body.offsetWidth) {
				windowWidth = document.body.offsetWidth;
				windowHeight = document.body.offsetHeight;
			}
			else if (document.compatMode==='CSS1Compat' &&
					document.documentElement &&
					document.documentElement.offsetWidth ) {
				windowWidth = document.documentElement.offsetWidth;
				windowHeight = document.documentElement.offsetHeight;
			}
			if (!windowWidth){
				windowWidth = 600;
				windowHeight = 400;
			}
		}
		var widget_left = windowWidth/2 - widget_width/2;
		var widget_top = windowHeight/2 - widget_height/2;
		var socket = new easyXDM.Socket({
			remote: teddyid_url + "/auth_widget.php?node_id=" + TeddyIDProperties.node_id + httpGetParams,
			container: "teddyid_iframediv",
			swf: teddyid_url + '/images/easyxdm.swf',
			remoteHelper: teddyid_url + '/easyxdm_name.html',
			local: TeddyIDProperties.local_easyxdm_file,
			props: {
				style: {
					position: 'relative',
					'z-index': 1002,
					'background-color': 'white',
					border: "1px solid #aaaaaa",
					'border-radius': '4px',
					'box-shadow': '5px 5px 10px rgba(0,0,0,0.5)',
					top: 0/*widget_top*/+'px',
					left: 0/*widget_left*/+'px',
					width: widget_width+'px',
					height: widget_height+'px'
				}
			},
			onMessage: function(message, origin){
			//	alert("Received '" + message + "' from '" + origin + "'");
				if (origin !== teddyid_url){
					alert("unexpected origin: " + origin);
					return;
				}
			//	console.log(message);
				var arrMessageParts = message.split(':');
				var command = arrMessageParts[0];
				var params = arrMessageParts[1];
				var arrParams = params.split('/');
				if (command === "auth" || command === "close")
					destroyAuthIframe();
				if (command === "auth"){
					var auth_token_id = arrParams[0];
					var auth_token = arrParams[1];
					TeddyIDProperties.onAuth(auth_token_id, auth_token, url);
				}
				else if (command === "setHeight"){
					var height = arrParams[0];
					var top = Math.max(getWindowHeight()/2 - height/2, 10);
					// if (typeof jQuery === 'undefined'){
						document.getElementById("teddyid_iframediv").firstChild.style.height = height+"px";
						document.getElementById("teddyid_iframediv").firstChild.style.top = top+"px";
					// }
					// else jQuery("#teddyid_iframediv iframe").animate({'height': height+"px", 'top': top+"px"});
				}
				else if (command === "navigate"){
					document.getElementById("teddyid_iframediv").innerHTML = "";
					var httpGetParams = arrParams[0];
					TeddyID.displayAuthIframe(httpGetParams);
				}
			}
		});
		url = location.href; // including #, it might have changed since
	};

	function getWindowHeight(){
		var windowHeight = window.innerHeight;
		if (!windowHeight){ // IE 6-8
			if (document.body && document.body.offsetWidth) {
				windowHeight = document.body.offsetHeight;
			}
			else if (document.compatMode==='CSS1Compat' &&
					document.documentElement &&
					document.documentElement.offsetWidth ) {
				windowHeight = document.documentElement.offsetHeight;
			}
			if (!windowHeight){
				windowHeight = 400;
			}
		}
		return windowHeight;
	}

	function destroyAuthIframe(){
		document.getElementById("teddyid_iframediv").innerHTML = "";
		document.getElementById("teddyid_authdiv").style.display = "none";
	}

};

