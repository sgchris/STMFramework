<?php

/*
  configuration file. includes:
  - connection to the database
  - session start
  - relevant definitions
 */

//error_reporting(E_FATAL);
// session
session_start();

// timezone
date_default_timezone_set('Asia/Jerusalem');


// database configuration
$mysql_server       = 'localhost';
$mysql_username     = 'root';
$mysql_password     = '123456';
$mysql_dbname       = 'mysite';


// database connection
@mysql_connect($mysql_server, $mysql_username, $mysql_password)
    or die(mysql_error());
@mysql_select_db($mysql_dbname)
    or die(mysql_error());
@mysql_query('set character set "utf8"')
    or die(mysql_error());
@mysql_query('set names utf8')
    or die(mysql_error());



// include base classes
require_once (dirname(__FILE__) . '/../classes/database.class.php');



// define the HTTP ROOT (base url for accessing the site)
$http_root = preg_replace('%/admin$%smi', '', str_replace(
        str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '',
        str_replace('\\', '/', _DIR_ROOT)));
define('_HTTP_ROOT', $http_root);

