<?php
$f = fopen('./test.txt', 'w');
fputs($f, 'ok');
//fputs($f, print_r(Zend_Session::getId(), true));

$base = dirname(dirname(__FILE__));
set_include_path(implode(PATH_SEPARATOR, array(
    dirname(dirname(__FILE__)),
    get_include_path(),
)));
fputs($f, 'ok');
/** Zend_Application */
require_once 'Zend/Application.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Session.php';
require_once 'Zend/Session/SaveHandler/DbTable.php';
fputs($f, 'ok');

// Create application
$application = new Zend_Application(
    'production',
    dirname($base) . '/app/configs/app.ini'
);
die('io');
fputs($f, 'ok');
// Run
$db = $application->bootstrap('db');
Zend_Registry::set('db', $db);
fputs($f, 'ok');
$configSession = new Zend_Config_Ini(dirname($base) . '/app/configs/session.ini', 'production');
        fputs($f, 'ok');
$config = array(
    'name'           => 'session',
    'primary'        => 'id',
    'modifiedColumn' => 'modified',
    'dataColumn'     => 'data',
    'lifetimeColumn' => 'lifetime',
    'lifetime'       => $configSession->gc_maxlifetime
);

Zend_Session::setSaveHandler(new Zend_Session_SaveHandler_DbTable($config));
fputs($f, 'ok');
//
$options = $configSession->toArray();
$types = array();
if(isset($options['types'])) {
    $types = $options['types'];
    unset($options['types']);
}
fputs($f, 'ok');
Zend_Session::setId($_REQUEST['session_id']);
Zend_Session::start($options);
fputs($f, 'ok');
//$session = new Front_Model_Session('front');
//
//if(!$session->isInitialized) {
//    Zend_Session::regenerateId();
//    $session->isInitialized = true;
//}


$f = fopen('./test.txt', 'w');
fputs($f, print_r($_REQUEST, true));
fputs($f, print_r($_FILES, true));
fputs($f, print_r(headers_list(), true));
fputs($f, print_r(session_id(), true));
//fputs($f, print_r(Zend_Session::getId(), true));
fclose($f);

/*
Uploadify v2.1.4
Release Date: November 8, 2010

Copyright (c) 2010 Ronnie Garcia, Travis Nickels

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/
header('HTTP/1.0 200 OK');
die('ok');
if (!empty($_FILES)) {
    $tempFile = $_FILES['Filedata']['tmp_name'];
    $targetPath = $_SERVER['DOCUMENT_ROOT'] . $_REQUEST['folder'] . '/';
    $targetFile =  str_replace('//','/',$targetPath) . $_FILES['Filedata']['name'];
    move_uploaded_file($tempFile,$targetFile);
    chmod($targetFile, 0777);
    echo str_replace($_SERVER['DOCUMENT_ROOT'],'',$targetFile);
}
?>
