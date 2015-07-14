<?php
include('./include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Index file
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 11.02.2014
 * @link		http://www.adrodev.de
 */
class Main {
	private $ad;
	public $log;

	public function __construct() {
		DLOG(__METHOD__ . ": ########################## A NEW REQUEST IS BEEING PROCESSED. ##########################");
	}

	public function log($msg) {
		DLOG($msg);
	}

	public function app() {
		$this->ad = new ActionDispatcher("action");
		$this->ad->set_handler("", 				"Login");
		$this->ad->set_handler("control", 		"Control");
		$this->ad->set_handler("login", 		"Login");
		$this->ad->set_handler("logout", 		"Logout");
		$this->ad->set_handler("mail", 			"Mail");
		$this->ad->set_handler("mailingedit", 	"MailingEdit");
		$this->ad->set_handler("mailinglist", 	"MailingList");
		$this->ad->set_handler("trackingstats", "TrackingStats");
		$this->ad->set_handler("uploads", 		"Uploads");
		$this->ad->set_default("login");
		$this->ad->set_control("control");
		$this->ad->run();
		echo($this->ad->get_ob());
		DLOG(__METHOD__ . ": ####### APPLICATION FINISHED SUCCESSFULLY! SENDING RESPONSE TO CLIENT... #######################");
	}


}

// redirect
if (php_sapi_name() != "cli") {
	if (substr($_SERVER["REQUEST_URI"], 0, 4) != "/app") {
		header("Location: /app");
		exit();
	}
} else {
	if ($argc <= 1) {
		die("CLI Mode detected: Please give a valid CLI submodule name as first parameter.\nValid modules are: cli\n");
	}
}

// entry point (MAIN)
try {
	$run = new Main();
	$run->app();
} catch (Exception $e) {
	if (defined('DEBUG') && DEBUG) {
		echo("<h2>An unhandled exception occured</h2>");
		echo("<h4>Message:</h4>");
		echo("<p>".$e->getMessage()."</p>");
		echo("<h4>Stacktrace:</h4>");
		echo("<pre>".$e->getTraceAsString()."</pre>");
	}
	$run->log($e->getTraceAsString());
}

