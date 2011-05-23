<?php
/**
 * Template created by: Gregory Chris
 */

// define the directory root for the application
define ('_DIR_ROOT', dirname(__FILE__));


// define the http root for the application (how the app is being accessed thru a browser)
$http_root = preg_replace('%/admin$%smi', '', str_replace(
        str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '',
        str_replace('\\', '/', _DIR_ROOT)));
define('_HTTP_ROOT', $http_root);

// load configutation
require_once _DIR_ROOT.'/application/config/config.php';


// load the router
require_once _DIR_ROOT.'/application/router.php';
