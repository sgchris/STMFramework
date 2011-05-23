<?php

require_once(dirname(__FILE__).'/edit_table_button_basic.class.php');

class edit_table_button_edit extends edit_table_button_basic {
	public function __construct() {
		// set default parameters for the button
		$this->set_caption('Edit');
	}
	
	/**
	Create JS code for the "delete" button
	*/
	public function get_javascript() {
			
		/**
		write JS code to get the marked rows IDs (only if weren't defined before)
		*/
		
		$js_code = '
			function edit_selected() {
				var inputs_arr = $("#'.$this->container_id.' input[@row_id][type=checkbox]:checked");
				var ids_arr = new Array();
				for(var i=0; i<inputs_arr.length; i++) {	
					ids_arr.push($(inputs_arr[i]).attr("row_id"));
				}
				var ids = ids_arr.join(",");
				if (ids != "") {
					document.location.href="'
						.$_SERVER['PHP_SELF']
						.$this->links_prefix
						.'edit_data=1&edit_ids="+ids+"&etet='.
						($_SESSION['edit_table_edit_token'] = md5(rand(0,10000)))
						.'";
				} else {
					alert("Please mark rows to edit!");
				};
			};
		';
		
		// set the javascript code to the parent JS var.
		$this->set_javascript($js_code, 'edit_selected');
		return $js_code;
	}
	
	
	/**
	overload parent's function
	*/
	public function get_html() {
		
		// set the image for "edit" operation
		$this->image 		= 'edit_table/images/edit.png';
		$this->img_height 	= 16;
		$this->img_width 	= 16;
		
		// return the html code for the button
		return parent::get_html();
	}
}

?>