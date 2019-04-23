<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Multiple Results Exception
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class MultipleResultsException extends DBException {
	public $previous;

	public function __construct($message = "Multiple results", $code = 404, Exception $previous = null) {
		DLOG("Multiple results. Reason: $code: $message");
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}

}
