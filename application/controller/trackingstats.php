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
class TrackingStats extends CMVCController {

	private $param0;
	private $param1;
	private $param2;
	private $param3;

	private $mailingid;

	private $sort_key;
	private $sort_dir;

	protected function dba() {
		return "DBA";
	}

	protected function retrieve_data() {
		DLOG(__METHOD__);
		$this->param0 = $this->request("param0");
		$this->param1 = $this->request("param1");
		$this->param2 = $this->request("param2");
		$this->param3 = $this->request("param3");
		if (is_numeric($this->param0)) {
			$this->mailingid = $this->param0;
		}
	}

	protected function run_page_logic_get() {
		DLOG(__METHOD__);
		$events = array();
		if ($this->param0 == "tp") {
			$mhr = $this->db->get_mhr_by_ident($this->param1, true);
			if (is_numeric($this->param2)) {
				$fk_id_mailparts = $this->param2;
				$this->db->create_tracking_event($mhr->id, TRACKING_LINK_CLICKED, $fk_id_mailparts);
				$this->log->notice("Tracking mailpart - mhr.id: ".$mhr->id." forwarding to: ".urldecode(base64_decode($this->param3)));
				header("Location: ".urldecode(base64_decode($this->param3)));
			} else {
				$fk_id_mailparts = null;
				$this->db->create_tracking_event($mhr->id, TRACKING_MAIL_OPENED, $fk_id_mailparts);
				$this->log->notice("Tracking pixel - mhr.id: ".$mhr->id);
				header("Content-Type: image; ");
				header('Content-Disposition: attachment; filename="tp.jpg"');
			}
			return;
		} else if ($this->param0 == "mail") {
			$mhr = $this->db->get_mhr_by_id($this->param1, true);
			$receiver = $this->db->get_receiver_by_id($mhr->fk_id_receivers, true);
			$mailing = new DbObject();
			$mailing = $mailing->table(TBL_MAILINGS)->by(array("id" => $mhr->fk_id_mailings));
			$events = $this->db->get_tracking_events_by_mhr_id($this->param1, true);
			foreach ($events as $key => $val) {
				try {
					$mp = new DbObject();
					$mp->table(TBL_MAILPARTS)->by(array("id" => $val->te_fk_id_mailparts));
					$events[$key]->mailpart = $mp;
				} catch (EmptyResultException $e) {
				}
			}
			$this->view->add_template("header.tpl");
			$this->view->add_template("receiverevents.tpl");
			$this->view->add_template("footer.tpl");
			$this->view->set_value("mhr", $mhr);
			$this->view->set_value("mailing", $mailing);
			$this->view->set_value("receiver", $receiver);
			$this->view->set_value("events", $events);
			return;
		}
		$mailing = new DbObject();
		$mailing = $mailing->table(TBL_MAILINGS)->by(array("id" => $mhr->fk_id_mailings));
		$this->view->set_value("mailing", $mailing);
		try {
			$events = $this->db->get_tracking_overview($this->mailingid);
		} catch (Exception $e) {
			header("Location: /app/mailinglist");
			return;
		}
		$maxord = 0;
		for ($i = 1; $i < 1024; $i++) {
			if (isset($events[0]["ord_".$i])) {
				$sum = 0;
				foreach ($events as $event) {
					$sum += $event["ord_".$i];
				}
				$this->view->set_value("sum_ord_".$i, $sum);
				$maxord = $i;
			}
		}
		$this->sort_key = $this->param1;
		$this->sort_dir = $this->param2;
		@usort($events, 'TrackingStats::cmp');
		$this->view->add_template("header.tpl");
		$this->view->add_template("trackingstats.tpl");
		$this->view->add_template("footer.tpl");
		$this->view->set_value("maxord", $maxord);
		$this->view->set_value("events", $events);
		$this->view->set_value("sort_type", ($this->param2 == null) ? SORT_ASC : ($this->param2 == SORT_ASC) ? SORT_DESC : SORT_ASC);
	}

	private function cmp($a, $b)
	{
	    if ($a[$this->sort_key] == $b[$this->sort_key]) {
	        return 0;
	    }
	    if ($this->sort_dir == SORT_DESC) {
		    return ($a[$this->sort_key] > $b[$this->sort_key]) ? -1 : 1;
	    } else {
	    	return ($a[$this->sort_key] > $b[$this->sort_key]) ? 1 : -1;
	    }
	}

}

?>