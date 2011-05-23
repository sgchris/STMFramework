<?php
##
# INTERFACE for the field class in the 'edit_table' object
#
################ INTERFACE #######################################

interface iedit_table_field {
	// get the title of a column
	public function get_title();
	public function set_title($field_title);
	
	// get/set the field name in the database
	public function get_database_name();
	public function set_database_name($field_database_name);
	
	// get/set the width of a table (in pixels)
	public function get_width();
	public function set_width($width);
	
	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset);	

	// get the edition function (what's drawn when you want to edit the field)
	// if the recordset parameter is empty, than return the "add new" field mode
	public function get_edit_mode($row_recordset, $html_input_id);
	
	// get the real value from a parameter
	// i.e. parameter is what comes in the post and the output is the value for database query
	public function get_value_from_post($post_val);
}


?>