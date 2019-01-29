//function basename(path) { return path.replace(/^.*[\/\\]/g,''); }

var treeselected={};
var treeicon=50;
var treefolder='/';

function treehasClass(elem,className){ return new RegExp("(^|\\s)"+className+"(\\s|$)").test(elem.className); }
function treeshowLoading(on,id){ idd(id).getElementsByTagName('DIV')[0].className=on?'ExpandLoading':'Expand'; }

function treeonLoaded(data,id) { var node=idd(id);
	for(var i=0;i<data.length;i++) {
		var li=document.createElement('LI');
		li.className="Node Expand"+(data[i][2]==1?'Closed':'Leaf');
			if(i==data.length-1) li.className+=' IsLast';
		li.innerHTML='\n\t<div class=Expand></div><div class=Con3>'+data[i][1]+'</div>';
		if(data[i][2]==1) {
			li.innerHTML+='\n\t<ul class=Container></ul>';
			li.id=data[i][0];
		}
		node.getElementsByTagName('UL')[0].appendChild(li);
	}
	node.isLoaded=true;
	treetoggleNode(node);
}

function treeload(id) { treeshowLoading(true,id); majax('foto.php',{a:'albumgo',id:id}); }

function treetoggleNode(node,r) { // заменить класс
	treefolder=node.id;
	if(typeof r == 'undefined') var newClass=treehasClass(node,'ExpandOpen')?'ExpandClosed':'ExpandOpen';
	else var newClass='ExpandClosed';
	node.className=node.className.replace(/(^|\s)(ExpandOpen|ExpandClosed)(\s|$)/,'$1'+newClass+'$3');
//	zabil('albumdir',node.id);
}

// ============================  последняя строка скрипта должна быть всегда такой: ========================
var src='tree.js'; ajaxoff(); var r=JSload[src]; JSload[src]='load'; if(r && r!='load') eval(r);
