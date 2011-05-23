<?php
/**
 * Template created by: Gregory Chris
 */
session_start();

date_default_timezone_set('Asia/Jerusalem');

// basic function of the application (the core)
require_once _DIR_ROOT.'/application/helpers/glob_functions.php';

// init database
define ('_DB_HOSTNAME', 'localhost');
define ('_DB_USERNAME', 'root');
define ('_DB_PASSWORD', '123456');
define ('_DB_DBNAME', 'mysite');

try {
    // create database connection
    $GLOBALS['db'] = new PDO('mysql:hostname='._DB_HOSTNAME.';dbname='._DB_DBNAME.';charset=utf-8', _DB_USERNAME, _DB_PASSWORD);

    // basic database initializations
    $GLOBALS['db']->exec('set names "utf8"');
    $GLOBALS['db']->exec('set character set utf-8');
    $GLOBALS['db']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
} catch (PDOException $e) {
    _die($e->getMessage());
}
