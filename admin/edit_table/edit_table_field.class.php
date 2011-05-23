<?php
################ IMPLEMENTATION ##################################

require_once(dirname(__FILE__) . '/iedit_table_field.class.php');

// define default field type - simple text
class edit_table_field implements iedit_table_field {

	protected $field_title = '';
	protected $field_database_name = '';
	protected $field_width = '';
	
	protected $is_readonly = false;
	protected $is_editonly = false;
	protected $default_value = '';
	
	// some filters to the field that will be applied after the field HTML is created
	protected $filter = array();
	
	
	// basic 'set' functions for the protected parameters
	public function set_readonly() { $this->is_readonly = true; return $this; }
	public function is_readonly() { return $this->is_readonly; }
	public function set_editonly() { $this->is_editonly = true; return $this; }
	public function is_editonly() { return $this->is_editonly; }
	public function set_default_value($def_val) { $this->default_value = $def_val; return $this; }
	public function get_default_value() { return $this->default_value; }
	public function has_default_value() { return !empty($this->default_value); }
	public function get_title() { return $this->field_title; }	
	public function set_title($field_title) { $this->field_title = $field_title; return $this; }
	public function get_database_name() { return $this->field_database_name; }
	public function set_database_name($field_database_name) { $this->field_database_name = $field_database_name; return $this; }
	public function is_file() { return false; }

	
	
	// default constructor
	public function __construct($field_title = '', $field_database_name = '', $field_width = 100) {
		$this->field_title 			= $field_title;
		$this->field_width 			= $field_width;
		$this->field_database_name 	= $field_database_name;
	}

	// function which adds some functions, that are applied 
	// after the output had beed rendered.
	public function add_filter($filter_function) {
		if (!empty($filter_function) && function_exists($filter_function)) {
			$this->filters[] = $filter_function;
		}
	}
	
	
	// function which applies all the filters to the var $output 
	public function apply_filters($output, $row_recordset) {
		if (!empty($this->filters)) {
			foreach ($this->filters as $filter) {
				
				// call the user functions
				$output = $filter($output, $row_recordset);
				
			}
		}
		
		return $output;
	}
	
	
	// get/set the width of a table (in pixels)
	public function get_width() {
		return $this->field_width;
	}
	public function set_width($width) {
		if (!is_numeric($width)) {
			$width = intval($width);
		}
		
		if (!is_numeric($width) || !($width > 0)) {
			if (defined('DEBUG_MODE')) {
				echo '<b>Error in ', __FUNCTION__,' class ',__CLASS__, ' width ', $width, ' is not numeric</b><br>';
			}
			
			return $this;
		}
		$this->field_width = $width;
		return $this;
	}
	
	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset) {
		if ($this->is_editonly()) return '&nbsp;';
		
		if (!is_array($row_recordset) || empty($row_recordset)) 
			return '&nbsp;';
			
		$field_value = $row_recordset[$this->field_database_name];
		$field_value = html_escape($field_value);
		$output = '';
		if (empty($field_value)) {
			$output = '&nbsp;';
		} else {
			$output = $field_value;
		}
		
		$output = $this->apply_filters($output, $row_recordset);
		
		return $output;
	}

	// get the edition function (what's drawn when you want to edit the field)
	public function get_edit_mode($row_recordset, $html_input_id) {	
		if ($this->is_readonly()) return '';
		if (empty($row_recordset) && !empty($this->default_value)) {
			$retVal = '<input type="hidden" 
				name="'.$html_input_id.'" 
				value="'.html_escape($this->get_default_value()).'">';
			return $retVal;
		}
		
		// get field value
		$field_value = $row_recordset[$this->field_database_name];
		$field_value = html_escape($field_value);
		
		// return the output
		$html_input_id = html_escape($html_input_id);;
		$html = '<input autocomplete="off" type="text" id="'.$html_input_id.'" name="'.$html_input_id.'" value="';
		$html.= $field_value;
		$html.= '" style="width:'.($this->field_width-5).'px" />';
		return $html;
	}
	
	public function get_value_from_post($post_val) {
		if (!empty($this->default_value)) {
			return $this->get_default_value();
		} else {
			return $post_val;
		}
	}
}

?>