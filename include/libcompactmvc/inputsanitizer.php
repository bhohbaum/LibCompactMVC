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
abstract class InputSanitizer implements JsonSerializable {
	private static $members_initialized;
	private static $members_populated;
	protected static $request_data;
	protected static $request_data_raw;
	protected static $action_mapper;
	protected static $member_variables;

	protected function __construct(ActionMapper $mapper = null) {
		if ($mapper != null) {
			self::$action_mapper = $mapper;
		}
		$this->populate_members();
	}

	protected function request($var = null) {
		if (!isset(InputSanitizer::$request_data) || InputSanitizer::$request_data == null) {
		    $put_vars = array();
		    if (!isset(InputSanitizer::$request_data_raw) || InputSanitizer::$request_data_raw == null) {
		        InputSanitizer::$request_data_raw = file_get_contents('php://input');
		        parse_str(InputSanitizer::$request_data_raw, $put_vars);
		    }
		    $data = array_merge($_REQUEST, $put_vars);
		    InputSanitizer::$request_data = $data;
		} else {
			$data = InputSanitizer::$request_data;
		}
		$ret = ($var != null) ? ((array_key_exists($var, $data)) ? $data[$var] : null) : $data;
		if (array_key_exists($var, self::$member_variables)) {
			$ret = self::$member_variables[$var];
		}
		DLOG("(" . $var . ") return: " . var_export($ret, true));
		return $ret;
	}

	private function populate_members() {
		if (self::$members_populated === true) {
			return;
		}
		if (self::$members_initialized === true && !isset(self::$action_mapper)) {
			return;
		}
		InputSanitizer::$request_data = null;
		self::$member_variables = array();
		global $argv;
		if (REGISTER_HTTP_VARS) {
			DLOG("Registering variables...");
			if (php_sapi_name() == "cli") {
				if (is_array(@getenv())) {
					foreach (@getenv() as $var => $val) {
						self::$member_variables[$var] = self::get_remapped($var, $val);
					}
				}
				if (is_array($argv)) {
					for($i = 1; $i <= 10; $i++) {
						if (array_key_exists($i + 0, $argv)) {
							$var = "path" . ($i - 1);
							self::$member_variables[$var] = self::get_remapped($var, $argv[$i + 0]);
						}
					}
				}
			} else {
				foreach (array_keys($this->request(null)) as $key) {
					self::$member_variables[$key] = self::get_remapped($key, $this->request($key));
				}
			}
			if (!array_key_exists("lang", self::$member_variables)) self::$member_variables["lang"] = LANG_DEFAULT;
			self::$member_variables["lang"] = (self::$member_variables["lang"] == null) ? LANG_DEFAULT : self::$member_variables["lang"];
		} else {
			DLOG("Registering variables is DISABLED...");
		}
		self::$members_populated = isset(self::$action_mapper);
		self::$members_initialized = true;
		DLOG(print_r(self::$member_variables, true));
	}

	private static function get_remapped($var_name, $value) {
		DLOG("$var_name = $value");
		if (isset(self::$action_mapper)) {
			if ($var_name == "path0") {
				$res = self::$action_mapper->reverse_path0($value);
			} else if ($var_name == "path1") {
				$res = self::$action_mapper->reverse_path1($value);
			} else {
				$res = $value;
			}
		} else {
			DLOG("ActionMapper not set!!!");
			$res = $value;
		}
		DLOG("Remapped: $var_name = $res");
		return $res;
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @throws InvalidMemberException
	 */
	public function __get($var_name) {
		if ($var_name == null) {
			$stack = debug_backtrace();
			throw new InvalidArgumentException('Unable to access a variable without a name at ' . $stack[0]["file"] . '" on line ' . $stack[0]["line"]);
		}
		$this->populate_members();
		if (!is_array(self::$member_variables)) {
			$stack = debug_backtrace();
			throw new InvalidMemberException('Member not defined: ' . get_class($this) . '::' . $var_name . ' in "' . $stack[0]["file"] . '" on line ' . $stack[0]["line"]);
		}
		if (!array_key_exists($var_name, self::$member_variables)) {
			$stack = debug_backtrace();
			throw new InvalidMemberException('Member not defined: ' . get_class($this) . '::' . $var_name . ' in "' . $stack[0]["file"] . '" on line ' . $stack[0]["line"]);
		} else {
			$res = self::$member_variables[$var_name];
		}
		return $res;
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @param unknown_type $value
	 */
	public function __set($var_name, $value) {
		$this->populate_members();
		self::$member_variables[$var_name] = $value;
	}

	/**
	 */
	public function jsonSerialize() {
		$ret = array();
		$this->populate_members();
		foreach (self::$member_variables as $key => $val) {
			$ret[$key] = $this->__get($key);
		}
		return $ret;
	}

	public function set_actionmapper(ActionMapper $mapper) {
		DLOG();
		self::$action_mapper = $mapper;
		$this->populate_members();
	}
	
	public function update_input_var($var, $content) {
		DLOG();
		$this->populate_members();
		self::$member_variables[$var] = $content;
	}
	
	public function to_array() {
		DLOG();
		$this->populate_members();
		return self::$member_variables;
	}

}
