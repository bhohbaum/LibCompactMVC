<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Action dispatcher
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
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
					$this->get_handlersobj($this->control_action)->view->clear();
					$this->get_handlersobj($this->control_action)->view->set_action_mapper(self::$action_mapper);
					$this->get_handlersobj($this->control_action)->view->set_value(self::$actionname, $this->action);
					$this->get_handlersobj($this->control_action)->run();
				} catch (RBRCException $rbrce) {
					DLOG("Returning response from the RBRC.");
				} catch (Exception $e) {
					$this->get_handlersobj($this->control_action)->run_exception_handler($e);
				}
				if ($this->get_handlersobj($this->control_action)->redirect != "") {
					$this->action = $this->get_handlersobj($this->control_action)->redirect;
				}
			}
		}
		do {
			if (isset($this->handlers[$this->action]) && $this->get_handlersobj($this->action)->redirect != "") {
				$this->action = $this->get_handlersobj($this->action)->redirect;
			}
			if (!isset($this->handlers[$this->action])) {
				throw new Exception("Redirect error: No handler registered for action '" . $this->action . "'");
			} else {
				try {
					$this->get_handlersobj($this->action)->view->clear();
					$this->get_handlersobj($this->action)->view->set_action_mapper(self::$action_mapper);
					$this->get_handlersobj($this->action)->view->set_value(self::$actionname, $this->action);
					$this->get_handlersobj($this->action)->run();
				} catch (RBRCException $rbrce) {
					DLOG("Returning response from the RBRC.");
				} catch (Exception $e) {
					$this->get_handlersobj($this->action)->run_exception_handler($e);
				}
			}
		} while ($this->get_handlersobj($this->action)->redirect != "");
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
