<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Redirect Exception
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class RedirectException extends Exception {
	private $is_internal;

	public function __construct($location = null, $code = 302, $internal = false) {
		DLOG($location);
		$this->message = $location;
		$this->code = $code;
		$this->is_internal = $internal;
		DLOG($this->getTraceAsString());
	}

	public function is_internal() {
		DLOG(print_r($this->is_internal, true));
		return $this->is_internal;
	}

}
