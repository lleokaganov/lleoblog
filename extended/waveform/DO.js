/*
var segments=[
    { startTime: 63.0, endTime: 125.5, editable: true, color: "#E7003E", labelText: "Хуяссе! Multi-celled organisms" },
    { startTime: 225.0, endTime: 267.0, editable: true, color: "#E7003E", labelText: "Во бля чо творят! Reptiles" }
];
*/

song_chmysegtext=function(e,i) { if(e.firstChild.tagName=='TEXTAREA') return;
    var tx=[],p=0,x=window.peaks.waveform.segments.segments[i].overview.label;
    while(x.textArr[p]) tx.push(x.textArr[p++].text);
    e.innerHTML="<textarea id='segtext"+i+"' cols=80 rows=5 style='text-align:left;' onblur=\"song_chmyseghtml(this,"+i+")\">"+tx.join('\n')+"</textarea>";
    idd('segtext'+i).focus();
};

song_chmyseghtml=function(e,i) {
    var x=window.peaks.waveform.segments.segments[i],tx=e.value.split('\n');
    for(var j=0;j<tx.length;j++) { x.overview.label.textArr[j]=x.zoom.label.textArr[j]={width:32,text:tx[j]}; }
    e.parentNode.innerHTML=tx[0]+(tx.length>1?' <i>[more]</i>':'');
};

function song_goto(t) {
    var e=idd('peaks-audio');
    e.pause();
    e.currentTime=t;
    window.peaks.waveform.segments.updateSegments();
    // e.play();
};

song_savelist=function(save){
    var o='',e=window.peaks.waveform.segments.segments,text=[]; if(!e) return;
    for(var i in e) {
	var x=e[i],from=Math.round(x.startTime),to=Math.round(x.endTime),color=x.color,tx=[],p=0;
	while(x.overview.label.textArr[p]) tx.push(x.overview.label.textArr[p++].text);
	var txt=tx[0]+(tx.length>1?' <i>[more]</i>':'');

	o+="<div><span style='cursor:pointer;color:"+color+" !important;'>\
<span alt='From: "+from+"' onclick='song_goto("+from+")'>"+time2dur(from)+"</span>"+" / \
<span alt='To: "+to+"' onclick='song_goto("+to+")'>"+time2dur(to)+"</span>\
</span> - <div class='ll' onclick='song_chmysegtext(this,"+i+")'>"+(txt==''?'[...]':txt)+"</div></div>";
	text.push(time2dur(from)+' - '+time2dur(to)+' '+tx.join('\n'));
    }
    zabil('peaks-buka',o);

    if(save!==1) majax('module.php',{mod:'SLONPLAY',a:'save_segments',num:idd('peaks-num').value,mp3:idd('peaks-mp3').value,text:text.join('\n')});

};

function dur2time(x) { // x=x.replace(/\./g,':');
    var h=0,m=0,s=0,a,i; a=x.split(':'); i=a.length;
    if(i==3) { h=a[0],m=a[1],s=a[2]; }
    else if(i==2) { m=a[0];s=a[1]; }
    else if(i==1) s=a[0];
    else idie("Error timestart `"+h(x)+"`");
    return h*3600+m*60+1*s;
}

function sprintf(x,d) { x=''+x; d=1*d; while(d && x.length<d) x='0'+x; return x; }

function time2dur(x) {
    var X=''+x,c=''; if(X.indexOf('.')!=-1) { c=X.split('.')[1]; x=1*X.split('.')[0]; }
    var h=Math.floor(x/3600),m=Math.floor((x-h*3600)/60),s=x-h*3600-m*60; return sprintf(h,2)+':'+sprintf(m,2)+':'+sprintf(s,2)+(c==''?c:'.'+c);
}

sound_DO=function(segments,fileDAT) {

nonav=1;opecha.n=0;

/* <!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><link href="assets/style_ie.css" rel="stylesheet" type="text/css" /><![endif]--> */

    // window.peaks.waveform=false;

      window.peaks.init({ /** REQUIRED OPTIONS **/
        container: document.getElementById('peaks-container'), // Containing element
        audioElement: document.getElementById('peaks-audio'), // HTML5 Audio element for audio track
        /** Optional config with defaults **/
        height: 200, // height of the waveform canvases in pixels
	zoomLevels: [8192, 4096, 2048, 1024, 512], // Array of zoom levels in samples per pixel (big >> small)
        keyboard: false, // true, // Bind keyboard controls
        nudgeIncrement: 0.05, // Keyboard nudge increment in seconds (left arrow/right arrow)
        inMarkerColor: '#a0a0a0', // Colour for the in marker of segments
        outMarkerColor: '#a0a0a0', // Colour for the out marker of segments
        zoomWaveformColor: 'rgba(0, 225, 128, 1)', // Colour for the zoomed in waveform
        overviewWaveformColor: 'rgba(0,0,0,0.2)', // Colour for the overview waveform
        segmentColor: 'rgba(255, 161, 39, 1)', // Colour for segments on the waveform
        randomizeSegmentColor: true, // Random colour per segment (overrides segmentColor)

        dataUri: fileDAT, // '/waveform/W/test_data/Z.dat', // URI to waveform data file in binary or JSON
        segments: segments
      });

	addEvent(idd('songbt-save'),'click',song_savelist);

      addEvent(idd('segtext'),'change',function(){
	    var e=window.peaks.waveform.segments.segments; e=e[e.length-1];
	    e.overview.label.textArr[0].text=this.value;
	    e.zoom.label.textArr[0].text=this.value;
	    zakryl(this);
	    song_savelist();
	});

      addEvent(idd('songbt-segment'),'click',function() {
	    idd('peaks-audio').pause();
	    var e=idd('segtext'); otkryl(e); e.focus();
        T=window.peaks.time.getCurrentTime();
        window.peaks.segments.addSegment(T,T+10,true);
      });

      addEvent(idd('songbt-zoomin'),'click',function(){ window.peaks.zoom.zoomIn(); });
      addEvent(idd('songbt-zoomout'),'click',function(){ window.peaks.zoom.zoomOut(); });

    setTimeout("song_savelist(1);center('songeditor');",200);
};