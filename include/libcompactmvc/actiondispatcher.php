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
	private $action_control;
	private $last_controller;
	private $base_path_num;
	private static $current_route_id;
	private static $mapper;
	
	public function __construct($mapper) {
		$this->handlers = array();
		$this->handlersobj = array();
		parent::__construct($mapper);
	}

	public function set_default($route_id) {
		$this->action_default = $route_id;
	}

	public function set_control($route_id) {
		$this->action_control = $route_id;
	}

	public function run() {
		$route_id = "";
		if ($this->action_control != "") {
			try {
				self::$current_route_id = $this->action_control;
				DLOG("EXECUTING CONTROL ACTION: " . $this->action_control);
				$ho = $this->get_handlersobj($this->action_control);
				$ho->set_base_path($this->base_path_num);
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
			$ho->set_base_path($this->base_path_num);
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
	
	public static function set_action_mapper(ActionMapperInterface $mapper) {
		self::$action_mapper = $mapper;
	}

/**
	 * 
	 * @param boolean $action true for automatic detection via get_requested_controller(), "" and $this->action_default for defautl ctrlr, $this->action_control for access control controller.
	 * @throws Exception
	 * @return CMVCController
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
		$this->base_path_num = $this->get_action_mapper()->get_link_property_by_route_id($id_used)->get_base_path_num();
		DLOG("id_used  = $id_used");
		DLOG("handler  = $handler");
		DLOG("base path depth = " . $this->base_path_num);
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
