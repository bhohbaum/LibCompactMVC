<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * FIFO Buffer Exception
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class FIFOBufferException extends Exception {

}
