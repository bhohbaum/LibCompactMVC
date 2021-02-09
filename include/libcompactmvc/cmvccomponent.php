<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Controller super class
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class CMVCComponent extends CMVCController {
	private $__instance_id;
	private $__run_executed = false;

	/**
	 * Has to be implemented by every subclass. The output of the component (in the view) is identified by this string.
	 *
	 * @return String Component identification string
	 */
	abstract public function get_component_id();

	/**
	 *
	 * @return String Unique component id string, for distinguishing multiple instances within one request.
	 */
	protected function get_component_instance_id() {
		DLOG();
		return $this->__instance_id;
	}

	/**
	 *
	 * @param int $base_path
	 */
	public function __construct($base_path = null) {
		DLOG();
		parent::__construct();
		if (!is_int($base_path) && $base_path != null)
			throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		if ($base_path != null) $this->set_base_path($base_path);
		$this->__instance_id = uniqid();
		$this->get_view()->set_value("CMP_INST_ID", $this->__instance_id);
		$this->get_view()->set_value("CMP_ID", $this->get_component_id());
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see CMVCController::run()
	 */
	public function run() {
		DLOG();
		$this->__run_executed = true;
		parent::run();
	}

	/**
	 * 
	 * {@inheritDoc}
	 * @see CMVCController::get_ob()
	 */
	public function get_ob() {
		DLOG();
		if (!$this->__run_executed)
			$this->run();
		return parent::get_ob();
	}

	/**
	 *
	 * @param int $pnum
	 */
	protected function path($pnum) {
		if (!is_int($pnum))
			throw new InvalidArgumentException("Invalid Parameter: int expected.", 500);
		$varname = 'path' . ($this->get_base_path() + $pnum);
		if (!array_key_exists($varname, self::$member_variables))
			throw new InvalidMemberException("Invalid member: " . $varname);
		$val = self::$member_variables[$varname];
		DLOG($varname . " = " . $val);
		return $val;
	}

}
