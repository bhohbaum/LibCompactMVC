<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Map Marker
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class MapMarker extends DbObject {

	public function __construct($lat, $lng, $isnew = true) {
		parent::__construct(array(
				"lat" => $lat,
				"lng" => $lng
		), $isnew);
	}

}
