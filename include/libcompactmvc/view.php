<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Template handling
 *
 * This class is used for template handling. It loads the templates, fills them
 * with values and generates the output into a buffer that can be retrieved.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class View {
	private $part;
	private $vals;
	private $tpls;
	private $comp;
	private static $mapper;
	/**
	 *
	 * @var LinkBuilder
	 */
	private $lb;

	/**
	 */
	public function __construct() {
		$this->part = array();
		$this->vals = array();
		$this->tpls = array();
		$this->comp = array();
		$this->lb = LinkBuilder::get_instance();
	}

	/**
	 *
	 * @param String $part_name
	 */
	public function activate($part_name) {
		$this->part[$part_name] = true;
		return $this;
	}

	/**
	 *
	 * @param String $part_name
	 * @return View
	 */
	public function deactivate($part_name) {
		$this->part[$part_name] = false;
		return $this;
	}

	/**
	 *
	 * @param String $part_name
	 */
	private function is_active($part_name) {
		if (isset($this->part[$part_name])) {
			return $this->part[$part_name];
		} else {
			return false;
		}
	}

	/**
	 *
	 * @param String $key
	 * @param unknown $value
	 */
	public function set_value($key, $value) {
		$this->vals[$key] = $value;
		return $this;
	}

	/**
	 *
	 * @param String $key
	 */
	private function get_value($key) {
		if (isset($this->vals[$key])) {
			return $this->vals[$key];
		} else {
			return "";
		}
	}

	/**
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	public function set_component($key, CMVCController $component) {
		$this->comp[$key] = $component;
		return $this;
	}

	/**
	 *
	 * @param String $key
	 */
	public function get_component($key) {
		return (array_key_exists($key, $this->comp)) ? $this->comp[$key] : null;
	}

	/**
	 *
	 * @param ActionMapper $mapper
	 */
	public function set_action_mapper(ActionMapper $mapper) {
		if (!isset(self::$mapper) && $mapper != null)
			self::$mapper = $mapper;
	}

	/**
	 *
	 * @param String $key
	 */
	private function component($key) {
		return (array_key_exists($key, $this->comp)) ? $this->comp[$key]->get_ob() : "";
	}

	/**
	 *
	 * @param String $val
	 */
	private function encode($val) {
		return htmlentities(UTF8::encode($val), ENT_QUOTES | ENT_HTML401, 'UTF-8');
	}

	/**
	 *
	 * @param ActionMapperInterface $mapper
	 * @param String $action
	 * @param String $subaction
	 * @param String $urltail
	 */
	private function link(ActionMapperInterface $mapper, $action = null, $subaction = null, $urltail = null) {
		return $this->lb->get_link($mapper, $action, $subaction, $urltail);
	}

	/**
	 *
	 * @param String $action
	 * @param String $subaction
	 * @param String $urltail
	 */
	private function lnk($action = null, $subaction = null, $urltail = null) {
		return $this->lb->get_link(self::$mapper, $action, $subaction, $urltail);
	}

	/**
	 *
	 * @param int $index
	 * @param String $name
	 */
	public function set_template(int $index, $name) {
		$this->tpls[$index] = $name;
		return $this;
	}

	/**
	 *
	 * @param String $name
	 */
	public function add_template($name) {
		$this->tpls[] = $name;
		return $this;
	}

	/**
	 */
	public function get_templates() {
		return $this->tpls;
	}

	public function clear() {
		$this->part = array();
		$this->vals = array();
		$this->tpls = array();
		$this->comp = array();
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function get_hash() {
		return md5(serialize($this));
	}

	/**
	 *
	 * @param bool $caching
	 */
	public function render($caching = CACHING_ENABLED) {
		if (DEBUG == 0) {
			@ob_end_clean();
		}
		foreach ($this->comp as $c) {
			$c->run();
		}
		ob_start();
		if ($caching) {
			$start = microtime(true);
			$key = REDIS_KEY_RCACHE_PFX . md5(serialize($this));
			$out = RedisAdapter::get_instance()->get($key);
			if ($out !== false) {
				RedisAdapter::get_instance()->expire($key, REDIS_KEY_RCACHE_TTL);
				$time_taken = (microtime(true) - $start) * 1000 . " ms";
				$msg = 'Returning content from render cache... (' . $key . ' | ' . $time_taken . ')';
				DLOG($msg);
				return $out;
			}
			$time_taken = (microtime(true) - $start) * 1000 . " ms";
			$msg = 'Starting Rendering... (' . $key . ' | ' . $time_taken . ')';
			DLOG($msg);
			$out = "";
		}
		if (count($this->tpls) > 0) {
			foreach ($this->tpls as $t) {
				if ((!defined("DEBUG")) || (DEBUG == 0)) {
					@$this->include_template($t);
				} else {
					$this->include_template($t);
				}
			}
		}
		$out = ob_get_contents();
		ob_end_clean();
		if ((!defined("DEBUG")) || (DEBUG == 0)) {
			@ob_start();
		}
		if ($caching) {
			RedisAdapter::get_instance()->set($key, $out);
			RedisAdapter::get_instance()->expire($key, REDIS_KEY_RCACHE_TTL);
			$time_taken = (microtime(true) - $start) * 1000 . " ms";
			$msg = 'Returning rendered content... (' . $key . ' | ' . $time_taken . ')';
			DLOG($msg);
		}
		return $out;
	}

	/**
	 *
	 * @param String $tpl_name
	 * @throws Exception
	 */
	private function include_template($tpl_name) {
		$file1 = "./include/resources/templates/" . $tpl_name;
		$file2 = "./templates/" . $tpl_name;
		if (file_exists($file1)) {
			include ($file1);
		} else if (file_exists($file2)) {
			include ($file2);
		} else {
			throw new Exception("Could not find template file: " . $tpl_name, 404);
		}
	}

}
