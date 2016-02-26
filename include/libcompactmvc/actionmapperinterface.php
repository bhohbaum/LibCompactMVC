<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * actionmapperinterface.php
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
interface ActionMapperInterface {

	/**
	 *
	 * @return String base URL
	 */
	public function get_base_url();

	/**
	 *
	 * @param String $action
	 *        	action value
	 * @param String $subaction
	 *        	subaction value
	 * @param String $urltail
	 *        	additional tail of URL
	 * @return String path of URL
	 */
	public function get_path($lang, $action = null, $subaction = null, $urltail = null);

}
