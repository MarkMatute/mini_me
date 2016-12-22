<?php

/**
 * Test Model
 */
class Test_m extends Model{

	private $mysql; 

	public function __construct(){	
		parent::__construct();
		$this->mysql = $this->db->mysql();
	}

	public function get_data(){
		return array(
			'foo' => 'bar'
		);
	}

}
/* EOF */