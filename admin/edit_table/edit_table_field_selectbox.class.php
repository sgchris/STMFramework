<?php

require_once (dirname(__FILE__).'/edit_table_field.class.php');

class edit_table_field_selectbox extends edit_table_field {

	/**
	* @access private
	* the name of the field that will be shown in the select box
	*/
	protected $name_field = 'name';
	protected $table_name = '';
	protected $order = 'id';
	protected $extra_condition = '';
	protected $alter_sql = '';
	
	static $results = null;

	
	public function __construct($field_title, $field_database_name, $table_name, $name_field = 'name', $field_width = 100) {
		$this->table_name = $table_name;
		$this->name_field = $name_field;		
		
		parent::__construct($field_title, $field_database_name, $field_width);
	}
	
	
	// add conditions to the SQL of the selectbox
	// e.g. array('parent_id'=>0)
	public function add_extra_condition($str) {
		$this->extra_condition = $str; 
	}
	
	/**
	 * the alter. sql must contain field "name" and "id"
	 * This is only for edit mode!!! - the "show" remains
	 * the same (therefore you must work on the same table)
	 *
	 * @param String $sql
	 */
	public function set_alternative_sql($sql) {
		$this->alter_sql = $sql;
	}
	
	// change the way that the date is displayed
	public function set_name_field($name_field) {
		if (!empty($format)) {
			$this->name_field = $name_field;
		}
		
		return $this;
	}

	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset) {
		if ($this->is_editonly()) return '';
		
		$this->check_rs();
		
		$rec_id = $row_recordset[$this->field_database_name];
		$res_text = $this->get_name_by_id($rec_id);
		if (empty($res_text)) {
			$res_text = '&nbsp;';
		}
		return $res_text;
	}
	
	// get the edition function (what's drawn when you want to edit the field)
	public function get_edit_mode($row_recordset, $html_input_id) {	
	
		$this->check_rs();
		
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
		
		// set the input id
		$html_input_id = htmlentities($html_input_id, ENT_QUOTES);
		
		// get field value
		$field_value = $row_recordset[$this->field_database_name];
		$field_value = ($field_value > 0) ? $field_value : '';
		
		$html = '<select style="width:'.$this->field_width.'px;overflow:hidden;font:12px arial" name="'.$html_input_id.'">';
		$html.= '<option value=""></option>';
		
		if (!empty($this->results_for_edit_mode)) {
			foreach ($this->results_for_edit_mode as $res) {
				$sel = '';
				if ($field_value == $res['id']) {
					$sel = 'selected';
				}
				$html.= '<option value="'.$res['id'].'" '.$sel.'>'.$res[$this->name_field].'</option>';
			}
		}
		
		$html.= '</select>';
		return $html;
	}


	public function get_value_from_post($post_val) {
		if (!empty($this->default_value)) {
			return $this->get_default_value();
		}
		
		return $post_val;
	}
	
	
	protected function check_rs() {
		if (empty($this->results_for_edit_mode) || $this->results_for_edit_mode == null ||
			empty($this->results_for_show_mode) || $this->results_for_show_mode == null) {
			$sql = 'select `id`, `'.$this->name_field.'` from `'.$this->table_name.'` ';
			if (!empty($this->extra_condition)) {
				$sql .= ' where '.$this->extra_condition;
			}
			$sql .= ' order by `'.$this->order.'`';
			
			if (!empty($this->alter_sql)) {
				database::query($this->alter_sql);
			} else {
				database::query($sql);
			}
			$this->results_for_edit_mode = database::$arr_list;
			
			if (!empty($this->alter_sql)) {
				database::query($sql);
			}
			
			$this->results_for_show_mode = database::$arr_list;
		}
	}
	
	
	protected function get_name_by_id($rec_id) {
		if (empty($this->results_for_show_mode)) return '';
		
		foreach ($this->results_for_show_mode as $res) {
			if ($res['id'] == $rec_id) {
				return $res[$this->name_field];
			}
		}
		
		return '';
	}
}


?>