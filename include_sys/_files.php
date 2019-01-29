<?php // работа с файлами

// function testdir($s) { $a=explode('/',rtrim($s,'/')); $s=''; for($i=0;$i<sizeof($a);$i++) { $s.='/'.$a[$i]; if(!is_dir($s)) dirput($s); } }
// function getras($s){ $r=explode('.',$s); if(sizeof($r)==1) return ''; return strtolower(array_pop($r)); }

function feof_fp($fp) {
    $o=''; while(!feof($fp)) {
        if(false === ($q=fread($fp,8192))) idie("ERROR: feof"); // 4096
        if(!strlen($q)) break;
    $o.=$q;
    } fclose($fp);
    return $o;
}
function feof_load($url,$context) { if(false === ($fp=fopen($url,'r',false,$context))) idie("ERROR: Fopen #1 ".h($url)); return feof_fp($fp); }

// простой постинг, без файлов
function POST_data($urla,$ara,$uagent="Windows NT",$port=80,$scheme='http') {
        $url=array_merge(array('scheme'=>$scheme,'port'=>$port),parse_url($urla));
	$a=array(); if(is_array($ara)){ foreach($ara as $n=>$v) $a[]=$n.'='.urlencode($v); } $a=implode('&',$a);


        if(false === ($fp=fsockopen($url['host'],$url['port']))) return "ERROR: can't open url ".$url['host'].":".$url['port'];
        // запихнуть заголовок и POST-массив
        if(false === fputs($fp,"POST ".$url['path']." HTTP/1.1\r\nHost: ".$url['host']."\r\n".
		"User-Agent: $uagent\r\n".
		"Content-Type: application/x-www-form-urlencoded\r\n".
		"Content-Length: ".strlen($a)."\r\n".
		"Connection: close\r\n\r\n".$a)) return "ERROR: can't send #1";

        // и получить ответ
        $s=feof_fp($fp); if($s=='') return "ERROR: NO RESPONSE";

        list($h,$t)=explode("\r\n\r\n",$s,2);

        // обработка переноса
        if(stristr($h,'301 Moved Permanently')) {
                return POST_data(preg_replace("/^.+Location: ([^\s]+).*$/si","$1",$h),$ara,$uagent);
        }
	return $t;
}


//==================================================================================================
// процедура передачи данных и файлов через POST-запрос по старинке без всяких там уебищных CURL-библиотек
// $filePath - полное имя (с путем) файла для передачи или массив имен файлов для передачи (если файлов нет - '')
// $urla - адрес запроса, напр. http://lleo.aha.ru/blog/install
// $ara - массив переменных POST, напр: array('action'=>'do','key'=>'1','user'=>123)
// возвращает ответ сервера или, если ошибка, строку, начинающуюся с 'ERROR:'
function idiep($s) {
    if(!isset($GLOBALS["ajax"])) return $s;
    $s=h($s);
    $s=str_replace("\r","",$s);
    $s=str_replace("\n","<br>",$s);
    return "idie(\"".$s."\");";
}

function POST_file($filePath,$urla,$ara,$port=80,$scheme='http',$charset='Windows-1251') {

        if(gettype($filePath)!='array') $filePath=array($filePath);
        $url=array_merge(array('scheme'=>$scheme,'port'=>$port),parse_url($urla));
        $bu="---------------------".substr(md5($filePath.rand(0,32000)),0,10); $r="\r\n"; $ft=$r.'--'.$bu.'--'.$r;

        // данные
        $dat=''; if(count($ara)) foreach($ara as $n=>$v) $dat.='--'.$bu.$r.'Content-Disposition: form-data; name="'.$n
.'"'.$r.$r
//.$v
.urlencode($v)
.$r;

        $len=strlen($dat); // общая длина

        $files=array(); $k=0; foreach($filePath as $l) { if(empty($l)) continue;
                if(!is_file($l)) return "alert('ERROR: file not found ".h($l)."');";
                $fh='--'.$bu.$r
                .'Content-Disposition: form-data; name="file'.(++$k).'"; filename="'.urlencode(basename($l)).'"'.$r
                .'Content-Type: '.$charset.$r
                .$r;

                $len+=strlen($fh.$ft)+filesize($l);
                $files[$l]=$fh;
        }

        $headers="POST ".$url['path']." HTTP/1.0".$r
        ."Host: ".$url['host'].$r
//        ."Referer: ".$url['host'].$r
        ."Content-type: multipart/form-data, boundary=".$bu.$r
        ."Content-length: ".$len.$r
        .$r
        .$dat;

        // открыть хост
        if(false === ($fp=fsockopen($url['host'],$url['port']))) {
	    return idiep("can't open url: ".$url['host'].":".$url['port']."\n(BTW, try to open http://ya.ru - ".(file_get_contents('http://ya.ru')==false?'error too!':'success').")");
	}

        // запихнуть заголовок и POST-массив
        if(fputs($fp,$headers)===false) return idip("ERROR: can't send #1");
        if(count($files)) foreach($files as $l=>$fh) { // позапихивать файлы
                if(fputs($fp,$fh)===false) return idiep("ERROR: can't send #2");
                // открыть файл и запихнуть его
                if(($fp2=fopen($l,"rb"))===false) return idiep("ERROR: can't open file '".$l."'");
                while(!feof($fp2)) {
		    if(false===($a=fgets($fp2,4096))) break; // return idiep("ERROR: can't send #4.fgets");
		    if(false===(fputs($fp,$a))) return idiep("ERROR: can't send #4.fputs");
		} fclose($fp2);
                // запихнуть заключительный хедер
                if(fputs($fp,$ft)===false) return idiep("ERROR: can't send #5");
        }
        // и получить ответ
	$s=feof_fp($fp); if($s=='') { return idiep("ERROR: NO-RESPONSE ".h($url['host'].":".$url['port'])); }

        list($h,$t)=explode($r.$r,$s,2);

        // обработка переноса
        if(stristr($h,'301 Moved Permanently')) {
                return POST_file($filePath,preg_replace("/^.+Location: ([^\s]+).*$/si","$1",$h),$ara);
        }

	if(isset($GLOBALS["ajax"])&&$GLOBALS["ajax"]==2) $t=preg_replace("/^.*?\/\*start_js_code\*\//s",'',$t);

// return idiep($t);
// ifdebug(array("FILE_post OK: ",$s));
return $t;
}


// отправить в ответ просто файл

function Exit_SendFILE($file,$s=false,$r=1) {
$mimetypes=array(
	'jpg'=>'image/jpeg',
	'jpeg'=>'image/jpeg',
	'gif'=>'image/gif',
	'png'=>'image/png',
	'bmp'=>'image/bmp',

	'mp3'=>'audio/mp3',
	'wav'=>'audio/wav',

	'mid'=>'audio/midi',
	'txt'=>'text/plain'
); $mime=$mimetypes[getras(basename($file))];
if(empty($mime)) $mime='application/octet-stream'; elseif($r==3) $r=0;

header('Content-Description: File Transfer');
header('Content-Type: '.$mime);
if($r) header('Content-Disposition: attachment; filename="'.basename($file).'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: '.filesize($file));
ob_clean(); flush();
if($s!==false) die($s);
readfile($file);
exit;
}

function fileget_timeout($url,$timeout=10) {

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/533.2 (KHTML, like Gecko) Chrome/5.0.342.3 Safari/533.2');
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    $s=curl_exec($ch);
    // $info = curl_getinfo($ch);
    curl_close($ch);
    return $s;

/*
    $u=array_merge(array('scheme'=>'http','host'=>'www.ru','port'=>80,'path'=>'/'),parse_url($url));
    if(!($fp=fsockopen($u['host'],$u['port'],$errno,$errstr,$timeout))) return false;
    $o="GET ".$u['path'].(isset($u['query'])?"?".$u['query']:'')." HTTP/1.0\r\n"
	."Host: ".$u['host']."\r\n"
	."User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1) Gecko/20021204\r\n"
// ."Referer: http://$sDomain/\r\n";
//."Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*"."/"."*;q=0.8"."\r\n"
//."Accept-Encoding: gzip, deflate, br"."\r\n"
//."Accept-Language: en-US,en;q=0.5"."\r\n"
//."Connection: keep-alive"."\r\n"
//."Cookie: __cfduid=dbf0785289937b0243ec2902b4a3e30dd1530577252"."\r\n"
// ."Host: blockchain.info"."\r\n"
//."Upgrade-Insecure-Requests: 1"."\r\n"
// ."User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:59.0) Gecko/20100101 Firefox/59.0"."\r\n"
."Connection: Close\r\n"
."\r\n";
    fwrite($fp,$o); stream_set_timeout($fp,$timeout);
    $s=''; while(!feof($fp) && (($r=stream_get_meta_data($fp))||1) && !$r['timed_out']) { if(false===($a=fgets($fp,4096)))

return '';
// idie('fgets error #003');

$s.=$a; } fclose($fp);
    if($s=='') return false; // return $s;

    list($h,$t)=explode("\r\n\r\n",$s,2);

    if(stristr($h,"Content-Encoding: gzip")) $t=gzinflate(substr($t,10));

    if(stristr($h,'301 Moved Permanently')) return fileget_timeout(preg_replace("/^.+Location: ([^\s]+).*$/si","$1",$h),$timeout);
    return $t;
*/

}
?>