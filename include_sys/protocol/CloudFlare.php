<?php // ClaudFlare

/*
function idie($s) { die($s); }

$dyndns_clowdflare_mail='lleo@lleo.me';
$dyndns_clowdflare_token='28478569e80651d6a423f72f8ae01e9db8d34a7';

// $CloudFlareDEBUG=0;

$o='';

$o.="\n".cloud_add_items('gomel','binoniq.net','CNAME',120,'true');
$o.="\n".cloud_add_items('www.gomel','binoniq.net','CNAME',120,'true');

// $o.="\n".cloud_del_items('gomel','binoniq.net');
// $o.="\n".cloud_del_items('www.gomel','binoniq.net');

die($o."\n");

*/

//==================================================================
// РІР·СЏС‚СЊ СЃРїРёСЃРѕРє РґРѕРјРµРЅРѕРІ РЅР° Р°РєРєР°СѓРЅС‚Рµ
function cloud_get_domains() { global $ClaudFlare_DOMAINS; $o="cloud_get_domains:";
    if(isset($ClaudFlare_DOMAINS)) return $o." passed";
    $p=cufind(); if(gettype($p)!='array') return $p;
    $ClaudFlare_DOMAINS=array(); foreach($p['result'] as $i=>$e) { $e=(array)$e;
	$ClaudFlare_DOMAINS[$e['name']]=$e['id'];
	$o.="\n     ".$e['name']." ".$e['id']." ".$e['status'];
    }
    return $o."\n";
}

function cloud_get_items($site) { global $ClaudFlare_DOMAINS,$ClaudFlare_ITEMS; $o="cloud_get_itemss:";
    if(!isset($ClaudFlare_DOMAINS)) $o.=cloud_get_domains();
    if(!isset($ClaudFlare_DOMAINS[$site])) idie($o." Error: site not found ".$site);
    $id=$ClaudFlare_DOMAINS[$site];
    $p=cufind($id."/dns_records"); if(gettype($p)!='array') return $p;
    $ClaudFlare_ITEMS=array(); foreach($p['result'] as $i=>$e) { $e=(array)$e;
	$ClaudFlare_ITEMS[$e['name']]=$e['id'];
	$o.="\n               ".$e['name']." ".$e['type']." ".$e['id'];
    }
    return $o."\n";
}

function cloud_del_items($acc,$site) { global $ClaudFlare_DOMAINS,$ClaudFlare_ITEMS; $o="cloud_del_items:";
    if(!isset($ClaudFlare_DOMAINS)) $o.=cloud_get_domains();
    if(!isset($ClaudFlare_DOMAINS[$site])) idie($o." Error: site not found: ".$site);
	$id=$ClaudFlare_DOMAINS[$site];
    if(!isset($ClaudFlare_ITEMS)) $o.=cloud_get_items($site);
    $o.=" $acc.$site";
    if(!isset($ClaudFlare_ITEMS["$acc.$site"])) return $o.=" - Nothing To Do (DELETED)";
	$iid=$ClaudFlare_ITEMS["$acc.$site"];
    $p=cufind("$id/dns_records/$iid",'','DELETE'); if(gettype($p)!='array') return $p;
    $p=(array)$p['result']; $id=$p['id'];
    unset($ClaudFlare_ITEMS["$acc.$site"]);
    return $o.=" DELETED: ".$id;
}

function cloud_add_items($acc,$site,$type='CNAME',$ttl=120,$proxied='false') { global $ClaudFlare_DOMAINS,$ClaudFlare_ITEMS; $o="cloud_add_items:";
    if(!isset($ClaudFlare_DOMAINS)) $o.=cloud_get_domains();
    if(!isset($ClaudFlare_DOMAINS[$site])) idie($o.=" Error: site not found: ".$site);
	$id=$ClaudFlare_DOMAINS[$site];
    if(!isset($ClaudFlare_ITEMS)) $o.=cloud_get_items($site);
    $o.=" $acc.$site";
    if(isset($ClaudFlare_ITEMS["$acc.$site"])) return $o.=" - Nothing To Do (ADDED)";
    $json='{"type":"'.$type.'","name":"'.$acc.'","content":"'.$site.'","ttl":'.$ttl.',"proxied":'.$proxied.'}';
    $p=cufind("$id/dns_records",'','POST',$json); if(gettype($p)!='array') {
    if(strstr($p,'record already exists')) $p="Domain http://$acc.$site already exist on ClaudFlare";
    return $p;
    }
    $p=(array)$p['result']; $id=$p['id'];
    $ClaudFlare_ITEMS["$acc.$site"]=$id;
    return $o.=" ADDED: ".$id;
}

function cufind($url='',$name='*',$get='GET',$data='') {
    if(!isset($GLOBALS['dyndns_clowdflare_mail']) || !isset($GLOBALS['dyndns_clowdflare_token']))
	idie("Error: setup config.php:\n\$dyndns_clowdflare_mail=\n\$dyndns_clowdflare_token=");
// === curl ===
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,"https://api.cloudflare.com/client/v4/zones/".$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HTTPHEADER,array(
	"X-Auth-Email: ".$GLOBALS['dyndns_clowdflare_mail'],
	"X-Auth-Key: ".$GLOBALS['dyndns_clowdflare_token'],
	"Content-Type: application/json"
    )); // Массив устанавливаемых HTTP-заголовков, в формате array('Content-type: text/plain', 'Content-length: 100')
    if($get!='GET') { curl_setopt($ch,CURLOPT_POST,1); curl_setopt($ch,CURLOPT_POSTFIELDS,$data); }
    $e=(array)json_decode(curl_exec($ch));
    curl_close($ch);
// === curl ===
//	"curl -s -X ".$get." \"https://api.cloudflare.com/client/v4/zones/".$url."\""
//	." -H \"X-Auth-Email: ".$GLOBALS['dyndns_clowdflare_mail']."\""
//	." -H \"X-Auth-Key: ".$GLOBALS['dyndns_clowdflare_token']."\""
//	.($data!=''?" -H \"Content-Type: application/json\" --data '$data'":'')
    if(isset($GLOBALS['CloudFlareDEBUG'])) file_put_contents('stage_'.(++$GLOBALS['CloudFlareDEBUG']).".txt",print_r($e,1));
    if(isset($e['errors'])&&!empty($e['errors'])) { $s=''; foreach($e['errors'] as $p) { $p=(array)$p; $s.=$p['message']."\n"; } return "Error: ".$s; }
    if(!isset($e['success'])||$e['success']!=1) return "Error: No success";
    if(!isset($e['result'])) idie("Error: No result");
    return $e;
}

?>