<?php

// ####################################################### ./include/libcompactmvc/functions.php ####################################################### \\


/**
 * Global functions
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */

/**
 * Compares two strings and returns the character position of the first difference.
 *
 * @param unknown_type $str1
 * @param unknown_type $str2
 * @param unknown_type $encoding
 */
function strdiff($str1, $str2, $encoding = 'UTF-8') {
	return mb_strlen(
		mb_strcut(
			$str1,
			0, strspn($str1 ^ $str2, "\0"),
			$encoding
		),
		$encoding
	);
}

/**
 * Converts unix to windows linebreaks
 *
 * @param string $string
 */
function cr2crlf($string) {
	$string = preg_replace('~\R~u', "\r\n", $string);
	return $string;
}

/**
 * Converts windows to unix linebreaks
 *
 * @param string $string
 */
function crlf2cr($string) {
	$string = preg_replace('~\R~u', "\n", $string);
	return $string;
}

/**
 * Filesystem helper
 */
function rrmdir($path, $ignore = array()) {
	DLOG();
	foreach ($ignore as $i) {
		if (pathinfo($path, PATHINFO_BASENAME) == $i) {
			DLOG(" " . $path . " is on ignore list, leaving it undeleted...\n");
			return;
		}
	}
	if (is_dir($path)) {
		$path = rtrim($path, '/') . '/';
		$items = glob($path . '*');
		foreach ($items as $item) {
			is_dir($item) ? rrmdir($item, $ignore) : unlink($item);
		}
		rmdir($path);
	} else {
		unlink($path);
	}
}

function rcopy($src, $dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while (false !== ($file = readdir($dir))) {
		if (($file != '.') && ($file != '..')) {
			if (is_dir($src . '/' . $file)) {
				rcopy($src . '/' . $file, $dst . '/' . $file);
			} else {
				copy($src . '/' . $file, $dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}

function is_windows() {
	DLOG();
	if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
		return true;
	} else {
		return false;
	}
}

function is_tls_con() {
	$ret = null;
	if (php_sapi_name() != "cli") {
		$ret = (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] != 'off') ? true : false;
	}
	return $ret;
}

/**
 * 
 * @param unknown $url
 * @return boolean
 */
function is_imgdataurl($url) {
	return str_contains($url, "data:image") && str_contains($url, ";base64,");
}

/**
 * 
 * @param unknown $url
 * @param unknown $data
 * @param unknown $type
 * @return boolean
 */
function imgdataurl_extract($url, &$data, &$type) {
	if (is_imgdataurl($url)) {
		$tmp = explode(";base64,", $url);
		$data = base64_decode($tmp[1]);
		$type = str_replace("data:image/", "", $tmp[0]);
		return true;
	}
	return false;
}

function mkpw($length = 9, $add_dashes = false, $available_sets = 'luds') {
	$sets = array();
	if (strpos($available_sets, 'l') !== false)
		$sets[] = 'abcdefghjkmnpqrstuvwxyz';
	if (strpos($available_sets, 'u') !== false)
		$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
	if (strpos($available_sets, 'd') !== false)
		$sets[] = '23456789';
	if (strpos($available_sets, 's') !== false)
		$sets[] = '!@#$%&*?';
	$all = '';
	$password = '';
	foreach ($sets as $set) {
		$password .= $set[array_rand(str_split($set))];
		$all .= $set;
	}
	$all = str_split($all);
	for($i = 0; $i < $length - count($sets); $i++)
		$password .= $all[array_rand($all)];
	$password = str_shuffle($password);
	if (!$add_dashes)
		return $password;
	$dash_len = floor(sqrt($length));
	$dash_str = '';
	while (strlen($password) > $dash_len) {
		$dash_str .= substr($password, 0, $dash_len) . '-';
		$password = substr($password, $dash_len);
	}
	$dash_str .= $password;
	return $dash_str;
}

function strip_tags_and_attributes($html, $tags, $attributes = array()) {
	// Get array representations of the safe tags and attributes:
	// Parse the HTML into a document object:
	$dom = new DOMDocument();
	$dom->loadHTML('<div>' . $html . '</div>');

	// Loop through all of the nodes:
	$stack = new SplStack();
	$stack->push($dom->documentElement);

	while ($stack->count() > 0) {
		// Get the next element for processing:
		$element = $stack->pop();

		// Add all the element's child nodes to the stack:
		foreach ($element->childNodes as $child) {
			if ($child instanceof DOMElement) {
				$stack->push($child);
			}
		}

		// And now, we do the filtering:
		if (in_array(strtolower($element->nodeName), $tags)) {
			// It's an unwanted tag; unwrap it:
			while ($element->hasChildNodes()) {
				$element->parentNode->insertBefore($element->firstChild, $element);
			}

			// Finally, delete the offending element:
			$element->parentNode->removeChild($element);
		} else {
			// The tag is safe; now filter its attributes:
			for($i = 0; $i < $element->attributes->length; $i++) {
				$attribute = $element->attributes->item($i);
				$name = strtolower($attribute->name);

				if (in_array($name, $attributes)) {
					// Found an unsafe attribute; remove it:
					$element->removeAttribute($attribute->name);
					$i--;
				}
			}
		}
	}

	$html = $dom->saveHTML();
	$start = strpos($html, '<div>');
	$end = strrpos($html, '</div>');

	return substr($html, $start + 5, $end - $start - 5);
}

function exec_bg($cmd) {
	$cmd .= " 2>&1 >/dev/null &";
	DLOG("COMMAND: " . $cmd);
	proc_close(proc_open($cmd, array(), $pipes));
}

function echo_flush($str) {
	echo ($str);
	@ob_flush();
	flush();
}

function file_extension($fname) {
	$arr = explode(".", basename($fname));
	$ext = $arr[count($arr) - 1];
	if ($ext == basename($fname))
		$ext = "";
// 	DLOG("Filename: " . $fname . " Extension: " . $ext);
	return $ext;
}

function file_name($fname) {
	$ext = file_extension($fname);
	$fname = substr($fname, 0, strlen($fname) - (($ext == "") ? strlen($ext) : strlen("." . $ext)));
	DLOG("Filename: " . $fname . " Extension: " . $ext);
	return $fname;
}

function cmvc_include($fname) {
	if (defined("COMBINED_CODE_LOADED")) return;
	if (function_exists("DLOG")) DLOG($fname);
	$basepath = dirname(dirname(__FILE__) . "../");

	$dirs_up = array(
			"./",
			"../",
			"../../",
			"../../../",
			"../../../../",
			"../../../../../"
	);

	// Put all directories into this array, where source files shall be included.
	// This function is intended to work from everywhere.
	$dirs_down = array(
			"./",
			"application/",
			"application/component/",
			"application/controller/",
			"application/dba/",
			"application/framework/",
			"include/",
			"include/libcompactmvc/"
	);

	foreach ($dirs_up as $u) {
		foreach ($dirs_down as $d) {
			// if directory of index.php or below
			$f = dirname($u . $d . $fname) . "/" . basename($u . $d . $fname);
			// and file exists
			if (file_exists($f)) {
				// include it once
				include_once ($f);
				return;
			}
		}
	}
}

function cmvc_include_dir($path, $ignore = array()) {
	if (defined("COMBINED_CODE_LOADED")) return;
	// DLOG($path);
	if (is_dir($path)) {
		$path = rtrim($path, '/') . '/';
		$items = glob($path . '*');
		foreach ($items as $item) {
			foreach ($ignore as $i) {
				if (pathinfo($item, PATHINFO_BASENAME) == $i) {
					if (function_exists("DLOG")) DLOG(" " . $item . " is on ignore list.");
					return;
				}
			}
			if (is_dir($item)) {
				cmvc_include_dir($item, $ignore);
			}
			if (strtolower(file_extension($item)) == "php") {
				if (function_exists("DLOG")) DLOG($item);
				require_once ($item);
			}
		}
	} else {
		throw new Exception("A directory must be given as first parameter.");
	}
}

function base_url() {
	$ret = ".";
	if (php_sapi_name() != "cli") {
		$ret = sprintf("%s://%s", isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http', $_SERVER['SERVER_NAME']);
	}
	return $ret;
}

function lnk($path0 = null, $path1 = null, $urltail = "", $lang = null) {
	return LinkBuilder::get_instance()->get_link(ActionDispatcher::get_action_mapper(), $path0, $path1, $urltail, $lang);
}

function lnk_by_route_id($route_id) {
	$arr = explode(".", $route_id);
	if (count($arr) == 2) {
		return LinkBuilder::get_instance()->get_link(ActionDispatcher::get_action_mapper(), $arr[1], null, null, $arr[0]);
	}
	if (count($arr) == 3) {
		return LinkBuilder::get_instance()->get_link(ActionDispatcher::get_action_mapper(), $arr[1], $arr[2], null, $arr[0]);
	}
}

function route_id($path0 = null, $path1 = null, $urltail = "", $lang = null) {
	if ($lang == null) $lang = InputProvider::get_instance()->get_var("lang");
	if ($path0 != null && $path1 == null) {
		return $lang . "." . $path0;
	} else if ($path0 != null && $path1 != null) {
		return $lang . "." . $path0 . "." . $path1;
	}
}

function uppercase($str) {
	$str = strtoupper($str);
	$str = str_replace("ä", "Ä", $str);
	$str = str_replace("ö", "Ö", $str);
	$str = str_replace("ü", "Ü", $str);
	return $str;
}

function uc($str) {
	return uppercase($str);
}

function lowercase($str) {
	$str = strtolower($str);
	$str = str_replace("Ä", "ä", $str);
	$str = str_replace("Ö", "ö", $str);
	$str = str_replace("Ü", "ü", $str);
	return $str;
}

function lc($str) {
	lowercase($str);
}

function str_contains($haystack, $needle) {
	if (strpos($haystack, $needle) !== false) {
		return true;
	}
	return false;
}

function tr($id, $language, $text = null) {
	if ($text == null) {
		if (array_key_exists("tr", $GLOBALS)) {
			return $GLOBALS["tr"][$id][$language];
		} else {
			$chr = new CachedHttpRequest();
			$url = BASE_URL . "/couchdb/" . TRANSLATION_DATABASE . "/" . $id;
			$res = $chr->get($url);
			if ($res === false || $res == null) {
				ELOG("TRANSLATION NOT FOUND: id='$id', language='$language'\n" . getStackTrace());
				return "";
			}
			$dec = json_decode($res);
			if (!is_object($dec)) {
				ELOG("ERRONEOUS CONTENT IN LANGUAGE CACHE: id='$id', language='$language', content='$res'\n" . getStackTrace());
				// we delete the erroneous entry here to fix the language cache content
				$chr->flush($url);
				return "";
			}
			return $dec->$language;
		}
	} else {
		$GLOBALS["tr"][$id][$language] = $text;
	}
}

function obj_sort_by_member(&$obj_arr, $member) {
	DLOG();
	$unsorted = true;
	while ($unsorted) {
		$unsorted = false;
		foreach ($obj_arr as $key => $val) {
			if (array_key_exists($key + 1, $obj_arr)) {
				if ($obj_arr[$key + 1]->{$member} < $obj_arr[$key]->{$member}) {
					$unsorted = true;
					$tmp = $obj_arr[$key];
					$obj_arr[$key] = $obj_arr[$key + 1];
					$obj_arr[$key + 1] = $tmp;
				}
			}
		}
	}
	return $obj_arr;
}

function sort_obj_by_member(&$obj_arr, $member) {
	obj_sort_by_member($obj_arr, $member);
}
	
function xor_crypt($key, $text) {
	$out = '';
	for($i=0; $i<strlen($text); )
	{
		for($j=0; ($j<strlen($key) && $i<strlen($text)); $j++,$i++)
		{
			$out .= $text{$i} ^ $key{$j};
		}
	}
	return $out;
}

$GLOBALS["SITEMAP"] = array();

/**
 * Callback function that builds the sitemap array.
 *
 * @param LinkProperty $lp
 */
function add_to_sitemap(LinkProperty $lp) {
	if ($lp->is_in_sitemap())
		$GLOBALS["SITEMAP"][] = $lp->get_path();
}

// MIME types
// This list may be completed with required entries
define('MIME_TYPE_HTML', 'text/html; charset=utf-8');
define('MIME_TYPE_CSV', 'text/csv; charset=utf-8');
define('MIME_TYPE_JS', 'application/javascript; charset=utf-8');
define('MIME_TYPE_JSON', 'application/json; charset=utf-8');
define('MIME_TYPE_JPG', 'image/jpg');
define('MIME_TYPE_JPEG', 'image/jpeg');
define('MIME_TYPE_PNG', 'image/png');
define('MIME_TYPE_GIF', 'image/gif');
define('MIME_TYPE_PDF', 'application/pdf');
define('MIME_TYPE_DDS', 'image/vnd-ms.dds');
define('MIME_TYPE_BINARY', 'application/binary');
define('MIME_TYPE_OCTET_STREAM', 'application/octet-stream');

// ####################################################### ./include/libcompactmvc/mysqlhost.php ####################################################### \\


/**
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
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
// ####################################################### ./include/config.php ####################################################### \\


/**
 * Application config
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 11.02.2014
 * @link		http://www.github.com/bhohbaum
 */

// Set global constants
// Debug messages & logging
// 0 off
// 1 on
if (gethostname() == 'vweb02') {
	define('DEBUG', 1);
	define('LOG_LEVEL', 3);
	define('LOG_TARGET', 1);
	define('LOG_TYPE', 0);
} else if (gethostname() == 'production') {
	define('DEBUG', 0);
	define('LOG_LEVEL', 0);
	define('LOG_TARGET', 2);
	define('LOG_TYPE', 1);
} else {
	define('DEBUG', 1);
	define('LOG_LEVEL', 3);
	define('LOG_TARGET', 1);
	define('LOG_TYPE', 0);
}

define('REDIS_HOST', '127.0.0.1');
define('REDIS_PORT', 6379);
define('REDIS_FILE_FALLBACK_DISABLED', false);

define('CACHING_ENABLED', DEBUG != 1 || true);
define('LANG_DEFAULT', 'app');

define('SMTP_SERVER', '127.0.0.1');
define('SMTP_USER', '');
define('SMTP_PASS', '');
define('SMTP_SENDER', 'test@libcompactmvc.local');
define('SMTP_SENDER_NAME', 'LibCompactMVC');
// Send method: MAIL (mail() function) or SMTP
define('MAIL_TRANS_TYPE', 'SMTP');
define('MAIL_DEBUG_ADDR', 'b.hohbaum@googlemail.com');

//setlocale(LC_ALL, "de_DE.UTF-8", "de_DE@euro", "de_DE", "de", "ge");
setlocale(LC_ALL, 0);

// WebSocket config
// WS server cluster config
define('WS_SRV_COUNT', 1);
define('ST_WS_SRV_IDX', 'ws_srv_idx');
if (gethostname() == 'vweb05') {
	$GLOBALS['WS_BASE_URL'] = array(
			((is_tls_con()) ? 'wss' : 'ws') . '://app.joe-nimble.com/ws/'
	);
} else {
	$GLOBALS['WS_BASE_URL'] = array(
			((is_tls_con()) ? 'wss' : 'ws') . '://benimble.kmundp.local/ws/'
	);
}

// populate controllers with POST/GET variables?
define('REGISTER_HTTP_VARS', true);

// Session
define('SESSION_INSECURE_COOKIE', true);
define('SESSION_DYNAMIC_ID_DISABLED', true);
define('SESSION_TIMEOUT', 1200);

// Active Sessions measuring
define('ACTIVESESSIONS_MIN_HITS', 60);
define('ACTIVESESSIONS_MAX_HITS', 180);
define('ACTIVESESSIONS_HIT_INCR', 20);

// Certificate validation in CachedHttpRequest?
define('SSL_VERIFYPEER', true);
define('SSL_VERIFYHOST', 2);

// Further constants to be set at installation time
define('UPLOAD_BASE_DIR', './files/upload');				// relative to $_SERVER['DOCUMENT_ROOT']
define('IMAGES_BASE_DIR', './files/images');				// relative to $_SERVER['DOCUMENT_ROOT']
define('TEMP_DIR', './files/temp');							// relative to $_SERVER['DOCUMENT_ROOT']
define('CSV_BASE_DIR', './files/csv');						// relative to $_SERVER['DOCUMENT_ROOT']
define('LOG_FILE', '/var/log/php/cmvc.log');				// relative to $_SERVER['DOCUMENT_ROOT']
define('LOG_IDENT', 'libcompactmvc');
define('LOG_FACILITY', 'local7');
if (gethostname() == 'libo') {
	define('BASE_URL', 'http://cmvc.kmundp.local');
} else if (gethostname() == 'develwebss') {
	define('BASE_URL', 'http://cmvc.kmundp.local');
}
define('DEFAULT_TIMEZONE', 'CET');

define('CAPTCHA_RES_PATH', "./include/resources"); // relative to $_SERVER['DOCUMENT_ROOT']
define('ST_CAPTCHA_SESS_VAR', "captcha");

// uncomment to use proxy
// define('PROXY_CONFIG', '');
// define('PROXY_PORT', 8080);

define('CEPH_CONF', './files/ceph/ceph.prod.conf');
define('CEPH_POOL', 'ceph');
define('CEPH_MAX_OBJ_SIZE', 64 * 1024 * 1024);

define('REDIS_KEY_PREFIX', 'CMVC_');
define('REDIS_KEY_RCACHE_PFX', 'RENDERCACHE_');
define('REDIS_KEY_RCACHE_TTL', 7200);
define('REDIS_KEY_TBLDESC_PFX', 'TBLDESC_');
define('REDIS_KEY_FKINFO_PFX', 'FKINFO_');
define('REDIS_KEY_TBLCACHE_PFX', 'TBLCACHE_');
define('REDIS_KEY_TBLCACHE_TTL', 720000);
define('REDIS_KEY_FIFOBUFF_PFX', 'FIFOBUFF_');
define('REDIS_KEY_FIFOBUFF_TTL', 10000);
define('REDIS_KEY_HTMLCACHE_PFX', 'HTMLCACHE_');
define('REDIS_KEY_HTMLCACHE_TTL', 10000);
define('REDIS_KEY_CACHEDHTTP_PFX', 'HTTPCACHE_');
define('REDIS_KEY_CACHEDHTTP_TTL', 10000);

// couchdb database
define('TRANSLATION_DATABASE', 'libcompactmvc');

// Session tokens
define('ST_USER_ID', 'user_id');

// DB Server:
// DB schema name for ORM learning
define('MYSQL_SCHEMA', 'libcompactmvc');
$GLOBALS['MYSQL_HOSTS'] = array(
		new MySQLHost("localhost", "root", "toor", MYSQL_SCHEMA, MySQLHost::SRV_TYPE_READWRITE)
);
$GLOBALS['MYSQL_NO_CACHING'] = array(
		TBLV_NEXT_RECEIVER,
		TBLV_SEND_LIST,
		TBLV_TODAYS_MAILINGS,
		TBLV_TRACKING_COMBINED,
		TBLP_TRACKING_OVERVIEW
);
define("DBA_DEFAULT_CLASS", "DBA");

define("FIREBASE_API_TOKEN", "AAAAg778THU:APA91bFRNSAc_54I3-INM-DS9Q3gg1-8ijLKzpUvY14r3hX5Wx2L38E3aCCnuqehIn2dTQMWfx4Hd7cLhaMwWU_f_M2RWkdn3qwv7myrPuGVhWKyfX0vUAoDy5VAaltUb1UJOYv5eowR");

define('TBL_APP_PFX', 'cmvc_');
define('TBL_EVENT_TYPES', 'event_types');
define('TBL_IMAGES', 'images');
define('TBL_MAILINGS', 'mailings');
define('TBL_MAILINGS_HAS_RECEIVERS', 'mailings_has_receivers');
define('TBL_MAILPARTS', 'mailparts');
define('TBL_MAILPART_TYPES', 'mailpart_types');
define('TBL_RECEIVERS', 'receivers');
define('TBL_TEXTS', 'texts');
define('TBL_TRACKING_EVENTS', 'tracking_events');
define('TBL_USER', 'user');

define('TBLV_NEXT_RECEIVER', 'next_receiver');
define('TBLV_SEND_LIST', 'send_list');
define('TBLV_TODAYS_MAILINGS', 'todays_mailings');
define('TBLV_TRACKING_COMBINED', 'tracking_combined');

define('TBLP_TRACKING_OVERVIEW', 'tracking_overview');
// ####################################################### ./include/libcompactmvc/singleton.php ####################################################### \\


/**
 * This class can be used as base class for all Singleton based constructs.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class Singleton {
	// keeps instance of the class
	protected static $instance;

	/**
	 * Protected constructor to prevent uncontrolled instantiation.
	 */
	protected function __construct() {
	}

	public function __destruct() {
	}
	
	// prevent cloning
	private function __clone() {
	}

	/**
	 *
	 * @return returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null, $j = null, $k = null, $l = null, $m = null, $n = null, $o = null, $p = null) {
		$name = get_called_class();
		if ((!isset(self::$instance)) || (!array_key_exists($name, self::$instance))) {
			self::$instance[$name] = new $name($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
		}
		return self::$instance[$name];
	}

}// ####################################################### ./include/libcompactmvc/log.php ####################################################### \\


/**
 * Logger
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Log extends Singleton {
	private $db;
	private $fname;
	private $logtarget;
	private $logtype;
	const LOG_TARGET_DB = 0;
	const LOG_TARGET_FILE = 1;
	const LOG_TARGET_SYSLOG = 2;
	const LOG_TYPE_MULTILINE = 0;
	const LOG_TYPE_SINGLELINE = 1;
	const LOG_LVL_ERROR = 0;
	const LOG_LVL_WARNING = 1;
	const LOG_LVL_NOTICE = 2;
	const LOG_LVL_DEBUG = 3;

	protected function __construct($logtarget, $logtype = Log::LOG_TYPE_MULTILINE) {
		$this->logtarget = $logtarget;
		date_default_timezone_set(DEFAULT_TIMEZONE);
		$this->logtype = $logtype;
		if ($this->logtype == Log::LOG_TARGET_SYSLOG) {
			if (!defined("LOG_IDENT") && !defined("LOG_FACILITY")) {
				throw new Exception("When Syslog is configured as log output, LOG_IDENT and LOG_FACILITY must be defined.", 500);
			}
			openlog(LOG_IDENT, LOG_ODELAY | LOG_PID, LOG_FACILITY);
		}
	}

	public function __destruct() {
		closelog();
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
		$text = ($this->logtype == Log::LOG_TYPE_SINGLELINE) ? str_replace("\n", " ", $text) : $text;
		if ($loglevel <= LOG_LEVEL) {
			if ($this->logtarget == Log::LOG_TARGET_DB) {
				$this->db->write2log($loglevel, date(DATE_ISO8601), $text);
			} else if ($this->logtarget == Log::LOG_TARGET_FILE) {
				error_log($loglevel . " " . date(DATE_ISO8601) . " " . $text . "\n", 3, LOG_FILE);
			} else if ($this->logtarget == Log::LOG_TARGET_SYSLOG) {
				$lvl = ($loglevel == Log::LOG_LVL_DEBUG) ? LOG_DEBUG : ($loglevel == Log::LOG_LVL_NOTICE) ? LOG_NOTICE : ($loglevel == Log::LOG_LVL_WARNING) ? LOG_WARNING : ($loglevel == Log::LOG_LVL_ERROR) ? LOG_ERR : 0;
				syslog($lvl, LOG_IDENT . " " . preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text));
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
 * PHP doesn't know c-like macros.
 * hence we use the debug_backtrace() trick to get the callers object.
 */
function ELOG($msg = "") {
	if (Log::LOG_LVL_ERROR > LOG_LEVEL)
		return;
	$stack = debug_backtrace();
	$class = (array_key_exists("class", $stack[1])) ? $stack[1]["class"] : "";
	Log::get_instance(LOG_TARGET, LOG_TYPE)->error($class . "::" . $stack[1]["function"] . " " . $msg);
}

function WLOG($msg = "") {
	if (Log::LOG_LVL_WARNING > LOG_LEVEL)
		return;
	$stack = debug_backtrace();
	$class = (array_key_exists("class", $stack[1])) ? $stack[1]["class"] : "";
	Log::get_instance(LOG_TARGET, LOG_TYPE)->warning($class . "::" . $stack[1]["function"] . " " . $msg);
}

function NLOG($msg = "") {
	if (Log::LOG_LVL_NOTICE > LOG_LEVEL)
		return;
	$stack = debug_backtrace();
	$class = (array_key_exists("class", $stack[1])) ? $stack[1]["class"] : "";
	Log::get_instance(LOG_TARGET, LOG_TYPE)->notice($class . "::" . $stack[1]["function"] . " " . $msg);
}

function DLOG($msg = "") {
	if (Log::LOG_LVL_DEBUG > LOG_LEVEL)
		return;
	$stack = debug_backtrace();
	$class = (array_key_exists("class", $stack[1])) ? $stack[1]["class"] : "";
	Log::get_instance(LOG_TARGET, LOG_TYPE)->debug($class . "::" . $stack[1]["function"] . " " . $msg);
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

/**
 * returns the current stack trace
 */
function getStackTrace() {
	try {
		throw new Exception("", 0);
	} catch (Exception $e) {
		return $e->getTraceAsString();
	}
}

// ####################################################### ./include/libcompactmvc/inputsanitizer.php ####################################################### \\


/**
 * Controller super class
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class InputSanitizer implements JsonSerializable {
	private static $members_initialized;
	private static $members_populated;
	protected static $request_data;
	protected static $request_data_raw;
	protected static $action_mapper;
	protected static $member_variables;

	protected function __construct(ActionMapper $mapper = null) {
		if ($mapper != null) {
			self::$action_mapper = $mapper;
		}
		$this->populate_members();
	}

	protected function request($var = null) {
		if (!isset(InputSanitizer::$request_data) || InputSanitizer::$request_data == null) {
			if (!isset(InputSanitizer::$request_data_raw) || InputSanitizer::$request_data_raw == null) {
				parse_str(file_get_contents('php://input'), $put_vars);
				InputSanitizer::$request_data_raw = $put_vars;
			}
			$data = array_merge($_REQUEST, InputSanitizer::$request_data_raw);
			InputSanitizer::$request_data = $data;
		} else {
			$data = InputSanitizer::$request_data;
		}
		$ret = ($var != null) ? ((array_key_exists($var, $data)) ? $data[$var] : null) : $data;
		if (array_key_exists($var, self::$member_variables)) {
			$ret = self::$member_variables[$var];
		}
		DLOG("(" . $var . ") return: " . var_export($ret, true));
		return $ret;
	}

	private function populate_members() {
		if (self::$members_populated === true) {
			return;
		}
		if (self::$members_initialized === true && !isset(self::$action_mapper)) {
			return;
		}
		InputSanitizer::$request_data = null;
		self::$member_variables = array();
		global $argv;
		if (REGISTER_HTTP_VARS) {
			DLOG("Registering variables...");
			if (php_sapi_name() == "cli") {
				if (is_array(@getenv())) {
					foreach (@getenv() as $var => $val) {
						self::$member_variables[$var] = self::get_remapped($var, $val);
					}
				}
				if (is_array($argv)) {
					for($i = 1; $i <= 10; $i++) {
						if (array_key_exists($i + 0, $argv)) {
							$var = "path" . ($i - 1);
							self::$member_variables[$var] = self::get_remapped($var, $argv[$i + 0]);
						}
					}
				}
			} else {
				foreach (array_keys($this->request(null)) as $key) {
					self::$member_variables[$key] = self::get_remapped($key, $this->request($key));
				}
			}
			if (!array_key_exists("lang", self::$member_variables)) self::$member_variables["lang"] = LANG_DEFAULT;
			self::$member_variables["lang"] = (self::$member_variables["lang"] == null) ? LANG_DEFAULT : self::$member_variables["lang"];
		} else {
			DLOG("Registering variables is DISABLED...");
		}
		self::$members_populated = isset(self::$action_mapper);
		self::$members_initialized = true;
		DLOG(print_r(self::$member_variables, true));
	}

	private static function get_remapped($var_name, $value) {
		DLOG("$var_name = $value");
		if (isset(self::$action_mapper)) {
			if ($var_name == "path0") {
				$res = self::$action_mapper->reverse_path0($value);
			} else if ($var_name == "path1") {
				$res = self::$action_mapper->reverse_path1($value);
			} else {
				$res = $value;
			}
		} else {
			DLOG("ActionMapper not set!!!");
			$res = $value;
		}
		DLOG("Remapped: $var_name = $res");
		return $res;
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @throws InvalidMemberException
	 */
	public function __get($var_name) {
		if ($var_name == null) {
			$stack = debug_backtrace();
			throw new InvalidArgumentException('Unable to access a variable without a name at ' . $stack[0]["file"] . '" on line ' . $stack[0]["line"]);
		}
		$this->populate_members();
		if (!is_array(self::$member_variables)) {
			$stack = debug_backtrace();
			throw new InvalidMemberException('Member not defined: ' . get_class($this) . '::' . $var_name . ' in "' . $stack[0]["file"] . '" on line ' . $stack[0]["line"]);
		}
		if (!array_key_exists($var_name, self::$member_variables)) {
			$stack = debug_backtrace();
			throw new InvalidMemberException('Member not defined: ' . get_class($this) . '::' . $var_name . ' in "' . $stack[0]["file"] . '" on line ' . $stack[0]["line"]);
		} else {
			$res = self::$member_variables[$var_name];
		}
		return $res;
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @param unknown_type $value
	 */
	public function __set($var_name, $value) {
		$this->populate_members();
		self::$member_variables[$var_name] = $value;
	}

	/**
	 */
	public function jsonSerialize() {
		$ret = array();
		$this->populate_members();
		foreach (self::$member_variables as $key => $val) {
			$ret[$key] = $this->__get($key);
		}
		return $ret;
	}

	public function set_actionmapper(ActionMapper $mapper) {
		DLOG();
		self::$action_mapper = $mapper;
		$this->populate_members();
	}
	
	public function update_input_var($var, $content) {
		DLOG();
		$this->populate_members();
		self::$member_variables[$var] = $content;
	}
	
	public function to_array() {
		DLOG();
		$this->populate_members();
		return self::$member_variables;
	}

}
// ####################################################### ./include/libcompactmvc/actionmapperinterface.php ####################################################### \\


/**
 * actionmapperinterface.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
interface ActionMapperInterface {

	/**
	 *
	 * @return String base URL
	 */
	public function get_base_url();

	/**
	 *
	 * @param String $path0
	 *        	path0 value
	 * @param String $path1
	 *        	path1 value
	 * @param String $urltail
	 *        	additional tail of URL
	 * @return String path of URL
	 */
	public function get_path($lang, $path0 = null, $path1 = null, $urltail = null);

}
// ####################################################### ./include/libcompactmvc/cmvccontroller.php ####################################################### \\


/**
 * Controller super class
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class CMVCController extends InputSanitizer {
	private $__ob;
	private static $__rbrc;
	private $__cmp_disp_base;
	private $__mime_type;
	private $__redirect;
	private $__caching;
	private $__base_param;
	private $__base_path;
	

	/**
	 *
	 * @var View $__view
	 */
	private $__view;

	/**
	 *
	 * @var DbAccess db
	 */
	private $__db;

	/**
	 */
	public function __construct() {
		DLOG();
		parent::__construct();
		$this->__view = new View();
		$this->__mime_type = MIME_TYPE_HTML;
		$this->__caching = CACHING_ENABLED;
	}

	/**
	 * Has to return the name of the DBA class.
	 * Overwrite this method if your controller requires a different DbAccess object from get_db().
	 *
	 * @return String
	 */
	protected function dba() {
		DLOG();
		return (defined("DBA_DEFAULT_CLASS")) ? DBA_DEFAULT_CLASS : "DbAccess";
	}

	protected function pre_run() {
		DLOG();
	}

	protected function pre_run_get() {
		DLOG();
	}

	protected function pre_run_post() {
		DLOG();
	}

	protected function pre_run_put() {
		DLOG();
	}

	protected function pre_run_delete() {
		DLOG();
	}

	protected function pre_run_exec() {
		DLOG();
	}

	protected function main_run() {
		DLOG();
	}

	protected function main_run_get() {
		DLOG();
	}

	protected function main_run_post() {
		DLOG();
	}

	protected function main_run_put() {
		DLOG();
	}

	protected function main_run_delete() {
		DLOG();
	}

	protected function main_run_exec() {
		DLOG();
	}

	protected function post_run() {
		DLOG();
	}

	protected function post_run_get() {
		DLOG();
	}

	protected function post_run_post() {
		DLOG();
	}

	protected function post_run_put() {
		DLOG();
	}

	protected function post_run_delete() {
		DLOG();
	}

	protected function post_run_exec() {
		DLOG();
	}

	/**
	 * Exception handler
	 *
	 * @param Exception $e
	 */
	protected function exception_handler(Exception $e) {
		DLOG(get_class($e));
		throw $e;
	}

	/**
	 */
	protected function get_raw_input() {
		return CMVCController::$request_data_raw;
	}

	/**
	 */
	protected function get_http_verb() {
		if (php_sapi_name() == "cli") {
			$method = (getenv("METHOD") !== false) ? getenv("METHOD") : "exec";
		} else {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		$method = strtoupper($method);
		DLOG($method);
		return $method;
	}

	/**
	 *
	 * @param Object $obj
	 */
	protected function json_response($obj) {
		$json = UTF8::encode(json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		$this->__mime_type = MIME_TYPE_JSON;
		$this->__view->clear();
		$this->__view->add_template("__out.tpl");
		$this->__view->set_value("out", $json);
	}

	/**
	 *
	 * @param unknown_type $obj
	 */
	protected function binary_response($obj, $mime = MIME_TYPE_OCTET_STREAM) {
		DLOG();
		$this->__mime_type = $mime;
		$this->__view->clear();
		$this->__view->add_template("__out.tpl");
		$this->__view->set_value("out", $obj);
	}

	/**
	 * Shorthand method to return the dispatched components output.
	 *
	 * @return Boolean true if a matching component was found, false otherwise.
	 */
	protected function component_response() {
		DLOG();
		if ($this->get_dispatched_component() != null) {
			$this->set_caching(false);
			$this->binary_response($this->get_dispatched_component()->get_ob(), $this->get_dispatched_component()->get_mime_type());
			return true;
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param unknown_type $code
	 * @throws Exception
	 */
	protected function response_code($code = null) {
		DLOG("(" . $code . ")");
		if (function_exists('http_response_code')) {
			$code = http_response_code($code);
		} else {
			if ($code !== null) {
				switch ($code) {
					case 100:
						$text = 'Continue';
						break;
					case 101:
						$text = 'Switching Protocols';
						break;
					case 200:
						$text = 'OK';
						break;
					case 201:
						$text = 'Created';
						break;
					case 202:
						$text = 'Accepted';
						break;
					case 203:
						$text = 'Non-Authoritative Information';
						break;
					case 204:
						$text = 'No Content';
						break;
					case 205:
						$text = 'Reset Content';
						break;
					case 206:
						$text = 'Partial Content';
						break;
					case 300:
						$text = 'Multiple Choices';
						break;
					case 301:
						$text = 'Moved Permanently';
						break;
					case 302:
						$text = 'Moved Temporarily';
						break;
					case 303:
						$text = 'See Other';
						break;
					case 304:
						$text = 'Not Modified';
						break;
					case 305:
						$text = 'Use Proxy';
						break;
					case 400:
						$text = 'Bad Request';
						break;
					case 401:
						$text = 'Unauthorized';
						break;
					case 402:
						$text = 'Payment Required';
						break;
					case 403:
						$text = 'Forbidden';
						break;
					case 404:
						$text = 'Not Found';
						break;
					case 405:
						$text = 'Method Not Allowed';
						break;
					case 406:
						$text = 'Not Acceptable';
						break;
					case 407:
						$text = 'Proxy Authentication Required';
						break;
					case 408:
						$text = 'Request Time-out';
						break;
					case 409:
						$text = 'Conflict';
						break;
					case 410:
						$text = 'Gone';
						break;
					case 411:
						$text = 'Length Required';
						break;
					case 412:
						$text = 'Precondition Failed';
						break;
					case 413:
						$text = 'Request Entity Too Large';
						break;
					case 414:
						$text = 'Request-URI Too Large';
						break;
					case 415:
						$text = 'Unsupported Media Type';
						break;
					case 500:
						$text = 'Internal Server Error';
						break;
					case 501:
						$text = 'Not Implemented';
						break;
					case 502:
						$text = 'Bad Gateway';
						break;
					case 503:
						$text = 'Service Unavailable';
						break;
					case 504:
						$text = 'Gateway Time-out';
						break;
					case 505:
						$text = 'HTTP Version not supported';
						break;
					default :
						throw new Exception('Unknown http status code "' . htmlentities($code) . '"', $code);
						break;
				}
				$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
				header($protocol . ' ' . $code . ' ' . $text);
				$GLOBALS['http_response_code'] = $code;
			} else {
				$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
			}
		}
		return $code;
	}

	/**
	 *
	 * @param unknown_type $observe_headers
	 * @throws RBRCException
	 */
	protected function rbrc($observe_headers = true) {
		DLOG();
		self::$__rbrc = RBRC::get_instance($this->request(), $observe_headers);
		if (self::$__rbrc->get()) {
			$this->__view->clear();
			$this->__view->add_template("__out.tpl");
			$this->__view->set_value("out", self::$__rbrc->get());
			$this->__ob = $this->__view->render($this->__caching);
			throw new RBRCException();
		}
	}

	/**
	 * Proxy method to $this->get_view()->set_component($key, CMVCController $component).
	 * Required to use one component multiple times.
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	protected function set_component($key, CMVCComponent $component) {
		DLOG("(" . $key . ", " . get_class($component) . ")");
		$this->__view->set_component($key, $component);
	}

	/**
	 * Proxy method to $this->get_view()->set_component($key, CMVCController $component).
	 * Associates the component with its own ID.
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	protected function add_component(CMVCComponent $component) {
		DLOG(get_class($component));
		$this->__view->set_component($component->get_component_id(), $component);
	}

	/**
	 * Sets string for component selection.
	 *
	 * @param String $base
	 */
	protected function set_component_dispatch_base($base) {
		DLOG($base);
		$this->__cmp_disp_base = $base;
	}

	/**
	 * Input-controlled component dispatcher.
	 *
	 * @param CMVCComponent $component
	 *        	Component to be dispatched.
	 */
	protected function dispatch_component(CMVCComponent $component) {
		DLOG(get_class($component));
		if ($this->__cmp_disp_base == $component->get_component_id())
			$this->add_component($component);
	}

	/**
	 * Get the component object that was selected / will be selected based on the component dispatch base.
	 *
	 * @return CMVCComponent the dispatched component object
	 */
	protected function get_dispatched_component() {
		DLOG();
		return $this->__view->get_component($this->__cmp_disp_base);
	}

	/**
	 * Call methods based on the input that is provided.
	 *
	 * @param String $var
	 * @return Boolean true if a matching method was found, false otherwise.
	 */
	protected function dispatch_method($var) {
		DLOG($var);
		$method = strtolower($this->get_http_verb());
		$func = $method . "_" . $var;
		if (is_callable(array(
				$this,
				$func
		))) {
			$this->$func();
			return true;
		}
		if (is_callable(array(
				$this,
				$var
		))) {
			$this->$var();
			return true;
		}
		return false;
	}

	/**
	 * Executes controller methods depending on request type.
	 */
	public function run() {
		DLOG();
		DLOG(var_export($_REQUEST, true));
		$this->__redirect = "";
		$this->__db = DbAccess::get_instance($this->dba());
		if (!isset($this->__view)) {
			$this->__view = new View();
		}
		try {
			switch ($this->get_http_verb()) {
				case 'GET':
					$this->pre_run_get();
					break;
				case 'POST':
					$this->pre_run_post();
					break;
				case 'PUT':
					$this->pre_run_put();
					break;
				case 'DELETE':
					$this->pre_run_delete();
					break;
				case 'EXEC':
					$this->pre_run_exec();
					break;
			}
			$this->pre_run();
			switch ($this->get_http_verb()) {
				case 'GET':
					$this->main_run_get();
					break;
				case 'POST':
					$this->main_run_post();
					break;
				case 'PUT':
					$this->main_run_put();
					break;
				case 'DELETE':
					$this->main_run_delete();
					break;
				case 'EXEC':
					$this->main_run_exec();
					break;
			}
			$this->main_run();
			switch ($this->get_http_verb()) {
				case 'GET':
					$this->post_run_get();
					break;
				case 'POST':
					$this->post_run_post();
					break;
				case 'PUT':
					$this->post_run_put();
					break;
				case 'DELETE':
					$this->post_run_delete();
					break;
				case 'EXEC':
					$this->post_run_exec();
					break;
			}
			$this->post_run();
		} catch (Exception $e) {
			$this->run_exception_handler($e);
		}

		// If we have a redirect, we don't want the current template(s) to be generated.
		if ($this->__redirect == "") {
			$this->__ob = $this->__view->render($this->__caching);
			if (isset(self::$__rbrc)) {
				self::$__rbrc->put($this->__ob);
			}
		}
	}

	/**
	 * Run the exception handler method
	 *
	 * @param Exception $e
	 *        	the exception
	 */
	private function run_exception_handler($e) {
		DLOG("Exception " . $e->getCode() . " '" . $e->getMessage() . "'");
		if ($e instanceof RedirectException) {
			$this->response_code(is_numeric($e->getCode()) ? $e->getCode() : 301);
			if ($e->is_internal()) {
				$this->__redirect = $e->getMessage();
				DLOG("INTERNAL REDIRECT: " . $this->__redirect);
			} else {
				header("Location: " . $e->getMessage());
			}
			throw $e;
		} else {
			try {
				$this->response_code(is_numeric($e->getCode()) ? $e->getCode() : 500);
				$this->exception_handler($e);
			} catch (RedirectException $e0) {
				if ($e0->is_internal()) {
					$this->__redirect = $e0->getMessage();
					DLOG("INTERNAL REDIRECT: " . $this->__redirect);
				} else {
					header("Location: " . $e0->getMessage());
				}
				throw $e0;
			} catch (Exception $e1) {
				$this->__ob = $this->__view->render($this->__caching);
				throw $e1;
			}
			$this->__ob = $this->__view->render($this->__caching);
		}
	}

	/**
	 * Returns the output buffer of the current controller
	 *
	 * @return String Rendered content
	 */
	public function get_ob() {
		DLOG();
		return $this->__ob;
	}

	/**
	 *
	 * @param String $mime_type
	 *        	the mime type of the current controllers output.
	 */
	protected function set_mime_type($mime_type) {
		DLOG($mime_type);
		$this->__mime_type = $mime_type;
	}
	
	protected function set_caching($caching = CACHING_ENABLED) {
		DLOG($caching);
		$this->__caching = $caching;
	}

	protected function get_db() {
		DLOG();
		return $this->__db;
	}

	/**
	 */
	public function get_mime_type() {
		DLOG("Return: " . $this->__mime_type);
		return $this->__mime_type;
	}

	public function get_view() {
		DLOG();
		return $this->__view;
	}

	public function get_redirect() {
		DLOG();
		return $this->__redirect;
	}
	
	/**
	 *
	 * @param int $pnum Set the param position
	 */
	public function set_base_path($pnum) {
		DLOG($pnum);
		if (!is_int($pnum))
			throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		$this->__base_path = $pnum;
	}
	
	/**
	 *
	 * @return int The URL path depth the controller is located in (based on routing)
	 */
	protected function get_base_path() {
		DLOG();
		return $this->__base_path;
	}
	
	

}
// ####################################################### ./include/libcompactmvc/cmvccomponent.php ####################################################### \\


/**
 * Controller super class
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class CMVCComponent extends CMVCController {
	private $__instance_id;
	private $__run_executed = false;

	/**
	 * Has to be implemented by every subclass. The output of the component (in the view) is identified by this string.
	 *
	 * @return String Component identification string
	 */
	abstract public function get_component_id();

	/**
	 *
	 * @return String Unique component id string, for distinguishing multiple instances within one request.
	 */
	protected function get_component_instance_id() {
		DLOG();
		return $this->__instance_id;
	}

	/**
	 *
	 * @param int $base_path
	 */
	public function __construct($base_path = null) {
		DLOG();
		parent::__construct();
		if (!is_int($base_path) && $base_path != null)
			throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		if ($base_path != null) $this->set_base_path($base_path);
		$this->__instance_id = uniqid();
		$this->get_view()->set_value("CMP_INST_ID", $this->__instance_id);
		$this->get_view()->set_value("CMP_ID", $this->get_component_id());
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see CMVCController::run()
	 */
	public function run() {
		DLOG();
		$this->__run_executed = true;
		parent::run();
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see CMVCController::get_ob()
	 */
	public function get_ob() {
		DLOG();
		if (!$this->__run_executed)
			$this->run();
		return parent::get_ob();
	}

	/**
	 *
	 * @param int $pnum
	 */
	protected function path($pnum) {
		if (!is_int($pnum))
			throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		$varname = 'path' . ($this->get_base_path() + $pnum);
		if (!array_key_exists($varname, self::$member_variables))
			throw new InvalidMemberException("Invalid member: " . $varname);
		$val = self::$member_variables[$varname];
		DLOG($varname . " = " . $val);
		return $val;
	}

}
// ####################################################### ./include/libcompactmvc/dbaccess.php ####################################################### \\


/**
 * this class handles our DB connection and requests
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class DbAccess {
	// keeps instance of the class
	private static $instance;
	protected static $mysqli;

	protected function __construct() {
		$this->open_db();
	}

	public function __destruct() {
		// Do not close the DB, as other objects might still need a connection.
		// $this->close_db();
	}

	// prevent cloning
	private function __clone() {
		DLOG();
	}

	/**
	 *
	 * @return DbAccess the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($name) {
		DLOG();
		if ((!isset(self::$instance)) || (!array_key_exists($name, self::$instance))) {
			if (($name == null) || ($name == "")) {
				$name = get_class($this);
			}
			if (!is_subclass_of($name, "DbAccess"))
				throw new DBException("Class must be a subclass of DbAccess.", 500);
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
		self::$mysqli = MySQLAdapter::get_instance($GLOBALS['MYSQL_HOSTS']);
	}

	/**
	 */
	protected function close_db() {
		DLOG();
		if (self::$mysqli != null) {
			self::$mysqli->close();
			self::$mysqli = null;
		}
	}

	/**
	 * Execute a DB query.
	 *
	 * @param String $query
	 *        	The query to execute.
	 * @param Boolean $has_multi_result
	 *        	Is one object expected as result, or a list?
	 * @param Boolean $object
	 *        	Return as array or as object
	 * @param String $field
	 *        	Columnname if a single value shall be returned.
	 * @param String $table
	 *        	Name of the table that is operated on.
	 * @param Boolean $is_write_access
	 *        	Set to true when issuing a write query.
	 * @throws Exception
	 * @return Ambigous <multitype:, NULL>
	 */
	protected function run_query($query, $has_multi_result = false, $object = false, $field = null, $table = null, $is_write_access = true) {
		DLOG($query);
		$ret = null;
		$typed_object = $table != null && class_exists($table) && is_subclass_of(new $table(), "DbObject");
		$key = REDIS_KEY_TBLCACHE_PFX . $table . "_" . $is_write_access . "_" . $field . "_" . $object . "_" . $has_multi_result . "_" . md5($query);
		$object = ($field == null) ? $object : false;
		if (array_search($table, $GLOBALS['MYSQL_NO_CACHING']) === false) {
			if ($is_write_access) {
				$delkey = REDIS_KEY_TBLCACHE_PFX;
				$delkey .= ($table == null) ? "*" : $table . "*";
				$keys = RedisAdapter::get_instance()->keys($delkey);
				foreach ($keys as $k) {
					RedisAdapter::get_instance()->delete($k);
				}
			} else {
				$res = RedisAdapter::get_instance()->get($key);
				if ($res !== false) {
					RedisAdapter::get_instance()->expire($key, REDIS_KEY_TBLCACHE_TTL);
					DLOG("Query was cached!!!");
					return unserialize($res);
				}
			}
		}
		$result = self::$mysqli->query($query, $is_write_access, $table);
		if ($result === false) {
			throw new DBException("Query \"$query\" caused an error: " . self::$mysqli->get_error(), self::$mysqli->get_errno());
		} else {
			if (is_object($result)) {
				if ($has_multi_result) {
					if ($object) {
						while ($row = $result->fetch_assoc()) {
							if ($typed_object) {
								$tmp = new $table($row, false);
							} else {
								$tmp = new DbObject($row, false);
								if ($table != null) {
									$tmp->table($table);
								}
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
					$num_loaded = 0;
					if ($object) {
						while ($row = $result->fetch_assoc()) {
							if ($num_loaded >= 1) throw new MultipleResultsException("Query has multiple results, but only one result was requested.", 500);
							if ($typed_object) {
								$tmp = new $table($row, false);
							} else {
								$tmp = new DbObject($row, false);
								if ($table != null) {
									$tmp->table($table);
								}
							}
							$num_loaded++;
							$ret = $tmp;
						}
					} else {
						while ($row = $result->fetch_assoc()) {
							if ($num_loaded >= 1) throw new MultipleResultsException("Query has multiple results, but only one result was requested.", 500);
							if ($field != null) {
								$ret = $row[$field];
							} else {
								$ret = $row;
							}
							$num_loaded++;
						}
					}
				}
				$result->close();
			} else {
				$ret = self::$mysqli->get_insert_id();
			}
		}
		if (($ret == null) && ($has_multi_result == true)) {
			$ret = array();
		}
		if (array_search($table, $GLOBALS['MYSQL_NO_CACHING']) === false) {
			if (!$is_write_access) {
				RedisAdapter::get_instance()->set($key, serialize($ret));
				RedisAdapter::get_instance()->expire($key, REDIS_KEY_TBLCACHE_TTL);
			}
		}
		DLOG("Query was NOT cached!!!");
		return $ret;
	}

	/**
	 *
	 * @param String $tablename
	 * @param array $constraint
	 */
	public function by_table($tablename, $constraint = null, $wildcard = false) {
		$qb = new QueryBuilder();
		$constraint = ($constraint == null) ? array() : $constraint;
		if ($wildcard)
			$q = $qb->like($tablename, $constraint);
		else
			$q = $qb->select($tablename, $constraint);
		if (is_object($constraint) && get_class($constraint) == "DbConstraint" && $constraint->count)
			$res = $this->run_query($q, false, true, "count", $tablename, false);
		else
			$res = $this->run_query($q, true, true, null, $tablename, false);
		return $res;
	}

	/**
	 *
	 * @param unknown_type $mode
	 * @throws Exception
	 */
	public function autocommit($mode) {
		DLOG();
		self::$mysqli->autocommit($mode);
	}

	/**
	 *
	 * @throws Exception
	 */
	public function begin_transaction() {
		DLOG();
		self::$mysqli->begin_transaction();
	}

	/**
	 *
	 * @throws Exception
	 */
	public function commit() {
		DLOG();
		self::$mysqli->commit();
	}

	/**
	 *
	 * @throws Exception
	 */
	public function rollback() {
		DLOG();
		self::$mysqli->rollback();
	}

	/**
	 *
	 * @param unknown $str
	 * @throws Exception
	 */
	protected function escape($str) {
		// we don't DLOG here, it's spaming...
		// DLOG();
		if (self::$mysqli) {
			return self::$mysqli->real_escape_string($str);
		}
		throw new Exception("DbAccess::mysqli is not initialized, unable to escape string.");
	}

	/**
	 * Use this method for values that can be null, when building the SQL query.
	 * Refrain from surrounding this return value with "'", as they are automatically added to string values!
	 *
	 * @param
	 *        	String_or_Number input value that has to be transformed
	 * @return String value to concatenate with the rest of the sql query
	 */
	protected function sqlnull($var, $wildcard = false) {
		// we don't DLOG here, it's spaming...
		// DLOG();
		$leadingzero = substr($var, 0, 1) == "0";
		$leadingplus = substr($var, 0, 1) == "+";
		$iszero = ($var === "0");
		if ($iszero || (is_numeric($var) && !$leadingzero && !$leadingplus)) {
			$var = (empty($var) && !is_numeric($var)) ? "NULL" : ($wildcard ? "'%" : "") . $var . ($wildcard ? "%'" : "");
		} else {
			$var = (empty($var) && !is_numeric($var)) ? "NULL" : "'" . ($wildcard ? "%" : "") . $var . ($wildcard ? "%" : "") . "'";
		}
		return $var;
	}

	/**
	 *
	 * @param unknown $var value that needs to be compared
	 * @return string comparison operator
	 */
	protected function cmpissqlnull($var) {
		if ($this->sqlnull($var) == "NULL") {
			return "IS";
		} else {
			return "=";
		}
	}
	
	/**
	 *
	 * @param unknown $var value that needs to be compared
	 * @return string comparison operator
	 */
	protected function cmpisnotsqlnull($var) {
		if ($this->sqlnull($var) == "NULL") {
			return "IS NOT";
		} else {
			return "!=";
		}
	}
	
}
// ####################################################### ./include/libcompactmvc/dbfilter.php ####################################################### \\


/**
 * Query filter definition.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DbFilter extends DbAccess implements JsonSerializable {
	private $qb;
	private $parent;					// parent element
	
	protected $filter = array();
	protected $comparator = array();
	protected $logic_op = array();
	protected $constraint = array();
	
	const LOGIC_OPERATOR_AND = "AND";
	const LOGIC_OPERATOR_OR = "OR";
	const LOGIC_OPERATOR_XOR = "XOR";
	const LOGIC_OPERATOR_NOT = "NOT";
	
	const COMPARE_EQUAL = "=";
	const COMPARE_NOT_EQUAL = "!=";
	const COMPARE_LIKE = "LIKE";
	const COMPARE_NOT_LIKE = "NOT LIKE";
	const COMPARE_GREATER_THAN = ">";
	const COMPARE_LESS_THAN = "<";
	const COMPARE_GREATER_EQUAL_THAN = ">=";
	const COMPARE_LESS_EQUAL_THAN = "<=";
	const COMPARE_IN = "IN";
	const COMPARE_NOT_IN = "NOT IN";
	
	/**
	 * 
	 * @param array $constraint
	 */
	public function __construct(array $constraint = array()) {
		DLOG(print_r($constraint, true));
		$this->constraint = $constraint;
		$this->filter = array();
		$this->comparator = DbFilter::COMPARE_EQUAL;
		$this->logic_op = DbFilter::LOGIC_OPERATOR_AND;
		$this->qb = new QueryBuilder();
	}
	
	/**
	 * 
	 * @param DbFilter $filter
	 * @return DbFilter
	 */
	public function add_filter(DbFilter $filter) {
		DLOG();
		$filter->set_parent($this);
		$this->filter[] = $filter;
		return $this;
	}

	protected function set_parent(DbFilter $parent) {
		$this->parent = $parent;
		return $this;
	}
	
	public function get_table() {
		$filter = $this;
		while (get_class($filter) != "DbConstraint") {
			$filter = $filter->parent;
		}
		$dto = $filter->get_dto();
		$table = $dto->get_table();
		return $table;
	}
	
	/**
	 * 
	 * @param unknown $column
	 * @param unknown $value
	 * @return DbFilter
	 */
	public function set_column_filter($column, $value) {
		DLOG();
		$this->constraint[$column] = $value;
		return $this;
	}
	
	/**
	 * 
	 * @param unknown $logic_op
	 * @return DbFilter
	 */
	public function set_logical_operator($logic_op) {
		DLOG();
		$this->logic_op = $logic_op;
		return $this;
	}
	
	/**
	 * 
	 * @param unknown $comparator
	 * @return DbFilter
	 */
	public function set_comparator($comparator) {
		DLOG();
		$this->comparator = $comparator;
		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_query_substring() {
		DLOG();
		return $this->qb->where_substring($this->get_table(), $this->constraint, $this->filter, $this->comparator, $this->logic_op);
	}

	/**
	 */
	public function jsonSerialize() {
		$base = array();
		$base["filter"] = $this->filter;
		$base["comparator"] = $this->comparator;
		$base["logic_op"] = $this->logic_op;
		$base["constraint"] = $this->constraint;
		$base["__type"] = get_class($this);
		return json_encode($base, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * DbFilter factory
	 * 
	 * @param string $json
	 * @return DbFilter
	 */
	public static function create_from_json($json) {
		$tmp = json_decode($json, true);
		if (array_key_exists("__type", $tmp)) {
			if (class_exists($tmp["__type"])) {
				if ($tmp["__type"] == "DbConstraint" || $tmp["__type"] == "DbFilter") {
					$tmpobj = json_decode($json, false);
					$ret = new DbFilter();
					foreach ($tmpobj->filter as $filter) {
						$f = DbFilter::create_from_json(json_encode($filter));
						if ($f != null) $ret->add_filter($f);
					}
					$ret->comparator = $tmpobj->comparator;
					$ret->logic_op = $tmpobj->logic_op;
					$ret->constraint = $tmpobj->constraint;
				}
			}
		}
		return $ret;
	}
	
}

// ####################################################### ./include/jwt/emarref/jwt/include.php ####################################################### \\




cmvc_include("./include/jwt/emarref/jwt/src/Algorithm/AlgorithmInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Algorithm/SymmetricInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Algorithm/AsymmetricInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Algorithm/RsaSsaPss.php");
cmvc_include("./include/jwt/emarref/jwt/src/Algorithm/RsaSsaPkcs.php");
cmvc_include("./include/jwt/emarref/jwt/src/Token/PropertyInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Claim/ClaimInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/FactoryTrait.php");
cmvc_include("./include/jwt/emarref/jwt/src/Encoding/EncoderInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Encryption/AbstractEncryption.php");
cmvc_include("./include/jwt/emarref/jwt/src/Encryption/EncryptionInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/HeaderParameter/ParameterInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Serialization/SerializerInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Signature/SignerInterface.php");
cmvc_include("./include/jwt/emarref/jwt/src/Verification/VerifierInterface.php");

// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/AlgorithmInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/AlgorithmInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/SymmetricInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/SymmetricInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/AsymmetricInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/AsymmetricInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/RsaSsaPss.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/RsaSsaPss.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/RsaSsaPkcs.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/RsaSsaPkcs.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Token/PropertyInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Token/PropertyInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/ClaimInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/ClaimInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/FactoryTrait.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/FactoryTrait.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Encoding/EncoderInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Encoding/EncoderInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Encryption/AbstractEncryption.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Encryption/AbstractEncryption.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Encryption/EncryptionInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Encryption/EncryptionInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/ParameterInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/ParameterInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Serialization/SerializerInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Serialization/SerializerInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Signature/SignerInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Signature/SignerInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/VerifierInterface.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/VerifierInterface.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/EcdSa.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/EcdSa.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Es256.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Es256.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Es384.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Es384.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Es512.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Es512.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Hmac.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Hmac.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Hs256.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Hs256.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Hs384.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Hs384.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Hs512.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Hs512.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/None.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/None.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Ps256.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Ps256.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Ps384.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Ps384.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Ps512.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Ps512.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Rs256.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Rs256.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Rs384.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Rs384.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Algorithm/Rs512.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Algorithm/Rs512.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/AbstractClaim.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/AbstractClaim.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/Audience.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/Audience.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/DateValueClaim.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/DateValueClaim.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/Expiration.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/Expiration.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/Factory.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/Factory.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/IssuedAt.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/IssuedAt.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/Issuer.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/Issuer.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/JwtId.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/JwtId.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/NotBefore.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/NotBefore.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/PrivateClaim.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/PrivateClaim.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/PublicClaim.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/PublicClaim.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Claim/Subject.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Claim/Subject.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Encoding/Base64.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Encoding/Base64.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Encryption/Asymmetric.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Encryption/Asymmetric.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Encryption/Factory.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Encryption/Factory.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Encryption/Symmetric.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Encryption/Symmetric.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Exception/VerificationException.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Exception/VerificationException.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/AbstractParameter.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/AbstractParameter.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/Algorithm.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/Algorithm.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/ContentType.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/ContentType.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/Critical.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/Critical.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/Custom.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/Custom.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/Factory.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/Factory.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/JsonWebKey.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/JsonWebKey.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/JwkSetUrl.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/JwkSetUrl.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/KeyId.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/KeyId.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/Type.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/Type.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/X509CertificateChain.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/X509CertificateChain.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/X509CertificateSha1Thumbprint.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/X509CertificateSha1Thumbprint.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/X509CertificateSha256Thumbprint.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/X509CertificateSha256Thumbprint.php');


// ####################################################### ./include/jwt/emarref/jwt/src/HeaderParameter/X509Url.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/HeaderParameter/X509Url.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Jwt.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Jwt.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Serialization/Compact.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Serialization/Compact.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Signature/Jws.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Signature/Jws.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Token/Header.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Token/Header.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Token/Payload.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Token/Payload.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Token/PropertyList.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Token/PropertyList.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Token.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Token.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/AudienceVerifier.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/AudienceVerifier.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/Context.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/Context.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/EncryptionVerifier.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/EncryptionVerifier.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/ExpirationVerifier.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/ExpirationVerifier.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/IssuerVerifier.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/IssuerVerifier.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/NotBeforeVerifier.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/NotBeforeVerifier.php');


// ####################################################### ./include/jwt/emarref/jwt/src/Verification/SubjectVerifier.php ####################################################### \\

require_once('./include/jwt/emarref/jwt/src/Verification/SubjectVerifier.php');


// ####################################################### ./include/ApnsPHP/Abstract.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Abstract class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * @mainpage
 *
 * @li ApnsPHP on GitHub: https://github.com/immobiliare/ApnsPHP
 */

/**
 * @defgroup ApplePushNotificationService ApnsPHP
 */

/**
 * Abstract class: this is the superclass for all Apple Push Notification Service
 * classes.
 *
 * This class is responsible for the connection to the Apple Push Notification Service
 * and Feedback.
 *
 * @ingroup ApplePushNotificationService
 * @see http://tinyurl.com/ApplePushNotificationService
 */
abstract class ApnsPHP_Abstract
{
	const ENVIRONMENT_PRODUCTION = 0; /**< @type integer Production environment. */
	const ENVIRONMENT_SANDBOX = 1; /**< @type integer Sandbox environment. */

	const DEVICE_BINARY_SIZE = 32; /**< @type integer Device token length. */

	const WRITE_INTERVAL = 10000; /**< @type integer Default write interval in micro seconds. */
	const CONNECT_RETRY_INTERVAL = 1000000; /**< @type integer Default connect retry interval in micro seconds. */
	const SOCKET_SELECT_TIMEOUT = 1000000; /**< @type integer Default socket select timeout in micro seconds. */

	protected $_aServiceURLs = array(); /**< @type array Container for service URLs environments. */

	protected $_nEnvironment; /**< @type integer Active environment. */

	protected $_nConnectTimeout; /**< @type integer Connect timeout in seconds. */
	protected $_nConnectRetryTimes = 3; /**< @type integer Connect retry times. */

	protected $_sProviderCertificateFile; /**< @type string Provider certificate file with key (Bundled PEM). */
	protected $_sProviderCertificatePassphrase; /**< @type string Provider certificate passphrase. */
	protected $_sRootCertificationAuthorityFile; /**< @type string Root certification authority file. */

	protected $_nWriteInterval; /**< @type integer Write interval in micro seconds. */
	protected $_nConnectRetryInterval; /**< @type integer Connect retry interval in micro seconds. */
	protected $_nSocketSelectTimeout; /**< @type integer Socket select timeout in micro seconds. */

	protected $_logger; /**< @type ApnsPHP_Log_Interface Logger. */

	protected $_hSocket; /**< @type resource SSL Socket. */

	/**
	 * Constructor.
	 *
	 * @param  $nEnvironment @type integer Environment.
	 * @param  $sProviderCertificateFile @type string Provider certificate file
	 *         with key (Bundled PEM).
	 * @throws ApnsPHP_Exception if the environment is not
	 *         sandbox or production or the provider certificate file is not readable.
	 */
	public function __construct($nEnvironment, $sProviderCertificateFile)
	{
		if ($nEnvironment != self::ENVIRONMENT_PRODUCTION && $nEnvironment != self::ENVIRONMENT_SANDBOX) {
			throw new ApnsPHP_Exception(
				"Invalid environment '{$nEnvironment}'"
			);
		}
		$this->_nEnvironment = $nEnvironment;

		if (!is_readable($sProviderCertificateFile)) {
			throw new ApnsPHP_Exception(
				"Unable to read certificate file '{$sProviderCertificateFile}'"
			);
		}
		$this->_sProviderCertificateFile = $sProviderCertificateFile;

		$this->_nConnectTimeout = ini_get("default_socket_timeout");
		$this->_nWriteInterval = self::WRITE_INTERVAL;
		$this->_nConnectRetryInterval = self::CONNECT_RETRY_INTERVAL;
		$this->_nSocketSelectTimeout = self::SOCKET_SELECT_TIMEOUT;
	}

	/**
	 * Set the Logger instance to use for logging purpose.
	 *
	 * The default logger is ApnsPHP_Log_Embedded, an instance
	 * of ApnsPHP_Log_Interface that simply print to standard
	 * output log messages.
	 *
	 * To set a custom logger you have to implement ApnsPHP_Log_Interface
	 * and use setLogger, otherwise standard logger will be used.
	 *
	 * @see ApnsPHP_Log_Interface
	 * @see ApnsPHP_Log_Embedded
	 *
	 * @param  $logger @type ApnsPHP_Log_Interface Logger instance.
	 * @throws ApnsPHP_Exception if Logger is not an instance
	 *         of ApnsPHP_Log_Interface.
	 */
	public function setLogger(ApnsPHP_Log_Interface $logger)
	{
		if (!is_object($logger)) {
			throw new ApnsPHP_Exception(
				"The logger should be an instance of 'ApnsPHP_Log_Interface'"
			);
		}
		if (!($logger instanceof ApnsPHP_Log_Interface)) {
			throw new ApnsPHP_Exception(
				"Unable to use an instance of '" . get_class($logger) . "' as logger: " .
				"a logger must implements ApnsPHP_Log_Interface."
			);
		}
		$this->_logger = $logger;
	}

	/**
	 * Get the Logger instance.
	 *
	 * @return @type ApnsPHP_Log_Interface Current Logger instance.
	 */
	public function getLogger()
	{
		return $this->_logger;
	}

	/**
	 * Set the Provider Certificate passphrase.
	 *
	 * @param  $sProviderCertificatePassphrase @type string Provider Certificate
	 *         passphrase.
	 */
	public function setProviderCertificatePassphrase($sProviderCertificatePassphrase)
	{
		$this->_sProviderCertificatePassphrase = $sProviderCertificatePassphrase;
	}

	/**
	 * Set the Root Certification Authority file.
	 *
	 * Setting the Root Certification Authority file automatically set peer verification
	 * on connect.
	 *
	 * @see http://tinyurl.com/GeneralProviderRequirements
	 * @see http://www.entrust.net/
	 * @see https://www.entrust.net/downloads/root_index.cfm
	 *
	 * @param  $sRootCertificationAuthorityFile @type string Root Certification
	 *         Authority file.
	 * @throws ApnsPHP_Exception if Root Certification Authority
	 *         file is not readable.
	 */
	public function setRootCertificationAuthority($sRootCertificationAuthorityFile)
	{
		if (!is_readable($sRootCertificationAuthorityFile)) {
			throw new ApnsPHP_Exception(
				"Unable to read Certificate Authority file '{$sRootCertificationAuthorityFile}'"
			);
		}
		$this->_sRootCertificationAuthorityFile = $sRootCertificationAuthorityFile;
	}

	/**
	 * Get the Root Certification Authority file path.
	 *
	 * @return @type string Current Root Certification Authority file path.
	 */
	public function getCertificateAuthority()
	{
		return $this->_sRootCertificationAuthorityFile;
	}

	/**
	 * Set the write interval.
	 *
	 * After each socket write operation we are sleeping for this 
	 * time interval. To speed up the sending operations, use Zero
	 * as parameter but some messages may be lost.
	 *
	 * @param  $nWriteInterval @type integer Write interval in micro seconds.
	 */
	public function setWriteInterval($nWriteInterval)
	{
		$this->_nWriteInterval = (int)$nWriteInterval;
	}

	/**
	 * Get the write interval.
	 *
	 * @return @type integer Write interval in micro seconds.
	 */
	public function getWriteInterval()
	{
		return $this->_nWriteInterval;
	}

	/**
	 * Set the connection timeout.
	 *
	 * The default connection timeout is the PHP internal value "default_socket_timeout".
	 * @see http://php.net/manual/en/filesystem.configuration.php
	 *
	 * @param  $nTimeout @type integer Connection timeout in seconds.
	 */
	public function setConnectTimeout($nTimeout)
	{
		$this->_nConnectTimeout = (int)$nTimeout;
	}

	/**
	 * Get the connection timeout.
	 *
	 * @return @type integer Connection timeout in seconds.
	 */
	public function getConnectTimeout()
	{
		return $this->_nConnectTimeout;
	}

	/**
	 * Set the connect retry times value.
	 *
	 * If the client is unable to connect to the server retries at least for this
	 * value. The default connect retry times is 3.
	 *
	 * @param  $nRetryTimes @type integer Connect retry times.
	 */
	public function setConnectRetryTimes($nRetryTimes)
	{
		$this->_nConnectRetryTimes = (int)$nRetryTimes;
	}

	/**
	 * Get the connect retry time value.
	 *
	 * @return @type integer Connect retry times.
	 */
	public function getConnectRetryTimes()
	{
		return $this->_nConnectRetryTimes;
	}

	/**
	 * Set the connect retry interval.
	 *
	 * If the client is unable to connect to the server retries at least for ConnectRetryTimes
	 * and waits for this value between each attempts.
	 *
	 * @see setConnectRetryTimes
	 *
	 * @param  $nRetryInterval @type integer Connect retry interval in micro seconds.
	 */
	public function setConnectRetryInterval($nRetryInterval)
	{
		$this->_nConnectRetryInterval = (int)$nRetryInterval;
	}

	/**
	 * Get the connect retry interval.
	 *
	 * @return @type integer Connect retry interval in micro seconds.
	 */
	public function getConnectRetryInterval()
	{
		return $this->_nConnectRetryInterval;
	}

	/**
	 * Set the TCP socket select timeout.
	 *
	 * After writing to socket waits for at least this value for read stream to
	 * change status.
	 *
	 * In Apple Push Notification protocol there isn't a real-time
	 * feedback about the correctness of notifications pushed to the server; so after
	 * each write to server waits at least SocketSelectTimeout. If, during this
	 * time, the read stream change its status and socket received an end-of-file
	 * from the server the notification pushed to server was broken, the server
	 * has closed the connection and the client needs to reconnect.
	 *
	 * @see http://php.net/stream_select
	 *
	 * @param  $nSelectTimeout @type integer Socket select timeout in micro seconds.
	 */
	public function setSocketSelectTimeout($nSelectTimeout)
	{
		$this->_nSocketSelectTimeout = (int)$nSelectTimeout;
	}

	/**
	 * Get the TCP socket select timeout.
	 *
	 * @return @type integer Socket select timeout in micro seconds.
	 */
	public function getSocketSelectTimeout()
	{
		return $this->_nSocketSelectTimeout;
	}

	/**
	 * Connects to Apple Push Notification service server.
	 *
	 * Retries ConnectRetryTimes if unable to connect and waits setConnectRetryInterval
	 * between each attempts.
	 *
	 * @see setConnectRetryTimes
	 * @see setConnectRetryInterval
	 * @throws ApnsPHP_Exception if is unable to connect after
	 *         ConnectRetryTimes.
	 */
	public function connect()
	{
		$bConnected = false;
		$nRetry = 0;
		while (!$bConnected) {
			try {
				$bConnected = $this->_connect();
			} catch (ApnsPHP_Exception $e) {
				$this->_log('ERROR: ' . $e->getMessage());
				if ($nRetry >= $this->_nConnectRetryTimes) {
					throw $e;
				} else {
					$this->_log(
						"INFO: Retry to connect (" . ($nRetry+1) .
						"/{$this->_nConnectRetryTimes})..."
					);
					usleep($this->_nConnectRetryInterval);
				}
			}
			$nRetry++;
		}
	}

	/**
	 * Disconnects from Apple Push Notifications service server.
	 *
	 * @return @type boolean True if successful disconnected.
	 */
	public function disconnect()
	{
		if (is_resource($this->_hSocket)) {
			$this->_log('INFO: Disconnected.');
			return fclose($this->_hSocket);
		}
		return false;
	}

	/**
	 * Connects to Apple Push Notification service server.
	 *
	 * @throws ApnsPHP_Exception if is unable to connect.
	 * @return @type boolean True if successful connected.
	 */
	protected function _connect()
	{
		$sURL = $this->_aServiceURLs[$this->_nEnvironment];
		unset($aURLs);

		$this->_log("INFO: Trying {$sURL}...");

		/**
		 * @see http://php.net/manual/en/context.ssl.php
		 */
		$streamContext = stream_context_create(array('ssl' => array(
			'verify_peer' => isset($this->_sRootCertificationAuthorityFile),
			'cafile' => $this->_sRootCertificationAuthorityFile,
			'local_cert' => $this->_sProviderCertificateFile
		)));

		if (!empty($this->_sProviderCertificatePassphrase)) {
			stream_context_set_option($streamContext, 'ssl',
				'passphrase', $this->_sProviderCertificatePassphrase);
		}

		$this->_hSocket = @stream_socket_client($sURL, $nError, $sError,
			$this->_nConnectTimeout, STREAM_CLIENT_CONNECT, $streamContext);

		if (!$this->_hSocket) {
			throw new ApnsPHP_Exception(
				"Unable to connect to '{$sURL}': {$sError} ({$nError})"
			);
		}

		stream_set_blocking($this->_hSocket, 0);
		stream_set_write_buffer($this->_hSocket, 0);

		$this->_log("INFO: Connected to {$sURL}.");

		return true;
	}

	/**
	 * Logs a message through the Logger.
	 *
	 * @param  $sMessage @type string The message.
	 */
	protected function _log($sMessage)
	{
		if (!isset($this->_logger)) {
			$this->_logger = new ApnsPHP_Log_Embedded();
		}
		$this->_logger->log($sMessage);
	}
}
// ####################################################### ./include/ApnsPHP/Autoload.php ####################################################### \\


/**
 * @file
 * Autoload stuff.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * This function is automatically called in case you are trying to use a
 * class/interface which hasn't been defined yet. By calling this function the
 * scripting engine is given a last chance to load the class before PHP
 * fails with an error.
 *
 * @see http://php.net/__autoload
 * @see http://php.net/spl_autoload_register
 *
 * @param  $sClassName @type string The class name.
 * @throws Exception if class name is empty, the current path is empty or class
 *         file does not exists or file was loaded but class name was not found.
 */
function ApnsPHP_Autoload($sClassName)
{
	if (empty($sClassName)) {
		throw new Exception('Class name is empty');
	}

	$sPath = dirname(dirname(__FILE__));
	if (empty($sPath)) {
		throw new Exception('Current path is empty');
	}

	$sFile = sprintf('%s%s%s.php',
		$sPath, DIRECTORY_SEPARATOR,
		str_replace('_', DIRECTORY_SEPARATOR, $sClassName)
	);
	if (is_file($sFile) && is_readable($sFile)) {
		require_once $sFile;
	}
}

// If your code has an existing __autoload function then this function must be explicitly registered on the __autoload stack.
// (PHP Documentation for spl_autoload_register [@see http://php.net/spl_autoload_register])
if (function_exists('__autoload')) {
	spl_autoload_register('__autoload');
}
spl_autoload_register('ApnsPHP_Autoload');
// ####################################################### ./include/ApnsPHP/Exception.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Exception class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * Exception class.
 *
 * @ingroup ApplePushNotificationService
 */
class ApnsPHP_Exception extends Exception
{
}// ####################################################### ./include/ApnsPHP/Feedback.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Feedback class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * @defgroup ApnsPHP_Feedback Feedback
 * @ingroup ApplePushNotificationService
 */

/**
 * The Feedback Service client.
 *
 * Apple Push Notification Service includes a feedback service that APNs continually
 * updates with a per-application list of devices for which there were failed-delivery
 * attempts. Providers should periodically query the feedback service to get the
 * list of device tokens for their applications, each of which is identified by
 * its topic. Then, after verifying that the application hasn’t recently been re-registered
 * on the identified devices, a provider should stop sending notifications to these
 * devices.
 *
 * @ingroup ApnsPHP_Feedback
 * @see http://tinyurl.com/ApplePushNotificationFeedback
 */
class ApnsPHP_Feedback extends ApnsPHP_Abstract
{
	const TIME_BINARY_SIZE = 4; /**< @type integer Timestamp binary size in bytes. */
	const TOKEN_LENGTH_BINARY_SIZE = 2; /**< @type integer Token length binary size in bytes. */

	protected $_aServiceURLs = array(
		'tls://feedback.push.apple.com:2196', // Production environment
		'tls://feedback.sandbox.push.apple.com:2196' // Sandbox environment
	); /**< @type array Feedback URLs environments. */

	protected $_aFeedback; /**< @type array Feedback container. */

	/**
	 * Receives feedback tuples from Apple Push Notification Service feedback.
	 *
	 * Every tuple (array) contains:
	 * @li @c timestamp indicating when the APNs determined that the application
	 *     no longer exists on the device. This value represents the seconds since
	 *     1970, anchored to UTC. You should use the timestamp to determine if the
	 *     application on the device re-registered with your service since the moment
	 *     the device token was recorded on the feedback service. If it hasn’t,
	 *     you should cease sending push notifications to the device.
	 * @li @c tokenLength The length of the device token (usually 32 bytes).
	 * @li @c deviceToken The device token.
	 *
	 * @return @type array Array of feedback tuples (array).
	 */
	public function receive()
	{
		$nFeedbackTupleLen = self::TIME_BINARY_SIZE + self::TOKEN_LENGTH_BINARY_SIZE + self::DEVICE_BINARY_SIZE;

		$this->_aFeedback = array();
		$sBuffer = '';
		while (!feof($this->_hSocket)) {
			$this->_log('INFO: Reading...');
			$sBuffer .= $sCurrBuffer = fread($this->_hSocket, 8192);
			$nCurrBufferLen = strlen($sCurrBuffer);
			if ($nCurrBufferLen > 0) {
				$this->_log("INFO: {$nCurrBufferLen} bytes read.");
			}
			unset($sCurrBuffer, $nCurrBufferLen);

			$nBufferLen = strlen($sBuffer);
			if ($nBufferLen >= $nFeedbackTupleLen) {
				$nFeedbackTuples = floor($nBufferLen / $nFeedbackTupleLen);
				for ($i = 0; $i < $nFeedbackTuples; $i++) {
					$sFeedbackTuple = substr($sBuffer, 0, $nFeedbackTupleLen);
					$sBuffer = substr($sBuffer, $nFeedbackTupleLen);
					$this->_aFeedback[] = $aFeedback = $this->_parseBinaryTuple($sFeedbackTuple);
					$this->_log(sprintf("INFO: New feedback tuple: timestamp=%d (%s), tokenLength=%d, deviceToken=%s.",
						$aFeedback['timestamp'], date('Y-m-d H:i:s', $aFeedback['timestamp']),
						$aFeedback['tokenLength'], $aFeedback['deviceToken']
					));
					unset($aFeedback);
				}
			}

			$read = array($this->_hSocket);
			$null = NULL;
			$nChangedStreams = stream_select($read, $null, $null, 0, $this->_nSocketSelectTimeout);
			if ($nChangedStreams === false) {
				$this->_log('WARNING: Unable to wait for a stream availability.');
				break;
			}
		}
		return $this->_aFeedback;
	}

	/**
	 * Parses binary tuples.
	 *
	 * @param  $sBinaryTuple @type string A binary tuple to parse.
	 * @return @type array Array with timestamp, tokenLength and deviceToken keys.
	 */
	protected function _parseBinaryTuple($sBinaryTuple)
	{
		return unpack('Ntimestamp/ntokenLength/H*deviceToken', $sBinaryTuple);
	}
}
// ####################################################### ./include/ApnsPHP/Log/Embedded.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Log_Embedded class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * A simple logger.
 *
 * This simple logger implements the Log Interface and is the default logger for
 * all ApnsPHP_Abstract based class.
 *
 * This simple logger outputs The Message to standard output prefixed with date,
 * service name (ApplePushNotificationService) and Process ID (PID).
 *
 * @ingroup ApnsPHP_Log
 */
class ApnsPHP_Log_Embedded implements ApnsPHP_Log_Interface
{
	/**
	 * Logs a message.
	 *
	 * @param  $sMessage @type string The message.
	 */
	public function log($sMessage)
	{
		printf("%s ApnsPHP[%d]: %s\n",
			date('r'), getmypid(), trim($sMessage)
		);
	}
}
// ####################################################### ./include/ApnsPHP/Log/Interface.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Log_Interface interface definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * @defgroup ApnsPHP_Log Log
 * @ingroup ApplePushNotificationService
 */

/**
 * The Log Interface.
 *
 * Implement the Log Interface and pass the object instance to all
 * ApnsPHP_Abstract based class to use a custom log.
 *
 * @ingroup ApnsPHP_Log
 */
interface ApnsPHP_Log_Interface
{
	/**
	 * Logs a message.
	 *
	 * @param  $sMessage @type string The message.
	 */
	public function log($sMessage);
}
// ####################################################### ./include/ApnsPHP/Message/Custom.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Message_Custom class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * The Push Notification Custom Message.
 *
 * The class represents a custom message to be delivered to an end user device.
 * Please refer to Table 3-2 for more information.
 *
 * @ingroup ApnsPHP_Message
 * @see http://tinyurl.com/ApplePushNotificationPayload
 */
class ApnsPHP_Message_Custom extends ApnsPHP_Message
{
	protected $_sActionLocKey; /**< @type string The "View" button title. */
	protected $_sLocKey; /**< @type string A key to an alert-message string in a Localizable.strings file */
	protected $_aLocArgs; /**< @type array Variable string values to appear in place of the format specifiers in loc-key. */
	protected $_sLaunchImage; /**< @type string The filename of an image file in the application bundle. */

	/**
	 * Set the "View" button title.
	 *
	 * If a string is specified, displays an alert with two buttons.
	 * iOS uses the string as a key to get a localized string in the current localization
	 * to use for the right button’s title instead of "View". If the value is an
	 * empty string, the system displays an alert with a single OK button that simply
	 * dismisses the alert when tapped.
	 *
	 * @param  $sActionLocKey @type string @optional The "View" button title, default
	 *         empty string.
	 */
	public function setActionLocKey($sActionLocKey = '')
	{
		$this->_sActionLocKey = $sActionLocKey;
	}

	/**
	 * Get the "View" button title.
	 *
	 * @return @type string The "View" button title.
	 */
	public function getActionLocKey()
	{
		return $this->_sActionLocKey;
	}

	/**
	 * Set the alert-message string in Localizable.strings file for the current
	 * localization (which is set by the user’s language preference).
	 *
	 * The key string can be formatted with %@ and %n$@ specifiers to take the variables
	 * specified in loc-args.
	 *
	 * @param  $sLocKey @type string The alert-message string.
	 */
	public function setLocKey($sLocKey)
	{
		$this->_sLocKey = $sLocKey;
	}

	/**
	 * Get the alert-message string in Localizable.strings file.
	 *
	 * @return @type string The alert-message string.
	 */
	public function getLocKey()
	{
		return $this->_sLocKey;
	}

	/**
	 * Set the variable string values to appear in place of the format specifiers
	 * in loc-key.
	 *
	 * @param  $aLocArgs @type array The variable string values.
	 */
	public function setLocArgs($aLocArgs)
	{
		$this->_aLocArgs = $aLocArgs;
	}

	/**
	 * Get the variable string values to appear in place of the format specifiers
	 * in loc-key.
	 *
	 * @return @type string The variable string values.
	 */
	public function getLocArgs()
	{
		return $this->_aLocArgs;
	}

	/**
	 * Set the filename of an image file in the application bundle; it may include
	 * the extension or omit it.
	 *
	 * The image is used as the launch image when users tap the action button or
	 * move the action slider. If this property is not specified, the system either
	 * uses the previous snapshot, uses the image identified by the UILaunchImageFile
	 * key in the application’s Info.plist file, or falls back to Default.png.
	 * This property was added in iOS 4.0.
	 *
	 * @param  $sLaunchImage @type string The filename of an image file.
	 */
	public function setLaunchImage($sLaunchImage)
	{
		$this->_sLaunchImage = $sLaunchImage;
	}

	/**
	 * Get the filename of an image file in the application bundle.
	 *
	 * @return @type string The filename of an image file.
	 */
	public function getLaunchImage()
	{
		return $this->_sLaunchImage;
	}

	/**
	 * Get the payload dictionary.
	 *
	 * @return @type array The payload dictionary.
	 */
	protected function _getPayload()
	{
		$aPayload = parent::_getPayload();

		$aPayload['aps']['alert'] = array();

		if (isset($this->_sText) && !isset($this->_sLocKey)) {
			$aPayload['aps']['alert']['body'] = (string)$this->_sText;
		}

		if (isset($this->_sActionLocKey)) {
			$aPayload['aps']['alert']['action-loc-key'] = $this->_sActionLocKey == '' ?
				null : (string)$this->_sActionLocKey;
		}

		if (isset($this->_sLocKey)) {
			$aPayload['aps']['alert']['loc-key'] = (string)$this->_sLocKey;
		}

		if (isset($this->_aLocArgs)) {
			$aPayload['aps']['alert']['loc-args'] = $this->_aLocArgs;
		}

		if (isset($this->_sLaunchImage)) {
			$aPayload['aps']['alert']['launch-image'] = (string)$this->_sLaunchImage;
		}

		return $aPayload;
	}
}// ####################################################### ./include/ApnsPHP/Message.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Message class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * @defgroup ApnsPHP_Message Message
 * @ingroup ApplePushNotificationService
 */

/**
 * The Push Notification Message.
 *
 * The class represents a message to be delivered to an end user device.
 * Notification Service.
 *
 * @ingroup ApnsPHP_Message
 * @see http://tinyurl.com/ApplePushNotificationPayload
 */
class ApnsPHP_Message
{
	const PAYLOAD_MAXIMUM_SIZE = 2048; /**< @type integer The maximum size allowed for a notification payload. */
	const APPLE_RESERVED_NAMESPACE = 'aps'; /**< @type string The Apple-reserved aps namespace. */

	protected $_bAutoAdjustLongPayload = true; /**< @type boolean If the JSON payload is longer than maximum allowed size, shorts message text. */

	protected $_aDeviceTokens = array(); /**< @type array Recipients device tokens. */

	protected $_sText; /**< @type string Alert message to display to the user. */
	protected $_nBadge; /**< @type integer Number to badge the application icon with. */
	protected $_sSound; /**< @type string Sound to play. */
	protected $_sCategory; /**< @type string notification category. */
	protected $_bContentAvailable; /**< @type boolean True to initiates the Newsstand background download. @see http://tinyurl.com/ApplePushNotificationNewsstand */

	protected $_aCustomProperties; /**< @type mixed Custom properties container. */

	protected $_nExpiryValue = 604800; /**< @type integer That message will expire in 604800 seconds (86400 * 7, 7 days) if not successful delivered. */

	protected $_mCustomIdentifier; /**< @type mixed Custom message identifier. */

	/**
	 * Constructor.
	 *
	 * @param  $sDeviceToken @type string @optional Recipients device token.
	 */
	public function __construct($sDeviceToken = null)
	{
		if (isset($sDeviceToken)) {
			$this->addRecipient($sDeviceToken);
		}
	}

	/**
	 * Add a recipient device token.
	 *
	 * @param  $sDeviceToken @type string Recipients device token.
	 * @throws ApnsPHP_Message_Exception if the device token
	 *         is not well formed.
	 */
	public function addRecipient($sDeviceToken)
	{
		if (!preg_match('~^[a-f0-9]{64}$~i', $sDeviceToken)) {
			throw new ApnsPHP_Message_Exception(
				"Invalid device token '{$sDeviceToken}'"
			);
		}
		$this->_aDeviceTokens[] = $sDeviceToken;
	}

	/**
	 * Get a recipient.
	 *
	 * @param  $nRecipient @type integer @optional Recipient number to return.
	 * @throws ApnsPHP_Message_Exception if no recipient number
	 *         exists.
	 * @return @type string The recipient token at index $nRecipient.
	 */
	public function getRecipient($nRecipient = 0)
	{
		if (!isset($this->_aDeviceTokens[$nRecipient])) {
			throw new ApnsPHP_Message_Exception(
				"No recipient at index '{$nRecipient}'"
			);
		}
		return $this->_aDeviceTokens[$nRecipient];
	}

	/**
	 * Get the number of recipients.
	 *
	 * @return @type integer Recipient's number.
	 */
	public function getRecipientsNumber()
	{
		return count($this->_aDeviceTokens);
	}

	/**
	 * Get all recipients.
	 *
	 * @return @type array Array of all recipients device token.
	 */
	public function getRecipients()
	{
		return $this->_aDeviceTokens;
	}

	/**
	 * Set the alert message to display to the user.
	 *
	 * @param  $sText @type string An alert message to display to the user.
	 */
	public function setText($sText)
	{
		$this->_sText = $sText;
	}

	/**
	 * Get the alert message to display to the user.
	 *
	 * @return @type string The alert message to display to the user.
	 */
	public function getText()
	{
		return $this->_sText;
	}

	/**
	 * Set the number to badge the application icon with.
	 *
	 * @param  $nBadge @type integer A number to badge the application icon with.
	 * @throws ApnsPHP_Message_Exception if badge is not an
	 *         integer.
	 */
	public function setBadge($nBadge)
	{
		if (!is_int($nBadge)) {
			throw new ApnsPHP_Message_Exception(
				"Invalid badge number '{$nBadge}'"
			);
		}
		$this->_nBadge = $nBadge;
	}

	/**
	 * Get the number to badge the application icon with.
	 *
	 * @return @type integer The number to badge the application icon with.
	 */
	public function getBadge()
	{
		return $this->_nBadge;
	}

	/**
	 * Set the sound to play.
	 *
	 * @param  $sSound @type string @optional A sound to play ('default sound' is
	 *         the default sound).
	 */
	public function setSound($sSound = 'default')
	{
		$this->_sSound = $sSound;
	}

	/**
	 * Get the sound to play.
	 *
	 * @return @type string The sound to play.
	 */
	public function getSound()
	{
		return $this->_sSound;
	}
	
	/**
	 * Set the category of notification
	 *
	 * @param  $sCategory @type string @optional A category for ios8 notification actions.
	 */
	public function setCategory($sCategory = '')
	{
		$this->_sCategory = $sCategory;
	}

	/**
	 * Get the category of notification
	 *
	 * @return @type string The notification category
	 */
	public function getCategory()
	{
		return $this->_sCategory;
	}

	/**
	 * Initiates the Newsstand background download.
	 * @see http://tinyurl.com/ApplePushNotificationNewsstand
	 *
	 * @param  $bContentAvailable @type boolean True to initiates the Newsstand background download.
	 * @throws ApnsPHP_Message_Exception if ContentAvailable is not a
	 *         boolean.
	 */
	public function setContentAvailable($bContentAvailable = true)
	{
		if (!is_bool($bContentAvailable)) {
			throw new ApnsPHP_Message_Exception(
				"Invalid content-available value '{$bContentAvailable}'"
			);
		}
		$this->_bContentAvailable = $bContentAvailable ? true : null;
	}

	/**
	 * Get if should initiates the Newsstand background download.
	 *
	 * @return @type boolean Initiates the Newsstand background download property.
	 */
	public function getContentAvailable()
	{
		return $this->_bContentAvailable;
	}

	/**
	 * Set a custom property.
	 *
	 * @param  $sName @type string Custom property name.
	 * @param  $mValue @type mixed Custom property value.
	 * @throws ApnsPHP_Message_Exception if custom property name is not outside
	 *         the Apple-reserved 'aps' namespace.
	 */
	public function setCustomProperty($sName, $mValue)
	{
		if (trim($sName) == self::APPLE_RESERVED_NAMESPACE) {
			throw new ApnsPHP_Message_Exception(
				"Property name '" . self::APPLE_RESERVED_NAMESPACE . "' can not be used for custom property."
			);
		}
		$this->_aCustomProperties[trim($sName)] = $mValue;
	}

	/**
	 * Get the first custom property name.
	 *
	 * @deprecated Use getCustomPropertyNames() instead.
	 *
	 * @return @type string The first custom property name.
	 */
	public function getCustomPropertyName()
	{
		if (!is_array($this->_aCustomProperties)) {
			return;
		}
		$aKeys = array_keys($this->_aCustomProperties);
		return $aKeys[0];
	}

	/**
	 * Get the first custom property value.
	 *
	 * @deprecated Use getCustomProperty() instead.
	 *
	 * @return @type mixed The first custom property value.
	 */
	public function getCustomPropertyValue()
	{
		if (!is_array($this->_aCustomProperties)) {
			return;
		}
		$aKeys = array_keys($this->_aCustomProperties);
		return $this->_aCustomProperties[$aKeys[0]];
	}

	/**
	 * Get all custom properties names.
	 *
	 * @return @type array All properties names.
	 */
	public function getCustomPropertyNames()
	{
		if (!is_array($this->_aCustomProperties)) {
			return array();
		}
		return array_keys($this->_aCustomProperties);
	}

	/**
	 * Get the custom property value.
	 *
	 * @param  $sName @type string Custom property name.
	 * @throws ApnsPHP_Message_Exception if no property exists with the specified
	 *         name.
	 * @return @type string The custom property value.
	 */
	public function getCustomProperty($sName)
	{
		if (!array_key_exists($sName, $this->_aCustomProperties)) {
			throw new ApnsPHP_Message_Exception(
				"No property exists with the specified name '{$sName}'."
			);
		}
		return $this->_aCustomProperties[$sName];
	}

	/**
	 * Set the auto-adjust long payload value.
	 *
	 * @param  $bAutoAdjust @type boolean If true a long payload is shorted cutting
	 *         long text value.
	 */
	public function setAutoAdjustLongPayload($bAutoAdjust)
	{
		$this->_bAutoAdjustLongPayload = (boolean)$bAutoAdjust;
	}

	/**
	 * Get the auto-adjust long payload value.
	 *
	 * @return @type boolean The auto-adjust long payload value.
	 */
	public function getAutoAdjustLongPayload()
	{
		return $this->_bAutoAdjustLongPayload;
	}

	/**
	 * PHP Magic Method. When an object is "converted" to a string, JSON-encoded
	 * payload is returned.
	 *
	 * @return @type string JSON-encoded payload.
	 */
	public function __toString()
	{
		try {
			$sJSONPayload = $this->getPayload();
		} catch (ApnsPHP_Message_Exception $e) {
			$sJSONPayload = '';
		}
		return $sJSONPayload;
	}

	/**
	 * Get the payload dictionary.
	 *
	 * @return @type array The payload dictionary.
	 */
	protected function _getPayload()
	{
		$aPayload[self::APPLE_RESERVED_NAMESPACE] = array();

		if (isset($this->_sText)) {
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['alert'] = (string)$this->_sText;
		}
		if (isset($this->_nBadge) && $this->_nBadge >= 0) {
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['badge'] = (int)$this->_nBadge;
		}
		if (isset($this->_sSound)) {
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['sound'] = (string)$this->_sSound;
		}
		if (isset($this->_bContentAvailable)) {
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['content-available'] = (int)$this->_bContentAvailable;
		}
		if (isset($this->_sCategory)) {
			$aPayload[self::APPLE_RESERVED_NAMESPACE]['category'] = (string)$this->_sCategory;
		}

		if (is_array($this->_aCustomProperties)) {
			foreach($this->_aCustomProperties as $sPropertyName => $mPropertyValue) {
				$aPayload[$sPropertyName] = $mPropertyValue;
			}
		}

		return $aPayload;
	}

	/**
	 * Convert the message in a JSON-encoded payload.
	 *
	 * @throws ApnsPHP_Message_Exception if payload is longer than maximum allowed
	 *         size and AutoAdjustLongPayload is disabled.
	 * @return @type string JSON-encoded payload.
	 */
	public function getPayload()
	{
		$sJSON = json_encode($this->_getPayload(), defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
		if (!defined('JSON_UNESCAPED_UNICODE') && function_exists('mb_convert_encoding')) {
			$sJSON = preg_replace_callback(
				'~\\\\u([0-9a-f]{4})~i',
				create_function('$aMatches', 'return mb_convert_encoding(pack("H*", $aMatches[1]), "UTF-8", "UTF-16");'),
				$sJSON);
		}

		$sJSONPayload = str_replace(
			'"' . self::APPLE_RESERVED_NAMESPACE . '":[]',
			'"' . self::APPLE_RESERVED_NAMESPACE . '":{}',
			$sJSON
		);
		$nJSONPayloadLen = strlen($sJSONPayload);

		if ($nJSONPayloadLen > self::PAYLOAD_MAXIMUM_SIZE) {
			if ($this->_bAutoAdjustLongPayload) {
				$nMaxTextLen = $nTextLen = strlen($this->_sText) - ($nJSONPayloadLen - self::PAYLOAD_MAXIMUM_SIZE);
				if ($nMaxTextLen > 0) {
					while (strlen($this->_sText = mb_substr($this->_sText, 0, --$nTextLen, 'UTF-8')) > $nMaxTextLen);
					return $this->getPayload();
				} else {
					throw new ApnsPHP_Message_Exception(
						"JSON Payload is too long: {$nJSONPayloadLen} bytes. Maximum size is " .
						self::PAYLOAD_MAXIMUM_SIZE . " bytes. The message text can not be auto-adjusted."
					);
				}
			} else {
				throw new ApnsPHP_Message_Exception(
					"JSON Payload is too long: {$nJSONPayloadLen} bytes. Maximum size is " .
					self::PAYLOAD_MAXIMUM_SIZE . " bytes"
				);
			}
		}

		return $sJSONPayload;
	}

	/**
	 * Set the expiry value.
	 *
	 * @param  $nExpiryValue @type integer This message will expire in N seconds
	 *         if not successful delivered.
	 */
	public function setExpiry($nExpiryValue)
	{
		if (!is_int($nExpiryValue)) {
			throw new ApnsPHP_Message_Exception(
				"Invalid seconds number '{$nExpiryValue}'"
			);
		}
		$this->_nExpiryValue = $nExpiryValue;
	}

	/**
	 * Get the expiry value.
	 *
	 * @return @type integer The expire message value (in seconds).
	 */
	public function getExpiry()
	{
		return $this->_nExpiryValue;
	}

	/**
	 * Set the custom message identifier.
	 *
	 * The custom message identifier is useful to associate a push notification
	 * to a DB record or an User entry for example. The custom message identifier
	 * can be retrieved in case of error using the getCustomIdentifier()
	 * method of an entry retrieved by the getErrors() method.
	 * This custom identifier, if present, is also used in all status message by
	 * the ApnsPHP_Push class.
	 *
	 * @param  $mCustomIdentifier @type mixed The custom message identifier.
	 */
	public function setCustomIdentifier($mCustomIdentifier)
	{
		$this->_mCustomIdentifier = $mCustomIdentifier;
	}

	/**
	 * Get the custom message identifier.
	 *
	 * @return @type mixed The custom message identifier.
	 */
	public function getCustomIdentifier()
	{
		return $this->_mCustomIdentifier;
	}
}
// ####################################################### ./include/ApnsPHP/Message/Exception.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Message_Exception class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * Exception class.
 *
 * @ingroup ApnsPHP_Message
 */
class ApnsPHP_Message_Exception extends ApnsPHP_Exception
{
}// ####################################################### ./include/ApnsPHP/Push/Exception.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Push_Exception class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * Exception class.
 *
 * @ingroup ApnsPHP_Push
 */
class ApnsPHP_Push_Exception extends ApnsPHP_Exception
{
}// ####################################################### ./include/ApnsPHP/Push/Server/Exception.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Push_Server_Exception class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * Exception class.
 *
 * @ingroup ApnsPHP_Push_Server
 */
class ApnsPHP_Push_Server_Exception extends ApnsPHP_Push_Exception
{
}// ####################################################### ./include/ApnsPHP/Push/Server.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Push_Server class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * @defgroup ApnsPHP_Push_Server Server
 * @ingroup ApnsPHP_Push
 */

/**
 * The Push Notification Server Provider.
 *
 * The class manages multiple Push Notification Providers and an inter-process message
 * queue. This class is useful to parallelize and speed-up send activities to Apple
 * Push Notification service.
 *
 * @ingroup ApnsPHP_Push_Server
 */
class ApnsPHP_Push_Server extends ApnsPHP_Push
{
	const MAIN_LOOP_USLEEP = 200000; /**< @type integer Main loop sleep time in micro seconds. */
	const SHM_SIZE = 524288; /**< @type integer Shared memory size in bytes useful to store message queues. */
	const SHM_MESSAGES_QUEUE_KEY_START = 1000; /**< @type integer Message queue start identifier for messages. For every process 1 is added to this number. */
	const SHM_ERROR_MESSAGES_QUEUE_KEY = 999; /**< @type integer Message queue identifier for not delivered messages. */

	protected $_nProcesses = 3; /**< @type integer The number of processes to start. */
	protected $_aPids = array(); /**< @type array Array of process PIDs. */
	protected $_nParentPid; /**< @type integer The parent process id. */
	protected $_nCurrentProcess; /**< @type integer Cardinal process number (0, 1, 2, ...). */
	protected $_nRunningProcesses; /**< @type integer The number of running processes. */

	protected $_hShm; /**< @type resource Shared memory. */
	protected $_hSem; /**< @type resource Semaphore. */

	/**
	 * Constructor.
	 *
	 * @param  $nEnvironment @type integer Environment.
	 * @param  $sProviderCertificateFile @type string Provider certificate file
	 *         with key (Bundled PEM).
	 * @throws ApnsPHP_Push_Server_Exception if is unable to
	 *         get Shared Memory Segment or Semaphore ID.
	 */
	public function __construct($nEnvironment, $sProviderCertificateFile)
	{
		parent::__construct($nEnvironment, $sProviderCertificateFile);

		$this->_nParentPid = posix_getpid();
		$this->_hShm = shm_attach(mt_rand(), self::SHM_SIZE);
		if ($this->_hShm === false) {
			throw new ApnsPHP_Push_Server_Exception(
				'Unable to get shared memory segment'
			);
		}

		$this->_hSem = sem_get(mt_rand());
		if ($this->_hSem === false) {
			throw new ApnsPHP_Push_Server_Exception(
				'Unable to get semaphore id'
			);
		}

		register_shutdown_function(array($this, 'onShutdown'));

		pcntl_signal(SIGCHLD, array($this, 'onChildExited'));
		foreach(array(SIGTERM, SIGQUIT, SIGINT) as $nSignal) {
			pcntl_signal($nSignal, array($this, 'onSignal'));
		}
	}

	/**
	 * Checks if the server is running and calls signal handlers for pending signals.
	 *
	 * Example:
	 * @code
	 * while ($Server->run()) {
	 *     // do somethings...
	 *     usleep(200000);
	 * }
	 * @endcode
	 *
	 * @return @type boolean True if the server is running.
	 */
	public function run()
	{
		pcntl_signal_dispatch();
		return $this->_nRunningProcesses > 0;
	}

	/**
	 * Waits until a forked process has exited and decreases the current running
	 * process number.
	 */
	public function onChildExited()
	{
		while (pcntl_waitpid(-1, $nStatus, WNOHANG) > 0) {
			$this->_nRunningProcesses--;
		}
	}

	/**
	 * When a child (not the parent) receive a signal of type TERM, QUIT or INT
	 * exits from the current process and decreases the current running process number.
	 *
	 * @param  $nSignal @type integer Signal number.
	 */
	public function onSignal($nSignal)
	{
		switch ($nSignal) {
			case SIGTERM:
			case SIGQUIT:
			case SIGINT:
				if (($nPid = posix_getpid()) != $this->_nParentPid) {
					$this->_log("INFO: Child $nPid received signal #{$nSignal}, shutdown...");
					$this->_nRunningProcesses--;
					exit(0);
				}
				break;
			default:
				$this->_log("INFO: Ignored signal #{$nSignal}.");
				break;
		}
	}

	/**
	 * When the parent process exits, cleans shared memory and semaphore.
	 *
	 * This is called using 'register_shutdown_function' pattern.
	 * @see http://php.net/register_shutdown_function
	 */
	public function onShutdown()
	{
		if (posix_getpid() == $this->_nParentPid) {
			$this->_log('INFO: Parent shutdown, cleaning memory...');
			@shm_remove($this->_hShm) && @shm_detach($this->_hShm);
			@sem_remove($this->_hSem);
		}
	}

	/**
	 * Set the total processes to start, default is 3.
	 *
	 * @param  $nProcesses @type integer Processes to start up.
	 */
	public function setProcesses($nProcesses)
	{
		$nProcesses = (int)$nProcesses;
		if ($nProcesses <= 0) {
			return;
		}
		$this->_nProcesses = $nProcesses;
	}

	/**
	 * Starts the server forking all processes and return immediately.
	 *
	 * Every forked process is connected to Apple Push Notification Service on start
	 * and enter on the main loop.
	 */
	public function start()
	{
		for ($i = 0; $i < $this->_nProcesses; $i++) {
			$this->_nCurrentProcess = $i;
			$this->_aPids[$i] = $nPid = pcntl_fork();
			if ($nPid == -1) {
				$this->_log('WARNING: Could not fork');
			} else if ($nPid > 0) {
				// Parent process
				$this->_log("INFO: Forked process PID {$nPid}");
				$this->_nRunningProcesses++;
			} else {
				// Child process
				try {
					parent::connect();
				} catch (ApnsPHP_Exception $e) {
					$this->_log('ERROR: ' . $e->getMessage() . ', exiting...');
					exit(1);
				}
				$this->_mainLoop();
				parent::disconnect();
				exit(0);
			}
		}
	}

	/**
	 * Adds a message to the inter-process message queue.
	 *
	 * Messages are added to the queues in a round-robin fashion starting from the
	 * first process to the last.
	 *
	 * @param  $message @type ApnsPHP_Message The message.
	 */
	public function add(ApnsPHP_Message $message)
	{
		static $n = 0;
		if ($n >= $this->_nProcesses) {
			$n = 0;
		}
		sem_acquire($this->_hSem);
		$aQueue = $this->_getQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $n);
		$aQueue[] = $message;
		$this->_setQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $n, $aQueue);
		sem_release($this->_hSem);
		$n++;
	}

	/**
	 * Returns messages in the message queue.
	 *
	 * When a message is successful sent or reached the maximum retry time is removed
	 * from the message queue and inserted in the Errors container. Use the getErrors()
	 * method to retrive messages with delivery error(s).
	 *
	 * @param  $bEmpty @type boolean @optional Empty message queue.
	 * @return @type array Array of messages left on the queue.
	 */
	public function getQueue($bEmpty = true)
	{
		$aRet = array();
		sem_acquire($this->_hSem);
		for ($i = 0; $i < $this->_nProcesses; $i++) {
			$aRet = array_merge($aRet, $this->_getQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $i));
			if ($bEmpty) {
				$this->_setQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $i);
			}
		}
		sem_release($this->_hSem);
		return $aRet;
	}

	/**
	 * Returns messages not delivered to the end user because one (or more) error
	 * occurred.
	 *
	 * @param  $bEmpty @type boolean @optional Empty message container.
	 * @return @type array Array of messages not delivered because one or more errors
	 *         occurred.
	 */
	public function getErrors($bEmpty = true)
	{
		sem_acquire($this->_hSem);
		$aRet = $this->_getQueue(self::SHM_ERROR_MESSAGES_QUEUE_KEY);
		if ($bEmpty) {
			$this->_setQueue(self::SHM_ERROR_MESSAGES_QUEUE_KEY, 0, array());
		}
		sem_release($this->_hSem);
		return $aRet;
	}

	/**
	 * The process main loop.
	 *
	 * During the main loop: the per-process error queue is read and the common error message
	 * container is populated; the per-process message queue is spooled (message from
	 * this queue is added to ApnsPHP_Push queue and delivered).
	 */
	protected function _mainLoop()
	{
		while (true) {
			pcntl_signal_dispatch();

			if (posix_getppid() != $this->_nParentPid) {
				$this->_log("INFO: Parent process {$this->_nParentPid} died unexpectedly, exiting...");
				break;
			}

			sem_acquire($this->_hSem);
			$this->_setQueue(self::SHM_ERROR_MESSAGES_QUEUE_KEY, 0,
				array_merge($this->_getQueue(self::SHM_ERROR_MESSAGES_QUEUE_KEY), parent::getErrors())
			);

			$aQueue = $this->_getQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $this->_nCurrentProcess);
			foreach($aQueue as $message) {
				parent::add($message);
			}
			$this->_setQueue(self::SHM_MESSAGES_QUEUE_KEY_START, $this->_nCurrentProcess);
			sem_release($this->_hSem);

			$nMessages = count($aQueue);
			if ($nMessages > 0) {
				$this->_log('INFO: Process ' . ($this->_nCurrentProcess + 1) . " has {$nMessages} messages, sending...");
				parent::send();
			} else {
				usleep(self::MAIN_LOOP_USLEEP);
			}
		}
	}

	/**
	 * Returns the queue from the shared memory.
	 *
	 * @param  $nQueueKey @type integer The key of the queue stored in the shared
	 *         memory.
	 * @param  $nProcess @type integer @optional The process cardinal number.
	 * @return @type array Array of messages from the queue.
	 */
	protected function _getQueue($nQueueKey, $nProcess = 0)
	{
		if (!shm_has_var($this->_hShm, $nQueueKey + $nProcess)) {
			return array();
		}
		return shm_get_var($this->_hShm, $nQueueKey + $nProcess);
	}

	/**
	 * Store the queue into the shared memory.
	 *
	 * @param  $nQueueKey @type integer The key of the queue to store in the shared
	 *         memory.
	 * @param  $nProcess @type integer @optional The process cardinal number.
	 * @param  $aQueue @type array @optional The queue to store into shared memory.
	 *         The default value is an empty array, useful to empty the queue.
	 * @return @type boolean True on success, false otherwise.
	 */
	protected function _setQueue($nQueueKey, $nProcess = 0, $aQueue = array())
	{
		if (!is_array($aQueue)) {
			$aQueue = array();
		}
		return shm_put_var($this->_hShm, $nQueueKey + $nProcess, $aQueue);
	}
}// ####################################################### ./include/ApnsPHP/Push.php ####################################################### \\


/**
 * @file
 * ApnsPHP_Push class definition.
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/apns-php/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to aldo.armiento@gmail.com so we can send you a copy immediately.
 *
 * @author (C) 2010 Aldo Armiento (aldo.armiento@gmail.com)
 * @version $Id$
 */

/**
 * @defgroup ApnsPHP_Push Push
 * @ingroup ApplePushNotificationService
 */

/**
 * The Push Notification Provider.
 *
 * The class manages a message queue and sends notifications payload to Apple Push
 * Notification Service.
 *
 * @ingroup ApnsPHP_Push
 */
class ApnsPHP_Push extends ApnsPHP_Abstract
{
	const COMMAND_PUSH = 1; /**< @type integer Payload command. */

	const ERROR_RESPONSE_SIZE = 6; /**< @type integer Error-response packet size. */
	const ERROR_RESPONSE_COMMAND = 8; /**< @type integer Error-response command code. */

	const STATUS_CODE_INTERNAL_ERROR = 999; /**< @type integer Status code for internal error (not Apple). */

	protected $_aErrorResponseMessages = array(
		0   => 'No errors encountered',
		1   => 'Processing error',
		2   => 'Missing device token',
		3   => 'Missing topic',
		4   => 'Missing payload',
		5   => 'Invalid token size',
		6   => 'Invalid topic size',
		7   => 'Invalid payload size',
		8   => 'Invalid token',
		self::STATUS_CODE_INTERNAL_ERROR => 'Internal error'
	); /**< @type array Error-response messages. */

	protected $_nSendRetryTimes = 3; /**< @type integer Send retry times. */

	protected $_aServiceURLs = array(
		'tls://gateway.push.apple.com:2195', // Production environment
		'tls://gateway.sandbox.push.apple.com:2195' // Sandbox environment
	); /**< @type array Service URLs environments. */

	protected $_aMessageQueue = array(); /**< @type array Message queue. */
	protected $_aErrors = array(); /**< @type array Error container. */

	/**
	 * Set the send retry times value.
	 *
	 * If the client is unable to send a payload to to the server retries at least
	 * for this value. The default send retry times is 3.
	 *
	 * @param  $nRetryTimes @type integer Send retry times.
	 */
	public function setSendRetryTimes($nRetryTimes)
	{
		$this->_nSendRetryTimes = (int)$nRetryTimes;
	}

	/**
	 * Get the send retry time value.
	 *
	 * @return @type integer Send retry times.
	 */
	public function getSendRetryTimes()
	{
		return $this->_nSendRetryTimes;
	}

	/**
	 * Adds a message to the message queue.
	 *
	 * @param  $message @type ApnsPHP_Message The message.
	 */
	public function add(ApnsPHP_Message $message)
	{
		$sMessagePayload = $message->getPayload();
		$nRecipients = $message->getRecipientsNumber();

		$nMessageQueueLen = count($this->_aMessageQueue);
		for ($i = 0; $i < $nRecipients; $i++) {
			$nMessageID = $nMessageQueueLen + $i + 1;
			$this->_aMessageQueue[$nMessageID] = array(
				'MESSAGE' => $message,
				'BINARY_NOTIFICATION' => $this->_getBinaryNotification(
					$message->getRecipient($i),
					$sMessagePayload,
					$nMessageID,
					$message->getExpiry()
				),
				'ERRORS' => array()
			);
		}
	}

	/**
	 * Sends all messages in the message queue to Apple Push Notification Service.
	 *
	 * @throws ApnsPHP_Push_Exception if not connected to the
	 *         service or no notification queued.
	 */
	public function send()
	{
		if (!$this->_hSocket) {
			throw new ApnsPHP_Push_Exception(
				'Not connected to Push Notification Service'
			);
		}

		if (empty($this->_aMessageQueue)) {
			throw new ApnsPHP_Push_Exception(
				'No notifications queued to be sent'
			);
		}

		$this->_aErrors = array();
		$nRun = 1;
		while (($nMessages = count($this->_aMessageQueue)) > 0) {
			$this->_log("INFO: Sending messages queue, run #{$nRun}: $nMessages message(s) left in queue.");

			$bError = false;
			foreach($this->_aMessageQueue as $k => &$aMessage) {
				if (function_exists('pcntl_signal_dispatch')) {
					pcntl_signal_dispatch();
				}

				$message = $aMessage['MESSAGE'];
				$sCustomIdentifier = (string)$message->getCustomIdentifier();
				$sCustomIdentifier = sprintf('[custom identifier: %s]', empty($sCustomIdentifier) ? 'unset' : $sCustomIdentifier);

				$nErrors = 0;
				if (!empty($aMessage['ERRORS'])) {
					foreach($aMessage['ERRORS'] as $aError) {
						if ($aError['statusCode'] == 0) {
							$this->_log("INFO: Message ID {$k} {$sCustomIdentifier} has no error ({$aError['statusCode']}), removing from queue...");
							$this->_removeMessageFromQueue($k);
							continue 2;
						} else if ($aError['statusCode'] > 1 && $aError['statusCode'] <= 8) {
							$this->_log("WARNING: Message ID {$k} {$sCustomIdentifier} has an unrecoverable error ({$aError['statusCode']}), removing from queue without retrying...");
							$this->_removeMessageFromQueue($k, true);
							continue 2;
						}
					}
					if (($nErrors = count($aMessage['ERRORS'])) >= $this->_nSendRetryTimes) {
						$this->_log(
							"WARNING: Message ID {$k} {$sCustomIdentifier} has {$nErrors} errors, removing from queue..."
						);
						$this->_removeMessageFromQueue($k, true);
						continue;
					}
				}

				$nLen = strlen($aMessage['BINARY_NOTIFICATION']);
				$this->_log("STATUS: Sending message ID {$k} {$sCustomIdentifier} (" . ($nErrors + 1) . "/{$this->_nSendRetryTimes}): {$nLen} bytes.");

				$aErrorMessage = null;
				if ($nLen !== ($nWritten = (int)@fwrite($this->_hSocket, $aMessage['BINARY_NOTIFICATION']))) {
					$aErrorMessage = array(
						'identifier' => $k,
						'statusCode' => self::STATUS_CODE_INTERNAL_ERROR,
						'statusMessage' => sprintf('%s (%d bytes written instead of %d bytes)',
							$this->_aErrorResponseMessages[self::STATUS_CODE_INTERNAL_ERROR], $nWritten, $nLen
						)
					);
				}
				usleep($this->_nWriteInterval);

				$bError = $this->_updateQueue($aErrorMessage);
				if ($bError) {
					break;
				}
			}

			if (!$bError) {
				$read = array($this->_hSocket);
				$null = NULL;
				$nChangedStreams = @stream_select($read, $null, $null, 0, $this->_nSocketSelectTimeout);
				if ($nChangedStreams === false) {
					$this->_log('ERROR: Unable to wait for a stream availability.');
					break;
				} else if ($nChangedStreams > 0) {
					$bError = $this->_updateQueue();
					if (!$bError) {
						$this->_aMessageQueue = array();
					}
				} else {
					$this->_aMessageQueue = array();
				}
			}

			$nRun++;
		}
	}

	/**
	 * Returns messages in the message queue.
	 *
	 * When a message is successful sent or reached the maximum retry time is removed
	 * from the message queue and inserted in the Errors container. Use the getErrors()
	 * method to retrive messages with delivery error(s).
	 *
	 * @param  $bEmpty @type boolean @optional Empty message queue.
	 * @return @type array Array of messages left on the queue.
	 */
	public function getQueue($bEmpty = true)
	{
		$aRet = $this->_aMessageQueue;
		if ($bEmpty) {
			$this->_aMessageQueue = array();
		}
		return $aRet;
	}

	/**
	 * Returns messages not delivered to the end user because one (or more) error
	 * occurred.
	 *
	 * @param  $bEmpty @type boolean @optional Empty message container.
	 * @return @type array Array of messages not delivered because one or more errors
	 *         occurred.
	 */
	public function getErrors($bEmpty = true)
	{
		$aRet = $this->_aErrors;
		if ($bEmpty) {
			$this->_aErrors = array();
		}
		return $aRet;
	}

	/**
	 * Generate a binary notification from a device token and a JSON-encoded payload.
	 *
	 * @see http://tinyurl.com/ApplePushNotificationBinary
	 *
	 * @param  $sDeviceToken @type string The device token.
	 * @param  $sPayload @type string The JSON-encoded payload.
	 * @param  $nMessageID @type integer @optional Message unique ID.
	 * @param  $nExpire @type integer @optional Seconds, starting from now, that
	 *         identifies when the notification is no longer valid and can be discarded.
	 *         Pass a negative value (-1 for example) to request that APNs not store
	 *         the notification at all. Default is 86400 * 7, 7 days.
	 * @return @type string A binary notification.
	 */
	protected function _getBinaryNotification($sDeviceToken, $sPayload, $nMessageID = 0, $nExpire = 604800)
	{
		$nTokenLength = strlen($sDeviceToken);
		$nPayloadLength = strlen($sPayload);

		$sRet  = pack('CNNnH*', self::COMMAND_PUSH, $nMessageID, $nExpire > 0 ? time() + $nExpire : 0, self::DEVICE_BINARY_SIZE, $sDeviceToken);
		$sRet .= pack('n', $nPayloadLength);
		$sRet .= $sPayload;

		return $sRet;
	}

	/**
	 * Parses the error message.
	 *
	 * @param  $sErrorMessage @type string The Error Message.
	 * @return @type array Array with command, statusCode and identifier keys.
	 */
	protected function _parseErrorMessage($sErrorMessage)
	{
		return unpack('Ccommand/CstatusCode/Nidentifier', $sErrorMessage);
	}

	/**
	 * Reads an error message (if present) from the main stream.
	 * If the error message is present and valid the error message is returned,
	 * otherwhise null is returned.
	 *
	 * @return @type array|null Return the error message array.
	 */
	protected function _readErrorMessage()
	{
		$sErrorResponse = @fread($this->_hSocket, self::ERROR_RESPONSE_SIZE);
		if ($sErrorResponse === false || strlen($sErrorResponse) != self::ERROR_RESPONSE_SIZE) {
			return;
		}
		$aErrorResponse = $this->_parseErrorMessage($sErrorResponse);
		if (!is_array($aErrorResponse) || empty($aErrorResponse)) {
			return;
		}
		if (!isset($aErrorResponse['command'], $aErrorResponse['statusCode'], $aErrorResponse['identifier'])) {
			return;
		}
		if ($aErrorResponse['command'] != self::ERROR_RESPONSE_COMMAND) {
			return;
		}
		$aErrorResponse['time'] = time();
		$aErrorResponse['statusMessage'] = 'None (unknown)';
		if (isset($this->_aErrorResponseMessages[$aErrorResponse['statusCode']])) {
			$aErrorResponse['statusMessage'] = $this->_aErrorResponseMessages[$aErrorResponse['statusCode']];
		}
		return $aErrorResponse;
	}

	/**
	 * Checks for error message and deletes messages successfully sent from message queue.
	 *
	 * @param  $aErrorMessage @type array @optional The error message. It will anyway
	 *         always be read from the main stream. The latest successful message
	 *         sent is the lowest between this error message and the message that
	 *         was read from the main stream.
	 *         @see _readErrorMessage()
	 * @return @type boolean True if an error was received.
	 */
	protected function _updateQueue($aErrorMessage = null)
	{
		$aStreamErrorMessage = $this->_readErrorMessage();
		if (!isset($aErrorMessage) && !isset($aStreamErrorMessage)) {
			return false;
		} else if (isset($aErrorMessage, $aStreamErrorMessage)) {
			if ($aStreamErrorMessage['identifier'] <= $aErrorMessage['identifier']) {
				$aErrorMessage = $aStreamErrorMessage;
				unset($aStreamErrorMessage);
			}
		} else if (!isset($aErrorMessage) && isset($aStreamErrorMessage)) {
			$aErrorMessage = $aStreamErrorMessage;
			unset($aStreamErrorMessage);
		}

		$this->_log('ERROR: Unable to send message ID ' .
			$aErrorMessage['identifier'] . ': ' .
			$aErrorMessage['statusMessage'] . ' (' . $aErrorMessage['statusCode'] . ').');

		$this->disconnect();

		foreach($this->_aMessageQueue as $k => &$aMessage) {
			if ($k < $aErrorMessage['identifier']) {
				unset($this->_aMessageQueue[$k]);
			} else if ($k == $aErrorMessage['identifier']) {
				$aMessage['ERRORS'][] = $aErrorMessage;
			} else {
				break;
			}
		}

		$this->connect();

		return true;
	}

	/**
	 * Remove a message from the message queue.
	 *
	 * @param  $nMessageID @type integer The Message ID.
	 * @param  $bError @type boolean @optional Insert the message in the Error container.
	 * @throws ApnsPHP_Push_Exception if the Message ID is not valid or message
	 *         does not exists.
	 */
	protected function _removeMessageFromQueue($nMessageID, $bError = false)
	{
		if (!is_numeric($nMessageID) || $nMessageID <= 0) {
			throw new ApnsPHP_Push_Exception(
				'Message ID format is not valid.'
			);
		}
		if (!isset($this->_aMessageQueue[$nMessageID])) {
			throw new ApnsPHP_Push_Exception(
				"The Message ID {$nMessageID} does not exists."
			);
		}
		if ($bError) {
			$this->_aErrors[$nMessageID] = $this->_aMessageQueue[$nMessageID];
		}
		unset($this->_aMessageQueue[$nMessageID]);
	}
}
// ####################################################### ./include/libcompactmvc/actiondispatcher.php ####################################################### \\


/**
 * Action dispatcher
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ActionDispatcher extends InputSanitizer {
	private $handlers;
	private $handlersobj;
	private static $action_default;
	private static $action_control;
	private $last_controller;
	private $base_path_num;
	private static $current_route_id;
	private static $mapper;
	
	public function __construct($mapper = null) {
		$this->handlers = array();
		$this->handlersobj = array();
		if ($mapper != null) parent::__construct($mapper);
	}

	public static function set_default($route_id) {
		self::$action_default = $route_id;
	}

	public static function set_control($route_id) {
		self::$action_control = $route_id;
	}

	public function run() {
		$route_id = "";
		if (self::$action_control != "") {
			try {
				self::$current_route_id = self::$action_control;
				DLOG("EXECUTING CONTROL ACTION: " . self::$action_control);
				$ho = $this->get_handlersobj(self::$action_control);
				$ho->set_base_path($this->base_path_num);
				DLOG("CONTROLER TYPE: " . get_class($ho));
				$ho->get_view()->clear();
				$ho->run();
				$this->last_controller = $ho;
			} catch (RBRCException $rbrce) {
				DLOG("Returning response from the RBRC.");
			} catch (RedirectException $re) {
				if ($re->is_internal()) {
					if ($ho->get_redirect() != "") {
						$route_id = $ho->get_redirect();
					}
				}
			}
		}
		do {
			$route_id = ($route_id == "") ? $this->get_action_mapper()->get_route_id() : $route_id;
			$route_id = ($route_id == "" && $this->get_action_mapper()->get_route_id()) ? self::$action_default : $route_id;
			self::$current_route_id = $route_id;
			$ho = $this->get_handlersobj($route_id);
			$ho->set_base_path($this->base_path_num);
			DLOG("EXECUTING MAIN ACTION: " . $route_id);
			DLOG("CONTROLER TYPE: " . get_class($ho));
			try {
				$ho->get_view()->clear();
				$ho->run();
				$this->last_controller = $ho;
			} catch (RBRCException $rbrce) {
				DLOG("Returning response from the RBRC.");
			} catch (RedirectException $re) {
				if ($re->is_internal()) {
					if ($ho->get_redirect() != "") {
						$route_id = $ho->get_redirect();
					}
				}
			}
		} while ($ho->get_redirect() != "");
	}

	public function get_ob() {
		return $this->last_controller->get_ob();
	}

	public function get_mime_type() {
		return $this->last_controller->get_mime_type();
	}

	public static function get_action_mapper() {
		return self::$action_mapper;
	}
	
	public static function set_action_mapper(ActionMapperInterface $mapper) {
		self::$action_mapper = $mapper;
	}

/**
	 * 
	 * @param boolean $action true for automatic detection via get_requested_controller(), "" and self::$action_default for defautl ctrlr, self::$action_control for access control controller.
	 * @throws Exception
	 * @return CMVCController
	 */
	private function get_handlersobj($route_id = true) {
		$id_used = "";
		if ($route_id == "") {
			$route_id = self::$action_default;
		}
		$handler = $this->get_action_mapper()->get_link_property_by_route_id($route_id)->get_controller_name();
		$id_used = $route_id;
		if ($handler == "") {
			$handler = $this->get_action_mapper()->get_link_property_by_route_id(self::$action_default)->get_controller_name();
			$id_used = self::$action_default;
		}
		$this->base_path_num = $this->get_action_mapper()->get_link_property_by_route_id($id_used)->get_base_path_num();
		DLOG("id_used  = $id_used");
		DLOG("handler  = $handler");
		DLOG("base path depth = " . $this->base_path_num);
		if (array_key_exists($id_used, $this->handlersobj)) {
			DLOG("Retrieved object from cache.");
			return $this->handlersobj[$id_used];
		}
		DLOG("First use of this route, instantiating new $handler().");
		$ret = new $handler();
		$this->handlersobj[$id_used] = $ret;
		if (!is_subclass_of($this->handlersobj[$id_used], "CMVCController")) {
			unset($this->handlersobj[$id_used]);
			throw new Exception("ActionDispatcher::get_handlersobj(\"$action\"): Class must be a subclass of CMVCController.");
		}
		return $ret;
	}
	
	public static function get_current_route_id() {
		DLOG();
		return self::$current_route_id;
	}
	
	
}
// ####################################################### ./include/libcompactmvc/actionmapper.php ####################################################### \\


/**
 * actionmapper.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package 	LibCompactMVC
 * @copyright 	Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link 		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class ActionMapper extends Singleton implements ActionMapperInterface {
	private $urllist;
	private $rev_path0;
	private $rev_path1;
	private static $rev_route_lookup;
	private $base_path_num;
	protected $mapping2;
	protected $mapping3;

	protected function __construct() {
		DLOG();
		parent::__construct();
		$this->mapping2 = array();
		$this->mapping3 = array();
		self::$rev_route_lookup = array();
		$this->rev_path0 = array();
		$this->rev_path1 = array();
		$this->register_internal_endpoints();
		$this->register_endpoints();
		$GLOBALS["SITEMAP"] = array();
		$m2 = $this->get_mapping_2();
		$m3 = $this->get_mapping_3();
		array_walk_recursive($m2, "add_to_sitemap");
		array_walk_recursive($m3, "add_to_sitemap");
	}
	
	/**
	 * 
	 * @param unknown $a
	 * @param unknown $b
	 * @param unknown $c
	 * @param unknown $d
	 * @param unknown $e
	 * @param unknown $f
	 * @param unknown $g
	 * @param unknown $h
	 * @param unknown $i
	 * @param unknown $j
	 * @param unknown $k
	 * @param unknown $l
	 * @param unknown $m
	 * @param unknown $n
	 * @param unknown $o
	 * @param unknown $p
	 * @return ActionMapper
	 */
	public static function get_instance($a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null, $j = null, $k = null, $l = null, $m = null, $n = null, $o = null, $p = null) {
		return parent::get_instance($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
	}
	

	/**
	 * Overwrite this method and register all endpoints via calls to register_ep_*() in there.
	 */
	abstract protected function register_endpoints();

	/**
	 *
	 * @param string $lang
	 * @param string $path0
	 * @param LinkProperty $lprop
	 */
	protected function register_ep_2($lang, $path0, LinkProperty $lprop) {
		DLOG("('$lang', '$path0')");
		if (array_key_exists($lang, $this->mapping2) && is_array($this->mapping2[$lang])) {
			if (array_key_exists($path0, $this->mapping2[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered: lang = '$lang', path0 = '$path0'", 500);
			}
		}
		if (array_key_exists($lang, $this->mapping3) && is_array($this->mapping3[$lang])) {
			if (array_key_exists($path0, $this->mapping3[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered on a deeper level: lang = '$lang', path0 = '$path0'", 500);
			}
		}
		if (array_key_exists($lprop->get_path_level(0), $this->rev_path0)) {
			if ($this->rev_path0[$lprop->get_path_level(0)] != $path0) {
				throw new ActionMapperException("Ambiguous path mapping: path0 '" . $lprop->get_path_level(0) . "' mapps to '" . $this->rev_path0[$lprop->get_path_level(0)] . "', '" . $path0 . "' and maybe others. Stopping reverse path resolution.", 500);
			}
		}
		$lprop->set_base_path_num(0);
		$this->mapping2[$lang][$path0] = $lprop;
		$this->rev_path0[$lprop->get_path_level(0)] = $path0;
		self::$rev_route_lookup[route_id($path0, null, "", $lang)] = $lprop;
	}

	/**
	 *
	 * @param string $lang
	 * @param string $path0
	 * @param string $path1
	 * @param LinkProperty $lprop
	 */
	protected function register_ep_3($lang, $path0, $path1, LinkProperty $lprop) {
		DLOG("('$lang', '$path0', '$path1')");
		if (array_key_exists($lang, $this->mapping2) && is_array($this->mapping2[$lang])) {
			if (array_key_exists($path0, $this->mapping2[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered on a higher level: lang = '$lang', path0 = '$path0', (path1 = '$path1')", 500);
			}
		}
		if (@array_key_exists($lang, $this->mapping3) && @is_array($this->mapping3[$lang]) && @is_array($this->mapping3[$lang][$path0])) {
			if (array_key_exists($path0, $this->mapping3[$lang]) && array_key_exists($path1, $this->mapping3[$lang][$path0])) {
				throw new ActionMapperException("Path mapping is allready registered: lang = '$lang', path0 = '$path0', path1 = '$path1'", 500);
			}
		}
		if (array_key_exists($lprop->get_path_level(1), $this->rev_path1)) {
			if ($this->rev_path1[$lprop->get_path_level(1)] != $path1) {
				throw new ActionMapperException("Ambiguous path mapping: path1 '" . $lprop->get_path_level(1) . "' mapps to '" . $this->rev_path1[$lprop->get_path_level(1)] . "', '" . $path1 . "' and maybe others. Stopping reverse path resolution.", 500);
			}
		}
		$lprop->set_base_path_num(1);
		$this->mapping3[$lang][$path0][$path1] = $lprop;
		$this->rev_path1[$lprop->get_path_level(1)] = $path1;
		self::$rev_route_lookup[route_id($path0, $path1, "", $lang)] = $lprop;
	}

	/**
	 * 
	 * @param string $id
	 * @return LinkProperty
	 */
	public function get_link_property_by_route_id($inid) {
		$id = $inid;
		DLOG("('$id')");
		while (strlen($id) > 0 && !array_key_exists($id, self::$rev_route_lookup)) {
			DLOG($id);
			if (!array_key_exists($id, self::$rev_route_lookup)) {
				$arr = explode(".", $id);
				unset($arr[count($arr) - 1]);
				$id = implode(".", $arr);
			}
		}
		if (!array_key_exists($id, self::$rev_route_lookup)) {
			throw new ActionMapperException("Route id '$inid' could not be resolved.", 404);
		}
		return self::$rev_route_lookup[$id];
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_route_id() {
		$tmp = "";
		try {
			$tmp = route_id(InputProvider::get_instance()->get_var("path0"));
			$tmp = route_id(InputProvider::get_instance()->get_var("path0"), InputProvider::get_instance()->get_var("path1"));
		} catch (InvalidMemberException $e) {
		}
		return $tmp;
	}

	/**
	 *
	 * @return 2-dimensional array containing the paths
	 */
	protected function get_mapping_2() {
		return $this->mapping2;
	}

	/**
	 *
	 * @return 3-dimensional array containing the paths
	 */
	protected function get_mapping_3() {
		return $this->mapping3;
	}

	/**
	 * (non-PHPdoc)
	 *
	 * @see ActionMapperInterface::get_base_url()
	 */
	abstract public function get_base_url();

	/**
	 * (non-PHPdoc)
	 *
	 * @see ActionMapperInterface::get_path()
	 */
	public function get_path($lang, $path0 = null, $path1 = null, $urltail = null) {
		DLOG("lang = $lang, path0 = $path0, path1 = $path1, urltail = $urltail");
		$lnk = "";
		if ($path0 != null && $path1 == null) {
			$arr = $this->get_mapping_2();
			$GLOBALS["FATAL_ERR_MSG"] = "lang = $lang, path0 = $path0";
			if (!array_key_exists($lang, $arr)) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			} else if (!array_key_exists($path0, $arr[$lang])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			}
			$lnk = $arr[$lang][$path0]->get_path();
		}
		if ($path0 != null && $path1 != null) {
			$arr = $this->get_mapping_3();
			$GLOBALS["FATAL_ERR_MSG"] = "lang = $lang, path0 = $path0, path1 = $path1";
			if (!array_key_exists($lang, $arr)) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			} else if (!array_key_exists($path0, $arr[$lang])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			} else if (!array_key_exists($path1, $arr[$lang][$path0])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			}
			$lnk = $arr[$lang][$path0][$path1]->get_path();
		}
		if ($lnk == "") {
			$lnk = $this->get_base_url();
		}
		if ($urltail != "") {
			$lnk .= $urltail;
		}
		return $lnk;
	}

	/**
	 * Returns the text-sitemap. Can directly be returned to the client.
	 * 
	 * @return string
	 */
	public function get_sitemap() {
		DLOG();
		$urls = "";
		foreach ($GLOBALS["SITEMAP"] as $url)
			$urls .= BASE_URL . $url . "\n";
		return $urls;
	}

	/**
	 * Reverse translates the path0 variable (SEO value > internal value)
	 * 
	 * @param unknown $path0
	 * @return unknown|mixed
	 */
	public function reverse_path0($path0, $nolog = false) {
		if (!$nolog) DLOG(var_export($this->rev_path0, true));
		$tr = (array_key_exists($path0, $this->rev_path0)) ? $this->rev_path0[$path0] : $path0;
		if (!$nolog) DLOG("'$path0' translates back to '$tr'");
		return $tr;
	}

	/**
	 * Reverse translates the path1 variable (SEO value > internal value)
	 * 
	 * @param unknown $path1
	 * @return unknown|mixed
	 */
	public function reverse_path1($path1, $nolog = false) {
		if (!$nolog) DLOG(var_export($this->rev_path1, true));
		$tr = (array_key_exists($path1, $this->rev_path1)) ? $this->rev_path1[$path1] : $path1;
		if (!$nolog) DLOG("'$path1' translates back to '$tr'");
		return $tr;
	}
	
	private function register_internal_endpoints() {
		DLOG();
		$lang = InputProvider::get_instance()->get_var("lang");
		$this->register_ep_2($lang, "sys", new LinkProperty("/" . $lang . "/sys", false, "CMVCSystem"));
		$this->register_ep_3($lang, "sysint", "ormclientcomponent", new LinkProperty("/" . $lang . "/sysint/ormclient.js", false, "ORMClientComponent"));
	}

}
// ####################################################### ./include/libcompactmvc/actionmapperexception.php ####################################################### \\


/**
 * actionmapperexception.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ActionMapperException extends Exception {

}
// ####################################################### ./include/libcompactmvc/activesessions.php ####################################################### \\


/**
 * activesessions.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ActiveSessions extends Singleton {

	protected function __construct() {
		DLOG();
		parent::__construct();
		$this->update();
	}

	/**
	 *
	 * @return ActiveSessions returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null, $j = null, $k = null, $l = null, $m = null, $n = null, $o = null, $p = null) {
		return parent::get_instance($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
	}

	public function update() {
		DLOG();
		$ra = RedisAdapter::get_instance();
		$key = "ACTIVESESSIONS_" . Session::get_instance()->get_id();
		if ($key == "ACTIVESESSIONS_")
			return;
		$val = $ra->get($key);
		if (is_numeric($val))
			$val += ACTIVESESSIONS_HIT_INCR;
		else
			$val = ACTIVESESSIONS_HIT_INCR;
		$val = ($val > ACTIVESESSIONS_MAX_HITS) ? ACTIVESESSIONS_MAX_HITS : $val;
		$ra->set($key, $val);
		$ra->expire($key, $val);
		DLOG("Setting key ". $key . " to " . $val);
	}

	public function get_session_count() {
		DLOG();
		$ids = array();
		$keys = RedisAdapter::get_instance()->keys("ACTIVESESSIONS_*");
		$count = 0;
		foreach ($keys as $k) {
			if (RedisAdapter::get_instance()->get($k) > ACTIVESESSIONS_MIN_HITS)
				$count++;
		}
		return $count;
	}

}
// ####################################################### ./include/libcompactmvc/applepushnotification.php ####################################################### \\


/**
 * Class for sending Apple push notifications
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ApplePushNotification {
	private $certpath;
	private $device_token;

	public function __construct($certpath) {
		DLOG();
		$this->certpath = $certpath;
	}

	public function alert($device_token, $message, $badge = 1, $sound = "default") {
		DLOG();
		// Payload erstellen und JSON codieren
		$payload['aps'] = array(
				'alert' => $message,
				'badge' => $badge,
				'sound' => $sound
		);
		$this->send($payload, $device_token);
	}

	private function send($payload, $device_token) {
		DLOG();
		$payload = json_encode($payload);
		
		$apnsHost = 'gateway.sandbox.push.apple.com';
		$apnsPort = 2195;
		
		// Stream erstellen
		$streamContext = stream_context_create();
		stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certpath);
		
		$apns = stream_socket_client('ssl://' . $apnsHost . ':' . $apnsPort, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		if ($apns) {
			// Nachricht erstellen und senden
			$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', str_replace(' ', '', $device_token)) . chr(0) . chr(strlen($payload)) . $payload;
			fwrite($apns, $apnsMessage);
			
			// Verbindung schliessen
			fclose($apns);
		} else {
			throw new Exception($errorString, $error);
		}
	}

}
// ####################################################### ./include/libcompactmvc/cachedhttprequest.php ####################################################### \\


/**
 * Cached HTTP request.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class CachedHttpRequest {
	private $ttl;
	private $caching_enabled;

	/**
	 *
	 * @param string $ttl        	
	 */
	public function __construct($ttl = REDIS_KEY_CACHEDHTTP_TTL) {
		DLOG();
		$this->ttl = $ttl;
		$this->caching_enabled = true;
	}

	/**
	 *
	 * @param String $url        	
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function get($url, $caching = "default") {
		DLOG("GET " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 *
	 * @param unknown $url        	
	 * @param unknown $vars        	
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function post($url, $vars = array(), $caching = "default") {
		DLOG("POST " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize(array(
				$url,
				$vars
		)));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		curl_setopt($curl, CURLOPT_POST, count($vars));
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($vars));
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 *
	 * @param unknown $url        	
	 * @param unknown $vars        	
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function put($url, $vars = array(), $caching = "default") {
		DLOG("PUT " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($vars));
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 *
	 * @param String $url
	 *        	URL to send the request to.
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function delete($url, $caching = "default") {
		DLOG("DELETE " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 * Delete one or all entries from cache
	 *
	 * @param string $url
	 *        	URL of the cache entry
	 */
	public function flush($url = null) {
		DLOG("FLUSH " . (($url == null) ? "ALL" : $url));
		if ($url != null) {
			$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
			RedisAdapter::get_instance()->delete($key);
		} else {
			$keys = RedisAdapter::get_instance()->keys(REDIS_KEY_CACHEDHTTP_PFX . "*");
			foreach ($keys as $k) {
				RedisAdapter::get_instance()->delete($k);
			}
		}
	}

	/**
	 *
	 * @param boolean $caching
	 *        	Set the caching mode. Give null here to just retrieve the current status.
	 */
	public function caching_enabled($caching = null) {
		if ($caching !== null) {
			$this->caching_enabled = $caching;
		}
		return $this->caching_enabled;
	}

}
// ####################################################### ./include/libcompactmvc/captcha.php ####################################################### \\


/**
 * Captcha class
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Captcha {
	
	/**
	 * Width of the image
	 */
	public $width = 200;
	
	/**
	 * Height of the image
	 */
	public $height = 70;
	
	/**
	 * Dictionary word file (empty for random text)
	 */
	public $wordsFile = 'words/en.php';
	
	/**
	 * Path for resource files (fonts, words, etc.)
	 *
	 * "resources" by default. For security reasons, is better move this
	 * directory to another location outise the web server
	 */
	public $resourcesPath = CAPTCHA_RES_PATH;
	
	/**
	 * Min word length (for non-dictionary random text generation)
	 */
	public $minWordLength = 5;
	
	/**
	 * Max word length (for non-dictionary random text generation)
	 *
	 * Used for dictionary words indicating the word-length
	 * for font-size modification purposes
	 */
	public $maxWordLength = 8;
	
	/**
	 * Sessionname to store the original text
	 */
	public $session_var = ST_CAPTCHA_SESS_VAR;
	
	/**
	 * Background color in RGB-array
	 */
	public $backgroundColor = array(
			255,
			255,
			255
	);
	
	/**
	 * Foreground colors in RGB-array
	 */
	public $colors = array(
			array(
					27,
					78,
					181
			), // blue
			array(
					22,
					163,
					35
			), // green
			array(
					214,
					36,
					7
			)
	); // red

	
	/**
	 * Shadow color in RGB-array or null
	 */
	public $shadowColor = null; // array(0, 0, 0);
	
	/**
	 * Horizontal line through the text
	 */
	// array(0, 0, 0);
	public $lineWidth = 0;
	
	/**
	 * Font configuration
	 *
	 * - font: TTF file
	 * - spacing: relative pixel space between character
	 * - minSize: min font size
	 * - maxSize: max font size
	 */
	public $fonts = array(
			'Antykwa' => array(
					'spacing' => -3,
					'minSize' => 27,
					'maxSize' => 30,
					'font' => 'AntykwaBold.ttf'
			),
			'Candice' => array(
					'spacing' => -1.5,
					'minSize' => 28,
					'maxSize' => 31,
					'font' => 'Candice.ttf'
			),
			'DingDong' => array(
					'spacing' => -2,
					'minSize' => 24,
					'maxSize' => 30,
					'font' => 'Ding-DongDaddyO.ttf'
			),
			'Duality' => array(
					'spacing' => -2,
					'minSize' => 30,
					'maxSize' => 38,
					'font' => 'Duality.ttf'
			),
			'Heineken' => array(
					'spacing' => -2,
					'minSize' => 24,
					'maxSize' => 34,
					'font' => 'Heineken.ttf'
			),
			'Jura' => array(
					'spacing' => -2,
					'minSize' => 28,
					'maxSize' => 32,
					'font' => 'Jura.ttf'
			),
			'StayPuft' => array(
					'spacing' => -1.5,
					'minSize' => 28,
					'maxSize' => 32,
					'font' => 'StayPuft.ttf'
			),
			'Times' => array(
					'spacing' => -2,
					'minSize' => 28,
					'maxSize' => 34,
					'font' => 'TimesNewRomanBold.ttf'
			),
			'VeraSans' => array(
					'spacing' => -1,
					'minSize' => 20,
					'maxSize' => 28,
					'font' => 'VeraSansBold.ttf'
			)
	);
	
	/**
	 * Wave configuracion in X and Y axes
	 */
	public $Yperiod = 12;
	public $Yamplitude = 14;
	public $Xperiod = 11;
	public $Xamplitude = 5;
	
	/**
	 * letter rotation clockwise
	 */
	public $maxRotation = 8;
	
	/**
	 * Internal image size factor (for better image quality)
	 * 1: low, 2: medium, 3: high
	 */
	public $scale = 3;
	
	/**
	 * Blur effect for better image quality (but slower image processing).
	 * Better image results with scale=3
	 */
	public $blur = true;
	
	/**
	 * Debug?
	 */
	public $debug = false;
	
	/**
	 * Image format: jpeg or png
	 */
	public $imageFormat = 'png';
	
	/**
	 * GD image
	 */
	public $im;

	public function __construct($config = array()) {
	}

	public function CreateImage() {
		$ini = microtime(true);
		
		/**
		 * Initialization
		 */
		$this->ImageAllocate();
		
		/**
		 * Text insertion
		 */
		$text = $this->GetCaptchaText();
		$fontcfg = $this->fonts[array_rand($this->fonts)];
		$this->WriteText($text, $fontcfg);
		
		Session::get_instance()->set_property($this->session_var, $text);
		// $_SESSION[$this->session_var] = $text;
		
		/**
		 * Transformations
		 */
		if (!empty($this->lineWidth)) {
			$this->WriteLine();
		}
		$this->WaveImage();
		if ($this->blur && function_exists('imagefilter')) {
			$res = imagefilter($this->im, IMG_FILTER_GAUSSIAN_BLUR);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
		$this->ReduceImage();
		
		if ($this->debug) {
			$res = imagestring($this->im, 1, 1, $this->height - 8, "$text {$fontcfg['font']} " . round((microtime(true) - $ini) * 1000) . "ms", $this->GdFgColor);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
		
		/**
		 * Output
		 */
		$out = $this->WriteImage();
		$this->Cleanup();
		return $out;
	}

	public function GetText() {
		return Session::get_instance()->get_property($this->session_var);
	}

	/**
	 * Creates the image resources
	 */
	protected function ImageAllocate() {
		// Cleanup
		if (!empty($this->im)) {
			$res = imagedestroy($this->im);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
		
		$this->im = imagecreatetruecolor($this->width * $this->scale, $this->height * $this->scale);
		if ($this->im === false) {
			throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
		}
		
		// Background color
		$this->GdBgColor = imagecolorallocate($this->im, $this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);
		$res = imagefilledrectangle($this->im, 0, 0, $this->width * $this->scale, $this->height * $this->scale, $this->GdBgColor);
		if ($res === false) {
			throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
		}
		
		// Foreground color
		$color = $this->colors[mt_rand(0, sizeof($this->colors) - 1)];
		$this->GdFgColor = imagecolorallocate($this->im, $color[0], $color[1], $color[2]);
		if ($this->GdFgColor === false) {
			throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
		}
		
		// Shadow color
		if (!empty($this->shadowColor) && is_array($this->shadowColor) && sizeof($this->shadowColor) >= 3) {
			$this->GdShadowColor = imagecolorallocate($this->im, $this->shadowColor[0], $this->shadowColor[1], $this->shadowColor[2]);
			if ($this->GdShadowColor === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
	}

	/**
	 * Text generation
	 *
	 * @return string Text
	 */
	protected function GetCaptchaText() {
		$text = $this->GetDictionaryCaptchaText();
		if (!$text) {
			$text = $this->GetRandomCaptchaText();
		}
		return $text;
	}

	/**
	 * Random text generation
	 *
	 * @return string Text
	 */
	protected function GetRandomCaptchaText($length = null) {
		if (empty($length)) {
			$length = rand($this->minWordLength, $this->maxWordLength);
		}
		
		$words = "abcdefghijlmnopqrstvwyz";
		$vocals = "aeiou";
		
		$text = "";
		$vocal = rand(0, 1);
		for($i = 0; $i < $length; $i++) {
			if ($vocal) {
				$text .= substr($vocals, mt_rand(0, 4), 1);
			} else {
				$text .= substr($words, mt_rand(0, 22), 1);
			}
			$vocal = !$vocal;
		}
		return $text;
	}

	/**
	 * Random dictionary word generation
	 *
	 * @param boolean $extended
	 *        	Add extended "fake" words
	 * @return string Word
	 */
	function GetDictionaryCaptchaText($extended = false) {
		if (empty($this->wordsFile)) {
			return false;
		}
		
		// Full path of words file
		if (substr($this->wordsFile, 0, 1) == '/') {
			$wordsfile = $this->wordsFile;
		} else {
			$wordsfile = $this->resourcesPath . '/' . $this->wordsFile;
		}
		
		if (!file_exists($wordsfile)) {
			return false;
		}
		
		$fp = fopen($wordsfile, "r");
		$length = strlen(fgets($fp));
		if (!$length) {
			return false;
		}
		$line = rand(1, (filesize($wordsfile) / $length) - 2);
		if (fseek($fp, $length * $line) == -1) {
			return false;
		}
		$text = trim(fgets($fp));
		fclose($fp);
		
		/**
		 * Change ramdom volcals
		 */
		if ($extended) {
			$text = preg_split('//', $text, -1, PREG_SPLIT_NO_EMPTY);
			$vocals = array(
					'a',
					'e',
					'i',
					'o',
					'u'
			);
			foreach ($text as $i => $char) {
				if (mt_rand(0, 1) && in_array($char, $vocals)) {
					$text[$i] = $vocals[mt_rand(0, 4)];
				}
			}
			$text = implode('', $text);
		}
		
		return $text;
	}

	/**
	 * Horizontal line insertion
	 */
	protected function WriteLine() {
		$x1 = $this->width * $this->scale * .15;
		$x2 = $this->textFinalX;
		$y1 = rand($this->height * $this->scale * .40, $this->height * $this->scale * .65);
		$y2 = rand($this->height * $this->scale * .40, $this->height * $this->scale * .65);
		$width = $this->lineWidth / 2 * $this->scale;
		
		for($i = $width * -1; $i <= $width; $i++) {
			$res = imageline($this->im, $x1, $y1 + $i, $x2, $y2 + $i, $this->GdFgColor);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
	}

	/**
	 * Text insertion
	 */
	protected function WriteText($text, $fontcfg = array()) {
		if (empty($fontcfg)) {
			// Select the font configuration
			$fontcfg = $this->fonts[array_rand($this->fonts)];
		}
		
		// Full path of font file
		$fontfile = $this->resourcesPath . '/fonts/' . $fontcfg['font'];
		
		/**
		 * Increase font-size for shortest words: 9% for each glyp missing
		 */
		$lettersMissing = $this->maxWordLength - strlen($text);
		$fontSizefactor = 1 + ($lettersMissing * 0.09);
		
		// Text generation (char by char)
		$x = 20 * $this->scale;
		$y = round(($this->height * 27 / 40) * $this->scale);
		$length = strlen($text);
		for($i = 0; $i < $length; $i++) {
			$degree = rand($this->maxRotation * -1, $this->maxRotation);
			$fontsize = rand($fontcfg['minSize'], $fontcfg['maxSize']) * $this->scale * $fontSizefactor;
			$letter = substr($text, $i, 1);
			
			if ($this->shadowColor) {
				$coords = imagettftext($this->im, $fontsize, $degree, $x + $this->scale, $y + $this->scale, $this->GdShadowColor, $fontfile, $letter);
				if ($coords === false) {
					throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
				}
			}
			$coords = imagettftext($this->im, $fontsize, $degree, $x, $y, $this->GdFgColor, $fontfile, $letter);
			if ($coords === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
			$x += ($coords[2] - $x) + ($fontcfg['spacing'] * $this->scale);
		}
		
		$this->textFinalX = $x;
	}

	/**
	 * Wave filter
	 */
	protected function WaveImage() {
		// X-axis wave generation
		$xp = $this->scale * $this->Xperiod * rand(1, 3);
		$k = rand(0, 100);
		for($i = 0; $i < ($this->width * $this->scale); $i++) {
			$res = imagecopy($this->im, $this->im, $i - 1, sin($k + $i / $xp) * ($this->scale * $this->Xamplitude), $i, 0, 1, $this->height * $this->scale);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
		
		// Y-axis wave generation
		$k = rand(0, 100);
		$yp = $this->scale * $this->Yperiod * rand(1, 2);
		for($i = 0; $i < ($this->height * $this->scale); $i++) {
			$res = imagecopy($this->im, $this->im, sin($k + $i / $yp) * ($this->scale * $this->Yamplitude), $i - 1, 0, $i, $this->width * $this->scale, 1);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
	}

	/**
	 * Reduce the image to the final size
	 */
	protected function ReduceImage() {
		// Reduzco el tamaÒo de la imagen
		$imResampled = imagecreatetruecolor($this->width, $this->height);
		if ($imResampled === false) {
			throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
		}
		$res = imagecopyresampled($imResampled, $this->im, 0, 0, 0, 0, $this->width, $this->height, $this->width * $this->scale, $this->height * $this->scale);
		if ($res === false) {
			throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
		}
		$res = imagedestroy($this->im);
		if ($res === false) {
			throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
		}
		$this->im = $imResampled;
	}

	/**
	 * File generation
	 */
	protected function WriteImage() {
		$ret = null;
		$img = tempnam(TEMP_DIR, "img_");
		if ($this->imageFormat == 'png' && function_exists('imagepng')) {
			DLOG("Sending image in PNG format.");
			header("Content-type: image/png");
			$res = imagepng($this->im, $img);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		} else {
			DLOG("Sending image in JPG format.");
			header("Content-type: image/jpeg");
			$res = imagejpeg($this->im, $img, 80);
			if ($res === false) {
				throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
			}
		}
		$ret = file_get_contents($img);
		unlink($img);
		return $ret;
	}

	/**
	 * Cleanup
	 */
	protected function Cleanup() {
		$res = imagedestroy($this->im);
		if ($res === false) {
			throw new Exception(__METHOD__ . ": Error in line " . __LINE__);
		}
	}

}
// ####################################################### ./include/libcompactmvc/centermap.php ####################################################### \\


/**
 * Calculates approximately the center of a rectangle
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class CenterMap {
	// this class will only work for germany!!!
	// there may occur unexpected results for other areas on our globe...
	private $max_lat;
	private $min_lat;
	private $max_lng;
	private $min_lng;

	public function __construct() {
	}

	public function add_element($lat, $lng) {
		if ($this->max_lat == null) {
			$this->max_lat = $lat;
		}
		if ($this->min_lat == null) {
			$this->min_lat = $lat;
		}
		if ($this->max_lng == null) {
			$this->max_lng = $lng;
		}
		if ($this->min_lng == null) {
			$this->min_lng = $lng;
		}
		if ($lat > $this->max_lat) {
			$this->max_lat = $lat;
		}
		if (($lat < $this->min_lat) && ($lat != null)) {
			$this->min_lat = $lat;
		}
		if ($lng > $this->max_lng) {
			$this->max_lng = $lng;
		}
		if (($lng < $this->min_lng) && ($lng != null)) {
			$this->min_lng = $lng;
		}
	}

	public function get_center() {
		$ret['lat'] = $this->min_lat + (($this->max_lat - $this->min_lat) / 2);
		$ret['lng'] = $this->min_lng + (($this->max_lng - $this->min_lng) / 2);
		return $ret;
	}

	public function get_zoom() {
		$diff_lat = $this->max_lat - $this->min_lat;
		$diff_lng = $this->max_lng - $this->min_lng;
		$rel_lat = $diff_lat;
		$rel_lng = $diff_lng * 1.5;
		$diff = ($rel_lat > $rel_lng) ? $rel_lat : $rel_lng;
		$zoom = round(9.6 + ($diff * $diff) / 25 - $diff);
		$zoom = ($zoom < 5) ? 5 : $zoom;
		return $zoom;
	}

}

// ####################################################### ./include/libcompactmvc/cephadapter.php ####################################################### \\


/**
 * cephadapter.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class CephAdapter extends Singleton {
	private $rados;
	private $ctx;

	protected function __construct() {
		DLOG();
		parent::__construct();
		if (!extension_loaded("rados")) return;
		$this->rados = rados_create();
		rados_conf_read_file($this->rados, CEPH_CONF);
		if (!rados_connect($this->rados))
			throw new Exception("Could not connect to rados (ceph cluster)");
		$this->ctx = rados_ioctx_create($this->rados, CEPH_POOL);
	}

	public function __destruct() {
		DLOG();
		parent::__destruct();
		if (!extension_loaded("rados")) return;
		rados_ioctx_destroy($this->ctx);
		rados_shutdown($this->rados);
	}

	/**
	 *
	 * @return CephAdapter
	 */
	public static function get_instance($a = NULL, $b = NULL, $c = NULL, $d = NULL, $e = NULL, $f = NULL, $g = NULL, $h = NULL, $i = NULL, $j = NULL, $k = NULL, $l = NULL, $m = NULL, $n = NULL, $o = NULL, $p = NULL) {
		DLOG();
		return parent::get_instance($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
	}

	public function put($oid, $buf) {
		DLOG($oid);
		if (!extension_loaded("rados")) return;
		$res = rados_write_full($this->ctx, $oid, $buf);
		return $res;
	}

	public function get($oid) {
		DLOG($oid);
		if (!extension_loaded("rados")) return "";
		$buf = rados_read($this->ctx, $oid, CEPH_MAX_OBJ_SIZE);
		if (is_array($buf)) {
			throw new EmptyResultException($buf['errMessage'], 404);
			// throw new EmptyResultException($buf['errMessage'], $buf['errCode']);
		}
		return $buf;
	}

	public function remove($oid) {
		DLOG($oid);
		if (!extension_loaded("rados")) return;
		$res = rados_remove($this->ctx, $oid);
		return $res;
	}

	public function objects_list() {
		DLOG();
		if (!extension_loaded("rados")) return array();
		$res = rados_objects_list($this->ctx);
		return $res;
	}

}
// ####################################################### ./include/libcompactmvc/cmvccrudcomponent.php ####################################################### \\


/**
 * CRUD Component super class
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class CMVCCRUDComponent extends CMVCComponent {
	private $__m_subject;
	private $__m_object;
	private $__m_response;
	private $__m_called_method;
	
	protected function get_subject() {
		return $this->__m_subject;
	}
	
	protected function get_object() {
		return $this->__m_object;
	}
	
	protected function get_response() {
		return $this->__m_response;
	}

	/**
	 * Retrieve the name of the called method after it has been called.
	 * 
	 * @return mixed
	 */
	protected function get_called_method() {
		return $this->__m_called_method;
	}
	
	/**
	 * This method can be used in the pre_run_ phase to detect if RPC call should be allowed or not.
	 * 
	 * @param unknown $method
	 * @throws DBException
	 * @return void|boolean|mixed
	 */
	protected function will_call_method($method = null) {
		DLOG("('$method')");
		if (Session::get_instance()->get_property(ST_USER_ID) != null) return;
		try {
			$subject = json_decode($this->__subject);
		} catch (InvalidMemberException $e) {
		}
		try {
			if (is_callable(array(
					$subject,
					$this->path(2)
			))) {
				$cmethod = $this->path(2);
				if ($cmethod == $method) return true;
			}
		} catch (DBException $e3) {
			throw $e3;
		} catch (InvalidMemberException $e4) {
			// that's ok...
		}
		if ($method == null && is_string($cmethod)) return $cmethod;
		return false;
	}
	
	

	/**
	 * Overwrite this method to define the table that shall be operated on.
	 *
	 * @return String Table name to operate on
	 */
	protected function get_table_name() {
		DLOG();
		$td = new TableDescription();
		$found = false;
		foreach ($td->get_all_tables() as $table) {
			if ($table == $this->get_component_id()) {
				$found = true;
				break;
			}
		}
		if (!$found)
			throw new Exception("Table does not exist: " . $this->get_component_id(), 500);
		return $this->get_component_id();
	}
	
	protected function json_response($obj) {
		DLOG();
		$this->__m_response = $obj;
		parent::json_response($obj);
	}
	
	protected function pre_run_get() {
		DLOG();
		parent::pre_run_get();
		$this->__run_get(true);
	}
	
	protected function pre_run_post() {
		DLOG();
		parent::pre_run_post();
		$this->__run_post(true);
	}
	
	protected function pre_run_put() {
		DLOG();
		parent::pre_run_put();
		$this->__run_put(true);
	}
	
	protected function pre_run_delete() {
		DLOG();
		parent::pre_run_delete();
		$this->__run_delete(true);
	}
	
	/**
	 * Get record by id
	 */
	protected function main_run_get() {
		DLOG();
		parent::main_run_get();
		$this->__run_get(false);
	}
	
	private function __run_get($init = false) {
		DLOG();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = $pk[0];
		$this->__m_subject = new $table();
		$this->__m_subject->by(array(
				$pk => $this->path(1)
		));
		$this->json_response($this->__m_subject);
	}

	/**
	 * Update record, call ORM methods
	 */
	protected function main_run_post() {
		DLOG();
		parent::main_run_post();
		$this->__run_post(false);
	}
	
	private function __run_post($init = false) {
		DLOG();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = (is_array($pk) && count($pk) > 0) ? $pk[0] : "id";
		$this->__m_subject = new $table();
		if ($this->path(1) != "undefined") {
			$this->__m_subject->by(array(
					$pk => $this->path(1)
			));
		}
		try {
			$subject = json_decode($this->__subject, false);
			DTOTool::copy($subject, $this->__m_subject);
		} catch (InvalidMemberException $e5) {
			DTOTool::copy($this, $this->__m_subject);
		}
		try {
			if (is_callable(array(
					$this->__m_subject,
					$this->path(2)
			))) {
				$res = null;
				$method = $this->path(2);
				try {
					$param = null;
					$this->__m_object = json_decode($this->__object, true);
					if (is_array($this->__m_object) && array_key_exists("__type", $this->__m_object)) {
						$pclass = $this->__m_object["__type"];
						if (class_exists($pclass)) {
							if ($this->__m_object["__type"] == "DbConstraint") {
								$param = DbConstraint::create_from_json($this->__object);
							} else {
								$param = new $pclass;
								$data = json_decode($this->__object);
								DTOTool::copy($data, $param);
							}
						}
					}
					if ($param == null) {
						$param = json_decode($this->__object, true);
					}
					if (!$init) $res = $this->__m_subject->$method($param);
				} catch (InvalidMemberException $e4) {
					if (!$init) $res = $this->__m_subject->$method();
				}
				$this->__m_called_method = $method;
				$this->json_response($res);
				return;
			} else {
				if ($this->path(2) == null)
					throw new InvalidMemberException('$this->path(2) is null, doing full copy...');
				$this->__m_subject->{$this->path(2)} = $this->__object;
				$this->__m_object = $this->__object;
			}
		} catch (InvalidMemberException $e1) {
			try {
				$this->__m_subject->{$pk} = $this->{$pk};
			} catch (InvalidMemberException $e2) {
				try {
					$this->__m_subject->{$pk} = $this->path(1);
				} catch (InvalidMemberException $e6) {
					unset($this->__m_subject->{$pk});
				}
			}
		}
		if (!$init) $this->__m_subject->save();
		$this->json_response($this->__m_subject);
	}

	/**
	 * Create new record
	 */
	protected function main_run_put() {
		DLOG();
		parent::main_run_put();
		$this->__run_put(false);
	}
	
	private function __run_put($init = false) {
		DLOG();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = $pk[0];
		$this->__m_subject = new $table();
		try {
			$subject = json_decode($this->__subject, false);
			DTOTool::copy($subject, $this->__m_subject);
		} catch (InvalidMemberException $e5) {
			DTOTool::copy($this, $this->__m_subject);
		}
		try {
			$this->__m_subject->{$pk} = $this->{$pk};
		} catch (InvalidMemberException $e2) {
			try {
				$this->__m_subject->{$pk} = $this->path(1);
			} catch (InvalidMemberException $e) {
				unset($this->__m_subject->{$pk});
			}
		}
		if (!$init) $this->__m_subject->save();
		$this->json_response($this->__m_subject);
	}

	/**
	 * Delete record
	 */
	protected function main_run_delete() {
		DLOG();
		parent::main_run_delete();
		$this->__run_delete(false);
	}
	
	private function __run_delete($init = false) {
		DLOG();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = $pk[0];
		$this->__m_subject = new $table();
		$this->__m_subject->by(array(
				$pk => $this->path(1)
		));
		if (!$init) $this->__m_subject->delete();
	}
	
	/**
	 * Do not print stack trace in API environment, catch and return json-serialized exception instead.
	 *
	 * {@inheritdoc}
	 * @see CMVCController::exception_handler()
	 */
	protected function exception_handler(Exception $e) {
		DLOG(print_r($e, true));
		$this->json_response(array(
				"message" => $e->getMessage(),
				"trace" => $e->getTrace(),
				"code" => $e->getCode()
		));
	}
	
}
// ####################################################### ./include/libcompactmvc/cmvcsystem.php ####################################################### \\


/**
 * cmvcsystem.php
 *
 * @author		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class CMVCSystem extends CMVCComponent {
	private $bridf = "./include/resources/config/orm_ep_base_route_id.txt";
	private $ccf = "./include/resources/cache/combined.php";
	
	public function get_component_id() {
		DLOG();
		return "sys";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		try {
			$this->set_component_dispatch_base($this->path(1));
			$this->dispatch_component(new ORMClientComponent());
			if ($this->get_dispatched_component() != null) {
				$this->component_response();
			} else {
				$this->dispatch_method($this->path(1));
			}
		} catch (InvalidMemberException $e) {
			$this->get_view()->activate("main");
			$this->get_view()->add_template("__syshelp.tpl");
		}
	}
	
	protected function exec_gendto($is_second = false) {
		DLOG();
		$changed = false;
		if (!file_exists($this->bridf)) {
			$brid = readline("Please enter the base route id for where the endpoints are located: ");
			echo_flush("\n");
			file_put_contents($this->bridf, $brid);
		}
		$brid = file_get_contents($this->bridf);
		$bridarr = explode(".", $brid);
		$briddepth = count($bridarr);
		$view = new View();
		$view->add_template("__dtotemplate.tpl");
		$view->set_value("brid", $brid);
		$view->set_value("bridarr", $bridarr);
		$view->set_value("briddepth", $briddepth);
		$td = new TableDescription();
		$tables = $td->get_all_tables();
		$addtables = array();
		foreach ($tables as $table) {
			$fname = "./application/dba/" . $table . ".php";
			if (!class_exists($table)) {
				echo_flush("No DTO class found for table: " . $table . "\n");
				if (file_exists($fname)) {
					echo_flush("...but file exists: " . $fname . "\n");
					echo_flush("\nSituation must be resolved manually! Exiting...\n\n");
					return;
				}
				$view->set_value("table", $table);
				$code = $view->render(false);
				echo_flush("Writing skeleton class to file: " . $fname . "\n");
				file_put_contents($fname, $code);
				$changed = true;
				$addtables[] = $table;
			}
		}
		$this->get_view()->activate("newrouting");
		$this->get_view()->set_value("tables", $addtables);
		$this->get_view()->set_value("bridarr", $bridarr);
		$this->get_view()->set_value("briddepth", $briddepth);
		$this->get_view()->add_template("__syshelp.tpl");
		$quit = false;
		if (!$is_second) {
			while ($changed && !$quit) {
				echo_flush("\n");
				$response = readline("One or more DTO classes where added, do you also want to setup the corresponding endpoints? (yes/no) ");
				echo_flush("\n");
				if (str_contains(strtolower($response), "y")) {
					$this->exec_genep(true);
					$quit = true;
				}
				if (str_contains(strtolower($response), "n")) {
					$quit = true;
				}
				if (!$quit) echo_flush("Invalid input!\n");
			}
		}
	}
	
	protected function exec_genep($is_second = false) {
		DLOG();
		$changed = false;
		$view = new View();
		$view->add_template("__dtoeptemplate.tpl");
		$td = new TableDescription();
		$tables = $td->get_all_tables();
		$addtables = array();
		foreach ($tables as $table) {
			$fname = "./application/component/ep" . $table . ".php";
			if (!class_exists("EP" . $table)) {
				echo_flush("No endpoint class found for table: " . $table . "\n");
				if (file_exists($fname)) {
					echo_flush("...but file exists: " . $fname . "\n");
					echo_flush("\nSituation must be resolved manually! Exiting...\n\n");
					return;
				}
				$view->set_value("table", $table);
				$code = $view->render(false);
				echo_flush("Writing skeleton class to file: " . $fname . "\n");
				file_put_contents($fname, $code);
				$changed = true;
				$addtables[] = $table;
			}
		}
		$quit = false;
		if (!$is_second) {
			while ($changed && !$quit) {
				echo_flush("\n");
				$response = readline("One or more endpoint classes where added, do you also want to setup the DTOs? (yes/no) ");
				echo_flush("\n");
				if (str_contains(strtolower($response), "y")) {
					$this->exec_gendto(true);
					$quit = true;
				}
				if (str_contains(strtolower($response), "n")) {
					$quit = true;
				}
				if (!$quit) echo_flush("Invalid input!\n");
			}
		}
	}
	
	protected function exec_cc() {
		DLOG();
		$this->exec_cc_file();
		$this->exec_cc_redis();
	}
	
	protected function exec_cc_file() {
		DLOG();
		if (file_exists($this->ccf)) {
			unlink($this->ccf);
			echo_flush("Deleted file: " . $this->ccf . " \n");
		}
	}
	
	protected function exec_cc_redis() {
		DLOG();
		echo_flush("Executing FLUSHALL...\n");
		RedisAdapter::get_instance()->flushall();
	}
	
	protected function exec_cc_table() {
		DLOG();
		$search = REDIS_KEY_TBLCACHE_PFX . "*";
		$keys = RedisAdapter::get_instance()->keys($search);
		foreach ($keys as $k) {
			echo_flush($k . "\n");
			RedisAdapter::get_instance()->delete($k);
		}
		$search = REDIS_KEY_TBLDESC_PFX . "*";
		$keys = RedisAdapter::get_instance()->keys($search);
		foreach ($keys as $k) {
			echo_flush($k . "\n");
			RedisAdapter::get_instance()->delete($k);
		}
		$search = REDIS_KEY_FKINFO_PFX . "*";
		$keys = RedisAdapter::get_instance()->keys($search);
		foreach ($keys as $k) {
			echo_flush($k . "\n");
			RedisAdapter::get_instance()->delete($k);
		}
	}
	
	protected function exec_cc_render() {
		DLOG();
		$search = REDIS_KEY_RCACHE_PFX . "*";
		$keys = RedisAdapter::get_instance()->keys($search);
		foreach ($keys as $k) {
			echo_flush($k . "\n");
			RedisAdapter::get_instance()->delete($k);
		}
	}
	
	
}
// ####################################################### ./include/libcompactmvc/dbconstraint.php ####################################################### \\


/**
 * Query constraint definition.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DbConstraint extends DbFilter implements JsonSerializable {
	protected $order = array();
	protected $limit = array();
	protected $count = false;
	protected $dto = null;

	const LOGIC_OPERATOR_AND = "AND";
	const LOGIC_OPERATOR_OR = "OR";
	const LOGIC_OPERATOR_XOR = "XOR";
	const LOGIC_OPERATOR_NOT = "NOT";
	
	const COMPARE_EQUAL = "=";
	const COMPARE_NOT_EQUAL = "!=";
	const COMPARE_LIKE = "LIKE";
	const COMPARE_NOT_LIKE = "NOT LIKE";
	const COMPARE_GREATER_THAN = ">";
	const COMPARE_LESS_THAN = "<";
	const COMPARE_GREATER_EQUAL_THAN = ">=";
	const COMPARE_LESS_EQUAL_THAN = "<=";
	const COMPARE_IN = "IN";
	const COMPARE_NOT_IN = "NOT IN";
	
	const ORDER_ASCENDING = "ASC";
	const ORDER_DESCENDING = "DESC";
	
	/**
	 * 
	 * @param array $constraint
	 */
	public function __construct($constraint = array()) {
		DLOG();
		parent::__construct($constraint);
	}
	
	public function set_dto(DbObject $dto) {
		DLOG();
		$this->dto = $dto;
	}
	
	public function get_dto() {
		DLOG();
		return $this->dto;
	}
	
	/**
	 * 
	 * @param unknown $column
	 * @param unknown $direction
	 * @return DbConstraint
	 */
	public function order_by($column, $direction) {
		DLOG();
		$this->order[$column] = $direction;
		return $this;
	}
	
	/**
	 * 
	 * @param unknown $start_or_count
	 * @param unknown $opt_count
	 * @return void|DbConstraint
	 */
	public function limit($start_or_count, $opt_count = null) {
		DLOG();
		$this->limit = array();
		if ($start_or_count === null && $opt_count === null) return;
		if ($opt_count == null) {
			$this->limit[0] = $start_or_count;
		} else {
			$this->limit[0] = $start_or_count;
			$this->limit[1] = $opt_count;
		}
		return $this;
	}
	
	public function count_only(bool $count_only = null) {
		DLOG();
		$count_only = ($count_only == null) ? true : $count_only;
		$this->count = $count_only;
	}
	
	/**
	 * 
	 * @return array
	 */
	public function get_query_info() {
		DLOG();
		$ret = array();
		$first = true;
		$qstr = parent::get_query_substring() . " ";
		$qstr = ($qstr == "() ") ? "1 " : $qstr;
		if (count($this->order) > 0) {
			$qstr .= "ORDER BY ";
			foreach ($this->order as $col => $dir) {
				if (!$first) $qstr .= ", ";
				$first = false;
				$qstr .= "`" . $col . "` " . $dir;
			}
			$qstr .= " ";
		}
		if (count($this->limit) > 0) {
			if (count($this->limit) == 1) {
				$qstr .= "LIMIT " . $this->limit[0];
			} else if (count($this->limit) == 2) {
				$qstr .= "LIMIT " . $this->limit[0] . ", " . $this->limit[1];
			}
		}
		$ret["where_string"] = $qstr;
		$ret["count"] = $this->count;
		return $ret;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see DbFilter::jsonSerialize()
	 */
	public function jsonSerialize() {
		$base = array();
		$base["filter"] = $this->filter;
		$base["comparator"] = $this->comparator;
		$base["logic_op"] = $this->logic_op;
		$base["constraint"] = $this->constraint;
		$base["order"] = $this->order;
		$base["limit"] = $this->limit;
		$base["__type"] = get_class($this);
		return json_encode($base, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
	
	/**
	 * DbConstraint factory
	 * 
	 * @param unknown $json
	 * @return DbConstraint
	 */
	public static function create_from_json($json) {
		$tmp = json_decode($json, true);
		if (array_key_exists("__type", $tmp)) {
			if (class_exists($tmp["__type"])) {
				if ($tmp["__type"] == "DbConstraint" || $tmp["__type"] == "DbFilter") {
					$tmpobj = json_decode($json, false);
					$ret = new DbConstraint();
					foreach ($tmpobj->filter as $filter) {
						$f = DbFilter::create_from_json(json_encode($filter));
						if ($f != null) $ret->add_filter($f);
					}
					$ret->comparator = $tmpobj->comparator;
					$ret->logic_op = $tmpobj->logic_op;
					$ret->constraint = $tmp["constraint"];
					$ret->order = $tmp["order"];
					$ret->limit = $tmpobj->limit;
					$ret->count = $tmpobj->count;
				}
			}
		}
		return $ret;
	}
	
	
}

// ####################################################### ./include/libcompactmvc/dbexception.php ####################################################### \\


/**
 * Database Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DBException extends Exception {
	public $previous;

	public function __construct($message = null, $code = null, Exception $previous = null) {
		DLOG("DB Exception $code: $message");
		parent::__construct($message, $code, $previous);
		$this->previous = $previous;
	}

}
// ####################################################### ./include/libcompactmvc/dbobject.php ####################################################### \\


/**
 * Generic database object.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DbObject extends DbAccess implements JsonSerializable {
	private $__member_variables;
	private $__tablename;
	private $__isnew;
	private $__td;
	private $__fk_resolution;
	private $__fk_obj_cache;
	
	/**
	 *
	 * @return string Endpoint URL for this DTO
	 */
	public function get_endpoint() {
		throw new DBException("get_endpoint() has to be implemented in all DTO classes! Method is missing in class '" . get_class($this) . "'.");
	}

	/**
	 * This method is called from the constructor when an object is created and before the member vars are set via the constructor.
	 */
	protected function pre_init() {
		DLOG();
	}
	
	/**
	 * This method is called from the constructor when an object is created and after the member vars are set via the constructor.
	 */
	protected function init() {
		DLOG();
	}

	/**
	 * This method is called before a save operation.
	 */
	protected function on_before_save() {
		DLOG();
	}
	
	/**
	 * This method is called after a save operation.
	 */
	protected function on_after_save() {
		DLOG();
	}

	/**
	 * This method is called before a load operation.
	 */
	protected function on_before_load() {
		DLOG();
	}
	
	/**
	 * This method is called after a load operation.
	 */
	protected function on_after_load() {
		DLOG();
	}

	/**
	 *
	 * @param array() $members:
	 *        	array or DbObject
	 * @param bool $isnew
	 */
	public function __construct($members = array(), $isnew = true) {
		parent::__construct();
		$this->__fk_resolution = true;
		$this->__fk_obj_cache = array();
		$this->__tablename = null;
		$this->__isnew = $isnew;
		$this->__td = new TableDescription();
		$this->__type = get_class($this);
		$tablename = $this->get_table();
		if ($tablename == null) $tablename = get_class($this);
		$this->table($tablename);
		if (!$isnew)
			$this->on_before_load();
		$this->pre_init();
		if (is_array($members)) {
			foreach ($members as $key => $val) {
				$this->__member_variables[$key] = $members[$key];
			}
		} else if (is_object($members) && is_subclass_of($members, "DbObject")) {
			$tmp = $members->to_array();
			foreach ($tmp as $key => $val) {
				$this->__member_variables[$key] = $tmp[$key];
			}
		}
		$this->init();
		if (!$isnew)
			$this->on_after_load();
	}

	/**
	 *
	 * @param string $var_name
	 */
	public function __get($var_name) {
		if (!isset($this->__tablename) || $this->__tablename == "") {
			return (array_key_exists($var_name, $this->__member_variables)) ? $this->__member_variables[$var_name] : null;
		}
		$ret = null;
		if ($this->__fk_resolution) {
			if (array_key_exists($var_name, $this->__fk_obj_cache)) {
				$ret = $this->__fk_obj_cache[$var_name];
			} else {
				$this->__td = (isset($this->__td)) ? $this->__td : new TableDescription();
				$fks = $this->__td->fkinfo($this->__tablename);
				foreach ($fks as $fk) {
					$tmp = explode(".", $fk->fk);
					$column = $tmp[1];
					$tmp = explode(".", $fk->ref);
					$reftab = $tmp[0];
					$refcol = $tmp[1];
					if ($column == $var_name) {
						$qb = new QueryBuilder();
						if (is_object($this->__member_variables[$var_name])) {
							$this->__member_variables[$var_name] = $this->__member_variables[$var_name]->$refcol;
						}
						$q = $qb->select($reftab, array(
								$refcol => $this->__member_variables[$var_name]
						));
						$ret = $this->run_query($q, true, true, null, $reftab, false);
						if (count($ret) == 1) {
							$ret = $ret[0];
						}
						$this->__fk_obj_cache[$var_name] = $ret;
					}
				}
			}
		}
		if ($ret == null) {
			$ret = (array_key_exists($var_name, $this->__member_variables)) ? $this->__member_variables[$var_name] : null;
		}
		return $ret;
	}
	
	public function unset($var_name) {
		unset($this->__member_variables[$var_name]);
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @param unknown_type $value
	 * @return DbObject
	 */
	public function __set($var_name, $value) {
		$this->__member_variables[$var_name] = $value;
		return $this;
	}

	/**
	 */
	public function jsonSerialize() {
		$ret = array();
		if (!isset($this->__fk_resolution))
			$this->__fk_resolution = true;
		if (!isset($this->__fk_obj_cache))
			$this->__fk_obj_cache = array();
		foreach ($this->__member_variables as $key => $val) {
			$val = $this->__get($key);
			$ret[$key] = (!is_string($val)) ? $val : UTF8::encode($val);
		}
		return $ret;
	}

	/**
	 *
	 * @param string $tablename
	 * @throws InvalidArgumentException
	 * @return DbObject
	 */
	public function table($tablename) {
		if ($this->__tablename != "" && isset($this->__tablename) && $this->__tablename != $tablename) {
			throw new InvalidArgumentException("Table can only be set once and can not be changed afterwards.");
		}
		$this->__tablename = $tablename;
		if ($tablename != "DbObject") {
			$pks = $this->__td->primary_keys($this->__tablename);
			$this->__pk = (count($pks) > 0) ? $pks[0] : null;
		}
		return $this;
	}

	/**
	 * Returns the name of the table this object operates on.
	 *
	 * @return string Table name
	 */
	public function get_table() {
		DLOG();
		return $this->__tablename;
	}

	/**
	 *
	 * @param array $constraint
	 * @throws DBException, EmptyResultException
	 * @return DbObject
	 */
	public function by($constraint = array()) {
		if (!isset($this->__tablename)) {
			throw new DBException("Invalid call: No table selected.");
		}
		$constraint = ($constraint == null) ? array() : $constraint;
		if (is_object($constraint) && get_class($constraint) == "DbConstraint") {
			$constraint->set_dto($this);
		}
		$qb = new QueryBuilder();
		$q = $qb->select($this->__tablename, $constraint);
		$res = $this->run_query($q, false, false, null, $this->__tablename, false);
		if (!$res) {
			throw new EmptyResultException("Query: " . $q);
		}
		if (is_array($res)) {
			foreach ($res as $key => $val) {
				$this->$key = $val;
			}
		}
		$this->__isnew = false;
		$this->on_after_load();
		return $this;
	}

	/**
	 *
	 * @throws DBException
	 * @return DbObject
	 */
	public function save() {
		$slotid = $this->{$this->__pk};
		$this->on_before_save();
		if (!isset($this->__tablename)) {
			throw new DBException("Invalid call: No table selected.");
		}
		$pks = $this->__td->primary_keys($this->__tablename);
		$cols = $this->__td->columns($this->__tablename);
		$ci = $this->__td->columninfo($this->__tablename);
		if ($this->__isnew) {
			foreach ($ci as $info) {
				if ($info->Field == $pks[0] && strtolower(substr($info->Type, 0, 6)) === "binary") {
					$this->__insid = rand(1, 1000000000);
				}
			}
		}
		$fields = array();
		foreach ($cols as $key => $val) {
			if (array_key_exists($val, $this->__member_variables)) {
				$fields[$val] = $this->__member_variables[$val];
			}
		}
		$qb = new QueryBuilder();
		if ($this->__isnew) {
			$q = $qb->insert($this->__tablename, $fields);
			$slotid = "";
		} else {
			$constraint = array();
			foreach ($pks as $key => $val) {
				if (array_key_exists($val, $this->__member_variables)) {
					$constraint[$val] = $this->__member_variables[$val];
				}
			}
			$q = $qb->update($this->__tablename, $fields, $constraint);
		}
		$ret = $this->run_query($q, false, false, null, $this->__tablename, true);
		if ($this->__isnew && count($pks) == 1) {
			foreach ($ci as $info) {
				if ($info->Field == $pks[0] && strtolower(substr($info->Type, 0, 6)) !== "binary") {
					$this->by(array($pks[0] => $ret));
				} else {
					if ($this->__insid != null) {
						$this->by(array("__insid" => $this->__insid));
						$fields["__insid"] = null;
						$q = $qb->update($this->__tablename, $fields, array("__insid" => $this->__insid));
						$ret = $this->run_query($q, false, false, null, $this->__tablename, true);
						$this->__insid = null;
					}
				}
			}
		}
		$this->__isnew = false;
		$this->on_after_save();
		$json = json_encode($this);
		$slot = "dbeventslot___" . $this->get_table() . "___" . $slotid;
		WSAdapter::get_instance()->notify($slot, $json);
		if ($slotid != "") {
			$slot = "dbeventslot___" . $this->get_table() . "___";
			WSAdapter::get_instance()->notify($slot, $json);
		}
		return $this;
	}
	
	public function update_all(DbConstraint $constraint = null) {
		if ($constraint == null) {
			$constraint = array();
		} else {
			$constraint->set_dto($this);
		}
		$constraint = ($constraint == null) ? array() : $constraint;
		$fields = array();
		$cols = $this->__td->columns($this->__tablename);
		foreach ($cols as $key => $val) {
			if (array_key_exists($val, $this->__member_variables)) {
				$fields[$val] = $this->__member_variables[$val];
			}
		}
		$qb = new QueryBuilder();
		$q = $qb->update($this->__tablename, $fields, $constraint);
		$ret = $this->run_query($q, false, false, null, $this->__tablename, true);
		$slot = "dbeventslot___" . $this->get_table() . "___";
		WSAdapter::get_instance()->notify($slot);
	}

	/**
	 * Delete the current record
	 *
	 * @return DbObject
	 */
	public function delete() {
		$pks = $this->__td->primary_keys($this->__tablename);
		$constraint = array();
		foreach ($pks as $key => $val) {
			if (array_key_exists($val, $this->__member_variables)) {
				$constraint[$val] = $this->__member_variables[$val];
			}
		}
		$qb = new QueryBuilder();
		$q = $qb->delete($this->__tablename, $constraint);
		$this->__isnew = true;
		$this->__member_variables = array();
		$this->run_query($q, false, false, null, $this->__tablename, true);
		$slot = "dbeventslot___" . $this->get_table() . "___";
		WSAdapter::get_instance()->notify($slot);
		return $this;
	}

	/**
	 * Get all records from the table
	 *
	 * @return DbObject[]
	 */
	public function all() {
		DLOG();
		return $this->by_table($this->__tablename, array());
	}

	/**
	 * Get all records from the table where the given constraint matches
	 *
	 * @param
	 *        	array Constraint array
	 * @return DbObject[] Result records
	 */
	public function all_by($constraint = array()) {
		DLOG();
		if (is_object($constraint) && get_class($constraint) == "DbConstraint") {
			$constraint->set_dto($this);
		}
		return $this->by_table($this->__tablename, $constraint);
	}

	/**
	 * Get all records from the table where the given constraint matches
	 *
	 * @param
	 *        	array Constraint array
	 * @return DbObject[] Result records
	 */
	public function all_like($constraint = array()) {
		DLOG();
		return $this->by_table($this->__tablename, $constraint, true);
	}

	/**
	 * Convert this object to an array
	 *
	 * @return array()
	 */
	public function to_array() {
		return $this->__member_variables;
	}

	/**
	 * Enable/Disable foreign key resolution
	 *
	 * @throws InvalidArgumentException
	 * @param bool $enabled
	 */
	public function fk_resolution($enabled = true) {
		DLOG();
		if ($enabled !== true && $enabled !== false)
			throw new InvalidArgumentException("Boolean expected", 500);
		$this->__fk_resolution = $enabled;
	}

	/**
	 * Tells if the foreign key resolution is enabled or not
	 *
	 * @return bool
	 */
	public function fk_resolution_enabled() {
		DLOG();
		return $this->__fk_resolution;
	}

}

// ####################################################### ./include/libcompactmvc/dtotool.php ####################################################### \\


/**
 * Data Object Tools.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DTOTool {

	/**
	 * Copy DTO members from one object to another
	 *
	 * @param DTO $src
	 *        	in / out
	 * @param DTO $dst
	 *        	in / out
	 */
	public static function copy(&$src, &$dst) {
		DLOG();
		$in = json_decode(json_encode($src), true);
		foreach ($in as $key => $val) {
			if (is_object($key) || is_null($key)) {
				continue;
			}
			$dst->{$key} = $src->{$key};
		}
	}

}
// ####################################################### ./include/libcompactmvc/emptyresultexception.php ####################################################### \\


/**
 * Empty Result Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class EmptyResultException extends DBException {
	public $previous;

	public function __construct($message = "Empty result", $code = 404, Exception $previous = null) {
		DLOG("Empty result. Reason: $code: $message");
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}

}
// ####################################################### ./include/libcompactmvc/error_messages.php ####################################################### \\


/**
 * General error messages.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ErrorMessages {
	const ERR_NO_CONNECTION = "Verbindung zum SQL-Server nicht m&ouml;glich";
	const ERR_NO_AUTHORIZATION = "Die Authentifizierung ist fehlgeschlagen";
	const ERR_METHOD_NOT_FOUND = "Die Methode wurde nicht gefunden";
	const ERR_UTF8_NOT_SUPPORTED = "UTF8 wird von ihrer Datenbank nicht unterst&uuml;tzt";
	const ERR_ENTRY_NOT_FOUND = "Der Eintrag wurde nicht gefunden";
	const ERR_NO_VALID_DATA = "Die Daten sind ung&uuml;ltig";
	const ERR_NOT_IMPLEMENTED = "Funktion nicht implementiert";
	const ERR_GENERAL_ERROR = "Genereller Fehler";
	const ERR_DB_QUERY_ERROR = "Fehler bei Datenbankabfrage: ";

}
// ####################################################### ./include/libcompactmvc/fcmadapter.php ####################################################### \\


/**
 * Firebase Cloud Messaging Adapter
 * With additional variable cache.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FCMAdapter extends Singleton {
	protected  static $instance;
	private $apikey;

	protected  function __construct() {
		DLOG();
		$this->apikey = null;
	}

	/**
	 *
	 * @return FCMAdapter Returns the only instance of this class. This is a Singleton, so there can only be one instance.
	 */
	public static function get_instance($a = NULL, $b = NULL, $c = NULL, $d = NULL, $e = NULL, $f = NULL, $g = NULL, $h = NULL, $i = NULL, $j = NULL, $k = NULL, $l = NULL, $m = NULL, $n = NULL, $o = NULL, $p = NULL) {
		return parent::get_instance();
	}
	
	public function send($title, $text, $client_token) {
		if ($this->apikey == null) throw new Exception("FCM API key is not set, cannot send push message!", 500);
		$view = new View();
		$view->add_template("__fcmsend.tpl");
		$view->set_value("title", $title);
		$view->set_value("body", $text);
		$view->set_value("token", $client_token);
		$view->set_value("fbapitoken", $this->apikey);
		$fname = tempnam("./files/temp/", "fcm_send_");
		file_put_contents($fname, $view->render(false));
		system("/bin/chmod +x " . $fname);
		system($fname . " > " . $fname . ".out" . " 2> " . $fname . ".err");
		$io = file_get_contents($fname . ".out");
		$ie = file_get_contents($fname . ".err");
		DLOG("CURL stdout: " . $io);
		DLOG("CURL stderr: " . $ie);
		unlink($fname);
		unlink($fname . ".out");
		unlink($fname . ".err");
		return $this;
	}
	
	public function set_api_key($key) {
		$this->apikey = $key;
		return $this;
	}

	
}

// ####################################################### ./include/libcompactmvc/fifobuffer.php ####################################################### \\


/**
 * FIFO Buffer.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FIFOBuffer {
	private $id_bufferid;
	private $id_first;
	private $id_last;
	private $elm_current;
	private $lockfile;
	private $ttl;

	/**
	 * Constructor.
	 * Leave the ID empty to create a new buffer. To access an already existing buffer, provide its id here.
	 *
	 * @param string $id
	 *        	Buffer ID or null to create a new one.
	 * @throws FIFOBufferException
	 */
	public function __construct($id = null) {
		$this->ttl = REDIS_KEY_FIFOBUFF_TTL;
		if ($id == null) {
			$this->id_bufferid = md5(microtime() . rand(0, 255));
			$this->save_state();
		} else {
			$this->id_bufferid = $id;
			$this->load_state();
		}
		if (!is_dir("./files/lock/"))
			mkdir("./files/lock/");
		$this->lockfile = "./files/lock/" . $this->id_bufferid . ".lock";
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->unlock();
		try {
			$this->check_buffer_status();
			$this->save_state();
		} catch (FIFOBufferException $e) {
		}
	}

	private function load_state() {
		$state = RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, $this->ttl);
		if ($state === false)
			throw new FIFOBufferException("Invalid FIFO buffer ID.", 404);
		$state = json_decode($state, true);
		$this->id_first = $state["first"];
		$this->id_last = $state["last"];
	}

	/**
	 * Save the buffer state to Redis.
	 */
	private function save_state() {
		$state = array(
				"first" => $this->id_first,
				"last" => $this->id_last
		);
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, json_encode($state), false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, $this->ttl);
	}

	/**
	 * Load a buffer element from Redis.
	 *
	 * @param unknown $id
	 *        	Element ID
	 * @throws FIFOBufferException
	 * @return FIFOBufferElement
	 */
	private function load_element($id) {
		$this->check_buffer_status();
		$obj = unserialize(RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $id, false));
		if ($obj === false) {
			throw new FIFOBufferException("Unable to load element " . $id);
		}
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $id, $this->ttl);
		return $obj;
	}

	/**
	 * Save a buffer element in Redis.
	 *
	 * @param FIFOBufferElement $elem
	 *        	The element that is to save.
	 * @throws FIFOBufferException
	 */
	private function save_element(FIFOBufferElement $elem) {
		$this->check_buffer_status();
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $elem->get_id(), serialize($elem), false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $elem->get_id(), $this->ttl);
	}

	/**
	 * Save a buffer element in Redis.
	 *
	 * @param unknown $id
	 *        	Element ID.
	 */
	private function delete_element($id) {
		$this->check_buffer_status();
		RedisAdapter::get_instance()->delete(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $id);
	}

	/**
	 * Check if the buffer still exists.
	 *
	 * @throws FIFOBufferException
	 */
	private function check_buffer_status() {
		if ($this->id_bufferid == null) {
			throw new FIFOBufferException("Buffer destroyed.", 404);
		} else {
			$state = RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, false);
			if ($state === false) {
				$this->id_bufferid = null;
				throw new FIFOBufferException("Buffer destroyed.", 404);
			}
		}
	}

	/**
	 * Get the buffer ID.
	 *
	 * @throws FIFOBufferException
	 * @return string The buffer ID.
	 */
	public function get_id() {
		$this->check_buffer_status();
		return $this->id_bufferid;
	}

	/**
	 * Check if buffer is empty.
	 *
	 * @throws FIFOBufferException
	 * @return boolean True if buffer is empty, false otherwise.
	 */
	public function is_empty() {
		$this->check_buffer_status();
		$this->load_state();
		return !($this->id_first != null || $this->id_last != null);
	}

	/**
	 * Returns the number of elements in the buffer.
	 *
	 * @throws FIFOBufferException
	 * @return int Number of elements
	 */
	public function size() {
		$this->check_buffer_status();
		if ($this->is_empty()) {
			return 0;
		}
		$keys = RedisAdapter::get_instance()->keys(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_*");
		return count($keys);
	}

	/**
	 * Add an element to the buffer queue.
	 *
	 * @param mixed $data
	 *        	Element data.
	 * @throws FIFOBufferException
	 */
	public function write($data, $ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			$elem = new FIFOBufferElement();
			$elem->set_data($data);
			$elem->set_prev(null);
			$elem->set_next(null);
			$this->id_first = $elem->get_id();
		} else {
			$elem = new FIFOBufferElement();
			$elem->set_data($data);
			$lastelem = $this->load_element($this->id_last);
			$lastelem->set_next($elem->get_id());
			$elem->set_prev($lastelem->get_id());
			$this->save_element($lastelem);
		}
		$this->id_last = $elem->get_id();
		$this->save_element($elem);
		$this->save_state();
		if (!$ignore_lock) {
			$this->unlock();
		}
	}

	/**
	 * Read the next element from the buffer and delete it.
	 *
	 * @throws FIFOBufferException
	 * @return mixed Element data.
	 */
	public function read($ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		$elem = $this->load_element($this->id_first);
		if ($elem->get_next() != null) {
			$firstelem = $this->load_element($elem->get_next());
			$firstelem->set_prev(null);
			$this->save_element($firstelem);
			$this->id_first = $firstelem->get_id();
		} else {
			$this->id_first = null;
			$this->id_last = null;
		}
		$this->save_state();
		usleep(1000);
		$this->delete_element($elem->get_id());
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $elem->get_data();
	}

	/**
	 * Read the first element of the buffer without deleting it.
	 * Increments the internal (object-wide, not global) iterator.
	 *
	 * @throws FIFOBufferException
	 * @return mixed Element data
	 */
	public function read_first($ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		$this->elm_current = $this->load_element($this->id_first);
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $this->elm_current->get_data();
	}

	/**
	 * Read the next element of the buffer without deleting it.
	 * Increments the internal (object-wide, not global) iterator.
	 *
	 * @throws FIFOBufferException
	 * @return mixed Element data
	 */
	public function read_next($ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		if ($this->elm_current == null) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return $this->read_first();
		}
		if ($this->elm_current->get_next() == null) {
			$this->elm_current = $this->load_element($this->elm_current->get_id());
			if ($this->elm_current->get_next() == null) {
				if (!$ignore_lock) {
					$this->unlock();
				}
				return null;
			}
		}
		try {
			$this->elm_current = $this->load_element($this->elm_current->get_next());
		} catch (FIFOBufferException $e) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return $this->read_first();
		}
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $this->elm_current->get_data();
	}

	/**
	 * Read an element at the given position.
	 *
	 * @param int $idx
	 *        	Element index
	 * @throws FIFOBufferException
	 * @return mixed Element data.
	 */
	public function read_at($idx, $ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		$elem = $this->load_element($this->id_first);
		for($i = 0; $i < $idx; $i++) {
			$elem = $this->load_element($elem->get_next());
		}
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $elem->get_data();
	}

	/**
	 * Delete element at the given position.
	 *
	 * @param int $idx
	 *        	Element index
	 * @throws FIFOBufferException
	 */
	public function delete_at($idx, $ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			throw new FIFOBufferException("Invalid index, buffer is empty.", 404);
		}
		$elem = $this->load_element($this->id_first);
		for($i = 0; $i < $idx; $i++) {
			$elem = $this->load_element($elem->get_next());
		}
		$prev = $this->load_element($elem->get_prev());
		$next = $this->load_element($elem->get_next());
		$this->delete_element($elem->get_id());
		$prev->set_next($next->get_id());
		$next->set_prev($prev->get_id());
		$this->save_element($prev);
		$this->save_element($next);
		$this->save_state();
		if (!$ignore_lock) {
			$this->unlock();
		}
	}

	/**
	 * Destroy the buffer.
	 * All subsequent method calls on this buffer will throw a FIFOBufferException.
	 *
	 * @throws FIFOBufferException
	 */
	public function destroy() {
		$this->check_buffer_status();
		while (!$this->is_empty()) {
			$this->read();
		}
		RedisAdapter::get_instance()->delete(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid);
		$this->id_bufferid = null;
		$this->unlock();
	}

	/**
	 * Set an explicit lock on the buffer.
	 */
	public function lock() {
		clearstatcache($this->lockfile);
		while (file_exists($this->lockfile)) {
			usleep(10);
		}
		$fh = fopen($this->lockfile, "w+");
		fwrite($fh, "locked");
		fflush($fh);
		fclose($fh);
		clearstatcache($this->lockfile);
		while (!file_exists($this->lockfile)) {
			usleep(10);
		}
	}

	/**
	 * Remove an explicit lock from the buffer.
	 */
	public function unlock() {
		clearstatcache($this->lockfile);
		if (file_exists($this->lockfile))
			@unlink($this->lockfile);
		clearstatcache($this->lockfile);
	}

	/**
	 * Set a deviating TTL for this buffer instance.
	 *
	 * @param int $ttl
	 *        	The TTL to use for this buffer instance
	 */
	public function set_ttl($ttl) {
		$this->ttl = $ttl;
	}

}
// ####################################################### ./include/libcompactmvc/fifobufferelement.php ####################################################### \\


/**
 * Element for ArrayList.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FIFOBufferElement {
	private $id;
	private $data;
	private $prev;
	private $next;

	public function __construct() {
		$this->id = md5(microtime() . rand(0, 255));
	}

	public function get_id() {
		return $this->id;
	}

	public function get_data() {
		return $this->data;
	}

	public function set_data($data) {
		$this->data = $data;
	}

	public function get_prev() {
		return $this->prev;
	}

	public function set_prev($prev) {
		$this->prev = $prev;
	}

	public function get_next() {
		return $this->next;
	}

	public function set_next($next) {
		$this->next = $next;
	}

}
// ####################################################### ./include/libcompactmvc/fifobufferexception.php ####################################################### \\


/**
 * FIFO Buffer Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FIFOBufferException extends Exception {

}
// ####################################################### ./include/libcompactmvc/filenotfoundexception.php ####################################################### \\


/**
 * File not found Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FileNotFoundException extends Exception {
	public $previous;

	public function __construct($filename = "", $code = 404, Exception $previous = null) {
		$this->message = "File not found: $filename";
		DLOG($this->message);
		$this->code = $code;
		$this->previous = $previous;
	}
	
}
// ####################################################### ./include/libcompactmvc/googlejwt.php ####################################################### \\


/**
 * Google JSON web token
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
use Emarref\Jwt\Claim;
class GoogleJWT {
	private $token;
	private $encryption;
	private $tz;
	private $jwt;

	public function __construct($issuer, $pk) {
		DLOG();
		$this->tz = date_default_timezone_get();
		date_default_timezone_set('UTC');
		
		$this->token = new Emarref\Jwt\Token();
		
		// Standard claims are supported
		$this->token->addClaim(new Claim\Audience('https://www.googleapis.com/oauth2/v3/token'));
		$this->token->addClaim(new Claim\Expiration(new DateTime('60 minutes')));
		$this->token->addClaim(new Claim\IssuedAt(new DateTime('now')));
		$this->token->addClaim(new Claim\Issuer($issuer));
		
		$this->jwt = new Emarref\Jwt\Jwt();
		$algorithm = new Emarref\Jwt\Algorithm\Rs256('notasecret');
		$this->encryption = Emarref\Jwt\Encryption\Factory::create($algorithm);
		if (file_exists($pk)) {
			$pk = file_get_contents($pk);
		}
		$this->encryption->setPrivateKey($pk);
		
		date_default_timezone_set($this->tz);
	}

	public function add_scope($scope) {
		$this->token->addClaim(new Claim\PublicClaim('scope', $scope));
		return $this;
	}

	public function get_token() {
		date_default_timezone_set('UTC');
		$serializedToken = $this->jwt->serialize($this->token, $this->encryption);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v3/token");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "&access_type=offline&grant_type=urn%3Aietf%3Aparams%3Aoauth%3Agrant-type%3Ajwt-bearer&assertion=" . $serializedToken . "'");
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($ch, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($ch, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		$this->token = json_decode(curl_exec($ch), true);
		date_default_timezone_set($this->tz);
		return $this->token;
	}

}
// ####################################################### ./include/libcompactmvc/googlemaps.php ####################################################### \\


/**
 * This class provides some functions that make use of the Google API.
 * They where required to generate some
 * of the data now stored in the database in conjuction with the agencies.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class GoogleMaps {

	/**
	 * Private constructor.
	 * This class contains only static functions, hence there is no instantiation neccessary.
	 */
	private function __construct() {
	}

	/**
	 * Make Google give me the location of the given address.
	 *
	 * @param String $address        	
	 * @return The whole dataset received from Google.
	 */
	public static function encode($address) {
		$address = UTF8::encode($address);
		$get = "http://maps.google.com/maps/api/geocode/json?address=";
		$get .= urlencode($address);
		$get .= "&sensor=false";
		$curl_session = curl_init($get);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl_session);
		curl_close($curl_session);
		
		return json_decode($ret, true);
	}

	/**
	 * Let Google calculate e distance between two coordinates.
	 * This function calculates the distance using existing roads.
	 * Linear distance can be calculated using the MapRadius class.
	 *
	 * @param Float $lat_dep
	 *        	Latitude start point
	 * @param Float $lng_dep
	 *        	Longitude start point
	 * @param Float $lat_dest
	 *        	Latitude end point
	 * @param Float $lng_dest
	 *        	Longitude end point
	 * @return The whole dataset received from Google.
	 */
	public static function get_dist($lat_dep, $lng_dep, $lat_dest, $lng_dest) {
		$get = "http://maps.google.com/maps/api/directions/json?origin=$lat_dep,$lng_dep&destination=$lat_dest,$lng_dest&sensor=false";
		$curl_session = curl_init($get);
		curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, true);
		$ret = curl_exec($curl_session);
		curl_close($curl_session);
		
		return json_decode($ret, true);
	}

}
// ####################################################### ./include/libcompactmvc/htmlmail.php ####################################################### \\


/**
 * The HTMLMail class can be used to send text and HTML mails either via PHP's mail() function or
 * directly through an SMTP server.
 * Additionaly the following files are required:
 * SMTP.php
 * UTF8.php
 * Socket.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class HTMLMail {
	private $inline;
	private $attachment;
	private $sender_name;
	private $sender_mail;
	private $receiver_name;
	private $receiver_mail;
	private $replyto_name;
	private $replyto_mail;
	private $returnpath_name;
	private $returnpath_mail;
	private $cc;
	private $bcc;
	private $subject;
	private $htmlbody;
	private $textbody;
	private $boundary_a;
	private $boundary_m;
	private $boundary_r;
	private $mailtype;
	private $transfertype;
	private $smtpserver;
	private $smtpuser;
	private $smtppass;
	private $mailbody;
	const MAIL_TYPE_TEXT = 1;
	const MAIL_TYPE_HTML = 2;
	const TRANS_TYPE_MAIL = 1;
	const TRANS_TYPE_SMTP = 2;

	/**
	 * Instantiate this class telling the constructor if you wish do send a pure text or an HTML mail.
	 * Allowed values:
	 * HTMLMail::MAIL_TYPE_TEXT
	 * HTMLMail::MAIL_TYPE_HTML
	 *
	 * @param Integer $type
	 */
	public function __construct($type = self::MAIL_TYPE_HTML) {
		DLOG();
		$this->mailtype = $type;
		$this->transfertype = self::TRANS_TYPE_MAIL;
		$this->htmlbody = "";
		$this->textbody = "";
		$this->inline = array();
		$this->attachment = array();
		$this->cc = array();
		$this->bcc = array();
		$this->boundary_a = md5(time() + mt_rand());
		$this->boundary_m = md5(time() + mt_rand());
		$this->boundary_r = md5(time() + mt_rand());
	}

	/**
	 * Set the mail type (text/HTML) on an already existing object of this class.
	 * Allowed values:
	 * HTMLMail::MAIL_TYPE_TEXT
	 * HTMLMail::MAIL_TYPE_HTML
	 *
	 * @param Integer $type
	 */
	public function set_mail_type($type) {
		DLOG();
		$this->mailtype = $type;
	}

	/**
	 * Set the transfer type.
	 * This decides if mail() is used or direct communication to an SMTP server.
	 * Allowed values:
	 * HTMLMail::TRANS_TYPE_MAIL
	 * HTMLMail::TRANS_TYPE_SMTP
	 *
	 * @param unknown_type $type
	 */
	public function set_transfer_type($type) {
		DLOG();
		$this->transfertype = $type;
	}

	/**
	 * Set hostname and login credentials for SMTP server.
	 * Local SMTP servers mostly don't require a login,
	 * thus, the second and third parameters are optional.
	 *
	 * @param String $server
	 *        	IP or hostname of SMTP server
	 * @param String $user
	 *        	login user (optional)
	 * @param String $pass
	 *        	login password (optional)
	 */
	public function set_smtp_access($server, $user = "", $pass = "") {
		DLOG();
		$this->smtpserver = $server;
		$this->smtpuser = $user;
		$this->smtppass = $pass;
	}

	/**
	 * Set the receiver of the mail.
	 *
	 * @param String $email
	 *        	E-Mail address
	 * @param String $name
	 *        	name of the receiver (optional)
	 */
	public function set_receiver($email, $name = "") {
		DLOG();
		$this->receiver_name = UTF8::encode($name);
		$this->receiver_mail = UTF8::encode($email);
	}

	/**
	 * Set the sender of the mail.
	 *
	 * @param String $email
	 *        	E-Mail address
	 * @param String $name
	 *        	name of the sender (optional)
	 */
	public function set_sender($email, $name = "") {
		DLOG();
		$this->sender_name = UTF8::encode($name);
		$this->sender_mail = UTF8::encode($email);
	}

	/**
	 * Set the "Reply-To:" header field of the mail.
	 * If unset, the sender will be used for this field.
	 *
	 * @param String $email
	 *        	E-Mail address
	 */
	public function set_reply_to($email) {
		DLOG();
		$this->replyto_mail = UTF8::encode($email);
	}

	/**
	 * Set the "Return-Path:" header field of the mail.
	 *
	 * @param String $email
	 *        	E-Mail address
	 */
	public function set_return_path($email) {
		DLOG();
		$this->returnpath_mail = UTF8::encode($email);
	}

	/**
	 * Add a CC entry.
	 *
	 * @param String $email
	 *        	E-Mail address
	 * @param String $name
	 *        	name (optional)
	 */
	public function add_cc($email, $name = "") {
		DLOG();
		mb_internal_encoding('UTF-8');
		$this->cc[] = mb_encode_mimeheader(UTF8::encode($name), "UTF-8", "Q") . " <" . $email . ">";
	}

	/**
	 * Add a BCC entry.
	 *
	 * @param String $email
	 *        	E-Mail address
	 * @param String $name
	 *        	name (optional)
	 */
	public function add_bcc($email, $name = "") {
		DLOG();
		mb_internal_encoding('UTF-8');
		$this->bcc[] = mb_encode_mimeheader(UTF8::encode($name), "UTF-8", "Q") . " <" . $email . ">";
	}

	/**
	 * Set the mail's subject.
	 *
	 * @param String $subject
	 *        	the subject
	 */
	public function set_subject($subject) {
		DLOG();
		$this->subject = UTF8::encode($subject);
	}

	/**
	 * Set the HTML body of the mail.
	 *
	 * @param String $body
	 *        	the HTML body
	 */
	public function set_html_body($body) {
		DLOG();
		$this->htmlbody = UTF8::encode($body);
	}

	/**
	 * Set the text body of the mail.
	 * If the text body is not explicitly set and the mail type ist set to HTML,
	 * it will be generated automatically from the HTML body. Use this function to set the mail body for
	 * text only mails.
	 *
	 * @param String $body
	 *        	text mail body
	 */
	public function set_text_body($body) {
		DLOG();
		$this->textbody = UTF8::encode($body);
	}

	/**
	 * Add attachments to the mail, that do not show up as attachments.
	 * This is required in case you want to embed
	 * pictures in the mail and reference them from the HTML body with cid:... . The CID will be the basename of
	 * the file you attach here. The file doesn't have to be local, also http:// URLs can be given here.
	 *
	 * @param String $file
	 *        	full path or URL to the file to be attached
	 */
	public function add_inline($file) {
		DLOG();
		$this->inline[] = UTF8::encode($file);
	}

	/**
	 * Add an attachment to the mail.
	 * The file doesn't have to be local, also http:// URLs can be given here.
	 *
	 * @param String $file
	 *        	full path or URL to the file to be attached
	 */
	public function add_attachment($file) {
		DLOG();
		$this->attachment[] = UTF8::encode($file);
	}

	/**
	 * Send the mail.
	 *
	 * @throws Exception
	 */
	public function send() {
		DLOG();
		mb_internal_encoding('UTF-8');
		$this->replace_image_tags();
		$this->auto_text_body();
		if ($this->replyto_mail == "" || !isset($this->replyto_mail)) {
			$this->replyto_mail = $this->sender_mail;
		}
		$this->assemble_mail();
		switch ($this->transfertype) {
			case self::TRANS_TYPE_MAIL:
				if ($this->receiver_name == "") {
					$receiver = $this->receiver_mail;
				} else {
					$receiver = mb_encode_mimeheader($this->receiver_name, "UTF-8", "Q") . " <" . $this->receiver_mail . ">";
				}
				if (!(mail($receiver, mb_encode_mimeheader($this->subject, "UTF-8", "Q"), $this->mailbody, $this->mailheader, '-f' . $this->sender_mail))) {
					throw new Exception("An error occurred. Function mail() returned false.");
				}
				break;
			case self::TRANS_TYPE_SMTP:
				$smtp = new SMTP($this->smtpserver);
				$smtp->set_login($this->smtpuser, $this->smtppass);
				$smtp->set_mail($this->sender_mail, $this->receiver_mail, $this->mailheader . $this->mailbody);
				$smtp->send();
				foreach ($this->cc as $receiver) {
					$tmp = strip_tags($receiver);
					$receiver = str_replace($tmp, "", $receiver);
					$receiver = str_replace("<", "", $receiver);
					$receiver = str_replace(">", "", $receiver);
					$smtp->set_mail($this->sender_mail, $receiver, $this->mailheader . $this->mailbody);
					$smtp->send();
				}
				foreach ($this->bcc as $receiver) {
					$tmp = strip_tags($receiver);
					$receiver = str_replace($tmp, "", $receiver);
					$receiver = str_replace("<", "", $receiver);
					$receiver = str_replace(">", "", $receiver);
					$smtp->set_mail($this->sender_mail, $receiver, $this->mailheader . $this->mailbody);
					$smtp->send();
				}
				break;
		}
	}

	/**
	 * Puts everything together.
	 */
	private function assemble_mail() {
		DLOG();
		mb_internal_encoding('UTF-8');
		$this->mailheader = 'Subject: ' . mb_encode_mimeheader($this->subject, "UTF-8", "Q") . "\n";
		$this->mailheader .= 'From: ' . mb_encode_mimeheader($this->sender_name, "UTF-8", "Q") . ' <' . $this->sender_mail . ">\n";
		if ($this->transfertype != self::TRANS_TYPE_MAIL) {
			$this->mailheader .= 'To: ' . mb_encode_mimeheader($this->receiver_name, "UTF-8", "Q") . ' <' . $this->receiver_mail . ">\n";
		}
		$this->mailheader .= 'Reply-To: ' . $this->replyto_mail . "\n";
		if (isset($this->returnpath_mail)) {
			$this->mailheader .= 'Return-Path: <' . $this->returnpath_mail . ">\n";
		}
		$this->mailheader .= 'CC: ' . implode(', ', $this->cc) . "\n";
		$this->mailheader .= 'BCC: ' . implode(', ', $this->bcc) . "\n";
		$this->mailheader .= 'Content-Type: multipart/mixed; boundary="' . $this->boundary_m . '"' . "\n";
		$this->mailheader .= 'MIME-Version: 1.0' . "\n";
		$this->mailheader .= 'X-Mailer: LibCompactMVC Mail Module (c) 2012 by Botho Hohbaum.' . "\n";
		$this->mailheader .= "\n\n";

		$this->mailbody = "--" . $this->boundary_m . "\n";
		$this->mailbody .= 'Content-Type: multipart/related; type="multipart/alternative"; boundary="' . $this->boundary_r . '"' . "\n";
		$this->mailbody .= "\n\n";
		$this->mailbody .= "--" . $this->boundary_r . "\n";
		$this->mailbody .= 'Content-Type: multipart/alternative; boundary="' . $this->boundary_a . '"' . "\n";
		$this->mailbody .= "\n\n";
		$this->mailbody .= "--" . $this->boundary_a . "\n";
		$this->mailbody .= 'Content-Type: text/plain; charset="utf-8"' . "\n";
		$this->mailbody .= "Content-Transfer-Encoding: 8bit\n\n";
		$this->mailbody .= $this->textbody;
		$this->mailbody .= "\n\n";
		if ($this->mailtype == self::MAIL_TYPE_HTML) {
			$this->mailbody .= "--" . $this->boundary_a . "\n";
			$this->mailbody .= 'Content-Type: text/html; charset="utf-8"' . "\n";
			$this->mailbody .= "Content-Transfer-Encoding: 8bit\n\n";
			$this->mailbody .= $this->htmlbody;
			$this->mailbody .= "\n\n";
		}
		$this->mailbody .= "--" . $this->boundary_a . "--\n\n";
		if (count($this->inline) > 0) {
			foreach ($this->inline as $i) {
				$fcont = "";
				if (strtoupper(substr($i, 0, 4)) == "HTTP") {
					$curl = curl_init($i);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
						curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
						curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
					}
					$fcont = curl_exec($curl);
				} else {
					$fcont = file_get_contents($i);
				}
				$fcont = base64_encode($fcont);
				$fcont = chunk_split($fcont, 76, "\n");
				$bn = basename($i);
				$tmparr = explode('?', $bn);
				$bname = array_shift($tmparr);
				$farr = explode('.', $bname);
				$fext = end($farr);
				$this->mailbody .= "--" . $this->boundary_r . "\n";
				$this->mailbody .= 'Content-ID: <' . $bname . '>' . "\n";
				$this->mailbody .= 'Content-Disposition: inline; filename="' . $bname . '"' . "\n";
				$this->mailbody .= 'Content-Type: ' . $this->mime_type($fext) . '; name="' . $bname . '"' . "\n";
				$this->mailbody .= "Content-Transfer-Encoding: base64\n\n";
				$this->mailbody .= $fcont;
				$this->mailbody .= "\n\n";
			}
		}
		$this->mailbody .= "--" . $this->boundary_r . "--\n\n";
		if (count($this->attachment) > 0) {
			foreach ($this->attachment as $a) {
				$fcont = "";
				if (strtoupper(substr($a, 0, 4)) == "HTTP") {
					$curl = curl_init($a);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
						curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
						curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
					}
					$fcont = curl_exec($curl);
				} else {
					$fcont = file_get_contents($a);
				}
				$fcont = base64_encode($fcont);
				$fcont = chunk_split($fcont, 76, "\n");
				$bn = basename($a);
				$tmparr = explode('?', $bn);
				$bname = array_shift($tmparr);
				$farr = explode('.', $bname);
				$fext = end($farr);
				$this->mailbody .= "--" . $this->boundary_m . "\n";
				$this->mailbody .= 'Content-Type: ' . $this->mime_type($fext) . '; name="' . $bname . '"' . "\n";
				$this->mailbody .= 'Content-Disposition: attachment; filename="' . $bname . '"' . "\n";
				$this->mailbody .= "Content-Transfer-Encoding: base64\n\n";
				$this->mailbody .= $fcont;
				$this->mailbody .= "\n\n";
			}
		}
		$this->mailbody .= "--" . $this->boundary_m . "--\n\n";
	}

	/**
	 * Generates the text body from the HTML body.
	 */
	private function auto_text_body() {
		DLOG();
		if ($this->mailtype == self::MAIL_TYPE_HTML) {
			DLOG("Mail type is MAIL_TYPE_HTML, creating text body from HTML body.");
			if ($this->textbody == "") {
				$this->textbody = str_replace("\r", "", $this->htmlbody);
				$this->textbody = str_replace("\n", "", $this->textbody);
				$this->textbody = preg_replace('/<br(\s+)?\/?>/i', "\n\n", $this->textbody);
				$this->textbody = str_replace("\n\n\n\n", "\n\n\n", $this->textbody);
				$this->textbody = wordwrap(html_entity_decode(strip_tags($this->textbody)));
				$this->textbody = UTF8::encode($this->textbody);
			}
		}
	}

	/**
	 * When URLs are given to the add_inline() method and the same URLs are used in the HTML body,
	 * this will automaticcaly rewritten to internal images using cid: in the src attribute.
	 */
	private function replace_image_tags() {
		DLOG();
		if (count($this->inline) > 0) {
			foreach ($this->inline as $i) {
				$bn = basename($i);
				$tmparr = explode('?', $bn);
				$bname = array_shift($tmparr);
				$this->htmlbody = str_replace($i, 'cid:' . $bname, $this->htmlbody);
			}
		}
	}

	/**
	 * Returns the MIME type based on the file extension.
	 *
	 * @param String $ext
	 *        	file extension
	 * @return String MIME type
	 */
	private function mime_type($ext = '') {
		DLOG();
		$mimes = array(
				'hqx' => 'application/mac-binhex40',
				'cpt' => 'application/mac-compactpro',
				'doc' => 'application/msword',
				'bin' => 'application/macbinary',
				'dms' => 'application/octet-stream',
				'lha' => 'application/octet-stream',
				'lzh' => 'application/octet-stream',
				'exe' => 'application/octet-stream',
				'class' => 'application/octet-stream',
				'psd' => 'application/octet-stream',
				'so' => 'application/octet-stream',
				'sea' => 'application/octet-stream',
				'dll' => 'application/octet-stream',
				'oda' => 'application/oda',
				'pdf' => 'application/pdf',
				'ai' => 'application/postscript',
				'eps' => 'application/postscript',
				'ps' => 'application/postscript',
				'smi' => 'application/smil',
				'smil' => 'application/smil',
				'mif' => 'application/vnd.mif',
				'xls' => 'application/vnd.ms-excel',
				'ppt' => 'application/vnd.ms-powerpoint',
				'wbxml' => 'application/vnd.wap.wbxml',
				'wmlc' => 'application/vnd.wap.wmlc',
				'dcr' => 'application/x-director',
				'dir' => 'application/x-director',
				'dxr' => 'application/x-director',
				'dvi' => 'application/x-dvi',
				'gtar' => 'application/x-gtar',
				'php' => 'application/x-httpd-php',
				'php4' => 'application/x-httpd-php',
				'php3' => 'application/x-httpd-php',
				'phtml' => 'application/x-httpd-php',
				'phps' => 'application/x-httpd-php-source',
				'js' => 'application/x-javascript',
				'swf' => 'application/x-shockwave-flash',
				'sit' => 'application/x-stuffit',
				'tar' => 'application/x-tar',
				'tgz' => 'application/x-tar',
				'xhtml' => 'application/xhtml+xml',
				'xht' => 'application/xhtml+xml',
				'zip' => 'application/zip',
				'mid' => 'audio/midi',
				'midi' => 'audio/midi',
				'mpga' => 'audio/mpeg',
				'mp2' => 'audio/mpeg',
				'mp3' => 'audio/mpeg',
				'aif' => 'audio/x-aiff',
				'aiff' => 'audio/x-aiff',
				'aifc' => 'audio/x-aiff',
				'ram' => 'audio/x-pn-realaudio',
				'rm' => 'audio/x-pn-realaudio',
				'rpm' => 'audio/x-pn-realaudio-plugin',
				'ra' => 'audio/x-realaudio',
				'rv' => 'video/vnd.rn-realvideo',
				'wav' => 'audio/x-wav',
				'bmp' => 'image/bmp',
				'gif' => 'image/gif',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				'jpe' => 'image/jpeg',
				'png' => 'image/png',
				'tiff' => 'image/tiff',
				'tif' => 'image/tiff',
				'css' => 'text/css',
				'html' => 'text/html',
				'htm' => 'text/html',
				'shtml' => 'text/html',
				'txt' => 'text/plain',
				'text' => 'text/plain',
				'log' => 'text/plain',
				'rtx' => 'text/richtext',
				'rtf' => 'text/rtf',
				'xml' => 'text/xml',
				'xsl' => 'text/xml',
				'mpeg' => 'video/mpeg',
				'mpg' => 'video/mpeg',
				'mpe' => 'video/mpeg',
				'qt' => 'video/quicktime',
				'mov' => 'video/quicktime',
				'avi' => 'video/x-msvideo',
				'movie' => 'video/x-sgi-movie',
				'doc' => 'application/msword',
				'word' => 'application/msword',
				'xl' => 'application/excel',
				'eml' => 'message/rfc822'
		);
		return (!isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
	}

}
// ####################################################### ./include/libcompactmvc/inputprovider.php ####################################################### \\


/**
 * Input provider
 * provides access to input vars and prevents them to appear in serialization
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class InputProvider extends InputSanitizer {
	private static $instance = null;
	
	protected function __construct() {
		DLOG();
		parent::__construct(null);
	}
	
	public static function get_instance() {
		if (self::$instance == null) {
			self::$instance = new InputProvider();
		}
		return self::$instance;
	}
	
	public function get_var($var_name) {
		return parent::__get($var_name);
	}
	
	/**
	 *
	 * @param unknown_type $var_name
	 * @throws InvalidMemberException
	 */
	public function __get($var_name) {
		throw new InvalidMemberException();
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @param unknown_type $value
	 */
	public function __set($var_name, $value) {
		throw new InvalidMemberException();
	}

	/**
	 */
	public function jsonSerialize() {
		return parent::to_array();
	}

}
// ####################################################### ./include/libcompactmvc/invalidmemberexception.php ####################################################### \\


/**
 * Invalid Member Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class InvalidMemberException extends Exception {
	public $previous;

	public function __construct($message = "Invalid member", $code = null, Exception $previous = null) {
		DLOG($message);
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}

}
// ####################################################### ./include/libcompactmvc/linkbuilder.php ####################################################### \\


/**
 * linkbuilder.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class LinkBuilder extends Singleton {

	public function get_link(ActionMapperInterface $mapper, $path0 = null, $path1 = null, $urltail = "", $lang = null) {
		if ($lang == null && defined("ST_LANGUAGE")) {
			$lang = Session::get_instance()->get_property(ST_LANGUAGE);
		}
		$lang = (!is_string($lang)) ? InputProvider::get_instance()->get_var("lang") : $lang;
		return $mapper->get_base_url() . $mapper->get_path($lang, $path0, $path1, $urltail);
	}

}

// ####################################################### ./include/libcompactmvc/linkproperty.php ####################################################### \\


/**
 * linkproperty.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class LinkProperty {
	private $path;
	private $isinsitemap;
	private $controller;
	private $base_path_num;

	public function __construct($path, $isinsitemap, $controller) {
		$this->path = $path;
		$this->isinsitemap = $isinsitemap;
		$this->controller = $controller;
	}

	public function get_path() {
		return $this->path;
	}
	
	public function get_path_level($num) {
		return explode("/", $this->path)[2 + $num];
	}

	public function get_action() {
		return explode("/", $this->path)[2];
	}

	public function get_param($num) {
		return explode("/", $this->path)[3 + $num];
	}

	public function is_in_sitemap() {
		return $this->isinsitemap;
	}
	
	public function get_controller_name() {
		return $this->controller;
	}
	
	public function set_base_path_num($num) {
		$this->base_path_num = $num;
	}
	
	public function get_base_path_num() {
		return $this->base_path_num;
	}
	
}

// ####################################################### ./include/libcompactmvc/map_radius.php ####################################################### \\


/**
 * This class calculates if a given point on earth (designated by its latitude and longitude) lies within a
 * given radius around another point.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MapRadius {
	private $lat_ctr;
	private $lng_ctr;
	private $radius;
	private $unit;
	const UNIT_TYPE_KILOMETRES = 0;
	const UNIT_TYPE_MILES = 1;
	const UNIT_TYPE_NAUTIC_MILES = 2;

	/**
	 * Instantiate this class giving a circle on our globe designated by its coordinates and radius.
	 *
	 * @param Float $lat_ctr
	 *        	latitude of the center
	 * @param Float $lng_ctr
	 *        	longitude of the center
	 * @param Float $radius
	 *        	radius
	 */
	public function __construct($lat_ctr, $lng_ctr, $radius, $unit = MapRadius::UNIT_TYPE_KILOMETRES) {
		$this->lat_ctr = $lat_ctr;
		$this->lng_ctr = $lng_ctr;
		$this->radius = $radius;
		$this->unit = $unit;
	}

	/**
	 * Set the latitude of the center.
	 *
	 * @param Float $lat
	 *        	latitude
	 */
	public function set_lat($lat) {
		$this->lat_ctr = $lat;
	}

	/**
	 * Set the longitude of the center.
	 *
	 * @param Float $lng
	 *        	longitude
	 */
	public function set_lng($lng) {
		$this->lng_ctr = $lng;
	}

	/**
	 * Set the radius.
	 *
	 * @param Float $radius
	 *        	radius
	 */
	public function set_radius($radius) {
		$this->radius = $radius;
	}

	/**
	 * Set the unit type
	 *
	 * @param const $unit
	 *        	the unit type to use
	 */
	public function set_unit($unit) {
		$this->unit = $unit;
	}

	/**
	 * Check if the given point is within the circle.
	 *
	 * @param Float $lat
	 *        	latitude
	 * @param Float $lng
	 *        	longitude
	 */
	public function is_inside($lat, $lng) {
		if ($this->radius == null) {
			throw new Exception("Radius is not set.");
		}
		$theta = $this->lng_ctr - $lng;
		$dist = sin(deg2rad($this->lat_ctr)) * sin(deg2rad($lat)) + cos(deg2rad($this->lat_ctr)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$dist = $this->apply_unit($dist);
		return ($dist <= $this->radius) ? true : false;
	}

	/**
	 * Get the distance between the center and the given point.
	 *
	 * @param Float $lat
	 *        	latitude
	 * @param Float $lng
	 *        	longitude
	 */
	public function get_dist($lat, $lng) {
		$theta = $this->lng_ctr - $lng;
		$dist = sin(deg2rad($this->lat_ctr)) * sin(deg2rad($lat)) + cos(deg2rad($this->lat_ctr)) * cos(deg2rad($lat)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$dist = $this->apply_unit($dist);
		return $dist;
	}

	private function apply_unit($degrees) {
		switch ($this->unit) {
			case 0:
				$dist = $degrees * 111.13384;
				break;
			case 1:
				$dist = $degrees * 69.05482;
				break;
			case 2:
				$dist = $degrees * 59.97662;
		}
		return $dist;
	}

}
// ####################################################### ./include/libcompactmvc/mapcluster.php ####################################################### \\


/**
 * Point clustering on a map.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MapCluster {
	private $single_markers = array();
	private $cluster_markers = array();
	
	// Minimum distance between markers to be included in a cluster, at diff. zoom levels
	private $distance;

	public function __construct($zoom) {
		$this->distance = (10000000 >> $zoom) / 100000;
	}

	public function cluster($markers) {
		// Loop until all markers have been compared.
		while (count($markers)) {
			$marker = array_pop($markers);
			$cluster = array();
			
			// Compare against all markers which are left.
			foreach ($markers as $key => $target) {
				$pixels = abs($marker->lat - $target->lat) + abs($marker->lng - $target->lng);
				
				// If the two markers are closer than given distance remove target marker from array and add it to cluster.
				if ($pixels < $this->distance) {
					unset($markers[$key]);
					$cluster[] = $target;
				}
			}
			
			// If a marker has been added to cluster, add also the one we were comparing to.
			if (count($cluster) > 0) {
				$cluster[] = $marker;
				$this->cluster_markers[] = $cluster;
			} else {
				$this->single_markers[] = $marker;
			}
		}
		return array_merge($this->cluster_markers, $this->single_markers);
	}

}
// ####################################################### ./include/libcompactmvc/mapclustermarker.php ####################################################### \\


/**
 * Map Cluster-Marker
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MapClusterMarker {
	public $lat;
	public $lng;
	public $size;

	public function __construct($lat, $lng, $size) {
		$this->lat = $lat;
		$this->lng = $lng;
		$this->size = $size;
	}

}
// ####################################################### ./include/libcompactmvc/mapmarker.php ####################################################### \\


/**
 * Map Marker
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MapMarker {
	public $lat;
	public $lng;

	public function __construct($lat, $lng) {
		$this->lat = $lat;
		$this->lng = $lng;
	}

}
// ####################################################### ./include/libcompactmvc/multiextender.php ####################################################### \\


// TODO Property ebenfalls behandeln
// TODO Statische Aufrufe ebenfalls behandeln
// TODO Beim Aufruf prüfen ob die Methode im Original Protected ist
// TODO Bei doppelten Properties/Methodes bestimmen welcher Parent gilt

/**
 * Abstrakte Klasse
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 *      
 *       verwendung:
 *       im konstruktor der abgeleiteten klasse die parent classes mit
 *       parent::addExtendedClass($param1, ..., $paramn);
 *       hinzufügen.
 */
abstract class MultiExtender {
	private $methods = array();
	private $properties = array();
	private $objs = array();
	const C_METHOD = 'methods';
	const C_PROPERTY = 'properties';

	/**
	 * ein weitere Extended Class hinzufügen
	 *
	 * @param String $name        	
	 * @param Array $params        	
	 */
	protected function addExtendedClass($className, $params = array()) {
		// Eine Wrapperklasse erstellen, damit die Protected-Methodes Public werden
		$name = self::createWrapperClass($className);
		// Falls nur ein Parameter angegeben wird, diesen in einen Array schreiben
		if (!is_array($params))
			$params = array(
					$params
			);
			// Objecktname defineiren
		$objName = "obj{$name}";
		// Eine Instance der Wrapperklasse anlegen und in den privaten Array speichern
		$this->objs[$objName] = $this->create_user_obj_array($name, $params);
		// Methodennamen der Klasse in einen privaten Array speichern
		$this->addItems($objName, self::C_METHOD, get_class_methods($name));
		// Propertiesnamen der Klasse in einen privaten Array speichern
		$this->addItems($objName, self::C_PROPERTY, array_keys(get_class_vars($name)));
	}

	/**
	 * Methoden oder Properties der Extended-Klasse in den Index einfügen
	 *
	 * @param String $name
	 *        	Objektname
	 * @param Constante $var
	 *        	Art der items
	 * @param Array<String> $array
	 *        	Liste der items
	 */
	private function addItems($name, $list, $array) {
		$newVars = array_fill_keys($array, "\$this->objs['{$name}']");
		$this->$list = array_merge($newVars, $this->$list);
	}

	/**
	 * Aufruf einer Methode.
	 * Der Aufruf wird an das entsprechende Parentobjekt weitergeleitet
	 *
	 * @param String $name        	
	 * @param Array $params        	
	 * @return Variant
	 */
	public function __call($name, $params) {
		if (array_key_exists($name, $this->methods)) {
			$obj = $this->methods[$name];
			$obj = eval("return $obj;");
			return call_user_func_array(array(
					$obj,
					$name
			), $params);
		}
	}

	/**
	 * Aufruf eines Property.
	 * Der Aufruf wird an das entsprechende Parentobjekt weitergeleitet
	 *
	 * @param String $name        	
	 * @return Variant
	 */
	public function __get($name) {
		if (array_key_exists($name, $this->properties)) {
			$obj = $this->properties[$name];
			return eval("return {$obj}->{$name};");
		}
	}

	/**
	 * Erstellen einer Wrapperlasse um die ParentClass
	 *
	 * @param String $className        	
	 * @return String Name der Wrapperklasse
	 */
	final private static function createWrapperClass($className) {
		// ReflectionObject der Klasse zur weiteren Analyse anlegen
		$ref = new ReflectionClass($className);
		$wrapperName = "{$className}Wrapper";
		// Die Classe zusammenstellen
		$lines[] = "class {$wrapperName} extends {$className}{";
		$lines[] = '
			public function __construct(){
				$pStrings  = $params = func_get_args();
				array_walk($pStrings, create_function(\'&$item, $key\', \'$item = "\$params[{$key}]";\'));
				eval(\'parent::__construct(\' . implode(\',\', $pStrings) . \');\');
			}
		';
		// Die Methoden hinzufügen
		self::createWrapperMethodes($lines, $ref);
		$lines[] = '}';
		// Aus allen Zeilen ein String erstellen
		$classPhp = implode("\n", $lines);
		// Die Klasse ausführen
		eval($classPhp);
		return $wrapperName;
	}

	/**
	 * Erstellen der Wrappermethoden für die Wrapperklasse
	 *
	 * @param
	 *        	$lines
	 * @param
	 *        	$ref
	 */
	final static function createWrapperMethodes(&$lines, ReflectionClass $ref) {
		foreach ($ref->getMethods() as $method) {
			if ($method->isProtected()) {
				$params = array();
				$modifiers = $method->getModifiers() - ReflectionMethod::IS_PROTECTED + ReflectionMethod::IS_PUBLIC;
				$modifiers = implode(' ', Reflection::getModifierNames($modifiers));
				foreach ($method->getParameters() as $param) {
					$params[] = $param->getName();
				}
				array_walk($params, create_function('&$item, $key', '$item = "\${$item}";'));
				$paramString = implode(', ', $params);
				$lines[] = "
				{$modifiers} function {$method->name}({$paramString}){
						return parent::{$method->name}({$paramString});
					}
				";
			}
		}
	}

	/**
	 * erstellt ein Object einer Klasse mit dem einer freien Anzahl Paramtern
	 *
	 * @param String $className        	
	 * @param
	 *        	Variant
	 * @return Object
	 *
	 * @example $obj = create_user_obj('myClass', $paramter1, $paramter2);
	 */
	private static function create_user_obj($className) {
		$params = func_get_args();
		$className = array_shift($params);
		return create_user_obj_array($className, $params);
	}

	/**
	 * erstellt ein Object einer Klasse mit dem Array $params als Argumente
	 *
	 * @param String $className        	
	 * @param Array $params        	
	 * @return Object
	 *
	 * @example $obj = create_user_obj('myClass', array($paramter1, $paramter2));
	 */
	private static function create_user_obj_array($className, $params = array()) {
		$pStrings = $params = array_values($params);
		array_walk($pStrings, create_function('&$item, $key', '$item = "\$params[{$key}]";'));
		$php = 'return new $className(' . implode(',', $pStrings) . ');';
		return eval($php);
	}

}
// ####################################################### ./include/libcompactmvc/multipleresultsexception.php ####################################################### \\


/**
 * Multiple Results Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MultipleResultsException extends DBException {
	public $previous;

	public function __construct($message = "Multiple results", $code = 404, Exception $previous = null) {
		DLOG("Multiple results. Reason: $code: $message");
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}

}
// ####################################################### ./include/libcompactmvc/mutex.php ####################################################### \\


/**
 * Mutex
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Mutex {
	private $key;
	private $token;
	private $delay;
	private $maxwait;

	public function __construct($key) {
		$this->key = $key;
		$this->token = md5(microtime() . rand(0, 99999999));
		$this->delay = 2;
		$this->register();
	}

	public function __destruct() {
		$this->unregister();
	}

	public function lock($maxwait = 60) {
		echo ("Lock\n");
		$start = time();
		$this->maxwait = $maxwait;
		while (time() < $start + $maxwait) {
			if (count($this->get_requests()) == 0) {
				$this->set_request();
				// usleep($this->delay * 5000);
				if (count($this->get_requests()) == 1) {
					if (count($this->get_acks()) + 1 == count($this->get_registrations())) {
						return;
					}
				}
			}
			if (count($this->get_requests()) == 1) {
				if (!$this->is_ack_set() && !$this->is_request_set()) {
					$this->set_ack();
				}
			}
			if (count($this->get_requests()) > 1) {
				echo ("Increasing delay: " . $this->delay . "\n");
				$this->delay += 1;
			}
			$this->unlock();
			usleep(rand(0, $this->delay * 500));
		}
		throw new MutexException("max wait time elapsed", 500);
	}

	public function unlock() {
		echo ("UnLock\n");
		foreach ($this->get_acks() as $ack) {
			echo ("Deleting " . $ack . "\n");
			RedisAdapter::get_instance()->delete($ack, false);
		}
		foreach ($this->get_requests() as $request) {
			echo ("Deleting " . $request . "\n");
			RedisAdapter::get_instance()->delete($request, false);
		}
	}

	private function register() {
		echo ("Registering " . REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->set(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token, 1, false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $elem->token, $this->maxwait);
	}

	private function unregister() {
		echo ("Unregistering " . REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->delete(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token, false);
	}

	private function get_registrations() {
		return RedisAdapter::get_instance()->keys(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_*", false);
	}

	private function set_request() {
		echo ("Setting request " . REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->set(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token, false);
	}

	private function del_request() {
		echo ("Deleting request " . REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->delete(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token, false);
	}

	private function get_requests() {
		return RedisAdapter::get_instance()->keys(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_*", false);
	}

	private function is_request_set() {
		return (RedisAdapter::get_instance()->get(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token, false) != null);
	}

	private function set_ack() {
		echo ("Set ACK " . REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->set(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token, false);
	}

	private function del_ack() {
		echo ("Del ACK " . REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->delete(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token, false);
	}

	private function get_acks() {
		return RedisAdapter::get_instance()->keys(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_*", false);
	}

	private function is_ack_set() {
		return (RedisAdapter::get_instance()->get(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token, false) != null);
	}

}
// ####################################################### ./include/libcompactmvc/mutexexception.php ####################################################### \\


/**
 * Mutex Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MutexException extends Exception {

}
// ####################################################### ./include/libcompactmvc/mysqladapter.php ####################################################### \\


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
// ####################################################### ./include/libcompactmvc/network.php ####################################################### \\


/**
 * Network helper
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Network {

	/**
	 *
	 * @return returns the real client IP, even if a proxy is used
	 */
	public static function get_real_client_ip() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

}
// ####################################################### ./include/libcompactmvc/ormclientcomponent.php ####################################################### \\


/**
 * ormclientcomponent.php
 *
 * @author		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ORMClientComponent extends CMVCComponent {
	
	public function get_component_id() {
		DLOG();
		return "ormclientcomponent";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		$tables = (new TableDescription())->get_all_tables();
		$this->get_view()->set_value("tables", $tables);
		foreach ($tables as $table) {
			if (!class_exists($table))
				throw new DBException("Missing DTO class: " . $table);
			$subject = new $table();
			if (!is_subclass_of($subject, "DbObject"))
				throw new DBException("Class " . $table . " must be derived from DbObject.");
			$class = new ReflectionClass($table);
			$methods = array();
			$am = $class->getMethods();
			foreach ($am as $method) {
				if ($method->class == $table && 
					$method->name != "get_endpoint" &&
					$method->name != "init" &&
					$method->name != "on_after_load" &&
					$method->name != "on_before_save" &&
					$method->name != "save" &&
					$method->name != "delete" &&
					$method->name != "unset" &&
					$method->name != "jsonSerialize")
					$methods[] = $method->name;
			}
			$methods[] = "update_all";
			$this->get_view()->set_value("methods_" . $table, $methods);
			$this->get_view()->set_value("endpoint_" . $table, $subject->get_endpoint());
			foreach ($am as $method) {
				if ($method->class == $table || 
						$method->name == "update_all")
					$this->get_view()->set_value("method_" . $table. "::" . $method->name, count($method->getParameters()) > 0);
			}
		}
		$this->get_view()->set_value("ws_server_uri", WSAdapter::get_instance()->get_srv_url());
		$this->get_view()->set_template(0, "__ormclient.tpl");
		$this->set_mime_type(MIME_TYPE_JS);
	}

}
// ####################################################### ./include/libcompactmvc/ormclientcomponentqt.php ####################################################### \\


/**
 * ormclientcomponentqt.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ORMClientComponentQt extends ORMClientComponent {
	
	public function get_component_id() {
		DLOG();
		return "ormclientcomponentqt";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		$this->get_view()->set_template(0, "__ormclientqt.tpl");
	}

}
// ####################################################### ./include/libcompactmvc/password.php ####################################################### \\



/**
 * A Compatibility library with PHP 5.5's simplified password hashing API.
 *
 * @author Anthony Ferrara <ircmaxell@php.net>
 * @license http://www.opensource.org/licenses/mit-license.html MIT License
 * @copyright 2012 The Authors
 */
	if (!defined('PASSWORD_BCRYPT')) {
		/**
		 * PHPUnit Process isolation caches constants, but not function declarations.
		 * So we need to check if the constants are defined separately from
		 * the functions to enable supporting process isolation in userland
		 * code.
		 */
		define('PASSWORD_BCRYPT', 1);
		define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);
		define('PASSWORD_BCRYPT_DEFAULT_COST', 10);
	}
	
	if (!function_exists('password_hash')) {

		/**
		 * Hash the password using the specified algorithm
		 *
		 * @param string $password
		 *        	The password to hash
		 * @param int $algo
		 *        	The algorithm to use (Defined by PASSWORD_* constants)
		 * @param array $options
		 *        	The options for the algorithm to use
		 *        	
		 * @return string|false The hashed password, or false on error.
		 */
		function password_hash($password, $algo, array $options = array()) {
			if (!function_exists('crypt')) {
				trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
				return null;
			}
			if (is_null($password) || is_int($password)) {
				$password = ( string ) $password;
			}
			if (!is_string($password)) {
				trigger_error("password_hash(): Password must be a string", E_USER_WARNING);
				return null;
			}
			if (!is_int($algo)) {
				trigger_error("password_hash() expects parameter 2 to be long, " . gettype($algo) . " given", E_USER_WARNING);
				return null;
			}
			$resultLength = 0;
			switch ($algo) {
				case PASSWORD_BCRYPT:
					$cost = PASSWORD_BCRYPT_DEFAULT_COST;
					if (isset($options['cost'])) {
						$cost = ( int ) $options['cost'];
						if ($cost < 4 || $cost > 31) {
							trigger_error(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), E_USER_WARNING);
							throw new Exception(sprintf("password_hash(): Invalid bcrypt cost parameter specified: %d", $cost), 500);
							return null;
						}
					}
					// The length of salt to generate
					$raw_salt_len = 16;
					// The length required in the final serialization
					$required_salt_len = 22;
					$hash_format = sprintf("$2y$%02d$", $cost);
					// The expected length of the final crypt() output
					$resultLength = 60;
					break;
				default :
					trigger_error(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), E_USER_WARNING);
					throw new Exception(sprintf("password_hash(): Unknown password hashing algorithm: %s", $algo), 500);
					return null;
			}
			$salt_req_encoding = false;
			if (isset($options['salt'])) {
				switch (gettype($options['salt'])) {
					case 'NULL':
					case 'boolean':
					case 'integer':
					case 'double':
					case 'string':
						$salt = ( string ) $options['salt'];
						break;
					case 'object':
						if (method_exists($options['salt'], '__tostring')) {
							$salt = ( string ) $options['salt'];
							break;
						}
					case 'array':
					case 'resource':
					default :
						trigger_error('password_hash(): Non-string salt parameter supplied', E_USER_WARNING);
						throw new Exception('password_hash(): Non-string salt parameter supplied', 500);
						return null;
				}
				if (PasswordCompat\binary\_strlen($salt) < $required_salt_len) {
					trigger_error(sprintf("password_hash(): Provided salt is too short: %d expecting %d", PasswordCompat\binary\_strlen($salt), $required_salt_len), E_USER_WARNING);
					throw new Exception(sprintf("password_hash(): Provided salt is too short: %d expecting %d", PasswordCompat\binary\_strlen($salt), $required_salt_len), 500);
					return null;
				} elseif (0 == preg_match('#^[a-zA-Z0-9./]+$#D', $salt)) {
					$salt_req_encoding = true;
				}
			} else {
				$buffer = '';
				$buffer_valid = false;
				if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
					$buffer = mcrypt_create_iv($raw_salt_len, MCRYPT_DEV_URANDOM);
					if ($buffer) {
						$buffer_valid = true;
					}
				}
				if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
					$strong = false;
					$buffer = openssl_random_pseudo_bytes($raw_salt_len, $strong);
					if ($buffer && $strong) {
						$buffer_valid = true;
					}
				}
				if (!$buffer_valid && @is_readable('/dev/urandom')) {
					$file = fopen('/dev/urandom', 'r');
					$read = 0;
					$local_buffer = '';
					while ($read < $raw_salt_len) {
						$local_buffer .= fread($file, $raw_salt_len - $read);
						$read = PasswordCompat\binary\_strlen($local_buffer);
					}
					fclose($file);
					if ($read >= $raw_salt_len) {
						$buffer_valid = true;
					}
					$buffer = str_pad($buffer, $raw_salt_len, "\0") ^ str_pad($local_buffer, $raw_salt_len, "\0");
				}
				if (!$buffer_valid || PasswordCompat\binary\_strlen($buffer) < $raw_salt_len) {
					$buffer_length = PasswordCompat\binary\_strlen($buffer);
					for($i = 0; $i < $raw_salt_len; $i++) {
						if ($i < $buffer_length) {
							$buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
						} else {
							$buffer .= chr(mt_rand(0, 255));
						}
					}
				}
				$salt = $buffer;
				$salt_req_encoding = true;
			}
			if ($salt_req_encoding) {
				// encode string with the Base64 variant used by crypt
				$base64_digits = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';
				$bcrypt64_digits = './ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
				
				$base64_string = base64_encode($salt);
				$salt = strtr(rtrim($base64_string, '='), $base64_digits, $bcrypt64_digits);
			}
			$salt = PasswordCompat\binary\_substr($salt, 0, $required_salt_len);
			
			$hash = $hash_format . $salt;
			
			$ret = crypt($password, $hash);
			
			if (!is_string($ret) || PasswordCompat\binary\_strlen($ret) != $resultLength) {
				return false;
			}
			
			return $ret;
		}

		/**
		 * Get information about the password hash.
		 * Returns an array of the information
		 * that was used to generate the password hash.
		 *
		 * array(
		 * 'algo' => 1,
		 * 'algoName' => 'bcrypt',
		 * 'options' => array(
		 * 'cost' => PASSWORD_BCRYPT_DEFAULT_COST,
		 * ),
		 * )
		 *
		 * @param string $hash
		 *        	The password hash to extract info from
		 *        	
		 * @return array The array of information about the hash.
		 */
		function password_get_info($hash) {
			$return = array(
					'algo' => 0,
					'algoName' => 'unknown',
					'options' => array()
			);
			if (PasswordCompat\binary\_substr($hash, 0, 4) == '$2y$' && PasswordCompat\binary\_strlen($hash) == 60) {
				$return['algo'] = PASSWORD_BCRYPT;
				$return['algoName'] = 'bcrypt';
				list($cost) = sscanf($hash, "$2y$%d$");
				$return['options']['cost'] = $cost;
			}
			return $return;
		}

		/**
		 * Determine if the password hash needs to be rehashed according to the options provided
		 *
		 * If the answer is true, after validating the password using password_verify, rehash it.
		 *
		 * @param string $hash
		 *        	The hash to test
		 * @param int $algo
		 *        	The algorithm used for new password hashes
		 * @param array $options
		 *        	The options array passed to password_hash
		 *        	
		 * @return boolean True if the password needs to be rehashed.
		 */
		function password_needs_rehash($hash, $algo, array $options = array()) {
			$info = password_get_info($hash);
			if ($info['algo'] !== ( int ) $algo) {
				return true;
			}
			switch ($algo) {
				case PASSWORD_BCRYPT:
					$cost = isset($options['cost']) ? ( int ) $options['cost'] : PASSWORD_BCRYPT_DEFAULT_COST;
					if ($cost !== $info['options']['cost']) {
						return true;
					}
					break;
			}
			return false;
		}

		/**
		 * Verify a password against a hash using a timing attack resistant approach
		 *
		 * @param string $password
		 *        	The password to verify
		 * @param string $hash
		 *        	The hash to verify against
		 *        	
		 * @return boolean If the password matches the hash
		 */
		function password_verify($password, $hash) {
			if (!function_exists('crypt')) {
				trigger_error("Crypt must be loaded for password_verify to function", E_USER_WARNING);
				throw new Exception("Crypt must be loaded for password_verify to function", 500);
				return false;
			}
			$ret = crypt($password, $hash);
			if (!is_string($ret) || PasswordCompat\binary\_strlen($ret) != PasswordCompat\binary\_strlen($hash) || PasswordCompat\binary\_strlen($ret) <= 13) {
				return false;
			}
			
			$status = 0;
			for($i = 0; $i < PasswordCompat\binary\_strlen($ret); $i++) {
				$status |= (ord($ret[$i]) ^ ord($hash[$i]));
			}
			
			return $status === 0;
		}
	}



// namespace_PasswordCompat\binary {

	if (!function_exists('_strlen')) {

		/**
		 * Count the number of bytes in a string
		 *
		 * We cannot simply use strlen() for this, because it might be overwritten by the mbstring extension.
		 * In this case, strlen() will count the number of *characters* based on the internal encoding. A
		 * sequence of bytes might be regarded as a single multibyte character.
		 *
		 * @param string $binary_string
		 *        	The input string
		 *        	
		 * @internal
		 *
		 * @return int The number of bytes
		 */
		function _strlen($binary_string) {
			if (function_exists('mb_strlen')) {
				return mb_strlen($binary_string, '8bit');
			}
			return strlen($binary_string);
		}

		/**
		 * Get a substring based on byte limits
		 *
		 * @see _strlen()
		 *
		 * @param string $binary_string
		 *        	The input string
		 * @param int $start        	
		 * @param int $length        	
		 *
		 * @internal
		 *
		 * @return string The substring
		 */
		function _substr($binary_string, $start, $length) {
			if (function_exists('mb_substr')) {
				return mb_substr($binary_string, $start, $length, '8bit');
			}
			return substr($binary_string, $start, $length);
		}

		/**
		 * Check if current PHP version is compatible with the library
		 *
		 * @return boolean the check result
		 */
		function check() {
			static $pass = NULL;
			
			if (is_null($pass)) {
				if (function_exists('crypt')) {
					$hash = '$2y$04$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
					$test = crypt("password", $hash);
					$pass = $test == $hash;
				} else {
					$pass = false;
				}
			}
			return $pass;
		}
	}


// ####################################################### ./include/libcompactmvc/querybuilder.php ####################################################### \\


/**
 * SQL query builder.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class QueryBuilder extends DbAccess {
	private $td;

	/**
	 */
	public function __construct() {
		parent::__construct();
		$this->td = new TableDescription();
	}
	
	private function selcols($tablename, $constraint) {
		$ci = $this->td->columninfo($tablename);
		$selcols = "";
		if (!is_array($constraint)) {
			if (is_array($this->td->primary_keys($tablename)) && get_class($constraint) == "DbConstraint") {
				if (is_string($constraint->get_query_info()["count"])) {
					$selcols = "COUNT(" . $constraint->get_query_info()["count"] . ") AS count";
				} else if ($constraint->get_query_info()["count"] == true) {
// 					$selcols = "COUNT(" . $this->td->primary_keys($tablename)[0] . ") AS count";
					$selcols = "COUNT(*) AS count";
				}
			}
		} 
		if ($selcols == "") {
			foreach ($ci as $column) {
				if (strtolower(substr($column->Type, 0, 6)) == "binary") {
					$selcols .= "HEX(" . $column->Field . ") AS " . $column->Field . ", ";
				} else {
					$selcols .= $column->Field . ", ";
				}
			}
			$selcols = substr($selcols, 0, -2);
		}
		return $selcols;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function select($tablename, $constraint = array()) {
		$q = "SELECT " . $this->selcols($tablename, $constraint) . " FROM `" . $tablename . "`";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= " WHERE " . $constraint->get_query_info()["where_string"];
		} else {
			if (count($constraint) > 0) {
				$q .= " WHERE ";
				$q .= $this->where_substring($tablename, $constraint);
			}
		}
		DLOG($q);
		return $q;
	}
	
	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function like($tablename, $constraint = array()) {
		$q = "SELECT " . $this->selcols($tablename, $constraint) . " FROM `" . $tablename . "`";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= " WHERE " . $constraint->get_query_info()["where_string"];
		} else {
			if (count($constraint) > 0) {
				$q .= " WHERE ";
				$q .= $this->where_substring($tablename, $constraint, array(), DbFilter::COMPARE_LIKE);
			}
		}
		DLOG($q);
		return $q;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $fields
	 */
	public function insert($tablename, $fields) {
		$nofields = true;
		$desc = $this->td->columninfo($tablename);
		$q = "INSERT INTO `" . $tablename . "` (";
		foreach ($desc as $key => $val) {
			if (array_key_exists($val->Field, $fields)) {
				$q .= "`" . $val->Field . "`, ";
				$nofields = false;
			}
		}
		$q = substr($q, 0, strlen($q) - 2);
		if ($nofields) {
			return $q . " () VALUES ()";
		}
		$q .= ") VALUES (";
		foreach ($desc as $key => $val) {
			if (array_key_exists($val->Field, $fields)) {
				if (strtolower(substr($val->Type, 0, 6)) == "binary") {
					if($this->sqlnull($fields[$val->Field]) == "null") {
						$q .= "null, ";
					} else {
						$q .= "UNHEX('" . $this->escape($fields[$val->Field]) . "'), ";
					}
				} else {
					$q .= $this->sqlnull($this->escape($fields[$val->Field])) . ", ";
				}
			}
		}
		$q = substr($q, 0, strlen($q) - 2);
		$q .= ")";
		DLOG($q);
		return $q;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $fields
	 * @param unknown_type $constraint
	 */
	public function update($tablename, $fields, $constraint = array()) {
		$desc = $this->td->columninfo($tablename);
		$q = "UPDATE `" . $tablename . "` SET ";
		foreach ($desc as $key => $val) {
			if (array_key_exists($val->Field, $fields)) {
				if (strtolower(substr($val->Type, 0, 6)) == "binary") {
					if($this->sqlnull($fields[$val->Field]) == "null") {
						$q .= "`" . $val->Field . "` = null, ";
					} else {
						$q .= "`" . $val->Field . "` = UNHEX('" . $this->escape($fields[$val->Field]) . "'), ";
					}
				} else {
					$q .= "`" . $val->Field . "` = " . $this->sqlnull($this->escape($fields[$val->Field])) . ", ";
				}
			}
		}
		$q = substr($q, 0, strlen($q) - 2);
		$q .= " WHERE ";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= $constraint->get_query_info()["where_string"];
		} else {
			$q .= $this->where_substring($tablename, $constraint);
		}
		DLOG($q);
		return $q;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function delete($tablename, $constraint = array()) {
		$desc = $this->td->columninfo($tablename);
		$q = "DELETE FROM `" . $tablename . "` WHERE ";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= $constraint->get_query_info()["where_string"];
		} else {
			$q .= $this->where_substring($tablename, $constraint);
		}
		DLOG($q);
		return $q;
	}
	
	/**
	 * 
	 * @param array $constraint
	 * @param array $filter
	 * @param unknown $comparator
	 * @param unknown $logic_op
	 * @return string
	 */
	public function where_substring($table = null, $constraint = array(), $filter = array(), $comparator = DbFilter::COMPARE_EQUAL, $logic_op = DbFilter::LOGIC_OPERATOR_AND) {
		if ($table == null) return "1";
		if (is_array($constraint) && count($constraint) == 0 && count($filter) == 0) return "1";
		$desc = $this->td->columninfo($table);
		$first = true;
		$qstr1 = "(";
		foreach ($constraint as $col => $val) {
			foreach ($desc as $k => $v) {
				if ($v->Field == $col) {
					if (!$first) $qstr1 .= $logic_op . " ";
					$first = false;
					if ($comparator == DbFilter::COMPARE_IN || $comparator == DbFilter::COMPARE_NOT_IN) {
						if (!is_array($val)) throw new DBException("IN comparator requires array(s) as column filter.");
						$first2 = true;
						$qstr2 = "(";
						foreach ($val as $k2 => $v2) {
							if (!$first2) $qstr2 .= ", ";
							$first2 = false;
							if (strtolower(substr($v->Type, 0, 6)) == "binary") {
								if($this->sqlnull($this->escape($val)) == "NULL") {
									$qstr2 .= "NULL";
								} else {
									$qstr2 .= "UNHEX('" . $this->escape($val) . "')";
								}
							} else {
								$qstr2 .= $this->sqlnull($this->escape($v2));
							}
						}
						$qstr2 .= ")";
						$qstr1 .= "`" . $col . "` " . $comparator . " " . $qstr2 . " ";
					} else {
						if (strtolower(substr($v->Type, 0, 6)) == "binary") {
							if($this->sqlnull($this->escape($val)) == "NULL") {
								$qstr1 .= "`" . $col . "` = NULL ";
							} else {
								$qstr1 .= "`" . $col . "` " . $this->comparator($comparator, $val) . " UNHEX('" . $this->escape($val) . "') ";
							}
						} else {
							$qstr1 .= "`" . $col . "` " . $this->comparator($comparator, $val) . " " . $this->sqlnull($this->escape($val)) . " ";
						}
					}
				}
			}
		}
		$qstr2 = "";
		foreach ($filter as $filter) {
			if (!$first) $qstr2 .= " " . $logic_op . " ";
			$first = false;
			$qstr2 .= $filter->get_query_substring();
		}
// 		$qstr1 = substr($qstr1, 0, -1);
		$qstr2 .= ")";
		$qstr = $qstr1 . $qstr2;
		DLOG($qstr);
		return $qstr;
	}
	
	/**
	 *
	 * @param unknown $val
	 * @return string|unknown
	 */
	protected function comparator($comparator, $val) {
		if ($comparator == DbFilter::COMPARE_EQUAL)
			return $this->cmpissqlnull($val);
		else if ($comparator == DbFilter::COMPARE_NOT_EQUAL)
			return $this->cmpisnotsqlnull($val);
		else
			return $comparator;
	}
	

}

// ####################################################### ./include/libcompactmvc/rbrc.php ####################################################### \\


/**
 * Request Based Response Cache
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class RBRC {
	private static $instance;
	private $rhash;

	/**
	 *
	 * @param unknown_type $rdata        	
	 * @param unknown_type $observe_headers        	
	 */
	private function __construct($rdata, $observe_headers) {
		DLOG();
		if ($observe_headers) {
			$this->rhash = md5(serialize($rdata) . serialize(apache_request_headers()));
		} else {
			$this->rhash = md5(serialize($rdata));
		}
	}

	/**
	 *
	 * @return returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($rdata, $observe_headers = true) {
		DLOG();
		if (!isset(self::$instance)) {
			self::$instance = new RBRC($rdata, $observe_headers);
		}
		return self::$instance;
	}

	/**
	 *
	 * @param unknown_type $data        	
	 */
	public function put($data) {
		RedisAdapter::get_instance()->set($this->rhash, $data);
		RedisAdapter::get_instance()->expire($this->rhash, REDIS_KEY_RCACHE_TTL);
	}

	/**
	 */
	public function get() {
		$data = RedisAdapter::get_instance()->get($this->rhash);
		RedisAdapter::get_instance()->expire($this->rhash, REDIS_KEY_RCACHE_TTL);
		return $data;
	}

}// ####################################################### ./include/libcompactmvc/rbrcexception.php ####################################################### \\


/**
 * Invalid Member Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class RBRCException extends Exception {

}
// ####################################################### ./include/libcompactmvc/redirectexception.php ####################################################### \\


/**
 * Redirect Exception
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class RedirectException extends Exception {
	private $is_internal;

	/**
	 *
	 * @param String $location For external redirects the target URL or route id, for internal redirects the target route id.
	 * @param int $code	The HTTP status code to use for external redirects.
	 * @param Boolean $internal Set to true for internal redirects, false for external redirects.
	 */
	public function __construct($location = null, $code = 302, $internal = false) {
		DLOG($location);
		if (!$internal) {
			if (lc(substr($location, 0,4)) != "http") {
				$location = lnk_by_route_id($location);
			}
		}
		$this->message = $location;
		$this->code = $code;
		$this->is_internal = $internal;
		DLOG($this->getTraceAsString());
	}

	/**
	 * @return Boolean Is this an internal redirection?
	 */
	public function is_internal() {
		DLOG(print_r($this->is_internal, true));
		return $this->is_internal;
	}

}
// ####################################################### ./include/libcompactmvc/redisadapter.php ####################################################### \\


/**
 * Redis Adapter
 * With additional variable cache.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
if (!extension_loaded("redis")  && !REDIS_FILE_FALLBACK_DISABLED) {
	class Redis {
		private $redisdir;
		private $content;

		public function __construct() {
			DLOG("WARNING! Redis is running in fallback (file) mode!");
			$this->redisdir = TEMP_DIR . "/redis.dat/";
			if (!is_dir($this->redisdir))
				mkdir($this->redisdir);
		}

		public function __destruct() {
		}

		public function connect($host, $port) {
		}

		public function get($key) {
			$fname = $this->redisdir . md5($key);
			if (!file_exists($fname))
				return false;
			$ttl = filemtime($fname);
			$val = file_get_contents($this->redisdir . md5($key));
			//touch($fname, $ttl);
			if ($ttl < time()) {
				unlink($fname);
				return false;
			}
			return $val;
		}

		public function set($key, $val) {
			$fname = $this->redisdir . md5($key);
			file_put_contents($fname, $val);
			touch($fname, time() + 3600 * 24 * 356 * 10);
		}

		public function expire($key, $ttl) {
			$fname = $this->redisdir . md5($key);
			if (!file_exists($fname))
				return false;
			touch($fname, time() + $ttl);
		}

		public function delete($key) {
			@unlink($this->redisdir . md5($key));
		}

		public function keys($filter) {
			$regex = "/" . str_replace("*", ".*", $filter) . "/";
			$resarr = array();
			if ($handle = opendir($this->redisdir)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						echo "$entry\n";
						preg_match($regex, $entry, $outarray);
						if ($outarray[0] == $entry)
							$resarr[] = $entry;
					}
				}
				closedir($handle);
			}
			return $resarr;
		}
	
	}
}
class RedisAdapter {
	private static $instance;
	private $redis;
	private $data;

	private function __construct() {
		DLOG();
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT);
		$this->data = array();
	}

	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new RedisAdapter();
		}
		return self::$instance;
	}

	public function get($key, $use_local_cache = true) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '")');
		if ($use_local_cache) {
			if (array_key_exists($key, $this->data)) {
				return $this->data[$key];
			}
		}
		return $this->redis->get($key);
	}

	public function set($key, $val, $use_local_cache = true) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '", <content>)');
		if ($use_local_cache) {
			$this->data[$key] = $val;
		}
		return @$this->redis->set($key, $val);
	}

	public function expire($key, $ttl) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '", ' . $ttl . ')');
		return $this->redis->expire($key, $ttl);
	}

	public function keys($key) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '")');
		$keys = $this->redis->keys($key);
		foreach ($keys as $k => $v) {
			$keys[$k] = substr($v, strlen(REDIS_KEY_PREFIX));
		}
		return $keys;
	}

	public function delete($key) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '")');
		unset($this->data[$key]);
		return $this->redis->delete($key);
	}
	
	public function flushall() {
		DLOG();
		return $this->redis->flushAll();
	}
	
	public function flushdb() {
		DLOG();
		return $this->redis->flushDB();
	}
	
}
// ####################################################### ./include/libcompactmvc/securecrudcomponent.php ####################################################### \\


/**
 * securecrudcomponent.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class SecureCRUDComponent extends CMVCCRUDComponent {
	private $__auth_token_supported = true;
	
	/********************************************************************************************************
	 * The following methods MUST be overwritten to ensure everybody can only access his own data,
	 * based on PHP session or what ever determines the user object, that comes out of
	 * get_current_user_record().
	 ********************************************************************************************************/
	
	/**
	 * Return the user DTO that is currently logged in
	 */
	abstract protected function get_current_user_record();
	
	/**
	 * Return the referenced user DTO belonging to $dto
	 * 
	 * @param DbObject $dto
	 */
	abstract protected function get_user_for_dto($dto);
	
	/**
	 * Return an array of publicly callable method namess of this endpoint, that do not require authentication.
	 */
	abstract protected function get_public_methods();
	
	/**
	 * Table name in which the user records are stored
	 */
	abstract protected function get_user_table_name();

	/********************************************************************************************************
	 * The following methods CAN be overwritten, to support an additional auth token. For a working 
	 * authentication the current valid token must be stored in a column in the user table. Further the
	 * token has to be sent in the request in a special objekt member field or a variable like a GET 
	 * parameter.
	 ********************************************************************************************************/
	
	/**
	 * Name of the variable that carries the auth token within the requests
	 */
	protected function get_auth_token_varname() {
		DLOG();
		$this->__auth_token_supported = false;
	}
	
	/**
	 * Name of the column that holds the valid token in the user table.
	 */
	protected function get_auth_token_colname() {
		DLOG();
		$this->__auth_token_supported = false;
	}
	
	
	
	/********************************************************************************************************
	 * Implementation.
	 ********************************************************************************************************/
	protected function dto_belongs_to_user($dto, $user) {
		DLOG();
		$duser = $this->get_user_for_dto($dto);
		return $duser->{$duser->__pk} == $user->{$user->__pk};
	}
	
	protected function get_expected_dto_type() {
		DLOG();
		return $this->get_table_name();
	}
	
	protected function pre_run() {
		DLOG();
		parent::pre_run();
		$atvn = $this->get_auth_token_varname();
		$atcn = $this->get_auth_token_colname();
		$utable = $this->get_user_table_name();
		$stype = (isset($this->__subject->__type) && $this->__subject->__type != null && is_string($this->__subject->__type)) ? $this->__subject->__type : "";
		$otype = (isset($this->__object) && isset($this->__object->__type) && $this->__object->__type != null && is_string($this->__object->__type)) ? $this->__object->__type : "";
		try {
			$method = $this->path(2);
			$subject = $this->get_subject();
			if (is_callable(array(
					$subject,
					$method
			))) {
				DLOG("Checking access rights for requested RPC: " . $stype . "::" . $method . "(" . $otype . ") HTTP verb: " . $this->get_http_verb());
				foreach ($this->get_public_methods() as $pm) {
					if ($method == $pm) return;
				}
				if ($this->__auth_token_supported) {
					$user = new $utable();
					try {
						try {
							$user->by(array($atcn => $subject->$atvn));
						} catch (InvalidMemberException $e) {
							$user->by(array($atcn => $this->$atvn));
						}
					} catch (InvalidMemberException $e1) {
						$msg = "Missing user key! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					} catch (EmptyResultException $e2) {
						$msg = "Invalid user key! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					}
				}
			}
		} catch (DBException $e3) {
			throw $e3;
		} catch (InvalidMemberException $e4) {
			// that's ok, we possibly have a create/update action:
		}
	}
	
	protected function post_run() {
		DLOG();
		parent::post_run();
		$atvn = $this->get_auth_token_varname();
		$atcn = $this->get_auth_token_colname();
		if (is_array($this->get_response())) {
			$found = false;
			foreach ($this->get_response() as $key => $val) {
				try {
					$dusr = $this->get_user_for_dto($val);
					if ($dusr == null) {
						$found = true;
					} else if ($this->__auth_token_supported && $dusr->{$atcn} != $this->get_subject()->$atvn) {
						$found = true;
					} else if (!$this->dto_belongs_to_user($val, $this->get_current_user_record())) {
						$found = true;
					}
				} catch (Exception $e) {
					$found = true;
				}
			}
			if ($found) {
				$msg = "Array contains foreign/invalid content! Access forbidden!";
				ELOG($msg);
				throw new DBException($msg, 403);
			}
		} else if (is_object($this->get_response()) && get_class($this->get_response()) == $this->get_expected_dto_type()) {
			$cm = $this->get_called_method();
			foreach ($this->get_public_methods() as $pm) {
				if ($cm == $pm) return;
			}
			if ($this->get_user_for_dto($this->get_response()) == null) {
				$msg = "Response content does not belong to an existing user! Access forbidden!";
				ELOG($msg);
				throw new DBException($msg, 403);
			}
			if ($this->__auth_token_supported) {
				try {
					if ($this->get_user_for_dto($this->get_response())->$atcn != $this->get_subject()->$atvn) {
						$msg = "Response content does not match the provided user key! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					}
				} catch (InvalidMemberException $e) {
					try {
						if ($this->get_user_for_dto($this->get_response())->$atcn != $this->$atvn) {
							$msg = "Response content does not match the provided user key! Access forbidden!";
							ELOG($msg);
							throw new DBException($msg, 403);
						}
					} catch (InvalidMemberException $e) {
						$msg = "Auth token is misssing! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					}
				}
			}
		}
	}
	
	
}
// ####################################################### ./include/libcompactmvc/session.php ####################################################### \\


/**
 * Session handler
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Session {
	private $session_id;
	// REMEMBER!!!
	// NEVER use the $_SESSION array directly when using this class!
	// your data will get lost!

	// keeps instance of the classs
	private static $instance;

	// contains all session data
	private static $parray;

	// private constructor prevents direct instantiation
	private function __construct() {
		DLOG();
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
			WLOG("WARNING!!! DYNAMIC SESSION ID IS IN USE! THIS MAY CAUSE TROUBLE IN CONJUNCTION WITH AJAX!");
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
		if (self::$instance == null) {
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
		if (self::$instance == null) {
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
			if (!SESSION_INSECURE_COOKIE) {
				if (is_tls_con()) {
					ini_set('session.cookie_secure', 1);
				}
			}
		} else if (is_tls_con()) {
			ini_set('session.cookie_secure', 1);
		}
		session_start();
		session_regenerate_id(true);
		self::$instance = null;
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
	public function switch_to($id) {
		DLOG("Saving current content: " . var_export(self::$parray, true));
		RedisAdapter::get_instance()->set("SESSION_" . $this->session_id, serialize(self::$parray));
		RedisAdapter::get_instance()->expire("SESSION_" . $this->session_id, SESSION_TIMEOUT);
		session_destroy();
		session_id($id);
		session_start();
		$this->session_id = $id;
		DLOG("Session ID = " . $this->session_id);
		self::$parray = unserialize(RedisAdapter::get_instance()->get("SESSION_" . $this->session_id));
		ActiveSessions::get_instance()->update();
	}

}
// ####################################################### ./include/libcompactmvc/simplehtmldom.php ####################################################### \\

/**
 * Website: http://sourceforge.net/projects/simplehtmldom/
 * Acknowledge: Jose Solorzano (https://sourceforge.net/projects/php-html/)
 * Contributions by:
 * Yousuke Kumakura (Attribute filters)
 * Vadim Voituk (Negative indexes supports of "find" method)
 * Antcs (Constructor with automatically load contents either text or file/url)
 *
 * all affected sections have comments starting with "PaperG"
 *
 * Paperg - Added case insensitive testing of the value of the selector.
 * Paperg - Added tag_start for the starting index of tags - NOTE: This works but not accurately.
 * This tag_start gets counted AFTER \r\n have been crushed out, and after the remove_noice calls so it will not reflect the REAL position of the tag in the source,
 * it will almost always be smaller by some amount.
 * We use this to determine how far into the file the tag in question is. This "percentage will never be accurate as the $dom->size is the "real" number of bytes the dom was created from.
 * but for most purposes, it's a really good estimation.
 * Paperg - Added the forceTagsClosed to the dom constructor. Forcing tags closed is great for malformed html, but it CAN lead to parsing errors.
 * Allow the user to tell us how much they trust the html.
 * Paperg add the text and plaintext to the selectors for the find syntax. plaintext implies text in the innertext of a node. text implies that the tag is a text node.
 * This allows for us to find tags based on the text they contain.
 * Create find_ancestor_tag to see if a tag is - at any level - inside of another specific tag.
 * Paperg: added parse_charset so that we know about the character set of the source document.
 * NOTE: If the user's system has a routine called get_last_retrieve_url_contents_content_type availalbe, we will assume it's returning the content-type header from the
 * last transfer or curl_exec, and we will parse that and use it in preference to any other method of charset detection.
 *
 * Found infinite loop in the case of broken html in restore_noise. Rewrote to protect from that.
 * PaperG (John Schlick) Added get_display_size for "IMG" tags.
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @author S.C. Chen <me578022@gmail.com>
 * @author John Schlick
 * @author Rus Carroll
 * @version 1.5 ($Rev: 196 $)
 * @package PlaceLocalInclude
 * @subpackage simple_html_dom
 */

/**
 * All of the Defines for the classes below.
 *
 * @author S.C. Chen <me578022@gmail.com>
 */
define('HDOM_TYPE_ELEMENT', 1);
define('HDOM_TYPE_COMMENT', 2);
define('HDOM_TYPE_TEXT', 3);
define('HDOM_TYPE_ENDTAG', 4);
define('HDOM_TYPE_ROOT', 5);
define('HDOM_TYPE_UNKNOWN', 6);
define('HDOM_QUOTE_DOUBLE', 0);
define('HDOM_QUOTE_SINGLE', 1);
define('HDOM_QUOTE_NO', 3);
define('HDOM_INFO_BEGIN', 0);
define('HDOM_INFO_END', 1);
define('HDOM_INFO_QUOTE', 2);
define('HDOM_INFO_SPACE', 3);
define('HDOM_INFO_TEXT', 4);
define('HDOM_INFO_INNER', 5);
define('HDOM_INFO_OUTER', 6);
define('HDOM_INFO_ENDSPACE', 7);
define('DEFAULT_TARGET_CHARSET', 'UTF-8');
define('DEFAULT_BR_TEXT', "\r\n");
define('DEFAULT_SPAN_TEXT', " ");
define('MAX_FILE_SIZE', 600000);
// helper functions
// -----------------------------------------------------------------------------
// get html dom from file
// $maxlen is defined in the code as PHP_STREAM_COPY_ALL which is defined as -1.
function file_get_html($url, $use_include_path = false, $context = null, $offset = -1, $maxLen = -1, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
	// We DO force the tags to be terminated.
	$dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
	// For sourceforge users: uncomment the next line and comment the retreive_url_contents line 2 lines down if it is not already done.
	$contents = file_get_contents($url, $use_include_path, $context, $offset);
	// Paperg - use our own mechanism for getting the contents as we want to control the timeout.
	// $contents = retrieve_url_contents($url);
	if (empty($contents) || strlen($contents) > MAX_FILE_SIZE) {
		return false;
	}
	// The second parameter can force the selectors to all be lowercase.
	$dom->load($contents, $lowercase, $stripRN);
	return $dom;
}

// get html dom from string
function str_get_html($str, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
	$dom = new simple_html_dom(null, $lowercase, $forceTagsClosed, $target_charset, $stripRN, $defaultBRText, $defaultSpanText);
	if (empty($str) || strlen($str) > MAX_FILE_SIZE) {
		$dom->clear();
		return false;
	}
	$dom->load($str, $lowercase, $stripRN);
	return $dom;
}

// dump html dom tree
function dump_html_tree($node, $show_attr = true, $deep = 0) {
	$node->dump($node);
}

/**
 * simple html dom node
 * PaperG - added ability for "find" routine to lowercase the value of the selector.
 * PaperG - added $tag_start to track the start position of the tag in the total byte index
 *
 * @package PlaceLocalInclude
 */
class simple_html_dom_node {
	public $nodetype = HDOM_TYPE_TEXT;
	public $tag = 'text';
	public $attr = array();
	public $children = array();
	public $nodes = array();
	public $parent = null;
	// The "info" array - see HDOM_INFO_... for what each element contains.
	public $_ = array();
	public $tag_start = 0;
	private $dom = null;

	function __construct($dom) {
		$this->dom = $dom;
		$dom->nodes[] = $this;
	}

	function __destruct() {
		$this->clear();
	}

	function __toString() {
		return $this->outertext();
	}
	
	// clean up memory due to php5 circular references memory leak...
	function clear() {
		$this->dom = null;
		$this->nodes = null;
		$this->parent = null;
		$this->children = null;
	}
	
	// dump node's tree
	function dump($show_attr = true, $deep = 0) {
		$lead = str_repeat('    ', $deep);
		
		echo $lead . $this->tag;
		if ($show_attr && count($this->attr) > 0) {
			echo '(';
			foreach ($this->attr as $k => $v)
				echo "[$k]=>\"" . $this->$k . '", ';
			echo ')';
		}
		echo "\n";
		
		if ($this->nodes) {
			foreach ($this->nodes as $c) {
				$c->dump($show_attr, $deep + 1);
			}
		}
	}
	
	// Debugging function to dump a single dom node with a bunch of information about it.
	function dump_node($echo = true) {
		$string = $this->tag;
		if (count($this->attr) > 0) {
			$string .= '(';
			foreach ($this->attr as $k => $v) {
				$string .= "[$k]=>\"" . $this->$k . '", ';
			}
			$string .= ')';
		}
		if (count($this->_) > 0) {
			$string .= ' $_ (';
			foreach ($this->_ as $k => $v) {
				if (is_array($v)) {
					$string .= "[$k]=>(";
					foreach ($v as $k2 => $v2) {
						$string .= "[$k2]=>\"" . $v2 . '", ';
					}
					$string .= ")";
				} else {
					$string .= "[$k]=>\"" . $v . '", ';
				}
			}
			$string .= ")";
		}
		
		if (isset($this->text)) {
			$string .= " text: (" . $this->text . ")";
		}
		
		$string .= " HDOM_INNER_INFO: '";
		if (isset($node->_[HDOM_INFO_INNER])) {
			$string .= $node->_[HDOM_INFO_INNER] . "'";
		} else {
			$string .= ' NULL ';
		}
		
		$string .= " children: " . count($this->children);
		$string .= " nodes: " . count($this->nodes);
		$string .= " tag_start: " . $this->tag_start;
		$string .= "\n";
		
		if ($echo) {
			echo $string;
			return;
		} else {
			return $string;
		}
	}
	
	// returns the parent of node
	// If a node is passed in, it will reset the parent of the current node to that one.
	function parent($parent = null) {
		// I am SURE that this doesn't work properly.
		// It fails to unset the current node from it's current parents nodes or children list first.
		if ($parent !== null) {
			$this->parent = $parent;
			$this->parent->nodes[] = $this;
			$this->parent->children[] = $this;
		}
		
		return $this->parent;
	}
	
	// verify that node has children
	function has_child() {
		return !empty($this->children);
	}
	
	// returns children of node
	function children($idx = -1) {
		if ($idx === -1) {
			return $this->children;
		}
		if (isset($this->children[$idx]))
			return $this->children[$idx];
		return null;
	}
	
	// returns the first child of node
	function first_child() {
		if (count($this->children) > 0) {
			return $this->children[0];
		}
		return null;
	}
	
	// returns the last child of node
	function last_child() {
		if (($count = count($this->children)) > 0) {
			return $this->children[$count - 1];
		}
		return null;
	}
	
	// returns the next sibling of node
	function next_sibling() {
		if ($this->parent === null) {
			return null;
		}
		
		$idx = 0;
		$count = count($this->parent->children);
		while ($idx < $count && $this !== $this->parent->children[$idx]) {
			++$idx;
		}
		if (++$idx >= $count) {
			return null;
		}
		return $this->parent->children[$idx];
	}
	
	// returns the previous sibling of node
	function prev_sibling() {
		if ($this->parent === null)
			return null;
		$idx = 0;
		$count = count($this->parent->children);
		while ($idx < $count && $this !== $this->parent->children[$idx])
			++$idx;
		if (--$idx < 0)
			return null;
		return $this->parent->children[$idx];
	}
	
	// function to locate a specific ancestor tag in the path to the root.
	function find_ancestor_tag($tag) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		// Start by including ourselves in the comparison.
		$returnDom = $this;
		
		while (!is_null($returnDom)) {
			if (is_object($debugObject)) {
				$debugObject->debugLog(2, "Current tag is: " . $returnDom->tag);
			}
			
			if ($returnDom->tag == $tag) {
				break;
			}
			$returnDom = $returnDom->parent;
		}
		return $returnDom;
	}
	
	// get dom node's inner html
	function innertext() {
		if (isset($this->_[HDOM_INFO_INNER]))
			return $this->_[HDOM_INFO_INNER];
		if (isset($this->_[HDOM_INFO_TEXT]))
			return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
		
		$ret = '';
		foreach ($this->nodes as $n)
			$ret .= $n->outertext();
		return $ret;
	}
	
	// get dom node's outer text (with tag)
	function outertext() {
		global $debugObject;
		if (is_object($debugObject)) {
			$text = '';
			if ($this->tag == 'text') {
				if (!empty($this->text)) {
					$text = " with text: " . $this->text;
				}
			}
			$debugObject->debugLog(1, 'Innertext of tag: ' . $this->tag . $text);
		}
		
		if ($this->tag === 'root')
			return $this->innertext();
			
			// trigger callback
		if ($this->dom && $this->dom->callback !== null) {
			call_user_func_array($this->dom->callback, array(
					$this
			));
		}
		
		if (isset($this->_[HDOM_INFO_OUTER]))
			return $this->_[HDOM_INFO_OUTER];
		if (isset($this->_[HDOM_INFO_TEXT]))
			return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
			
			// render begin tag
		if ($this->dom && $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]) {
			$ret = $this->dom->nodes[$this->_[HDOM_INFO_BEGIN]]->makeup();
		} else {
			$ret = "";
		}
		
		// render inner text
		if (isset($this->_[HDOM_INFO_INNER])) {
			// If it's a br tag... don't return the HDOM_INNER_INFO that we may or may not have added.
			if ($this->tag != "br") {
				$ret .= $this->_[HDOM_INFO_INNER];
			}
		} else {
			if ($this->nodes) {
				foreach ($this->nodes as $n) {
					$ret .= $this->convert_text($n->outertext());
				}
			}
		}
		
		// render end tag
		if (isset($this->_[HDOM_INFO_END]) && $this->_[HDOM_INFO_END] != 0)
			$ret .= '</' . $this->tag . '>';
		return $ret;
	}
	
	// get dom node's plain text
	function text() {
		if (isset($this->_[HDOM_INFO_INNER]))
			return $this->_[HDOM_INFO_INNER];
		switch ($this->nodetype) {
			case HDOM_TYPE_TEXT:
				return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
			case HDOM_TYPE_COMMENT:
				return '';
			case HDOM_TYPE_UNKNOWN:
				return '';
		}
		if (strcasecmp($this->tag, 'script') === 0)
			return '';
		if (strcasecmp($this->tag, 'style') === 0)
			return '';
		
		$ret = '';
		// In rare cases, (always node type 1 or HDOM_TYPE_ELEMENT - observed for some span tags, and some p tags) $this->nodes is set to NULL.
		// NOTE: This indicates that there is a problem where it's set to NULL without a clear happening.
		// WHY is this happening?
		if (!is_null($this->nodes)) {
			foreach ($this->nodes as $n) {
				$ret .= $this->convert_text($n->text());
			}
			
			// If this node is a span... add a space at the end of it so multiple spans don't run into each other. This is plaintext after all.
			if ($this->tag == "span") {
				$ret .= $this->dom->default_span_text;
			}
		}
		return $ret;
	}

	function xmltext() {
		$ret = $this->innertext();
		$ret = str_ireplace('<![CDATA[', '', $ret);
		$ret = str_replace(']]>', '', $ret);
		return $ret;
	}
	
	// build node's text with tag
	function makeup() {
		// text, comment, unknown
		if (isset($this->_[HDOM_INFO_TEXT]))
			return $this->dom->restore_noise($this->_[HDOM_INFO_TEXT]);
		
		$ret = '<' . $this->tag;
		$i = -1;
		
		foreach ($this->attr as $key => $val) {
			++$i;
			
			// skip removed attribute
			if ($val === null || $val === false)
				continue;
			
			$ret .= $this->_[HDOM_INFO_SPACE][$i][0];
			// no value attr: nowrap, checked selected...
			if ($val === true)
				$ret .= $key;
			else {
				switch ($this->_[HDOM_INFO_QUOTE][$i]) {
					case HDOM_QUOTE_DOUBLE:
						$quote = '"';
						break;
					case HDOM_QUOTE_SINGLE:
						$quote = '\'';
						break;
					default :
						$quote = '';
				}
				$ret .= $key . $this->_[HDOM_INFO_SPACE][$i][1] . '=' . $this->_[HDOM_INFO_SPACE][$i][2] . $quote . $val . $quote;
			}
		}
		$ret = $this->dom->restore_noise($ret);
		return $ret . $this->_[HDOM_INFO_ENDSPACE] . '>';
	}
	
	// find elements by css selector
	// PaperG - added ability for find to lowercase the value of the selector.
	function find($selector, $idx = null, $lowercase = false) {
		$selectors = $this->parse_selector($selector);
		if (($count = count($selectors)) === 0)
			return array();
		$found_keys = array();
		
		// find each selector
		for($c = 0; $c < $count; ++$c) {
			// The change on the below line was documented on the sourceforge code tracker id 2788009
			// used to be: if (($levle=count($selectors[0]))===0) return array();
			if (($levle = count($selectors[$c])) === 0)
				return array();
			if (!isset($this->_[HDOM_INFO_BEGIN]))
				return array();
			
			$head = array(
					$this->_[HDOM_INFO_BEGIN] => 1
			);
			
			// handle descendant selectors, no recursive!
			for($l = 0; $l < $levle; ++$l) {
				$ret = array();
				foreach ($head as $k => $v) {
					$n = ($k === -1) ? $this->dom->root : $this->dom->nodes[$k];
					// PaperG - Pass this optional parameter on to the seek function.
					$n->seek($selectors[$c][$l], $ret, $lowercase);
				}
				$head = $ret;
			}
			
			foreach ($head as $k => $v) {
				if (!isset($found_keys[$k]))
					$found_keys[$k] = 1;
			}
		}
		
		// sort keys
		ksort($found_keys);
		
		$found = array();
		foreach ($found_keys as $k => $v)
			$found[] = $this->dom->nodes[$k];
			
			// return nth-element or array
		if (is_null($idx))
			return $found;
		else if ($idx < 0)
			$idx = count($found) + $idx;
		return (isset($found[$idx])) ? $found[$idx] : null;
	}
	
	// seek for given conditions
	// PaperG - added parameter to allow for case insensitive testing of the value of a selector.
	protected function seek($selector, &$ret, $lowercase = false) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		list($tag, $key, $val, $exp, $no_key) = $selector;
		
		// xpath index
		if ($tag && $key && is_numeric($key)) {
			$count = 0;
			foreach ($this->children as $c) {
				if ($tag === '*' || $tag === $c->tag) {
					if (++$count == $key) {
						$ret[$c->_[HDOM_INFO_BEGIN]] = 1;
						return;
					}
				}
			}
			return;
		}
		
		$end = (!empty($this->_[HDOM_INFO_END])) ? $this->_[HDOM_INFO_END] : 0;
		if ($end == 0) {
			$parent = $this->parent;
			while (!isset($parent->_[HDOM_INFO_END]) && $parent !== null) {
				$end -= 1;
				$parent = $parent->parent;
			}
			$end += $parent->_[HDOM_INFO_END];
		}
		
		for($i = $this->_[HDOM_INFO_BEGIN] + 1; $i < $end; ++$i) {
			$node = $this->dom->nodes[$i];
			
			$pass = true;
			
			if ($tag === '*' && !$key) {
				if (in_array($node, $this->children, true))
					$ret[$i] = 1;
				continue;
			}
			
			// compare tag
			if ($tag && $tag != $node->tag && $tag !== '*') {
				$pass = false;
			}
			// compare key
			if ($pass && $key) {
				if ($no_key) {
					if (isset($node->attr[$key]))
						$pass = false;
				} else {
					if (($key != "plaintext") && !isset($node->attr[$key]))
						$pass = false;
				}
			}
			// compare value
			if ($pass && $key && $val && $val !== '*') {
				// If they have told us that this is a "plaintext" search then we want the plaintext of the node - right?
				if ($key == "plaintext") {
					// $node->plaintext actually returns $node->text();
					$nodeKeyValue = $node->text();
				} else {
					// this is a normal search, we want the value of that attribute of the tag.
					$nodeKeyValue = $node->attr[$key];
				}
				if (is_object($debugObject)) {
					$debugObject->debugLog(2, "testing node: " . $node->tag . " for attribute: " . $key . $exp . $val . " where nodes value is: " . $nodeKeyValue);
				}
				
				// PaperG - If lowercase is set, do a case insensitive test of the value of the selector.
				if ($lowercase) {
					$check = $this->match($exp, strtolower($val), strtolower($nodeKeyValue));
				} else {
					$check = $this->match($exp, $val, $nodeKeyValue);
				}
				if (is_object($debugObject)) {
					$debugObject->debugLog(2, "after match: " . ($check ? "true" : "false"));
				}
				
				// handle multiple class
				if (!$check && strcasecmp($key, 'class') === 0) {
					foreach (explode(' ', $node->attr[$key]) as $k) {
						// Without this, there were cases where leading, trailing, or double spaces lead to our comparing blanks - bad form.
						if (!empty($k)) {
							if ($lowercase) {
								$check = $this->match($exp, strtolower($val), strtolower($k));
							} else {
								$check = $this->match($exp, $val, $k);
							}
							if ($check)
								break;
						}
					}
				}
				if (!$check)
					$pass = false;
			}
			if ($pass)
				$ret[$i] = 1;
			unset($node);
		}
		// It's passed by reference so this is actually what this function returns.
		if (is_object($debugObject)) {
			$debugObject->debugLog(1, "EXIT - ret: ", $ret);
		}
	}

	protected function match($exp, $pattern, $value) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		switch ($exp) {
			case '=':
				return ($value === $pattern);
			case '!=':
				return ($value !== $pattern);
			case '^=':
				return preg_match("/^" . preg_quote($pattern, '/') . "/", $value);
			case '$=':
				return preg_match("/" . preg_quote($pattern, '/') . "$/", $value);
			case '*=':
				if ($pattern[0] == '/') {
					return preg_match($pattern, $value);
				}
				return preg_match("/" . $pattern . "/i", $value);
		}
		return false;
	}

	protected function parse_selector($selector_string) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		// pattern of CSS selectors, modified from mootools
		// Paperg: Add the colon to the attrbute, so that it properly finds <tag attr:ibute="something" > like google does.
		// Note: if you try to look at this attribute, yo MUST use getAttribute since $dom->x:y will fail the php syntax check.
		// Notice the \[ starting the attbute? and the @? following? This implies that an attribute can begin with an @ sign that is not captured.
		// This implies that an html attribute specifier may start with an @ sign that is NOT captured by the expression.
		// farther study is required to determine of this should be documented or removed.
		// $pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
		$pattern = "/([\w-:\*]*)(?:\#([\w-]+)|\.([\w-]+))?(?:\[@?(!?[\w-:]+)(?:([!*^$]?=)[\"']?(.*?)[\"']?)?\])?([\/, ]+)/is";
		preg_match_all($pattern, trim($selector_string) . ' ', $matches, PREG_SET_ORDER);
		if (is_object($debugObject)) {
			$debugObject->debugLog(2, "Matches Array: ", $matches);
		}
		
		$selectors = array();
		$result = array();
		// print_r($matches);
		
		foreach ($matches as $m) {
			$m[0] = trim($m[0]);
			if ($m[0] === '' || $m[0] === '/' || $m[0] === '//')
				continue;
				// for browser generated xpath
			if ($m[1] === 'tbody')
				continue;
			
			list($tag, $key, $val, $exp, $no_key) = array(
					$m[1],
					null,
					null,
					'=',
					false
			);
			if (!empty($m[2])) {
				$key = 'id';
				$val = $m[2];
			}
			if (!empty($m[3])) {
				$key = 'class';
				$val = $m[3];
			}
			if (!empty($m[4])) {
				$key = $m[4];
			}
			if (!empty($m[5])) {
				$exp = $m[5];
			}
			if (!empty($m[6])) {
				$val = $m[6];
			}
			
			// convert to lowercase
			if ($this->dom->lowercase) {
				$tag = strtolower($tag);
				$key = strtolower($key);
			}
			// elements that do NOT have the specified attribute
			if (isset($key[0]) && $key[0] === '!') {
				$key = substr($key, 1);
				$no_key = true;
			}
			
			$result[] = array(
					$tag,
					$key,
					$val,
					$exp,
					$no_key
			);
			if (trim($m[7]) === ',') {
				$selectors[] = $result;
				$result = array();
			}
		}
		if (count($result) > 0)
			$selectors[] = $result;
		return $selectors;
	}

	function __get($name) {
		if (isset($this->attr[$name])) {
			return $this->convert_text($this->attr[$name]);
		}
		switch ($name) {
			case 'outertext':
				return $this->outertext();
			case 'innertext':
				return $this->innertext();
			case 'plaintext':
				return $this->text();
			case 'xmltext':
				return $this->xmltext();
			default :
				return array_key_exists($name, $this->attr);
		}
	}

	function __set($name, $value) {
		switch ($name) {
			case 'outertext':
				return $this->_[HDOM_INFO_OUTER] = $value;
			case 'innertext':
				if (isset($this->_[HDOM_INFO_TEXT]))
					return $this->_[HDOM_INFO_TEXT] = $value;
				return $this->_[HDOM_INFO_INNER] = $value;
		}
		if (!isset($this->attr[$name])) {
			$this->_[HDOM_INFO_SPACE][] = array(
					' ',
					'',
					''
			);
			$this->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
		}
		$this->attr[$name] = $value;
	}

	function __isset($name) {
		switch ($name) {
			case 'outertext':
				return true;
			case 'innertext':
				return true;
			case 'plaintext':
				return true;
		}
		// no value attr: nowrap, checked selected...
		return (array_key_exists($name, $this->attr)) ? true : isset($this->attr[$name]);
	}

	function __unset($name) {
		if (isset($this->attr[$name]))
			unset($this->attr[$name]);
	}
	
	// PaperG - Function to convert the text from one character set to another if the two sets are not the same.
	function convert_text($text) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		$converted_text = $text;
		
		$sourceCharset = "";
		$targetCharset = "";
		
		if ($this->dom) {
			$sourceCharset = strtoupper($this->dom->_charset);
			$targetCharset = strtoupper($this->dom->_target_charset);
		}
		if (is_object($debugObject)) {
			$debugObject->debugLog(3, "source charset: " . $sourceCharset . " target charaset: " . $targetCharset);
		}
		
		if (!empty($sourceCharset) && !empty($targetCharset) && (strcasecmp($sourceCharset, $targetCharset) != 0)) {
			// Check if the reported encoding could have been incorrect and the text is actually already UTF-8
			if ((strcasecmp($targetCharset, 'UTF-8') == 0) && ($this->is_utf8($text))) {
				$converted_text = $text;
			} else {
				$converted_text = iconv($sourceCharset, $targetCharset, $text);
			}
		}
		
		// Lets make sure that we don't have that silly BOM issue with any of the utf-8 text we output.
		if ($targetCharset == 'UTF-8') {
			if (substr($converted_text, 0, 3) == "\xef\xbb\xbf") {
				$converted_text = substr($converted_text, 3);
			}
			if (substr($converted_text, -3) == "\xef\xbb\xbf") {
				$converted_text = substr($converted_text, 0, -3);
			}
		}
		
		return $converted_text;
	}

	/**
	 * Returns true if $string is valid UTF-8 and false otherwise.
	 *
	 * @param mixed $str
	 *        	String to be tested
	 * @return boolean
	 */
	static function is_utf8($str) {
		$c = 0;
		$b = 0;
		$bits = 0;
		$len = strlen($str);
		for($i = 0; $i < $len; $i++) {
			$c = ord($str[$i]);
			if ($c > 128) {
				if (($c >= 254))
					return false;
				elseif ($c >= 252)
					$bits = 6;
				elseif ($c >= 248)
					$bits = 5;
				elseif ($c >= 240)
					$bits = 4;
				elseif ($c >= 224)
					$bits = 3;
				elseif ($c >= 192)
					$bits = 2;
				else
					return false;
				if (($i + $bits) > $len)
					return false;
				while ($bits > 1) {
					$i++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191)
						return false;
					$bits--;
				}
			}
		}
		return true;
	}

	/*
	 * function is_utf8($string)
	 * {
	 * //this is buggy
	 * return (utf8_encode(utf8_decode($string)) == $string);
	 * }
	 */
	
	/**
	 * Function to try a few tricks to determine the displayed size of an img on the page.
	 * NOTE: This will ONLY work on an IMG tag. Returns FALSE on all other tag types.
	 *
	 * @author John Schlick
	 * @version April 19 2012
	 * @return array an array containing the 'height' and 'width' of the image on the page or -1 if we can't figure it out.
	 */
	function get_display_size() {
		global $debugObject;
		
		$width = -1;
		$height = -1;
		
		if ($this->tag !== 'img') {
			return false;
		}
		
		// See if there is aheight or width attribute in the tag itself.
		if (isset($this->attr['width'])) {
			$width = $this->attr['width'];
		}
		
		if (isset($this->attr['height'])) {
			$height = $this->attr['height'];
		}
		
		// Now look for an inline style.
		if (isset($this->attr['style'])) {
			// Thanks to user gnarf from stackoverflow for this regular expression.
			$attributes = array();
			preg_match_all("/([\w-]+)\s*:\s*([^;]+)\s*;?/", $this->attr['style'], $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$attributes[$match[1]] = $match[2];
			}
			
			// If there is a width in the style attributes:
			if (isset($attributes['width']) && $width == -1) {
				// check that the last two characters are px (pixels)
				if (strtolower(substr($attributes['width'], -2)) == 'px') {
					$proposed_width = substr($attributes['width'], 0, -2);
					// Now make sure that it's an integer and not something stupid.
					if (filter_var($proposed_width, FILTER_VALIDATE_INT)) {
						$width = $proposed_width;
					}
				}
			}
			
			// If there is a width in the style attributes:
			if (isset($attributes['height']) && $height == -1) {
				// check that the last two characters are px (pixels)
				if (strtolower(substr($attributes['height'], -2)) == 'px') {
					$proposed_height = substr($attributes['height'], 0, -2);
					// Now make sure that it's an integer and not something stupid.
					if (filter_var($proposed_height, FILTER_VALIDATE_INT)) {
						$height = $proposed_height;
					}
				}
			}
		}
		
		// Future enhancement:
		// Look in the tag to see if there is a class or id specified that has a height or width attribute to it.
		
		// Far future enhancement
		// Look at all the parent tags of this image to see if they specify a class or id that has an img selector that specifies a height or width
		// Note that in this case, the class or id will have the img subselector for it to apply to the image.
		
		// ridiculously far future development
		// If the class or id is specified in a SEPARATE css file thats not on the page, go get it and do what we were just doing for the ones on the page.
		
		$result = array(
				'height' => $height,
				'width' => $width
		);
		return $result;
	}
	
	// camel naming conventions
	function getAllAttributes() {
		return $this->attr;
	}

	function getAttribute($name) {
		return $this->__get($name);
	}

	function setAttribute($name, $value) {
		$this->__set($name, $value);
	}

	function hasAttribute($name) {
		return $this->__isset($name);
	}

	function removeAttribute($name) {
		$this->__set($name, null);
	}

	function getElementById($id) {
		return $this->find("#$id", 0);
	}

	function getElementsById($id, $idx = null) {
		return $this->find("#$id", $idx);
	}

	function getElementByTagName($name) {
		return $this->find($name, 0);
	}

	function getElementsByTagName($name, $idx = null) {
		return $this->find($name, $idx);
	}

	function parentNode() {
		return $this->parent();
	}

	function childNodes($idx = -1) {
		return $this->children($idx);
	}

	function firstChild() {
		return $this->first_child();
	}

	function lastChild() {
		return $this->last_child();
	}

	function nextSibling() {
		return $this->next_sibling();
	}

	function previousSibling() {
		return $this->prev_sibling();
	}

	function hasChildNodes() {
		return $this->has_child();
	}

	function nodeName() {
		return $this->tag;
	}

	function appendChild($node) {
		$node->parent($this);
		return $node;
	}

}

/**
 * simple html dom parser
 * Paperg - in the find routine: allow us to specify that we want case insensitive testing of the value of the selector.
 * Paperg - change $size from protected to public so we can easily access it
 * Paperg - added ForceTagsClosed in the constructor which tells us whether we trust the html or not. Default is to NOT trust it.
 *
 * @package PlaceLocalInclude
 */
class simple_html_dom {
	public $root = null;
	public $nodes = array();
	public $callback = null;
	public $lowercase = false;
	// Used to keep track of how large the text was when we started.
	public $original_size;
	public $size;
	protected $pos;
	protected $doc;
	protected $char;
	protected $cursor;
	protected $parent;
	protected $noise = array();
	protected $token_blank = " \t\r\n";
	protected $token_equal = ' =/>';
	protected $token_slash = " />\r\n\t";
	protected $token_attr = ' >';
	// Note that this is referenced by a child node, and so it needs to be public for that node to see this information.
	public $_charset = '';
	public $_target_charset = '';
	protected $default_br_text = "";
	public $default_span_text = "";
	
	// use isset instead of in_array, performance boost about 30%...
	protected $self_closing_tags = array(
			'img' => 1,
			'br' => 1,
			'input' => 1,
			'meta' => 1,
			'link' => 1,
			'hr' => 1,
			'base' => 1,
			'embed' => 1,
			'spacer' => 1
	);
	protected $block_tags = array(
			'root' => 1,
			'body' => 1,
			'form' => 1,
			'div' => 1,
			'span' => 1,
			'table' => 1
	);
	// Known sourceforge issue #2977341
	// B tags that are not closed cause us to return everything to the end of the document.
	protected $optional_closing_tags = array(
			'tr' => array(
					'tr' => 1,
					'td' => 1,
					'th' => 1
			),
			'th' => array(
					'th' => 1
			),
			'td' => array(
					'td' => 1
			),
			'li' => array(
					'li' => 1
			),
			'dt' => array(
					'dt' => 1,
					'dd' => 1
			),
			'dd' => array(
					'dd' => 1,
					'dt' => 1
			),
			'dl' => array(
					'dd' => 1,
					'dt' => 1
			),
			'p' => array(
					'p' => 1
			),
			'nobr' => array(
					'nobr' => 1
			),
			'b' => array(
					'b' => 1
			),
			'option' => array(
					'option' => 1
			)
	);

	function __construct($str = null, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
		if ($str) {
			if (preg_match("/^http:\/\//i", $str) || is_file($str)) {
				$this->load_file($str);
			} else {
				$this->load($str, $lowercase, $stripRN, $defaultBRText, $defaultSpanText);
			}
		}
		// Forcing tags to be closed implies that we don't trust the html, but it can lead to parsing errors if we SHOULD trust the html.
		if (!$forceTagsClosed) {
			$this->optional_closing_array = array();
		}
		$this->_target_charset = $target_charset;
	}

	function __destruct() {
		$this->clear();
	}
	
	// load html from string
	function load($str, $lowercase = true, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
		global $debugObject;
		
		// prepare
		$this->prepare($str, $lowercase, $stripRN, $defaultBRText, $defaultSpanText);
		// strip out comments
		$this->remove_noise("'<!--(.*?)-->'is");
		// strip out cdata
		$this->remove_noise("'<!\[CDATA\[(.*?)\]\]>'is", true);
		// Per sourceforge http://sourceforge.net/tracker/?func=detail&aid=2949097&group_id=218559&atid=1044037
		// Script tags removal now preceeds style tag removal.
		// strip out <script> tags
		$this->remove_noise("'<\s*script[^>]*[^/]>(.*?)<\s*/\s*script\s*>'is");
		$this->remove_noise("'<\s*script\s*>(.*?)<\s*/\s*script\s*>'is");
		// strip out <style> tags
		$this->remove_noise("'<\s*style[^>]*[^/]>(.*?)<\s*/\s*style\s*>'is");
		$this->remove_noise("'<\s*style\s*>(.*?)<\s*/\s*style\s*>'is");
		// strip out preformatted tags
		$this->remove_noise("'<\s*(?:code)[^>]*>(.*?)<\s*/\s*(?:code)\s*>'is");
		// strip out server side scripts
		$this->remove_noise("'(<\?)(.*?)(\?>)'s", true);
		// strip smarty scripts
		$this->remove_noise("'(\{\w)(.*?)(\})'s", true);
		
		// parsing
		while ($this->parse())
			;
			// end
		$this->root->_[HDOM_INFO_END] = $this->cursor;
		$this->parse_charset();
		
		// make load function chainable
		return $this;
	}
	
	// load html from file
	function load_file() {
		$args = func_get_args();
		$this->load(call_user_func_array('file_get_contents', $args), true);
		// Throw an error if we can't properly load the dom.
		if (($error = error_get_last()) !== null) {
			$this->clear();
			return false;
		}
	}
	
	// set callback function
	function set_callback($function_name) {
		$this->callback = $function_name;
	}
	
	// remove callback function
	function remove_callback() {
		$this->callback = null;
	}
	
	// save dom as string
	function save($filepath = '') {
		$ret = $this->root->innertext();
		if ($filepath !== '')
			file_put_contents($filepath, $ret, LOCK_EX);
		return $ret;
	}
	
	// find dom node by css selector
	// Paperg - allow us to specify that we want case insensitive testing of the value of the selector.
	function find($selector, $idx = null, $lowercase = false) {
		return $this->root->find($selector, $idx, $lowercase);
	}
	
	// clean up memory due to php5 circular references memory leak...
	function clear() {
		foreach ($this->nodes as $n) {
			$n->clear();
			$n = null;
		}
		// This add next line is documented in the sourceforge repository. 2977248 as a fix for ongoing memory leaks that occur even with the use of clear.
		if (isset($this->children))
			foreach ($this->children as $n) {
				$n->clear();
				$n = null;
			}
		if (isset($this->parent)) {
			$this->parent->clear();
			unset($this->parent);
		}
		if (isset($this->root)) {
			$this->root->clear();
			unset($this->root);
		}
		unset($this->doc);
		unset($this->noise);
	}

	function dump($show_attr = true) {
		$this->root->dump($show_attr);
	}
	
	// prepare HTML data and init everything
	protected function prepare($str, $lowercase = true, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
		$this->clear();
		
		// set the length of content before we do anything to it.
		$this->size = strlen($str);
		// Save the original size of the html that we got in. It might be useful to someone.
		$this->original_size = $this->size;
		
		// before we save the string as the doc... strip out the \r \n's if we are told to.
		if ($stripRN) {
			$str = str_replace("\r", " ", $str);
			$str = str_replace("\n", " ", $str);
			
			// set the length of content since we have changed it.
			$this->size = strlen($str);
		}
		
		$this->doc = $str;
		$this->pos = 0;
		$this->cursor = 1;
		$this->noise = array();
		$this->nodes = array();
		$this->lowercase = $lowercase;
		$this->default_br_text = $defaultBRText;
		$this->default_span_text = $defaultSpanText;
		$this->root = new simple_html_dom_node($this);
		$this->root->tag = 'root';
		$this->root->_[HDOM_INFO_BEGIN] = -1;
		$this->root->nodetype = HDOM_TYPE_ROOT;
		$this->parent = $this->root;
		if ($this->size > 0)
			$this->char = $this->doc[0];
	}
	
	// parse html content
	protected function parse() {
		if (($s = $this->copy_until_char('<')) === '') {
			return $this->read_tag();
		}
		
		// text
		$node = new simple_html_dom_node($this);
		++$this->cursor;
		$node->_[HDOM_INFO_TEXT] = $s;
		$this->link_nodes($node, false);
		return true;
	}
	
	// PAPERG - dkchou - added this to try to identify the character set of the page we have just parsed so we know better how to spit it out later.
	// NOTE: IF you provide a routine called get_last_retrieve_url_contents_content_type which returns the CURLINFO_CONTENT_TYPE from the last curl_exec
	// (or the content_type header from the last transfer), we will parse THAT, and if a charset is specified, we will use it over any other mechanism.
	protected function parse_charset() {
		global $debugObject;
		
		$charset = null;
		
		if (function_exists('get_last_retrieve_url_contents_content_type')) {
			$contentTypeHeader = get_last_retrieve_url_contents_content_type();
			$success = preg_match('/charset=(.+)/', $contentTypeHeader, $matches);
			if ($success) {
				$charset = $matches[1];
				if (is_object($debugObject)) {
					$debugObject->debugLog(2, 'header content-type found charset of: ' . $charset);
				}
			}
		}
		
		if (empty($charset)) {
			$el = $this->root->find('meta[http-equiv=Content-Type]', 0);
			if (!empty($el)) {
				$fullvalue = $el->content;
				if (is_object($debugObject)) {
					$debugObject->debugLog(2, 'meta content-type tag found' . $fullvalue);
				}
				
				if (!empty($fullvalue)) {
					$success = preg_match('/charset=(.+)/', $fullvalue, $matches);
					if ($success) {
						$charset = $matches[1];
					} else {
						// If there is a meta tag, and they don't specify the character set, research says that it's typically ISO-8859-1
						if (is_object($debugObject)) {
							$debugObject->debugLog(2, 'meta content-type tag couldn\'t be parsed. using iso-8859 default.');
						}
						$charset = 'ISO-8859-1';
					}
				}
			}
		}
		
		// If we couldn't find a charset above, then lets try to detect one based on the text we got...
		if (empty($charset)) {
			// Have php try to detect the encoding from the text given to us.
			$charset = mb_detect_encoding($this->root->plaintext . "ascii", $encoding_list = array(
					"UTF-8",
					"CP1252"
			));
			if (is_object($debugObject)) {
				$debugObject->debugLog(2, 'mb_detect found: ' . $charset);
			}
			
			// and if this doesn't work... then we need to just wrongheadedly assume it's UTF-8 so that we can move on - cause this will usually give us most of what we need...
			if ($charset === false) {
				if (is_object($debugObject)) {
					$debugObject->debugLog(2, 'since mb_detect failed - using default of utf-8');
				}
				$charset = 'UTF-8';
			}
		}
		
		// Since CP1252 is a superset, if we get one of it's subsets, we want it instead.
		if ((strtolower($charset) == strtolower('ISO-8859-1')) || (strtolower($charset) == strtolower('Latin1')) || (strtolower($charset) == strtolower('Latin-1'))) {
			if (is_object($debugObject)) {
				$debugObject->debugLog(2, 'replacing ' . $charset . ' with CP1252 as its a superset');
			}
			$charset = 'CP1252';
		}
		
		if (is_object($debugObject)) {
			$debugObject->debugLog(1, 'EXIT - ' . $charset);
		}
		
		return $this->_charset = $charset;
	}
	
	// read tag info
	protected function read_tag() {
		if ($this->char !== '<') {
			$this->root->_[HDOM_INFO_END] = $this->cursor;
			return false;
		}
		$begin_tag_pos = $this->pos;
		$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
		                                                                            
		// end tag
		if ($this->char === '/') {
			$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
			                                                                            // This represents the change in the simple_html_dom trunk from revision 180 to 181.
			                                                                            // $this->skip($this->token_blank_t);
			$this->skip($this->token_blank);
			$tag = $this->copy_until_char('>');
			
			// skip attributes in end tag
			if (($pos = strpos($tag, ' ')) !== false)
				$tag = substr($tag, 0, $pos);
			
			$parent_lower = strtolower($this->parent->tag);
			$tag_lower = strtolower($tag);
			
			if ($parent_lower !== $tag_lower) {
				if (isset($this->optional_closing_tags[$parent_lower]) && isset($this->block_tags[$tag_lower])) {
					$this->parent->_[HDOM_INFO_END] = 0;
					$org_parent = $this->parent;
					
					while (($this->parent->parent) && strtolower($this->parent->tag) !== $tag_lower)
						$this->parent = $this->parent->parent;
					
					if (strtolower($this->parent->tag) !== $tag_lower) {
						$this->parent = $org_parent; // restore origonal parent
						if ($this->parent->parent)
							$this->parent = $this->parent->parent;
						$this->parent->_[HDOM_INFO_END] = $this->cursor;
						return $this->as_text_node($tag);
					}
				} else if (($this->parent->parent) && isset($this->block_tags[$tag_lower])) {
					$this->parent->_[HDOM_INFO_END] = 0;
					$org_parent = $this->parent;
					
					while (($this->parent->parent) && strtolower($this->parent->tag) !== $tag_lower)
						$this->parent = $this->parent->parent;
					
					if (strtolower($this->parent->tag) !== $tag_lower) {
						$this->parent = $org_parent; // restore origonal parent
						$this->parent->_[HDOM_INFO_END] = $this->cursor;
						return $this->as_text_node($tag);
					}
				} else if (($this->parent->parent) && strtolower($this->parent->parent->tag) === $tag_lower) {
					$this->parent->_[HDOM_INFO_END] = 0;
					$this->parent = $this->parent->parent;
				} else
					return $this->as_text_node($tag);
			}
			
			$this->parent->_[HDOM_INFO_END] = $this->cursor;
			if ($this->parent->parent)
				$this->parent = $this->parent->parent;
			
			$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
			return true;
		}
		
		$node = new simple_html_dom_node($this);
		$node->_[HDOM_INFO_BEGIN] = $this->cursor;
		++$this->cursor;
		$tag = $this->copy_until($this->token_slash);
		$node->tag_start = $begin_tag_pos;
		
		// doctype, cdata & comments...
		if (isset($tag[0]) && $tag[0] === '!') {
			$node->_[HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until_char('>');
			
			if (isset($tag[2]) && $tag[1] === '-' && $tag[2] === '-') {
				$node->nodetype = HDOM_TYPE_COMMENT;
				$node->tag = 'comment';
			} else {
				$node->nodetype = HDOM_TYPE_UNKNOWN;
				$node->tag = 'unknown';
			}
			if ($this->char === '>')
				$node->_[HDOM_INFO_TEXT] .= '>';
			$this->link_nodes($node, true);
			$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
			return true;
		}
		
		// text
		if ($pos = strpos($tag, '<') !== false) {
			$tag = '<' . substr($tag, 0, -1);
			$node->_[HDOM_INFO_TEXT] = $tag;
			$this->link_nodes($node, false);
			$this->char = $this->doc[--$this->pos]; // prev
			return true;
		}
		
		if (!preg_match("/^[\w-:]+$/", $tag)) {
			$node->_[HDOM_INFO_TEXT] = '<' . $tag . $this->copy_until('<>');
			if ($this->char === '<') {
				$this->link_nodes($node, false);
				return true;
			}
			
			if ($this->char === '>')
				$node->_[HDOM_INFO_TEXT] .= '>';
			$this->link_nodes($node, false);
			$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
			return true;
		}
		
		// begin tag
		$node->nodetype = HDOM_TYPE_ELEMENT;
		$tag_lower = strtolower($tag);
		$node->tag = ($this->lowercase) ? $tag_lower : $tag;
		
		// handle optional closing tags
		if (isset($this->optional_closing_tags[$tag_lower])) {
			while (isset($this->optional_closing_tags[$tag_lower][strtolower($this->parent->tag)])) {
				$this->parent->_[HDOM_INFO_END] = 0;
				$this->parent = $this->parent->parent;
			}
			$node->parent = $this->parent;
		}
		
		$guard = 0; // prevent infinity loop
		$space = array(
				$this->copy_skip($this->token_blank),
				'',
				''
		);
		
		// attributes
		do {
			if ($this->char !== null && $space[0] === '') {
				break;
			}
			$name = $this->copy_until($this->token_equal);
			if ($guard === $this->pos) {
				$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
				continue;
			}
			$guard = $this->pos;
			
			// handle endless '<'
			if ($this->pos >= $this->size - 1 && $this->char !== '>') {
				$node->nodetype = HDOM_TYPE_TEXT;
				$node->_[HDOM_INFO_END] = 0;
				$node->_[HDOM_INFO_TEXT] = '<' . $tag . $space[0] . $name;
				$node->tag = 'text';
				$this->link_nodes($node, false);
				return true;
			}
			
			// handle mismatch '<'
			if ($this->doc[$this->pos - 1] == '<') {
				$node->nodetype = HDOM_TYPE_TEXT;
				$node->tag = 'text';
				$node->attr = array();
				$node->_[HDOM_INFO_END] = 0;
				$node->_[HDOM_INFO_TEXT] = substr($this->doc, $begin_tag_pos, $this->pos - $begin_tag_pos - 1);
				$this->pos -= 2;
				$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
				$this->link_nodes($node, false);
				return true;
			}
			
			if ($name !== '/' && $name !== '') {
				$space[1] = $this->copy_skip($this->token_blank);
				$name = $this->restore_noise($name);
				if ($this->lowercase)
					$name = strtolower($name);
				if ($this->char === '=') {
					$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
					$this->parse_attr($node, $name, $space);
				} else {
					// no value attr: nowrap, checked selected...
					$node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
					$node->attr[$name] = true;
					if ($this->char != '>')
						$this->char = $this->doc[--$this->pos]; // prev
				}
				$node->_[HDOM_INFO_SPACE][] = $space;
				$space = array(
						$this->copy_skip($this->token_blank),
						'',
						''
				);
			} else
				break;
		} while ($this->char !== '>' && $this->char !== '/');
		
		$this->link_nodes($node, true);
		$node->_[HDOM_INFO_ENDSPACE] = $space[0];
		
		// check self closing
		if ($this->copy_until_char_escape('>') === '/') {
			$node->_[HDOM_INFO_ENDSPACE] .= '/';
			$node->_[HDOM_INFO_END] = 0;
		} else {
			// reset parent
			if (!isset($this->self_closing_tags[strtolower($node->tag)]))
				$this->parent = $node;
		}
		$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
		                                                                            
		// If it's a BR tag, we need to set it's text to the default text.
		                                                                            // This way when we see it in plaintext, we can generate formatting that the user wants.
		                                                                            // since a br tag never has sub nodes, this works well.
		if ($node->tag == "br") {
			$node->_[HDOM_INFO_INNER] = $this->default_br_text;
		}
		
		return true;
	}
	
	// parse attributes
	protected function parse_attr($node, $name, &$space) {
		// Per sourceforge: http://sourceforge.net/tracker/?func=detail&aid=3061408&group_id=218559&atid=1044037
		// If the attribute is already defined inside a tag, only pay atetntion to the first one as opposed to the last one.
		if (isset($node->attr[$name])) {
			return;
		}
		
		$space[2] = $this->copy_skip($this->token_blank);
		switch ($this->char) {
			case '"':
				$node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_DOUBLE;
				$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
				$node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('"'));
				$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
				break;
			case '\'':
				$node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_SINGLE;
				$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
				$node->attr[$name] = $this->restore_noise($this->copy_until_char_escape('\''));
				$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
				break;
			default :
				$node->_[HDOM_INFO_QUOTE][] = HDOM_QUOTE_NO;
				$node->attr[$name] = $this->restore_noise($this->copy_until($this->token_attr));
		}
		// PaperG: Attributes should not have \r or \n in them, that counts as html whitespace.
		$node->attr[$name] = str_replace("\r", "", $node->attr[$name]);
		$node->attr[$name] = str_replace("\n", "", $node->attr[$name]);
		// PaperG: If this is a "class" selector, lets get rid of the preceeding and trailing space since some people leave it in the multi class case.
		if ($name == "class") {
			$node->attr[$name] = trim($node->attr[$name]);
		}
	}
	
	// link node's parent
	protected function link_nodes(&$node, $is_child) {
		$node->parent = $this->parent;
		$this->parent->nodes[] = $node;
		if ($is_child) {
			$this->parent->children[] = $node;
		}
	}
	
	// as a text node
	protected function as_text_node($tag) {
		$node = new simple_html_dom_node($this);
		++$this->cursor;
		$node->_[HDOM_INFO_TEXT] = '</' . $tag . '>';
		$this->link_nodes($node, false);
		$this->char = (++$this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
		return true;
	}

	protected function skip($chars) {
		$this->pos += strspn($this->doc, $chars, $this->pos);
		$this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
	}

	protected function copy_skip($chars) {
		$pos = $this->pos;
		$len = strspn($this->doc, $chars, $pos);
		$this->pos += $len;
		$this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
		if ($len === 0)
			return '';
		return substr($this->doc, $pos, $len);
	}

	protected function copy_until($chars) {
		$pos = $this->pos;
		$len = strcspn($this->doc, $chars, $pos);
		$this->pos += $len;
		$this->char = ($this->pos < $this->size) ? $this->doc[$this->pos] : null; // next
		return substr($this->doc, $pos, $len);
	}

	protected function copy_until_char($char) {
		if ($this->char === null)
			return '';
		
		if (($pos = strpos($this->doc, $char, $this->pos)) === false) {
			$ret = substr($this->doc, $this->pos, $this->size - $this->pos);
			$this->char = null;
			$this->pos = $this->size;
			return $ret;
		}
		
		if ($pos === $this->pos)
			return '';
		$pos_old = $this->pos;
		$this->char = $this->doc[$pos];
		$this->pos = $pos;
		return substr($this->doc, $pos_old, $pos - $pos_old);
	}

	protected function copy_until_char_escape($char) {
		if ($this->char === null)
			return '';
		
		$start = $this->pos;
		while (1) {
			if (($pos = strpos($this->doc, $char, $start)) === false) {
				$ret = substr($this->doc, $this->pos, $this->size - $this->pos);
				$this->char = null;
				$this->pos = $this->size;
				return $ret;
			}
			
			if ($pos === $this->pos)
				return '';
			
			if ($this->doc[$pos - 1] === '\\') {
				$start = $pos + 1;
				continue;
			}
			
			$pos_old = $this->pos;
			$this->char = $this->doc[$pos];
			$this->pos = $pos;
			return substr($this->doc, $pos_old, $pos - $pos_old);
		}
	}
	
	// remove noise from html content
	// save the noise in the $this->noise array.
	protected function remove_noise($pattern, $remove_tag = false) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		$count = preg_match_all($pattern, $this->doc, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		
		for($i = $count - 1; $i > -1; --$i) {
			$key = '___noise___' . sprintf('% 5d', count($this->noise) + 1000);
			if (is_object($debugObject)) {
				$debugObject->debugLog(2, 'key is: ' . $key);
			}
			$idx = ($remove_tag) ? 0 : 1;
			$this->noise[$key] = $matches[$i][$idx][0];
			$this->doc = substr_replace($this->doc, $key, $matches[$i][$idx][1], strlen($matches[$i][$idx][0]));
		}
		
		// reset the length of content
		$this->size = strlen($this->doc);
		if ($this->size > 0) {
			$this->char = $this->doc[0];
		}
	}
	
	// restore noise to html content
	function restore_noise($text) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		while (($pos = strpos($text, '___noise___')) !== false) {
			// Sometimes there is a broken piece of markup, and we don't GET the pos+11 etc... token which indicates a problem outside of us...
			if (strlen($text) > $pos + 15) {
				$key = '___noise___' . $text[$pos + 11] . $text[$pos + 12] . $text[$pos + 13] . $text[$pos + 14] . $text[$pos + 15];
				if (is_object($debugObject)) {
					$debugObject->debugLog(2, 'located key of: ' . $key);
				}
				
				if (isset($this->noise[$key])) {
					$text = substr($text, 0, $pos) . $this->noise[$key] . substr($text, $pos + 16);
				} else {
					// do this to prevent an infinite loop.
					$text = substr($text, 0, $pos) . 'UNDEFINED NOISE FOR KEY: ' . $key . substr($text, $pos + 16);
				}
			} else {
				// There is no valid key being given back to us... We must get rid of the ___noise___ or we will have a problem.
				$text = substr($text, 0, $pos) . 'NO NUMERIC NOISE KEY' . substr($text, $pos + 11);
			}
		}
		return $text;
	}
	
	// Sometimes we NEED one of the noise elements.
	function search_noise($text) {
		global $debugObject;
		if (is_object($debugObject)) {
			$debugObject->debugLogEntry(1);
		}
		
		foreach ($this->noise as $noiseElement) {
			if (strpos($noiseElement, $text) !== false) {
				return $noiseElement;
			}
		}
	}

	function __toString() {
		return $this->root->innertext();
	}

	function __get($name) {
		switch ($name) {
			case 'outertext':
				return $this->root->innertext();
			case 'innertext':
				return $this->root->innertext();
			case 'plaintext':
				return $this->root->text();
			case 'charset':
				return $this->_charset;
			case 'target_charset':
				return $this->_target_charset;
		}
	}
	
	// camel naming conventions
	function childNodes($idx = -1) {
		return $this->root->childNodes($idx);
	}

	function firstChild() {
		return $this->root->first_child();
	}

	function lastChild() {
		return $this->root->last_child();
	}

	function createElement($name, $value = null) {
		return @str_get_html("<$name>$value</$name>")->first_child();
	}

	function createTextNode($value) {
		return @end(str_get_html($value)->nodes);
	}

	function getElementById($id) {
		return $this->find("#$id", 0);
	}

	function getElementsById($id, $idx = null) {
		return $this->find("#$id", $idx);
	}

	function getElementByTagName($name) {
		return $this->find($name, 0);
	}

	function getElementsByTagName($name, $idx = -1) {
		return $this->find($name, $idx);
	}

	function loadFile() {
		$args = func_get_args();
		$this->load_file($args);
	}

}

// ####################################################### ./include/libcompactmvc/sitemap.php ####################################################### \\


/**
 * sitemap.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Sitemap extends CMVCController {

	protected function main_run() {
		DLOG();
		parent::main_run();
		$urls = ApplicationMapper::get_instance()->get_sitemap();
		$this->binary_response($urls, MIME_TYPE_HTML);
	}

}
// ####################################################### ./include/libcompactmvc/smtp.php ####################################################### \\


/**
 * Simple PHP implementation of an SMTP client.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class SMTP {
	private $server;
	private $user;
	private $pass;
	private $mail;
	private $sender;
	private $receiver;

	/**
	 * Construct an object of this class by giving the IP address or hostname of the SMTP server to the constructor.
	 *
	 * @param String $server
	 *        	IP address in xxx.xxx.xxx.xxx notation or host name
	 */
	public function __construct($server) {
		$this->server = $server;
	}

	/**
	 * Set the login credentials for SMTP access.
	 *
	 * @param String $user
	 *        	user name
	 * @param String $pass
	 *        	password
	 */
	public function set_login($user, $pass) {
		$this->user = $user;
		$this->pass = $pass;
	}

	/**
	 * Set sender and receiver email address and the mail body.
	 * The mail body must have Unix line breaks.
	 *
	 * @param String $sender
	 *        	sender email address
	 * @param String $receiver
	 *        	receiver email address
	 * @param String $mail
	 *        	mail body
	 */
	public function set_mail($sender, $receiver, $mail) {
		$this->mail = $mail;
		$this->sender = $sender;
		$this->receiver = $receiver;
	}

	/**
	 * Send the mail.
	 *
	 * @throws Exception contains the SMTP error
	 */
	public function send() {
		$mailarr = explode("\n", $this->mail);
		$sock = new Socket($this->server, 25);
		$ret = $sock->read();
		$sock->write("HELO " . gethostname() . "\n");
		$ret = $sock->read();
		if (($this->user != "") && ($this->pass != "")) {
			$sock->write("AUTH LOGIN\n");
			$ret = $sock->read();
			$sock->write(base64_encode($this->user) . "\n");
			$ret = $sock->read();
			$sock->write(base64_encode($this->pass) . "\n");
			$ret = $sock->read();
			if (strpos(strtolower($ret), "535") !== false) {
				$sock->write("QUIT\n");
				$sock->read();
				throw new Exception("SMTP authentication failed: " . $ret, 535);
			}
		}
		$sock->write("MAIL FROM:" . $this->sender . "\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "250") === false) {
			$sock->write("QUIT\n");
			$sock->read();
			throw new Exception("Could not set sender: " . $ret, substr($ret, 0, 3));
		}
		$sock->write("RCPT TO:" . $this->receiver . "\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "250") === false) {
			$sock->write("QUIT\n");
			$sock->read();
			throw new Exception("Could not set receipient: " . $ret, substr($ret, 0, 3));
		}
		$sock->write("DATA\n");
		$ret = $sock->read();
		foreach ($mailarr as $m) {
			$sock->write($m . "\n");
		}
		$sock->write(".\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "250") === false) {
			$sock->write("QUIT\n");
			$sock->read();
			throw new Exception("Error during mail transmission: " . $ret, substr($ret, 0, 3));
		}
		$sock->write("QUIT\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "221") === false) {
			throw new Exception("Notice: Could not close connection cleanly: " . $ret, substr($ret, 0, 3));
		}
	}

}
// ####################################################### ./include/libcompactmvc/socket.php ####################################################### \\


/**
 * Socket wrapper class for easy socket handling.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Socket {
	private $fh;

	/**
	 * Connect to an host on the given port.
	 *
	 * @param String $host
	 *        	hostname or IP address
	 * @param Integer $port
	 *        	port number
	 * @throws Exception error returned from fsockopen()
	 */
	public function __construct($host, $port = 25, $timeout = 2000) {
		$errno = 0;
		$errstr = "";
		$this->fh = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$this->fh) {
			throw new Exception($errstr, $errno);
		}
	}

	/**
	 * Read from the socket.
	 *
	 * @throws Exception
	 */
	public function read($maxsize = 8192) {
		$buf = "";
		$oldbuf = "";
		if ($this->fh) {
			$buf .= fread($this->fh, $maxsize);
			// TODO: read sizes >8192 bytes
			// while (!feof($this->fh)) {
			// stream_set_blocking($this->fh, true);
			// $buf .= fread($this->fh, 8192);
			// stream_set_blocking($this->fh, true);
			// if ($oldbuf == $buf) {
			// break;
			// }
			// $oldbuf = $buf;
			// }
		} else {
			throw new Exception("Unable to read from socket. No connection established.");
		}
		return $buf;
	}

	/**
	 * Write to the socket.
	 *
	 * @param String $buf
	 *        	Data to be written
	 * @throws Exception
	 */
	public function write($buf) {
		$n = 0;
		$bytes_written = 0;
		$bytes_to_write = strlen($buf);
		if ($this->fh) {
			while ($bytes_written < $bytes_to_write) {
				if ($bytes_written == 0) {
					$rv = fwrite($this->fh, $buf);
				} else {
					$rv = fwrite($this->fh, substr($buf, $bytes_written));
				}
				if ($rv === false || $rv == 0) {
					throw new Exception("Unable to write to socket any more. " . $bytes_written . " of " . $bytes_to_write . " bytes written.");
				}
				$bytes_written += $rv;
			}
		} else {
			throw new Exception("Unable to write to socket. No connection established.");
		}
		return $n;
	}

}
// ####################################################### ./include/libcompactmvc/tabledescription.php ####################################################### \\


/**
 * Table characteristics.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class TableDescription extends DbAccess {
	// static storage to buffer multiple foreign key lookups
	// and reduce traffic between application server and redis cluster
	private static $colinfoarr;
	private static $fkinfoarr;

	public function __construct() {
		parent::__construct();
		self::$colinfoarr = json_decode("{}", true);
		self::$fkinfoarr = json_decode("{}", true);
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function columninfo($tablename) {
		if (array_key_exists($tablename, self::$colinfoarr)) {
			return self::$colinfoarr[$tablename];
		}
		$desc = RedisAdapter::get_instance()->get(REDIS_KEY_TBLDESC_PFX . $tablename);
		if ($desc !== false) {
			$desc = unserialize($desc);
			self::$colinfoarr[$tablename] = $desc;
			return $desc;
		}
		$q = "DESCRIBE " . $tablename;
		$desc = $this->run_query($q, true, true, null, null, false);
		self::$colinfoarr[$tablename] = $desc;
		RedisAdapter::get_instance()->set(REDIS_KEY_TBLDESC_PFX . $tablename, serialize($desc));
		return $desc;
	}

	/**
	 *
	 * @param unknown_type $tablename        	
	 */
	public function fkinfo($tablename) {
		if (array_key_exists($tablename, self::$fkinfoarr)) {
			return self::$fkinfoarr[$tablename];
		}
		$desc = RedisAdapter::get_instance()->get(REDIS_KEY_FKINFO_PFX . $tablename);
		if ($desc !== false) {
			$desc = unserialize($desc);
			self::$fkinfoarr[$tablename] = $desc;
			return $desc;
		}
		$q = "
		SELECT		CONCAT(table_name, '.', column_name) as 'fk',
					CONCAT(referenced_table_name, '.', referenced_column_name) as 'ref'
		FROM		information_schema.key_column_usage
		WHERE		referenced_table_name IS NOT NULL
		AND			table_schema = '" . MYSQL_SCHEMA . "'
		AND			table_name = '" . $tablename . "'";
		$desc = $this->run_query($q, true, true, null, null, false);
		self::$fkinfoarr[$tablename] = $desc;
		RedisAdapter::get_instance()->set(REDIS_KEY_FKINFO_PFX . $tablename, serialize($desc));
		return $desc;
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function primary_keys($tablename) {
		$desc = $this->columninfo($tablename);
		$ret = array();
		foreach ($desc as $key => $val) {
			if ($val->Key == "PRI") {
				$ret[] = $val->Field;
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function columns($tablename) {
		$desc = $this->columninfo($tablename);
		$ret = array();
		foreach ($desc as $key => $val) {
			$ret[] = $val->Field;
		}
		return $ret;
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function table_exists($tablename) {
		try {
			$this->columninfo($tablename);
			return true;
		} catch (DBException $e) {
			return false;
		}
	}
	
	public function get_all_tables() {
		$q = "
		SELECT		table_name 
		FROM		information_schema.tables
		WHERE		table_schema = '" . MYSQL_SCHEMA . "'";
		$desc = $this->run_query($q, true, false, "table_name", "information_schema.tables", false);
		return $desc;
	}

}
// ####################################################### ./include/libcompactmvc/upload.php ####################################################### \\


/**
 * Upload helper
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Upload {
	private $ul_path;
	private $files_arr;
	private $names_arr;

	public function __construct($path) {
		DLOG();
		$this->ul_path = $path;
		$this->files_arr = $_FILES;
	}

	/**
	 * Saves one ore more files in the upload directory.
	 *
	 * @return array file names
	 * @throws Exception
	 */
	public function save() {
		DLOG();
		$ret = array();
		$i = 0;
		foreach ($this->files_arr as $key => $value) {
			$this->names_arr[$i++] = $key;
			if ($value['name'] != "") {
				if (!move_uploaded_file($value['tmp_name'], $this->ul_path . "/" . $value['name'])) {
					throw new Exception("Cannot write to upload directory: '" . $this->ul_path . "/" . $value['name'] . "'");
				}
				chmod($this->ul_path . "/" . $value['name'], 0666);
				$ret[] = $this->ul_path . "/" . $value['name'];
			}
		}
		return $ret;
	}

	/**
	 * Saves one or more files in corresponding subdirectories of the upload directory.
	 *
	 * @return array file names
	 * @throws Exception
	 */
	public function save_sub() {
		DLOG();
		$ret = array();
		foreach ($this->files_arr as $key => $value) {
			if ($value['name'] != "") {
				if (!move_uploaded_file($value['tmp_name'], $this->ul_path . "/" . $key . "/" . $value['name'])) {
					throw new Exception("Cannot write to upload directory: '" . $this->ul_path . "/" . $key . "/" . $value['name'] . "'");
				}
				chmod($this->ul_path . "/" . $key . "/" . $value['name'], 0666);
				$ret[$key][] = $this->ul_path . "/" . $key . "/" . $value['name'];
			}
		}
		return $ret;
	}

	public function get_param_name($index) {
		DLOG();
		if (!isset($index)) {
			return $this->names_arr;
		}
		return $this->names_arr[$index];
	}

}
// ####################################################### ./include/libcompactmvc/utf8.php ####################################################### \\


/**
 * UTF-8 helper class.
 * These methods check the encoding of the input and convert it if required.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class UTF8 {

	/**
	 * Only static functions.
	 * No instantiation required.
	 */
	private function __construct() {
		;
	}

	/**
	 * Convert to UTF-8
	 *
	 * @param string_or_array $subject
	 *        	input
	 */
	public static function encode($subject) {
		if (is_array($subject)) {
			foreach ($subject as $key => $val) {
				$subject[$key] = self::_encode($val);
			}
			return $subject;
		} else {
			return self::_encode($subject);
		}
	}

	/**
	 * Convert to ISO-8859-1
	 *
	 * @param string_or_array $subject
	 *        	input
	 */
	public static function decode($subject) {
		if (is_array($subject)) {
			foreach ($subject as $key => $val) {
				$subject[$key] = self::_decode($val);
			}
			return $subject;
		} else {
			return self::_decode($subject);
		}
	}

	/**
	 * Check if the input is properly UTF-8 encoded.
	 *
	 * @param String $str
	 *        	string to be checked
	 */
	private static function check_utf8($str) {
		$len = strlen($str);
		for($i = 0; $i < $len; $i++) {
			$c = ord($str[$i]);
			if ($c > 128) {
				if (($c > 247))
					return false;
				elseif ($c > 239)
					$bytes = 4;
				elseif ($c > 223)
					$bytes = 3;
				elseif ($c > 191)
					$bytes = 2;
				else
					return false;
				if (($i + $bytes) > $len)
					return false;
				while ($bytes > 1) {
					$i++;
					$b = ord($str[$i]);
					if ($b < 128 || $b > 191)
						return false;
					$bytes--;
				}
			}
		}
		return true;
	}

	/**
	 * Required to convert other formats than ISO-8859-1.
	 *
	 * @param String $string
	 *        	intput string
	 * @param String $string_encoding
	 *        	desired encoding
	 */
	private static function checkEncoding($string, $string_encoding) {
		$fs = $string_encoding == 'UTF-8' ? 'UTF-32' : $string_encoding;
		$ts = $string_encoding == 'UTF-32' ? 'UTF-8' : $string_encoding;
		return $string === mb_convert_encoding(mb_convert_encoding($string, $fs, $ts), $ts, $fs);
	}

	private static function _encode($string) {
		if (!is_string($string)) {
			return false;
		}
		if (self::check_utf8($string)) {
			return $string;
		} else {
			if (strlen(self::checkEncoding($string, "UTF-8")) != strlen($string)) {
				return utf8_encode($string);
			} else {
				return self::checkEncoding($string, "UTF-8");
			}
		}
	}

	private static function _decode($string) {
		if (!is_string($string)) {
			return false;
		}
		return utf8_decode(self::encode($string));
	}

}
// ####################################################### ./include/libcompactmvc/uuid.php ####################################################### \\


/**
 * UUID Generator (v3, v4, v5).
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class UUID {

	public static function v3($namespace, $name) {
		DLOG();
		if (!self::is_valid($namespace))
			return false;
			// Get hexadecimal components of namespace
		$nhex = str_replace(array(
				'-',
				'{',
				'}'
		), '', $namespace);
		// Binary Value
		$nstr = '';
		// Convert Namespace UUID to bits
		for($i = 0; $i < strlen($nhex); $i += 2) {
			$nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
		}
		// Calculate hash value
		$hash = md5($nstr . $name);
		return sprintf('%08s-%04s-%04x-%04x-%12s', 
				// 32 bits for "time_low"
				substr($hash, 0, 8), 
				// 16 bits for "time_mid"
				substr($hash, 8, 4), 
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 3
				(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x3000, 
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000, 
				// 48 bits for "node"
				substr($hash, 20, 12));
	}

	public static function v4() {
		DLOG();
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
				// 32 bits for "time_low"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), 
				// 16 bits for "time_mid"
				mt_rand(0, 0xffff), 
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand(0, 0x0fff) | 0x4000, 
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand(0, 0x3fff) | 0x8000, 
				// 48 bits for "node"
				mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
	}

	public static function v5($namespace, $name) {
		DLOG();
		if (!self::is_valid($namespace))
			return false;
			// Get hexadecimal components of namespace
		$nhex = str_replace(array(
				'-',
				'{',
				'}'
		), '', $namespace);
		// Binary Value
		$nstr = '';
		// Convert Namespace UUID to bits
		for($i = 0; $i < strlen($nhex); $i += 2) {
			$nstr .= chr(hexdec($nhex[$i] . $nhex[$i + 1]));
		}
		// Calculate hash value
		$hash = sha1($nstr . $name);
		return sprintf('%08s-%04s-%04x-%04x-%12s', 
				// 32 bits for "time_low"
				substr($hash, 0, 8), 
				// 16 bits for "time_mid"
				substr($hash, 8, 4), 
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 5
				(hexdec(substr($hash, 12, 4)) & 0x0fff) | 0x5000, 
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				(hexdec(substr($hash, 16, 4)) & 0x3fff) | 0x8000, 
				// 48 bits for "node"
				substr($hash, 20, 12));
	}

	public static function is_valid($uuid) {
		DLOG();
		return preg_match('/^\{?[0-9a-f]{8}\-?[0-9a-f]{4}\-?[0-9a-f]{4}\-?' . '[0-9a-f]{4}\-?[0-9a-f]{12}\}?$/i', $uuid) === 1;
	}

}
// ####################################################### ./include/libcompactmvc/validator.php ####################################################### \\


/**
 * Input validator
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Validator {

	private function __construct() {
		;
	}

	public static function email($string) {
		return filter_var($string, FILTER_VALIDATE_EMAIL) ? true : false;
	}

	public static function boolean($string) {
		return filter_var($string, FILTER_VALIDATE_BOOLEAN) ? true : false;
	}

	public static function float($string) {
		return filter_var($string, FILTER_VALIDATE_FLOAT) ? true : false;
	}

	public static function int($string) {
		return filter_var($string, FILTER_VALIDATE_INT) ? true : false;
	}

	public static function ipaddr($string) {
		return filter_var($string, FILTER_VALIDATE_IP) ? true : false;
	}

	public static function url($string) {
		return filter_var($string, FILTER_VALIDATE_URL) ? true : false;
	}
	
	public static function uuid($string) {
		return UUID::is_valid($string);
	}

}
// ####################################################### ./include/libcompactmvc/view.php ####################################################### \\


/**
 * Template handling
 *
 * This class is used for template handling. It loads the templates, fills them
 * with values and generates the output into a buffer that can be retrieved.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class View {
	private $__part;
	private $__vals;
	private $__tpls;
	private $__comp;
	private static $__mapper;
	
	/**
	 *
	 * @var LinkBuilder
	 */
	private $__lb;

	/**
	 */
	public function __construct() {
		$this->__part = array();
		$this->__vals = array();
		$this->__tpls = array();
		$this->__comp = array();
		$this->__lb = LinkBuilder::get_instance();
	}

	/**
	 *
	 * @param String $part_name
	 */
	public function activate($part_name) {
		$this->__part[$part_name] = true;
		return $this;
	}

	/**
	 *
	 * @param String $part_name
	 * @return View
	 */
	public function deactivate($part_name) {
		$this->__part[$part_name] = false;
		return $this;
	}

	/**
	 *
	 * @param String $part_name
	 */
	private function is_active($part_name) {
		if (array_key_exists($part_name, $this->__part)) {
			return $this->__part[$part_name];
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param String $key
	 * @param unknown $value
	 */
	public function set_value($key, $value) {
		$this->__vals[$key] = $value;
		return $this;
	}

	/**
	 *
	 * @param String $key
	 */
	private function get_value($key) {
		if (array_key_exists($key, $this->__vals)) {
			return $this->__vals[$key];
		} else {
			return "";
		}
	}
	
	/**
	 * 
	 * @return InputProvider
	 */
	private function get_input() {
		return InputProvider::get_instance();
	}
	
	/**
	 * get variable content from request
	 * 
	 * @param unknown $var_name 
	 * @return mixed
	 */
	private function get_input_var($var_name) {
		try {
			return InputProvider::get_instance()->get_var($var_name);
		} catch (InvalidMemberException $e) {
			return "";
		}
	}

    /**
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	public function set_component($key, CMVCController $component) {
		if (array_key_exists($key, $this->__comp))
			throw new Exception("Component id is already in use: " . $key);
		$this->__comp[$key] = $component;
		return $this;
	}

	/**
	 *
	 * @param String $key
	 */
	public function get_component($key) {
		return (array_key_exists($key, $this->__comp)) ? $this->__comp[$key] : null;
	}

	/**
	 *
	 * @param ActionMapper $mapper
	 */
	public function set_action_mapper(ActionMapper $mapper) {
		if (!isset(self::$__mapper) && $mapper != null)
			self::$__mapper = $mapper;
	}

	/**
	 *
	 * @param String $key
	 */
	private function component($key) {
		return (array_key_exists($key, $this->__comp)) ? $this->__comp[$key]->get_ob() : "";
	}

	/**
	 *
	 * @param String $val
	 */
	private function encode($val) {
		return htmlentities(UTF8::encode($val), ENT_QUOTES | ENT_HTML401, 'UTF-8');
	}

	/**
	 *
	 * @param ActionMapperInterface $mapper
	 * @param String $path0
	 * @param String $path1
	 * @param String $urltail
	 */
	private function link(ActionMapperInterface $mapper, $path0 = null, $path1 = null, $urltail = null, $lang = null) {
		return $this->__lb->get_link($mapper, $path0, $path1, $urltail, $lang);
	}

	/**
	 *
	 * @param String $path0
	 * @param String $path1
	 * @param String $urltail
	 */
	private function lnk($path0 = null, $path1 = null, $urltail = null, $lang = null) {
		return $this->__lb->get_link(self::$__mapper, $path0, $path1, $urltail, $lang);
	}

	/**
	 *
	 * @param int $index
	 * @param String $name
	 */
	public function set_template($index, $name) {
		$this->__tpls[$index] = $name;
		return $this;
	}

	/**
	 *
	 * @param String $name
	 */
	public function add_template($name) {
		$this->__tpls[] = $name;
		return $this;
	}

	/**
	 */
	public function get_templates() {
		return $this->__tpls;
	}

	public function clear() {
		$this->__part = array();
		$this->__vals = array();
		$this->__tpls = array();
		$this->__comp = array();
		return $this;
	}
	
	public function clear_templates() {
		$this->__tpls = array();
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function get_hash() {
		$serialized = serialize(array(
				$this->__part,
				$this->__vals,
				$this->__tpls,
				$this->__comp,
				InputProvider::get_instance()->to_array()
		));
		$hash = md5($serialized);
		return $hash;
	}

	/**
	 *
	 * @param bool $caching
	 */
	public function render($caching = CACHING_ENABLED) {
		if (DEBUG == 0) {
			@ob_end_clean();
		}
		foreach ($this->__comp as $c) {
			$c->run();
		}
		ob_start();
		if ($caching) {
			$start = microtime(true);
			$key = REDIS_KEY_RCACHE_PFX . $this->get_hash();
			$out = RedisAdapter::get_instance()->get($key);
			if ($out !== false) {
				RedisAdapter::get_instance()->expire($key, REDIS_KEY_RCACHE_TTL);
				$time_taken = (microtime(true) - $start) * 1000 . " ms";
				$msg = 'Returning content from render cache... (' . $key . ' | ' . $time_taken . ')';
				DLOG($msg);
				return $out;
			}
			$time_taken = (microtime(true) - $start) * 1000 . " ms";
			$msg = 'Starting Rendering... (' . $key . ' | ' . $time_taken . ')';
			DLOG($msg);
			$out = "";
		}
		if (count($this->__tpls) > 0) {
			foreach ($this->__tpls as $t) {
				if ((!defined("DEBUG")) || (DEBUG == 0)) {
					@$this->include_template($t);
				} else {
					$this->include_template($t);
				}
			}
		}
		$out = ob_get_contents();
		ob_end_clean();
		if ((!defined("DEBUG")) || (DEBUG == 0)) {
			@ob_start();
		}
		if ($caching) {
			RedisAdapter::get_instance()->set($key, $out);
			RedisAdapter::get_instance()->expire($key, REDIS_KEY_RCACHE_TTL);
			$time_taken = (microtime(true) - $start) * 1000 . " ms";
			$msg = 'Returning rendered content... (' . $key . ' | ' . $time_taken . ')';
			DLOG($msg);
		}
		return $out;
	}

	/**
	 *
	 * @param String $tpl_name
	 * @throws Exception
	 */
	private function include_template($tpl_name) {
		$file1 = "./include/resources/templates/" . $tpl_name;
		$file2 = "./templates/" . $tpl_name;
		if (file_exists($file1)) {
			include ($file1);
		} else if (file_exists($file2)) {
			include ($file2);
		} else {
			throw new FileNotFoundException("Could not find template file: " . $tpl_name, 404);
		}
	}

}
// ####################################################### ./include/libcompactmvc/webview.php ####################################################### \\


/**
 * webview.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class WebView {
	private static $instance;
	private $is_open;

	private function __construct() {
		DLOG();
		$this->is_open = false;
	}

	public static function get_instance() {
		DLOG();
		if (!isset(self::$instance)) {
			self::$instance = new WebView();
		}
		return self::$instance;
	}

	public function close() {
		DLOG();
		if (!$this->is_open) return false;
		$this->is_open = false;
		if (is_windows()) exec_bg('bin\WebView.exe close');
		return true;
	}

	public function open($posx, $posy, $width, $height, $url) {
		DLOG();
		if ($this->is_open) return false;
		$this->is_open = true;
		if (is_windows()) exec_bg('bin\WebView.exe ' . $posx . ' ' . $posy . ' ' . $width . ' ' . $height . ' ' . $url);
		if (is_linux()) exec_bg('xdg-open ' . $url);
		return true;
	}

	public function is_open() {
		DLOG();
		return $this->is_open;
	}

}
// ####################################################### ./include/libcompactmvc/wsadapter.php ####################################################### \\


/**
 *
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class WSAdapter {
	private static $instance;
	
	private $sesskey;

	private function __construct() {
		DLOG();
		if (Session::get_instance()->get_property(ST_WS_SRV_IDX) == null) {
			Session::get_instance()->set_property(ST_WS_SRV_IDX, rand(0, WS_SRV_COUNT - 1));
		}
		$this->sesskey = md5(Session::get_instance()->get_id());
	}

	public static function get_instance() {
		DLOG();
		if (!isset(self::$instance)) {
			self::$instance = new WSAdapter();
		}
		return self::$instance;
	}

	public function get_srv_url() {
		DLOG();
		return $GLOBALS['WS_BASE_URL'][Session::get_instance()->get_property(ST_WS_SRV_IDX)] . $this->sesskey;
	}

	public function notify($event, $payload = null) {
		if ($payload != null) {
			$encp = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			if (!$encp) $encp = $payload;
			$msg = $event . " " . $encp;
		} else {
			$msg = $event;
		}
		DLOG("Message: " . $msg);
		$fname = tempnam("./files/temp/", "qtwsdata_");
		file_put_contents($fname, $msg);
		$cmd = "/bin/bash -c '/bin/cat " . $fname . " | bin/qtwsclient -c " . $this->get_srv_url() . "; rm -f " . $fname . "'";
		exec_bg($cmd);
	}
	
	public function get_session_key() {
		DLOG();
		return $this->sesskey;
	}
	
	public function set_session_key($sesskey) {
		DLOG($sesskey);
		$this->sesskey = $sesskey;
	}

}
// ####################################################### ./include/libcompactmvc/xmltojson.php ####################################################### \\


/**
 * XML to JSON
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class XmlToJson {

	public static function ParseURI($url) {
		$fileContents = file_get_contents($url);
		$fileContents = str_replace(array(
				"\n",
				"\r",
				"\t"
		), '', $fileContents);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents, null, LIBXML_NOCDATA);
		$json = json_encode($simpleXml);
		return $json;
	}

	public static function ParseData($data) {
		$fileContents = $data;
		$fileContents = str_replace(array(
				"\n",
				"\r",
				"\t"
		), '', $fileContents);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents, null, LIBXML_NOCDATA);
		$json = json_encode($simpleXml);
		return $json;
	}

}
// ####################################################### ./application/include.php ####################################################### \\



cmvc_include("dummycomponent.php");
// ####################################################### ./application/component/dummycomponent.php ####################################################### \\


/**
 * Dummy component
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DummyComponent extends CMVCComponent {

	public function get_component_id() {
		return "dummy";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		$test = $this->param(1);
		$this->set_base_param($test);
	}

}
// ####################################################### ./application/controller/control.php ####################################################### \\


/**
 * Access controller
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Control extends CMVCController {


}

// ####################################################### ./application/controller/home.php ####################################################### \\


/**
 * Home page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Home extends CMVCController {

	protected function main_run() {
		DLOG();
		parent::main_run();
	}

	protected function exception_handler($e) {
		DLOG();
		throw $e;
	}


}

// ####################################################### ./application/controller/login.php ####################################################### \\


/**
 * Login page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Login extends CMVCController {


}

// ####################################################### ./application/controller/logout.php ####################################################### \\


/**
 * Logout page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Logout extends CMVCController {

	protected function main_run() {
		DLOG();
		parent::main_run();
		Session::get_instance()->clear();
		throw new RedirectException(lnk("login"));
	}

}
// ####################################################### ./application/dba/dba.php ####################################################### \\


/**
 * Database functions
 *
 * @author		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DBA extends DbAccess {

}

// ####################################################### ./application/dba/user.php ####################################################### \\


/**
 * user.php
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package Siemens CMS
 * @copyright Copyright (c) Media Impression Unit 08
 * @license BSD License (see LICENSE file in root directory)
 * @link http://www.miu08.de
 */
class user extends DbObject {

	protected function init() {
		DLOG();
		parent::init();
		$this->table(TBL_USER);
	}

}
// ####################################################### ./application/framework/applicationmapper.php ####################################################### \\


/**
 * applicationmapper.php
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ApplicationMapper extends ActionMapper {

	protected function register_endpoints() {
		DLOG();
		ActionDispatcher::set_action_mapper($this);
		ActionDispatcher::set_control(route_id("control"));
		ActionDispatcher::set_default(route_id("login"));
		$langs = array("app");
		foreach ($langs as $lang) {
			$this->register_ep_2($lang, "home", new LinkProperty("/de/home", true, "Home"));
			
			$this->register_ep_3($lang, "ajaxep", "user", new LinkProperty("/de/ajaxep/user", false, "user"));
		}
	}

	public function get_base_url() {
		return BASE_URL;
	}


}
