<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Action dispatcher
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ActionDispatcher extends InputSanitizer {
	private $handlers;
	private $handlersobj;
	private $action_default;
	private $control_action;
	private $last_controller;
	private static $current_route_id;
	
	public function __construct($postgetvar, $mapper) {
		self::$actionname = $postgetvar;
		$this->handlers = array();
		$this->handlersobj = array();
		parent::__construct($mapper);
	}

	public function set_default($pgvvalue) {
		$this->action_default = $pgvvalue;
	}

	public function set_control($pgvvalue) {
		$this->control_action = $pgvvalue;
	}

	public function run() {
		$route_id = "";
		if ($this->control_action != "") {
			try {
				self::$current_route_id = $this->control_action;
				DLOG("EXECUTING CONTROL ACTION: " . $this->control_action);
				$ho = $this->get_handlersobj($this->control_action);
				DLOG("CONTROLER TYPE: " . get_class($ho));
				$ho->get_view()->clear();
				$ho->run();
				$this->last_controller = $ho;
			} catch (RBRCException $rbrce) {
				DLOG("Returning response from the RBRC.");
			} catch (RedirectException $re) {
				if ($re->is_internal()) {
					if ($ho->get_redirect() != "") {
						$route_id = $ho->get_redirect();
					}
				}
			}
		}
		do {
			$route_id = ($route_id == "") ? $this->get_action_mapper()->get_route_id() : $route_id;
			$route_id = ($route_id == "" && $this->get_action_mapper()->get_route_id()) ? $this->action_default : $route_id;
			self::$current_route_id = $route_id;
			$ho = $this->get_handlersobj($route_id);
			DLOG("EXECUTING MAIN ACTION: " . $route_id);
			DLOG("CONTROLER TYPE: " . get_class($ho));
			try {
				$ho->get_view()->clear();
				$ho->run();
				$this->last_controller = $ho;
			} catch (RBRCException $rbrce) {
				DLOG("Returning response from the RBRC.");
			} catch (RedirectException $re) {
				if ($re->is_internal()) {
					if ($ho->get_redirect() != "") {
						$route_id = $ho->get_redirect();
					}
				}
			}
		} while ($ho->get_redirect() != "");
	}

	public function get_ob() {
		return $this->last_controller->get_ob();
	}

	public function get_mime_type() {
		return $this->last_controller->get_mime_type();
	}

	public static function get_action_mapper() {
		return self::$action_mapper;
	}

	/**
	 * 
	 * @param boolean $action true for automatic detection via get_requested_controller(), "" and $this->action_default for defautl ctrlr, $this->control_action for access control controller.
	 * @throws Exception
	 * @return unknown|mixed
	 */
	private function get_handlersobj($route_id = true) {
		$id_used = "";
		if ($route_id == "") {
			$route_id = $this->action_default;
		}
		$handler = $this->get_action_mapper()->get_link_property_by_route_id($route_id)->get_controller_name();
		$id_used = $route_id;
		if ($handler == "") {
			$handler = $this->get_action_mapper()->get_link_property_by_route_id($this->action_default)->get_controller_name();
			$id_used = $this->action_default;
		}
		DLOG("id_used  = $id_used");
		DLOG("handler  = $handler");
		if (array_key_exists($id_used, $this->handlersobj)) {
			DLOG("Retrieved object from cache.");
			return $this->handlersobj[$id_used];
		}
		DLOG("First use of this route, instantiating new $handler().");
		$ret = new $handler();
		$this->handlersobj[$id_used] = $ret;
		if (!is_subclass_of($this->handlersobj[$id_used], "CMVCController")) {
			unset($this->handlersobj[$id_used]);
			throw new Exception("ActionDispatcher::get_handlersobj(\"$action\"): Class must be a subclass of CMVCController.");
		}
		return $ret;
	}
	
	public static function get_current_route_id() {
		DLOG();
		return self::$current_route_id;
	}

}
