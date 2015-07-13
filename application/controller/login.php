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
class Login extends CMVCController {

	private $user;
	private $pass;

	protected function retrieve_data_post() {
		DLOG(__METHOD__);
		$this->user = $this->request("user");
		$this->pass = $this->request("pass");
	}

	protected function run_page_logic() {
		DLOG(__METHOD__);
		$this->view->add_template("header.tpl");
		$this->view->add_template("login.tpl");
		$this->view->add_template("footer.tpl");
	}

	protected function run_page_logic_post() {
		DLOG(__METHOD__);
		$userarr = json_decode(LOGIN_USERS, true);
		if ((isset($userarr[$this->user])) && ($userarr[$this->user] == md5($this->pass))) {
			Session::get_instance()->set_property("user", $this->user);
			$this->redirect = "mailinglist";
			return;
		} else {
			$this->view->set_value("error", "Zugangsdaten sind nicht gültig!");
		}
	}

}

?>