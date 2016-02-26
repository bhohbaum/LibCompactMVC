<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * linkproperty.php
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class LinkProperty {
	private $path;
	private $isinsitemap;

	public function __construct($path, $isinsitemap) {
		$this->path = $path;
		$this->isinsitemap = $isinsitemap;
	}

	public function get_path() {
		return $this->path;
	}

	public function is_in_sitemap() {
		return $this->isinsitemap;
	}

}

