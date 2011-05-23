<?php

require_once (dirname(__FILE__).'/edit_table_field.class.php');

class edit_table_field_password extends edit_table_field {
	
	// get the display function (what's drawn in the table)
	public function get_display_mode($row_recordset) {
		if (!is_array($row_recordset) || empty($row_recordset)) 
			return '';
			
		$field_value = $row_recordset[$this->field_database_name];
		if (!empty($field_value)) {
			$field_value = '<span style="color:#666">(encrypted)</span>';
		} else {
			$field_value = '&nbsp;';
		}
		
		return $field_value;
	}
	
	// get the edition function (what's drawn when you want to edit the field)
	public function get_edit_mode($row_recordset, $html_input_id) {	
		// get field value
		$field_value = $row_recordset[$this->field_database_name];
		
		// return the output
		$html_input_id = htmlentities($html_input_id, ENT_QUOTES);
		$html = '<input autocomplete="off" type="password" id="'.$html_input_id.'" name="'.$html_input_id.'" ';
		$html.= 'value="" style="width:'.($this->field_width-5).'px" />';
		if (!empty($field_value)) {
			$html.= '<div style="font:10px georgia;color:#AAA">(Leave empty to avoid changes)</div>';
		}
		return $html;
	}
	
	public function get_value_from_post($post_val) {
		if (empty($post_val))
			return false;
			
		return md5($post_val);
	}
	
}

?>