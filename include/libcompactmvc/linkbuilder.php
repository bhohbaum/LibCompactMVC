<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * linkbuilder.php
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     digimap
 * @copyright   Copyright (c) PIKMA GmbH
 * @link		http://www.pikma.de
 */
class LinkBuilder extends Singleton {
	private $session;

	protected function __construct() {
		$this->session = Session::get_instance();
	}

	public function get_link(ActionMapperInterface $mapper, $action = null, $subaction = null, $urltail = "") {
		return $mapper->get_base_url() . $mapper->get_path($this->session->get_property(ST_LANGUAGE), $action, $subaction, $urltail);
	}

}

