<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Socket wrapper class for easy socket handling.
 * 
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC Mail Module
 * @license LGPL version 3
 * @link http://www.gnu.org/licenses/lgpl.html
 */
class Socket {
	
	private $fh;
	
	/**
	 * Connect to an host on the given port.
	 * @param String $host hostname or IP address
	 * @param Integer $port port number
	 * @throws Exception error returned from fsockopen()
	 */
	public function __construct($host, $port) {
		$errno = 0;
		$errstr = "";
		$this->fh = fsockopen($host.":".$port, 25, $errno, $errstr, 2000);
		if (!$this->fh) {
			throw new Exception($errstr, $errno);
		}
	}
	
	/**
	 * Read from the socket.
	 * @throws Exception
	 */
	public function read() {
		if ($this->fh) {
			$buf = fread($this->fh, 128);
		} else {
			throw new Exception("Unable to read from socket. No connection established.");
		}
		return $buf;
	}
	
	/**
	 * Write to the socket.
	 * @param String $buf String to be written
	 * @throws Exception
	 */
	public function write($buf) {
		if ($this->fh) {
			fwrite($this->fh, $buf);
		} else {
			throw new Exception("Unable to write to socket. No connection established.");
		}
	}
	
}


?>