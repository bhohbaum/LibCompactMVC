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
		if ($loglevel <= LOG_LEVEL) {
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

/*
 * The XLOG() functions can be used in all controller classes. PHP doesn't know c-like macros.
* hence we use the debug_backtrace() trick to get the callers object.
*/
function ELOG($msg) {
	$stack = debug_backtrace();
	if (@$stack[1]["object"]->log == null) {
		@$stack[1]["object"]->log = new Log(Log::LOG_TYPE_FILE);
		$stack[1]["object"]->log->set_log_file(LOG_FILE);
	}
	$stack[1]["object"]->log->error($msg);
}

function WLOG($msg) {
	$stack = debug_backtrace();
	if (@$stack[1]["object"]->log == null) {
		@$stack[1]["object"]->log = new Log(Log::LOG_TYPE_FILE);
		$stack[1]["object"]->log->set_log_file(LOG_FILE);
	}
	$stack[1]["object"]->log->warning($msg);
}

function NLOG($msg) {
	$stack = debug_backtrace();
	if (@$stack[1]["object"]->log == null) {
		@$stack[1]["object"]->log = new Log(Log::LOG_TYPE_FILE);
		$stack[1]["object"]->log->set_log_file(LOG_FILE);
	}
	$stack[1]["object"]->log->notice($msg);
}

function DLOG($msg) {
	$stack = debug_backtrace();
	if (@$stack[1]["object"]->log == null) {
		@$stack[1]["object"]->log = new Log(Log::LOG_TYPE_FILE);
		$stack[1]["object"]->log->set_log_file(LOG_FILE);
	}
	$stack[1]["object"]->log->debug($msg);
}

/**
 * prints the current stack trace
 */
function printStackTrace() {
	try {
		throw new Exception("", 0);
	} catch (Exception $e) {
		echo ("<pre>" . $e->getTraceAsString() . "</pre>");
	}
}

