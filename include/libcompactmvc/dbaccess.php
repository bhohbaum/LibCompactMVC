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
	// keeps instance of the class
	private static $instance;
	protected static $mysqli;
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

	/**
	 *
	 * @throws Exception
	 */
	protected function open_db() {
		if (isset(self::$mysqli)) {
			return;
		}
		DLOG(__METHOD__ . ": Connecting to database...");
		self::$mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
		if (mysqli_connect_error()) {
			throw new Exception('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error(), mysqli_connect_errno());
		}
	}

	/**
	 *
	 */
	protected function close_db() {
		DLOG(__METHOD__);
		if (self::$mysqli != null) {
			self::$mysqli->close();
			self::$mysqli = null;
		}
	}

	/**
	 * Execute a DB query.
	 *
	 * @param String $query The query to execute.
	 * @param Boolean $has_multi_result Is one object expected as result, or a list?
	 * @param Boolean $object Return as array or as object
	 * @param String $field Columnname if a single value shall be returned.
	 * @param String $table Name of the table that is operated on.
	 * @throws Exception
	 * @return Ambigous <multitype:, NULL>
	 */
	protected function run_query($query, $has_multi_result = false, $object = false, $field = null, $table = null) {
		DLOG(__METHOD__ . ": " . $query);
		$ret = null;
		$object = ($field == null) ? $object : false;
		if (!($result = self::$mysqli->query($query))) {
			throw new Exception(ErrorMessages::DB_QUERY_ERROR . '"' . self::$mysqli->error . '"' . "\nQuery: " . $query);
		} else {
			if (is_object($result)) {
				if ($has_multi_result) {
					if ($object) {
						while ($row = $result->fetch_assoc()) {
							$tmp = new DbObject($row, false);
							if ($table != null) {
								$tmp->table($table);
							}
							$ret[] = $tmp;
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
						while ($row = $result->fetch_assoc()) {
							$tmp = new DbObject($row, false);
							if ($table != null) {
								$tmp->table($table);
							}
							$ret = $tmp;
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
				$ret = self::$mysqli->insert_id;
			}
		}
		if (($ret == null) && ($has_multi_result == true)) {
			$ret = array();
		}
		return $ret;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function by($tablename, $constraint) {
		$qb = new QueryBuilder();
		$constraint = ($constraint == null) ? array() : $constraint;
		$q = $qb->select($tablename, $constraint);
		$res = $this->run_query($q, true, true, null, $tablename);
		return $res;
	}

	/**
	 *
	 * @param unknown_type $mode
	 * @throws Exception
	 */
	public function autocommit($mode) {
		DLOG(__METHOD__);
		if (!self::$mysqli->autocommit($mode)) {
			throw new Exception(__METHOD__." MySQLi error: ".self::$mysqli->error);
		}
	}

	/**
	 *
	 * @throws Exception
	 */
	public function begin_transaction() {
		DLOG(__METHOD__);
		if (function_exists("mysqli_begin_transaction")) {
			if (!self::$mysqli->begin_transaction()) {
				throw new Exception(__METHOD__." MySQLi error: ".self::$mysqli->error);
			}
		}
		$this->autocommit(false);
	}

	/**
	 *
	 * @throws Exception
	 */
	public function commit() {
		DLOG(__METHOD__);
		if (!self::$mysqli->commit()) {
			throw new Exception(__METHOD__." MySQLi error: ".self::$mysqli->error);
		}
		$this->autocommit(true);
	}

	/**
	 *
	 * @throws Exception
	 */
	public function rollback() {
		DLOG(__METHOD__);
		if (!self::$mysqli->rollback()) {
			throw new Exception(__METHOD__." MySQLi error: ".self::$mysqli->error);
		}
	}

	/**
	 *
	 * @param unknown_type $str
	 * @throws Exception
	 */
	protected function escape($str) {
		// we don't DLOG here, it's spaming...
		// DLOG(__METHOD__);
		if (self::$mysqli) {
			return self::$mysqli->real_escape_string($str);
		}
		throw new Exception("DbAccess::mysqli is not initialized, unable to escape string.");
	}

	/**
	 * Converts arrays to DbObject, if required.
	 *
	 * @param Array_or_Object $var
	 * @return DbObject instance
	 */
	protected function mkobj($var) {
		DLOG(__METHOD__);
		return (is_object($var)) ? $var : new DbObject($var);
	}

	/**
	 * Use this method for values that can be null, when building the SQL query.
	 * Refrain from surrounding this return value with "'", as they are automatically added to string values!
	 *
	 * @param String_or_Number input value that has to be transformed
	 * @return String value to concatenate with the rest of the sql query
	 */
	protected function sqlnull($var) {
		// we don't DLOG here, it's spaming...
		// DLOG(__METHOD__);
		if (is_numeric($var)) {
			$var = ($var == null) ? "null" : $var;
		} else {
			$var = ($var == null) ? "null" : "'" . $var . "'";
		}
		return $var;
	}
}
