<?php
/**
 * Base Controller
 */

class Controller {

	/**
	 * Used for returning json_encoded array
	 * @var array
	 */
	public $response = array();

	/**
	 * Controller Contructor
	 */
	public function __construct(){
	}

	/**
	 * Function to echo json_encoded array
	 * @return [string]       [json_encoded value]
	 */
	public function return_json(){
		echo json_encode($this->response);
	}
}