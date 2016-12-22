<?php
date_default_timezone_set('Asia/Manila');

/**
 * Playback Controller
 */
class Playback extends Controller {

	private $Playback_m;
	private $Historical_m;

	/**
	 * Constructor
	 */
	public function __construct(){
		parent::__construct();

		// Load Playback Model
		require_once(MODELS.'/Playback_m.php');
		$this->Playback_m = new Playback_m();

		// Load Historical Model
		require_once(MODELS.'/Historical_m.php');
		$this->Historical_m = new Historical_m();
		
	}

	/**
	 * Get Data of Vehicle	
	 * 
	 */
	public function get_data(){

		// Check if GPS is set
		if(isset($_POST['plate_no'])){

			// PLATE NO ID
			$plate_no = $_POST['plate_no'];

			// START DATE
			if(isset($_POST['start_date'])){
				$start = htmlspecialchars($_POST['start_date']);
			}else{
				$start = date("Y-m-d")." 00:00:00";
			}

			// END DATE
			if(isset($_POST['end_date'])){
				$end = htmlspecialchars($_POST['end_date']);
			}else{
				$end = date("Y-m-d")." 23:59:59";
			}

			$this->response['playback_data'] = $this->Historical_m->get_by_vehicle($plate_no,$start,$end);

		}else{
			$this->response['message'] = "plate_no is not set...";
			$this->response['status']  = FALSE;
		}

		// Return JSON
		$this->return_json();
	}

}