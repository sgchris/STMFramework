<?php
/*********************************************
GLOBAL FUNCTIONS FOR "EDIT_TABLE" MODULE 
**********************************************/


/////////////////////////////////////////////////////
// escaping function for displaying data in an input box
// escapes chars: ",',\
function html_escape($str) {
	$str = htmlentities($str, ENT_QUOTES, 'UTF-8');
	$str = str_replace('\\', '&#92;', $str);
	return $str;
}


?>