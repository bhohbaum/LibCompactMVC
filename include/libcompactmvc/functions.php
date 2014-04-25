<?php

/**
 * Global functions
 *
 * @author		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package	LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 24.01.2012
 * @license	LGPL version 3
 * @link		https://github.com/bhohbaum/libcompactmvc
 */

/**
 * prints the current stack trace
 */
function printStackTrace() {
	try {
		throw new Exception("", 0);
	} catch (Exception $e) {
		echo ("<pre>" . $e->getTraceAsString() . "</pre>");
	}
}

/*
 * The XLOG() functions can be used in all controller classes. PHP doesn't know c-like macros. hence we use the debug_backtrace() trick to get the callers object.
 */
function ELOG($msg) {
	$stack = debug_backtrace();
	$stack[1]["object"]->log->error($msg);
}

function WLOG($msg) {
	$stack = debug_backtrace();
	$stack[1]["object"]->log->warning($msg);
}

function NLOG($msg) {
	$stack = debug_backtrace();
	$stack[1]["object"]->log->notice($msg);
}

function DLOG($msg) {
	$stack = debug_backtrace();
	$stack[1]["object"]->log->debug($msg);
}

?>