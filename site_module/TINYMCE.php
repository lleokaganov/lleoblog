<?php

function TINYMCE($e) {
	ob_clean();
	header('Content-Type: application/javascript');
	header('cache-control: no-cashe');

	switch($_GET['js']) {

case 'template_list.js': die('
// This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There templates will be displayed as a dropdown in all media dialog if the "template_external_list_url"
// option is defined in TinyMCE init.

var tinyMCETemplateList = [
        // Name, URL, Description
        ["Simple snippet", "templates/snippet1.htm", "Simple HTML snippet."],
        ["Layout", "templates/layout1.htm", "HTML Layout."]
];
');

case 'link_list.js': die('
// This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There links will be displayed as a dropdown in all link dialogs if the "external_link_list_url"
// option is defined in TinyMCE init.

var tinyMCELinkList = new Array(
        // Name, URL
        ["Moxiecode", "http://www.moxiecode.com"],
        ["Freshmeat", "http://www.freshmeat.com"],
        ["Sourceforge", "http://www.sourceforge.com"]
);
');

case 'image_list.js':
$s='';
foreach(glob($GLOBALS['filehost']."2012/12/*.jpg") as $l) {
	$l=str_replace('/var/www','',$l);
	$s.="\n[\"".h(basename($l))."\",\"".h($l)."\"],";
}

die('
// This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There images will be displayed as a dropdown in all image dialogs if the "external_link_image_url"
// option is defined in TinyMCE init.

var tinyMCEImageList = new Array( // Name, URL
'.rtrim($s,',').'
);
');

//        ["Logo 1 R", "media/logo.jpg"],
//        ["Logo 2 Over R", "media/logo_over.jpg"]


case 'media_list.js': die('
// This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There flash movies will be displayed as a dropdown in all media dialog if the "media_external_list_url"
// option is defined in TinyMCE init.

var tinyMCEMediaList = [
        // Name, URL
        ["Some Flash", "media/sample.swf"],
        ["Some Quicktime", "media/sample.mov"],
        ["Some AVI", "media/sample.avi"],
        ["Some RealMedia", "media/sample.rm"],
        ["Some Shockwave", "media/sample.dcr"],
        ["Some Video", "media/sample.mp4"],
        ["Some FLV", "media/sample.flv"]
];
');

default: die('this module for TinyMCE only');

	}

	}

?>