<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Database Exception
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Media Impression Unit 08
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class DBException extends Exception {
	public $previous;

	public function __construct($message = null, $code = null, Exception $previous = null) {
		DLOG("DB Exception $code: $message");
		parent::__construct($message, $code, $previous);
		$this->previous = $previous;
	}

}
