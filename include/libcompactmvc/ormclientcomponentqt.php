<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * ormclientcomponentqt.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ORMClientComponentQt extends ORMClientComponent {
	
	protected function get_component_id() {
		DLOG();
		return "ormclientcomponentqt";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		$this->get_view()->set_template(0, "__ormclientqt.tpl");
	}

}
