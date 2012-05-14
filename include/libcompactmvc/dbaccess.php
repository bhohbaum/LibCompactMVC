<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// this class handles our DB connection and requests

class DbAccess {
	
	private $mysqli;
	private $mssql;
	
	// keeps instance of the classs
	private static $instance;
	
	private function __construct() {
//		$this->mysqli = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DB);
//		
//		if (mysqli_connect_error()) {
//		    die('Connect Error ('.mysqli_connect_errno().') '. mysqli_connect_error());
//		}
//		if ($this->action != "nlsend") {
//			$this->mssql = mssql_connect(MSSQL_HOST, MSSQL_USER, MSSQL_PASS);
//			mssql_select_db(MSSQL_DB, $this->mssql);
//		}
	}
	
	public function __destruct() {
		$this->close_db();
	}
	
	// prevent cloning
	private function __clone()
	{
		;
	}
	
	/**
	 * @return returns the instance of this class. this is a singleton. there can only be one instance.
	 */
	public static function get_instance() 
	{
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}
	
	public function close_db() {
//		if ($this->mysqli != null) {
//			$this->mysqli->close();
//			$this->mysqli = null;
//		}
//		if ($this->action != "nlsend") {
//			if ($this->mssql != null) {
//				mssql_close($this->mssql);
//				$this->mssql = null;
//			}
//		}
	}

	
	
}	

?>