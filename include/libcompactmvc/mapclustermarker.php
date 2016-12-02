<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Map Cluster-Marker
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class MapClusterMarker extends DbObject {

	public function __construct($lat, $lng, $size, $isnew = true) {
		parent::__construct(array(
				"lat" => $lat,
				"lng" => $lng,
				"size" => $size
		), $isnew);
	}

}
