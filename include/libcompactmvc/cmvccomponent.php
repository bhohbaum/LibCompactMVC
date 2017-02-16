<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Controller super class
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
abstract class CMVCComponent extends CMVCController {
	private $instance_id;
	private $base_param;

	/**
	 * Has to be implemented by every subclass. The output of the component (in the view) is identified by this string.
	 *
	 * @return String Component identification string
	 */
	abstract protected function get_component_id();

	/**
	 *
	 * @param int $base_param
	 */
	public function __construct($base_param = 0) {
		DLOG();
		parent::__construct();
		if (!is_int($base_param)) throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		$this->base_param = (!isset($this->base_param)) ? $base_param : $this->base_param;
		$this->instance_id = uniqid();
		$this->view->set_value("CMP_INST_ID", $this->instance_id);
		$this->view->set_value("CMP_ID", $this->get_component_id());
	}

	/**
	 *
	 * @param int $pnum
	 */
	public function set_base_param($pnum) {
		DLOG($pnum);
		if (!is_int($pnum)) throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		$this->base_param = $pnum;
	}

	/**
	 *
	 * @param int $pnum
	 */
	protected function param($pnum) {
		if (!is_int($pnum)) throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		$varname = 'param' . ($this->base_param + $pnum);
		$val = self::$member_variables[$varname];
		DLOG($varname . " = " . $val);
		return $val;
	}

}
