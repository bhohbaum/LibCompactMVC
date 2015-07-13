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
class Control extends CMVCController {

	private $user;
	private $ok;

	private $param0;
	private $param1;
	private $param2;

	protected function retrieve_data() {
		DLOG(__METHOD__);
		$this->user = Session::get_instance()->get_property("user");
		$this->param0 = $this->request("param0");
		$this->param1 = $this->request("param1");
		$this->param2 = $this->request("param2");
	}

	protected function run_page_logic() {
		DLOG(__METHOD__);
		// tracking pixel
		if ((($this->request("action") == "trackingstats")) && ($this->param0 == "tp")) {
			return;
		}
		// online mail view
		if ($this->request("action") == "mail") {
			return;
		}
		if (!isset($this->user)) {
			$this->redirect = "logout";
		}
	}

}

?>