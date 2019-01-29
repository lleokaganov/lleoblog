<?php

if(!function_exists('iconv')) { function iconv($f,$t,$s) { return riconv($f,$t,$s); } }

$GLOBALS['enc']=array(
'koi'=>array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,154,0,0,0,0,0,0,0,179,191,0,0,0,0,0,0,156,0,0,0,0,0,0,158,163,0,
0,0,0,0,0,0,225,226,247,231,228,229,246,250,233,234,235,236,237,238,239,240,242,243,244,245,230,232,227,254,251,253,255,249,248,252,224,241,193,
194,215,199,196,197,214,218,201,202,203,204,205,206,207,208,210,211,212,213,198,200,195,222,219,221,223,217,216,220,192,209),
'win'=>array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,160,0,176,0,183,0,0,0,0,184,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,168,0,0,0,0,0,0,0,
0,0,0,0,169,254,224,225,246,228,229,244,227,245,232,233,234,235,236,237,238,239,255,240,241,242,243,230,226,252,251,231,248,253,249,247,250,222,
192,193,214,196,197,212,195,213,200,201,202,203,204,205,206,207,223,208,209,210,211,198,194,220,219,199,216,221,217,215,218),
'utf'=>array(chr(208).chr(130),chr(208).chr(131),chr(226).chr(128),chr(209).chr(147),chr(226).chr(128),chr(226).chr(128),chr(226).chr(128),chr(226).chr(128),
chr(226).chr(130),chr(226).chr(128),chr(208).chr(137),chr(226).chr(128),chr(208).chr(138),chr(208).chr(140),chr(208).chr(139),chr(208).chr(143),chr(209).chr(146),
chr(226).chr(128),chr(226).chr(128),chr(226).chr(128),chr(226).chr(128),chr(226).chr(128),chr(226).chr(128),chr(226).chr(128),chr(0).chr(0),chr(226).chr(132),
chr(209).chr(153),chr(226).chr(128),chr(209).chr(154),chr(209).chr(156),chr(209).chr(155),chr(209).chr(159),chr(194).chr(160),chr(208).chr(142),chr(209).chr(158),
chr(208).chr(136),chr(194).chr(164),chr(210).chr(144),chr(194).chr(166),chr(194).chr(167),chr(208).chr(129),chr(194).chr(169),chr(208).chr(132),chr(194).chr(171),
chr(194).chr(172),chr(194).chr(173),chr(194).chr(174),chr(208).chr(135),chr(194).chr(176),chr(194).chr(177),chr(208).chr(134),chr(209).chr(150),chr(210).chr(145),
chr(194).chr(181),chr(194).chr(182),chr(194).chr(183),chr(209).chr(145),chr(226).chr(132),chr(209).chr(148),chr(194).chr(187),chr(209).chr(152),chr(208).chr(133),
chr(209).chr(149),chr(209).chr(151),chr(208).chr(144),chr(208).chr(145),chr(208).chr(146),chr(208).chr(147),chr(208).chr(148),chr(208).chr(149),chr(208).chr(150),
chr(208).chr(151),chr(208).chr(152),chr(208).chr(153),chr(208).chr(154),chr(208).chr(155),chr(208).chr(156),chr(208).chr(157),chr(208).chr(158),chr(208).chr(159),
chr(208).chr(160),chr(208).chr(161),chr(208).chr(162),chr(208).chr(163),chr(208).chr(164),chr(208).chr(165),chr(208).chr(166),chr(208).chr(167),chr(208).chr(168),
chr(208).chr(169),chr(208).chr(170),chr(208).chr(171),chr(208).chr(172),chr(208).chr(173),chr(208).chr(174),chr(208).chr(175),chr(208).chr(176),chr(208).chr(177),
chr(208).chr(178),chr(208).chr(179),chr(208).chr(180),chr(208).chr(181),chr(208).chr(182),chr(208).chr(183),chr(208).chr(184),chr(208).chr(185),chr(208).chr(186),
chr(208).chr(187),chr(208).chr(188),chr(208).chr(189),chr(208).chr(190),chr(208).chr(191),chr(209).chr(128),chr(209).chr(129),chr(209).chr(130),chr(209).chr(131),
chr(209).chr(132),chr(209).chr(133),chr(209).chr(134),chr(209).chr(135),chr(209).chr(136),chr(209).chr(137),chr(209).chr(138),chr(209).chr(139),chr(209).chr(140),
chr(209).chr(141),chr(209).chr(142),chr(209).chr(143))
);

function riconv_fromto($l) { return str_ireplace(
array('//IGNORE','windows1251','windows-1251','cp1251','cp-1251','1251','koi8-r','koi8r','koi-8','koi8','utf-8','utf8'),
array('','win','win','win','win','win','koi','koi','koi','koi','utf','utf'),$l);
}

function riconv($f,$t,$s) { $f=riconv_fromto($f); $t=riconv_fromto($t); global $enc; $o='';
    if($f.$t=='winkoi'||$f.$t=='koiwin') { for($e=strlen($s),$i=0;$i<$e;$i++) { $c=ord($s[$i]); $o.=$c<128?$s[$i]:chr($enc[$t][$c-128]); } return $o; }
    if($t=='utf') {
	if($f=='koi') $s=riconv('koi','win',$s);
	for($e=strlen($s),$i=0;$i<$e;$i++) { $c=ord($s[$i]); $o.=($c<128?$s[$i]:$enc['utf'][$c-128]); } return $o;
    }
    if($f=='utf') {
	for($e=strlen($s),$i=0;$i<$e;$i++){
	    if(ord($s[$i])<128 || !isset($s[$i+1]) || (false==($p=array_search($s[$i].$s[$i+1],$enc['utf'])))) $o.=$s[$i];
	    else { $i++; $o.=chr($p+128); }
	}
    return $t=='win'?$o:riconv('win','koi',$o);
    }
    return $s;
}

?>