<?php
/**
the file contains a class, an implementation of iedit_table_button
interface. this is the basic class for a button. you may inherit
the class and modify some of functions.
*/

require_once (dirname(__FILE__) . '/iedit_table_button.class.php');

/**
basic button
*/
class edit_table_button_basic implements iedit_table_button {
	protected $js_code 					= '';
	protected $default_function_name 	= '';
	protected $default_function_name_params	= '';
	/**
	define the table container (id of the DOM object which wrapps the table)
	*/
	protected $container_id	= '';
	
	protected $css_styles 	= '';
	protected $css_class 	= '';
	protected $caption 		= '';
	protected $html 		= '';
	protected $image		= '';	
	protected $image_width	= '';	
	protected $image_height	= '';
	protected $images_url	= 'edit_table/images';
	protected $links_prefix = '?';	// prefix for all links in the class
									// (may be: foo=bar&x=y)
	
	
	
	/**
	set the location of the images
	*/
	public function set_images_url($images_url) {
		if ($images_url[strlen($images_url) - 1] == '/') {
			$images_url = substr($images_url, 0, strlen($images_url) - 1);
		}
		$this->images_url = $images_url;
	}
									
	/**
	get and set the links prefix
	*/
	public function set_links_prefix($lpref) {
		// prepend '?' sign to the prefix
		if (!empty($lpref) && $lpref[0] != '?') {
			$lpref = '?'.$lpref;
		}
		
		$this->links_prefix = $lpref . '&';
		return $this;
	}
	public function get_links_prefix() {
		return $this->links_prefix;
	}
	/**
	return the javascript code (without <script> tags)
	*/
	public function get_javascript() {
		return $this->js_code;
	}
	/**
	get/set the CSS classes (without <style> tags
	*/
	public function get_css() {
		return $this->css_styles;
	}
	public function set_css($p_css) {
		$this->css_styles = $p_css;
		return $this;
	}
	/** 
	the id of the HTML container (the wrapper for the checkboxes)
	*/
	public function set_container_id($container_id) {
		if (empty($container_id)) {
			throw new Exception('container id is null!');
		}
		
		$this->container_id = $container_id;
		return $this;
	}
	
	/**
	get the html of the table
	*/
	public function get_html() {
		$html = '<button type="button"';
		// CLASS
		if (!empty($this->css_class)) {
			$html .= ' class="'.$this->css_class.'"';
		}
		
		// ACTION
		if (!empty($this->default_function_name)) {
			$html .= ' onclick="'.$this->default_function_name.'('.$this->default_function_name_params.')"';
		}
		$html .= '  style="padding:2px 5px;margin:0px">';
		
		// IMAGE
		if (!empty($this->image)) {
			$html .= '<img src="'.$this->image.'" width="'.$this->image_width.'"'
				.' height="'.$this->image_height.'" border="0" align="absmiddle"/>&nbsp;';
		}
		
		// CAPTION
		if (!empty($this->caption)) {
			$html .= '<b id="caption_'.str_replace(' ', '_', $this->caption).'">'.$this->caption.'</b>';
		}
		
		$html .= '</button>';
		return $html;
	}
	/**
	set the text inside the button
	*/
	public function set_caption($p_caption){
		$this->caption = $p_caption;
		return $this;
	}
	
	/********
	Additional functions for the basic button class
	*********/
	
	/**
	the basic class doesn't have a default value, so a javascript function
	must be supplied
	$js_code - the whole Javascript code
	$default_function_name - the name of the function, which the button
	should call on click.
	$default_function_name_params - the parameters that have to be passed
	to the default JS function (like "this", or "100", or sth else);
	*/
	public function set_javascript($js_code, $default_function_name, $default_function_name_params = '') {
		$this->js_code = $js_code;
		$this->default_function_name = $default_function_name;
		$this->default_function_name_params = $default_function_name_params;
		return $this;
	}
	
	/**
	set the image that will appear on the button
	*/
	public function set_image($image_path, $image_width, $image_height) {
		$this->image = $image_path;
		$this->image_width = $image_width;
		$this->image_height= $image_height;
		return $this;
	}
	
	/**
	the function returns JS code which returnes CSV list of marked rows
	*/
	protected function get_marked_rows_csv_js_func() {
		$html = '
			if (typeof("get_marked_rows") == "undefined") {
				function get_marked_rows() {
					var ids = "";
					for (var i=0; i<$(".edit_table_div input[row_id]:checked").length; i++) {
						ids += (ids!=""?",":"");
						ids += $(".edit_table_div input[row_id]:checked").eq(i).attr("row_id");
					}
					return ids;
				}
			}
		';
		return $html;
	}
}