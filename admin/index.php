<?php

define(_DIR_ROOT, dirname(__FILE__));

require_once (dirname(__FILE__).'/config/config.php');
require_once (dirname(__FILE__).'/funcs/functions.php');
require_once (dirname(__FILE__).'/edit_table/edit_table.class.php');
require_once (dirname(__FILE__).'/edit_table/edit_table_row.class.php');
require_once (dirname(__FILE__).'/edit_table/edit_table_tree.class.php');

// check authentication
require_once (dirname(__FILE__).'/config/left_menu.php');
require_once (dirname(__FILE__).'/funcs/check_login.php');
require_once (dirname(__FILE__).'/html/body.php');