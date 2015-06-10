<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Action dispatcher
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class ActionDispatcher extends InputSanitizer {
	private $actionname;
	private $handlers;
	private $action;
	private $action_default;
	private $control_action;

	public function __construct($postgetvar) {
		parent::__construct();
		$this->actionname = $postgetvar;
		$this->handlers = array();
	}

	public function set_handler($pgvvalue, $classname) {
		$this->handlers[$pgvvalue] = new $classname();
		$this->action_default = "";
		$this->control_action = "";
		if (is_subclass_of($this->handlers[$pgvvalue], "CMVCController")) {
			return true;
		} else {
			throw new Exception("ActionDispatcher::set_handler(\"$pgvvalue\", \"$classname\"): Class must be a subclass of CMVCController.");
		}
	}

	public function set_default($pgvvalue) {
		$this->action_default = $pgvvalue;
	}

	public function set_control($pgvvalue) {
		$this->control_action = $pgvvalue;
	}

	public function run() {
		$this->action = ($this->request($this->actionname) == null) ? $this->action_default : $this->request($this->actionname);
		if ($this->control_action != "") {
			if (!isset($this->handlers[$this->control_action])) {
				throw new Exception("ActionDispatcher error: No handler registered for action " . $this->control_action);
			} else {
				try {
					$this->handlers[$this->control_action]->view->clear();
					$this->handlers[$this->control_action]->view->set_value($this->actionname, $this->action);
					$this->handlers[$this->control_action]->run();
				} catch (RBRCException $rbrce) {
					DLOG("Returning response from the RBRC.");
				} catch (Exception $e) {
					$this->handlers[$this->control_action]->run_exception_handler($e);
				}
				if ($this->handlers[$this->control_action]->redirect != "") {
					$this->action = $this->handlers[$this->control_action]->redirect;
				}
			}
		}
		do {
			if (isset($this->handlers[$this->action]) && $this->handlers[$this->action]->redirect != "") {
				$this->action = $this->handlers[$this->action]->redirect;
			}
			if (!isset($this->handlers[$this->action])) {
				throw new Exception("Redirect error: No handler registered for action '" . $this->action . "'");
			} else {
				try {
					$this->handlers[$this->action]->view->clear();
					$this->handlers[$this->action]->view->set_value($this->actionname, $this->action);
					$this->handlers[$this->action]->run();
				} catch (RBRCException $rbrce) {
					DLOG("Returning response from the RBRC.");
				} catch (Exception $e) {
					$this->handlers[$this->action]->run_exception_handler($e);
				}
			}
		} while ($this->handlers[$this->action]->redirect != "");
	}

	public function get_ob() {
		return $this->handlers[$this->action]->get_ob();
	}


}
