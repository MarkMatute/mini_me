<?php
/**
 * Base Model
 */
class Model {

	// Database Variable
	public $db;

	/**
	 * Contructor For Model
	 */
	public function __construct(){

		// Initalize Database
		$this->db = new Database();
	}
}

/* EOF */