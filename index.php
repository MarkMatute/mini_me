<?php
header("Access-Control-Allow-Origin: *");
session_start();

/**
 * Require Core Files
 */
require_once 'Config.php';
require_once 'core/Controller.php';
require_once 'core/Model.php';

/**
 * Prepare Route 
 */
if(isset($_GET['ctrl'])){

	$controller = $_GET['ctrl'];
	$action     = isset($_GET['act'])?$_GET['act']:'index';

}else{

	$controller = "defaultCtrl";
	$action     = "index";
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