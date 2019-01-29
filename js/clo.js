/*
<input type='button' onclick='clop_all(1)' value='раскрыть'>
<input type='button' onclick='clop_all(0)' value='скрыть'>
*/

if(typeof(clopta)=='undefined') var clopta={}; var clop=0;


function clop_all(x){
        doclass('cl',function(e){e.style.display=x?'inline-block':'none'},'');
        doclass('cl_plus',function(e){ e.src=www_design+'e3/expand_'+(x==0?'plus':'minus')+'.gif'; },'');
	for(var i=0;i<clopta[num].length;i++) clopta[num][i]=x?0:1;
        f5_save('clopta_'+num,clopta[num].join(','));
}

function doclop(e,div,n){
     if(n) var x='none',l='plus'; else var x='inline-block',l='minus';
     e.style.display=x; div.src=www_design+'e3/expand_'+l+'.gif';
}

function setclo(){
        if(typeof(clopta[num])=='undefined') {
           var v=f5_read('clopta_'+num); clop=0;
           if(v!=false) clopta[num]=v.split(','); else clopta[num]=[];
        }
        doclass('cl',function(e){
                var d=document.createElement('IMG');
                var a=e,c=(clop++),g=num;
                var w=clopta[g][c]==1?1:0; clopta[g][c]=w; doclop(e,d,w);
                d.onclick=function(e){ var n=clopta[g][c]?0:1; doclop(a,d,n); clopta[g][c]=n;
                	var v=clopta[g].join(',');
			f5_save('clopta_'+g,clopta[g].join(','));
		};
	d.className='cl_plus';
        e.parentNode.insertBefore(d,e);
	},'');
}

page_onstart.push('setclo()');

/*
<br>Первое:<div class=cl>текст1</div>
<br>Второе:<div class=cl>текст2</div>
*/
