<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

class SMTP {
	
	private $server;
	private $user;
	private $pass;
	private $mail;
	private $sender;
	private $receiver;
	
	public function __construct($server) {
		$this->server = $server;
	}
	
	public function set_login($user, $pass) {
		$this->user = $user;
		$this->pass = $pass;
	}
	
	public function set_mail($sender, $receiver, $mail) {
		$this->mail = $mail;
		$this->sender = $sender;
		$this->receiver = $receiver;
	}
	
	public function send() {
		if (function_exists("gethostname")) {
			$hostname = gethostname();
		} elseif (function_exists("exec")) {
			$hostname = exec('hostname -f');
		} else {
			// this will probably make problems
			$hostname = "localhost";
		}
		$mailarr = explode("\n", $this->mail);
		foreach ($mailarr as $key => $val) {
			$mailarr[$key] = str_replace("\r", "", $val);
		}
		$sock = new Socket($this->server, 25);
		$ret = $sock->read();
		$sock->write("HELO ".$hostname."\r\n");
		$ret = $sock->read();
		if (($this->user != "") && ($this->pass != "")) {
			$sock->write("AUTH LOGIN\r\n");
			$ret = $sock->read();
			$sock->write(base64_encode($this->user)."\r\n");
			$ret = $sock->read();
			$sock->write(base64_encode($this->pass)."\r\n");
			$ret = $sock->read();
			if (strpos(strtolower($ret), "535") !== false) {
				$sock->write("QUIT\r\n");
				$sock->read();
				throw new Exception("SMTP authentication failed: ".$ret, 535);
			}
		}
		$sock->write("MAIL FROM:".$this->sender."\r\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "250") === false) {
			$sock->write("QUIT\r\n");
			$sock->read();
			throw new Exception("Could not set sender: ".$ret, substr($ret, 0, 3));
		}
		$sock->write("RCPT TO:".$this->receiver."\r\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "250") === false) {
			$sock->write("QUIT\r\n");
			$sock->read();
			throw new Exception("Could not set receipient: ".$ret, substr($ret, 0, 3));
		}
		$sock->write("DATA\r\n");
		$ret = $sock->read();
		foreach ($mailarr as $m) {
			$sock->write($m."\r\n");
		}
		$sock->write(".\r\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "250") === false) {
			$sock->write("QUIT\r\n");
			$sock->read();
			throw new Exception("Error during mail transmission: ".$ret, substr($ret, 0, 3));
		}
		$sock->write("QUIT\r\n");
		$ret = $sock->read();
		if (strpos(strtolower($ret), "221") === false) {
			throw new Exception("Notice: Could not close connection cleanly: ".$ret, substr($ret, 0, 3));
		}
	}
	
}


?>