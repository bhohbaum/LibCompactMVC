<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * This class can be used as base class for all Singleton based constructs.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class Singleton {
	// keeps instance of the class
	protected static $instance;

	/**
	 * Protected constructor to prevent uncontrolled instantiation.
	 */
	protected function __construct() {
	}

	public function __destruct() {
	}
	
	// prevent cloning
	private function __clone() {
	}

	/**
	 *
	 * @return returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null, $j = null, $k = null, $l = null, $m = null, $n = null, $o = null, $p = null) {
		$name = get_called_class();
		if ((!isset(self::$instance)) || (!array_key_exists($name, self::$instance))) {
			self::$instance[$name] = new $name($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
		}
		
		return self::$instance[$name];
	}

}