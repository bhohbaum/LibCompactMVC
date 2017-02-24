<?php
include('./include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Index file
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class Main {
	private $ad;

	public function __construct() {
		DLOG(__METHOD__ . ": ########################## A NEW REQUEST IS BEEING PROCESSED. ##########################");
	}

	public function log($msg) {
		DLOG($msg);
	}

	public function app() {
		$this->ad = new ActionDispatcher("action", ApplicationMapper::get_instance());
		$this->ad->set_handler("", 				"Login");
		$this->ad->set_handler("control", 		"Control");
		$this->ad->set_handler("login", 		"Login");
		$this->ad->set_handler("logout", 		"Logout");
		$this->ad->set_default("login");
		$this->ad->set_control("control");
		$this->ad->run();
		header("Content-Type: " . $this->ad->get_mime_type());
// 		header("Content-Security-Policy: default-src * 'unsave-inline 'unsave-eval'; script-src 'self' www.google-analytics.com ajax.googleapis.com;");
		header("X-Frame-Options: SAMEORIGIN");
		header("X-XSS-Protection: 1; mode=block");
		header("X-Content-Type-Options: nosniff");
		echo($this->ad->get_ob());
		DLOG("####### APPLICATION FINISHED SUCCESSFULLY! SENDING RESPONSE TO CLIENT... #######################");
	}


}

// redirect
if (php_sapi_name() == "cli") {
	if ($argc <= 1) {
		die("CLI Mode detected: Please give a valid CLI submodule name as first parameter.\nValid modules are: cli\n");
	}
}

// entry point (MAIN)
try {
	$run = new Main();
	$run->app();
} catch (Exception $e) {
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


