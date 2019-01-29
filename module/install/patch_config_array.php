<?php

function installmod_init() {
	if($GLOBALS['uset']=='{rray();' or $GLOBALS['uset']=='array();') config_change('uset','array()');
	return false;
}

?>