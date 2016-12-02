<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Database Exception
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class DBException extends Exception {

	public function __construct($message = null, $code = null, $previous = null) {
		DLOG("MySQL Errno $code: $message");
		parent::__construct($message, $code, $previous);
	}

}
