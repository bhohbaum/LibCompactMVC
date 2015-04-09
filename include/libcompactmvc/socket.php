<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Socket wrapper class for easy socket handling.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class Socket {
	private $fh;

	/**
	 * Connect to an host on the given port.
	 *
	 * @param String $host
	 *        	hostname or IP address
	 * @param Integer $port
	 *        	port number
	 * @throws Exception error returned from fsockopen()
	 */
	public function __construct($host, $port = 25, $timeout = 2000) {
		$errno = 0;
		$errstr = "";
		$this->fh = fsockopen($host, $port, $errno, $errstr, $timeout);
		if (!$this->fh) {
			throw new Exception($errstr, $errno);
		}
	}

	/**
	 * Read from the socket.
	 *
	 * @throws Exception
	 */
	public function read() {
		$buf = "";
		$oldbuf = "";
		if ($this->fh) {
			$buf .= fread($this->fh, 8192);
			// TODO: read sizes >8192 bytes
// 			while (!feof($this->fh)) {
// 				stream_set_blocking($this->fh, true);
// 				$buf .= fread($this->fh, 8192);
// 				stream_set_blocking($this->fh, true);
// 				if ($oldbuf == $buf) {
// 					break;
// 				}
// 				$oldbuf = $buf;
// 			}
		} else {
			throw new Exception("Unable to read from socket. No connection established.");
		}
		return $buf;
	}

	/**
	 * Write to the socket.
	 *
	 * @param String $buf
	 *        	Data to be written
	 * @throws Exception
	 */
	public function write($buf) {
		$n = 0;
		$bytes_written = 0;
		$bytes_to_write = strlen($buf);
		if ($this->fh) {
			while ($bytes_written < $bytes_to_write) {
				if ($bytes_written == 0) {
					$rv = fwrite($this->fh, $buf);
				} else {
					$rv = fwrite($this->fh, substr($buf, $bytes_written));
				}
				if ($rv === false || $rv == 0) {
					throw new Exception("Unable to write to socket any more. " . $bytes_written . " of " . $bytes_to_write . " bytes written.");
				}
				$bytes_written += $rv;
			}
		} else {
			throw new Exception("Unable to write to socket. No connection established.");
		}
		return $n;
	}


}
