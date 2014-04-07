<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Test page
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 19.02.2014
 * @link		http://www.adrodev.de
 */
class Logout extends CMVCController {
	
	protected function dba() {
		return "DBA";
	}
	
	protected function run_page_logic() {
		DLOG(__METHOD__);
		Session::get_instance()->clear();
		$this->redirect = "login";
	}

}

?>