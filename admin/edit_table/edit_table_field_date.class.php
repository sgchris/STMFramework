<?php

require_once (dirname(__FILE__).'/edit_table_field.class.php');

class edit_table_field_date extends edit_table_field {

	protected $date_format = 'd M Y (H:i)';
	
	// change the way that the date is displayed
	public function set_date_format($format) {
		if (!empty($format)) {
			$this->date_format = $format;
		}
		
		return $this;
	}
	
	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset) {
		if ($this->is_editonly()) return '';
		
		
		$field_value = $row_recordset[$this->field_database_name];
		if (!is_numeric($field_value) || !($field_value > 0)) {
			return '&nbsp;';
		}
		
		$field_value = date($this->date_format, $field_value);
		if (empty($field_value)) {
			return '&nbsp;';
		} else {
			return $field_value;
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
		$field_value = empty($field_value)? time() : $field_value;
		
		// check that the value is ok
		if (!empty($field_value) && !($field_value > 0)) {
			// ERROR!
			if (defined('DEBUG_MODE')) {
				echo '<b>Error in ', __FUNCTION__,' class ',__CLASS__, 
					' date format wrong - '.$field_value.'</b><br>';
			}
			return '';
		}
		
		$field_value = date('d/m/Y H:i', $field_value);
		
		// return the output
		$html_input_id = htmlentities($html_input_id, ENT_QUOTES);
		$html = '<input autocomplete="off" type="text" id="'.$html_input_id.'" name="'.$html_input_id.'" value="';
		$html.= $field_value;
		$html.= '" style="width:'.($this->field_width-5).'px" />';
		return $html;
	}


	public function get_value_from_post($post_val) {
		if (!empty($this->default_value)) {
			return $this->get_default_value();
		}
		
		// check that the date is in time format
		if (!preg_match('%^([\d]+)/([\d]+)/([\d]+)\ +([\d]*)[:]*([\d]*)$%', $post_val, $res_arr)) {
			return false;
		}
		
		$hour 	= empty($res_arr[4])?0:$res_arr[4];
		$min 	= empty($res_arr[5])?0:$res_arr[5];
		$time = mktime($hour, $min, 0, $res_arr[2], $res_arr[1], $res_arr[3]);
		
		return $time;
	}
}


?>