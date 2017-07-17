<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Global functions
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */

/*
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
	DLOG("Filename: " . $fname . " Extension: " . $ext);
	return $ext;
}

function file_name($fname) {
	$ext = file_extension($fname);
	$fname = substr($fname, 0, strlen($fname) - (($ext == "") ? strlen($ext) : strlen("." . $ext)));
	DLOG("Filename: " . $fname . " Extension: " . $ext);
	return $fname;
}

function cmvc_include($fname) {
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
	// DLOG($path);
	if (is_dir($path)) {
		$path = rtrim($path, '/') . '/';
		$items = glob($path . '*');
		foreach ($items as $item) {
			foreach ($ignore as $i) {
				if (pathinfo($item, PATHINFO_BASENAME) == $i) {
					DLOG(" " . $item . " is on ignore list.");
					return;
				}
			}
			if (is_dir($item)) {
				cmvc_include_dir($item, $ignore);
			}
			if (strtolower(file_extension($item)) == "php") {
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

function is_tls_con() {
	$ret = null;
	if (php_sapi_name() != "cli") {
		$ret = (array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] != 'off') ? true : false;
	}
	return $ret;
}

function lnk($action = null, $param0 = null, $urltail = "", $lang = null) {
	return LinkBuilder::get_instance()->get_link(ActionDispatcher::get_action_mapper(), $action, $param0, $urltail, $lang);
}

function uppercase($str) {
	$str = strtoupper($str);
	$str = str_replace("ä", "Ä", $str);
	$str = str_replace("ö", "Ö", $str);
	$str = str_replace("ü", "Ü", $str);
	return $str;
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

// This list may be completed with required entries
define('MIME_TYPE_HTML', 'text/html; charset=utf-8');
define('MIME_TYPE_CSV', 'text/csv; charset=utf-8');
define('MIME_TYPE_JSON', 'application/json; charset=utf-8');
define('MIME_TYPE_JPG', 'image/jpg');
define('MIME_TYPE_JPEG', 'image/jpeg');
define('MIME_TYPE_PNG', 'image/png');
define('MIME_TYPE_GIF', 'image/gif');
define('MIME_TYPE_PDF', 'application/pdf');
define('MIME_TYPE_DDS', 'image/vnd-ms.dds');
define('MIME_TYPE_BINARY', 'application/binary');
define('MIME_TYPE_OCTET_STREAM', 'application/octet-stream');

