<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once ('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Home page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Home extends CMVCController {

	protected function main_run() {
		DLOG();
		parent::main_run();
		$user = new user();
		$user->by(array(
				"name" => $this->user
		));
		$user->type_id->name;
		$user->type_id = 19;  // neue id
		$users = $this->get_db()->by(TBL_USER, array());
		DbAccess::get_instance(DBA_DEFAULT_CLASS);
	}

}

