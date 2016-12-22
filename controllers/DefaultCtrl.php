<?php
/**
 * Default Controller
 */
class DefaultCtrl extends Controller{

	/**
	 * DefaultCtrl Constructor
	 */
	public function __construct(){
		parent::__construct();
	}

	/**
	 * Returns Error 404
	 * @return JSON encoded array 404
	 */
	function show_404(){
		$this->response['message'] = "404";
		$this->return_json();
	}

}