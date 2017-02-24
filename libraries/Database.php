<?php

# Singleton Database Class
# Uses PDO Driver

class Database {

	# Instance of THIS class
	private $_instance = NULL;

	# Database Connection
	private $_conn     = NULL;

	# Constructor
	public function __construct(){
		$this->_conn = new PDO('mysql:host=localhost;dbname=tester','root','');
	}	

	# Get Singleton Instance
	public static function getInstance(){
		if(!self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}		

	# Hide Clone
	private function __clone(){}

}