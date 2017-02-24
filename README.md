# mini_me
30kb PHP MVC Framework

<p>
  
  A Very Light PHP MVC Framework, best to be used on small APIs.

</p>

# Usage
<p>

  You can access a certain controller via URL by defining the GET variable 'ctrl' [Stands for Controller]
  
  and for the controller method, specify it to the GET variable 'act' [Stands for Action]

</p>

# Creating a Controller
<p>

  You can create a controller inside controllers directory, all controller must extends CONTROLLER Class.
  <br/>
  <b>Sample Controller</b>
  
  <pre>
class TestCtrl extends Controller{
	private $Test_m;	

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
  </pre>
</p>

# Creating a Model
<p id="modelSample">

  You can create a Model inside odels directory, all models must extends MODEL Class.
  <br/>
  <b>Sample Model</b>
  
  <pre>
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
  </pre>
</p>

# Connecting to Database
<p>
  Connecting to a database is fairly easy.You can create database connections on core/database.php
  Ive tried to connect it to the ff databases:
  <ul>
    <li>MySQL</li>
    <li>Postgre</li>
    <li>MongoDB</li>
  </ul>
</p>
<h4>Sample</h4>
<pre>
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
</pre>
<p>
  And you can access the database object via db object on Models, See <a href="#modelSample">model</a> example.
</p>
# Feel Free to Comment or Suggest edits improvements, just drop an email markernest.matute@gmail.com
