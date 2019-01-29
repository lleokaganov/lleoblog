<?php
// График работы над книгой. Модель показа картинки.
// Это чисто моя фишка, не думаю, что она кому-то понадобится в силу специфики,
// и сам модуль графика книги сложен и работает совсем в других местах сайта,
// частично на настольной машине (информацию о сделанном объеме передает она).
//

function KNIGA($k) { $k=1.5;

SCRIPTS("kniga","function knigaf(e){
	zabil('knigak','<img style=\"margin: 8px; border: 1px solid #ccc\" src=\"'+e+'\">');
	idd('knigak').style.top=(mouse_y-490/2)+'px';
	idd('knigak').style.left=(mouse_x+50)+'px';
	otkryl('knigak');
}");

return "<div id='knigak' onclick=\"zakryl('knigak')\" style='position: absolute; z-index:9999; padding: 2px; display:'none'; background-color: #F0F0F0; border: 1px solid #ccc;'></div>"
."<p><img src=/backup/kniga_small.gif border=1 onmouseover='knigaf(\"/backup/kniga_big.gif\")' onmouseout=\"zakryl('knigak')\">";
}

?>