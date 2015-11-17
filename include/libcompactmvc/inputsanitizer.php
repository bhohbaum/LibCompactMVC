<?php
@include_once ('../libcompactmvc.php');
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
abstract class InputSanitizer implements JsonSerializable {
	private $members_populated;
	protected static $request_data;
	protected static $request_data_raw;
	protected $member_variables;
	public $log;

	protected function __construct() {
		$this->populate_members();
	}

	protected function request($var = null) {
		if (CMVCController::$request_data == null) {
			parse_str(file_get_contents('php://input'), $put_vars);
			CMVCController::$request_data_raw = $put_vars;
			$data = array_merge($_REQUEST, $put_vars);
			CMVCController::$request_data = $data;
		} else {
			$data = CMVCController::$request_data;
		}
		$ret = (isset($var)) ? (isset($data[$var]) ? $data[$var] : null) : $data;
		if (array_key_exists($var, $this->member_variables)) {
			$ret = $this->member_variables[$var];
		}
		DLOG(__METHOD__ . "(" . $var . ") return: " . var_export($ret, true));
		return $ret;
	}

	protected function populate_members() {
		if ($this->members_populated === true) {
			return;
		}
		CMVCController::$request_data = null;
		$this->member_variables = array();
		global $argv;
		if (REGISTER_HTTP_VARS) {
			if (php_sapi_name() == "cli") {
				if (is_array($argv)) {
					$var = "action";
					$this->member_variables[$var] = $argv[1];
					for ($i = 1; $i <=5 ; $i++) {
						if (array_key_exists($i + 1, $argv)) {
							$var = "param" . ($i - 1);
							$this->member_variables[$var] = $argv[$i + 1];
						}
					}
				}
			} else {
				foreach (array_keys($this->request(null)) as $key) {
					$this->{$key} = $this->request($key);
				}
			}
		}
		$this->members_populated = true;
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @throws InvalidMemberException
	 */
	public function __get($var_name) {
		if (!array_key_exists($var_name, $this->member_variables)) {
			$stack = debug_backtrace();
			throw new InvalidMemberException('Member not defined: '.get_class($this).'::'.$var_name.' in "'.$stack[0]["file"].'" on line '.$stack[0]["line"]);
		} else {
			return $this->member_variables[$var_name];
		}
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @param unknown_type $value
	 */
	public function __set($var_name, $value) {
		$this->member_variables[$var_name] = $value;
	}

	/**
	 *
	 */
	public function jsonSerialize() {
		$ret = array();
		foreach ($this->member_variables as $key => $val) {
			$ret[$key] = $this->__get($key);
		}
		return $ret;
	}

}
