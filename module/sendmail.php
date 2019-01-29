<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй
// сравни - чи срав, чи ни

	ini_set("display_errors","1");
	ini_set("display_startup_errors","1");
	ini_set('error_reporting', E_ALL); // включить сообщения об ошибках

$s="
<script>
var amas=false;
var sendmode=0;
var sendi=0;

function sendika() {

if(sendmode >1) { idie('DONE'); return clean('sendmail_button'); }

if(amas===false) {
amas=idd('mailsend_adresa').value.split(\"\\n\");
if(0==amas.length) return idie('Error adress');

sendi=0; sendmode=0;
idd('sendmail_button').value='TEST';
}

var s=amas[sendi];
if(s==undefined) { sendi=0;
if(++sendmode==1) { idd('sendmail_button').value='START'; salert('Ready to send '+amas.length+' emails',10000); }
else { clean('sendmail_button'); salert('Done!',10000); }
return; }

majax('sendmail.php',{a:'send',mode:sendmode,adr:s,from:idd('mailsend_from').value,subj:idd('mailsend_subj').value,text:idd('mailsend_text').value});

}
</script>

<p class=name>РАССЫЛКА EMAIL

{_BC:
<form name='sendmail'>
<table border=0 cellspacing=10 cellpadding=0 width=100%>
<tr><td>From:</td><td><input name='mailsend_from' id='mailsend_from' value='".$GLOBALS['admin_name']." ".$GLOBALS['admin_mail']."' type=text style='width:50%'></td></tr>
<tr><td>To:</td><td><textarea name='mailsend_adresa' id='mailsend_adresa' style='width:50%;height:50px;'>".$GLOBALS['admin_name']." ".$GLOBALS['admin_mail']."</textarea></td></tr>
<tr><td>Subj:</td><td><input name='mailsend_subj' id='mailsend_subj' type=text size=80></td></tr>
<tr><td>Text:</td><td><textarea name='mailsend_text' id='mailsend_text' style='width:100%;height:300px;'></textarea></td></tr>
<tr><td></td><td><input id='sendmail_button' type=button value='GO' onclick='sendika()' style='padding:30px;'></td></tr></table>
</form>
_}

<div class=r id='senden'></div>

";


$article=array(
'Date'=>'mail',
'Header'=>'mail',
'Body'=>$s,
'Access'=>'admin',
'DateUpdate'=>0,'num'=>0,'DateDatetime'=>0,'DateDate'=>0,
'opt'=>'a:3:{s:8:"template";s:5:"blank";s:10:"autoformat";s:2:"no";s:7:"autokaw";s:2:"no";}',
'view_counter'=>0
);
ARTICLE();
?>