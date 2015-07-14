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
class Mail extends CMVCController {

	private $param0;
	private $param1;
	private $param2;

	private $mailing;
	private $receiver;
	private $mhr;

	protected function retrieve_data() {
		DLOG(__METHOD__);

		$this->param0 = $this->request("param0");
		$this->param1 = $this->request("param1");
		$this->param2 = $this->request("param2");

		$this->mailing = new DbObject();
		if ($this->param0 == "byid") {
			$this->mailing->table(TBL_MAILINGS)->by(array("id" => $this->param1));
		} else if ($this->param0 == "byident") {
			$this->mhr = $this->db->get_mhr_by_ident($this->param1);
			$this->mailing->table(TBL_MAILINGS)->by(array("id" => $this->mhr["fk_id_mailings"]));
			$this->receiver = $this->db->get_receiver_by_id($this->mhr["fk_id_receivers"], true);
		}
	}


	protected function run_page_logic() {
		DLOG(__METHOD__);
		$mailparts = $this->db->get_mailparts_by_mailing_id($this->mailing->id);
		foreach ($mailparts as $key => $mailpart) {
			if ($mailpart["fk_id_texts"] != null) {
				$text = new DbObject();
				$mailparts[$key]["fk_id_texts"] = $text->table(TBL_TEXTS)->by(array("id" => $mailpart["fk_id_texts"]));
			}
			if ($mailpart["fk_id_images"] != null) {
				$img = new DbObject();
				$mailparts[$key]["fk_id_images"] = $img->table(TBL_IMAGES)->by(array("id" => $mailpart["fk_id_images"]));
			}
			if ($mailpart["fk_id_mailpart_types"] != null) {
				$mailparts[$key]["fk_id_mailpart_types"] = $this->db->get_mailpart_type_by_id($mailpart["fk_id_mailpart_types"], true);
			}
		}

		$this->view->add_template("mail.tpl");
		$this->view->set_value("mailing", $this->mailing);
		$this->view->set_value("mailparts", $mailparts);
		$this->view->set_value("receiver", $this->receiver);
		$this->view->set_value("mhr", $this->mhr);
		$this->view->set_value("ident", $this->param1);
	}

}

?>