<?php /* операция подзамка - раскрыть пост

Пост сможет открыть/закрыть подзамок с unic=12345 - он увидит кнопку.

{_OPEN_POST: 12345 _}

*/


function OPEN_POST_ajax() { if(!$GLOBALS['podzamok']) return '';
	$num=RE('num');
	if(($sql=ms("SELECT `Body`,`Access` FROM `dnevnik_zapisi` WHERE `num`='$num'".ANDC(),'_1',0))==false) return "alert('Error #1')";
	$s=preg_replace("/^\{_OPEN_POST\: *([^_\}]+) *_\}.*$/s","$1",$sql['Body']); if($s==$sql['Body']) return "alert('Error #2')";
	$a=$sql['Access'];
	$p=explode(',',$s);
        $GLOBALS['IS']['unic']=$GLOBALS['unic'];
        $id=''; foreach($p as $l) { $l=c($l);
                if(substr($l,1,1)==':') {
                        $c=substr($l,0,1);
                        if($c=='u') { $id='unic'; }
                        elseif($c=='l') { $id='login'; }
                        elseif($c=='o') { $id='openid'; }
                        elseif($c=='r') { $id='realname'; }
                        if($GLOBALS['IS'][$id]==substr($l,2)) return OPEN_POST_action($num,$a);
                        continue;
                }

                if($id=='') {
                        if(intval($l)) { if($GLOBALS['IS']['unic']==$l) return OPEN_POST_action($num,$a); }
                        else { if($GLOBALS['IS']['realname']==$l) return OPEN_POST_action($num,$a); }
                        continue;
                }

                if($GLOBALS['IS'][$id]==$l) { return OPEN_POST_action($num,$a); }
        }
	return "alert('Error #3 (Admin #".$GLOBALS['unic']." not in list?)')";
}


function OPEN_POST_action($num,$a) {
	if($a=='podzamok') $a='all';
	elseif($a=='all') $a='podzamok';
	else return '';
	msq_update('dnevnik_zapisi',array('Access'=>$a),"WHERE `num`='$num'".ANDC());
	return "if(confirm('Now access is `$a`\\nReload?')) document.location.href=mypage;";
}

function OPEN_POST($u) { if(!$GLOBALS['podzamok']) return '';
	if(!$GLOBALS['admin'] && $u!=$GLOBALS['unic']) return '';
	$s=($GLOBALS['article']['Access']=='podzamok'?'open':'close');
	return "<input type='button' value='$s' onclick=\"majax('module.php',{mod:'OPEN_POST',num:'".$GLOBALS['article']['num']."'})\">";
}

?>