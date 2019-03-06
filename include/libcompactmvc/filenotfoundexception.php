<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * File not found Exception
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Media Impression Unit 08
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FileNotFoundException extends Exception {
	public $previous;

	public function __construct($filename = "", $code = 404, Exception $previous = null) {
		$this->message = "File not found: $filename";
		DLOG($this->message);
		$this->code = $code;
		$this->previous = $previous;
	}
	
}
