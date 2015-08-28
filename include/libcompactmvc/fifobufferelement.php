<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Element for ArrayList.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class FIFOBufferElement {
	private $id;
	private $data;
	private $prev;
	private $next;

	public function __construct() {
		$this->id = md5(microtime() . rand(0, 255));
	}

	public function get_id() {
		return $this->id;
	}

	public function get_data() {
		return $this->data;
	}

	public function set_data($data) {
		$this->data = $data;
	}

	public function get_prev() {
		return $this->prev;
	}

	public function set_prev($prev) {
		$this->prev = $prev;
	}

	public function get_next() {
		return $this->next;
	}

	public function set_next($next) {
		$this->next = $next;
	}

}
