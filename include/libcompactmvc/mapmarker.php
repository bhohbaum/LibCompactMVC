<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Map Marker
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class MapMarker extends DbObject {

	public function __construct($lat, $lng, $isnew = true) {
		parent::__construct(array(
				"lat" => $lat,
				"lng" => $lng
		), $isnew);
	}

}
