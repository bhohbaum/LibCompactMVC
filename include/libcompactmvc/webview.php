<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * webview.php
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class WebView {
	private static $instance;
	private $is_open;

	private function __construct() {
		DLOG();
		$this->is_open = false;
	}

	public static function get_instance() {
		DLOG();
		if (!isset(self::$instance)) {
			self::$instance = new WebView();
		}
		return self::$instance;
	}

	public function close() {
		DLOG();
		if (!$this->is_open) return false;
		$this->is_open = false;
		if (is_windows()) exec_bg('bin\WebView.exe close');
		return true;
	}

	public function open($posx, $posy, $width, $height, $url) {
		DLOG();
		if ($this->is_open) return false;
		$this->is_open = true;
		if (is_windows()) exec_bg('bin\WebView.exe ' . $posx . ' ' . $posy . ' ' . $width . ' ' . $height . ' ' . $url);
		if (is_linux()) exec_bg('xdg-open ' . $url);
		return true;
	}

	public function is_open() {
		DLOG();
		return $this->is_open;
	}

}
