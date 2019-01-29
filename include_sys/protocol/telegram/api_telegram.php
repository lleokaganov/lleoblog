<?php

// if(!isset($GLOBALS['telegram_API_key'])) die("\$telegram_API_key not defined in config.sys");
//define('BOT_TOKEN', $GLOBALS['telegram_API_key']);
//define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
// function tdie($s) { apiRequest("sendMessage", array('chat_id' => $GLOBALS['telegram_chat_id'], "text" => $s)); exit; }


function telegram_api_url() {
    if(empty($GLOBALS['telegram_API_key'])) idie('Error telegram api url');
    return 'https://api.telegram.org/bot'.$GLOBALS['telegram_API_key'].'/';
}

// ==================================================================================

function telegram_geturl($id,$user) { return "https://t.me/".$user."/".$id; }

function telegram_delete($id,$channel) { $e=apiRequest("deleteMessage", array( 'chat_id' => "@".$channel, 'message_id' => $id)); return 1; }

function telegram_post($channel,$s,$fotos='') { /*$fotos='';
    if(!empty($fotos)) {
	$foto=(gettype($fotos)=='string'?$fotos:$fotos[0]);
	$e=apiRequestJson("sendPhoto", array('chat_id' => "@".$channel, 'photo' => $foto, 'caption' => $s, 'parse_mode' => "HTML"));
	return intval($e['message_id']);
    }*/

    $e=apiRequest("sendMessage", array('chat_id' => "@".$channel, 'text' => wu($s), 'parse_mode' => "HTML"));
    return intval($e['message_id']);
}

function telegram_edit($id,$channel,$s,$fotos='') {
    $e=apiRequest("editMessageText", array('chat_id' => "@".$channel, 'message_id' => $id, 'text' => wu($s), 'parse_mode' => "HTML"));
    if(!isset($e['edit_date'])) idie("Edit error: ".nl2br(h(print_r($e,1))));
    return $id;
}

// ==================================================================================

function ferror_log($s) {
    if(function_exists('idie')) { idie(nl2br(h($s))); }
    error_log($s."\n");
    exit;
}


function exec_curl_request($handle) {
  $response = curl_exec($handle);

  if ($response === false) {
    $errno = curl_errno($handle);
    $error = curl_error($handle);
    ferror_log("Curl returned error $errno: $error");
    curl_close($handle);
    return false;
  }

  $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
  curl_close($handle);

  if($http_code >= 500) { // do not wat to DDOS server if something goes wrong
    sleep(10);
    return false;
  }

  if ($http_code != 200) {
    $response = json_decode($response, true);
    ferror_log("Request has failed with error {$response['error_code']}: {$response['description']}");
    if ($http_code == 401) { throw new Exception('Invalid access token provided'); }
    return false;
  }

  $r = json_decode($response, true);
  if(!isset($r['result']) || isset($r['description'])) {
      // error_log("Request was successful: {$response['description']}\n");
      return $response;
  }

  return $r['result'];
}




function apiRequest($method, $parameters) {
  if(!is_string($method)) ferror_log("Method name must be a string");
  if(!$parameters) { $parameters = array(); } elseif (!is_array($parameters)) ferror_log("Parameters must be an array");
  foreach ($parameters as $key => &$val) { // encoding to JSON array parameters, for example reply_markup
    if (!is_numeric($val) && !is_string($val)) { $val = json_encode($val); }
  }
  $url = telegram_api_url().$method.'?'.http_build_query($parameters);

  $handle = curl_init($url);
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  return exec_curl_request($handle);
}


function apiRequestJson($method, $parameters) {
  if(!is_string($method)) ferror_log("Method name must be a string");

  if(!$parameters) { $parameters = array(); } elseif (!is_array($parameters)) ferror_log("Parameters must be an array");

  $parameters["method"] = $method;
  $handle = curl_init(telegram_api_url());
  curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($handle, CURLOPT_TIMEOUT, 60);
  curl_setopt($handle, CURLOPT_POST, true);
  curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
  curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
  return exec_curl_request($handle);
}

/* https://core.telegram.org/bots/samples/hellobot */

?>