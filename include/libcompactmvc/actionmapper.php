<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * actionmapper.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package 	LibCompactMVC
 * @copyright 	Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link 		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class ActionMapper extends Singleton implements ActionMapperInterface {
	private $urllist;
	private $rev_action;
	private $rev_param0;
	private static $rev_route_lookup;
	protected $mapping2;
	protected $mapping3;

	protected function __construct() {
		DLOG();
		parent::__construct();
		$this->mapping2 = array();
		$this->mapping3 = array();
		self::$rev_route_lookup = array();
		$this->register_endpoints();
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
	 * Overwrite this method and register all endpoints via calls to register_ep_*() in there.
	 */
	abstract protected function register_endpoints();

	/**
	 *
	 * @param string $lang
	 * @param string $action
	 * @param LinkProperty $lprop
	 */
	protected function register_ep_2($lang, $action, LinkProperty $lprop) {
		DLOG("('$lang', '$action')");
		if (array_key_exists($lang, $this->mapping2) && is_array($this->mapping2[$lang])) {
			if (array_key_exists($action, $this->mapping2[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered: lang = '$lang', action = '$action'", 500);
			}
		}
		if (array_key_exists($lang, $this->mapping3) && is_array($this->mapping3[$lang])) {
			if (array_key_exists($action, $this->mapping3[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered on a deeper level: lang = '$lang', action = '$action'", 500);
			}
		}
		$this->mapping2[$lang][$action] = $lprop;
		self::$rev_route_lookup[route_id($action, null, "", $lang)] = $lprop;
	}

	/**
	 *
	 * @param string $lang
	 * @param string $action
	 * @param string $param0
	 * @param LinkProperty $lprop
	 */
	protected function register_ep_3($lang, $action, $param0, LinkProperty $lprop) {
		DLOG("('$lang', '$action', '$param0')");
		if (array_key_exists($lang, $this->mapping2) && is_array($this->mapping2[$lang])) {
			if (array_key_exists($action, $this->mapping2[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered on a higher level: lang = '$lang', action = '$action', (param0 = '$param0')", 500);
			}
		}
		if (array_key_exists($lang, $this->mapping3) && is_array($this->mapping3[$lang]) && is_array($this->mapping3[$lang][$action])) {
			if (array_key_exists($action, $this->mapping3[$lang]) && array_key_exists($param0, $this->mapping3[$lang][$action])) {
				throw new ActionMapperException("Path mapping is allready registered: lang = '$lang', action = '$action', param0 = '$param0'", 500);
			}
		}
		$this->mapping3[$lang][$action][$param0] = $lprop;
		self::$rev_route_lookup[route_id($action, $param0, "", $lang)] = $lprop;
	}

	public function get_requested_controller() {
		DLOG();
		try {
			$tmp = InputProvider::get_instance()->get_var("lang");
			$lang_defined = true;
		} catch (InvalidMemberException $e) {
			$lang_defined = false;
			WLOG("The request didn't contain the \$lang variable!! The default controller was subsequentely selected.");
			return "";
		}
		try {
			$tmp = InputProvider::get_instance()->get_var("action");
			$action_defined = true;
		} catch (InvalidMemberException $e) {
			$action_defined = false;
		}
		if ($action_defined && array_key_exists(InputProvider::get_instance()->get_var("action"), $this->mapping2[InputProvider::get_instance()->get_var("lang")]))
			return $this->mapping2[InputProvider::get_instance()->get_var("lang")][InputProvider::get_instance()->get_var("action")]->get_controller_name();
		try {
			$tmp = InputProvider::get_instance()->get_var("param0");
			$param0_defined = true;
		} catch (InvalidMemberException $e) {
			$param0_defined = false;
		}
		if ($action_defined && $param0_defined && 
				array_key_exists(InputProvider::get_instance()->get_var("action"), $this->mapping2[InputProvider::get_instance()->get_var("lang")]) && 
				array_key_exists(InputProvider::get_instance()->get_var("param0"), $this->mapping3[InputProvider::get_instance()->get_var("lang")][InputProvider::get_instance()->get_var("action")]))
			return $this->mapping3[InputProvider::get_instance()->get_var("lang")][InputProvider::get_instance()->get_var("action")][InputProvider::get_instance()->get_var("param0")]->get_controller_name();
		return "";
	}

	/**
	 * 
	 * @param string $id
	 * @return LinkProperty
	 */
	public function get_link_property_by_route_id($id) {
		DLOG("('$id')");
		if (!array_key_exists($id, self::$rev_route_lookup)) {
			throw new ActionMapperException("Route id '$id' could not be resolved.", 404);
		}
		return self::$rev_route_lookup[$id];
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_route_id() {
		$tmp = "";
		try {
			$tmp = route_id(InputProvider::get_instance()->get_var("action"));
			$tmp = route_id(InputProvider::get_instance()->get_var("action"), InputProvider::get_instance()->get_var("param0"));
		} catch (InvalidMemberException $e) {
		}
		return $tmp;
	}

	/**
	 *
	 * @return 2-dimensional array containing the paths
	 */
	protected function get_mapping_2() {
		return $this->mapping2;
	}

	/**
	 *
	 * @return 3-dimensional array containing the paths
	 */
	protected function get_mapping_3() {
		return $this->mapping3;
	}

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
			if (!array_key_exists($lang, $arr)) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', action = '$action', param0 = '$param0', urltail = '$urltail'", 500);
			} else if (!array_key_exists($action, $arr[$lang])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', action = '$action', param0 = '$param0', urltail = '$urltail'", 500);
			}
			$lnk = $arr[$lang][$action]->get_path();
		}
		if ($action != null && $param0 != null) {
			$arr = $this->get_mapping_3();
			$GLOBALS["FATAL_ERR_MSG"] = "lang = $lang, action = $action, param0 = $param0";
			if (!array_key_exists($lang, $arr)) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', action = '$action', param0 = '$param0', urltail = '$urltail'", 500);
			} else if (!array_key_exists($action, $arr[$lang])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', action = '$action', param0 = '$param0', urltail = '$urltail'", 500);
			} else if (!array_key_exists($param0, $arr[$lang][$action])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', action = '$action', param0 = '$param0', urltail = '$urltail'", 500);
			}
			$lnk = $arr[$lang][$action][$param0]->get_path();
		}
		if ($lnk == "") {
			$lnk = $this->get_base_url();
		}
		if ($urltail != "") {
			$lnk .= $urltail;
		}
		return $lnk;
	}

	public function get_sitemap() {
		DLOG();
		$urls = "";
		foreach ($GLOBALS["SITEMAP"] as $url)
			$urls .= BASE_URL . $url . "\n";
		return $urls;
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
