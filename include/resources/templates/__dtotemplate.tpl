<?= "<?php\n" ?>
if (file_exists('../../include/libcompactmvc.php'))
	include_once ('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * <?= $this->get_value("table") ?>.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class <?= $this->get_value("table") ?> extends DbObject {

	public function get_endpoint() {
		DLOG();
<?php if ($this->get_value("briddepth") == 1) { ?>
		return lnk("<?= $this->get_value("table") ?>");
<?php } ?>
<?php if ($this->get_value("briddepth") == 2) { ?>
		return lnk("<?= $this->get_value("bridarr")[1] ?>", "<?= $this->get_value("table") ?>");
<?php } ?>
<?php if ($this->get_value("briddepth") == 3) { ?>
		return lnk("<?= $this->get_value("bridarr")[1] ?>", "<?= $this->get_value("bridarr")[2] ?>", "/<?= $this->get_value("table") ?>");
<?php } ?>
<?php if ($this->get_value("briddepth") == 4) { ?>
		return lnk("<?= $this->get_value("bridarr")[1] ?>", "<?= $this->get_value("bridarr")[2] ?>", "/<?= $this->get_value("bridarr")[3] ?>/<?= $this->get_value("table") ?>");
<?php } ?>
	}

}
