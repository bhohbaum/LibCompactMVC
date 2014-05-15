<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Test page
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package Birk Mailing
 * @copyright Copyright (c) Botho Hohbaum 19.02.2014
 * @link http://www.adrodev.de
 */
class Uploads extends CMVCController {
	private $direntries;
	private $param0;

	protected function retrieve_data() {
		DLOG(__METHOD__);
		$this->param0 = $this->request("param0");
		$this->direntries = array();
	}

	protected function run_page_logic_post() {
		$ul = new Upload(CSV_BASE_DIR);
		$ul->save();
		$this->run_page_logic_get();
	}
	
	protected function run_page_logic_get() {
		DLOG(__METHOD__);
		$dh = opendir(CSV_BASE_DIR);
		$counter = 0;
		while ($entry = readdir($dh)) {
			if (($entry == ".") || ($entry == "..")) {
				continue;
			}
			$this->direntries[$counter]["name"] = $entry;
			$this->direntries[$counter]["size"] = filesize(CSV_BASE_DIR."/".$entry);
			$counter++;
		}
		$this->view->add_template("header.tpl");
		$this->view->add_template("uploads.tpl");
		$this->view->set_value("direntries", $this->direntries);
	}

	protected function run_page_logic_delete() {
		DLOG(__METHOD__);
		$this->json_response(unlink(CSV_BASE_DIR."/".urldecode($this->param0)));
	}


}
	