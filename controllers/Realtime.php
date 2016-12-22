<?php

/**
 * Realtime Controller
 */
class Realtime extends Controller {
	private $Realtime_m;
	/**
	 * Realtime Contructor
	 */
	public function __construct(){	
		parent::__construct();

		// Load Realtime_m Model
		require_once(MODELS.'/Realtime_m.php');

		$this->Realtime_m = new Realtime_m();
	}

	/**
	 * Get Default
	 * @return json All Running Vehicles
	 */
	public function get_all(){
		$this->response['vehicles'] = $this->Realtime_m->get_all();
		$this->return_json();
	}

	/**
	 * Get Vehicle By Plate No
	 * @return json
	 */
	public function get_by_vehicle(){

		// Prepare Vehicle
		if(!isset($_POST['plate'])){
			$this->response['error'] = "`plate` is needed...";
		}else{
			$plate = $_POST['plate'];
			$this->response['vehicles'] = $this->Realtime_m->get_by_vehicle($plate);
		}

		// Return JSON
		$this->return_json();
	}

	/**
	 * Get Vehicles By Routes
	 * @return json
	 */
	public function get_by_route(){

		// Prepare Route
		if(!isset($_POST['route'])){
			$this->response['error'] = "`route` is needed...";
		}else{
			$route = $_POST['route'];
			$this->response['vehicles'] = $this->Realtime_m->get_by_route($route);
		}

		// Return JSON
		$this->return_json();
	}

	/**
	 * Get Vehicles By Operator
	 * @return json 
	 */
	public function get_by_operator(){

		// Prepare Operator
		if(!isset($_POST['operator'])){
			$this->response['error'] = "`operator` is needed...";
		}else{
			$operator = $_POST['operator'];
			$this->response['vehicles'] = $this->Realtime_m->get_by_operator($operator);
		}

		// Return JSON
		$this->return_json();
	}

	/**
	 * Get Route Points
	 * @param  String $route 
	 * @return json        
	 */
	public function get_stops(){

		// Prepare Stops
		if(!isset($_POST['route'])){
			$this->response['error'] = "`route` is needed...";
		}else{
			$route = $_POST['route'];
			$route_id = $_POST['route_id'];
			$this->response['stops'] = $this->Realtime_m->get_stops($route,$route_id);
			# $this->response['route_id'] = $route_id;
		}

		// Return JSON
		$this->return_json();
	}

}
/* EOF */