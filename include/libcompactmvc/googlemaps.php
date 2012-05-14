<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

class GoogleMaps {
	
	public function __construct() {
		
	}
	
	public function encode($address) {
		$address = UTF8::encode($address);
		$get = "http://maps.google.com/maps/api/geocode/json?address=";
		$get .= urlencode($address);
		$get .= "&sensor=false";
		$curl_session = curl_init($get);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl_session);
		curl_close($curl_session);

		return json_decode($ret);
	}
	
	public function get_dist($lat_dep, $lng_dep, $lat_dest, $lng_dest) {
		$get = "http://maps.google.com/maps/api/directions/json?origin=$lat_dep,$lng_dep&destination=$lat_dest,$lng_dest&sensor=false";
		$curl_session = curl_init($get);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl_session);
		curl_close($curl_session);
		
		return json_decode($ret);
	}
	
	
}


?>
