<?php

require_once (dirname(__FILE__).'/edit_table_field.class.php');

class edit_table_field_bool extends edit_table_field {
	
	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset) {
		if ($this->is_editonly()) return '';
		
		if (!is_array($row_recordset) || empty($row_recordset)) 
			return '';
			
		$field_value = $row_recordset[$this->field_database_name];
		
		if (!empty($field_value) && $field_value > 0) {
			return '<span style="color:green">Yes</span>';
		} else {
			return '<span style="color:#CC141E">No</span>';
		}
	}
	
	
	// get the edition function (what's drawn when you want to edit the field)
	public function get_edit_mode($row_recordset, $html_input_id) {	
		// check the default value
		if (empty($row_recordset) && !empty($this->default_value)) {
			return '<input type="hidden" 
				name="'.$html_input_id.'" 
				value="'.html_escape($this->get_default_value()).'">';
		}
		
		// check readonly mode
		if ($this->is_readonly()) {
			return $this->get_display_mode($row_recordset);
		}
		
		// get field value
		$field_value = $row_recordset[$this->field_database_name];
		$field_value = htmlentities($field_value, ENT_QUOTES);
		
		// return the output
		$html_input_id = htmlentities($html_input_id, ENT_QUOTES);
		$html = '<input type="checkbox" id="'.$html_input_id.'" name="'.$html_input_id.'" ';
		$html.= (!empty($field_value) && $field_value > 0)?'checked':'';
		$html.= ' />';
		return $html;
	}
	
	public function get_value_from_post($post_val) {
		if (!empty($this->default_value)) {
			return $this->get_default_value();
		}
		
		return empty($post_val) ? '0' : '1';
	}

}


?>