<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MySQLHost extends mysqli {
	private $host;
	private $user;
	private $pass;
	private $db;
	private $type;
	private $constructor_called;
	const SRV_TYPE_READ = 0;
	const SRV_TYPE_WRITE = 1;
	const SRV_TYPE_READWRITE = 2;

	public function __construct($host, $user, $pass, $db, $type) {
		if (!isset($host) || !isset($user) || !isset($pass) || !isset($db) || !isset($type)) {
			$code = isset($host) * 10000 + isset($user) * 1000 + isset($pass) * 100 + !isset($db) * 10 + !isset($type) * 1;
			$code = str_pad($code, 5, "0", STR_PAD_LEFT);
			throw new InvalidArgumentException("Missing parameter", $code, null);
		}
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this->db = $db;
		$this->type = $type;
		$this->constructor_called = false;
	}

	private function lazy_init() {
		if (!$this->constructor_called) {
			parent::__construct($this->host, $this->user, $this->pass, $this->db);
			if (!$this->set_charset("utf8"))
				throw new Exception("Error setting charset: " . $this->error, $this->errno);
			$this->constructor_called = true;
		}
	}

	public function get_host() {
		return $this->host;
	}

	public function get_user() {
		return $this->user;
	}

	public function get_db() {
		return $this->db;
	}

	public function get_type() {
		return $this->type;
	}

	public function query($query, $resultmode = NULL) {
		$this->lazy_init();
		return parent::query($query);
	}

	public function autocommit($mode) {
		$this->lazy_init();
		return parent::autocommit($mode);
	}

	public function begin_transaction($flags = NULL, $name = NULL) {
		$this->lazy_init();
		return parent::begin_transaction($flags, $name);
	}

	public function commit($flags = NULL, $name = NULL) {
		$this->lazy_init();
		return parent::commit($flags, $name);
	}

	public function rollback($flags = NULL, $name = NULL) {
		$this->lazy_init();
		return parent::rollback($flags, $name);
	}

	public function real_escape_string($str) {
		$this->lazy_init();
		if (is_object($str)) {
			ELOG("Object given instead of a String: " . print_r($str));
			ELOG("Using an empty String as value in the Query.");
			return "";
		}
		return parent::real_escape_string($str);
	}

}
