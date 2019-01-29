<?php
$js=(isset($_POST['token'])?preg_replace("/[^0-9\_\-a-z]/si",'',$_POST['token'])."'":
"',o=(''+window.location.hash).replace(/\#/g,''); if(o!='' && o.split(':')[0]=='token') t=o.split(':')[1]");
die("<html><body><script>var t='".$js.";
if(t!='') window.parent.location.href=(''+window.location).split('/ajax/ulogin_xd.php')[0]+'/login?loginza='+t+'&QUERY=".urlencode($_SERVER['QUERY_STRING'])."';
</script></body></html>");
?>