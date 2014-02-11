<?php
@include('./include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

session_start();

class Main {
	
	private $ad;
	
	public function __construct() {
		;
	}
	
	public function dispatch() {
		$this->ad = new ActionDispatcher("action");
		$this->ad->set_handler("test", 		"Test");
		$this->ad->set_default("test");
//		$this->ad->set_control("nlcontrol");
		$this->ad->run();
		echo($this->ad->get_ob());
	}
	
	
}

// entry point (MAIN)
$run = new Main();
$run->dispatch();

?>
