<?php

require_once(dirname(__FILE__).'/edit_table_button_basic.class.php');

class edit_table_button_mark extends edit_table_button_basic {

	public function __construct() {
		// set default parameters for the button
		$this->set_caption('Mark all');
	}
		
	/**
	Create JS code for the "delete" button
	*/
	public function get_javascript() {
			
		/**
		write JS code to get the marked rows IDs (only if weren't defined before)
		*/
		$js_code = '
			var operation_is_mark = true;
			function mark_all() {
				$("#'.$this->container_id.' input[name=select_row]").attr("checked", "checked");
			};
			function unmark_all() {
				$("#'.$this->container_id.' input[name=select_row]").attr("checked", "");
			};
			function mark() {
				if (operation_is_mark) {
					mark_all();
					document.getElementById("caption_'.str_replace(' ', '_', $this->caption).'").innerHTML = "unMark all";
					operation_is_mark = false;
				} else {
					unmark_all();
					document.getElementById("caption_'.str_replace(' ', '_', $this->caption).'").innerHTML = "Mark all";
					operation_is_mark = true;
				}
			};
		';
		
		// set the javascript code to the parent JS var + the default function.
		$this->set_javascript($js_code, 'mark');
		return $js_code;
	}
	
	
	/**
	overload parent's function
	*/
	public function get_html() {
		
		// set the image for "edit" operation
		$this->image 		= 'edit_table/images/mark.png';
		$this->img_height 	= 16;
		$this->img_width 	= 16;
		
		// return the html code for the button
		return parent::get_html();
	}
}

?>