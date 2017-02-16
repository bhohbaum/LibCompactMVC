<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Logout page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 19.02.2014
 * @link		http://www.adrodev.de
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