<?php

require_once __DIR__.'/SystemServices/ClassLoader.php';

if (!defined('LOCALHOST')) {
    define('LOCALHOST', $_SERVER['REMOTE_ADDR'] == '::1');
}

session_start();

$json_data = null;

// controller has not been initialized
if (!isset($_SESSION['controller'])) {
    $_SESSION['controller'] = new GeneralController();
    $_SESSION['session_start'] = date(DATE_ATOM);
} else {
    
    if (isset($_POST['data'])) {
        
        $json_data = json_decode($_POST['data'], true);
        unset($_POST['data']);
    }
}

$_SESSION['controller']->invoke($json_data); // invoke controller
