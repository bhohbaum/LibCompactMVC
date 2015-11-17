<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * actionmapperinterface.php
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     digimap
 * @copyright   Copyright (c) PIKMA GmbH
 * @link		http://www.pikma.de
 */
interface ActionMapperInterface {

	/**
	 * @return String base URL
	 */
	public function get_base_url();

	/**
	 *
	 * @param String $action action value
	 * @param String $subaction subaction value
	 * @param String $urltail additional tail of URL
	 * @return String path of URL
	 */
	public function get_path($lang, $action = null, $subaction = null, $urltail = null);

}
