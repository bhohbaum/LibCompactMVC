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
	
	protected function dba() {
		return "DBA";
	}
	
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
		$type = $this->db->get_mailpart_type_by_name($this->request("type"));
		if ($type == null) {
			$type = $this->db->get_mailpart_type_by_name($this->param2);
		}
		$this->type = $type["id"];
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
			$paramarr = explode("_", $upload->get_param_name());
			$this->ordinal = $paramarr[1];
			$mailpart = $this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $paramarr[1]);
			if ($mailpart == null) {
				$mailpartid = $this->db->create_mailpart($this->ordinal, $this->mailingid, $this->type, null, null);
				$mailpart = $this->db->get_mailpart_by_id($mailpartid);
			}
			if ($mailpart["fk_id_images"] != null) {
				$img = $this->db->get_image_by_id($mailpart["fk_id_images"]);
				if ($img == null) {
					$imgid = $this->db->create_image();
					$img = $this->db->get_image_by_id($imgid);
				}
			} else {
				$imgid = $this->db->create_image();
				$img = $this->db->get_image_by_id($imgid);
			}
			$this->db->update_mailpart(	$mailpart["id"], 
										null,
										$mailpart["ordinal"], 
										$mailpart["fk_id_mailings"], 
										$mailpart["fk_id_mailpart_types"], 
										$mailpart["fk_id_texts"], 
										$img["id"]);
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
			$mailpart = $this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $this->ordinal);
			if ($mailpart == null) {
				$mailpartid = $this->db->create_mailpart($this->ordinal, $this->mailingid, $this->type, null, null);
				$mailpart = $this->db->get_mailpart_by_id($mailpartid);
			}
			if ($mailpart["fk_id_texts"] != null) {
				$txt = $this->db->get_text_by_id($mailpart["fk_id_texts"]);
				if ($txt == null) {
					$txtid = $this->db->create_text($this->text);
					$txt = $this->db->get_text_by_id($txtid);
				} else {
					$this->db->update_text($txt["id"], $this->text);
				}
			} else {
				$txtid = $this->db->create_text($this->text);
				$txt = $this->db->get_text_by_id($txtid);
			}
			$this->db->update_mailpart(	$mailpart["id"], 
										$mailpart["link"], 
										$mailpart["ordinal"], 
										$mailpart["fk_id_mailings"], 
										$mailpart["fk_id_mailpart_types"], 
										$txt["id"], 
										$mailpart["fk_id_images"]);
		} else if ($this->param1 == "lnk") {
			$mailpart = $this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $this->ordinal);
			if ($mailpart == null) {
				$mailpartid = $this->db->create_mailpart($this->ordinal, $this->mailingid, $this->type, null, null);
				$mailpart = $this->db->get_mailpart_by_id($mailpartid);
			}
			$this->db->update_mailpart(	$mailpart["id"], 
										$this->link, 
										$mailpart["ordinal"], 
										$mailpart["fk_id_mailings"], 
										$mailpart["fk_id_mailpart_types"], 
										$mailpart["fk_id_texts"], 
										$mailpart["fk_id_images"]);
		} else if ($this->param1 == "testmail") {
			if (system("cd assets/scripts ; ./mailcron.php -d -t ".$this->param2." ".$this->mailingid." >> ".LOG_FILE) !== false) {
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
			$response["text"] = $this->db->get_text_by_id($response["mailpart"]["fk_id_texts"]);
			$this->json_response($response);
		} else if ($this->param1 == "img") {
			$response["mailpart"] = $this->db->get_mailpart_by_mailing_id_and_ordinal($this->mailingid, $this->ordinal);
			$response["image"] = $this->db->get_image_by_id($response["mailpart"]["fk_id_images"]);
			$this->json_response($response);
		} else if ($this->param1 == "mailing") {
			$response["mailing"] = $this->db->get_mailing_by_id($this->mailingid);
			$response["mailparts"] = $this->db->get_mailparts_by_mailing_id($this->mailingid);
			foreach ($response["mailparts"] as $key => $mailpart) {
				$response["mailparttypes"][$key] = $this->db->get_mailpart_type_by_id($mailpart["fk_id_mailpart_types"]);
			}
			$this->json_response($response);
		} else if ($this->param1 == "ordinals") {
			$mailparts = $this->db->get_mailparts_by_mailing_id($this->mailingid, true);
			foreach ($mailparts as $key => $mailpart) {
				$this->db->update_mailpart($mailpart->id, $mailpart->link, $key + 1, $mailpart->fk_id_mailings, $mailpart->fk_id_mailpart_types, $mailpart->fk_id_texts, $mailpart->fk_id_images);
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