<?php
defined('LIBCOMPACTMVC_ENTRY') || define('LIBCOMPACTMVC_ENTRY', (($_SERVER['DOCUMENT_ROOT'] == "") || (str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']) == str_replace("\\", "/", getcwd()))) || die('Invalid entry point'));

/**
 * LibCompactMVC application loader
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 11.02.2014
 * @link		http://www.github.com/bhohbaum
 */

require_once('./include/libcompactmvc/functions.php');

@cmvc_include('mysqlhost.php');
@cmvc_include('config.php');
@cmvc_include('singleton.php');
@cmvc_include('log.php');

register_shutdown_function("last_error_handler");
$GLOBALS["FATAL_ERR_MSG"] = "";

function last_error_handler() {
	$error = error_get_last();
	if ($error == null) return;
	try {
		throw new Exception();
	} catch (Exception $e) {
		$trace = $e->getTraceAsString();
	}
	$msg = "Last PHP error: " . print_r($error, true) . (array_key_exists("FATAL_ERR_MSG", $GLOBALS) ? $GLOBALS["FATAL_ERR_MSG"] : "") . "\nStack trace:\n" . $trace;
	ELOG($msg);
}

function exception_error_handler($severity, $message, $file, $line) {
	if (!(error_reporting() & $severity)) {
		// Dieser Fehlercode ist nicht in error_reporting enthalten
		return;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

// first include the configuration

if (!defined("LOG_FILE")) {
	die("LOG_FILE is undefined, please define it in config.php - exiting.\n");
}
@touch(LOG_FILE);
if (!file_exists(LOG_FILE)) {
	die(LOG_FILE." cannot be created, exiting.\n");
}
if (!is_writable(LOG_FILE)) {
	die(LOG_FILE." is not writable by the current process, exiting.\n");
}

if (defined('DEBUG') && (DEBUG == 0)) {
	ob_start();
}

// include files with content that is required elsewhere
cmvc_include('inputsanitizer.php');
cmvc_include('actionmapperinterface.php');
cmvc_include('cmvccontroller.php');
cmvc_include('cmvccomponent.php');
cmvc_include('dbaccess.php');
cmvc_include('dbfilter.php');
cmvc_include('./jwt/autoload.php');

// load the framework
cmvc_include_dir("./include/libcompactmvc/");

// and the rest
cmvc_include('include.php');
cmvc_include_dir("./application/", array(
		"CWebDriverTestCase.php"
));

// let's begin the execution...
