<?php

SCRIPTS("
function listik_do(n,w){
    var e=idd('listik'+n); e.style.width=w+'px';
    var p=e.parentNode; p.style.width=''+(w*1.4)+'px';
    var cs=window.getComputedStyle?getComputedStyle(e,''):e.currentStyle;
    var h=cs.height;
    var w=cs.width; w=''+(1*w.replace(/[^\d]+/g,'')+20)+'px';
    doclass('list-wrapper',function(x){x.style.height=h;x.style.width=w;},'',p);
}
");

/*
STYLES("listki","
.listcont { width: 80%; margin: 0 auto; position: relative; padding-bottom: 10%; }
.list, .list-wrapper { margin: 40px auto; background: white;
width: 73.023255814%; margin-top: 7.9069767442%; padding-right: 6.976744186%; margin-bottom: -10%; padding-left: 5%; border: 1px solid rgba(44,62,80,0.5); }

.list { position: relative; z-index: 10; min-height: 100px; }
.list-wrapper { position: absolute; margin: 0; }
.list-wrapper-1 { z-index: 3; -webkit-transform: rotate(-2deg); -ms-transform: rotate(-2deg); margin-left: 3% }
.list-wrapper-2 { z-index: 2; -webkit-transform: rotate(-3deg); -ms-transform: rotate(-3deg); transform: rotate(-3deg); }
.list-wrapper-3 { z-index: 1; -webkit-transform: rotate(1deg); -ms-transform: rotate(1deg); transform: rotate(1deg); margin-left: 6% }
");
*/

STYLES("listki","
.listcont { width: 900px; margin: 0 auto; position: relative; }
.list, .list-wrapper { margin: 40px auto; background: white;
width: 73.023255814%; padding-top: 7.9069767442%; padding-right: 6.976744186%; padding-bottom: 6.976744186%; padding-left: 5%; border: 1px solid rgba(44,62,80,0.5); }

.list { position: relative; z-index: 10; min-height: 100px; }
.list-wrapper { position: absolute; left: 50%; margin: 0; margin-left: -44.7674418605%; }
.list-wrapper-1 { z-index: 3; -webkit-transform: rotate(-2deg); -ms-transform: rotate(-2deg);  }
.list-wrapper-2 { z-index: 2; -webkit-transform: rotate(-3deg); -ms-transform: rotate(-3deg); transform: rotate(-3deg); }
.list-wrapper-3 { z-index: 1; -webkit-transform: rotate(1deg); -ms-transform: rotate(1deg); transform: rotate(1deg); }
");



// .list-wrapper { position: absolute; left: 50%; margin: 0; margin-left: -44.7674418605%; }

$GLOBALS['LISTIK_n']=0;

function LISTIK($e) { $cf=array_merge(array(
    'width'=>900
),parse_e_conf($e));

$GLOBALS['LISTIK_n']++;

SCRIPTS("page_onstart.push(\"listik_do(".$GLOBALS['LISTIK_n'].",".$cf['width'].")\");");

return "<div class='listcont'>"
."<div id='list1' style='height:100px;' class='list-wrapper list-wrapper-1'></div>"
."<div id='list2' style='height:100px;' class='list-wrapper list-wrapper-2'></div>"
."<div id='list3' style='height:100px;' class='list-wrapper list-wrapper-3'></div>"
."<section class='list' id='listik".($GLOBALS['LISTIK_n'])."'>".$cf['body']."</section></div>";

/*
return "<div class='listcont'>"
."<div id='list1' style='height:100%; width:80%;' class='list-wrapper list-wrapper-1'></div>"
."<div id='list2' style='height:100%; width:80%;' class='list-wrapper list-wrapper-2'></div>"
."<div id='list3' style='height:100%; width:80%;' class='list-wrapper list-wrapper-3'></div>"
."<section class='list' id='listik".($GLOBALS['LISTIK_n'])."' style='width:80%; height: 100%'>".$cf['body']."</section></div>";
*/


}
?>