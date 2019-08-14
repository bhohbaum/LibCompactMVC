<?= "<?php\n" ?>
if (file_exists('../../include/libcompactmvc.php'))
	include_once ('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * ep<?= $this->get_value("table") ?>.php
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class EP<?= $this->get_value("table") ?> extends CMVCCRUDComponent {

	protected function get_component_id() {
		return "<?= $this->get_value("table") ?>";
	}
	
}
