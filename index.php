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
	
	public function __construct() {
		;
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
		$this->ad->set_default("login");
		$this->ad->set_control("control");
		$this->ad->run();
		echo($this->ad->get_ob());
	}
	
	
}

// redirect
if (substr($_SERVER["REQUEST_URI"], 0, 4) != "/app") {
	header("Location: /app/");
}

// entry point (MAIN)
try {
	$run = new Main();
	$run->app();
} catch (Exception $e) {
	echo("<pre>".$e->getTraceAsString()."</pre>");
}

