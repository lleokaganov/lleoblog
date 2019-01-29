var picker = {
	V: 100,
	S:100,
	status:false,

//=========================
	DrawNewColor: function(c){
	    c=(''+c).split(','); for(var i in c) { var l=(isNaN(c[i])?0:1*c[i]).toString(16); l=((''+l).length<2?'0':'')+l; l=l.toUpperCase(); c[i]=l; }
	    document.getElementById("txt_color").value=c.join(''); // parseInt(c.join(''),16); 
	},
//=========================

	
	init: function(c) {

	// var s=parseInt((''+c[0]+c[1]),16)+','
    
    // picker.DrawNewColor('0,0,0');
    document.getElementById("txt_color").value=c;

//=========================
mouse={
    pageX:function(b){
	var a,c,d;d=b||event;
	return null==d.pageX&&null!=d.clientX?(a=document.body,c=document.documentElement,b=c.scrollLeft||a&&a.scrollLeft||0,b=d.clientX+b-(c.clientLeft||a.clientLeft||0)):d.pageX
    },
    pageY:function(b){
	var a,c,d;d=b||event;
	return null==d.pageX&&null!=d.clientX?(a=document.body,c=document.documentElement,b=c.scrollTop||a&&a.scrollTop||0,b=d.clientY+b-(c.clientTop||a.clientTop||0)):d.pageY
    }
},

Obj={
    positX:function(b){
	var a,c;a=0;c=b.getBoundingClientRect();b=document.body;
	a=document.documentElement;a=c.left+(a.scrollLeft||b&&b.scrollLeft||0)-(a.clientLeft||b.sclientLeft||0);
	return Math.round(a)
    },
    positY:function(b){
	var a,c;a=0;c=b.getBoundingClientRect();b=document.body;a=document.documentElement;a=c.top+(a.scrollTop||b&&b.scrollTop||0)-(a.clientTop||b.sclientTop||0);return Math.round(a)
    }
},

querySelector={
    All:function(b){
	var a,c,d=[];if(a=document.querySelectorAll("["+b+"]"))return a;a=document.body.getElementsByTagName("*");for(var e=a.length;e--;)c=a[e],null!==c.getAttribute(b)&&d.push(c);return d
    }
},

cls={
    has:function(b,a){
	var c,d;d=b.className.split(" ");for(c=d.length;c--;)if(d[c]===a)return!0;return!1
    },
    removeClass:function(b,a){
	for(var c=b.className.split(" "),d=0;d<c.length;d++)c[d]==a&&c.splice(d--,1);b.className=c.join(" ")
    },
    addClass:function(b,a){
	for(var c=b.className.split(" "),d=0;d<c.length;d++)if(c[d]==a)return;c.push(a);b.className=c.join(" ")
    }
};
//=========================

     var id_elements = {primary: "primary_block", arrows: "arrows", block: "block_picker", circle: "circle", line: "line"}; 

    var s ={h:180, w:20, th: id_elements.arrows, bk: id_elements.block, line: id_elements.line};
	/*
	Параметры передаваемые через обьект "s" обьекту "Line"
	h - высота линни Hue
	w- ширина линни Hue
	th  - id для елмента в котором находяться стрелки || ползунок для управление шкалой Hue
	bk - id блока главного блока с изображение и изменяемым фоном
	*/
    Line.init(s);//отрисовка линий hue и привязка событий

     var b = {block: id_elements.block, circle: id_elements.circle};
	/*
	Параметры передаваемые через обьект "b" обьекту "Block"
	id - id блока выбора цвета (основной блок)
	c - круг для перемещения по основнoму блоку(для выбора цвета)
	*/
     Block.init(b);// привязка событий к блоку и кругу для управления

      picker.out_color = document.getElementById("out_color");
//      picker.txt_color = document.getElementById("txt_color");

    }
};

var Line ={
	  
	   Hue: 0,
	
	init: function (elem){
		
      var canvaLine, cAr, pst, bk, t = 0;;
        
		canvaLine = Line.create(elem.h, elem.w, elem.line, "cLine");

          cAr = document.getElementById(elem.th);
           bk = document.getElementById(elem.bk);

       Line.posit = function (e){
		 var top, rgb;
           
		  top = mouse.pageY(e) - pst;
           top = (top < 0 )? 0 : top;
             top = (top > elem.h )? elem.h  : top;
 
               cAr.style.top = top-2 +"px";
                t =  Math.round(top /(elem.h/ 360));
                 t = Math.abs(t - 360);
                   t = (t == 360)? 0 : t;
  
                     Line.Hue = t;

                       bk.style.backgroundColor = "rgb("+convert.hsv_rgb(t,100,100)+")";
                        picker.out_color.style.backgroundColor= "rgb("+convert.hsv_rgb(t,picker.S,picker.V)+")";
                        // picker.txt_color.innerHTML = convert.hsv_rgb(t,picker.S,picker.V);
			picker.DrawNewColor(convert.hsv_rgb(t,picker.S,picker.V));
	}
// события перемещения по линии
      cAr.onmousedown = function (){
	
	      pst = Obj.positY(canvaLine);
	
	         document.onmousemove = function(e){Line.posit(e);}
		}

       cAr.onclick = Line.posit;

         canvaLine.onclick = function (e){Line.posit(e)};
           
		   canvaLine.onmousedown = function (){
	
	         pst = Obj.positY(canvaLine);
	           
			   document.onmousemove = function(e){Line.posit(e);}
	}
                 document.onmouseup = function (){
					 document.onmousemove = null; 
					 cAr.onmousemove = null; 
					 
					 }
},
	
	
	create : function (height, width, line, cN){
	  var canvas = document.createElement("canvas");
	
	    canvas.width = width;
	     canvas.height = height;	
	
	       canvas.className = cN;
	        
			document.getElementById(line).appendChild(canvas);
		 
		      Line.grd(canvas, height, width);
		 
		 return canvas;
	},
	
	grd:function (canva, h, w){
		var gradient,hue,color, canva, gradient1;
		
		 canva = canva.getContext("2d");

	       gradient = canva.createLinearGradient(w/2,h,w/2,0);
	 
	         hue = [[255,0,0],[255,255,0],[0,255,0],[0,255,255],[0,0,255],[255,0,255],[255,0,0]];
	
	for (var i=0; i <= 6;i++){
		
	  color = 'rgb('+hue[i][0]+','+hue[i][1]+','+hue[i][2]+')';
	
	     gradient.addColorStop(i*1/6, color);
	
	};
	  canva.fillStyle = gradient;
         	canva.fillRect(0,0, w ,h);	
	}
};

	var Block = {
			
	init: function (elem) {
		
		var circle, block, colorO, bPstX, bPstY, bWi, bHe, cW, cH, pxY, pxX;
		 
		 circle = document.getElementById(elem.circle);
		  block = document.getElementById(elem.block);
		    cW = circle.offsetWidth ;
	         cH = circle.offsetHeight;
		       bWi = block.offsetWidth - cW;
	             bHe = block.offsetHeight - cH;
		           pxY = bHe / 100; 
		            pxX = bWi / 100; 
		
		Block.cPos = function (e){
			
			var top, left, S, V;
			
			 document.ondragstart = function() { return false;}
			
			   document.body.onselectstart = function() { return false; }
			
			left = mouse.pageX(e) - bPstX - cW/2;
			 left = (left < 0)? 0 : left;
			  left = (left > bWi  )? bWi  : left;
			   
			   circle.style.left = left  + "px"; 
			   
			    S = Math.ceil(left /pxX) ;
			    
				 top = mouse.pageY(e)  - bPstY - cH/2;
			      top = (top > bHe  )? bHe : top;
			
			        top = (top < 0)? 0 : top;
			
			          circle.style.top = top   + "px";
			
			            V = Math.ceil(Math.abs(top / pxY - 100));
			             
						 if (V <50) circle.style.borderColor = "#fff";
			
			else circle.style.borderColor = "#000";
			
			picker.S = S;
			
			  picker.V = V;
			
			     picker.out_color.style.backgroundColor = "rgb("+convert.hsv_rgb(Line.Hue,S,V)+")";
			     picker.DrawNewColor(convert.hsv_rgb(Line.Hue,S,V));
                            // picker.txt_color.innerHTML='#'+convert.hsv_rgb(Line.Hue,S,V);

				 var _res = convert.hsv_rgb(Line.Hue,S,V);
			     _res = _res[0].toString(16)+""+_res[1].toString(16)+""+_res[2].toString(16);
				 console.log(_res);
			}
			
			block.onclick = function(e){Block.cPos(e);}
			block.onmousedown  = function (){
			document.onmousemove = function (e){
				bPstX = Obj.positX(block);
				bPstY = Obj.positY(block);
				Block.cPos(e);
				}
			}

			document.onmouseup=function() {
				document.onmousemove = null;
				}
		}
		
		};

var convert = {
	
	hsv_rgb: function (H,S,V){
	 
	 var f , p, q , t, lH;
   
	  S /=100;
      V /=100;
     
	 lH = Math.floor(H / 60);
      
	  f = H/60 - lH;
        p = V * (1 - S); 
         q = V *(1 - S*f);
	       t = V* (1 - (1-f)* S);
      
	  switch (lH){
      
	  case 0: R = V; G = t; B = p; break;
        case 1: R = q; G = V; B = p; break;
         case 2: R = p; G = V; B = t; break;
           case 3: R = p; G = q; B = V; break;
            case 4: R = t; G = p; B = V; break;
              case 5: R = V; G = p; B = q; break;}
     
	 return [parseInt(R*255), parseInt(G*255), parseInt(B*255)];
	 }
	
};	