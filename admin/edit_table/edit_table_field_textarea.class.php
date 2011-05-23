<?php
################ IMPLEMENTATION ##################################

require_once(dirname(__FILE__) . '/edit_table_field.class.php');

// define default field type - simple text
class edit_table_field_textarea extends edit_table_field {

	protected $field_height = 150;
	
	// define the max text length for "display" mode
    protected $field_max_display_len = 150;
	
	// default constructor
	public function __construct($field_title = '', $field_database_name = '', $field_width = 100) {
		parent::__construct($field_title, $field_database_name, $field_width);
	}

	// get/set the width of a table (in pixels) - MUST BE NUMERIC
	public function get_height() {
		return $this->field_height;
	}
	public function set_height($height) {
		if (!is_numeric($height)) {
			$width = intval($height);
		}
		
		if (!is_numeric($height) || !($height > 0)) {
			throw new Exception('<b>Error in ', __FUNCTION__,' class ',__CLASS__, ' height ', $height, ' is not numeric</b><br>');
	
			return $this;
		}
		$this->field_height = $height;
		return $this;
	}
	
	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset) {
		if ($this->is_editonly()) return '';
		
		if (!is_array($row_recordset) || empty($row_recordset)) 
			return '';
			
		$field_value = $row_recordset[$this->field_database_name];
		$field_value_small = htmlentities($field_value, ENT_QUOTES, "utf-8");
        $field_value_small = substr($field_value_small, 0, $this->field_max_display_len);
        $field_value_small = nl2br($field_value_small);
		
        if (strlen($field_value) >= $this->field_max_display_len) $field_value_small.= '...';
		return !empty($field_value_small) ? trim($field_value_small) : '&nbsp;';
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
		$field_value = html_escape($field_value);
		
		// return the output
		$html_input_id = html_escape($html_input_id);;
		$html = '<textarea  style="width:'.($this->field_width-5).'px;height:'.($this->field_height-5).'px" id="'.$html_input_id.'" name="'.$html_input_id.'">';
		$html.= $field_value;
		$html.= '</textarea>';
		return $html;
	}
	
	public function get_value_from_post($post_val) {
		if (!empty($this->default_value)) {
			return $this->get_default_value();
		}
		
		return strip_tags($post_val, '<b><u><i>');
	}
}

?>