<?php /* Вставка фоток из фотоальбома

Указываем имя фотки в альбоме или url (автоопределение). Также можно поставить запятую и указать дополнительный аргумент выравнивание:
center - по центру экрана
left - дальнейший текст будет обтекать фотку слева
right -  дальнейший текст будет обтекать фотку справа

<div style='clear:both'>{_IMG: http://lleo.aha.ru/blog/photo/1.jpg _}</div>
<div style='clear:both'>{_IMG: http://lleo.aha.ru/blog/photo/2.jpg, center _}</div>
<div style='clear:both'>{_IMG: http://lleo.aha.ru/blog/photo/3.jpg, left _} Все остальное будет огибать эту фотку слева.  Все остальное будет огибать эту фотку слева. Все остальное будет огибать эту фотку слева. Все остальное будет огибать эту фотку слева.  Все остальное будет огибать эту фотку слева. Все остальное будет огибать эту фотку слева.</div>
<div style='clear:both'>{_IMG: http://lleo.aha.ru/blog/photo/4.jpg, right _} Все остальное будет огибать эту фотку справа. Все остальное будет огибать эту фотку справа. Все остальное будет огибать эту фотку справа. Все остальное будет огибать эту фотку справа. Все остальное будет огибать эту фотку справа. Все остальное будет огибать эту фотку справа.</div>

*/


function IMG($e) { $o=''; if(strstr($e,',')) { $g=explode(',',$e,2); if(1*$g[1]==0) list($e,$o)=$g; }
	$e=(strstr($e,'/')?$e:$GLOBALS['foto_www_small'].$e); $e=c($e); $o=c($o);
	$mn='foto-'.md5($e);

	if($GLOBALS['ADM']) {
		$s1="<div id='".$mn."' style='position:relative'>"."<div style='font-size:10px;position:absolute;top:10px;left:10px;'>";
		$s2="</div>";
		$s3="onload=\""
."var w=this.width,h=this.height;"
."idd('".$mn."_w').value=w;"
."zabil('".$mn."_h',h);"
// ."idd('".$mn."_w').size=1+0*(''+w).length;"
."zabil('".$mn."_ww',w);"
."zabil('".$mn."_hh',h);"
."\" ";

if(!strstr($e,'://') || substr($e,0,strlen($GLOBALS['httphost']))==$GLOBALS['httphost'] ) {
    if(time()-$GLOBALS['article']['DateUpdate'] < 5*60) {

    if(!strstr($e,'://')) $e=$GLOBALS['httpsite'].h($e);

    $s1.="<div>"
."<i class='knop e_remove' title='Delete' onclick=\"if(confirm('Delete?')) majax('foto.php',{a:'fot_del',img:'".$e."'})\"></i>"
."&nbsp;<i class='knop e_rotate_left' title='270' onclick=\"if(confirm('Rotate 270?')) majax('foto.php',{a:'fot_rotate',degree:270,img:'".$e."'})\"></i>"
."&nbsp;<i class='knop e_rotate_right' title='90' onclick=\"if(confirm('Rotate 90?')) majax('foto.php',{a:'fot_rotate',degree:90,img:'".$e."'})\"></i>"
."&nbsp;<i class='knop e_blend' title='180' onclick=\"if(confirm('Rotate 180?')) majax('foto.php',{a:'fot_rotate',degree:180,img:'".$e."'})\"></i>"
."&nbsp;<i class='knop e_kontact_journal' title='Album Edit' onclick=\"majax('foto.php',{a:'album_edit',p:'".RE('p')."',num:num})\"></i>"
."</div>";

}

} else {

SCRIPTS("IMG_scr","

function IMGreview(e) {
    var x=1*e.value,id=e.id.split('_')[0],w=1*vzyal(id+'_ww'),h=1*vzyal(id+'_hh');
    if(x>w) e.value=x=w; 
    var y=Math.floor(x*(h/w));
    e=idd(id+'_i'); e.width=x; e.height=y;
    zabil(id+'_h',y);
}

function IMGrz(id,i) {
    var x=1*idd(id+'_w').value,y=vzyal(id+'_h'),x0=1*vzyal(id+'_ww');
    if(i>0 && x>=x0 || i<0 && x<=10) { salert('limit: '+x0,500); return; }

    var X=x+i,Y=Math.floor(X*y/x),e=idd(id+'_i'); e.width=X; e.height=Y;

    idd(id+'_w').value=X;
    zabil(id+'_h',Y); /*idd(id+'_h').value=Y;*/
}

");

    $id=$mn.'_i';

    $s1.="<form onsubmit=\"return send_this_form(this,'foto.php',{a:'download',r:3,num:".$GLOBALS['article']['num'].",url:'".h($e)."'})\">"
."<div style='padding:2px;display:inline-block;color:black;background-color:white;'>"

."<div class='br l' title='подпись' onclick=\"otkryl('".$mn."_txt');\"><span id='".$mn."_ww'>0</span>x<span id='".$mn."_hh'>0</span>"
// ."&nbsp;".$e
."</div>"

."<i class='knop e_viewmagm' onclick=\"IMGrz('".$mn."',-10)\"></i> &nbsp; "
."<i class='knop e_viewmagp' onclick=\"IMGrz('".$mn."',+10)\"></i> &nbsp; "
."<input type=text name='newsize' size=3 id='".$mn."_w' value='' onchange=\"IMGreview(this);\">x<span id='".$mn."_h'></span>"
// .selecto('newsize','1024',array('50'=>'50','100'=>'100','150'=>'150','200'=>'200','300'=>'300','400'=>'400','500'=>'500','600'=>'600','700'=>'700','900'=>'900','1024'=>'1024'),"class='r' name")
."<div id='".$mn."_txt' style='display:none;'>подпись:&nbsp;<input title='Signature' style='font-size:10px' type='text' size='25' name='sign' value=''></div>"
."<input style='font-size:10px' type=button value='View' onclick=\"\"IMGreview(idd('".$mn."_w'))\"> &nbsp; "
."<input style='font-size:10px' type=submit value='Download'>"
."</div>"
."</form>";

    $s3="id='".$id."' ".$s3;

}


$s1.="</div>";


} else $s1=$s2=$s3='';

// return "<span class='hultura-pic' style=\"background-image:url('".h($e)."')\"><img src='".h($e)."'></span>";

	if(isset($GLOBALS['IMG_template'])&& !empty($GLOBALS['IMG_template'])) return mpers($GLOBALS['IMG_template'],array('text'=>$e,'pre'=>$s1,'post'=>$s2,'html'=>$s3,'align'=>$o));

	// "<span class='hultura-pic' style=\"background-image:url('".h($e)."')\">".$s1."<img ".$s3."src='".h($e)."'>".$s2."</span>"

// $tra="<img style='position:absolute;bottom:0px;left:100px;' src='http://lleo.me/dnevnik/2015/12/travolta.gif'>";
// if($s1!='') $s1.=$tra; else { $s1="<div style='position:relative'>".$tra; $s2="</div>"; }

	if($o=='') return $s1."<img ".$s3."src='".h($e)."' hspace='5' vspace='5' border='0'>".$s2;
	if($o=='center') return $s1."<center><img ".$s3."src='".h($e)."' hspace='5' vspace='5' border='1'></center>".$s2;
	return $s1."<img ".$s3."src='".h($e)."' hspace='5' vspace='5' border='1' align='".h($o)."'>".$s2;
}

?>