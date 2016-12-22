<?php

/**
 * Realtim Model
 */
class Realtime_m extends Model{
	// Mongo Instance
	private $mongo;

	// MySQL Instances
	private $dotc_bms_mysql;
	private $dotc_gtfs_data;

	/**
	 * Realtime Model Contructor
	 */
	public function __construct(){
		parent::__construct();

		// Get Mongo Instance
		$this->mongo = $this->db->Mongo_Db();
	}

	/**
	 * Get All Busses
	 * @return [json]
	 */
	public function get_all(){
		// Realtime Busses From dotc_realtime.json
		$realtime_busses 	= $this->get_realtime_busses();

		// Filter Busses on Mongo Db
		$response = $this->check_vehicle_existence($realtime_busses);			

	    // Return $response
	   	return $response;
	}		

	/**
	 * Get Vehicle By Plate Number
	 * @param  String $plate 
	 * @return array        
	 */
	public function get_by_vehicle($plate){

		// Gps IDs
		$gps_ids = $this->get_gps_ids("vehicle",$plate);

		// Realtime Busses
		$realtime_busses = $this->get_realtime_busses();

		// Response Busses
		$responseBusses = $this->build_response_busses($gps_ids,$realtime_busses);

		// Mongo Busses
		$mongo_busses = $this->get_mongo_busses();

		// Build Response
		$response = $this->check_vehicle_existence($responseBusses);

		// Return Busses response	
		return $response;
	}

	/**
	 * Get Vehicles By Route
	 * @param  boolean $route Route of Vehicles
	 * @return json         
	 */
	public function get_by_route($route = FALSE){

		// Route Code
		$route_code = $this->get_route_code($route);
		
		// Gps IDs
		$gps_ids = $this->get_gps_ids("route",$route_code);

		// Realtime Busses
		$realtime_busses = $this->get_realtime_busses();

		// Response Busses
		$responseBusses = $this->build_response_busses($gps_ids,$realtime_busses);

		// Mongo Busses
		$mongo_busses = $this->get_mongo_busses();

		// Build Response
		$response = $this->check_vehicle_existence($responseBusses);

		// Return Busses response	
		return $response;
	}

	/**
	 * Get Vehicles By Operator
	 * @param  String $operator 
	 * @return array           
	 */
	public function get_by_operator($operator){

		// GPS Ids 
		$gps_ids = $this->get_gps_ids("operator",$operator);

		// Reatlrime Busses
		$realtimeBusses = $this->get_realtime_busses();

		// Response Busses
		$responseBusses = $this->build_response_busses($gps_ids,$realtimeBusses);

		// Mongo Busses
		$mongo_busses = $this->get_mongo_busses();

		// Build Response
		$response = $this->check_vehicle_existence($responseBusses);

		// Return response
		return $response;

	}

	/**
	 * Get Stops
	 * @param  String $rooute 
	 * @return json         
	 */
	public function get_stops($route,$route_id = ''){
		// $stmt = $this->dotc_gtfs_data->prepare("SELECT stops.stop_lat,stops.stop_lon,stops.stop_name FROM stops INNER JOIN stop_times ON stops.stop_id=stop_times.stop_id INNER JOIN trips ON stop_times.trip_id=trips.trip_id INNER JOIN routes ON trips.route_id=routes.route_id WHERE routes.route_long_name=:route");
		// $stmt->bindParam(":route",$route);
		// $stmt->execute();
		// return $stmt->fetchAll(PDO::FETCH_ASSOC);
		
		$sql = $this->dotc_gtfs_data->prepare(
			"
				SELECT
					a.stop_lat, a.stop_lon, a.stop_name
				FROM
					`stops` a
				INNER JOIN
					`stop_times` b
				USING
					( `stop_id` )
				INNER JOIN
					`trips` c
				USING
					( `trip_id` )
				WHERE
					c.`route_id` = :route_id
			"
		);
		
		$sql->bindValue( ':route_id', $route_id, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return $rows;
	}	

	/**
	 * Build Response Busses
	 * @param  array $gps_ids         
	 * @param  array $realtime_busses 
	 * @return array                  
	 */
	public function build_response_busses($gps_ids,$realtime_busses){
		$responseBusses = array();
		foreach ($gps_ids as $key => $value) {
			foreach ($realtime_busses as $keyBus => $busValue) {
				if($value['gps_id']==$busValue->id){
					array_push($responseBusses, $busValue);
				}
			}
		}
		return $responseBusses;
	}

	/**
	 * Check Array of Busses if Is In Mongo DB
	 * @param  array $reltimeBusses 
	 * @return array                
	 */
	private function check_vehicle_existence($realtimeBusses){
		// Busses From Mongo Db
	    $mongo_busses    	= $this->get_mongo_busses();

	    // GPS Ids
		$gps_ids			= array();

		// Response Array
		$response 			= array();

		// Get GPS IDs
	    foreach($mongo_busses as $mongo_b){
	    	array_push($gps_ids, $mongo_b['details']['gps_id']);
	    }

	    // Check Busses Data
	    foreach ($realtimeBusses as $key => $value) {
	    	
	    	// If Source is c4.eacomm.com Check http://202.90.154.136/
	    	if($value->source=="c4.eacomm.com"){

	    		// Check If Bus exists
	    		$tmp_arr = $this->checkToC4($value);
	    		array_push($response,$tmp_arr);
	    	}

	    	//If ASTI Check Mongo
	    	else{
	    		if(in_array($value->id,$gps_ids)){
	    			$tmp_arr = array();
	    			$tmp_arr = $value;
	    			foreach ($mongo_busses as $mongo_b) {
	    				if($mongo_b['details']['gps_id']==$value->id){
	    					$tmp_arr->_name 	= $mongo_b['details']['name']; 
	    					$tmp_arr->_plate 	= $mongo_b['details']['plate_no'];
	    					$tmp_arr->_route 	= $mongo_b['details']['gtfs_route_name'];
							$tmp_arr->_route_id = $mongo_b['details']['gtfs_route_id'];
	    				}
	    			}
	    			array_push($response,$tmp_arr);
	    		}else{
	    			$tmp_arr = array();
	    			$tmp_arr = $value;
	    			$tmp_arr->_name 	= 'unknown';
	    			$tmp_arr->_plate 	= 'unknown';
	    			$tmp_arr->_route 	= 'unknown';
					$tmp_arr->_route_id = 'unknown';
	    			array_push($response,$tmp_arr);
	    		}
	    	}
	    }

	    return $response;
	}

	/**
	 * Check GPS ID if Registered
	 * @param  String $gps_id 
	 * @return aray         
	 */
	private function checkToC4($value){
		$tmp_arr = array();
		$tmp_arr = $value;

		$stmt = $this->dotc_bms_mysql->query("SELECT dotc_pub_frnc.name,dotc_pub_frnc.route_code,dotc_pub_unit.plate_no FROM dotc_pub_unit INNER JOIN dotc_pub_frnc ON dotc_pub_unit.case_no=dotc_pub_frnc.case_no WHERE dotc_pub_unit.gps_id='".$value->id."'");
		$stmt->execute();
	    $rs = $stmt->fetchAll(PDO::FETCH_OBJ);

	    // Name and Plate No
	    $tmp_arr->_name 	= $rs[0]->name;
	    $tmp_arr->_plate 	= $rs[0]->plate_no;
	    $route_code 	    = $rs[0]->route_code;

	    // Get Route Long Name
	    $stmt = $this->dotc_gtfs_data->query("SELECT route_long_name FROM `routes` WHERE `route_id`='$route_code'");
	    $stmt->execute();
	    $rs = $stmt->fetchAll(PDO::FETCH_OBJ);
        $tmp_arr->_route 	= $rs[0]->route_long_name;

        return $tmp_arr;
	}

	/**
	 * Get Vehicles Route Code
	 * @param  String $route Vehicle Route
	 * @return String Route ID
	 */
	private function get_route_code($route){
		$stmt = $this->dotc_gtfs_data->prepare("SELECT route_id FROM routes WHERE route_long_name=:route LIMIT 1");
		$stmt->bindParam(":route",$route);
		$stmt->execute();
		$rs = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $rs[0]->route_id;
	}

	/**
	 * Get GPS ids
	 * @param  STRING $type  route,operator
	 * @param  String $value for query
	 * @return array        
	 */
	private function get_gps_ids($type,$value){
		
		// GPS ID according to route
		if($type=="route"){
			$stmt = $this->dotc_bms_mysql->prepare("SELECT dotc_pub_unit.gps_id FROM dotc_pub_unit INNER JOIN dotc_pub_frnc ON dotc_pub_frnc.case_no=dotc_pub_unit.case_no WHERE  dotc_pub_frnc.route_code = :route_code AND dotc_pub_unit.gps_id!=''");
			$stmt->bindParam(":route_code",$value);
			$stmt->execute();
			$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $rs;
		}

		// GPS ID according to operator
		elseif($type=="operator"){
			$stmt = $this->dotc_bms_mysql->prepare("SELECT dotc_pub_unit.gps_id FROM dotc_pub_unit INNER JOIN dotc_pub_frnc ON dotc_pub_frnc.case_no=dotc_pub_unit.case_no WHERE  dotc_pub_frnc.name =:operator AND dotc_pub_unit.gps_id!=''");
			$stmt->bindParam(":operator",$value);
			$stmt->execute();
			$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $rs;
		} 

		// GPS ID according to plate no
		elseif ($type=="vehicle") {
			$stmt = $this->dotc_bms_mysql->prepare("SELECT gps_id FROM dotc_pub_unit WHERE plate_no = :plate AND gps_id!=''");
			$stmt->bindParam(":plate",$value);
			$stmt->execute();
			$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $rs;
		}

	}

	/**
	 * Get realtime busses from json
	 * @return JSON from dotc_realtime.json
	 */
	public function get_realtime_busses(){
		return json_decode(file_get_contents("../dotc_realtime.json"));
	}

	/**
	 * Get Busses From MongoDb
	 * @return json JSON result of busses from MongoDb
	 */
	public function get_mongo_busses(){

	    // Select Database
	    $db = $this->mongo->DOTCBMIS;

	    // Select Collection
	    $collection = $db->realtime;


	    $cursor = $collection->find();
			
		$gtfs_ids 	= array();
	    $result 	= array();


		foreach ($cursor as $document) {
			array_push($result, $document);
		}

		return $result;
	}

}
/* EOF */