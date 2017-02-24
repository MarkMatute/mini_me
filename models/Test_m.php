<?php

/**
 * Test Model
 */
class Test_m extends Model{

	public function __construct(){	
		parent::__construct();
	}
	
	public static function foo(){
		return array(
			'foo' => 'bar'
		);
	}

}
/* EOF */