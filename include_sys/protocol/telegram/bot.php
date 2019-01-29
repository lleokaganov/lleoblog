<?php

$GLOBALS['TELEBOT_salt']="PRXpodzalupnaya";

include_once "../../../config.php";

$BOT=(isset($_GET['bot'])?intval($_GET['bot']):0);

ldie('DDD: '.$_SERVER['QUERY_STRING']); // $GLOBALS['telegram_API_key']);

// if(!isset($GLOBALS['telegram_API_key'])) die("\$telegram_API_key not defined in config.sys");
if(!isset($GLOBALS['telegram_IP'])) tdie("BOT ERROR: set telegram IP in config.php: \$telegram_IP='149.154.167.204';");
include_once "api_telegram.php";
include("patch.php"); msq_connect();

$GLOBALS['telegram_API_key']=DD('tg_API_key');
// phpinfo();
// ldie('DDD: '.$GLOBALS['telegram_API_key']);

function ldie($s) { logi("--> LDIE: ".$s."\n\n"); die($s); }
function logi($s,$n="super_logi.txt",$a="a+") { $l=fopen($n,$a); fputs($l,$s); fclose($l); chmod($n,0666); }

    $content = file_get_contents("php://input");
    $E = json_decode($content, true);
    logi("========== ".date("Y-m-d H:i:s")." ==========\n");
    logi(print_r($_SERVER,1));
    logi("\n--------- content -------------- ".$content." ------------------\n");
    logi("\n--------- JSON ------------- ".print_r($E,1)." ---------==---------\n");
    logi("\n--------- POST ------------- ".print_r($_POST,1)." ---------==---------\n");
    logi("\n--------- GET ------------- ".print_r($_GET,1)." ---------==---------\n");
    logi("\n--------- REQUEST ------------- ".print_r($_REQUEST,1)." ---------==---------\n");

if(isset($_GET['hook'])) { $a=$_GET['hook'];

        if($a=='test') ldie('OK');

	if($a=='zilla') {

	    if(!isset($_POST['p_chat_id']) || !isset($_POST['p_text'])) ldie("Error Zilla");
	    $chat=intval($_POST['p_chat_id']); if(!$chat) ldie("Chat_id error");
	    $GLOBALS['telegram_chat_id']=$userid=intval($_GET['userid']); if(!$userid) ldie("userid error");
	    if($_GET['md5'] != secretsha($GLOBALS['BOT'],$_GET['chat'],$_GET['userid']) ) ldie("Security error");
	    $s=$_POST['p_text'];
	    $s=trim($s);
	    $s=str_ireplace(
		array('<br>'),
		array("\n")
	    ,$s);

	    if(! intval(ms("SELECT COUNT(*) FROM `telezil_messages` WHERE `bot`='".e($BOT)."' AND `user`=0 AND `chat`='".e($chat)."'",'_l')) ) {
		$p=ms("SELECT `text`,`time` FROM `telezil_messages` WHERE `bot`='".e($BOT)."' AND `chat`='".e($chat)."'",'_a');
		foreach($p as $l) { $x=zilla_send_message($chat,/*$l['time'].": ".*/$l['text']); }
	    }

	    // теперь добавить сообщение ответа в базу
	    msq_add('telezil_messages',arae(array('bot'=>$BOT, 'user'=>0, 'chat' => $chat, 'text'=>$s))); if($msqe!='') tdie('Error MySQL #5 message save '.$msqe);
	    tdie($s);
	}

	ldie("Unknown Error");
}

function tdie($s,$opt=false) { $ara=array('chat_id' => $GLOBALS['telegram_chat_id'], "text" => $s, "parse_mode"=>"HTML");
    if($opt) $ara['reply_markup']=$opt;
apiRequest("sendMessage",$ara); exit; }

if(!$E) { exit; }   // receive wrong update, must not happen
if(!isset($E["message"])) { exit; }

$message_id = $E['message']['message_id'];
$GLOBALS['telegram_chat_id'] = $chat_id = $E['message']['chat']['id'];

    if(isset($E['message']['contact']['phone_number'])) {
	$E['message']['text']="+".$E['message']['contact']['phone_number'];
	// tdie("Tel: +".$E['message']['contact']['phone_number']);
    }


    if(!isset($E['message']['text'])) {
/*
            [contact] => Array
                (
                    [phone_number] => 79166801685
                    [first_name] => Leonid
                    [last_name] => Kaganov
                    [user_id] => 151852904
                )
*/


	apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => "Я понимаю только текстовые сообщения"));
	return;
    }

$text = $E['message']['text'];
$from = $E['message']['from']['username'];
$userid = $E['message']['from']['id'];
$username = $E['message']['from']['first_name']." ".$E['message']['from']['last_name'];

//========================================================

/*
if(substr($text,0,5) == "ping ") {
    list($cmd,$name)=explode(' ',$text);
    $nm=preg_replace("/[^a-z0-9\-\_\@]+/s",'',$name); if($nm=='') tdie("Error name: ".$name);


//        $e=apiRequest("sendMessage", array(
//<------><------>'chat_id' => "@lleokaganovdnevnik",
//<------><------>'text' => $s,
//<------><------>'parse_mode' => "HTML")); $id=intval($e['message_id']);

// 226578334 dmitystu
// 151852904 lleo
    if($nm=='lleo') $nm=151852904;
    elseif($nm=='chuk') $nm=226578334;
    elseif($nm=='f') $nm=131804611; // ильин владимир


    $e=apiRequestJson("sendMessage", array(
        'chat_id' => $nm,
        'parse_mode' => "HTML",
        'text' => "Это я, LLeo Kaganov, бота настраиваю, привет тебе $nm, если видишь меня!",
        'reply_markup' => array(
            'keyboard' => array(array('ВИЖУ!', 'Не вижу!')),
            'one_time_keyboard' => true,
            'resize_keyboard' => true
        ))); $id=intval($e['message_id']);
    file_put_contents('ping.txt',print_r($e,1));
    tdie("Ping message send:".$id);
}
*/

//========================================================

if(strpos($text,"/start") === 0) {
    apiRequestJson("sendMessage", array('chat_id' => $chat_id,
	    "text" =>  "Приветствую тебя в моем боте!",
	    'reply_markup' => array(
	            'keyboard' => array(array('Hello', 'Hi')),
	            'one_time_keyboard' => true,
	            'resize_keyboard' => true
        )));
    return;
}

// if(strpos($text, "/stop") === 0) { return; } // stop now
if(stripos($text,"x") === 0) {

tdie("<b>bold</b>, <strong>bold</strong>
<i>italic</i>, <em>italic</em>
<a href='http://lleo.me'>inline URL</a>
<code>inline fixed-width code</code>
<pre>pre-formatted fixed-width code block</pre>", // false
array(
"keyboard" => array(array(
    array("text" => "/button"),
    array("text" => "contact1", "request_contact" => true),
    array("text" => "location1", "request_location" => true)
)),
"one_time_keyboard" => true, // можно заменить на FALSE,клавиатура скроется после нажатия кнопки автоматически при True
"resize_keyboard" => true // можно заменить на FALSE, клавиатура будет использовать компактный размер автоматически при True
/*
"parse_mode" => "Markdown",
"reply_markup" => array(
            "one_time_keyboard" => true,
            "keyboard" => array(
			    array("text" => "My phone number", "request_contact" => true),
			    array("Cancel")
			)
	    )
*/
));
}

if(strpos($text,"/logon") === 0) { touch("log.flag"); tdie("log ON"); } // включить логи
if(strpos($text,"/logoff") === 0) { unlink("log.flag"); tdie("log OFF"); } // выключить логи

// ================================== ZILLA =======================================================================
// https://groosha.gitbooks.io/telegram-bot-lessons/content/chapter9.html
// https://docs.google.com/document/d/1f7RoP3itvSfjIySh56aCGuYBkc25rX2eYWkeKaponac/edit?ts=5bb397aa

    $LOG=array();
    $sended=0;
    $created=0;

    // проверим, есть ли юзер
    $userdat=mus("SELECT `user`,`bot`,`date`,`nick`,`name` FROM `telezil_users` WHERE `id`='".e($userid)."'","_1");
    if($userdat===false) { // если не было - добавить

	msq_add('telezil_users',arae(array('bot'=>$BOT, 'id'=>$userid, 'nick'=>$from, 'name'=>$username))); if($msqe!='') tdie('Error MySQL useradd #2 '.$msqe);
	$user=msq_id(); if(!$user) tdie('Error MySQL user #1 '.$msqe);
	$LOG[]="New user [$user] created";
	$chat=0;

    } else {
	$user=$userdat['user'];
	$LOG[]="Old user detected: ".$user;
	// юзер был - взять его последний чат
        $chat=intval(mus("SELECT `chat` FROM `telezil_messages` WHERE `bot`='".e($BOT)."' AND `user`='".e($user)."' ORDER BY `time` DESC LIMIT 1","_l"));
	$LOG[]="Last chat #".$chat;

	// есть ответы в этом чате - попытаться отправить
	if( intval(mus("SELECT COUNT(*) FROM `telezil_messages` WHERE `bot`='".e($BOT)."' AND `user`=0 AND `chat`='".e($chat)."'",'_l'))
	    || intval(mus("SELECT COUNT(*) FROM `telezil_messages` WHERE `bot`='".e($BOT)."' AND `user`!=0 AND `chat`='".e($chat)."'",'_l')) > 1
	    ) {
	    $LOG[]="Try to send #".$chat;
	    if( zilla_send_message($chat,$text) ) { // если удалось
		$LOG[]="Nessage sended to #".$chat;
		$sended=1;
	    } else { // не удалось отправить
		$LOG[]="Can't send message to #".$chat;
		$chat=0;
	    }
	} else {
	    $LOG[]="No answers, collect one message to #".$chat;
	}
    }

    if(!$chat) { // не удалось отправить? создать новый чат
	$chat=zilla_chat_create($message_id,$userid,$username); if(!$chat) { if(strstr($x,'403 Forbidden') && strstr($x,'(Field: UserId)')) tdie("Chat ID Error"); tdie("Error: ".$x); }
	$created=1;
	$LOG[]="Chat created: #".$chat;
    }

    // добавить сообщение в базу
    msq_add('telezil_messages',arae(array('bot'=>$BOT, 'user'=>$user, 'chat' => $chat, 'text'=>$text))); if($msqe!='') tdie('Error MySQL #5 message save '.$msqe);
    $messageid=msq_id();
    $LOG[]="Message #".$messageid." stored for #".$chat;

    if($created) {
	tdie("Открыт чат #".$chat.", вам ответит первый недавно освободившийся оператор.");
    }

    // конец работы
    if(is_file("log.flag")) tdie( implode("\n",$LOG) );
    die();



function xpost($url,$post) { // $s=wu($s); ,$auth=false
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL, $url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch,CURLOPT_HTTPAUTH,CURLAUTH_BASIC);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
    curl_setopt($ch,CURLOPT_TIMEOUT,5); // 10 sec
    $o=curl_exec($ch); // $info=curl_getinfo($ch);
    curl_close($ch); // $a=(array)json_decode($output);
    return $o;
}

function zilla_send_message($chat,$s) { // $s=str_replace('"','\"',$s); $s=str_replace("'","\'",$s); $s=str_replace("\r","",$s); $s=str_replace("\t"," ",$s); $s=str_replace("\n"," <enter> ",$s);
	$ara=array("Message" => array("ChatId" => intval($chat), "Text" => $s));
	$d=json_encode($ara);
logi("\n\nPOSLANO: ".$d);
	$x=xpost(DD('lz_url'),http_build_query(array('p_user'=>DD('lz_login'),'p_pass'=>DD('lz_pass'),
	    'p_chat_add_message'=>1,
	    'p_data'=> $d // '{"Message": {"ChatId": "'.intval($chat).'","Text": "'.$s.'"}}'
	)));
        return ( strstr($x,'403 Forbidden (Error:' ) ? false : $x); //)
}

function secretsha($BOT,$chat,$userid) { return substr(sha1($GLOBALS['TELEBOT_salt']." $BOT,$chat,$userid"),5,10); }

function zilla_chat_create($message_id,$userid,$username) {
    $x=xpost(DD('lz_url'),http_build_query(array('p_user'=>DD('lz_login'),'p_pass'=>DD('lz_pass'),
	'p_chat_create'=>1,
	'p_data'=>json_encode(array("Chat" => array(
    // "Email": "john@doe.com",
    // "Company": "Doe Ltd.",
    // "Phone": "004977311894432",
    // "Customs": "",
    // "Operator": "fc6a5761d39598c",
    // "Country": "ES",
    // "Language": "DE",
    // "Identifier": "This is an API chat",
    // "IP": "111.111.111.111",
    "Fullname" => $username,
    "UserId" => $message_id,
    "Group" => DD('lz_group'),
    "Webhook" => $_SERVER["SCRIPT_NAME"]."?bot=".$GLOBALS['BOT']."&hook=zilla&chat=".$message_id."&userid=".$userid."&md5=".secretsha($GLOBALS['BOT'],$message_id,$userid)
    )
    ))
    )));
// '{"Chat": {"UserId": "'.$message_id.'","Group": "support","Webhook": "'.str_replace('/','\/',"https://lleo.me/dnevnik/include_sys/protocol/telegram/bot.php?bot=0&hook=zilla&chat=".$message_id."&userid=".$userid."&md5=".$secret).'"}}'
        $ar=json_decode($x);
logi("\n\nCHAT_CREATED: ".$x);
logi("\nCHAT_CREATED: ".print_r($ar,1));
        return intval($ar->{Chats}[0]->{Chat}->{ChatId}); // [UserId] => 3 [SystemId] => 3~95479b7198eb7cd5
}

function mus($sql,$l="_a") {
    $p=ms($sql,$l);
    if($GLOBALS['msqe']=='') return $p;
    tdie('Error MySQL ['.$sql.']');
}

function DD($n) { global $DDAT; $i=intval($GLOBALS['BOT']);
    if(!isset($DDAT)) { if(0===$i || false==($DDAT=ms("SELECT * FROM `telezil_scenary` WHERE `i`='".$i."'",'_a'))) tdie("Error: DDAT BOT=".$i); } // ���� ��� ������� - ���������
    if(isset($DDAT[$n])) return $DDAT[$n];
    tdie("Error: BOT=".$i." n=".h($n)." DDAT not found");



/*
-- Структура таблицы `telezil_scenary`
CREATE TABLE IF NOT EXISTS `telezil_scenary` (
    `i` smallint(10) unsigned NOT NULL auto_increment COMMENT 'id сценария',
    `project_id` smallint(10) unsigned NOT NULL COMMENT 'id проекта, к которому он относится',
    `scenary_name` varchar(256) COMMENT 'Имя сценария',
    `date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP COMMENT 'время создания',

//        `lz_url` varchar(256) COMMENT 'URL сервера', // http://livezilla8.list24.ru/api/v2/api.php'
//        `lz_login` varchar(256) COMMENT 'login',  // 'kaganov',
//        `lz_pass` char(32) COMMENT 'Позорище',// '747e1bda2017018f55719d74916166c8',
//        `lz_group` varchar(128) COMMENT 'Группа', // "support",
//        `lz_user` varchar(128) COMMENT 'Пользователь',
//        `lz_lang` varchar(5) COMMENT 'Язык по умолчанию',
//        `lz_err_message` varchar(512) COMMENT 'Сообщение о недоступности Партнера',

    `tg_API_id` bigint(20) unsigned COMMENT 'ИД бота telegram_API_myid',
    `tg_API_key` varchar(45) COMMENT 'Ключ API бота telegram_API_key',
    `tg_name` varchar(32) COMMENT 'Имя бота',
    `tg_info` varchar(512) COMMENT 'Инфо бота',
    `tg_image` varchar(128) COMMENT 'УРЛ картинки бота',
    `tg_err_message` varchar(512) COMMENT 'Сообщение о недоступности Партнера',
    `tg_wait_message` varchar(512) COMMENT 'Текст на ожидании',
        `command_list` text COMMENT 'Список команд',
        `keywords` text COMMENT 'текст (действие)',
        `name_template` varchar(128) COMMENT 'Настройка формирования имени пользователя',
        `banlist` text COMMENT 'Бан-листы абонентов На основании user_id',
PRIMARY KEY (`i`),
KEY `project_id` (`project_id`)
) ENGINE=XtraDB default CHARSET=utf8 COMMENT='база телеграм-юзеров' ;
*/
}

?>