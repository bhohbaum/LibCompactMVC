<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 *
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class WSAdapter {
	private static $instance;

	private function __construct() {
		DLOG(__METHOD__);
		if (Session::get_instance()->get_property(ST_WS_SRV_IDX) == null) {
			Session::get_instance()->set_property(ST_WS_SRV_IDX, rand(0, WS_SRV_COUNT - 1));
		}
	}

	public static function get_instance() {
		DLOG(__METHOD__);
		if (!isset(self::$instance)) {
			self::$instance = new WSAdapter();
		}
		return self::$instance;
	}

	public function get_srv_url() {
		DLOG(__METHOD__);
		return $GLOBALS['WS_BASE_URL'][Session::get_instance()->get_property(ST_WS_SRV_IDX)] . md5(session_id());
	}

	public function notify($msg) {
		DLOG(__METHOD__ . "('" . $msg . "')");
		$cmd = "/bin/bash -c 'echo " . md5(Session::get_instance()->get_id()) . " " . $msg . " | bin/libwebsockets-client " . $GLOBALS['WS_SRV_ADDR'][Session::get_instance()->get_property(ST_WS_SRV_IDX)] . " --port " . $GLOBALS['WS_SRV_PORT'][Session::get_instance()->get_property(ST_WS_SRV_IDX)] . "' &";
		DLOG("COMMAND: " . $cmd);
		system($cmd);
	}

}
