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
class MailingList extends CMVCController {

	protected function run_page_logic() {
		DLOG(__METHOD__);
		$this->view->add_template("header.tpl");
		$this->view->add_template("mailinglist.tpl");
		$this->view->add_template("footer.tpl");
		$this->view->set_value("mailings", $this->db->by(TBL_MAILINGS, array()));
	}

}

?>