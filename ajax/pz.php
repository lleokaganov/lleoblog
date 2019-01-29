<?php

// function PZ() {

// return 

AD();

$topo="WHERE `admin`='podzamok'";
$n=ms("SELECT COUNT(*) FROM $db_unic $topo","_l");
$pp=ms("SELECT `id`,`login`,`openid`,`realname` FROM ".$GLOBALS['db_unic']." $topo ORDER BY `id` DESC","_a");

SCRIPTS("
chzamok=function(e,d){
        if(d=='user') var o='podzamok';
        else if(d=='podzamok') var o='user';
        else return;
        var unic=ecom(e).id.replace(/u+/,'');
	alert(unic+': '+o);
        // majax('okno.php',{a:'dostup',unic:unic,value:o})
};

chzp=function(e) {
    var u=1*ecom(e).id.replace(/u+/,'');
    var p=idd('txa').value.split(',');
                                                                    
    if(e.checked) { p.push(u); }                                    
    else {
        for(var i in p) { if(1*p[i]==u) { delete(p[i]); break; } }
    }
    var o=''; for(var i in p) o+=(o==''?o:',')+p[i];
    idd('txa').value=o; // p.join(',');
};

");

$tmpl="<div id='u{id}'><label>"
// ."<div class=ll onclick=\"chzamok(this,'podzamok')\">".zamok('podzamok')."</div>"
." <input class='mch' type=checkbox onchange='chzp(this)'>"
." <div class=ll onmouseover=\"majax('login.php',{action:'getinfo',unic:{id}})\">{imgicourl}</div>"
."</label></div>";

$o="<textarea id='txa' style='width:100%;height:100px'></textarea><p>"; foreach($pp as $p) {
	$p=get_ISi($p);
        $o.=mpers($tmpl,$p); 
}

// $o="www";

// }

/*
// ======== unics - листать базу посетителей ===========
if($a=='unics') {

        $nskip=1*RE0('nskip');

        $nlim=20;
$jscripts=($admin?"
":'');

$search=RE('search');
$n=RE0('n');

if(1*$search) {
    $pp=array(0=>array('id'=>$search));
} else {

    if($search=='') $topo="WHERE (`login`!='' AND `password`!='') OR `openid`!=''";
    elseif($podzamok && $search=='podzamok') { $topo="WHERE `admin`='podzamok'"; $nlim=2000; }
    else {
        $se="LIKE '%".e($search)."%'";
        $topo="WHERE `login` $se OR `openid` $se OR `realname` $se OR `site` $se".($admin?" OR `mail` $se":"");
    }

    if(!$n) $n=ms("SELECT COUNT(*) FROM $db_unic $topo","_l");

    $pp=ms("SELECT `id`,`login`,`openid`,`realname`,`site`,`birth`,`time_reg`,`timelast`"
    .($podzamok?",`mail`,`admin`,`ipn`,`capchakarma`":'')
    ." FROM $db_unic $topo ORDER BY `time_reg` DESC LIMIT $nskip,".($nlim));

//    dier($pp);
}

mk_okno("<center><input id='search_unic' type='text'
onchange=\"majax('okno.php',{a:'unics',search:this.value})\"
size='40' value=\"".h($search)."\">"
."<input type='submit' value='search' onclick=\"majax('okno.php',{a:'unics',search:idd(search_unic).value})\"></center><br>"
.pr_unics($pp),"Зарегистрировавшиеся посетители ($nlim с ".h($nskip).", всего ".h($n).")","a:'$a',id:'$id'"
);
}
*/

?>