<?php // sendmail

include "../config.php"; include $include_sys."_autorize.php"; $a=RE('a'); AD(); // только дял админа

//========================================================================================================================

function emailpars($mail) { $a=array('',false); $mail=c0($mail);
    if(!preg_match("/^(.*?)([0-9a-z\.\-\_\@]+)$/si",$mail,$m)) return $a;
    $a[1]=mail_validate($m[2]);
    $a[0]=c0($m[1]);
    return $a;
}


// function mail_validate($s) {
// <------>$s=preg_replace("/[^0-9a-z\_\-\.\@]+/si",'',$s);
//<------>return (preg_match("/^[0-9a-z\_\-\.]+\@[0-9a-z\-\.]+\.[0-9a-z]{2,10}$/si", $s) ? $s : false);

if($a=='send') { AD();

    $adr=strtr(c0(RE('adr')),'<>','  '); if($adr=='') otprav("sendi++; sendika();");
    $from=strtr(c0(RE('from')),'<>','  ');
    $text=c0(RE('text')); if($text=='') idie("Error: empty Text");
    $subj=c0(RE('subj')); if($subj=='') idie("Error: empty Subj");
    $mode=RE0('mode');
    list($fname,$fadr)=emailpars($from); if($fadr==false) idie("Wrong FROM_NAME: `".h($from)."`");
    list($tname,$tadr)=emailpars($adr); if($tadr==false) idie("Wrong ADR: `".h($from)."`");

$s="
<div>mode: <b>".($mode?'SEND':'test')."</b></div>
<div><b>From:</b> ".h($fname)." &lt;".h($fadr)."&gt;</div>
<div><b>To:</b> <font color=green><b>".h($tname)." &lt;".h($tadr)."&gt;</b></font></div>
<div><b>Subj:</b> ".h($subj)."</div>
<p><div>".nl2br($text)."</div>
";

$real='';

if($mode==1) {
    include_once $include_sys."_sendmail.php";
    if($fname=='') $fname=$fadr;
    if($tname=='') $tname=$tadr;
    $text=nl2br($text);
    sendmail($fname,$fadr,$tname,$tadr,$subj,$text);

$real="zabil('senden',vzyal('senden')+'<div>".h($tname)." &lt;".h($tadr)."&gt;</div>');";

}

    otprav($real."clean('salert');salert(\"".njsn($s)."\",5000); sendi++; sendika();");

}

idie(nl2br(h(__FILE__.": unknown action `".$a."`")));
?>