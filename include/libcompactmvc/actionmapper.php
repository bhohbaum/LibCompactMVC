<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * applicationmapper.php
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
abstract class ActionMapper extends Singleton implements ActionMapperInterface {

	protected function __construct() {
		DLOG();
		parent::__construct();
	}

	/**
	 * @return 2-dimensional array containing the paths
	 */
	abstract protected function get_mapping_2();

	/**
	 * @return 3-dimensional array containing the paths
	 */
	abstract protected function get_mapping_3();

	/**
	 * (non-PHPdoc)
	 * @see ActionMapperInterface::get_base_url()
	 */
	abstract public function get_base_url();

	/**
	 * (non-PHPdoc)
	 * @see ActionMapperInterface::get_path()
	 */
	public function get_path($lang, $action = null, $subaction = null, $urltail = null) {
		$lnk = "";
		if ($action != null && $subaction == null) {
			$arr = $this->get_mapping_2();
			$lnk = $arr[$lang][$action]->get_path();
		}
		if ($action != null && $subaction != null) {
			$arr = $this->get_mapping_3();
			$lnk = $arr[$lang][$action][$subaction]->get_path();
		}
		if ($urltail != "") {
			$lnk .= $urltail;
		}
		return $lnk;
	}

}
