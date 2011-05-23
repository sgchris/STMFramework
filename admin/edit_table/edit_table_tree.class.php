<?php

require_once(dirname(__FILE__) . '/database.class.php');

class edit_table_tree {
	
	// define the fields for the tree
	private $table_name;
	private $parent_id_field = 'parent_id';
	private $name_field = 'name';
	
	// recordset
	private $rs = array();
	private $pages_rs = array();
	
	/**
	 * 
	 * Constructor
	 * @param String $table_name - the name of the table
	 */
	public function __construct($table_name) {
		$this->table_name = $table_name;
	}
	
	/**
	 * 
	 * Display the tree
	 */
	public function show() {
		$this->__receive_ajax_calls();
		
		// load the data
		$this->__get_rs();
		
		// show the tree
		$this->__display_the_tree();
		
		// show the js bottom script;
		$this->__show_js();
	}
	
	
	
	
	/**********************************************************************/
	/**
	 * 
	 * Enter description here ...
	 * @param String/Integer $id - the top level id (or empty if the parent_id for root is '')
	 */
	private function __build_rs($id = '') {
		$arr = array();
		foreach ($this->rs as $row) {
			if ($row['parent_id'] == $id) {
				$arr[] = array(
					'id'			=>$row['id'], 
					'name'			=>$row['name'],
					'page_id'		=>$row['page_id'],
					'page_name'		=>$row['page_name'],
					'english_name'	=>$row['page_url'],
					'external_url'	=>$row['external_url'],
					'children'		=>$this->__build_rs($row['id'], $arr)
				);
			}
		}
		
		return $arr;
	}
	
	/**
	 * 
	 * Load the rs from the database to the array
	 */
	private function __get_rs() {
		if (empty($this->rs)) {
			database::query('select m.*, p.`url` page_url, p.`id` page_id, p.`name` page_name from `menu` m
								left join `pages` p on (p.`menu_id` = m.id AND p.`is_active` = 1)
							order by m.`display_order`');
			$this->rs = database::$arr_list;
			$arr = $this->__build_rs(0);
			$this->rs = $arr;
		}
		
		if (empty($this->pages_rs)) {
			database::query('select * from `pages` where `is_active` = 1');
			$this->pages_rs = database::$arr_list;
		}
	}
	
	
	private function __display_the_tree($arr = null, $level = 0) {
		
		if ($arr === null) $arr = $this->rs;
		if (empty($arr)) {
			echo 'Empty RS<br>';
			return;
		}
		
		foreach($arr as $item) {
			echo
				'<div id="menu_item-',$item['id'],'" class="menu_item" style="margin:5px 0 0 ',($level*50),'px;">',
					
					// english name / URL
					'<div class="english_name">English name / URL : <span>',$item['english_name'],'</span></div>',
					
					// information
					'<b>',$item['name'],'</b> | page: <i>',
					(!empty($item['page_name']) ? $item['page_name'] : ' -- '), '</i>',
					
					// opening div
					'<div class="details">',
						'<div style="width:1px;height:10px"></div>',
						'<em>Name: </em> <input id="menu_name-',$item['id'],'" type="text" value="',$item['name'],'" />',
							'<input class="s_menu_name" id="s_menu_name-',$item['id'],'" type="button" value="Save" />',
							'<div class="clear"></div>',
						'<em>URL name: </em> <input id="url_name-',$item['id'],'" type="text" value="',$item['english_name'],'" />',
							'<input class="s_url_name" id="s_url_name-',$item['id'],'" type="button" value="Save" />',
							'<div class="clear"></div>',
						'<em>External URL: </em> <input id="external_url-',$item['id'],'" type="text" value="',htmlentities($item['external_url']),'" />',
							'<input class="s_external_url" id="s_external_url-',$item['id'],'" type="button" value="Save" />',
							'<div class="clear"></div>',
						'<em>Page: </em> <span class="select_wrapper" id="select_wrapper-',$item['id'],'">',
							'<select class="select_page" id="select-',$item['id'],'">',
								'<option>qwerty</option>',
								'<option>gfdsa</option>',
								'<option>hgfewq</option>',
							'</select></span>',
							//?page=edit_one_page&page_id=5
							'<input class="s_select_wrapper" id="s_select-',$item['id'],'" type="button" value="Save" />',
							'<input class="b_edit_page" id="b_edit_page-',$item['id'],'" type="button" value="Edit page" />',
							'<div class="clear"></div>',
						'<em>Add New Child Menu: </em> <input id="new_menu_name-',$item['id'],'" type="text" value="" />',
							'<input class="s_new_menu_name" id="s_new_menu_name-',$item['id'],'" type="button" value="Add subMenu" />',
							'<div class="clear"></div>',
						'<em>Delete this menu: </em> ',
							'<input class="s_delete" id="s_delete-',$item['id'],'" type="button" value="Delete" />',
							'<div class="clear"></div>',
					'</div>',
					
				'</div>';
			if (!empty($item['children'])) {
				$this->__display_the_tree($item['children'], $level + 1);
			}
		}
	}
	

	private function __show_js() {
		$js = <<<JS
		<script>
		
		function buildSelectboxFromJSON(sel_obj, json_obj) {
			sel_obj.options.length = 0;
			for (var i in json_obj) {
				var selected = (json_obj[i]["menu_id"] != 0);
				var newOption = new Option(json_obj[i]["name"], json_obj[i]["id"], selected);
				sel_obj.options.add(newOption);
			}
		}
		
		
		$(function() {
			var current_url = document.location.href;
			
			$(".menu_item")
				.bind("mouseenter", function() {
					$(this).addClass('menu_item_hover');
				})
				.bind("mouseleave", function() {
					$(this).removeClass('menu_item_hover');
				})
				.click(function() {					
					if ($(this).find(".details").css("display") == "none") {
						// raise all the rest
						$(".details").slideUp();
						
						// update the current select box
						var details_div_splitted_id = $(this).attr("id").split("-");
						var menu_item_id = details_div_splitted_id[1];
						var selBoxObj = document.getElementById("select-"+menu_item_id);
						if (!(menu_item_id > 0)) {
							alert("Error in details div ID");
							return;
						}
							
						// fade the div until loading finished.
						var zanaveska = $("<div id='zanaveska'></div>");
						zanaveska.css({
							background:"#000",
							opacity: "0.4",
							width:"100%",
							height:"100%",
							position: "absolute",
							left:"0",
							top:"0"});
						$(this).css("position","relative").prepend(zanaveska);
						
						$(this).find(".details").slideDown("fast", function() {
							// update the current selectbox
							
							$.ajax({
								type:"post",
								url: current_url,
								data:{act:"get_selectbox", menu_id:menu_item_id},
								success:function(res) {
									buildSelectboxFromJSON(selBoxObj, res);
									
									// remove the faded div
									$("#zanaveska").fadeOut("fast", function() { $(this).remove(); });
								},
								async:true,
								dataType:"json"
							});
						});
					}
				});
			
			// bind the "save" buttons
			$("input.s_select_wrapper").click(function() {
				var value = $("select#"+$(this).attr("id").replace(/^s_/g, '')).val();
				var menu_id = $(this).attr("id").split("-").pop();
				$.post(current_url,
					{act:"change_page", menu_id:menu_id, page_id: value},
					function(res) {
						$("#menu_item-"+menu_id+">i").html(res);
						$(".details").slideUp();
					});
			});
			
			// edit page button
			$(".b_edit_page").click(function() {
				document.location.href = "index.php?page=edit_one_page&page_id=" + $(this).parents(".menu_item").find(".select_page").val();
			});
			
			// Save the menu name
			$("input.s_menu_name").click(function() {
				var value = $("input#"+$(this).attr("id").replace(/^s_/g, '')).val();
				var menu_id = $(this).attr("id").split("-").pop();
				$.post(current_url,
					{act:"menu_name", menu_id:menu_id, value:value},
					function(res) {
						if (res.length > 1) {
							$("#menu_item-"+menu_id+">b").html(res);
						}
						$(".details").slideUp();
					});
			})
			
			// save the URL name
			$("input.s_url_name").click(function() {
				var value = $("input#"+$(this).attr("id").replace(/^s_/g, '')).val();
				var menu_id = $(this).attr("id").split("-").pop();
				$.post(current_url,
					{act:"url_name", menu_id:menu_id, value:value},
					function(res) {
						if (res.length > 1) {
							$("#menu_item-"+menu_id+" .english_name span").html(res);
						}
						$(".details").slideUp();
					});
			})
			
			// save the URL name
			$("input.s_external_url").click(function() {
				var value = $("input#"+$(this).attr("id").replace(/^s_/g, '')).val();
				var menu_id = $(this).attr("id").split("-").pop();
				$.post(current_url,
					{act:"external_url", menu_id:menu_id, value:value},
					function(res) {
						if (res.length > 1) {
							//
						}
						$(".details").slideUp();
					});
			})
			
			// new child
			$("input.s_new_menu_name").click(function() {
				var value = $("input#"+$(this).attr("id").replace(/^s_/g, '')).val();
				var menu_id = $(this).attr("id").split("-").pop();
				$.post(current_url,
					{act:"new_menu_name", menu_id:menu_id, value:value},
					function(res) {
						
						
						// reload the main_frame_div
						$.ajax({
							type:"post",
							url: current_url,
							data: {get_only_tree:"1"},
							success:function(res) {
								$("div.main_frame_div").html(res);
							},
							async:false
						});
					});
			});
			
			// delete the menu_item
			$(".s_delete").click(function() {
				if (!confirm("Are you sure?")) return;
				var menu_id = $(this).attr("id").split("-").pop();
				$.post(current_url,
					{act:"delete", menu_id:menu_id},
					function(res) {
						$("#menu_item-"+menu_id).css("background", "red");
						$("#menu_item-"+menu_id).fadeOut("slow", function() {
							$(this).remove();
						});
					});
			});
			
		});
		</script>
JS;
		echo $js;
	}



	/*** A J A X     C A L L S ************************************/
	private function __receive_ajax_calls() {
		
		// save the name of the menu item
		if ($_POST['act'] == 'menu_name' &&
			!empty($_POST['menu_id']) && is_numeric($_POST['menu_id']) && $_POST['menu_id'] > 0 &&
			!empty($_POST['value'])) {
			
			// check that the name unique
			database::query('select * from `menu`
				where `name` = "'.mysql_real_escape_string($_POST['value']).'"');
			if (count(database::$arr_list) > 0) {
				ob_clean();
				die();
			}

			database::update('menu', array('name'=>$_POST['value']), array('id'=>$_POST['menu_id']));
			ob_clean();
			die($_POST['value']);
		}
		
		// save the url of the menu item (what will be the link of the page)
		if ($_POST['act'] == 'url_name' &&
			!empty($_POST['menu_id']) && is_numeric($_POST['menu_id']) && $_POST['menu_id'] > 0 &&
			!empty($_POST['value'])) {
				
			// check that the name unique and there is a page connected to the menu
			database::query('select `id` from `pages`
				where `url` = "'.mysql_real_escape_string($_POST['value']).'"
				OR `menu_id` = '.$_POST['menu_id']);
			if (count(database::$arr_list) != 1) {
				ob_clean();
				die();
			} 
			
			$page_id = database::$arr_list[0]['id'];
			
			database::update('pages', array('url'=>$_POST['value']), array('id'=>$page_id));
			ob_clean();
			die($_POST['value']);
		}
		
		// save the url of the menu item (what will be the link of the page)
		if ($_POST['act'] == 'external_url' &&
			!empty($_POST['menu_id']) && is_numeric($_POST['menu_id']) && $_POST['menu_id'] > 0) {
				
			// check that the name unique and there is a page connected to the menu
			database::update('menu', array('external_url'=>$_POST['value']), array('id'=>$_POST['menu_id']));
			ob_clean();
			die($_POST['value']);
		}
		
		if ($_POST['act'] == 'new_menu_name' &&
			!empty($_POST['menu_id']) && is_numeric($_POST['menu_id']) && $_POST['menu_id'] > 0 &&
			!empty($_POST['value'])) {

			// check that the name unique
			database::query('select * from `menu`
				where `name` = "'.mysql_real_escape_string($_POST['value']).'"');
			if (count(database::$arr_list) > 0) {
				ob_clean();
				die();
			}
			
			// get the order of the last element under this id
			database::query('select max(`display_order`) max_value
				from `menu`
				where `parent_id` = '.$_POST['menu_id']);
			$new_order = database::$arr_list[0]['max_value'] + 10;
			
			database::insert('menu', array(
				'name'=>$_POST['value'],
				'parent_id'=>$_POST['menu_id'],
				'display_order'=>$new_order,
				'english_name'=>$_POST['value']));
			
			ob_clean();
			die($_POST['value']);
		}
		
		// change the page connected to the menu
		if ($_POST['act'] == 'change_page' && is_numeric($_POST['menu_id']) && $_POST['menu_id'] > 0
			 && is_numeric($_POST['page_id']) && $_POST['page_id'] >= 0) {
			// get the name of the new page
			$name = '--';
			if ($_POST['page_id'] > 0) {
				database::query('select `name` from `pages` where `id` = '.$_POST['page_id']);
				$name = database::$arr_list[0]['name'];
			}

			// update the database with the new page id
			if ($_POST['page_id'] > 0) {
				
				// remove the menu_id from the previous page
				database::update('pages', array('menu_id' => 0),
								array('menu_id'=>$_POST['menu_id']));

				// set the new page
				database::update('pages', array('menu_id' => $_POST['menu_id']),
								array('id'=>$_POST['page_id']));
			} else {
				database::update('pages', array('menu_id' => '0'),
								array('menu_id'=>$_POST['menu_id']));
			}
			
			ob_clean();
			die($name);
		}

		// return the select box of the menu (available pages for the menu)
		if ($_POST['act'] == "get_selectbox" && is_numeric($_POST['menu_id']) && $_POST['menu_id']>0) {
			database::query('
				select `id`, `name`, `menu_id`
				from `pages`
				where `menu_id` = 0 OR `menu_id` = '.$_POST['menu_id']);
			ob_clean();
			
			// add an empty record to the start
			array_unshift(database::$arr_list, array('id'=>0, 'name'=>'', 'menu_id'=>''));
			
			// return JSONed array
			die(json_encode((Array)database::$arr_list));
		}
		
		// delete the menu item
		if ($_POST['act'] == "delete" && is_numeric($_POST['menu_id']) && $_POST['menu_id']>0) {
			database::query('
				delete from `menu` where id = '.$_POST['menu_id']);
			ob_clean();
			die();
		}
	}
	
}
