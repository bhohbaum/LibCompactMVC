<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * MySQL adapter
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MySQLAdapter extends Singleton {
	private $hosts_r;
	private $hosts_w;
	private $host_idx_r;
	private $host_idx_w;
	private $last_host;

	protected function __construct($hosts) {
		DLOG();
		parent::__construct();
		foreach ($hosts as $host) {
			if ($host->get_type() == MySQLHost::SRV_TYPE_READ || $host->get_type() == MySQLHost::SRV_TYPE_READWRITE) {
				$this->hosts_r[] = $host;
			}
			if ($host->get_type() == MySQLHost::SRV_TYPE_WRITE || $host->get_type() == MySQLHost::SRV_TYPE_READWRITE) {
				$this->hosts_w[] = $host;
			}
			$this->hosts[] = $host;
			$this->host_idx_r = rand(0, count($this->hosts_r) - 1);
			$this->host_idx_w = rand(0, count($this->hosts_w) - 1);
		}
	}

	/*
	 * public function connect() {
	 * if (!$this->hosts_r[$this->host_idx_r]->connect()) throw new Exception("Error connecting to database: " . $this->hosts_r[$this->host_idx_r]->error, $this->hosts_r[$this->host_idx_r]->errno);
	 * if (!$this->hosts_r[$this->host_idx_r]->set_charset("utf8")) throw new Exception("Error setting charset: " . $this->hosts_r[$this->host_idx_r]->error, $this->hosts_r[$this->host_idx_r]->errno);
	 * $this->last_host = $this->hosts_r[$this->host_idx_r];
	 * if (!$this->hosts_w[$this->host_idx_w]->connect()) throw new Exception("Error connecting to database: " . $this->hosts_w[$this->host_idx_w]->error, $this->hosts_w[$this->host_idx_w]->errno);
	 * if (!$this->hosts_w[$this->host_idx_r]->set_charset("utf8")) throw new Exception("Error setting charset: " . $this->hosts_w[$this->host_idx_w]->error, $this->hosts_w[$this->host_idx_w]->errno);
	 * $this->last_host = $this->hosts_w[$this->host_idx_w];
	 * }
	 */
	public function close() {
		$this->hosts_r[$this->host_idx_r]->close();
		$this->last_host = $this->hosts_r[$this->host_idx_r];
		$this->hosts_w[$this->host_idx_w]->close();
		$this->last_host = $this->hosts_w[$this->host_idx_w];
	}

	public function query($query, $is_write_access, $table) {
		$ret = null;
		$this->host_idx_r = rand(0, count($this->hosts_r) - 1);
		$this->host_idx_w = rand(0, count($this->hosts_w) - 1);
		$key = REDIS_KEY_TBLCACHE_PFX . $table . "_" . md5($query);
		if ($is_write_access) {
			$ret = $this->hosts_w[$this->host_idx_w]->query($query, $is_write_access);
			$this->last_host = $this->hosts_w[$this->host_idx_w];
		} else {
			$ret = $this->hosts_r[$this->host_idx_r]->query($query, $is_write_access);
			$this->last_host = $this->hosts_r[$this->host_idx_r];
		}
		return $ret;
	}

	public function get_error() {
		if (!isset($this->last_host)) {
			throw new Exception("No query issued yet. Unable to retrieve last error.");
		}
		return $this->last_host->error;
	}

	public function get_errno() {
		if (!isset($this->last_host)) {
			throw new Exception("No query issued yet. Unable to retrieve last error message.");
		}
		return $this->last_host->errno;
	}

	public function get_insert_id() {
		if (!isset($this->last_host)) {
			throw new Exception("No query issued yet. Unable to retrieve insert id.");
		}
		return $this->last_host->insert_id;
	}

	public function autocommit($mode) {
		if (!$this->hosts_r[$this->host_idx_r]->autocommit($mode)) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_r[$this->host_idx_r]->error, $this->hosts_r[$this->host_idx_r]->errno);
		}
		if (!$this->hosts_w[$this->host_idx_w]->autocommit($mode)) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_w[$this->host_idx_w]->error, $this->hosts_w[$this->host_idx_w]->errno);
		}
	}

	public function begin_transaction() {
		if (!$this->hosts_r[$this->host_idx_r]->begin_transaction()) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_r[$this->host_idx_r]->error, $this->hosts_r[$this->host_idx_r]->errno);
		}
		if (!$this->hosts_w[$this->host_idx_w]->begin_transaction()) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_w[$this->host_idx_w]->error, $this->hosts_w[$this->host_idx_w]->errno);
		}
		$this->autocommit(false);
	}

	public function commit() {
		if (!$this->hosts_r[$this->host_idx_r]->commit()) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_r[$this->host_idx_r]->error, $this->hosts_r[$this->host_idx_r]->errno);
		}
		if (!$this->hosts_w[$this->host_idx_w]->commit()) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_w[$this->host_idx_w]->error, $this->hosts_w[$this->host_idx_w]->errno);
		}
		$this->autocommit(true);
	}

	public function rollback() {
		if (!$this->hosts_r[$this->host_idx_r]->rollback()) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_r[$this->host_idx_r]->error, $this->hosts_r[$this->host_idx_r]->errno);
		}
		if (!$this->hosts_w[$this->host_idx_w]->rollback()) {
			throw new Exception(__METHOD__ . " MySQLi error: " . $this->hosts_w[$this->host_idx_w]->error, $this->hosts_w[$this->host_idx_w]->errno);
		}
	}

	public function real_escape_string($str) {
		return $this->hosts_r[$this->host_idx_r]->real_escape_string($str);
	}

}
