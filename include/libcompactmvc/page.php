<?php 
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

abstract class Page {
	
	private $ob;
	
	protected $view;
	
	public $redirect;
	
	abstract protected function retrieve_data();
	abstract protected function run_page_logic();
	
	public function __construct() {
		$this->view = new View();
	}
	
	protected function request($var) {
		return isset($_POST[$var]) ? $_POST[$var] : (isset($_GET[$var]) ? $_GET[$var] : "");
	}
	
	public function run() {
		$this->redirect = "";
		$this->db = DbAccess::get_instance();
		if (!isset($this->view)) {
			$this->view = new View();
		}
		$this->view->clear();
		
		$this->retrieve_data();
		$this->run_page_logic();
		// after the page logic has been executed, we don't need
		// the database any more. all information should be stored 
		// in variables by now.
		unset($this->db);
		// if we have a redirect, we don't want the current template to be displayed
		if ($this->redirect == "") {
			$this->ob = $this->view->render();
		}
	}
	
	public function get_ob() {
		return $this->ob;
	}
	
}

?>