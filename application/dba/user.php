<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once ('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * user.php
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package Siemens CMS
 * @copyright Copyright (c) Media Impression Unit 08
 * @license BSD License (see LICENSE file in root directory)
 * @link http://www.miu08.de
 */
class user extends DbObject {

	protected function init() {
		DLOG();
		parent::init();
		$this->table(TBL_USER);
	}

}
