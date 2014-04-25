<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Double linked list
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class ArrayList {
	private $ptr;
	private $items;

	public function __construct() {
		$this->ptr = 0;
		$this->items = array();
	}

	public function add_item($data) {
		while (isset($this->items[$this->ptr])) {
			$this->ptr++;
		}
		$this->items[$this->ptr] = $data;
	}

	public function get_item_count() {
		return count($this->items);
	}

	public function get_item($index) {
		if (isset($this->items[$index])) {
			return ($this->items[$index]);
		} else {
			return false;
		}
	}

	public function get_prev_item() {
		$this->ptr--;
		if (isset($this->items[$this->ptr])) {
			return $this->items[$this->ptr];
		} else {
			return false;
		}
	}

	public function get_current_item() {
		if (isset($this->items[$this->ptr])) {
			return $this->items[$this->ptr];
		} else {
			return false;
		}
	}

	public function get_next_item() {
		$this->ptr++;
		if (isset($this->items[$this->ptr])) {
			return $this->items[$this->ptr];
		} else {
			return false;
		}
	}

	public function get_position() {
		return $this->ptr;
	}

	public function set_position($pos) {
		$this->ptr = $pos;
		if ($this->ptr > count($this->items) - 1) {
			$this->ptr = count($this->items) - 1;
		}
		if ($this->ptr < 0) {
			$this->ptr = 0;
		}
	}

	public function reset() {
		$this->ptr = 0;
	}


}

?>