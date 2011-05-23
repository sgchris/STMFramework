<?php
/**
 * The class is the same as its parent (edit_table), but only on one row
 * and is displayed in vertical mode.. like regular form
 * the only mode is "edit"
 */

require_once(dirname(__FILE__) . '/edit_table.class.php');

class edit_table_row extends edit_table {

    protected $row_id = '';


	// constructor (default table may be defined)
	public function __construct($table_name, $row_id) {
		parent::__construct($table_name);
        $this->row_id = $row_id;
	}
    
    
	
	/** 
	display the table in edit mode
	*/
	public function show() {
        
		// check if the form needs a transaction
		try {
			$this->__check_transaction();
		} catch (Exception $e) {
			if (defined('DEBUG_MODE'))
				echo $e->getMessage();
		}
        
        
		// get the recordset for the table
		$rs = $this->__get_rs();
		if (empty($rs[0])) {
            throw new Exception('No recordset ('.__FUNCTION__.'/'.__CLASS__.')');
            return false;
        }

		
		// display the CSS
		$html = $this->__get_css_styles();
		
		// set the buttons for the table
		$this->__set_edit_buttons_array();
		$html .= $this->__get_buttons_javascript();
		
		// display the titles row
		$html.= $this->__display_table_h1();;
		
		// open the table div
		$html .= '<form name="edit_table_form" action="'.$this->links_prefix.'" method="post" enctype="multipart/form-data">';
		$html .= '<input type="hidden" name="etet" value="'.($_SESSION['etet']=md5(rand(0,10000))).'"/>';
		$html .= '<input type="hidden" name="edit_table_ids[]" value="'.$this->row_id.'"/>';
		$html .= '<div id=edit_table_div class=edit_table_div>';
        
        if (!empty($this->fields)) foreach ($this->fields as $field_obj) {
			$html .= 
				'<div class="edit_table_add_form_row">'
					.'<div class="edit_table_add_form_label">'
						.$field_obj->get_title()
					.'</div>'
					.'<div class="edit_table_add_form_field">'
						.$field_obj->get_edit_mode($rs[0], 'field_'.$this->row_id.'_'.$field_obj->get_database_name())
					.'</div>'
					.'<div class="edit_table_clear"></div>'
				.'</div>';
		}
        
		// display the bottom buttons
		$html.= $this->__get_buttons_html();
		
		// close the whole table
		$html.='</div><div class="edit_table_clear"></div>';
		$html.='</form>';
		
		echo $html;
		return $this;
	}
    
    
    
    
    
    
	/**
	reads data from the table that matches the ids list from GET
	and returns a recordset (using database class)
	*/
	protected function __get_rs() {
        if (!($this->row_id > 0)) {
            throw new Exception('no row id supplied!');
            return false;
        }
        
		$field_names_arr = array();
		foreach($this->fields as $field_obj) {
			$field_names_arr[] = '`'.$field_obj->get_database_name().'`';
		}
			
		$sql = 'SELECT `'.$this->id_field_name.'`'
			.(!empty($field_names_arr) ? ',' : '')
			.implode(',',$field_names_arr)
			.' FROM `'.$this->default_table.'` WHERE `id` = "'.$this->row_id.'"';
		
		database::query($sql);
		return database::$arr_list;
	}
    
}


?>