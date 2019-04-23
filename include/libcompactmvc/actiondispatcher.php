<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Action dispatcher
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ActionDispatcher extends InputSanitizer {
	private $handlers;
	private $handlersobj;
	private $action;
	private $action_default;
	private $control_action;

	public function __construct($postgetvar, $mapper) {
		self::$actionname = $postgetvar;
		$this->handlers = array();
		$this->handlersobj = array();
		parent::__construct($mapper);
	}

	public function set_handler($pgvvalue, $classname) {
		$this->handlers[$pgvvalue] = $classname;
		$this->action_default = "";
		$this->control_action = "";
	}

	public function set_default($pgvvalue) {
		$this->action_default = $pgvvalue;
	}

	public function set_control($pgvvalue) {
		$this->control_action = $pgvvalue;
	}

	public function run() {
		$this->action = ($this->request(self::$actionname) == null) ? $this->action_default : $this->request(self::$actionname);
		if ($this->control_action != "") {
			if (!isset($this->handlers[$this->control_action])) {
				throw new Exception("ActionDispatcher error: No handler registered for action " . $this->control_action);
			} else {
				try {
					$this->get_handlersobj($this->control_action)->get_view()->clear();
					$this->get_handlersobj($this->control_action)->get_view()->set_action_mapper(self::$action_mapper);
					$this->get_handlersobj($this->control_action)->run();
				} catch (RBRCException $rbrce) {
					DLOG("Returning response from the RBRC.");
				} catch (RedirectException $re) {
					if (!$re->is_internal()) return;
				}
				if ($this->get_handlersobj($this->control_action)->get_redirect() != "") {
					$this->action = $this->get_handlersobj($this->control_action)->get_redirect();
				}
			}
		}
		do {
			if (isset($this->handlers[$this->action]) && $this->get_handlersobj($this->action)->get_redirect() != "") {
				$this->action = $this->get_handlersobj($this->action)->get_redirect();
			}
			if (!isset($this->handlers[$this->action])) {
				throw new Exception("Redirect error: No handler registered for action '" . $this->action . "'");
			} else {
				try {
					$this->get_handlersobj($this->action)->get_view()->clear();
					$this->get_handlersobj($this->action)->get_view()->set_action_mapper(self::$action_mapper);
					$this->get_handlersobj($this->action)->run();
				} catch (RBRCException $rbrce) {
					DLOG("Returning response from the RBRC.");
				}
			}
		} while ($this->get_handlersobj($this->action)->get_redirect() != "");
	}

	public function get_ob() {
		return $this->get_handlersobj($this->action)->get_ob();
	}

	public function get_mime_type() {
		return $this->get_handlersobj($this->action)->get_mime_type();
	}

	public static function get_action_mapper() {
		DLOG();
		return self::$action_mapper;
	}

	private function get_handlersobj($action) {
		if (!@array_key_exists($action, $this->handlersobj)) {
			$this->handlersobj[$action] = new $this->handlers[$action]();
		}
		if (!is_subclass_of($this->handlersobj[$action], "CMVCController")) {
			unset($this->handlersobj[$action]);
			throw new Exception("ActionDispatcher::get_handlersobj(\"$action\"): Class must be a subclass of CMVCController.");
		}
		return $this->handlersobj[$action];
	}

}
