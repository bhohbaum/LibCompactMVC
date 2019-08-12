<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * linkbuilder.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class LinkBuilder extends Singleton {

	public function get_link(ActionMapperInterface $mapper, $path0 = null, $path1 = null, $urltail = "", $lang = null) {
		if ($lang == null && defined("ST_LANGUAGE")) {
			$lang = Session::get_instance()->get_property(ST_LANGUAGE);
		}
		$lang = (!is_string($lang)) ? InputProvider::get_instance()->get_var("lang") : $lang;
		return $mapper->get_base_url() . $mapper->get_path($lang, $path0, $path1, $urltail);
	}

}

