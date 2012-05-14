<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

class Socket {
	
	private $fh;
	
	public function __construct($host, $port) {
		$errno = 0;
		$errstr = "";
		$this->fh = fsockopen($host.":".$port, 25, $errno, $errstr, 2000);
		if (!$this->fh) {
			throw new Exception($errstr, $errno);
		}
	}
	
	public function read() {
		if ($this->fh) {
			$buf = fread($this->fh, 128);
		} else {
			throw new Exception("Unable to read from socket. No connection established.");
		}
		return $buf;
	}
	
	public function write($buf) {
		if ($this->fh) {
			fwrite($this->fh, $buf);
		} else {
			throw new Exception("Unable to write to socket. No connection established.");
		}
	}
	
}


?>