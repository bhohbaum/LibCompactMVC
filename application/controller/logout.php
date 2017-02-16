<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Logout page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class Logout extends CMVCController {

	protected function main_run() {
		DLOG();
		parent::main_run();
		Session::get_instance()->clear();
		throw new RedirectException(lnk("login"));
	}

}

?>