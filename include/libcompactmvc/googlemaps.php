<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * This class provides some functions that make use of the Google API.
 * They where required to generate some
 * of the data now stored in the database in conjuction with the agencies.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class GoogleMaps {

	/**
	 * Private constructor.
	 * This class contains only static functions, hence there is no instantiation neccessary.
	 */
	private function __construct() {
	}

	/**
	 * Make Google give me the location of the given address.
	 *
	 * @param String $address        	
	 * @return The whole dataset received from Google.
	 */
	public static function encode($address) {
		$address = UTF8::encode($address);
		$get = "http://maps.google.com/maps/api/geocode/json?address=";
		$get .= urlencode($address);
		$get .= "&sensor=false";
		$curl_session = curl_init($get);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl_session);
		curl_close($curl_session);
		
		return json_decode($ret, true);
	}

	/**
	 * Let Google calculate e distance between two coordinates.
	 * This function calculates the distance using existing roads.
	 * Linear distance can be calculated using the MapRadius class.
	 *
	 * @param Float $lat_dep
	 *        	Latitude start point
	 * @param Float $lng_dep
	 *        	Longitude start point
	 * @param Float $lat_dest
	 *        	Latitude end point
	 * @param Float $lng_dest
	 *        	Longitude end point
	 * @return The whole dataset received from Google.
	 */
	public static function get_dist($lat_dep, $lng_dep, $lat_dest, $lng_dest) {
		$get = "http://maps.google.com/maps/api/directions/json?origin=$lat_dep,$lng_dep&destination=$lat_dest,$lng_dest&sensor=false";
		$curl_session = curl_init($get);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl_session);
		curl_close($curl_session);
		
		return json_decode($ret, true);
	}

}
