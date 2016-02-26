<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Invalid Member Exception
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class InvalidMemberException extends Exception {

	public function __construct($message = "Invalid member", $code = null, $previous = null) {
		DLOG();
		$this->message = $message;
		$this->code = $code;
	}

}
