<?php

require_once(dirname(__FILE__).'/edit_table_button_basic.class.php');

class edit_table_button_save extends edit_table_button_basic {

	protected $form_name = '';


	public function __construct() {
		// set default parameters for the button
		$this->set_caption('Save');
	}

	/** 
	set the form name to submit
	*/
	public function set_form_name($form_name_param) {
		if (empty($form_name_param)) {
			return false;
		}
		
		$this->form_name = $form_name_param;
		return $this;
	}
	
	/**
	Create JS code for the "delete" button
	*/
	public function get_javascript() {
			
		/**
		write JS code to get the marked rows IDs (only if weren't defined before)
		*/
		$js_code = 'function save() {';	
		
		if (empty($this->form_name)) {
			$js_code.= 'alert("Error in form name");';
		} else {
			$js_code .= 'if (confirm("Are you sure?")) {'
							.'document.'.$this->form_name.'.submit();'
						.'};';
		}
		$js_code.='}';
		
		// set the javascript code to the parent JS var.
		$this->set_javascript($js_code, 'save');
		return $js_code;
	}
	
	
	/**
	overload parent's function
	*/
	public function get_html() {
		
		// set the image for "edit" operation
		$this->image 		= 'edit_table/images/save.png';
		$this->img_height 	= 16;
		$this->img_width 	= 16;
		
		// return the html code for the button
		return parent::get_html();
	}
}

?>