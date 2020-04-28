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
	private $rev_path0;
	private $rev_path1;
	private static $rev_route_lookup;
	private $base_path_num;
	protected $mapping2;
	protected $mapping3;

	protected function __construct() {
		DLOG();
		parent::__construct();
		$this->mapping2 = array();
		$this->mapping3 = array();
		self::$rev_route_lookup = array();
		$this->rev_path0 = array();
		$this->rev_path1 = array();
		$this->register_internal_endpoints();
		$this->register_endpoints();
		$GLOBALS["SITEMAP"] = array();
		$m2 = $this->get_mapping_2();
		$m3 = $this->get_mapping_3();
		array_walk_recursive($m2, "add_to_sitemap");
		array_walk_recursive($m3, "add_to_sitemap");
	}
	
	/**
	 * 
	 * @param unknown $a
	 * @param unknown $b
	 * @param unknown $c
	 * @param unknown $d
	 * @param unknown $e
	 * @param unknown $f
	 * @param unknown $g
	 * @param unknown $h
	 * @param unknown $i
	 * @param unknown $j
	 * @param unknown $k
	 * @param unknown $l
	 * @param unknown $m
	 * @param unknown $n
	 * @param unknown $o
	 * @param unknown $p
	 * @return ActionMapper
	 */
	public static function get_instance($a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null, $j = null, $k = null, $l = null, $m = null, $n = null, $o = null, $p = null) {
		return parent::get_instance($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
	}
	

	/**
	 * Overwrite this method and register all endpoints via calls to register_ep_*() in there.
	 */
	abstract protected function register_endpoints();

	/**
	 *
	 * @param string $lang
	 * @param string $path0
	 * @param LinkProperty $lprop
	 */
	protected function register_ep_2($lang, $path0, LinkProperty $lprop) {
		DLOG("('$lang', '$path0')");
		if (array_key_exists($lang, $this->mapping2) && is_array($this->mapping2[$lang])) {
			if (array_key_exists($path0, $this->mapping2[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered: lang = '$lang', path0 = '$path0'", 500);
			}
		}
		if (array_key_exists($lang, $this->mapping3) && is_array($this->mapping3[$lang])) {
			if (array_key_exists($path0, $this->mapping3[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered on a deeper level: lang = '$lang', path0 = '$path0'", 500);
			}
		}
		if (array_key_exists($lprop->get_path_level(0), $this->rev_path0)) {
			if ($this->rev_path0[$lprop->get_path_level(0)] != $path0) {
				throw new ActionMapperException("Ambiguous path mapping: path0 '" . $lprop->get_path_level(0) . "' mapps to '" . $this->rev_path0[$lprop->get_path_level(0)] . "', '" . $path0 . "' and maybe others. Stopping reverse path resolution.", 500);
			}
		}
		$lprop->set_base_path_num(0);
		$this->mapping2[$lang][$path0] = $lprop;
		$this->rev_path0[$lprop->get_path_level(0)] = $path0;
		self::$rev_route_lookup[route_id($path0, null, "", $lang)] = $lprop;
	}

	/**
	 *
	 * @param string $lang
	 * @param string $path0
	 * @param string $path1
	 * @param LinkProperty $lprop
	 */
	protected function register_ep_3($lang, $path0, $path1, LinkProperty $lprop) {
		DLOG("('$lang', '$path0', '$path1')");
		if (array_key_exists($lang, $this->mapping2) && is_array($this->mapping2[$lang])) {
			if (array_key_exists($path0, $this->mapping2[$lang])) {
				throw new ActionMapperException("Path mapping is allready registered on a higher level: lang = '$lang', path0 = '$path0', (path1 = '$path1')", 500);
			}
		}
		if (@array_key_exists($lang, $this->mapping3) && @is_array($this->mapping3[$lang]) && @is_array($this->mapping3[$lang][$path0])) {
			if (array_key_exists($path0, $this->mapping3[$lang]) && array_key_exists($path1, $this->mapping3[$lang][$path0])) {
				throw new ActionMapperException("Path mapping is allready registered: lang = '$lang', path0 = '$path0', path1 = '$path1'", 500);
			}
		}
		if (array_key_exists($lprop->get_path_level(1), $this->rev_path1)) {
			if ($this->rev_path1[$lprop->get_path_level(1)] != $path1) {
				throw new ActionMapperException("Ambiguous path mapping: path1 '" . $lprop->get_path_level(1) . "' mapps to '" . $this->rev_path1[$lprop->get_path_level(1)] . "', '" . $path1 . "' and maybe others. Stopping reverse path resolution.", 500);
			}
		}
		$lprop->set_base_path_num(1);
		$this->mapping3[$lang][$path0][$path1] = $lprop;
		$this->rev_path1[$lprop->get_path_level(1)] = $path1;
		self::$rev_route_lookup[route_id($path0, $path1, "", $lang)] = $lprop;
	}

	/**
	 * 
	 * @param string $id
	 * @return LinkProperty
	 */
	public function get_link_property_by_route_id($inid) {
		$id = $inid;
		DLOG("('$id')");
		while (strlen($id) > 0 && !array_key_exists($id, self::$rev_route_lookup)) {
			DLOG($id);
			if (!array_key_exists($id, self::$rev_route_lookup)) {
				$arr = explode(".", $id);
				unset($arr[count($arr) - 1]);
				$id = implode(".", $arr);
			}
		}
		if (!array_key_exists($id, self::$rev_route_lookup)) {
			throw new ActionMapperException("Route id '$inid' could not be resolved.", 404);
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
			$tmp = route_id(InputProvider::get_instance()->get_var("path0"));
			$tmp = route_id(InputProvider::get_instance()->get_var("path0"), InputProvider::get_instance()->get_var("path1"));
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
	public function get_path($lang, $path0 = null, $path1 = null, $urltail = null) {
		DLOG("lang = $lang, path0 = $path0, path1 = $path1, urltail = $urltail");
		$lnk = "";
		if ($path0 != null && $path1 == null) {
			$arr = $this->get_mapping_2();
			$GLOBALS["FATAL_ERR_MSG"] = "lang = $lang, path0 = $path0";
			if (!array_key_exists($lang, $arr)) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			} else if (!array_key_exists($path0, $arr[$lang])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			}
			$lnk = $arr[$lang][$path0]->get_path();
		}
		if ($path0 != null && $path1 != null) {
			$arr = $this->get_mapping_3();
			$GLOBALS["FATAL_ERR_MSG"] = "lang = $lang, path0 = $path0, path1 = $path1";
			if (!array_key_exists($lang, $arr)) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			} else if (!array_key_exists($path0, $arr[$lang])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			} else if (!array_key_exists($path1, $arr[$lang][$path0])) {
				throw new ActionMapperException("Missing path mapping: lang = '$lang', path0 = '$path0', path1 = '$path1', urltail = '$urltail'", 500);
			}
			$lnk = $arr[$lang][$path0][$path1]->get_path();
		}
		if ($lnk == "") {
			$lnk = $this->get_base_url();
		}
		if ($urltail != "") {
			$lnk .= $urltail;
		}
		return $lnk;
	}

	/**
	 * Returns the text-sitemap. Can directly be returned to the client.
	 * 
	 * @return string
	 */
	public function get_sitemap() {
		DLOG();
		$urls = "";
		foreach ($GLOBALS["SITEMAP"] as $url)
			$urls .= BASE_URL . $url . "\n";
		return $urls;
	}

	/**
	 * Reverse translates the path0 variable (SEO value > internal value)
	 * 
	 * @param unknown $path0
	 * @return unknown|mixed
	 */
	public function reverse_path0($path0, $nolog = false) {
		if (!$nolog) DLOG(var_export($this->rev_path0, true));
		$tr = (array_key_exists($path0, $this->rev_path0)) ? $this->rev_path0[$path0] : $path0;
		if (!$nolog) DLOG("'$path0' translates back to '$tr'");
		return $tr;
	}

	/**
	 * Reverse translates the path1 variable (SEO value > internal value)
	 * 
	 * @param unknown $path1
	 * @return unknown|mixed
	 */
	public function reverse_path1($path1, $nolog = false) {
		if (!$nolog) DLOG(var_export($this->rev_path1, true));
		$tr = (array_key_exists($path1, $this->rev_path1)) ? $this->rev_path1[$path1] : $path1;
		if (!$nolog) DLOG("'$path1' translates back to '$tr'");
		return $tr;
	}
	
	private function register_internal_endpoints() {
		DLOG();
		$lang = InputProvider::get_instance()->get_var("lang");
		$this->register_ep_2($lang, "sys", new LinkProperty("/" . $lang . "/sys", false, "CMVCSystem"));
		$this->register_ep_3($lang, "sysint", "ormclientcomponent", new LinkProperty("/" . $lang . "/sysint/ormclient.js", false, "ORMClientComponent"));
	}

}
