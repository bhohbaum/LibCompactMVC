<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Template handling
 *
 * This class is used for template handling. It loads the templates, fills them
 * with values and generates the output into a buffer that can be retrieved.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class View {
	private $__part;
	private $__vals;
	private $__tpls;
	private $__comp;
	private static $__mapper;
	
	/**
	 *
	 * @var LinkBuilder
	 */
	private $__lb;

	/**
	 */
	public function __construct() {
		$this->__part = array();
		$this->__vals = array();
		$this->__tpls = array();
		$this->__comp = array();
		$this->__lb = LinkBuilder::get_instance();
	}

	/**
	 *
	 * @param String $part_name
	 */
	public function activate($part_name) {
		$this->__part[$part_name] = true;
		return $this;
	}

	/**
	 *
	 * @param String $part_name
	 * @return View
	 */
	public function deactivate($part_name) {
		$this->__part[$part_name] = false;
		return $this;
	}

	/**
	 *
	 * @param String $part_name
	 */
	private function is_active($part_name) {
		if (array_key_exists($part_name, $this->__part)) {
			return $this->__part[$part_name];
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
		$this->__vals[$key] = $value;
		return $this;
	}

	/**
	 *
	 * @param String $key
	 */
	private function get_value($key) {
		if (array_key_exists($key, $this->__vals)) {
			return $this->__vals[$key];
		} else {
			return "";
		}
	}
	
	/**
	 * 
	 * @return InputProvider
	 */
	private function get_input() {
		return InputProvider::get_instance();
	}
	
	/**
	 * get variable content from request
	 * 
	 * @param unknown $var_name 
	 * @return mixed
	 */
	private function get_input_var($var_name) {
		try {
			return InputProvider::get_instance()->get_var($var_name);
		} catch (InvalidMemberException $e) {
			return "";
		}
	}

    /**
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	public function set_component($key, CMVCController $component) {
		if (array_key_exists($key, $this->__comp))
			throw new Exception("Component id is already in use: " . $key);
		$this->__comp[$key] = $component;
		return $this;
	}

	/**
	 *
	 * @param String $key
	 */
	public function get_component($key) {
		return (array_key_exists($key, $this->__comp)) ? $this->__comp[$key] : null;
	}

	/**
	 *
	 * @param ActionMapper $mapper
	 */
	public function set_action_mapper(ActionMapper $mapper) {
		if (!isset(self::$__mapper) && $mapper != null)
			self::$__mapper = $mapper;
	}

	/**
	 *
	 * @param String $key
	 */
	private function component($key) {
		return (array_key_exists($key, $this->__comp)) ? $this->__comp[$key]->get_ob() : "";
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
	 * @param String $param0
	 * @param String $urltail
	 */
	private function link(ActionMapperInterface $mapper, $action = null, $param0 = null, $urltail = null, $lang = null) {
		return $this->__lb->get_link($mapper, $action, $param0, $urltail, $lang);
	}

	/**
	 *
	 * @param String $action
	 * @param String $param0
	 * @param String $urltail
	 */
	private function lnk($action = null, $param0 = null, $urltail = null, $lang = null) {
		return $this->__lb->get_link(self::$__mapper, $action, $param0, $urltail, $lang);
	}

	/**
	 *
	 * @param int $index
	 * @param String $name
	 */
	public function set_template($index, $name) {
		$this->__tpls[$index] = $name;
		return $this;
	}

	/**
	 *
	 * @param String $name
	 */
	public function add_template($name) {
		$this->__tpls[] = $name;
		return $this;
	}

	/**
	 */
	public function get_templates() {
		return $this->__tpls;
	}

	public function clear() {
		$this->__part = array();
		$this->__vals = array();
		$this->__tpls = array();
		$this->__comp = array();
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function get_hash() {
		$serialized = serialize(array(
				$this->__part,
				$this->__vals,
				$this->__tpls,
				$this->__comp
		));
		$hash = md5($serialized);
		return $hash;
	}

	/**
	 *
	 * @param bool $caching
	 */
	public function render($caching = CACHING_ENABLED) {
		if (DEBUG == 0) {
			@ob_end_clean();
		}
		foreach ($this->__comp as $c) {
			$c->run();
		}
		ob_start();
		if ($caching) {
			$start = microtime(true);
			$key = REDIS_KEY_RCACHE_PFX . $this->get_hash();
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
		if (count($this->__tpls) > 0) {
			foreach ($this->__tpls as $t) {
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
			throw new FileNotFoundException("Could not find template file: " . $tpl_name, 404);
		}
	}

}
