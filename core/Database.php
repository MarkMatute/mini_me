<?php

/**
 * A Database Class
 */

class Database {
	
	// MySQL Databases
	public $mysql;

	/**
	 * MySQL Connection
	 */
	public function mysql(){
		//$this->mysql = new PDO("mysql:host=localhost;dbname=dbname", 'root','pass');
	    //$this->mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		return $this->mysql;
	}

}
/* EOF */