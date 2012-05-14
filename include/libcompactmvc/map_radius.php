<?php 
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

class MapRadius {
	
	private $lat_ctr;
	private $lng_ctr;
	private $radius;
	
	public function __construct($lat_ctr, $lng_ctr, $radius) {
		$this->lat_ctr = $lat_ctr;
		$this->lng_ctr = $lng_ctr;
		$this->radius = $radius;
	}
	
	public function set_lat($lat) {
		$this->lat_ctr = $lat;
	}
	
	public function set_lng($lng) {
		$this->lng_ctr = $lng;
	}
	
	public function set_radius($radius) {
		$this->radius = $radius;
	}

	public function is_inside($lat, $lng) {
		$theta = $this->lng_ctr - $lng;
		$dist = sin(deg2rad($this->lat_ctr)) * sin(deg2rad($lat)) + cos(deg2rad($this->lat_ctr)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$dist = $dist * 60 * 1.1515 * 1.609344;
		return ($dist <= $this->radius) ? true : false;
	}
	
	public function get_dist($lat, $lng) {
		$theta = $this->lng_ctr - $lng;
		$dist = sin(deg2rad($this->lat_ctr)) * sin(deg2rad($lat)) + cos(deg2rad($this->lat_ctr)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$dist = $dist * 60 * 1.1515 * 1.609344;
		return $dist;
	}
	
	
}



?>