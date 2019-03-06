<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * linkbuilder.php
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Media Impression Unit 08
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class LinkBuilder extends Singleton {
	private $session;

	protected function __construct() {
		$this->session = Session::get_instance();
	}

	public function get_link(ActionMapperInterface $mapper, $action = null, $param0 = null, $urltail = "", $lang = null) {
		if ($lang == null && defined("ST_LANGUAGE")) {
			$lang = $this->session->get_property(ST_LANGUAGE);
		}
		$lang = ($lang == null) ? "app" : $lang;
		return $mapper->get_base_url() . $mapper->get_path($lang, $action, $param0, $urltail);
	}

}

