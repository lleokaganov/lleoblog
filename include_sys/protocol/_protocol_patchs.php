<?php
/*
function file_get_contents_https($url,$i='') { $ch=curl_init();
    curl_setopt($ch, CURLOPT_URL,$url); curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    if($i!='') curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$i);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,2); curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $e=curl_exec($ch); curl_close($ch); return $e;
}
*/

function curlpost($url,$a) {
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$a);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    $e=curl_exec($ch); curl_close($ch); return $e;
}

if(!function_exists('curl_file_create')) {
    function curl_file_create($filename,$mimetype='',$postname='') {
        return "@$filename;filename=".($postname?$postname:basename($filename)).($mimetype?";type=$mimetype":'');
    }
}


if(!function_exists('hash_hmac')) {
	function hash_hmac($algo, $data, $key, $raw_output = false) {
	    $packs=array('md5'=>'H32','sha1'=>'H40');
	    if( !isset($packs[$algo]) ) return false;
	    $pack = $packs[$algo];
	    if (strlen($key) > 64) $key = pack($pack, $algo($key));
	    $key = str_pad($key, 64, chr(0));
	    $ipad = (substr($key, 0, 64) ^ str_repeat(chr(0x36), 64));
	    $opad = (substr($key, 0, 64) ^ str_repeat(chr(0x5C), 64));
	    $hmac = $algo($opad . pack($pack, $algo($ipad . $data)));
	    if ( $raw_output ) return pack( $pack, $hmac );
	    return $hmac;
	}
}


	function json_decode1( $string, $assoc_array = false ) {
                global $wp_json;
                if ( !is_a($wp_json, 'Services_JSON') ) {
                        require_once($GLOBALS['include_sys'].'class-json.php');
                        $wp_json = new Services_JSON();
                }
                $res = $wp_json->decode( $string );
                if ( $assoc_array ) $res = _json_decode_object_helper( $res );
                return $res;
	}

if(!function_exists('json_decode')) { function json_decode( $string, $assoc_array = false ) {
	    return json_decode1( $string, $assoc_array);
	}
	function _json_decode_object_helper($data) {
	        if ( is_object($data) )  $data = get_object_vars($data);
	        return is_array($data) ? array_map(__FUNCTION__, $data) : $data;
	}
}

/* ===================== Костыли для json ============================ */

if(!function_exists('json_encode')) {
    function json_encode($data) {
        switch ($type = gettype($data)) {
            case 'NULL':
                return 'null';
            case 'boolean':
                return ($data ? 'true' : 'false');
            case 'integer':
            case 'double':
            case 'float':
                return $data;
            case 'string':
                return '"' . addslashes($data) . '"';
            case 'object':
                $data = get_object_vars($data);
            case 'array':
                $output_index_count = 0;
                $output_indexed = array();
                $output_associative = array();
                foreach ($data as $key => $value) {
                    $output_indexed[] = json_encode($value);
                    $output_associative[] = json_encode($key) . ':' . json_encode($value);
                    if ($output_index_count !== NULL && $output_index_count++ !== $key) {
                        $output_index_count = NULL;
                    }
                }
                if ($output_index_count !== NULL) {
                    return '[' . implode(',', $output_indexed) . ']';
                } else { return '{' . implode(',', $output_associative) . '}'; }
            default: return ''; // Not supported
        }
    }
}

?>