<?php

// Эта функция возвращает 0, если выполнять этот модуль не требуется (напр. работа уже сделана)
// Либо - строку для отображения кнопки запуска работы.
function installmod_init() {
if(is_file($GLOBALS['filehost']."log/blogs.txt")) return "Проверить иные блоги";
return false;
}

function filegett($url,$t=10) { $url=array_merge(array('scheme'=>'http','port'=>'80'),parse_url($url));
	$fp=fsockopen($url['host'],80,$errno,$errstr,$t);
	if($fp){
	        fwrite($fp, "GET /".$url['path'].($url['query']!=''?'?'.$url['query']:'')." HTTP/1.0\r\n");
	        fwrite($fp, "Host: ".$url['host']."\r\n");
	        fwrite($fp, "Connection: Close\r\n\r\n");
	        stream_set_blocking($fp,TRUE);
	        stream_set_timeout($fp,$t);
        	$info = stream_get_meta_data($fp);

		// if($url['host']=='dbyd.ru') 
//		dier($info);

	        while((!feof($fp)) && (!$info['timed_out'])) {
        	        $data .= fgets($fp, 4096);
	                $info = stream_get_meta_data($fp);
	                ob_flush;
	                flush();
        	}
		fclose($fp);
	list($header,$data)=explode("\r\n\r\n",$data,2);
        if(!$info['timed_out']) return $data;
	}
//	idie('#');
	return false;
}


function metkalist($m,$url) {
	$f=$GLOBALS['filehost']."log/blogs.txt";
	$p=file($f);
	$k=0; $s=''; foreach($p as $l) { $l=trim($l);
		if(substr($l,0,1)=='#') $ll=substr($l,1); else $ll=$l;
		if($ll=='') continue;
		if(substr($ll,0,strlen($url))==$url) $l=$m.$ll; 
		$s.="$l\n";
		$k++;
	}
//	idie(nl2br($s."+++++++".$url));
	fileput($f,$s);
}

// Эта функция - сама работа модуля. Если работа не требует этапов - вернуть 0,
// иначе вернуть номер позиции, с которой продолжить работу, рисуя на экране професс выполнения.
// skip - с чего начинать, allwork - общее количество (измерено ранее), $o - то, что кидать на экран.
function installmod_do() { global $o,$skip,$allwork,$delknopka,$script;

	if(RE('act')=='blogtest') { $n=RE0('num'); $url=RE('url');

		$a=filegett(trim($url,' /').'/admin?version=1',2);
		if($a===false) {
			metkalist('#',$url); $script="
idd('blog_$n').style.color='#555555';
idd('blog_$n').style.background='transparent';
testblog();"; return 0;
			}
		$a=explode("\n",$a); $a=trim($a[0]);
		if(substr($a,0,8)!='lleoblog' or (preg_match("/[^0-9a-z\.\-\_ ]/si",$a))) {
			metkalist('#',$url); $script="
idd('blog_$n').style.color='#888888';
idd('blog_$n').style.background='transparent';
testblog();"; return 0;
		}
		list($lb,$a)=explode(' ',$a);
		metkalist('',$url);
		$script="
zabil('blog_$n',vzyal('blog_$n')+' <font color=red>".h(trim($a))."</font>');
idd('blog_$n').style.color='#368636';
idd('blog_$n').style.background='transparent';
testblog();";
		return 0;
	}

// var s=vzyal('blog_$n'); zabil('blog_$n',s+' #@#@#'); ".h($a)."

	$p=file($GLOBALS['filehost']."log/blogs.txt");
	$k=0; foreach($p as $l) {
		if(substr($l,0,1)=='#') $l=substr($l,1);
		if(trim($l)=='') continue;
		if(!strstr($l,' ')) $l=$l.' '.$l;
		list($url,$name)=explode(' ',$l,2);
		$o.="<div id='blog_".$k."'><b>".h($name)."</b>: &nbsp; &nbsp; <span id='blog_url_".$k."'>".h(trim($url,'/'))."</span></div>";
		$k++;
	}

	$script="var blognum=".$k.";
testblog=function(){ if(blognum==0) return; blognum--;
	idd('blog_'+blognum).style.background='#dd3300';
	var url=vzyal('blog_url_'+blognum);
	majax('module.php',{mod:'INSTALL',a:'do',module:'".RE('module')."',act:'blogtest',url:url,num:blognum});
	return false;
};
setTimeout('testblog()',200);
";

		$o.="
<input type='button' onclick='testblog()' value='test'>
";


/*
$starttime=time();

	while((time()-$starttime)<2 && $skip<$allwork) {
		usleep(100000);
		$skip+=57;
	}

	$o.=" ".$skip;
	if($skip<$allwork) return $skip;
	$delknopka=1;
	return 0;
*/
	return 0;
}

// Определяем общий объем предстоящей работы (напр. число позиций в базе для обработки).
// Если модуль одноразового запуска - вернуть 0.
function installmod_allwork() { return 10000; }

?>