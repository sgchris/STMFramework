<?php

################ IMPLEMENTATION ##################################

require_once(dirname(__FILE__) . '/edit_table_field.class.php');
require_once(dirname(__FILE__) . '/../spaw2/spaw.inc.php');

// define default field type - simple text
class edit_table_field_wysiwyg extends edit_table_field {

	protected $field_height = 150;
    
	// define the max text length for "display" mode
    protected $field_max_display_len = 150;
	
	// default constructor
	public function __construct($field_title = '', $field_database_name = '', $field_width = 300) {
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
	
    protected function br2nl($string){
        $return=eregi_replace('<br[[:space:]]*/?'.'[[:space:]]*>',chr(13).chr(10),$string);
        return $return;
    } 
    
	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset) {
		if ($this->is_editonly()) return '&nbsp;';
		
		if (!is_array($row_recordset) || empty($row_recordset)) 
			return '&nbsp;';
			
		$field_value = $row_recordset[$this->field_database_name];
        
        $field_value_small = substr(strip_tags($this->br2nl($field_value)), 0, $this->field_max_display_len);
        if (strlen($field_value) >= $this->field_max_display_len) $field_value_small.= '...';
		$field_value_small = nl2br(html_escape($field_value_small));
		return !empty($field_value_small) ? trim($field_value_small) : '&nbsp;';
	}

	// get the edition function (what's drawn when you want to edit the field)
	public function get_edit_mode($row_recordset, $html_input_id) {	
		// check the default value
		if (empty($row_recordset) && !empty($this->default_value)) {
            $spaw = new SpawEditor($html_input_id, html_escape($this->get_default_value()));
            $spaw->setDimensions(($this->field_width - 30).'px', ($this->field_height - 30).'px');
            $spaw->show();
		}
		
		// check readonly mode
		if ($this->is_readonly()) {
			return $this->get_display_mode($row_recordset);
		}
		
		
		// get field value
		$field_value = $row_recordset[$this->field_database_name];
		
		// return the output
		$html_input_id = html_escape($html_input_id);
        $spaw = new SpawEditor($html_input_id, $field_value);
        $spaw->setDimensions(($this->field_width - 30).'px', ($this->field_height - 30).'px');
		return $spaw->getHtml();
	}
	
	public function get_value_from_post($post_val) {
		if (!empty($this->default_value)) {
			return $this->get_default_value();
		}
		
		return $post_val;
	}
}

?>