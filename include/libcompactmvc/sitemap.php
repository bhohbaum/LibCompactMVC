<?php
if (file_exists('../../include/libcompactmvc.php')) include_once('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * sitemap.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Sitemap extends CMVCController {

	protected function main_run() {
		DLOG();
		parent::main_run();
		$urls = ApplicationMapper::get_instance()->get_sitemap();
		$this->binary_response($urls, MIME_TYPE_HTML);
	}

}
