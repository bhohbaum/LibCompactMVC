<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Action dispatcher
 * 
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @license LGPL version 3
 * @link http://www.gnu.org/licenses/lgpl.html
 */
class ActionDispatcher {
	
	private $actionname;
	private $handlers;
	private $action;
	private $action_default;
	private $control_action;
	
	public function __construct($postgetvar) {
		$this->actionname = $postgetvar;
	}
	
	public function set_handler($pgvvalue, $classname) {
		$this->handlers[$pgvvalue] = new $classname();
		$this->action_default = "";
		$this->control_action = "";
		if (is_subclass_of($this->handlers[$pgvvalue], "Page")) {
			return true;
		} else {
			die("ActionDispatcher::set_handler(\"$pgvvalue\", \"$classname\"): Class must be a subclass of class Page.");
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
				die("ActionDispatcher error: No handler registered for control ".$this->control_action);
			} else {
				$this->handlers[$this->control_action]->view->clear();
				$this->handlers[$this->control_action]->view->set_value($this->actionname, $this->action);
				$this->handlers[$this->control_action]->run();
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
				die("Redirect error: No handler registered for '".$this->action."'");
			} else {
				$this->handlers[$this->action]->view->clear();
				$this->handlers[$this->action]->view->set_value($this->actionname, $this->action);
				$this->handlers[$this->action]->run();
			}
		} while ($this->handlers[$this->action]->redirect != "");
	}
	
	public function get_ob() {
		return $this->handlers[$this->action]->get_ob();
	}
	
	
	private function request($var) {
		switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
			case 'GET':
				$data = $_REQUEST;
				break;
			case 'POST':
				$data = $_REQUEST;
				break;
			case 'PUT':
				// the action is always determined from the request url, so we can use $_REQUEST here.
				$data = $_REQUEST;
				break;
			case 'DELETE':
				$data = $_REQUEST;
				break;
		}
		return isset($data[$var]) ? $data[$var] : null;
	}
	
	
}


?>