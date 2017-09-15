<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Session handler
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class Session {
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
				if (defined('SESSION_INSECURE_COOKIE')) {
					if (!SESSION_INSECURE_COOKIE)
						if (is_tls_con())
							ini_set('session.cookie_secure', 1);
				} else if (is_tls_con())
					ini_set('session.cookie_secure', 1);
				session_start();
			}
		}
		$this->session_id = (session_id() == "") ? (getenv("PHPSESSID") !== false) ? getenv("PHPSESSID") : "" : session_id();
		DLOG("Session ID = " . $this->session_id);
		self::$parray = unserialize(RedisAdapter::get_instance()->get("SESSION_" . $this->session_id));
		DLOG("Loaded current content: " . var_export(unserialize(RedisAdapter::get_instance()->get("SESSION_" . $this->session_id)), true));

		// The following lines change the session id with every request.
		// This makes it harder for attackers to "steal" our session.
		// THIS WILL CAUSE TROUBLE WITH AJAX CALLS!!!
		if (!defined("SESSION_DYNAMIC_ID_DISABLED") || !SESSION_DYNAMIC_ID_DISABLED) {
			if (ini_get("session.use_cookies")) {
				$sname = session_name();
				setcookie($sname, '', time() - 42000);
				unset($_COOKIE[$sname]);
			}
			session_destroy();
			ini_set('session.cookie_httponly', 1);
			if (defined('SESSION_INSECURE_COOKIE')) {
				if (!SESSION_INSECURE_COOKIE)
					if (is_tls_con())
						ini_set('session.cookie_secure', 1);
			} else if (is_tls_con())
				ini_set('session.cookie_secure', 1);
			session_start();
			session_regenerate_id(true);
		}
	}

	// prevent cloning
	private function __clone() {
		DLOG();
	}

	// store our data into the $_SESSION variable
	public function __destruct() {
		if (!isset(self::$instance)) {
			DLOG("Sessions was destroyed. Deleting redis data.");
			RedisAdapter::get_instance()->delete("SESSION_" . $this->session_id);
		}
		DLOG("Saving current content: " . var_export(self::$parray, true));
		RedisAdapter::get_instance()->set("SESSION_" . $this->session_id, serialize(self::$parray));
		RedisAdapter::get_instance()->expire("SESSION_" . $this->session_id, SESSION_TIMEOUT);
		ActiveSessions::get_instance()->update();
	}

	/**
	 * returns the instance of this class.
	 * this is a singleton. there can only be one instance.
	 *
	 * @return Session
	 */
	public static function get_instance() {
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
		DLOG("('" . $pname . "', '" . $value . "')");
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
		DLOG("('" . $pname . "') return: '" . $ret . "'");
		return $ret;
	}

	/**
	 *
	 * @param String $pname
	 *        	property name
	 */
	public function clear_property($pname) {
		DLOG($pname);
		unset(self::$parray[$pname]);
	}

	/**
	 * clears all data from the session
	 */
	public function clear() {
		DLOG();
		self::$parray = array();
	}

	/**
	 * destroys the session
	 */
	public function destroy() {
		DLOG();
		if (ini_get("session.use_cookies")) {
			$sname = session_name();
			setcookie($sname, '', time() - 42000);
			unset($_COOKIE[$sname]);
		}
		session_destroy();
		ini_set('session.cookie_httponly', 1);
		if (defined('SESSION_INSECURE_COOKIE')) {
			if (!SESSION_INSECURE_COOKIE)
				if (is_tls_con())
					ini_set('session.cookie_secure', 1);
		} else if (is_tls_con())
			ini_set('session.cookie_secure', 1);
		session_start();
		session_regenerate_id(true);
		unset(self::$instance);
	}

	/**
	 *
	 * @return Session ID
	 */
	public function get_id() {
		DLOG("Return: " . $this->session_id);
		return $this->session_id;
	}

	/**
	 * Forcibly set the given session id and load their data.
	 *
	 * @param unknown_type $id
	 */
	public function force_id($id) {
		DLOG("Saving current content: " . var_export(self::$parray, true));
		RedisAdapter::get_instance()->set("SESSION_" . $this->session_id, serialize(self::$parray));
		RedisAdapter::get_instance()->expire("SESSION_" . $this->session_id, SESSION_TIMEOUT);
		session_id($id);
		$this->session_id = $id;
		DLOG("Session ID = " . $this->session_id);
		self::$parray = unserialize(RedisAdapter::get_instance()->get("SESSION_" . $this->session_id));
		ActiveSessions::get_instance()->update();
	}

}
