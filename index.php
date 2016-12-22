<?php
header("Access-Control-Allow-Origin: *");
/**
 * Require Core Files
 */
require_once 'Config.php';
require_once 'core/Controller.php';
require_once 'core/Model.php';
require_once 'core/Database.php';

/**
 * Prepare Route 
 */
if(isset($_GET['ctrl']) && isset($_GET['act'])){

	$controller = $_GET['ctrl'];
	$action     = $_GET['act'];

}else{

	$controller = "defaultCtrl";
	$action     = "show_404";

}

/**
 * Map the Controller And Method	
 * @param  $controller 
 * @param  $action     
 * @return NULL             
 */
function call($controller,$action){

	// File Name of The controller
	$controller_name = ucfirst($controller);

	// Require Controller File
	require_once('controllers/'.$controller_name.'.php');

	// // Instantiate Controller Object
	$controller_object = new $controller_name();

	// // Call Controller Action/Method
	$controller_object->{$action}();
}

call($controller,$action);

/* EOF */