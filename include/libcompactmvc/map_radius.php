<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * This class calculates if a given point on earth (designated by its latitude and longitude) lies within a
 * given radius around another point.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class MapRadius {
	private $lat_ctr;
	private $lng_ctr;
	private $radius;
	private $unit;
	const UNIT_TYPE_KILOMETRES = 0;
	const UNIT_TYPE_MILES = 1;
	const UNIT_TYPE_NAUTIC_MILES = 2;

	/**
	 * Instantiate this class giving a circle on our globe designated by its coordinates and radius.
	 *
	 * @param Float $lat_ctr
	 *        	latitude of the center
	 * @param Float $lng_ctr
	 *        	longitude of the center
	 * @param Float $radius
	 *        	radius
	 */
	public function __construct($lat_ctr, $lng_ctr, $radius, $unit = MapRadius::UNIT_TYPE_KILOMETRES) {
		$this->lat_ctr = $lat_ctr;
		$this->lng_ctr = $lng_ctr;
		$this->radius = $radius;
		$this->unit = $unit;
	}

	/**
	 * Set the latitude of the center.
	 *
	 * @param Float $lat
	 *        	latitude
	 */
	public function set_lat($lat) {
		$this->lat_ctr = $lat;
	}

	/**
	 * Set the longitude of the center.
	 *
	 * @param Float $lng
	 *        	longitude
	 */
	public function set_lng($lng) {
		$this->lng_ctr = $lng;
	}

	/**
	 * Set the radius.
	 *
	 * @param Float $radius
	 *        	radius
	 */
	public function set_radius($radius) {
		$this->radius = $radius;
	}

	/**
	 * Set the unit type
	 *
	 * @param const $unit
	 *        	the unit type to use
	 */
	public function set_unit($unit) {
		$this->unit = $unit;
	}

	/**
	 * Check if the given point is within the circle.
	 *
	 * @param Float $lat
	 *        	latitude
	 * @param Float $lng
	 *        	longitude
	 */
	public function is_inside($lat, $lng) {
		if ($this->radius == null) {
			throw new Exception("Radius is not set.");
		}
		$theta = $this->lng_ctr - $lng;
		$dist = sin(deg2rad($this->lat_ctr)) * sin(deg2rad($lat)) + cos(deg2rad($this->lat_ctr)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$dist = $this->apply_unit($dist);
		return ($dist <= $this->radius) ? true : false;
	}

	/**
	 * Get the distance between the center and the given point.
	 *
	 * @param Float $lat
	 *        	latitude
	 * @param Float $lng
	 *        	longitude
	 */
	public function get_dist($lat, $lng) {
		$theta = $this->lng_ctr - $lng;
		$dist = sin(deg2rad($this->lat_ctr)) * sin(deg2rad($lat)) + cos(deg2rad($this->lat_ctr)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$dist = $this->apply_unit($dist);
		return $dist;
	}

	private function apply_unit($degrees) {
		switch ($this->unit) {
			case 0:
				$dist = $degrees * 111.13384;
				break;
			case 1:
				$dist = $degrees * 69.05482;
				break;
			case 2:
				$dist = $degrees * 59.97662;
		}
		return $dist;
	}


}
