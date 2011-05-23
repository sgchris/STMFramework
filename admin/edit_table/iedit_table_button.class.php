<?php

/**
The file contains an interface for the button object for the 
edit_table class
*/

interface iedit_table_button {	
	/**
	return the javascript code (without <script> tags)
	*/
	public function get_javascript();
	/**
	get the CSS classes (without <style> tags
	*/
	public function get_css();
	/**
	get the html of the table
	*/
	public function get_html();	
	/**
	set the text inside the button
	*/
	public function set_caption($p_caption);
}

?>