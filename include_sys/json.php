<?php // ÅÁÀÍÛÉ ÑÓ×ÈÉ JSON ÊÀÊ ß ÅÃÎ ÍÅÍÀÂÈÆÓ ÃÎÂÍÎ ÑÐÀÍÎÅ

/*
function uXXX($t) {
return "|".iconv("utf-8","windows-1251//IGNORE",chr("0x".$t[1]))."|";
$s=chr(base_convert(substr($t[1],0,2),16,10)).chr(base_convert(substr($t[1],2),16,10));
return iconv("utf-8","windows-1251//IGNORE","|$s|");
return iconv("utf-8","windows-1251//IGNORE",chr(base_convert($t[1],16,10)));
}
*/

function jdecoder($json_str){ $cyr_chars = array (
         '\u0430' => 'à', '\u0410' => 'À',
         '\u0431' => 'á', '\u0411' => 'Á',
         '\u0432' => 'â', '\u0412' => 'Â',
         '\u0433' => 'ã', '\u0413' => 'Ã',
         '\u0434' => 'ä', '\u0414' => 'Ä',
         '\u0435' => 'å', '\u0415' => 'Å',
         '\u0451' => '¸', '\u0401' => '¨',
         '\u0436' => 'æ', '\u0416' => 'Æ',
         '\u0437' => 'ç', '\u0417' => 'Ç',
         '\u0438' => 'è', '\u0418' => 'È',
         '\u0439' => 'é', '\u0419' => 'É',
         '\u043a' => 'ê', '\u041a' => 'Ê',
         '\u043b' => 'ë', '\u041b' => 'Ë',
         '\u043c' => 'ì', '\u041c' => 'Ì',
         '\u043d' => 'í', '\u041d' => 'Í',
         '\u043e' => 'î', '\u041e' => 'Î',
         '\u043f' => 'ï', '\u041f' => 'Ï',
         '\u0440' => 'ð', '\u0420' => 'Ð',
         '\u0441' => 'ñ', '\u0421' => 'Ñ',
         '\u0442' => 'ò', '\u0422' => 'Ò',
         '\u0443' => 'ó', '\u0423' => 'Ó',
         '\u0444' => 'ô', '\u0424' => 'Ô',
         '\u0445' => 'õ', '\u0425' => 'Õ',
         '\u0446' => 'ö', '\u0426' => 'Ö',
         '\u0447' => '÷', '\u0427' => '×',
         '\u0448' => 'ø', '\u0428' => 'Ø',
         '\u0449' => 'ù', '\u0429' => 'Ù',
         '\u044a' => 'ú', '\u042a' => 'Ú',
         '\u044b' => 'û', '\u042b' => 'Û',
         '\u044c' => 'ü', '\u042c' => 'Ü',
         '\u044d' => 'ý', '\u042d' => 'Ý',
         '\u044e' => 'þ', '\u042e' => 'Þ',
         '\u044f' => 'ÿ', '\u042f' => 'ß',
         '\r' => '',
//         '\n' => '<br />',
         '\t' => ''
     );
  
     foreach ($cyr_chars as $key => $value) {
         $json_str = str_replace($key, $value, $json_str);
     }
     return $json_str;
}
  
function jsonDecode($json) {
	$json = str_replace('\\/','/',$json);
	$json = jdecoder($json);
	$json = str_replace(array("\\\\", "\\\""), array("&#92;", "&#34;"), $json);


      $parts = preg_split("@(\"[^\"]*\")|([\[\]\{\},:])|\s@is", $json, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);


//return $parts;
      foreach($parts as $i=>$part) {
          if(strlen($part) == 1) {
              switch ($part) {
                  case "[": case "{": $parts[$i] = "array("; break;
                  case "]": case "}": $parts[$i] = ")"; break;
                  case ":": $parts[$i] = "=>"; break;   
                  case ",": break;
                  default: return false; //array('e'=>$part);
              }
          } 
          else {
		if($part=="null") $parts[$i] = "\"\"";
		else if((substr($part,0,1) != '"') || (substr($part,-1,1) != '"')) $parts[$i]='"'.trim($parts[$i]).'"';
	}
//return null;
      }

      $json = str_replace(array("&#92;", "&#34;", "$"), array("\\\\", "\\\"", "\\$"), implode("", $parts));

//return array('dd'=>$json);
      return eval("return $json;");
}
?>