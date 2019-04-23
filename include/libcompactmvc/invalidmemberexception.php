<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Invalid Member Exception
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class InvalidMemberException extends Exception {
	public $previous;

	public function __construct($message = "Invalid member", $code = null, Exception $previous = null) {
		DLOG($message);
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}

}
