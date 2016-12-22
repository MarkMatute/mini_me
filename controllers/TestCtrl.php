<?php
/**
 * Default Controller
 */
class TestCtrl extends Controller{
	private $Test_m;	

	/**
	 * DefaultCtrl Constructor
	 */
	public function __construct(){
		parent::__construct();
		
		// Load Realtime_m Model
		require_once(MODELS.'/Test_m.php');

		$this->Test_m = new Test_m();
	}

	function test(){
		$this->response['message'] = $this->Test_m->get_data();
		$this->return_json();
	}

}