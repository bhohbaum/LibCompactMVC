<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Input provider
 * provides access to input vars and prevents them to appear in serialization
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class InputProvider extends InputSanitizer {
	private static $instance = null;
	
	protected function __construct() {
		DLOG();
		parent::__construct(null);
	}
	
	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = new InputProvider();
		}
		return self::$instance;
	}
	
	public function get_var($var_name) {
		return parent::__get($var_name);
	}
	
	/**
	 *
	 * @param unknown_type $var_name
	 * @throws InvalidMemberException
	 */
	public function __get($var_name) {
		throw new InvalidMemberException();
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @param unknown_type $value
	 */
	public function __set($var_name, $value) {
		throw new InvalidMemberException();
	}

	/**
	 */
	public function jsonSerialize() {
		return parent::to_array();
	}

}
