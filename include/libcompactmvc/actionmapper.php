<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * actionmapper.php
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
abstract class ActionMapper extends Singleton implements ActionMapperInterface {
	private $urllist;
	private $rev_action;
	private $rev_param0;

	protected function __construct() {
		DLOG();
		parent::__construct();
		$GLOBALS["SITEMAP"] = array();
		$m2 = $this->get_mapping_2();
		$m3 = $this->get_mapping_3();
		array_walk_recursive($m2, "add_to_sitemap");
		array_walk_recursive($m3, "add_to_sitemap");
		$this->rev_action = array();
		$this->rev_param0 = array();
		foreach ($this->get_mapping_2() as $actions) {
			foreach ($actions as $action => $lp) {
				if (array_key_exists($lp->get_action(), $this->rev_action) && $this->rev_action[$lp->get_action()] != $action) {
					throw new ActionMapperException("Ambiguous path mapping: Action '" . $lp->get_action() . "' mapps to '" . $this->rev_action[$lp->get_action()] . "', '" . $action . "' and maybe others. Stopping reverse path resolution.", 500);
				}
				$this->rev_action[$lp->get_action()] = $action;
			}
		}
		foreach ($this->get_mapping_3() as $k1 => $v1) {
			foreach ($this->get_mapping_3()[$k1] as $k2 => $v2) {
				foreach ($this->get_mapping_3()[$k1][$k2] as $k3 => $v3) {
					if (array_key_exists($v3->get_param(0), $this->rev_param0) && $this->rev_param0[$v3->get_param(0)] != $k3) {
						throw new ActionMapperException("Ambiguous path mapping: Param0 '" . $v3->get_param(0) . "' mapps to '" . $this->rev_param0[$v3->get_param(0)] . "', '" . $k3 . "' and maybe others. Stopping reverse path resolution.", 500);
					}
					$this->rev_param0[$v3->get_param(0)] = $k3;
				}
			}
		}
	}

	/**
	 *
	 * @return 2-dimensional array containing the paths
	 */
	abstract protected function get_mapping_2();

	/**
	 *
	 * @return 3-dimensional array containing the paths
	 */
	abstract protected function get_mapping_3();

	/**
	 * (non-PHPdoc)
	 *
	 * @see ActionMapperInterface::get_base_url()
	 */
	abstract public function get_base_url();

	/**
	 * (non-PHPdoc)
	 *
	 * @see ActionMapperInterface::get_path()
	 */
	public function get_path($lang, $action = null, $param0 = null, $urltail = null) {
		DLOG("lang = $lang, action = $action, param0 = $param0, urltail = $urltail");
		$lnk = "";
		if ($action != null && $param0 == null) {
			$arr = $this->get_mapping_2();
			$GLOBALS["FATAL_ERR_MSG"] = "lang = $lang, action = $action";
			$lnk = $arr[$lang][$action]->get_path();
		}
		if ($action != null && $param0 != null) {
			$arr = $this->get_mapping_3();
			$GLOBALS["FATAL_ERR_MSG"] = "lang = $lang, action = $action, param0 = $param0";
			$lnk = $arr[$lang][$action][$param0]->get_path();
		}
		if ($urltail != "") {
			$lnk .= $urltail;
		}
		return $lnk;
	}

	public function get_sitemap() {
		DLOG();
		return $GLOBALS["SITEMAP"];
	}

	public function reverse_action($action) {
		DLOG($action);
		return (array_key_exists($action, $this->rev_action)) ? $this->rev_action[$action] : $action;
	}

	public function reverse_param0($param0) {
		DLOG($param0);
		return (array_key_exists($param0, $this->rev_param0)) ? $this->rev_param0[$param0] : $param0;
	}

}
