<?php

/**
INCLUDE ALL RELATED CLASSES - BUTTONS, FIELDS.
*/
require_once(dirname(__FILE__) . '/edit_table_functions.php');
require_once(dirname(__FILE__) . '/database.class.php');
require_once(dirname(__FILE__) . '/edit_table_field.class.php');
require_once(dirname(__FILE__) . '/edit_table_field_date.class.php');
require_once(dirname(__FILE__) . '/edit_table_field_bool.class.php');
require_once(dirname(__FILE__) . '/edit_table_field_password.class.php');
require_once(dirname(__FILE__) . '/edit_table_field_textarea.class.php');
require_once(dirname(__FILE__) . '/edit_table_field_wysiwyg.class.php');
require_once(dirname(__FILE__) . '/edit_table_field_file.class.php');
require_once(dirname(__FILE__) . '/edit_table_field_selectbox.class.php');

// buttons include
require_once(dirname(__FILE__) . '/edit_table_button_basic.class.php');
require_once(dirname(__FILE__) . '/edit_table_button_delete.class.php');
require_once(dirname(__FILE__) . '/edit_table_button_edit.class.php');
require_once(dirname(__FILE__) . '/edit_table_button_add.class.php');
require_once(dirname(__FILE__) . '/edit_table_button_mark.class.php');
/**
Base class for all 'edit_table' classes
Implements 'show' function for the table
*/
class edit_table_base {

	// constants for the 'show' function
	const SHOW_DATA 	= 0x10;
	const EDIT_DATA 	= 0x20;
	const NEW_RECORD 	= 0x30;
	

	// instances of "edit_table_field" class
	protected $fields 		= array();
	protected $buttons 		= array();
	protected $extra_buttons= array();
	protected $default_table= '';
	protected $sql			= '';
	protected $title		= 'Manage table';
	
	// paging related
	protected $paging_needed		= false;
	protected $paging_total_pages	= 1;
	protected $paging_current_page	= 1;
	
	
	protected $images_path			= 'edit_table/images';
	protected $links_prefix 		= '?';
	
	protected $start_id		= 0;
	protected $finish_id	= 0;
	
	/**
	 * Extra conditions for the sql
	 * The array looks like (e.g.) array('parent_id'=>10, 'type'=>15, 'name'=>'John')
	 *
	 * in the sql these conditions will be added into 'where' clause,
	 * imploded with ' AND ' operator
	*/
	protected $extra_conditions = array();
	public function set_extra_conditions($conds) {
		$this->extra_conditions = $conds;
		return $this;
	}
	
	/**
	 * Define from which ID to which ID include in the RS.
	 */
	public function set_range($start_id, $finish_id) {
		$this->start_id = $start_id;
		$this->finish_id = $finish_id;
		return $this;
	}
	
	protected $show_new_button 		= true;
	protected $show_delete_button 	= true;
	public function hide_new_button() { $this->show_new_button = false; return $this; }
	public function hide_delete_button() { $this->show_delete_button = false; return $this; }

	
	// public parameters
	public $max_rows_count 	= 15;
	public $id_field_name	= 'id';
	
	protected $order_by_field 	= 'id';
	protected $order_method 	= 'asc';
	
	// constructor (default table may be defined)
	public function __construct($table_name = '') {
		$this->default_table = $table_name;
	}
	
	
	/**
	* set the order for the RS
	*/
	public function order_by($field_name, $method = 'asc') {
		$this->order_by_field 	= $field_name;
		$this->order_method		= $method;
		return $this;
	}
	
	
	/**
	set the action of the form
	i.e. If you want the form to redirect to another page
	like:
		index.php?foo=1&bar=2
	than set the form action = '?foo=1&bar=2'
	*/
	public function set_links_prefix($prefix) {
		$this->links_prefix = $prefix;
		return $this;
	}

	/**
	defines what will appear in the path of images before 'edit_table/imageXXX.gif' (for e.g.)
	*/
	public function set_images_path($new_path) {
		// remove the last '/' char
		if ($new_path[strlen($new_path) - 1] == '/') {
			$new_path = substr($new_path, 0, strlen($new_path) - 1);
		}
		
		$this->images_path = $new_path;
		return $this;
	}
	
	/**
	set the title of the table
	the title appears as H1 above the table
	*/
	public function set_title($title) {
		$this->title = htmlentities($title, ENT_QUOTES, 'UTF-8');
		return $this;
	}
	
	/**
	You may set the table to show data from alternative SQL query.
	In this case no "edit", "add" and "delete" options will be available
	*/
	public function set_alternative_sql($sql) {
		$this->sql = $sql;
		return $this;
	}
	
	
	/**
	Adds a field to the object. the parameter must be an instance of 
	implementation of "iedit_table_field.class.php" interface
	*/
	public function add_field($field_obj) {
		if (empty($field_obj)) {
			throw new Exception('<b>Error in '.__FUNCTION__.' class '.__CLASS__.' field_obj is not defined </b><br>');
		}
		$this->fields[] = $field_obj;
		return $this;
	}




	/**
	draw the table in "show" mode - just displays results from a 
	particular table (or an alternative SQL query, if supplied before)
	*/
	public function show() {
		// get the recordset for the table
		$rs = $this->__get_rs();
		
		// display the CSS
		$html = $this->__get_css_styles();
		
		// set the buttons for the table
		$this->__set_base_buttons_array();
		$this->__add_extra_buttons();
		$html .= $this->__get_buttons_javascript();
		
		// display the titles row with the paging
		$html.= $this->__display_table_h1();

		// open the table div
		$html .= '<div id=edit_table_div class=edit_table_div>';
		$html .= '
		<div>
			<div class="edit_table_titles_row edit_table_checkbox" style="margin:2px;padding:0px;visibility:hidden;">&nbsp;</div>';
			
			if (!empty($this->fields)) foreach($this->fields as $field_obj) {
				// check if the field is only for edit
				if ($field_obj->is_editonly()) {
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
		$i=0;
		if (!empty($rs)) foreach($rs as $row) {
			$odd_class = '';
			if ($i++%2==0) {
				$odd_class = 'background:#EAEAEA';
			}
			$html .= '<div class="edit_table_data_row" style="'.$odd_class.'">'
				.'<div class="edit_table_data_col_checkbox edit_table_checkbox" style="margin:2px;padding:0px;">'
					.'<input type="checkbox" name="select_row" row_id="'.$row['id'].'"/>'
				.'</div>';
			
			$counter = 0;
			if (!empty($this->fields)) foreach($this->fields as $field_obj) {
				if ($field_obj->is_editonly()) {
					continue;
				}
				
				$html.= '<div class="edit_table_data_col" style="width:'.$field_obj->get_width().'px;'.$odd_class.';">';
				$html.= $field_obj->get_display_mode($row);
				$html.= '</div>';				
			}
			$html.= '<div class="edit_table_clear"></div>';
			$html.= '</div>';
		}
		
		// display the bottom buttons
		$html.= $this->__get_buttons_html();
		
		// close the whole table
		$html.='</div><div class="edit_table_clear"></div>';
		
		//$html.='<div style="text-align:center">';
		$html.= $this->__display_paging();
		//$html.='<div class="clear"></div>';
		//$html.='</div>';
	
		// define the "hover" and "click" functions
		$html.='
		<script type="text/javascript">
			$(".edit_table_data_col").hover(function() {
				$(this).addClass("edit_table_data_col_hover");
			}, function() {
				$(this).removeClass("edit_table_data_col_hover");
			}).click(function(){
				if ($(this).parent().children()[0].children[0].checked) {
					$(this).parent().children()[0].children[0].checked = "";
				} else {
					$(this).parent().children()[0].children[0].checked = "true";
				}
			});
			'.$this->__display_buttons_css_script().'
			</script>
		';
		
		echo $html;
		return $this;
	}
	
	
	/**
	The function adds extra user defined buttons
	The parameter must be an instance of "edit_table_button_basic" class
	*/
	public function add_button($button_obj, $to_start = false)
	{
		if (!$button_obj instanceof edit_table_button_basic) {
			throw new Exception('Error in '.__FUNCTION__.', the button object is not an instance of edit_table_button!');
			return false;
		}
		
		// add to the local array
		$this->extra_buttons[] = array('object'=>$button_obj, 'to_start'=>$to_start);
		return $this;
	}
	


	//////////////////////////////////////////////////////////////////////////////////////
	// PROTECTED FUNCTIONS ///////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////////////
	
	
	/**
	The function checks if there are extra (user-defined) buttons
	and adds them to the local $buttons array
	*/
	protected function __add_extra_buttons() {
		if (!empty($this->extra_buttons)) foreach ($this->extra_buttons as $but) {
			if ($but['to_start']) {
				array_unshift($this->buttons, $but['object']);
			} else {
				$this->buttons[] = $but['object'];
			}
		}
	}
	
	/**
	reads data from the table or the sql and returns a recordset
	(using database class)
	*/
	protected function __get_rs() {
		$sql = '';
		
		// check for alternative SQL query
		if (!empty($this->sql)) {
			$sql = $this->sql;
			
			// disable the buttons for an alternative SQL.
			$this->show_delete_button 	= false;
			$this->show_new_button 		= false;
		} else {
			$field_names_arr = array();
			foreach($this->fields as $field_obj) {
				$field_names_arr[] = '`'.$field_obj->get_database_name().'`';
			}
				
			$sql = 'SELECT `'.$this->id_field_name.'`'
				.(!empty($field_names_arr) ? ',' : '')
				.implode(',',$field_names_arr)
				.' FROM `'.$this->default_table.'`';
			
			
			$where_clause = array();
			
			// check if it's "edit mode" - get the required IDs
			if ($_GET['edit_data'] == 1 && !empty($_GET['edit_ids'])) {
				if ($this->ids_get_is_ok($_GET['edit_ids'])) {
					$where_clause[] = '`id` IN ('.$_GET['edit_ids'].')';
				}
			} elseif ($this->start_id > 0 && $this->finish_id > 0) {
				// check the range
				$where_clause[] = '`id` BETWEEN '.$this->start_id.' AND '.$this->finish_id;
			}
				
			// append extra conditions
			if (!empty($this->extra_conditions)) {
				foreach ($this->extra_conditions as $idx=>$val) {
					
					// check if the condition is "in" 
					if (preg_match('%\s+in$%i', $idx)) {
						$idx = trim(preg_replace ('%in$%i', '', $idx));
						$where_clause[] = "`{$idx}` IN ({$val})";
					} else {
						$where_clause[] = "`{$idx}` = '{$val}'";
					}
					
				}
			}
			
			// append "where clause"
			if (!empty($where_clause)) {
				$sql .= ' WHERE '.implode(' AND ', $where_clause);
			}
			
			// add order and limit
			$sql .= ' ORDER BY `'.$this->order_by_field.'` '.$this->order_method;
			 
			if ($this->paging_current_page > 1) {
				$sql .= ' LIMIT '.(($this->paging_current_page - 1) * $this->max_rows_count) 
					.','
					.(($this->paging_current_page) * $this->max_rows_count);
			} else {
				$sql .= ' LIMIT '.$this->max_rows_count;
			}
			
		}
		database::query($sql);
		return database::$arr_list;
	}
	
	/**
	returns the default array of buttons for the table
	the array contains instances of implementation of "iedit_table_button" interface
	default buttons are: Mark, Unmark, Edit selected, Delete selected, Add new records
	*/
	protected function __set_base_buttons_array() {
		// MARK/UNMARK button
		$mark_button = new edit_table_button_mark();
		$mark_button->set_container_id('edit_table_div');
		$this->buttons[] = $mark_button;
		
		// EDIT button
		$edit_button = new edit_table_button_edit();
		$edit_button->set_container_id('edit_table_div')
					->set_links_prefix($this->links_prefix);
		$this->buttons[] = $edit_button;
		
		// Add new record button
		if ($this->show_new_button) {
			$add_new_button = new edit_table_button_basic();
			$add_new_button->set_javascript('function add_new() {
											document.location.href="'.$this->links_prefix.'&new_record=1";
										}', 'add_new')
						->set_caption('Add new')
						->set_image('edit_table/images/add.png', 16,16);
			$this->buttons[] = $add_new_button;
		}
		
		// DELETE button
		if ($this->show_delete_button) {
			$delete_button = new edit_table_button_delete();
			$delete_button->set_container_id('edit_table_div')
						->set_links_prefix($this->links_prefix);
			
			$this->buttons[] = $delete_button;
		}
	}

	/**
	display the bottom images, the parameter holds array with 'edit_table_button' objects
	*/
	protected function __get_buttons_javascript() {
		if (empty($this->buttons)) return $this;
		
		$html = '<script type="text/javascript">';
		foreach ($this->buttons as $button) {
			$html.= $button->get_javascript();
		}
		$html.= '</script>';
		return $html;
	}
	
	/**
	css of the table
	*/
	protected function __get_css_styles() {
		return '<style type="text/css">
			input[type="text"], textarea {
				border:1px solid #AAA;
				padding: 1px 2px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
			}
			input[type="button"], button{
				border:1px solid #AAA;
				padding: 1px 2px;
				-moz-border-radius: 5px;
				-webkit-border-radius: 5px;
			}
			
			.paging_ul {margin: 5px auto;width:450px}
			.paging_ul li {display:block;float:left;margin-left:2px}
			.paging_ul li a {font-weight:bold;display:block;padding:5px 10px;text-align:center;border:1px solid #CCC;background:#DDD url(edit_table/images/paging_bg.gif) left top no-repeat;color:#666;text-decoration:none;}
			.paging_ul li a:hover {color:#CC141E}
			.paging_ul li a.paging_current{
				border-top:1px solid black;
				border-left:1px solid black;
				border-bottom:1px solid white;
				border-right:1px solid white;
				color:#222;background:#DDD url(edit_table/images/paging_bg_selected.gif) left top no-repeat}
			.edit_table_div{float:left;background:#F6F6F6;border:1px solid #CCC;padding:5px;font:12px arial}
			.edit_table_checkbox{float:left;width:20px;overflow:hidden;background:#DDD}
			.edit_table_clear{width:1px;height:1px;overflow:hidden;clear:both}
			.edit_table_titles_row{background:#DDD url(edit_table/images/titles_bg.gif) top left repeat-x;border-bottom:1px solid #AAA;float:left;margin:2px 2px;padding:2px 2px;text-align:left;overflow:hidden}
			.edit_table_data_col{cursor:default;float:left;margin:2px 2px;padding:2px 2px;background:#F6F6F6;overflow:hidden}
			.edit_table_data_col_checkbox{cursor:default;float:left;margin:5px 2px;padding:2px 2px;background:#F6F6F6;overflow:hidden;text-align:center}
			.edit_table_data_col_hover{background:#BBB !important}
			.edit_table_button{background:#CCC;padding:2px 5px;margin:0px}
			.edit_table_row_closer{height:1px;overflow:hidden;clear:both;border-bottom:1px solid #AAA}
			.edit_table_add_form_row{border-top:1px solid #CCC;background:#EEE;margin-bottom:5px;padding:2px}
			.edit_table_add_form_label{margin-left:2px;font:14px georgia;color:#345}
			.edit_table_add_form_field{margin-left:2px}
		</style>';
	}
	
	/** 
	build the script that runs at the bottom of the table
	*/
	protected function __get_bottom_scripts() {
		return '
		<script type="text/javascript">
			$(".edit_table_data_col").hover(function() {
				$(this).css({backgroundColor:"#BBB"});
			}, function() {
				$(this).css({backgroundColor:"#EEE"});
			});
		</script>
		';
	}
	
	/**
	display column titles
	*/
	protected function __display_table_titles_row() {
		$html = '
		<div>
			<div class="edit_table_titles_row edit_table_checkbox" style="margin:2px;padding:0px;visibility:hidden;">&nbsp;</div>';
			
		if (!empty($this->fields)) foreach($this->fields as $field_obj) {
			$html.= '<div class="edit_table_titles_row" style="width:'.$field_obj->get_width().'px">';
			$html.= '<b>'.$field_obj->get_title().'</b>';
			$html.= '</div>';
		}
		$html.= '<div class="edit_table_clear"></div>';
		$html.= '</div>';
		return $html;
	}


	/** 
	display the header/title above the table
	*/
	protected function __display_table_h1() {
		return '<div><h1 style="font-size:36px;font-weight:bold;padding:0px;margin:2px;color:#CC141E">'.$this->title.'</h1></div>';
	}
	
	protected function __get_buttons_html() {
		$html = '';
		if (!empty($this->buttons)) {
			foreach ($this->buttons as $button) {
				$html.=$button->get_html();
			}
		}
		return $html;
	}
	
	/** 
	check that the $param consist only of comma separated numbers
	*/
	protected function ids_get_is_ok($param) {
		if (empty($param)) return false;
		$ids_arr = explode(',', $param);
		foreach ($ids_arr as $id) {
			if (!is_numeric($id)) {
				return false;
			}
		}
		
		return true;
	}
	
	/**
	checks if a transaction must by executed
	- in the base version of the table - only "delete" operation is relevant
	*/
	protected function __check_transaction() {
		if (empty($_GET['etdt']) || 
			$_GET['etdt'] != $_SESSION['etdt']) {
			return false;
		}
		
		unset($_SESSION['etdt']);
		
		if (!$this->ids_get_is_ok($_GET['delete_ids'])) {
			return false;
		}
		
		database::query('delete from `'.$this->default_table.'` where `id` in ('.$_GET['delete_ids'].')');
		unset($_SESSION['etdt']);
	}
	
	
	
	// the function reads the request_uri, and changes the paging (in the url)
	protected function get_paging_link($page_num) {
		$req = $_SERVER['REQUEST_URI'];
		$req = preg_replace('/etp=([\d]+)/mi', 'etp='.$page_num, $req, 1, $res);
		if (!$res) {
			$req .= '&etp='.$page_num;
		}
		
		return $req;
	}
	
	protected function __display_paging() {
		if (!$this->paging_needed) return;
		
		$html = '<div style="text-align:center"><ul class="paging_ul">';
		if ($this->paging_current_page > 4) {
			$html.= '<li><a href="'.$this->get_paging_link(1).'">1</a></li>';
			$html.= '<li> . . . </li>';
		}
		if ($this->paging_current_page > 3) {
			$html.= '<li><a href="'.$this->get_paging_link($this->paging_current_page - 3).'">'
				.($this->paging_current_page - 3).'</a></li>';
		}
		if ($this->paging_current_page > 2) {
			$html.= '<li><a href="'.$this->get_paging_link($this->paging_current_page - 2).'">'
				.($this->paging_current_page - 2).'</a></li>';
		}
		if ($this->paging_current_page > 1) {
			$html.= '<li><a href="'.$this->get_paging_link($this->paging_current_page - 1).'">'
				.($this->paging_current_page - 1).'</a></li>';
		}
		$html.= '<li><a class="paging_current" href="javascript:;">'.($this->paging_current_page).'</a></li>';
		if ($this->paging_total_pages > $this->paging_current_page) {
			$html.= '<li><a href="'.$this->get_paging_link($this->paging_current_page + 1).'">'
				.($this->paging_current_page + 1).'</a></li>';
		}
		if ($this->paging_total_pages > $this->paging_current_page + 1) {
			$html.= '<li><a href="'.$this->get_paging_link($this->paging_current_page + 2).'">'
				.($this->paging_current_page + 2).'</a></li>';
		}
		if ($this->paging_total_pages > $this->paging_current_page + 2) {
			$html.= '<li><a href="'.$this->get_paging_link($this->paging_current_page + 3).'">'
				.($this->paging_current_page + 3).'</a></li>';
		}
		if ($this->paging_total_pages > $this->paging_current_page + 3) {
			$html.= '<li> . . . </li>';
			$html.= '<li><a href="'.$this->get_paging_link($this->paging_total_pages).'">'
				.$this->paging_total_pages.'</a></li>';
		}
		$html.= '</ul><div class="clear"></div></div>';
		return $html;
	}
	
	
	protected function __display_buttons_css_script() {
		return '$(function(){
			$("button, input[type=\'button\'], input[type=\'submit\']").css({
				background:"#D4D0C8 url(edit_table/images/button_bg.gif) left top repeat-x",
				border:"1px solid #C4C0B8",
				margin:"5px 3px 1px",
				color:"#666"
			});			
		});';
	}
}

?>