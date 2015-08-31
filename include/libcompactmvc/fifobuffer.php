<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * FIFO Buffer.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class FIFOBuffer {
	private $id;
	private $first;
	private $last;

	public function __construct($id = null) {
		if ($id == null) {
			$this->id = md5(microtime() . rand(0, 255));
		} else {
			$this->id = $id;
			$state = json_decode(RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id), true);
			RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id, REDIS_KEY_FIFOBUFF_TTL);
			if ($state === false) throw new FIFOBufferException("Unable to initialize FIFO buffer.");
			$this->first = $state["first"];
			$this->last = $state["last"];
		}
	}

	public function __destruct() {
		$state = array("first" => $this->first, "last" => $this->last);
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id, json_encode($state));
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id, REDIS_KEY_FIFOBUFF_TTL);
	}

	/**
	 *
	 * @param unknown $id
	 * @throws FIFOBufferException
	 * @return FIFOBufferElement
	 */
	private function load_element($id) {
		$obj = unserialize(RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $id));
		if ($obj === false) {
			throw new FIFOBufferException("Unable to load element " . $id);
		}
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $id, REDIS_KEY_FIFOBUFF_TTL);
		return $obj;
	}

	private function save_element(FIFOBufferElement $elem) {
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $elem->get_id(), serialize($elem));
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $elem->get_id(), REDIS_KEY_FIFOBUFF_TTL);
	}

	private function delete_element($id) {
		RedisAdapter::get_instance()->delete(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $id);
	}

	public function get_id() {
		return $this->id;
	}

	public function is_empty() {
		return ($this->first == null && $this->last == null);
	}

	public function write($data) {
		if ($this->is_empty()) {
			$elem = new FIFOBufferElement();
			$elem->set_data($data);
			$elem->set_prev(null);
			$elem->set_next(null);
			$this->first = $elem->get_id();
		} else {
			$elem = new FIFOBufferElement();
			$elem->set_data($data);
			$lastelem = $this->load_element($this->last);
			$lastelem->set_next($elem->get_id());
			$elem->set_prev($lastelem->get_id());
			$this->save_element($lastelem);
		}
		$this->last = $elem->get_id();
		$this->save_element($elem);
	}

	public function read() {
		if ($this->is_empty()) return;
		$elem = $this->load_element($this->first);
		if ($elem->get_next() != null) {
			$firstelem = $this->load_element($elem->get_next());
			$firstelem->set_prev(null);
			$this->save_element($firstelem);
			$this->first = $firstelem->get_id();
		} else {
			$this->first = null;
			$this->last = null;
		}
		$this->delete_element($elem->get_id());
		return $elem->get_data();
	}

}
