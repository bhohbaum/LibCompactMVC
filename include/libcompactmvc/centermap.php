<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Calculates approximately the center of a rectangle
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class CenterMap {
	// this class will only work for germany!!!
	// there may occur unexpected results for other areas on our globe...
	private $max_lat;
	private $min_lat;
	private $max_lng;
	private $min_lng;

	public function __construct() {
	}

	public function add_element($lat, $lng) {
		if ($this->max_lat == null) {
			$this->max_lat = $lat;
		}
		if ($this->min_lat == null) {
			$this->min_lat = $lat;
		}
		if ($this->max_lng == null) {
			$this->max_lng = $lng;
		}
		if ($this->min_lng == null) {
			$this->min_lng = $lng;
		}
		if ($lat > $this->max_lat) {
			$this->max_lat = $lat;
		}
		if (($lat < $this->min_lat) && ($lat != null)) {
			$this->min_lat = $lat;
		}
		if ($lng > $this->max_lng) {
			$this->max_lng = $lng;
		}
		if (($lng < $this->min_lng) && ($lng != null)) {
			$this->min_lng = $lng;
		}
	}

	public function get_center() {
		$ret['lat'] = $this->min_lat + (($this->max_lat - $this->min_lat) / 2);
		$ret['lng'] = $this->min_lng + (($this->max_lng - $this->min_lng) / 2);
		return $ret;
	}

	public function get_zoom() {
		$diff_lat = $this->max_lat - $this->min_lat;
		$diff_lng = $this->max_lng - $this->min_lng;
		$rel_lat = $diff_lat;
		$rel_lng = $diff_lng * 1.5;
		$diff = ($rel_lat > $rel_lng) ? $rel_lat : $rel_lng;
		$zoom = round(9.6 + ($diff * $diff) / 25 - $diff);
		$zoom = ($zoom < 5) ? 5 : $zoom;
		return $zoom;
	}

}

?>