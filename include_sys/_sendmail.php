<?php // if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй

/*
// sendmail_service
if(isset($_POST['ssp'])) { include "../config.php";
die("Hole under construction");
if(isset($sendmail_gate_pass)) die(''.($_POST['ssp']==$sendmail_gate_pass?intval(
sendmail($_POST['from_name'],$_POST['from_adr'],$_POST['to_name'],$_POST['to_adr'],$_POST['subj'],$_POST['text'])):0));
    die(''.($sendmail_service_pass==$_POST['ssp']?intval(mail($_POST['to'],$_POST['subj'],$_POST['text'],$_POST['headers'],"-f".$_POST['from_addr'])):0));
}
*/

function mail_answer($id,$ara,$p,$m) { //------------------- коммент по емайл

$sys=array(
    'unic'=>$GLOBALS['unic'],
    'httphost'=>$GLOBALS['httphost'],
    'mail_system'=>$GLOBALS['admin_mail'],
    'mail'=>get_workmail($GLOBALS['IS']),
    'mail_parent'=>$m,
    'name'=>$GLOBALS['imgicourl'],
    'name_parent'=>$p['imgicourl'],
    'img'=>hh($GLOBALS['IS']['img']),
    'img_parent'=>hh($p['img']),
    'head'=>h(strip_tags(strtr($p['Date'],'/','-')." ".$p['Header'])),
    'date'=>$p['Date'],
    'admin_name'=>$GLOBALS["admin_name"],
    'link'=>h(getlink($p['Date'])."#".$id),
    'date'=>date('Y-m-d H:i',$ara['Time']),
    'date_parent'=>date('Y-m-d H:i',$p['Time']),
    'text_parent'=>h($p['Text']),
    'text'=>h($ara['Text'])
); $sys['subj']=$sys['httphost'].": ".imgicourl_text($sys['name'])." reply to you ".$sys['head'];

$sys['confirm']=substr(sha1($sys['date'].'|'.$sys['mail_parent'].'|'.$GLOBALS['newhash_user']),1,16); // для мгновенной отписки

$tmpl=get_sys_tmp("mail_send.htm"); $s=mpers($tmpl,$sys);

return ((true===($i=sendmail(
    imgicourl_text($sys['name']),'noreply@'.$_SERVER['HTTP_HOST'], // $sys['mail_system'], // ($sys['mail']?$sys['mail']:$sys['mail_system']),
    imgicourl_text($sys['name_parent']),$sys['mail_parent'],
$sys['subj'],$s)))?$sys:0);

}

//=========================================

function send_mail_confirm($mail,$realname='') { if($realname=='') $realname=$mail; // выслать подтверждение email
	global $include_sys,$httphost,$unic,$newhash_user,$admin_name,$admin_mail;
	if(!mail_validate($mail)) idie("Неверный формат ".h($mail));
	$link=$httphost."login?a=mailconfirm"."&u=".$unic."&m=".urlencode($mail)."&h=".md5($mail.$unic.$newhash_user);
	$s=mpers(get_sys_tmp("mail_confirm.htm"),array(
'httphost'=>$httphost,
'admin_name'=>$admin_name,
'link'=>$link,
'mail'=>$mail,
'realname'=>$realname));
	return sendmail(h($admin_name),h($admin_mail),h($realname),h($mail),$admin_name.": email confirm",$s);
}

/*
в config.php вписываем данные своего почтового сервиса:

$GLOBALS['smtp_mail']='sendmail@lleo.me';
$GLOBALS['smtp_pass']='gbgbrfkrf123';
$GLOBALS['smtp_smtp']='ssl://smtp.yandex.ru';
$GLOBALS['smtp_name']='LLeo';
$GLOBALS['smtp_port']='465';

// https://pdd.yandex.ru/domain/lleo.me/
echo "send: ".sendmail('Отправитель Петров','lleo@aha.ru','Получатель Каганов','lleo@lleo.me','Тема письма','Сам: http://home.lleo.me'); die('OK');
*/

function sendmail($from_name,$from_adr,$to_name,$to_adr,$subj,$text,$headers=false) {

if(!empty($GLOBALS['smtp_mail'])) { // если есть внешний почтовый сервис

    if($headers==false) $headers = "MIME-Version: 1.0\r\n"
    ."From: ".wu($from_name)." <".$from_adr.">\r\n"
    ."To: ".wu($to_name)." <".$to_adr.">\r\n"
    // ."Date: ".date("r")."\r\n"
    ."X-Dnevnik: ".$GLOBALS['httphost']."\r\n"
    ."Content-type: text/html; charset=utf-8\r\n";

    // $mailSMTP = new SendMailSmtpClass('zhenikipatov@yandex.ru', '****', 'smtp.yandex.ru', 'Evgeniy');
    // $mailSMTP = new SendMailSmtpClass('ipatovsoft@gmail.com', '*****', 'ssl://smtp.gmail.com', 'Evgeniy', 465);
    $mailSMTP = new SendMailSmtpClass($GLOBALS['smtp_mail'],$GLOBALS['smtp_pass'],$GLOBALS['smtp_smtp'],$GLOBALS['smtp_name'],$GLOBALS['smtp_port']);

    // $result =  $mailSMTP->send('lleo@lleo.me', 'Тема письма 2', 'Текст письма 2', $headers);
    return $mailSMTP->send($to_adr,wu($subj),wu($text),$headers);
}

/*
if(!empty($GLOBALS['sendmail_service'])) { // если прописан путь пересылки меж движками
    include $GLOBALS['include_sys']."_files.php"; // применить sendmail_service
    return POST_data($GLOBALS['sendmail_service'],array('from_addr'=>$from_adr,
	'to'=>$to,'subj'=>$subj,'text'=>$text,'headers'=>$headers,'ssp'=>$GLOBALS['sendmail_service_mypass']));
}
*/

$to = "=?windows-1251?B?".base64_encode($to_name)."?= <".$to_adr.">";
$subj = "=?windows-1251?B?".base64_encode($subj)."?=";

if($headers==false) $headers = "MIME-Version: 1.0
From: =?windows-1251?B?".base64_encode($from_name)."?= <".$from_adr.">
Date: ".date("r")."
X-Dnevnik: ".$GLOBALS['httphost']."
Content-type: text/html; charset=windows-1251";


return mail($to,$subj,$text,$headers,"-f".$from_adr);

}

//=========================================
// найдено на: http://vk-book.ru/otpravka-pisem-cherez-smtp-s-avtorizaciej-na-php/
// SendMailSmtpClass
// Класс для отправки писем через SMTP с авторизацией
// Может работать через SSL протокол
// Тестировалось на почтовых серверах yandex.ru, mail.ru и gmail.com
// @author Ipatov Evgeniy <admin@ipatov-soft.ru>
// @version 1.0

class SendMailSmtpClass {
    public $smtp_username;
    public $smtp_password;
    public $smtp_host;
    public $smtp_from;
    public $smtp_port;
    public $smtp_charset;

    public function __construct($smtp_username, $smtp_password, $smtp_host, $smtp_from, $smtp_port = 25, $smtp_charset = "utf-8") {
        $this->smtp_username = $smtp_username;
        $this->smtp_password = $smtp_password;
        $this->smtp_host = $smtp_host;
        $this->smtp_from = $smtp_from;
        $this->smtp_port = $smtp_port;
        $this->smtp_charset = $smtp_charset;
    }

//   $mailTo - получатель письма
//   $subject - тема письма
//   $message - тело письма
//   $headers - заголовки письма
//   В случае отправки вернет true, иначе текст ошибки

function send($mailTo, $subject, $message, $headers) {
    $contentMail = "Date: ".date("D, d M Y H:i:s")." UT\r\n"
    .'Subject: =?'. $this->smtp_charset .'?B?'.base64_encode($subject)."=?=\r\n"
    .$headers."\r\n"
    .$message."\r\n";
 try {
    if(!$c = @fsockopen($this->smtp_host,$this->smtp_port,$errorNumber,$errorDescription,30)){ throw new Exception($errorNumber.".".$errorDescription); }
    if(!$this->_parseServer($c,"220")){ throw new Exception('Connection error'); }
    $server_name = $_SERVER["SERVER_NAME"];
    fputs($c,"HELO $server_name\r\n"); if(!$this->_parseServer($c,"250")) { fclose($c); throw new Exception('Error of command sending: HELO'); }
    fputs($c,"AUTH LOGIN\r\n"); if(!$this->_parseServer($c,"334")) { fclose($c); throw new Exception('Autorization error'); }
    fputs($c,base64_encode($this->smtp_username)."\r\n"); if(!$this->_parseServer($c,"334")) { fclose($c); throw new Exception('Autorization error'); }
    fputs($c,base64_encode($this->smtp_password)."\r\n"); if(!$this->_parseServer($c,"235")) { fclose($c); throw new Exception('Autorization error'); }
    fputs($c,"MAIL FROM: <".$this->smtp_username.">\r\n"); if(!$this->_parseServer($c,"250")) { fclose($c); throw new Exception('Error of command sending: MAIL FROM'); }
        $mailTo=trim($mailTo,'<>');
    fputs($c,"RCPT TO: <" . $mailTo . ">\r\n"); if(!$this->_parseServer($c, "250")) { fclose($c); throw new Exception('Error of command sending: RCPT TO'); }
    fputs($c,"DATA\r\n"); if(!$this->_parseServer($c, "354")) { fclose($c); throw new Exception('Error of command sending: DATA'); }
    fputs($c, $contentMail."\r\n.\r\n"); if(!$this->_parseServer($c, "250")) { fclose($c); throw new Exception("E-mail didn't sent"); }
    fputs($c, "QUIT\r\n");
    fclose($c);
 } catch (Exception $e) { return $e->getMessage(); }
 return true;
}

 private function _parseServer($c, $response) {
    while(@substr($responseServer,3,1) != ' ') { if(!($responseServer=fgets($c,256))) return false; }
    if(!(substr($responseServer,0,3) == $response)) return false;
    return true;
 }
}




// ==========================
function sendmail_files($files, $path, $mailto, $from_mail, $from_name, $replyto='', $subject, $message) {
$uid = md5(uniqid(time()));

$header = "From: ".$from_name." <".$from_mail.">\r\n";
$header .= "Reply-To: ".$replyto."\r\n";
$header .= "MIME-Version: 1.0\r\n";
$header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\r\n\r\n";
$header .= "This is a multi-part message in MIME format.\r\n";
$header .= "--".$uid."\r\n";
$header .= "Content-type:text/html; charset=iso-8859-1\r\n";
$header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
$header .= $message."\r\n\r\n";

    foreach ($files as $filename) { $file = $path.$filename;

        $file_size = filesize($file);
        $handle = fopen($file, "r");
        $content = fread($handle, $file_size);
        fclose($handle);
        $content = chunk_split(base64_encode($content));

        $header .= "--".$uid."\r\n";
        $header .= "Content-Type: application/octet-stream; name=\"".$filename."\"\r\n"; // use different content types here
        $header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"".$filename."\"\r\n\r\n";
        $header .= $content."\r\n\r\n";
    }

$header .= "--".$uid."--";

return sendmail($from_name,$from_mail,$to_name,$mailto,$subject,$message,$header);

// return mail($mailto, $subject, "", $header);
}


// mail('lleo@lleo.me', 'text', "", '');
// $o=sendmail_files(array('pb.zip'),'/var/www/home/dnevnik/2016/11/pocketbook/', 'lleo@lleo.me', 'lleo@aha.ru', 'LLeo Kaganov', '', 'My new books!', 'Hello, LLeo!');
// die('Result: `'.$o.'` '.intval($o));

?>