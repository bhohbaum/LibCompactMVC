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
class MailingEdit extends CMVCController {

	private $param0;
	private $param1;
	private $param2;
	private $mailingid;
	private $text;
	private $type;
	private $ordinal;
	private $mailingname;
	private $mailingdate;
	private $dataurl;
	private $subject;
	private $link;

	protected function retrieve_data() {
		DLOG(__METHOD__);
		$this->param0 = $this->request("param0");
		$this->param1 = $this->request("param1");
		$this->param2 = $this->request("param2");
		$this->text = $this->request("text");
		$this->mailingname = urldecode($this->request("mailingname"));
		$this->mailingdate = urldecode($this->request("mailingdate"));
		$this->dataurl = urldecode($this->request("dataurl"));
		$this->subject = urldecode($this->request("subject"));
		$this->link = $this->request("link");
		$type = new DbObject();
		try {
			$type->table(TBL_MAILPART_TYPES)->by(array("name" => $this->request("type")));
		} catch (EmptyResultException $e) {
			try {
				$type->table(TBL_MAILPART_TYPES)->by(array("name" => $this->param2));
			} catch (EmptyResultException $e) {
			}
		}
		$this->type = $type->id;
		$this->ordinal = $this->request("ordinal");
		if ($this->ordinal == null) {
			if (is_numeric($this->param1)) {
				$this->ordinal = $this->param1;
			}
		}
		if (is_numeric($this->param0)) {
			$this->mailingid = $this->param0;
		} else {
			$this->mailingid = $this->db->create_mailing("");
		}
		$this->log->log(Log::LOG_LVL_NOTICE, "param0 = ".$this->param0." | param1 = ".$this->param1." | param2 = ".$this->param2." | mailingid = ".$this->mailingid);
	}





	protected function run_page_logic_get() {
		DLOG(__METHOD__);
		$this->view->add_template("header.tpl");
		$this->view->add_template("mailingedit.tpl");
		$this->view->add_template("footer.tpl");
		$this->view->set_value("mailingid", $this->mailingid);
	}

	protected function run_page_logic_post() {
		DLOG(__METHOD__);
		if ($this->param1 == "img") {
			$upload = new Upload(UPLOAD_BASE_DIR);
			$fnames = $upload->save();
			$fname = @basename($fnames[0]);
			$paramarr = explode("_", $upload->get_param_name(0));
			$this->ordinal = $paramarr[1];
			$mailpart = new DbObject();
			try {
				$mailpart->table(TBL_MAILPARTS)->by(array("fk_id_mailings" => $this->mailingid, "ordinal" => $this->ordinal));
			} catch (Exception $e) {
				$mailpart = new DbObject(array(
						"ordinal" => $this->ordinal,
						"fk_id_mailings" => $this->mailingid,
						"fk_id_mailpart_types" => $this->type));
				$mailpart->table(TBL_MAILPARTS)->save();
				$mailpart->by(array("id" => $mailpart->id));
			}
			if ($mailpart->fk_id_images != null) {
				try {
					$img = new DbObject();
					$img->table(TBL_IMAGES)->by(array("id" => $mailpart->fk_id_images));
				} catch (EmptyResultException $e) {
					$img->table(TBL_IMAGES)->save();
					$img->by(array($img->id));
				}
			} else {
				$img = new DbObject();
				$img->table(TBL_IMAGES)->save();
				$img->by(array($img->id));
			}
			$img = $img->to_array();
			$mailpart->fk_id_images = $img["id"];
			$mailpart->save();
			if (!rename(UPLOAD_BASE_DIR."/".$fname, IMAGES_BASE_DIR."/".$img["name"].".jpg")) {
				if ((file_exists(UPLOAD_BASE_DIR."/".$fname)) && (file_exists(IMAGES_BASE_DIR."/".$img["name"].".jpg"))) {
					unlink(IMAGES_BASE_DIR."/".$img["name"].".jpg");
					copy(UPLOAD_BASE_DIR."/".$fname, IMAGES_BASE_DIR."/".$img["name"].".jpg");
					unlink(UPLOAD_BASE_DIR."/".$fname);
				}
			}
			$tmpfname = tempnam(TEMP_DIR."/", "img_");
			$image = imagecreatefromjpeg(IMAGES_BASE_DIR."/".$img["name"].".jpg");
			$width = imagesx($image);
			$height = imagesy($image);
			if ($width > 600) {
				$new_width = ($width > 600) ? 600 : $new_width;
				$ratio = $width / $height;
				$new_height = $new_width / $ratio;
				$tmp_img = imagecreatetruecolor($new_width, $new_height);
				imagecopyresized($tmp_img, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
				imagejpeg($tmp_img, $tmpfname);
				if (!rename($tmpfname, IMAGES_BASE_DIR."/".$img["name"].".jpg")) {
					if ((file_exists($tmpfname)) && (file_exists(IMAGES_BASE_DIR."/".$img["name"].".jpg"))) {
						unlink(IMAGES_BASE_DIR."/".$img["name"].".jpg");
						copy($tmpfname, IMAGES_BASE_DIR."/".$img["name"].".jpg");
						unlink($tmpfname);
					}
				}
				if (file_exists($tmpfname)) {
					unlink($tmpfname);
				}
				if (file_exists(IMAGES_BASE_DIR."/".$img["name"].".jpg")) {
					chmod(IMAGES_BASE_DIR."/".$img["name"].".jpg", 0666);
				}
			}
		} else if ($this->param1 == "txt") {
			$mailpart = new DbObject();
			try {
				$mailpart->table(TBL_MAILPARTS)->by(array("fk_id_mailing_id" => $this->mailingid, "ordinal" => $this->ordinal));
			} catch (EmptyResultException $e) {
				$mailpart = new DbObject(array(
						"ordinal" => $this->ordinal,
						"fk_id_mailings" => $this->mailingid,
						"fk_id_mailpart_types" => $this->type));
				$mailpart->table(TBL_MAILPARTS)->save();
				$mailpart->by(array("id" => $mailpart->id));
			}
			if ($mailpart->fk_id_texts != null) {
				$txt = new DbObject();
				try {
					$txt->table(TBL_TEXTS)->by(array("id" => $mailpart->fk_id_texts->id));
					$txt->text = $this->text;
					$txt->save();
				} catch (Exception $e) {
					$txt->table(TBL_TEXTS)->save();
				}
			} else {
				$txt = new DbObject();
				$txt->table(TBL_TEXTS);
				$txt->text = $this->text;
				$txt->save();
			}
			$mailpart->fk_id_texts = $txt->id;
			$mailpart->save();
		} else if ($this->param1 == "lnk") {
			$mailpart = new DbObject();
			try {
				$mailpart->table(TBL_MAILPARTS)->by(array("fk_id_mailing_id" => $this->mailingid, "ordinal" => $this->ordinal));
				$mailpart->link = $this->link;
				$mailpart->save();
			} catch (Exception $e) {
				$mailpart = new DbObject(array(
						"ordinal" => $this->ordinal,
						"fk_id_mailings" => $this->mailingid,
						"fk_id_mailpart_types" => $this->type));
				$mailpart->table(TBL_MAILPARTS)->save();
				$mailpart->by(array("id" => $mailpart->id));
			}
		} else if ($this->param1 == "testmail") {
			$cmd = "./assets/scripts/mailcron.php -d -t " . $this->param2 . " " . $this->mailingid . " >> " . LOG_FILE;
			DLOG("COMMAND: " . $cmd);
			if (system($cmd) !== false) {
				$this->json_response(true);
			} else {
				$this->json_response(false);
			}
		}
		if (($this->mailingid != null) && (is_numeric($this->mailingid))) {
			if ($this->mailingname != null) {
				$this->db->update_mailing_name($this->mailingid, $this->mailingname);
			}
			if ($this->mailingdate != null) {
				$this->db->update_mailing_send_date($this->mailingid, $this->mailingdate);
			}
			if ($this->dataurl != null) {
				$this->db->update_mailing_data_url($this->mailingid, $this->dataurl);
			}
			if ($this->subject != null) {
				$this->db->update_mailing_subject($this->mailingid, $this->subject);
			}
		}
	}

	protected function run_page_logic_put() {
		DLOG(__METHOD__);
		if ($this->param1 == "mailpart") {
			$this->json_response($this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $this->ordinal));
		} else if ($this->param1 == "txt") {
			$response["mailpart"] = $this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $this->ordinal);
			$txt = new DbObject();
			$response["text"] = $txt->table(TBL_TEXTS)->by(array("id" => $response["mailpart"]["fk_id_texts"]))->to_array();
			$this->json_response($response);
		} else if ($this->param1 == "img") {
			$response["mailpart"] = $this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $this->ordinal);
			$img = new DbObject();
			try {
				$response["image"] = $img->table(TBL_IMAGES)->by(array("id" => $response["mailpart"]["fk_id_images"]))->to_array();
			} catch (EmptyResultException $e) {
			}
			$this->json_response($response);
		} else if ($this->param1 == "mailing") {
			$mailing = new DbObject();
			$mailing->table(TBL_MAILINGS)->by(array("id" => $this->mailingid));
			$response["mailing"] = $mailing->to_array();
			$response["mailparts"] = $this->db->get_mailparts_by_mailing_id($this->mailingid);
			foreach ($response["mailparts"] as $key => $mailpart) {
				$response["mailparttypes"][$key] = $this->db->get_mailpart_type_by_id($mailpart["fk_id_mailpart_types"]);
			}
			$this->json_response($response);
		} else if ($this->param1 == "ordinals") {
			$mailparts = $this->db->get_mailparts_by_mailing_id($this->mailingid, true);
			foreach ($mailparts as $key => $mailpart) {
				$mp = new DbObject();
				$mp->table(TBL_MAILPARTS)->by(array("id" => $mailpart->id));
				$mp->ordinal = $key + 1;
				$mp->save();
			}
			$this->json_response(true);
		}
	}

	protected function run_page_logic_delete() {
		DLOG(__METHOD__);
		if ($this->mailingid != null) {
			if ($this->ordinal != null) {
				$mailpart = $this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $this->ordinal);
				if ($mailpart["fk_id_texts"] != null) {
					$this->db->delete_text_by_id($mailpart["fk_id_texts"]);
				}
				if ($mailpart["fk_id_images"] != null) {
					$this->db->delete_image_by_id($mailpart["fk_id_images"]);
				}
				$this->db->delete_mailpart_by_id($mailpart["id"]);
			} else {
				$this->db->delete_mailing_by_id($this->mailingid);
			}
		}
	}

}

?>