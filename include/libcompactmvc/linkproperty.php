<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * linkproperty.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class LinkProperty {
	private $path;
	private $isinsitemap;
	private $controller;
	private $base_path_num;

	public function __construct($path, $isinsitemap, $controller) {
		$this->path = $path;
		$this->isinsitemap = $isinsitemap;
		$this->controller = $controller;
	}

	public function get_path() {
		return $this->path;
	}
	
	public function get_path_level($num) {
		return explode("/", $this->path)[2 + $num];
	}

	public function get_action() {
		return explode("/", $this->path)[2];
	}

	public function get_param($num) {
		return explode("/", $this->path)[3 + $num];
	}

	public function is_in_sitemap() {
		return $this->isinsitemap;
	}
	
	public function get_controller_name() {
		return $this->controller;
	}
	
	public function set_base_path_num($num) {
		$this->base_path_num = $num;
	}
	
	public function get_base_path_num() {
		return $this->base_path_num;
	}
	
}

