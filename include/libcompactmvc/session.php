<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Session handler
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class Session {
	public $log;
	private $session_id;
	// REMEMBER!!!
	// NEVER use the $_SESSION directly when using this class!
	// your data will get lost!

	// keeps instance of the classs
	private static $instance;

	// contains all session data
	private static $parray;

	// private constructor prevents direct instantiation
	private function __construct() {
		if (!isset($_SESSION)) {
			if (php_sapi_name() != "cli") {
				ini_set('session.cookie_httponly', 1);
				session_start();
			}
		}
		$this->session_id = (session_id() == "") ? $_ENV['PHPSESSID'] : session_id();
		DLOG(__METHOD__ . ": Session ID = " . $this->session_id);
		self::$parray = unserialize(RedisAdapter::get_instance()->get(REDIS_KEY_PRAEFIX . "SESSION_" . $this->session_id));

		// The following lines change the session id with every click.
		// This makes it harder for attackers to "steal" our session.
		// THIS CAN CAUSE TROUBLE WITH AJAX CALLS!!!
		if (!defined("SESSION_DYNAMIC_ID_DISABLED")) {
			if (ini_get("session.use_cookies")) {
				$sname = session_name();
				setcookie($sname, '', time() - 42000);
				unset($_COOKIE[$sname]);
			}
			session_destroy();
			ini_set('session.cookie_httponly', 1);
			session_start();
		}
	}

	// prevent cloning
	private function __clone() {
		DLOG(__METHOD__);
	}

	// store our data into the $_SESSION variable
	public function __destruct() {
		DLOG(__METHOD__ . ": Saving current content: " . var_export(self::$parray, true));
		RedisAdapter::get_instance()->set(REDIS_KEY_PRAEFIX . "SESSION_" . $this->session_id, serialize(self::$parray));
		RedisAdapter::get_instance()->expire(REDIS_KEY_PRAEFIX . "SESSION_" . $this->session_id, SESSION_TIMEOUT);
	}

	/**
	 * returns the instance of this class.
	 * this is a singleton. there can only be one instance.
	 *
	 * @return Session
	 */
	public static function get_instance() {
		DLOG(__METHOD__ . ": Current content: " . var_export(self::$parray, true));
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c();
		}

		return self::$instance;
	}

	/**
	 *
	 * @param String $pname
	 *        	property name
	 * @param Any $value
	 *        	property value. can be a scalar, array or object.
	 */
	public function set_property($pname, $value) {
		DLOG(__METHOD__ . "('" . $pname . "', '" . $value . "')");
		self::$parray[$pname] = $value;
	}

	/**
	 *
	 * @param String $pname
	 *        	property name
	 * @return returns the property
	 */
	public function get_property($pname) {
		$ret = (isset(self::$parray[$pname])) ? self::$parray[$pname] : null;
		DLOG(__METHOD__ . "('" . $pname . "') return: '" . $ret . "'");
		return $ret;
	}

	/**
	 *
	 * @param String $pname
	 *        	property name
	 */
	public function clear_property($pname) {
		DLOG(__METHOD__);
		unset(self::$parray[$pname]);
	}

	/**
	 * clears all data from the session
	 */
	public function clear() {
		DLOG(__METHOD__);
		self::$parray = array();
	}


}
