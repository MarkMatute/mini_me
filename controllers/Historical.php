<?php

/**
 * Historical Controller
 */
class Historical extends Controller {
	private $Historical_m;

	/**
	 * Constructor
	 */
	public function __construct(){
		parent::__construct();
		
		// Load Historical Model
		require_once(MODELS.'/Historical_m.php');

		$this->Historcal_m = new Historical_m();
	}

	/**
	 * Get Historical Data per vehicle
	 * @return json 
	 */
	public function get_by_vehicle(){
		
		// Check POST parameters
		if( !isset($_POST['plate_no']) || 
			!isset($_POST['start']) ||
			!isset($_POST['end'])
		){
			$this->response['error'] = "`plate` is needed...";
		}else{	

			// Vehicle Query Parameters
			$plate_no = $_POST['plate_no'];
			$start 	  = $_POST['start'];
			$end 	  = $_POST['end'];

			// Call Model
			$this->response['results'] = $this->Historcal_m->get_by_vehicle($plate_no,$start,$end);
		}

		// Return JSON
		$this->return_json();
	}

	/**
	 * Get Historical Data of Multiple Vehicles
	 * @return json 
	 */
	public function get_by_multiple_vehicles(){

		// Check POST parameters
		if( !isset($_POST['plate_no']) || 
			!isset($_POST['start']) ||
			!isset($_POST['end'])
		){
			$this->response['error'] = "`plate` is needed...";
		}else{	

			// Vehicle Query Parameters
			$plate_no = $_POST['plate_no'];
			$start 	  = $_POST['start'];
			$end 	  = $_POST['end'];

			// Call Model
			$this->response['results'] = $this->Historcal_m->get_by_multiple_vehicles($plate_no,$start,$end);
		}

		// Return JSON
		$this->return_json();
	}

	/**
	 * Get Historical Data of Operators
	 * @return json 
	 */
	public function get_by_operator(){

		// Check POST parameters
		if(!isset($_POST['operator'])){
			$this->reponse['error'] = "`operator` is needed...";
		}else{

			$operator = $_POST['operator'];
			
			// Call Model
			$this->response['results'] = $this->Historcal_m->get_by_operator($operator);
		}

		// Return JSON
		$this->return_json();	
	}

	/**
	 * Get Historical Data By Area
	 * @return json 
	 */
	public function get_by_route(){

		// Check POST parameters
		if(!isset($_POST['route'])){
			$this->response['error'] = "`route` is nedeed...";
		}else{

			$route = $_POST['route'];

			// Call Model
			$this->response['results'] = $this->Historcal_m->get_by_route($route);
		}

		// Return JSON
		$this->return_json();
	}


	public function get_by_area(){

		// Check POST parameters
		if( !isset($_POST['lat']) || !isset($_POST['lng']) ||!isset($_POST['start']) ||!isset($_POST['end']) ||!isset($_POST['radius'])){
			$this->response['error'] = "`parameters nedeed are incoreect...";
		}else{

			// Area Variables
			$center_lat =	$_POST['lat'];
			$center_lng = 	$_POST['lng'];
			$start 		= 	$_POST['start'];
			$end 		= 	$_POST['end'];
			$radius 	=	$_POST['radius'];

			$this->response 	=	$this->Historcal_m->get_by_area($center_lat,$center_lng,$start,$end,$radius);

		}

		// Return JSON
		$this->return_json();

	}

}
/* EOF */