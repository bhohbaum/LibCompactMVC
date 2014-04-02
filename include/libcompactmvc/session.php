<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Session handler
 * 
 * @author		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package	LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 24.01.2012
 * @license	LGPL version 3
 * @link		https://github.com/bhohbaum/libcompactmvc
 */
class Session
{
	// REMEMBER!!!
	// NEVER use the $_SESSION directly when using this class!
	// your data will get lost!
	
	// keeps instance of the classs
	private static $instance;
	
	// contains all session data
	private static $parray;
	
	// private constructor prevents direct instantiation
	private function __construct() 
	{
		if (!isset($_SESSION)) {
			session_start();
		}
		self::$parray = $_SESSION;
		
	//	The following lines change the session id with every click. 
	//	This makes it harder for attackers to "steal" our session.
	//	THIS CAN CAUSE TROUBLE WITH AJAX CALLS!!!
		if (!defined("SESSION_DYNAMIC_ID_DISABLED")) {
			if (ini_get("session.use_cookies")) {
				$sname = session_name();
				setcookie($sname, '', time() - 42000);
				unset($_COOKIE[$sname]);
			}
			session_destroy();
			session_start();
		}
	}

	// prevent cloning
	private function __clone()
	{
		;
	}

	// store our data into the $_SESSION variable
	public function __destruct() {
		$_SESSION = self::$parray;
	}
	
	/**
	 * returns the instance of this class. this is a singleton. there can only be one instance.
	 * 
	 * @return Session
	 */
	public static function get_instance() 
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}
	
	/**
	 * @param String $pname property name
	 * @param Any $value property value. can be a scalar, array or object.
	 */
	public function set_property($pname, $value)
	{
		self::$parray[$pname] = $value;
	}

	/**
	 * @param String $pname property name
	 * @return returns the property
	 */
	public function get_property($pname) {
		return (isset(self::$parray[$pname])) ? self::$parray[$pname] : null;
	}

	/**
	 * @param String $pname property name
	 */
	public function clear_property($pname) {
		unset(self::$parray[$pname]);
	}

	/**
	 * clears all data from the session
	 */
	public function clear() {
		self::$parray = array();
	}
}



?>