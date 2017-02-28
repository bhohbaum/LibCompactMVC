<?php
if (file_exists('../../include/libcompactmvc.php')) include_once('../../include/libcompactmvc.php');
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
	private $mapping2;
	private $mapping3;

	protected function __construct() {
		$this->mapping2["de"]["home"] = new LinkProperty("/de/home", true);

		$this->mapping3["de"]["ajaxep"]["user"] = new LinkProperty("/de/ajaxep/user", false);

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
