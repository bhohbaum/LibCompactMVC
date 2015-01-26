<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// This class is used for template handling.
// It loads the templates, fills them with values and generates the output
// into a buffer that can be retrieved.

/**
 * Template handling
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class View {
	private static $instance;
	private $comp;
	private $vals;
	private $tpls;
	private $redis;
	public $log;

	public function __construct() {
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT);
	}

// 	private function __clone() {
// 	}

// 	public static function instance() {
// 		$name = get_called_class();
// 		var_dump($name);
// 		if (!isset(self::$instance)) {
// 			self::$instance = array();
// 		}
// 		if (!array_key_exists($name, self::$instance)) {
// 			self::$instance[$name] = new $name();
// 		}
// 		return self::$instance[$name];
// 	}

	public function activate($comp_name) {
		$this->comp[$comp_name] = true;
	}

	public function deactivate($comp_name) {
		$this->comp[$comp_name] = false;
	}

	private function is_active($comp_name) {
		if (isset($this->comp[$comp_name])) {
			return $this->comp[$comp_name];
		} else {
			return false;
		}
	}

	public function set_value($key, $value) {
		$this->vals[$key] = $value;
	}

	public function get_value($key) {
		if (isset($this->vals[$key])) {
			return $this->vals[$key];
		} else {
			return "";
		}
	}

	public function encode($val) {
		return htmlentities(UTF8::encode($val), ENT_QUOTES | ENT_HTML401, 'UTF-8');
	}

	public function set_template($index, $name) {
		$this->tpls[$index] = $name;
	}

	public function add_template($name) {
		$this->tpls[] = $name;
	}

	public function get_templates() {
		return $this->tpls;
	}

	public function clear() {
		$this->comp = array();
		$this->vals = array();
		$this->tpls = array();
	}

	public function render($caching = CACHING_ENABLED) {
		if ($caching) {
			$start = microtime(true);
			$key = REDIS_KEY_RCACHE_PFX . md5(serialize($this));
			$out = $this->redis->get($key);
			if ($out != null) {
				$this->redis->expire($key, REDIS_KEY_RCACHE_TTL);
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
		if (DEBUG == 0) {
			@ob_end_clean();
		}
		ob_start();
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
			$this->redis->set($key, $out);
			$this->redis->expire($key, REDIS_KEY_RCACHE_TTL);
			$time_taken = (microtime(true) - $start) * 1000 . " ms";
			$msg = 'Returning rendered content... (' . $key . ' | ' . $time_taken . ')';
			DLOG($msg);
		}
		return $out;
	}

	private function include_template($tpl_name) {
		$file1 = "./include/resources/templates/" . $tpl_name;
		$file2 = "./templates/" . $tpl_name;
		if (file_exists($file1)) {
			include ($file1);
		} else if (file_exists($file2)) {
			include ($file2);
		} else {
			throw new Exception("Could not find template file: " . $tpl_name);
		}
	}


}

?>