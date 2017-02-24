<?php

class Test extends Controller {

	public function __construct(){
		parent::__construct();
	}

	# Add `/` before view
	public function foo(){
		include(MODELS.'/Test_m.php');
		
		$message = Test_m::foo();

		include(VIEWS.'/foo.php');
	}

}