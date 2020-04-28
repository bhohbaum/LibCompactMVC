<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 *
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class WSAdapter {
	private static $instance;
	
	private $sesskey;

	private function __construct() {
		DLOG();
		if (Session::get_instance()->get_property(ST_WS_SRV_IDX) == null) {
			Session::get_instance()->set_property(ST_WS_SRV_IDX, rand(0, WS_SRV_COUNT - 1));
		}
		$this->sesskey = md5(Session::get_instance()->get_id());
	}

	public static function get_instance() {
		DLOG();
		if (!isset(self::$instance)) {
			self::$instance = new WSAdapter();
		}
		return self::$instance;
	}

	public function get_srv_url() {
		DLOG();
		return $GLOBALS['WS_BASE_URL'][Session::get_instance()->get_property(ST_WS_SRV_IDX)] . $this->sesskey;
	}

	public function notify($event, $payload = null) {
		if ($payload != null) {
			$encp = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			if (!$encp) $encp = $payload;
			$msg = $event . " " . $encp;
		} else {
			$msg = $event;
		}
		DLOG("Message: " . $msg);
		$fname = tempnam("./files/temp/", "qtwsdata_");
		file_put_contents($fname, $msg);
		$cmd = "/bin/bash -c '/bin/cat " . $fname . " | bin/qtwsclient -c " . $this->get_srv_url() . "; rm -f " . $fname . "'";
		exec_bg($cmd);
	}
	
	public function get_session_key() {
		DLOG();
		return $this->sesskey;
	}
	
	public function set_session_key($sesskey) {
		DLOG($sesskey);
		$this->sesskey = $sesskey;
	}

}
