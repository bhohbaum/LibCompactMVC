<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Point clustering on a map.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class MapCluster {
	private $single_markers = array();
	private $cluster_markers = array();
	
	// Minimum distance between markers to be included in a cluster, at diff. zoom levels
	private $distance;

	public function __construct($zoom) {
		$this->distance = (10000000 >> $zoom) / 100000;
	}

	public function cluster($markers) {
		// Loop until all markers have been compared.
		while (count($markers)) {
			$marker = array_pop($markers);
			$cluster = array();
			
			// Compare against all markers which are left.
			foreach ($markers as $key => $target) {
				$pixels = abs($marker->lat - $target->lat) + abs($marker->lng - $target->lng);
				
				// If the two markers are closer than given distance remove target marker from array and add it to cluster.
				if ($pixels < $this->distance) {
					unset($markers[$key]);
					$cluster[] = $target;
				}
			}
			
			// If a marker has been added to cluster, add also the one we were comparing to.
			if (count($cluster) > 0) {
				$cluster[] = $marker;
				$this->cluster_markers[] = $cluster;
			} else {
				$this->single_markers[] = $marker;
			}
		}
		return array_merge($this->cluster_markers, $this->single_markers);
	}

}
