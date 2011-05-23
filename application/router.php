<?php
/**
 * Template created by: Gregory Chris
 */

// route according to the URL
$url = preg_replace('%^'.preg_quote(_HTTP_ROOT, '%').'%i', '', $_SERVER['REQUEST_URI']);
$url = trim($url, '/');
$url_splitted = explode('/', $url);
$controller_name = array_shift($url_splitted);

// parameter for the controller
$GLOBALS['url_params'] = array_map('urldecode', $url_splitted);


// check if this is homepage
if (empty($controller_name)) {
    $controller_name = 'index';
}


// include the controller
$controller = _DIR_ROOT.'/application/controller/'.$controller_name.'.php';

if (file_exists($controller)) {
    require_once $controller;
} else {
    header('404 Not Found');
    header('Location: '._HTTP_ROOT.'/404');
    die();
}

