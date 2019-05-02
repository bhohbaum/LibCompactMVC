<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * FIFO Buffer.
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FIFOBuffer {
	private $id_bufferid;
	private $id_first;
	private $id_last;
	private $elm_current;
	private $lockfile;
	private $ttl;

	/**
	 * Constructor.
	 * Leave the ID empty to create a new buffer. To access an already existing buffer, provide its id here.
	 *
	 * @param string $id
	 *        	Buffer ID or null to create a new one.
	 * @throws FIFOBufferException
	 */
	public function __construct($id = null) {
		$this->ttl = REDIS_KEY_FIFOBUFF_TTL;
		if ($id == null) {
			$this->id_bufferid = md5(microtime() . rand(0, 255));
			$this->save_state();
		} else {
			$this->id_bufferid = $id;
			$this->load_state();
		}
		if (!is_dir("./files/lock/"))
			mkdir("./files/lock/");
		$this->lockfile = "./files/lock/" . $this->id_bufferid . ".lock";
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$this->unlock();
		try {
			$this->check_buffer_status();
			$this->save_state();
		} catch (FIFOBufferException $e) {
		}
	}

	private function load_state() {
		$state = RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, $this->ttl);
		if ($state === false)
			throw new FIFOBufferException("Invalid FIFO buffer ID.", 404);
		$state = json_decode($state, true);
		$this->id_first = $state["first"];
		$this->id_last = $state["last"];
	}

	/**
	 * Save the buffer state to Redis.
	 */
	private function save_state() {
		$state = array(
				"first" => $this->id_first,
				"last" => $this->id_last
		);
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, json_encode($state), false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, $this->ttl);
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
		$obj = unserialize(RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $id, false));
		if ($obj === false) {
			throw new FIFOBufferException("Unable to load element " . $id);
		}
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $id, $this->ttl);
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
		RedisAdapter::get_instance()->set(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $elem->get_id(), serialize($elem), false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $elem->get_id(), $this->ttl);
	}

	/**
	 * Save a buffer element in Redis.
	 *
	 * @param unknown $id
	 *        	Element ID.
	 */
	private function delete_element($id) {
		$this->check_buffer_status();
		RedisAdapter::get_instance()->delete(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_" . $id);
	}

	/**
	 * Check if the buffer still exists.
	 *
	 * @throws FIFOBufferException
	 */
	private function check_buffer_status() {
		if ($this->id_bufferid == null) {
			throw new FIFOBufferException("Buffer destroyed.", 404);
		} else {
			$state = RedisAdapter::get_instance()->get(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid, false);
			if ($state === false) {
				$this->id_bufferid = null;
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
		return $this->id_bufferid;
	}

	/**
	 * Check if buffer is empty.
	 *
	 * @throws FIFOBufferException
	 * @return boolean True if buffer is empty, false otherwise.
	 */
	public function is_empty() {
		$this->check_buffer_status();
		$this->load_state();
		return !($this->id_first != null || $this->id_last != null);
	}

	/**
	 * Returns the number of elements in the buffer.
	 *
	 * @throws FIFOBufferException
	 * @return int Number of elements
	 */
	public function size() {
		$this->check_buffer_status();
		if ($this->is_empty()) {
			return 0;
		}
		$keys = RedisAdapter::get_instance()->keys(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFERELEMENT_" . $this->id_bufferid . "_*");
		return count($keys);
	}

	/**
	 * Add an element to the buffer queue.
	 *
	 * @param mixed $data
	 *        	Element data.
	 * @throws FIFOBufferException
	 */
	public function write($data, $ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			$elem = new FIFOBufferElement();
			$elem->set_data($data);
			$elem->set_prev(null);
			$elem->set_next(null);
			$this->id_first = $elem->get_id();
		} else {
			$elem = new FIFOBufferElement();
			$elem->set_data($data);
			$lastelem = $this->load_element($this->id_last);
			$lastelem->set_next($elem->get_id());
			$elem->set_prev($lastelem->get_id());
			$this->save_element($lastelem);
		}
		$this->id_last = $elem->get_id();
		$this->save_element($elem);
		$this->save_state();
		if (!$ignore_lock) {
			$this->unlock();
		}
	}

	/**
	 * Read the next element from the buffer and delete it.
	 *
	 * @throws FIFOBufferException
	 * @return mixed Element data.
	 */
	public function read($ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		$elem = $this->load_element($this->id_first);
		if ($elem->get_next() != null) {
			$firstelem = $this->load_element($elem->get_next());
			$firstelem->set_prev(null);
			$this->save_element($firstelem);
			$this->id_first = $firstelem->get_id();
		} else {
			$this->id_first = null;
			$this->id_last = null;
		}
		$this->save_state();
		usleep(1000);
		$this->delete_element($elem->get_id());
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $elem->get_data();
	}

	/**
	 * Read the first element of the buffer without deleting it.
	 * Increments the internal (object-wide, not global) iterator.
	 *
	 * @throws FIFOBufferException
	 * @return mixed Element data
	 */
	public function read_first($ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		$this->elm_current = $this->load_element($this->id_first);
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $this->elm_current->get_data();
	}

	/**
	 * Read the next element of the buffer without deleting it.
	 * Increments the internal (object-wide, not global) iterator.
	 *
	 * @throws FIFOBufferException
	 * @return mixed Element data
	 */
	public function read_next($ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		if ($this->elm_current == null) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return $this->read_first();
		}
		if ($this->elm_current->get_next() == null) {
			$this->elm_current = $this->load_element($this->elm_current->get_id());
			if ($this->elm_current->get_next() == null) {
				if (!$ignore_lock) {
					$this->unlock();
				}
				return null;
			}
		}
		try {
			$this->elm_current = $this->load_element($this->elm_current->get_next());
		} catch (FIFOBufferException $e) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return $this->read_first();
		}
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $this->elm_current->get_data();
	}

	/**
	 * Read an element at the given position.
	 *
	 * @param int $idx
	 *        	Element index
	 * @throws FIFOBufferException
	 * @return mixed Element data.
	 */
	public function read_at($idx, $ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			return null;
		}
		$elem = $this->load_element($this->id_first);
		for($i = 0; $i < $idx; $i++) {
			$elem = $this->load_element($elem->get_next());
		}
		if (!$ignore_lock) {
			$this->unlock();
		}
		return $elem->get_data();
	}

	/**
	 * Delete element at the given position.
	 *
	 * @param int $idx
	 *        	Element index
	 * @throws FIFOBufferException
	 */
	public function delete_at($idx, $ignore_lock = false) {
		if (!$ignore_lock) {
			$this->lock();
		}
		$this->check_buffer_status();
		$this->load_state();
		if ($this->is_empty()) {
			if (!$ignore_lock) {
				$this->unlock();
			}
			throw new FIFOBufferException("Invalid index, buffer is empty.", 404);
		}
		$elem = $this->load_element($this->id_first);
		for($i = 0; $i < $idx; $i++) {
			$elem = $this->load_element($elem->get_next());
		}
		$prev = $this->load_element($elem->get_prev());
		$next = $this->load_element($elem->get_next());
		$this->delete_element($elem->get_id());
		$prev->set_next($next->get_id());
		$next->set_prev($prev->get_id());
		$this->save_element($prev);
		$this->save_element($next);
		$this->save_state();
		if (!$ignore_lock) {
			$this->unlock();
		}
	}

	/**
	 * Destroy the buffer.
	 * All subsequent method calls on this buffer will throw a FIFOBufferException.
	 *
	 * @throws FIFOBufferException
	 */
	public function destroy() {
		$this->check_buffer_status();
		while (!$this->is_empty()) {
			$this->read();
		}
		RedisAdapter::get_instance()->delete(REDIS_KEY_FIFOBUFF_PFX . "FIFOBUFFER_" . $this->id_bufferid);
		$this->id_bufferid = null;
		$this->unlock();
	}

	/**
	 * Set an explicit lock on the buffer.
	 */
	public function lock() {
		clearstatcache($this->lockfile);
		while (file_exists($this->lockfile)) {
			usleep(10);
		}
		$fh = fopen($this->lockfile, "w+");
		fwrite($fh, "locked");
		fflush($fh);
		fclose($fh);
		clearstatcache($this->lockfile);
		while (!file_exists($this->lockfile)) {
			usleep(10);
		}
	}

	/**
	 * Remove an explicit lock from the buffer.
	 */
	public function unlock() {
		clearstatcache($this->lockfile);
		if (file_exists($this->lockfile))
			@unlink($this->lockfile);
		clearstatcache($this->lockfile);
	}

	/**
	 * Set a deviating TTL for this buffer instance.
	 *
	 * @param int $ttl
	 *        	The TTL to use for this buffer instance
	 */
	public function set_ttl($ttl) {
		$this->ttl = $ttl;
	}

}
