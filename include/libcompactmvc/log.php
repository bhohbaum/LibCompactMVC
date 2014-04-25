<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Logger
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class Log {
	private $db;
	private $fname;
	private $logtype;
	const LOG_TYPE_DB = 0;
	const LOG_TYPE_FILE = 1;
	const LOG_LVL_ERROR = 0;
	const LOG_LVL_WARNING = 1;
	const LOG_LVL_NOTICE = 2;
	const LOG_LVL_DEBUG = 3;

	public function __construct($logtype) {
		$this->logtype = $logtype;
		date_default_timezone_set(DEFAULT_TIMEZONE);
	}

	public function set_log_file($fname) {
		$this->fname = $fname;
		return $this;
	}

	public function set_log_db(DbAccess $db) {
		$this->db = $db;
		return $this;
	}
	
	// general logging method
	public function log($loglevel, $text) {
		if ((($loglevel == Log::LOG_LVL_DEBUG) && (defined("DEBUG") && (DEBUG == 1))) || ($loglevel != Log::LOG_LVL_DEBUG)) {
			if ($this->logtype == Log::LOG_TYPE_DB) {
				$this->db->write2log($loglevel, date(DATE_ISO8601), $text);
			} else if ($this->logtype == Log::LOG_TYPE_FILE) {
				error_log($loglevel . " " . date(DATE_ISO8601) . " " . $text . "\n", 3, LOG_FILE);
			}
		}
	}
	
	// short methods
	public function error($text) {
		$this->log(Log::LOG_LVL_ERROR, $text);
	}

	public function warning($text) {
		$this->log(Log::LOG_LVL_WARNING, $text);
	}

	public function notice($text) {
		$this->log(Log::LOG_LVL_NOTICE, $text);
	}

	public function debug($text) {
		$this->log(Log::LOG_LVL_DEBUG, $text);
	}


}

?>