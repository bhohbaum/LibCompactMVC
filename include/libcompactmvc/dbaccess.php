<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * this class handles our DB connection and requests
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
abstract class DbAccess {
	protected $mysqli;
	public $log;
	
	// keeps instance of the class
	private static $instance;

	public abstract function write2log($loglevel, $date, $text);

	private function __construct() {
		$this->mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
		
		if (mysqli_connect_error()) {
			throw new Exception('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error(), mysqli_connect_errno());
		}
		
		$this->log = new Log(Log::LOG_TYPE_FILE);
		$this->log->set_log_file(LOG_FILE);
	}

	public function __destruct() {
		$this->close_db();
	}
	
	// prevent cloning
	private function __clone() {
		;
	}

	/**
	 *
	 * @return returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($name) {
		if ((!isset(self::$instance)) || (!array_key_exists($name, self::$instance))) {
			if (($name == null) || ($name == "")) {
				$name = get_class($this);
			}
			self::$instance[$name] = new $name();
		}
		
		return self::$instance[$name];
	}

	public function close_db() {
		if ($this->mysqli != null) {
			$this->mysqli->close();
			$this->mysqli = null;
		}
	}

	protected function run_query($query, $has_multi_result = false, $object = false, $field = null) {
		$ret = null;
		DLOG($query);
		$object = ($field == null) ? $object : false;
		if (!($result = $this->mysqli->query($query))) {
			throw new Exception(ErrorMessages::DB_QUERY_ERROR . '"' . $this->mysqli->error . '"' . "\nQuery: " . $query);
		} else {
			if (is_object($result)) {
				if ($has_multi_result) {
					if ($object) {
						while ($row = $result->fetch_object()) {
							$ret[] = $row;
						}
					} else {
						while ($row = $result->fetch_assoc()) {
							if ($field != null) {
								$ret[] = $row[$field];
							} else {
								$ret[] = $row;
							}
						}
					}
				} else {
					if ($object) {
						while ($row = $result->fetch_object()) {
							$ret = $row;
						}
					} else {
						while ($row = $result->fetch_assoc()) {
							if ($field != null) {
								$ret = $row[$field];
							} else {
								$ret = $row;
							}
						}
					}
				}
				$result->close();
			} else {
				$ret = $this->mysqli->insert_id;
			}
		}
		if (($ret == null) && ($has_multi_result == true)) {
			$ret = array();
		}
		return $ret;
	}
	
	public function autocommit($mode) {
		if (!$this->mysqli->autocommit($mode)) {
			throw new Exception(__METHOD__." MySQLi error: ".$this->mysqli->error);
		}
	}
	
	public function begin_transaction() {
		if (function_exists("mysqli_begin_transaction")) {
			if (!$this->mysqli->begin_transaction()) {
				throw new Exception(__METHOD__." MySQLi error: ".$this->mysqli->error);
			}
		}
		$this->autocommit(false);
	}
	
	public function commit() {
		if (!$this->mysqli->commit()) {
			throw new Exception(__METHOD__." MySQLi error: ".$this->mysqli->error);
		}
		$this->autocommit(true);
	}
		
	public function rollback() {
		if (!$this->mysqli->rollback()) {
			throw new Exception(__METHOD__." MySQLi error: ".$this->mysqli->error);
		}
	}
		
}

?>