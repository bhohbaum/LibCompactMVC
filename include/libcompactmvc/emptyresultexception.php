<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Invalid Member Exception
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class EmptyResultException extends DBException {

	public function __construct($message = "Empty result", $code = 404, $previous = null) {
		DLOG("Empty result. Reason: $code: $message");
		$this->message = $message;
		$this->code = $code;
	}

}
