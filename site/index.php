<?php

require_once __DIR__.'/SystemServices/ClassLoader.php';

session_start();

$json_data = null;

// controller has not been initialized
if (!isset($_SESSION['controller'])) {
    $_SESSION['controller'] = new GeneralController();
} else {
    
    if (isset($_POST['data'])) {
        
        $json_data = json_decode($_POST['data'], true);
        unset($_POST['data']);
    }
}

$_SESSION['controller']->invoke($json_data); // invoke controller
