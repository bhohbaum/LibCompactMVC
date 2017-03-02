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
		$this->get_view()->add_template("home.tpl");
		$this->get_view()->set_template(10, "home.tpl");
		$this->get_view()->set_value("users", $users);
		$this->get_view()->activate("admin_pane");
		$this->dispatch_method($this->param0);
		$this->json_response($obj);
		$this->get_view()->clear();
		$this->binary_response($obj);
		$this->set_mime_type(MIME_TYPE_JSON);

		$this->set_component_dispatch_base($this->param0);
		$this->dispatch_component(new DummyComponent());
		$this->component_response();

		$this->add_component(new DummyComponent());

	}

	protected function exception_handler($e) {
		DLOG();
		throw $e;
	}


}

