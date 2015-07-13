<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * This class can be used as base class for all Singleton based constructs.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
abstract class Singleton {
	// keeps instance of the class
	private static $instance;
	public $log;

	protected function __construct() {
		$this->open_db();
		$this->log = new Log(Log::LOG_TYPE_FILE);
		$this->log->set_log_file(LOG_FILE);
	}

	public function __destruct() {
		// Do not close the DB, as other objects might still need a connection.
		// $this->close_db();
	}

	// prevent cloning
	private function __clone() {
		DLOG(__METHOD__);
	}

	/**
	 *
	 * @return returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($name) {
		DLOG(__METHOD__);
		if ((!isset(self::$instance)) || (!array_key_exists($name, self::$instance))) {
			if (($name == null) || ($name == "")) {
				$name = get_class($this);
			}
			self::$instance[$name] = new $name();
		}

		return self::$instance[$name];
	}

}