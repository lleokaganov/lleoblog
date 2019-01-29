<?php if(!function_exists('h')) die("Error 404"); // неправильно запрошенный скрипт - нахуй
// поддержка протокола автопостинга flat ver 1.0

ob_clean();

function erflat($s) { die("errmsg\n".$s."\nsuccess\nFAIL\n"); }
header("Content-Type: text/plain; charset=utf-8"); // header("Content-Type: text/xml; charset='".$wwwcharset."'");
foreach($_REQUEST as $i=>$l) $_REQUEST[$i]=uw($l);

if(!isset($_REQUEST['ver'])||$_REQUEST['ver']!=1) erflat("unknown protokol (only ver.1)");


 if(empty($flatlogin)||empty($flatpassword)) erflat('FLAT not set');
    if(RE('user')!=$flatlogin || RE('password')!=$flatpassword) { sleep(3); erflat('wrong Login/Password'); }

 $mode=RE('mode');

//$r=$_REQUEST; $r['password']='*******'; file_put_contents('module/flat.---.txt',print_r($r,1));
// erflat($tag."---".str_replace("\n","|",nl2br(print_r($opt,1))));

 if($mode=='postevent' || $mode=='editevent') {
        $p=array();
	$p['Header']=RE('subject');
        $p['Body']="{_NO:autopost:FLAT:".RE('link')."_}".RE('event');

	$Date=RE('year').'/'.RE('mon').'/'.RE('day').'_'.RE('hour').'_'.RE('min');

        $opt=array();
	$tag=RE('prop_taglist');

	foreach($_POST as $i=>$l) {
		$t='lleoopt_'; if(substr($i,0,strlen($t))==$t) {
			$i=substr($i,strlen($t));
			if($i=='tags') { $tag=$l; continue; } // опция opt_tags - принудительно становить тэги
			if($i=='addtags') { $tag=($tag==''?$l:$tag.','.$l); continue; } // опция opt_addtags - добавить новые тэги к существующим
			$opt[$i]=$l;
			continue;
		}
		$t='lleo_'; if(substr($i,0,strlen($t))==$t) $p[substr($i,strlen($t))]=$l;
        }
	$p['opt']=ser(cleanopt($opt)); // опции
	unset($p['Date']); // нахуй, у нас дата будет своя, это же автопостинг

 if($mode=='postevent') {
    // save new
    $d=date("Y/m/d");
    $i=$d; $k=0; while($k<100 && 1==ms("SELECT COUNT(*) FROM `dnevnik_zapisi` WHERE `Date`='".e($d)."'".ANDC(),"_l",0)) $d=$i.'_'.(++$k);
    $p['Date']=$d;
    $t=getmaketime($d);

        $p['DateUpdate']=time();
        $p['DateDate']=$t[0];
        $p['DateDatetime']=$t[1];

    	msq_add('dnevnik_zapisi',arae($p)); if($msqe) erflat('MySQL: '.$msqe);
    	$num=msq_id();
	if($tag!='') tags_save($tag); // и тэги дописать

    die("itemid\n".$num."\n"."url\n".getlink($p['Date'])."\n"."success\nOK\n");
 }
 if($mode=='editevent') { if(!($num=RE0('itemid'))) erflat('itemid=0');

    if(false==($p0=ms("SELECT `Date`,`Body` FROM `dnevnik_zapisi` WHERE `num`='".$num."'".ANDC(),'_1'))) erflat("issue #".$num." not exist");
    if(!strstr($p0['Body'],'{_NO:autopost:FLAT:')) erflat("This page did't loaded by FLAT");

    if($p['Header'].$p['Body']=='') { // delete
	erflat('DELETE');
	if(!zametka_del($num)) erflat('Delete Unknown Error');
	die("success\nOK\n");
    }

    $u=msq_update('dnevnik_zapisi',arae($p),"WHERE `num`='".$num."'".ANDC()); if($msqe) erflat('MySQL: '.$msqe);
    if($tag!='') tags_save($tag); // и тэги дописать
    die("itemid\n".$num."\n"."url\n".getlink($p0['Date'])."\n"."success\nOK\n");
 }
 }

?>