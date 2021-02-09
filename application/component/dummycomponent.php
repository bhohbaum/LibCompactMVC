<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once ('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Dummy component
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DummyComponent extends CMVCComponent {

	public function get_component_id() {
		return "dummy";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		$test = $this->param(1);
		$this->set_base_param($test);
	}

}
