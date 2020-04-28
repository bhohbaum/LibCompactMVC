<?php
if (file_exists('../../include/libcompactmvc.php')) 
	include_once('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * applicationmapper.php
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum
 * @license		BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ApplicationMapper extends ActionMapper {

	protected function register_endpoints() {
		DLOG();
		ActionDispatcher::set_action_mapper($this);
		ActionDispatcher::set_control(route_id("control"));
		ActionDispatcher::set_default(route_id("login"));
		$langs = array("app");
		foreach ($langs as $lang) {
			$this->register_ep_2($lang, "home", new LinkProperty("/de/home", true, "Home"));
			
			$this->register_ep_3($lang, "ajaxep", "user", new LinkProperty("/de/ajaxep/user", false, "user"));
		}
	}

	public function get_base_url() {
		return BASE_URL;
	}


}
