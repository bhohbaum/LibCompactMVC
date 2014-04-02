<?php 
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * This class calculates if a given point on earth (designated by its latitude and longitude) lies within a
 * given radius around another point.
 * 
 * @author		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package	LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 24.01.2012
 * @license	LGPL version 3
 * @link		https://github.com/bhohbaum/libcompactmvc
 */
class MapRadius {
	
	private $lat_ctr;
	private $lng_ctr;
	private $radius;
	
	/**
	 * Instantiate this class giving a circle on our globe designated by its coordinates and radius.
	 * @param Float $lat_ctr latitude of the center
	 * @param Float $lng_ctr longitude of the center
	 * @param Float $radius radius in Kilometeres
	 */
	public function __construct($lat_ctr, $lng_ctr, $radius) {
		$this->lat_ctr = $lat_ctr;
		$this->lng_ctr = $lng_ctr;
		$this->radius = $radius;
	}
	
	/**
	 * Set the latitude of the center.
	 * @param Float $lat latitude
	 */
	public function set_lat($lat) {
		$this->lat_ctr = $lat;
	}
	
	/**
	 * Set the longitude of the center.
	 * @param Float $lng longitude
	 */
	public function set_lng($lng) {
		$this->lng_ctr = $lng;
	}
	
	/**
	 * Set the radius in kilometeres.
	 * @param Float $radius radius in kilometeres
	 */
	public function set_radius($radius) {
		$this->radius = $radius;
	}
	
	/**
	 * Check if the given point is within the circle.
	 * @param Float $lat latitude
	 * @param Float $lng longitude
	 */
	public function is_inside($lat, $lng) {
		$theta = $this->lng_ctr - $lng;
		$dist = sin(deg2rad($this->lat_ctr)) * sin(deg2rad($lat)) + cos(deg2rad($this->lat_ctr)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$dist = $dist * 60 * 1.1515 * 1.609344;
		return ($dist <= $this->radius) ? true : false;
	}
	
	/**
	 * Get the distance in kilometeres between the center and the given point.
	 * @param Float $lat latitude
	 * @param Float $lng longitude
	 */
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