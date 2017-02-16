<?php
if (file_exists('../../include/libcompactmvc.php')) include_once('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * applicationmapper.php
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     digimap
 * @copyright   Copyright (c) Media Impression Unit 08
 * @link		http://www.miu08.de
 */
class ApplicationMapper extends ActionMapper {
	private $mapping2;
	private $mapping3;

	protected function __construct() {
		$this->mapping2["de"]["logout"] = new LinkProperty("/de/logout", true);

		$this->mapping3["de"]["staticcontent"]["aboutus"] = new LinkProperty("/de/s/ueber-uns", true);

		parent::__construct();
	}

	public function get_base_url() {
		return BASE_URL;
	}

	protected function get_mapping_2() {
		return $this->mapping2;
	}

	protected function get_mapping_3() {
		return $this->mapping3;
	}

}
