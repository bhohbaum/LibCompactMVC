<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

class Log {
	
	private $db;
	private $user_id;
	
	public function __construct(DbAccess $db) {
		$this->db = $db;
		$user = $this->db->get_user($_SESSION['user']);
		$this->user_id = $user['id'];
	}
	
	public function log($loglevel, $text) {
		$this->db->write2log($loglevel, $this->user_id, $text);
	}
	
	
}

?>