<?php
/*
	- add "add" option
	- add "edit_row" option (vertical form)
 
	- add options:
		* set_default_value to some fields
		* some fields have to be non-editable
		* when creating instance of 'edit table', the 
			user has to supply buttons
*/		



//
// the class extends the base version of the table
// which is only displaying data.
//
require_once(dirname(__FILE__) . '/edit_table_base.class.php');
require_once(dirname(__FILE__) . '/edit_table_button_save.class.php');

class edit_table extends edit_table_base {

	// constructor (default table may be defined)
	public function __construct($table_name = '') {
		parent::__construct($table_name);
	}
	
	/**
	checks what action should be passed to the "show" function
	*/
	static function detect_action() {
		if ($_GET['edit_data'] == 1 && !empty($_GET['edit_ids'])) {
			return edit_table_base::EDIT_DATA;
		} elseif ($_GET['new_record'] == 1) {
			return edit_table_base::NEW_RECORD;
		} else {
			return edit_table_base::SHOW_DATA;
		}
	}
	
	
	
	/**
	display the table
	parameter: SHOW_DATA / EDIT_DATA / NEW_RECORD
	the parameter may be remained "null", in this case the 
	operation is determined automatically.
	*/
	public function show($display_type = null) {
		
		if ($display_type == null) {
			$display_type = self::detect_action();
		}
		
		// check if the form needs a transaction
		try {
			$this->__check_transaction();
		} catch (Exception $e) {
			if (defined('DEBUG_MODE'))
				echo $e->getMessage();
		}
		
		// redirect to the proper 'show' function
		try {
			switch ($display_type) {
				case self::SHOW_DATA:
					parent::show();
					return $this;
					break;
				case self::EDIT_DATA:
					$this->show_edit_mode();
					return $this;
					break;
				case self::NEW_RECORD:
					$this->show_new_mode();
					return $this;
					break;
				default:
					if (defined('DEBUG_MODE')) {
						echo '<b>Error in ', __FUNCTION__,' class ',__CLASS__, ' Error in parameter "display_type"</b><br>';
					}
					break;
			}
		} catch (Exception $e) {
			if (defined('DEBUG_MODE')) 
				echo $e->getMessage();
		}
		
		return $this;
	}


	
	
	/** 
	display the table in edit mode
	*/
	protected function show_edit_mode() {
		// get the recordset for the table
		$rs = $this->__get_rs();
		
		if (empty($rs)) {
			throw new Exception ('<b>Error in ', __FUNCTION__,' class ',__CLASS__, ' Empty RS in edit mode</b><br>');
		}
		
		// display the CSS
		$html = $this->__get_css_styles();
		
		// set the buttons for the table
		$this->__set_edit_buttons_array();
		$html .= $this->__get_buttons_javascript();
		
		// display the titles row
		$html.= $this->__display_table_h1();
		
		// open the table div
		$html .= '<form name="edit_table_form" action="'.$this->links_prefix.'" method="post" enctype="multipart/form-data">';
		$html .= '<input type="hidden" name="etet" value="'.($_SESSION['etet']=md5(rand(0,10000))).'"/>';
		$html .= '<div id=edit_table_div class=edit_table_div>';
		$html .= '
		<div>
			<div class="edit_table_titles_row edit_table_checkbox" style="margin:2px;padding:0px;visibility:hidden;">&nbsp;</div>';
			
			if (!empty($this->fields)) foreach($this->fields as $field_obj) {
				if ($field_obj->is_readonly()) {
					continue;
				}
				
				$html.= '<div class="edit_table_titles_row" style="width:'.$field_obj->get_width().'px">';
				$html.= '<b>'.$field_obj->get_title().'</b>';
				$html.= '</div>';
			}
			$html.= '<div class="edit_table_clear"></div>';
		$html.= '
		</div>';
		
		// display the values
		if (!empty($rs)) foreach($rs as $row) {
			$html .= '<div class="edit_table_data_row">
				<div class="edit_table_data_col edit_table_checkbox" style="margin:2px;padding:0px">
					<input type="checkbox" name="select_row" disabled="disabled" />
					<input type="hidden" name="edit_table_ids[]" value="'.$row['id'].'" />
				</div>
			';
			
			$counter = 0;
			if (!empty($this->fields)) foreach($this->fields as $field_obj) {
				// check if the field is readonly
				if ($field_obj->is_readonly()) {
					continue;
				}
				
				$html.= '<div class="edit_table_data_col" style="width:'.$field_obj->get_width().'px">';
				$html.= $field_obj->get_edit_mode($row, 'field_'.$row['id'].'_'.$field_obj->get_database_name());
				$html.= '</div>';				
			}
			$html.= '<div class="edit_table_row_closer"></div>';
			$html.= '</div>';
		}
		
		// display the bottom buttons
		$html.= $this->__get_buttons_html();
		
		// close the whole table
		$html.='</div><div class="edit_table_clear"></div>';
		$html.='</form>';
		$html.='<script type="text/javascript">';
		$html.=$this->__display_buttons_css_script();
		$html.='</script>';
		
		echo $html;
		return $this;
	}
	

	
	/**
	display the table in "new" mode - add new record to the database
	*/
	protected function show_new_mode() {
		
		// display the CSS
		$html = $this->__get_css_styles();
		
		// set the buttons for the table
		$this->__set_add_buttons_array();
		$html .= $this->__get_buttons_javascript();
		
		// display the titles row
		$html.= $this->__display_table_h1();;
		
		// open the table div
		$html .= '<form name="edit_table_form" action="'.$this->links_prefix.'" method="post" enctype="multipart/form-data">';
		$html .= '<input type="hidden" name="etat" value="'.($_SESSION['etat']=md5(rand(0,10000))).'"/>';
		$html .= '<div id=edit_table_div class=edit_table_div>';
		$html .= '<div>';
			
		if (!empty($this->fields)) foreach ($this->fields as $field_obj) {
			if ($field_obj->has_default_value()) {
				$html .= $field_obj->get_edit_mode(array(), 'field_new_'.$field_obj->get_database_name());
			} else {
				$html .= 
					'<div class="edit_table_add_form_row">'
						.'<div class="edit_table_add_form_label">'
							.$field_obj->get_title()
						.'</div>'
						.'<div class="edit_table_add_form_field">'
							.$field_obj->get_edit_mode(array(), 'field_new_'.$field_obj->get_database_name())
						.'</div>'
						.'<div class="edit_table_clear"></div>'
					.'</div>';
			}
		}
		
		// display the bottom buttons
		$html.= $this->__get_buttons_html();
		
		// close the whole table
		$html.='</div><div class="edit_table_clear"></div>';
		$html.='</form>';
		$html.='<script type="text/javascript">';
		$html.=$this->__display_buttons_css_script();
		$html.='</script>';
		
		print $html;
		return $this;
	}

	
	/** 
	build buttons for the "edit" and "add" form
	the available parameters are EDIT_DATA, SHOW_DATA and NEW_RECORD
	(SHOW_DATA is managed in the base class)
	*/
	protected function __get_edit_buttons_array($mode = self::EDIT_DATA) {
		$butts_arr = array();
		
		$save_label = 'Save';
		if ($mode == self::NEW_RECORD) {
			$save_label = 'Add';
		}
		$but = new edit_table_button($save_label, 'save_changes()', 'edit_table_button');
		$but->set_image($this->images_path.'/edit_table/images/save.png', 32, 32);
		$butts_arr[] = $but;
				
		$but = new edit_table_button('Return', 'cancel_button()', 'edit_table_button');
		$but->set_image('edit_table/images/cancel.png', 32, 32);
		$butts_arr[] = $but;
				
		return $butts_arr;
	}

	
	/**
	removes slashed which the server adds, if magic_quotes are defined in the INI file.
	*/
	protected function __remove_magic_quotes_to_post() {
		// check the magic quotes - remove slashes!
		if (get_magic_quotes_gpc()) {
			foreach($_POST as $idx=>$data) {
				if (is_array($data)) continue;
				$_POST[$idx] = stripslashes($_POST[$idx]);
			}
		}
	}
	
	
	/**
	checks transaction on the "edit" operation
	*/
	protected function __check_transaction_data() {
		if (empty($_POST['etet']) ||
			$_POST['etet'] != $_SESSION['etet']) {
			return false;
		}
		
		// check that the table has columns
		if (empty($this->fields)) {
			return false;
		}
		$this->__remove_magic_quotes_to_post();
		
		// get the needed IDs from the 
		if (!$this->ids_post_is_ok($_POST['edit_table_ids'])) {
			#throw new Exception('post IDs error!...exiting ('.__FUNCTION__.'/'.__CLASS__.')<br>');
			return false;
		}
		$ids_arr = $_POST['edit_table_ids'];
		
		// build the sql
		foreach($ids_arr as $id) {
			$sql = 'UPDATE `'. $this->default_table.'` SET ';
			$update_fields_arr = array();
			foreach($this->fields as $field_obj) {
				$skip_field = false;
				if ($field_obj->is_file()) {
					$file = $_FILES['field_'.$id.'_'.$field_obj->get_database_name()];
					if (!empty($file) && $file['error'] == 0) {
						$value = $field_obj->get_value_from_post($file);
					 } elseif (!empty($_POST['del_field_'.$id.'_'.$field_obj->get_database_name()])) {
						// get the file from the database
						database::query('
							SELECT `'.$field_obj->get_database_name().'` 
							FROM `'.$this->default_table.'` 
							WHERE `id` = '.$id.' 
							LIMIT 1');
							
						// delete the file physically
						if (!empty(database::$arr_list)) {
							$field_obj->delete_file(database::$arr_list[0][$field_obj->get_database_name()]);
						}
						
						// update the field to an empty
						$value = '';
					 } else {
						$skip_field = true;
					 }
				} else {
					$post_val_for_field = $_POST['field_'.$id.'_'.$field_obj->get_database_name()];
					$value = $field_obj->get_value_from_post($post_val_for_field);
					if ($value === false) {
						continue;
					} else {
						$value = mysql_real_escape_string($value);
					}
				}
				
				if (!$skip_field) {
					$update_fields_arr[] = $field_obj->get_database_name().'="'.$value.'"';
				}
			}
			$sql.=implode(',', $update_fields_arr);
			$sql.= ' WHERE `id` = '.$id;
			
			if (!empty($update_fields_arr)) {
				database::query($sql);
			}
		}
		unset($_SESSION['etet']);		
		return true;
	}
	
	
	/**
	checks transaction on the "add new record" operation
	*/
	protected function __check_transaction_data_add() {
		// check the submit token
		if (empty($_POST['etat']) ||
			$_POST['etat'] != $_SESSION['etat']) {
			return false;
		}
		
		// remove slashes
		$this->__remove_magic_quotes_to_post();
		
		$fields = array();
		$values = array();
		$insert_arr = array();
		foreach ($this->fields as $field_obj) {
			if ($field_obj->is_file()) {
				$file = $_FILES['field_new_'.$field_obj->get_database_name()];
				if (!empty($file) && $file['error'] == 0) {
					$value = $field_obj->get_value_from_post($file);
					$insert_arr[$field_obj->get_database_name()] = $value;
				} 
			} else {
				$field_post_val = $_POST['field_new_' . $field_obj->get_database_name()];
				$insert_arr[$field_obj->get_database_name()] =
					$field_obj->get_value_from_post($field_post_val);
			}
		}
		
		// the insert function manages the special chars
		if (!empty($insert_arr)) {
			database::insert($this->default_table, $insert_arr);
		}
		unset($_SESSION['etat']);
	}
	
	
	/**
	check if any transaction has to be executed
	*/
	protected function __check_transaction() {
		parent::__check_transaction();
		$this->__check_transaction_data();
		$this->__check_transaction_data_add();
	}


	/**
	reads data from the table that matches the ids list from GET
	and returns a recordset (using database class)
	*/
	protected function __get_rs() {
		$sql = '';
		
		// check for alternative SQL query
		if (!empty($this->sql)) {
			throw new Exception('Alternative query defined!');
		} else {
			$field_names_arr = array();
			foreach($this->fields as $field_obj) {
				$field_names_arr[] = '`'.$field_obj->get_database_name().'`';
			}
			
			$sql = 'SELECT `'.$this->id_field_name.'`'
				.(!empty($field_names_arr) ? ',' : '')
				.implode(',',$field_names_arr)
				.' FROM `'.$this->default_table.'`';
			
			$where_included = false;
			if ($_GET['edit_data'] == 1 && !empty($_GET['edit_ids']) 
				&& !empty($_GET['etet'])) {
				if ($this->ids_get_is_ok($_GET['edit_ids'])) {
					$sql .= ' WHERE `id` IN ('.$_GET['edit_ids'].') ';
					$where_included = true;
				}
			} elseif ($this->start_id > 0 && $this->finish_id > 0) {
				$sql .= ' WHERE `id` BETWEEN '.$this->start_id.' AND '.$this->finish_id;
				$where_included = true;
			}
			
			if (!empty($this->extra_conditions)) {
				if (!$where_included) {
					$sql .= ' WHERE ';
				} else {
					$sql .= ' AND ';
				}
				
				$conds = array();
				foreach($this->extra_conditions as $idx=>$val) {
						
					// check if the condition is "in"
					if (preg_match('%\s+in$%i', $idx)) {
						$idx = trim(preg_replace ('%in$%i', '', $idx));
						$conds[] = "`{$idx}` IN ({$val})";
					} else {
						$val = mysql_real_escape_string($val);
						$conds[] = "`{$idx}` = '{$val}'";
					}
				}
					
				$sql .= implode(' AND ', $conds);
			}
			
			// add order and limit
			$sql .= ' ORDER BY `'.$this->order_by_field.'` '.$this->order_method;			
			
			// calculate total rows in the query
			$new_sql = preg_replace('/select(.*?)from/mi', 'select count(*) total_rows from', $sql);
			database::query($new_sql);
			$total = database::$arr_list[0]['total_rows'];
			if ($total > $this->max_rows_count) {
				$this->paging_needed = true;
				$this->paging_total_pages = floor($total / $this->max_rows_count);
				$this->paging_total_pages += $total % $this->max_rows_count == 0 ? 0 : 1;
				$this->paging_current_page = $_GET['etp'] > 1 ? $_GET['etp'] : 1;
			}
			if ($this->paging_current_page > 1) {
				$sql .= ' LIMIT '.(($this->paging_current_page - 1) * $this->max_rows_count) 
					.','
					.$this->max_rows_count;
			} else {
				$sql .= ' LIMIT '.$this->max_rows_count;
			}
		}
		
		database::query($sql);
		return database::$arr_list;
	}
	
	/**
	returns the default array of buttons for the table (edit mode)
	the array contains instances of implementation of "iedit_table_button" interface
	default buttons are: Mark, Unmark, Edit selected, Delete selected, Add new records
	*/
	protected function __set_edit_buttons_array() {
		// SAVE button
		$save_button = new edit_table_button_save();
		$save_button->set_container_id('edit_table_div')
					->set_form_name('edit_table_form');
		$this->buttons[] = $save_button;
		
		// CANCEL
		$cancel_button = new edit_table_button_basic();
		$cancel_button->set_image('edit_table/images/cancel.png', 16,16)
			->set_caption('cancel')
			->set_javascript('function cancel_action() { 
				document.location.href="'.$this->links_prefix.'";}', 'cancel_action');
		$this->buttons[] = $cancel_button;
	}

	
	/**
	returns the default array of buttons for the table (new mode)
	the array contains instances of implementation of "iedit_table_button" interface
	Buttons are: add, cancel
	*/
	protected function __set_add_buttons_array() {		
		// SAVE button
		$add_button = new edit_table_button_add();
		$add_button->set_form_name('edit_table_form');
		$this->buttons[] = $add_button;
		
		// CANCEL
		$cancel_button = new edit_table_button_basic();
		$cancel_button->set_image('edit_table/images/cancel.png', 16,16)
			->set_caption('cancel')
			->set_javascript('function cancel_action() { 
				document.location.href="'.$this->links_prefix.'";}', 'cancel_action');
		$this->buttons[] = $cancel_button;
	}




	/**
	the function checks if the POST parameter with all the ids
	of the rows (which were modified) are OK. (this is an array)
	*/
	protected function ids_post_is_ok($ids_arr) {
		// check the count
		if (count($ids_arr) < 1) {
			return false;
		}
		
		// check if all the ids are numbers
		foreach($ids_arr as $id) {
			if (!is_numeric($id)) {
				return false;
			}
		}
		
		return true;
	}


}
