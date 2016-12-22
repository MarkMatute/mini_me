<?php
/*
 * Route Visualization Controller
 *
 */
 
class RouteVisualization extends Controller{
	private $RealtimeModel;
	private $collections;
	private $MongoDb;
	private $MysqlConn;
	
	public function __construct(){
		$this->RouteVisualization();
	}
	
	protected function RouteVisualization(){
		parent::__construct();

		$m = new MongoClient();
		$this->MongoDb = $m->selectDB( 'DOTCBMIS' );
		
		$this->collections = array(
			'realtime' => new MongoCollection( $this->MongoDb, 'realtime' ),
			'astidata' => new MongoCollection( $this->MongoDb, 'astidata' ),
			'stops_realtime' => new MongoCollection( $this->MongoDb, 'stops_realtime' )
		);
		
		$this->MysqlConn = new PDO( 'mysql:host=localhost;dbname=dotc_bms_mysql;charset=utf8', 'root', 'E@c0mM2o14' );
		$this->MysqlConn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
		$this->MysqlConn->setAttribute( PDO::ATTR_EMULATE_PREPARES, false );
		$this->MysqlConn->setAttribute( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
	}
	
	public function Reports( $details = array() ){
		$ret = array(
			'response' => false,
			'message' => '',
			'data' => array()
		);
		
		try{
			if(
				!isset( $details['type'] )
				|| strlen( trim( $details['type'] ) ) == 0
			){
				throw new Exception( 'Empty Report Type' );
			}
			
			switch( $details['type'] ){
				case 'summary_of_performance':
					if(
						!isset( $details['route_id'] )
						|| strlen( trim( $details['route_id'] ) ) == 0
					){
						throw new Exception( 'We need the route id for this report' );
					}
				
					# get vehicle list
					$vehicles = $this->FindBusesOnRouteMongoRealtime( $details['route_id'] );
					
					# loop over the vehicle array
					foreach( $vehicles AS $keys => $values ):
						# trip details = eta and next_stops
						# skip
				
						# average speed
						$values['details']['average_speed'] = $this->ComputeAverageSpeed( $values['coordinates'] );
						
						# idle time
						$values['details']['idle_time'] = $this->ComputeIdleTime( $values['coordinates'] );
					endforeach;
					
					$ret['response'] = true;
					$ret['data'] = $vehicles;
				break;
			}
		} catch( Exception $e ){
			$ret['message'] = $e->getMessage();
		}
		
		return $ret;
	}
	
	protected function ComputeIdleTime( $coordinates = array() ){
		$speed_arr = array();
		$idletime = 0;
		
		foreach( $coordinates AS $keys => $idle ):
			if( $idle['sp'] == 0 && $keys > 0 ){
				$datetime1 = strtotime( date( 'Y-m-d H:i:s', $coordinates[$keys - 1]['dt'] ) );
				$datetime2 = strtotime( date( 'Y-m-d H:i:s', $idle['dt'] ) );
				
				$idletime += ( $datetime1 - $datetime2 );
			}
		endforeach;
		
		return $idletime;
	}
	
	protected function SpeedDiagram( $coordinates = array() ){
		$speed_arr = array();
		
		foreach( $coordinates AS $keys => $values ):
			if( $values['sp'] > 0 ){
				array_push( $speed_arr, $values );
			}
		endforeach;
		
		return $speed_arr;
	}
	
	protected function ComputeAverageSpeed( $coordinates = array() ){
		$speed_arr = array();
		
		foreach( $coordinates AS $keys => $values ):
			if( $values['sp'] > 0 ){
				array_push( $speed_arr, $values['sp'] );
			}
		endforeach;
		
		return count( $speed_arr ) > 0 ? array_sum( $speed_arr ) / count( $speed_arr ) : 0;
	}
	
	protected function FindBusesOnRouteMongoRealtime( $route_id = '' ){
		return array(
			"details.trip_details.trip_id" => array(
				'$exists' => true
			),
			"details.gtfs_route_id" => $route_id
		),
		array(
			'details.trips' => 0,
			'nearest_shapes' => 0,
			'coordinates.nearest_shape' => 0
		);
	}
	
	protected function FindMongoRealtimeData( $plate_no = '' ){
		return array(
			'details' => $this->collections['realtime']->findOne(
				array(
					'item' => $plate_no
				),
				array(
					'details.trips' => 0,
					'nearest_shapes' => 0,
					'coordinates.nearest_shape' => 0
				)
			)
		);
	}
	
	protected function ListOfVehicles( $route_id = '' ){
		$sql = $this->MysqlConn->prepare(
			"
				SELECT
					`plate_no`, `name`, `case_no`, `route`, `data_source`, `vehicle_id`, `gtfs_route_id`, `gtfs_route_name`, `gps_id`
				FROM
					`dotc_unit_frnc`
				WHERE
					`gtfs_route_id` = :route_id
				ORDER BY
					`plate_no` ASC
			"
		);
		
		$sql->bindValue( ':route_id', $route_id, PDO::PARAM_STR );
		$sql->execute();
		
		$rows = $sql->fetchAll( PDO::FETCH_ASSOC );
		
		return $rows;
	}
}
?>