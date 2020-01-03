<?php

/*
 * Loads files/classes when the system reffers to unloaded class\file
 */

spl_autoload_register('classLoader');

// <editor-fold defaultstate="collapsed" desc="Class loader">

// Class loader -loads system classes upon demand
function classLoader(string $class) {
	
	$pre_path = __DIR__.'/../';

	if (file_exists($pre_path.'Controllers/'.$class.'.php')) {

		$path = $pre_path.'Controllers/';
		
	} else if (file_exists($pre_path.'Controllers/UserControllers/'.$class.'.php')) {
		
		$path = $pre_path.'Controllers/UserControllers/';

	} else if (file_exists($pre_path.'Controllers/Helpers/'.$class.'.php')) {
		
		$path = $pre_path.'Controllers/Helpers/';

	} else if (file_exists($pre_path.'Models/'.$class.'.php')) {
		
		$path = $pre_path.'Models/';

	} else if (file_exists($pre_path.'Models/UserModels/'.$class.'.php')) {
		
		$path = $pre_path.'Models/UserModels/';

	} else if (file_exists($pre_path.'Models/Objects/'.$class.'.php')) {
		
		$path = $pre_path.'Models/Objects/';

	} else if (file_exists($pre_path.'Views/'.$class.'.php')) {
		
		$path = $pre_path.'Views/';

	} else if (file_exists($pre_path.'Views/Pages/Templates/'.$class.'.php')) {
		
		$path = $pre_path.'Views/Pages/Templates/';

	} else if (file_exists($pre_path.'SystemServices/'.$class.'.php')) {
		
		$path = $pre_path.'SystemServices/';
	}

	// path is found
	if (isset($path)) {
		require_once $path.$class.'.php';
	}
}
// </editor-fold>

