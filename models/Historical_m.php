<?php

/**
 * Historical Model
 */
class Historical_m extends Model {
	// Mongo Instance
	private $mongo;

	// PostGre Instance
	private $gpstrack;

	// MySQL Instances
	private $dotc_bms_mysql;
	private $dotc_gtfs_data;

	// Response Variable
	private $response = array();

	/**
	 * Constructor
	 */
	public function __construct(){
		parent::__construct();

		// Get Mongo Instance
		$this->mongo = $this->db->Mongo_Db();

		// MySQL Instances
		$this->dotc_gtfs_data = $this->db->Dotc_gtfs_data();
		$this->dotc_bms_mysql = $this->db->Dotc_bms_mysql();

		// PostGres Instace
		$this->gpstrack = $this->db->Gpstrack();

	}

	/**
	 * Get Historical Data of A Vehicle
	 * @param  String $plate_no 
	 * @param  String $start    Start Time / Date
	 * @param  String $end      End Time / Date
	 * @return array           
	 */
	public function get_by_vehicle($plate_no,$start,$end){

		// Gps Ids
		$gps_ids = $this->get_gps_ids('vehicle',$plate_no);
		
		// Check Source
		$data_source = $gps_ids[0]['data_source'];

		// FROM ASTI
		if($data_source=='asti'){
			// Get Data
			$stmt = $this->gpstrack->prepare("SELECT lt,ln,sp,(dt+interval '8 hours')as dt,co,dt_rcv FROM tblgpsdata WHERE id=:gps_id AND (dt+interval '8 hours') BETWEEN :start and :end AND NOT ((lt)::float>20.7 OR (lt)::float<5 OR (ln)::float<116 OR (ln)::float>127) ORDER BY dt");
			$stmt->bindParam(":gps_id",$gps_ids[0]['gps_id']);
			$stmt->bindParam(":start",$start);
			$stmt->bindParam(":end",$end);
			$stmt->execute();

			// Build response
			$this->response['vehicles']  = $stmt->fetchAll(PDO::FETCH_ASSOC);
			$this->response['last_seen'] = $this->response['vehicles'][count($this->response['vehicles'])-1]['dt'];
		}

		// FROM c4.eacomm
		elseif($data_source=="c4.eacomm.com"){

			// Set GPS Id
			$gps_id = $gps_ids[0]['gps_id'];

			// Get Data From c4.api
			$result = json_decode(file_get_contents("http://c4.eacomm.com/dotc_gps_api/index.php?method=historical_vehicle&id=".$gps_id."&from=".urlencode($start)."&to=".urlencode($end)));

			// Build response
			$this->response['vehicles']  = $result->result;
		}

		// Return response
		return $this->response;
	}

	/**
	 * Get Historical Data of Multiple Vehicles
	 * @param  String $plate_no 
	 * @param  String $start    Start Time/Date
	 * @param  String $end      End Time / Date
	 * @return array           
	 */
	public function get_by_multiple_vehicles($plate_no,$start,$end){

		// Gps Ids
		$gps_ids = $this->get_gps_ids('mutiple',$plate);

		// Raw Gps Ids
		$gps_ids_raw_arr = array();

		// Build Raw GPS array
		foreach ($gps_ids as $key => $value) {
			array_push($gps_ids_raw_arr,"'".$value['gps_id']."'");
		}		

		// Build GPS String
		$gps_id_str = implode(',', $gps_ids_raw_arr);
		$gps_id_str = "(".$gps_id_str.")";

		// Get Data
		$stmt = $this->gpstrack->prepare("SELECT lt,ln,sp,(dt+interval '8 hours')as dt,co FROM tblgpsdata WHERE id IN :gps_ids AND (dt+interval '8 hours') BETWEEN :start and :end AND NOT ((lt)::float>20.7 OR (lt)::float<5 OR (ln)::float<116 OR (ln)::float>127) ORDER BY dt GROUP BY id");
		$stmt->bindParam(':gps_ids',$gps_id_str);
		$stmt->bindParam(':start',$start);
		$stmt->bindParam(':end',$end);
		$stmt->execute();

		// Build Response
		$this->reponse['vehicles'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Return Response
		return $this->response;
	}	

	/**
	 * Get Data of Operators
	 * @param  String $operator 
	 * @return array           
	 */
	public function get_by_operator($operator){

		// Get Data
		$stmt = $this->dotc_bms_mysql->prepare("SELECT dotc_pub_unit.gps_id,dotc_pub_unit.plate_no FROM dotc_pub_unit INNER JOIN dotc_pub_frnc ON dotc_pub_frnc.case_no=dotc_pub_unit.case_no WHERE  dotc_pub_frnc.name LIKE '%$operator%' AND dotc_pub_unit.gps_id!=''");
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}


	/**
	 * Get Data of Routes
	 * @param  String $route 
	 * @return array        
	 */
	public function get_by_route($route){

		// Get Data
		$stmt = $this->dotc_gtfs_data->prepare("SELECT route_id FROM routes WHERE route_long_name LIKE '%".$route."%'");
		$stmt->execute();
		$rs = $stmt->fetchAll(PDO::FETCH_OBJ);

		// Route Code
		$route_code = array();

		// Populate the Route Code
		foreach ($rs as $key => $value) {
			array_push($route_code,"'".$value->route_id."'");
		}

		// Build Route Code String
		$route_code = implode(',', $route_code);

		// get The Unit
		$stmt = $this->dotc_bms_mysql->prepare("SELECT dotc_pub_unit.gps_id,dotc_pub_unit.plate_no FROM dotc_pub_unit INNER JOIN dotc_pub_frnc ON dotc_pub_frnc.case_no=dotc_pub_unit.case_no WHERE  dotc_pub_frnc.route_code IN ($route_code) AND dotc_pub_unit.gps_id!=''");
		$stmt->bindParam(":route_code",$route_code);
		$stmt->execute();

		// build Response
		$this->response = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Return Response
		return $this->response;
	}		

	/**
	 * Get Historical Data By Area
	 * @param  String $center_lat 
	 * @param  String $center_lng 
	 * @param  String $start      Start Time/ Date
	 * @param  String $end        End Time / Date
	 * @param  String $radius     
	 * @return array             
	 */
	public function get_by_area($center_lat,$center_lng,$start,$end,$radius){

		// Get GPS IDS
		$this->gpstrack->prepare("SELECT DISTINCT ON(id) id
					FROM (
					    SELECT id,(6371 * acos( cos( radians(:center_lat) ) * cos(radians(lt::float)) * cos(radians(ln::float) - radians(:center_lng) ) + sin( radians(:center_lat) ) * sin( radians(lt::float) ) ) ) AS distance
					    FROM tblgpsdata WHERE dt between :start AND :end
					) AS busses
				WHERE distance < :radius");
		$stmt->bindParam(':center_lat',$center_lat);
		$stmt->bindParam(':center_lng',$center_lng);
		$stmt->bindParam(':start',$start);
		$stmt->bindParam(':end',$end);
		$stmt->bindParam(':radius',$radius);
		$stmt->execute();
		$rs = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// GPS ids
		$gps_ids = array();

		// Build GPS ids 
		foreach ($rs as $key => $value) {
			array_push($gps_ids,"'".$value['id']."'");
		}

		// Build GPS ids String
		$gps_ids = implode(',',$gps_ids);

		// Get Data
		$stmt = $this->dotc_bms_mysql->prepare("SELECT gps_id,plate_no FROM dotc_pub_unit WHERE gps_id IN ($gps_ids) AND gps_id!=''");
		$stmt->execute();
		
		// Build Response
		$this->reponse = $stmt->fetchAll(PDO::FETCH_ASSOC);

		// Return Response
		return $this->response;
	}

	/**
	 * Get GPS Ids
	 * @param  String $type  
	 * @param  String $value 
	 * @return array        
	 */
	public function get_gps_ids($type,$value){

		// GPS Ids per plate_no
		if($type=='vehicle'){
			$stmt = $this->dotc_bms_mysql->prepare("SELECT dotc_pub_unit.gps_id,dotc_pub_frnc.data_source FROM dotc_pub_unit INNER JOIN dotc_pub_frnc ON dotc_pub_unit.case_no=dotc_pub_frnc.case_no WHERE dotc_pub_unit.plate_no = :plate_no AND gps_id!='' LIMIT 1");
			$stmt->bindParam(':plate_no',$value);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

		// GPS ids for multiple
		elseif($type=='mutiple'){
			$stmt = $this->Dotc_bms_mysql->prepare("SELECT gps_id FROM dotc_pub_unit WHERE plate_no IN :plate_no AND gps_id!=''");
			$stmt->bindParam(':plate_no',$value);
			$stmt->execute();
			return $stmt->fetchAll(PDO::FETCH_ASSOC);
		}

	}


}
/* EOF */