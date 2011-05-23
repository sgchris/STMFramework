<?php
################ IMPLEMENTATION ##################################

require_once(dirname(__FILE__) . '/edit_table_field.class.php');
require_once(dirname(__FILE__) . '/../classes/image.resizer.php');

// define default field type - simple text
class edit_table_field_file extends edit_table_field {

    // define the max size of a file (in BYTES) (def. is 500K)
    protected $max_file_size = 500000;
    
    // define the preview image width (def. 40px)
    protected $preview_width = 40;
    
    protected $keep_original_name = false;
    public function keep_original_name() { $this->keep_original_name = true; return $this; }

    // define the path (dir) of the file (to be stored)
    // these are the default paths.
    protected $file_path = '';
	public function set_file_path($fPath) { $this->file_path = $fPath; return $this; }
    
    /**
     * define the URL of the file
     * how to display the image (e.g."uploaded/files" will be translated into
     * "http://server_name/uploaded/files/myFile.jpg")
     */
    protected $file_url = 'uploaded_images';
	public function set_file_url($fUrl) {
		$this->file_url = $fUrl;
		
        return $this;
    }
    
	// change the file dimentions, it this is an image
	protected $image_x = 0;
	protected $image_y = 0;
	/**
	 * The function resized the image.. if X or Y is = 0 (zero) the image
	 * is not resized to the new dimention
	 * 
	 * @param $x - X dimention
	 * @param $y - Y dimention
	 */
	public function resize($x, $y) {
		if ($x > 0) $this->image_x = $x;
		if ($y > 0) $this->image_y = $y;
		
		return $this;
	}

    // the function tells the caller that it should pass $_FILES var instead
    // of $_POST or $_GET
    public function is_file() { return true; }
    
	// default constructor
	public function __construct($field_title = '', $field_database_name = '', $field_width = 100) {
		parent::__construct($field_title, $field_database_name, $field_width);
        
        $this->file_path = dirname(__FILE__).'/../../uploaded_images';
        $this->file_url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$this->file_url;
	}

	// get the display function (what's drawn in the table
	public function get_display_mode($row_recordset) {
		if ($this->is_editonly()) return '&nbsp;';
		
		if (!is_array($row_recordset) || empty($row_recordset)) 
			return '&nbsp;';
			
		$field_value = trim($row_recordset[$this->field_database_name]);
        
		if (empty($field_value)) {
			return '&nbsp;';
		}
		
        $link = ($this->file_is_image($field_value)) ?
            '<img src="'.$field_value.'" width='.$this->preview_width.' border=0/>' : '<i>Link</i>';
		return '<a href="'.$field_value.'" target="_blank">'.$link.'</a>';
	}

	// get the edition function (what's drawn when you want to edit the field)
	public function get_edit_mode($row_recordset, $html_input_id) {	
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
		
		
		// get field value
		$field_value = $row_recordset[$this->field_database_name];
		$field_value = html_escape($field_value);
		
        // check if the file is image
        $preview = '';
        if (in_array($this->get_file_extension($field_value), array('png', 'gif', 'jpg'))) {
            $preview = '<div style="margin:3px;text-align:center;padding:10px;border:1px solid #DDD">
                <img src="'.$field_value.'" width='.($this->field_width - 50).'/>
            </div>';
        }
        
		// return the output
		$html_input_id = html_escape($html_input_id);;
		$html = $preview.(!empty($field_value) ? '
		<div style="height:20px; line-height:20px; font-size:12px;">
			<input type="checkbox" name="del_'.$html_input_id.'" 
				value="del_'.$html_input_id.'" id="del_'.$html_input_id.'" /> 
			<label for="del_'.$html_input_id.'">Delete?</label>
		</div>' : '').
		'<input type="file" name="'.$html_input_id.'" '
            .'style="width:'.$this->field_width.'px" size="'.floor($this->field_width / 12).'"'
            .'value="'.$field_value.'" />';
		return $html;
	}
	
    /**
     * In this case the parameter $post_val
     * is the $_FILE['file_input_name'] variable
     */
	public function get_value_from_post($post_val) {
		if (!empty($this->default_value)) {
			return $this->get_default_value();
		}
		
        // check the directory, if it exists
        if (!$this->create_file_path()) return 'error creating directory';
        
        // upload the file ***set the enctype to multipart/form-data***
        if (is_uploaded_file($post_val['tmp_name'])) {
            $uploaddir = $this->file_path;
            $filename = $post_val['name'];
            if ($this->keep_original_name) {
                $uploadfile = $uploaddir.'/'.basename($filename);
            } else {
                $filename = md5(rand(1,1000)).'.'.$this->get_file_extension(basename($filename));
                $uploadfile = $uploaddir.'/'.$filename;
            }
            if (move_uploaded_file($post_val['tmp_name'], $uploadfile)) {
				
				// check if resize needed
				if ($this->image_x > 0 && $this->image_y > 0) {
					$ir =& new RESIZEIMAGE($uploadfile);
					$ir->resize($this->image_x, $this->image_y, $uploadfile);
				} elseif ($this->image_x > 0) {
					$ir =& new RESIZEIMAGE($uploadfile);
					$ir->resize_limitwh($this->image_x, 9999, $uploadfile);
				} elseif ($this->image_y > 0) {
					$ir =& new RESIZEIMAGE($uploadfile);
					$ir->resize_limitwh(9999, $this->image_y, $uploadfile);
				}
				
                return $this->file_url.'/'.$filename;
            }
        }
        
        // return error if reached here
		return 'error2';
	}
    
	
	// The function receives the field as it appears in the database,
	// i.e. the "URL" to the file
	public function delete_file($file_url) {
		if (empty($file_url)) {
			return;
		}
		
		// extract the name of the file from the parameter
		// and build the full filepath
		$fname = array_pop(explode('/', $file_url));
		$file_path = preg_replace('%/$%smi', '', $this->file_path);
		
		
		// delete the file physically
		if (file_exists($file_path . '/' . $fname) && !@unlink($file_path . '/' . $fname)) {
			echo '<br>ERROR DELETING (in func. '.__FUNCTION__.' (file:'.__FILE__.') ) :'.$file_path . '/' . $fname.'<br>';
			return false;
		}
		
		return true;
	}
	
	
	
	
	/////////////////////////////////////////////////////////////////////////////////

    protected function get_file_extension($filename) {
        $expl = explode('.', $filename);
        $ext = $expl[count($expl) - 1];
        if ($ext == $filename) {
            return '';
        } else {
            return $ext;
        }
    }
    
    /**
     * the function checks if the file is image
     * ext: gif, jpg, png
     */
    protected function file_is_image($file_path) {
        $ext = $this->get_file_extension($file_path);
        return in_array(strtolower($ext), array('gif', 'jpg', 'png'));
    }
    
    
    /**
     * The function checks that the path for uploading exists
     */
    protected function create_file_path($path = null) {
        if ($path === null) {
            $path = $this->file_path;
        }
        
        if (empty($path)) {
			$path = getcwd();
			return true;
		}
        
        // set the default dir_seprtr.
        $path = str_replace('\'', '/', $path);
        
        if (!is_dir($path)) {
            // create parent dir
            $parent_a = explode('/', $path);
            unset($parent_a[count($parent_a) - 1]);
            $parent = implode('/', $parent_a);
            if ($this->create_file_path($parent)) {
                // create current dir
                if (@mkdir($path)) {
                    return true;
                }
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
	
}

?>