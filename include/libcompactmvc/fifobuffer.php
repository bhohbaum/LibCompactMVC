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

	/**
	 * Constructor.
	 * Leave the ID empty to create a new buffer. To access an already existing buffer, provide its id here.
	 *
	 * @param string $id
	 *        	Buffer ID or null to create a new one.
	 * @throws FIFOBufferException
	 */
	public function __construct($id = null) {
		if ($id == null) {
			$this->id = md5(microtime() . rand(0, 255));
			$this->save_state();
		} else {
			$this->id = $id;
			$state = RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id);
			RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id, REDIS_KEY_FIFOBUFF_TTL);
			if ($state === false)
				throw new FIFOBufferException("Invalid FIFO buffer ID.", 404);
			$state = json_decode($state, true);
			$this->first = $state["first"];
			$this->last = $state["last"];
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		try {
			$this->check_buffer_status();
			$this->save_state();
		} catch ( FIFOBufferException $e ) {
		}
	}

	/**
	 * Save the buffer state to Redis.
	 */
	private function save_state() {
		$state = array(
				"first" => $this->first,
				"last" => $this->last
		);
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id, json_encode($state));
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id, REDIS_KEY_FIFOBUFF_TTL);
	}

	/**
	 * Load a buffer element from Redis.
	 *
	 * @param unknown $id
	 *        	Element ID
	 * @throws FIFOBufferException
	 * @return FIFOBufferElement
	 */
	private function load_element($id) {
		$this->check_buffer_status();
		$obj = unserialize(RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $id));
		if ($obj === false) {
			throw new FIFOBufferException("Unable to load element " . $id);
		}
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $id, REDIS_KEY_FIFOBUFF_TTL);
		return $obj;
	}

	/**
	 * Save a buffer element in Redis.
	 *
	 * @param FIFOBufferElement $elem
	 *        	The element that is to save.
	 * @throws FIFOBufferException
	 */
	private function save_element(FIFOBufferElement $elem) {
		$this->check_buffer_status();
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $elem->get_id(), serialize($elem));
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $elem->get_id(), REDIS_KEY_FIFOBUFF_TTL);
	}

	/**
	 * Save a buffer element in Redis.
	 *
	 * @param unknown $id
	 *        	Element ID.
	 */
	private function delete_element($id) {
		$this->check_buffer_status();
		RedisAdapter::get_instance()->delete(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id . "_" . $id);
	}

	/**
	 * Check if the buffer still exists.
	 *
	 * @throws FIFOBufferException
	 */
	private function check_buffer_status() {
		if ($this->id == null) {
			throw new FIFOBufferException("Buffer destroyed.", 404);
		} else {
			$state = RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id);
			if ($state === false) {
				$this->id = null;
				throw new FIFOBufferException("Buffer destroyed.", 404);
			}
		}
	}

	/**
	 * Get the buffer ID.
	 *
	 * @throws FIFOBufferException
	 * @return string The buffer ID.
	 */
	public function get_id() {
		$this->check_buffer_status();
		return $this->id;
	}

	/**
	 * Check if buffer is empty.
	 *
	 * @throws FIFOBufferException
	 * @return boolean True if buffer is empty, false otherwise.
	 */
	public function is_empty() {
		$this->check_buffer_status();
		return ($this->first == null && $this->last == null);
	}

	/**
	 * Add an element to the buffer queue.
	 *
	 * @param mixed $data
	 *        	Element data.
	 * @throws FIFOBufferException
	 */
	public function write($data) {
		$this->check_buffer_status();
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

	/**
	 * Read the next element from the buffer.
	 *
	 * @throws FIFOBufferException
	 * @return mixed Element data.
	 */
	public function read() {
		$this->check_buffer_status();
		if ($this->is_empty())
			return null;
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

	/**
	 * Destroy the buffer.
	 * All subsequent method calls on this buffer will throw a FIFOBufferException.
	 *
	 * @throws FIFOBufferException
	 */
	public function destroy() {
		$this->check_buffer_status();
		while ( ! $this->is_empty() ) {
			$this->read();
		}
		RedisAdapter::get_instance()->delete(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id);
		$this->id = null;
	}
}
