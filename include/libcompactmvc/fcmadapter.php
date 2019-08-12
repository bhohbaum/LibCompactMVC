<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Firebase Cloud Messaging Adapter
 * With additional variable cache.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FCMAdapter extends Singleton {
	protected  static $instance;
	private $apikey;

	protected  function __construct() {
		DLOG();
		$this->apikey = null;
	}

	/**
	 *
	 * @return FCMAdapter Returns the only instance of this class. This is a Singleton, so there can only be one instance.
	 */
	public static function get_instance($a = NULL, $b = NULL, $c = NULL, $d = NULL, $e = NULL, $f = NULL, $g = NULL, $h = NULL, $i = NULL, $j = NULL, $k = NULL, $l = NULL, $m = NULL, $n = NULL, $o = NULL, $p = NULL) {
		return parent::get_instance();
	}
	
	public function send($title, $text, $client_token) {
		if ($this->apikey == null) throw new Exception("FCM API key is not set, cannot send push message!", 500);
		$view = new View();
		$view->add_template("__fcmsend.tpl");
		$view->set_value("title", $title);
		$view->set_value("body", $text);
		$view->set_value("token", $client_token);
		$view->set_value("fbapitoken", $this->apikey);
		$fname = tempnam("./files/temp/", "fcm_send_");
		file_put_contents($fname, $view->render(false));
		system("/bin/chmod +x " . $fname);
		system($fname . " > " . $fname . ".out" . " 2> " . $fname . ".err");
		$io = file_get_contents($fname . ".out");
		$ie = file_get_contents($fname . ".err");
		DLOG("CURL stdout: " . $io);
		DLOG("CURL stderr: " . $ie);
		unlink($fname);
		unlink($fname . ".out");
		unlink($fname . ".err");
		return $this;
	}
	
	public function set_api_key($key) {
		$this->apikey = $key;
		return $this;
	}

	
}

