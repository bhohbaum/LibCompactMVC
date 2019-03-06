<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Input validator
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Media Impression Unit 08
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Validator {

	private function __construct() {
		;
	}

	public static function email($string) {
		return filter_var($string, FILTER_VALIDATE_EMAIL) ? true : false;
	}

	public static function boolean($string) {
		return filter_var($string, FILTER_VALIDATE_BOOLEAN) ? true : false;
	}

	public static function float($string) {
		return filter_var($string, FILTER_VALIDATE_FLOAT) ? true : false;
	}

	public static function int($string) {
		return filter_var($string, FILTER_VALIDATE_INT) ? true : false;
	}

	public static function ipaddr($string) {
		return filter_var($string, FILTER_VALIDATE_IP) ? true : false;
	}

	public static function url($string) {
		return filter_var($string, FILTER_VALIDATE_URL) ? true : false;
	}
	
	public static function uuid($string) {
		return UUID::is_valid($string);
	}

}
