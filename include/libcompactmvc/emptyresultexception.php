<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Empty Result Exception
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class EmptyResultException extends DBException {
	public $previous;

	public function __construct($message = "Empty result", $code = 404, Exception $previous = null) {
		DLOG("Empty result. Reason: $code: $message");
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}

}
