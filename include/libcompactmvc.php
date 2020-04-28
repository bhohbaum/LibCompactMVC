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
		return;
	}
	throw new ErrorException($message, 0, $severity, $file, $line);
}
set_error_handler("exception_error_handler");

// first include the configuration

function lcmvc_log_init() {
	if (!defined("LOG_FILE")) {
		die("LOG_FILE is undefined, please define it in ./include/config.php - exiting.\n");
	} else {
		@touch(LOG_FILE);
		if (!file_exists(LOG_FILE)) {
			die(LOG_FILE." cannot be created, exiting.\n");
		}
		if (!is_writable(LOG_FILE)) {
			die(LOG_FILE." is not writable by the current process, exiting.\n");
		}
	}
}

// entry point (MAIN)
function lcmvc_main() {
	try {
		$run = new Main();
		$run->app();
	} catch (Exception $e) {
		@ob_end_clean();
		echo("<h2>An unhandled exception occured</h2>");
		echo("<h4>Message:</h4>");
		echo("<p>".$e->getMessage()."</p>");
		if (defined('DEBUG') && DEBUG) {
			echo("<h4>Stacktrace:</h4>");
			echo("<pre>".$e->getTraceAsString()."</pre>");
		}
		$run->log("UNHANDLED EXCEPTION!!!");
		$run->log(print_r($_REQUEST, true));
		$run->log($e->getMessage());
		$run->log($e->getTraceAsString());
	}
}

$cfile = "./include/resources/cache/combined.php";

if (file_exists($cfile)) {
	define("COMBINED_CODE_LOADED", $cfile);
	include $cfile;
} else {
	require_once('./include/libcompactmvc/functions.php');
}

@cmvc_include('mysqlhost.php');
@cmvc_include('config.php');
@cmvc_include('singleton.php');
@cmvc_include('log.php');

lcmvc_log_init();

// include files with content that is required elsewhere
cmvc_include('inputsanitizer.php');
cmvc_include('actionmapperinterface.php');
cmvc_include('cmvccontroller.php');
cmvc_include('cmvccomponent.php');
cmvc_include('dbaccess.php');
cmvc_include('dbfilter.php');
// cmvc_include('./jwt/autoload.php');

// load the framework
cmvc_include('./include/jwt/emarref/jwt/include.php');
cmvc_include_dir("./include/jwt/emarref/jwt/src/");
cmvc_include_dir("./include/ApnsPHP/");
cmvc_include_dir("./include/libcompactmvc/");

// and the rest
cmvc_include('include.php');
cmvc_include_dir("./application/", array(
		"CWebDriverTestCase.php"
));

if (!file_exists($cfile)) {
	$files = get_included_files();
	$fh = fopen($cfile, "w");
	fwrite($fh, "<?php\n\n");
	foreach ($files as $file) {
		if (basename($file) != "index.php" && basename($file) != "libcompactmvc.php" && !str_contains(basename($file), "autoload") && !str_contains($file, "include/jwt/composer")) {
			$code = file_get_contents($file);
			$carr = explode("\n", $code);
			while (str_contains(implode("\n", $carr), "LIBCOMPACTMVC_ENTRY")) {
				array_shift($carr);
			}
			$code = implode("\n", $carr);
			$code = str_replace("<?php", "", $code);
			$rfname = str_replace(getcwd(), ".", $file);
			fwrite($fh, "// ####################################################### $rfname ####################################################### \\\\\n\n");
			if (str_contains($code, "namespace ")) {
				fwrite($fh, "require_once('$rfname');\n\n\n");
			} else {
				fwrite($fh, $code);
			}
		}
	}
	fclose($fh);
}

if (defined('DEBUG') && (DEBUG == 0)) {
	ob_start();
}


// let's begin the execution...
