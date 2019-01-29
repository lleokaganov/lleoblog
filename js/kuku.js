saytime=function(e){
	var d=new Date(); var t=d.valueOf();
	d.setSeconds(0); d.setMinutes(0); d.setHours(d.getHours()+1);
	var t=d.valueOf()-t; if(!t) t=1;
	// if(admin) t=8000; // http://w3pro.ru/article/radio-pleer-s-pomoshchyu-html5-audio
	var tp=Math.floor(t*3/4); // подгрузить заранее за 3/4 срока
	var pdz=www_design+'kukus/s'+(1+Math.floor(Math.random()*100)%10);
	var chas=www_design+'kukus/'+(1+new Date().getHours());
	if(tp && (t-tp)>3) { // если разница больше 3 секунд - сделать предварительную подгрузку
	setTimeout("playswf(www_design+'kukus/s0',1); playswf('"+pdz+"',1); playswf('"+chas+"',1);",tp);
	}
	setTimeout("playswf(www_design+'kukus/s0')",t);
	setTimeout("playswf('"+pdz+"')",t+2000);
	setTimeout("playswf('"+chas+"'); saytime();",t+4000);
}

saytime();

setTimeout("playswf(www_design+'kladez/"+((Math.floor(Math.random()*100)+1)%28)+"')",120000+Math.floor(Math.random()*2000000));
