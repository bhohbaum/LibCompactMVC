<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * linkproperty.php
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     digimap
 * @copyright   Copyright (c) PIKMA GmbH
 * @link		http://www.pikma.de
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

