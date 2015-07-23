<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Data Object Tools.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class DTOTool {
	public $log;

	/**
	 * Copy DTO members from one object to another
	 *
	 * @param DTO $src in / out
	 * @param DTO $dst in / out
	 */
	public static function copy(&$src, &$dst) {
		$in = json_decode(json_encode($src), true);
		foreach ($in as $key => $val) {
			if (is_object($key) || is_null($key)) {
				continue;
			}
			$dst->{$key} = $src->{$key};
		}
	}

}
