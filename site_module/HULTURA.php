<?php // Содержание дневника

$hf=$GLOBALS['include_sys']."hultura_auth.php"; if(is_file($hf)) include_once $hf; // авторизация хультурных админов

SCRIPT_ADD("http://s7.addthis.com/js/250/addthis_widget.js");

// html { font-size: 20px; }

STYLES("
body { font-family: 'Verdana', sans-serif; font-size: 100%; }
body.window { background: #ecf0f1; }
.container { max-width: 860px; min-width: 240px; margin: 0 auto; position: relative; }
.list, .list-wrapper { margin: 20px auto; background: white; border: 1px solid rgba(44,62,80,0.5);
width: 73.023255814%; padding-top: 7.9069767442%; padding-right: 6.976744186%; padding-bottom: 6.976744186%; padding-left: 10.4651162791%;
}
.list { position: relative; z-index: 10; min-height: 465px; }
.list-wrapper { position: absolute; left: 50%; margin: 0; margin-left: -44.7674418605%; }
.list-wrapper-1 { z-index: 3; -webkit-transform: rotate(-2deg); -ms-transform: rotate(-2deg); transform: rotate(-2deg); }
.list-wrapper-2 { z-index: 2; -webkit-transform: rotate(-3deg); -ms-transform: rotate(-3deg); transform: rotate(-3deg); }
.list-wrapper-3 { z-index: 1; -webkit-transform: rotate(1deg); -ms-transform: rotate(1deg); transform: rotate(1deg); }
");

/*
$hultura_users=array(
48055=>'shar',
48054=>'isakov',
48041=>'shest',
47184=>'botch',
13=>'lleo'
); $GLOBALS['hultura_user']=$hultura_users[$GLOBALS['unic']];
*/

function HULTURA($e) { global $article;

$conf=array_merge(array(

'header'=>$GLOBALS['article']['Header'],
'rulit'=>0,
'spam'=>0,
'tupit'=>0,
'rulit'=>0,
'count'=>0,
'id'=>0,
'time'=>0,
'rul_shest'=>0,
'rul_botch'=>0,
'rul_young'=>0,
'rul_lleo'=>0,

),parse_e_conf($e));

if(!isset($conf['author'])) {
    if(!isset($conf['acnh'])) $conf['author']='anonym';
    else {
$hultura_autors=array('a'=>'anonym','x'=>'anonym','b'=>'botch','i'=>'isakov','l'=>'lleo','h'=>'shar','s'=>'shest','y'=>'young');
$conf['author']=$hultura_autors[$conf['acnh']];
    }
}


if(!isset($conf['template'])) {

$conf['template']="<div><div style='font-size:22px;background-color:#EEE;'>{?anonym:
1:
on:
*:<img title='{author}' src='".$GLOBALS['wwwhost']."img/c/{author}.png'>&nbsp;
?}<a href='".getlink($GLOBALS['article']['Date'])."' class='ch'>{header}</a></div><div style='margin-top:20px;' class='cc'>{text}</div>
</div>{?answer:
:
*:<div class='ct'><span class='clh'>Hultura.ru:</span><div class='cl'>{answer}</div></div>
?}"

."<div class=r style='float:bottom;display:inline;'>"
.($GLOBALS['admin']?
"<input type=button value='написать&nbsp;реплику' onclick=\"majax('hultura.php',{a:'addcomm',num:'".$article['num']."'})\">"

." &nbsp; <input type=button value='редактировать' onclick=\"majax('editor.php',{a:'editform',editor:'hultura',num:".$article['num']."})\">"

." &nbsp; <input type=button value='РБ' alt='Редактор для Бочарика<br>с блэкджеком и шлюхами' onclick=\"majax('editor.php',{a:'editform',editor:'tinymce',num:".$article['num']."})\">"

.($article['Access']=='all'?
"<div style='display:inline' class='r addthis_toolbox addthis_pill_combo_style' addthis:url='".h(getlink($article['Date']))."'addthis:title='".h($article['Header'])."'><a class='addthis_button_compact'>поделиться</a></div>"
:"&nbsp; <input type=button value='Опубликовать' onclick=\"majax('hultura.php',{a:'publ',num:'".$article['num']."'})\">"
)
:''
)



."</div>"
;

















}

$conf['rul']='';
if($conf['rul_shest']) $conf['rul'].="<img src='/img/s.gif'> ";
if($conf['rul_botch']) $conf['rul'].="<img src='/img/b.gif'> ";
if($conf['rul_young']) $conf['rul'].="<img src='/img/y.gif'> ";
if($conf['rul_lleo'])  $conf['rul'].="<img src='/img/l.gif'> ";

if(strstr($conf['body'],'|---|')) list($conf['text'],$conf['answer']) = explode('|---|',$conf['body']);
else { $conf['text']=$conf['body']; $conf['answer']=''; }
$conf['text']=str_replace("\n","<br>",trim($conf['text'],"\n\r\t "));
$conf['answer']=str_replace("\n","<br>",trim($conf['answer'],"\n\r\t "));


return mper($conf['template'],$conf);
}

/*
'template'=>"
<div name='{id}' id='{id}' class='o'>

<div class='kk'>{rul}
<span class='k'><a id='k' href=\"javascript:jj('{id}');\">ГЕНИАЛЬНО </a>&nbsp;<span class='m'><font color='red'><b><big>+{rulit}</big></b></font></span></span><span class='k'><a id='k' href=\"javascript:tu('{id}');\">НЕПОНЯТНО </a>&nbsp;<span class='m'><font color='orange'><b><big>*{tupit}</big></b></font></span></span><span class='k'><a id='k' href=\"javascript:sp('{id}');\">ОТСТОЙ</a>&nbsp;<span class='m'><font color='magenta'><b><big>-{spam}</big></b></font></span></span>
<span class='k'><a id='k' href='/{id}.html'>ОБСУДИМ </a></span>

<span class='k'><a id='k' href=\"javascript:l('{id}');\">УТАЩИТЬ К СЕБЕ</a></span>
<span class='k'><b>{time}</b> просмотров: {count}</span>
</div><div class='c'>
{id}. {?anonym:
1:
*:<img src='/img/{acnh}.gif'>
?}<a href='/{id}.html' class='ch'>{header}</a><div class='cc'>{text}</div>
</div>{?answer:
:
*:<div class='ct'><span class='clh'>Hultura.ru:</span><div class='cl'>{answer}</div></div>
?}</div>

*/
?>