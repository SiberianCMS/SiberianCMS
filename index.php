<?php

set_time_limit(120);
ini_set('max_execution_time', 120);
umask(0);

setlocale(LC_MONETARY, 'en_US');

//defined('BASE_PATH')
//    || define('BASE_PATH', realpath(dirname(__FILE__)));

defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/app'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure lib/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    get_include_path(),
    realpath(APPLICATION_PATH . '/modules'),
    realpath(APPLICATION_PATH . '/../lib'),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application
$ini = file_exists(APPLICATION_PATH . '/configs/app.ini') ? APPLICATION_PATH . '/configs/app.ini' : APPLICATION_PATH . '/configs/app.sample.ini';
$application = new Zend_Application(
    APPLICATION_ENV,
    $ini
);

// Run
$application->bootstrap()->run();
