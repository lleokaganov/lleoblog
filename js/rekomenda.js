var o=(document.selection)?document.selection.createRange().text:window.getSelection();

q=document.body;

q.innerHTML="<center><p><br><table border=1 cellspacing=0 cellpadding=5 width=70%>\
<tr><td>link:</td><td>"+location+"</td></tr>\
<tr><td>text:</td><td>"+o+"</td></tr>\
<tr><td>time:</td><td>"+location+"</td></tr>\
<tr><td>wisi:</td><td>"+location+"</td></tr>\
</table></center>\
<a href=http://lleo.aha.ru/re.htm>\
<img src=http://lleo.aha.ru/blog/re.php?l=\
"+encodeURIComponent(location)+"&t=\
"+encodeURIComponent(""+o)+"></a></center>"+q.innerHTML;

if(document.documentElement.scrollTop) document.documentElement.scrollTop=0; if(document.body.scrollTop) document.body.scrollTop=0;
