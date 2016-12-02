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

	/**
	 * Has to be implemented by every subclass. The output of the component (in the view) is identified by this string.
	 *
	 * @return String Component identification string
	 */
	abstract protected function get_component_id();

	public function __construct() {
		DLOG();
		parent::__construct();
		$this->instance_id = uniqid();
		$this->view->set_value("CMP_INST_ID", $this->instance_id);
		$this->view->set_value("CMP_ID", $this->get_component_id());
	}

}
