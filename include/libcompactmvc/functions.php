<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Global functions
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */

/*
 * Filesystem helper
 */
function rrmdir($path, $ignore = array()) {
	DLOG();
	foreach ($ignore as $i) {
		if (pathinfo($path, PATHINFO_BASENAME) == $i) {
			DLOG(__METHOD__ . " " . $path . " is on ignore list, leaving it undeleted...\n");
			return;
		}
	}
	if (is_dir($path)) {
		$path = rtrim($path, '/') . '/';
		$items = glob($path . '*');
		foreach ($items as $item) {
			is_dir($item) ? rrmdir($item, $ignore) : unlink($item);
		}
		rmdir($path);
	} else {
		unlink($path);
	}
}

function is_windows(){
	DLOG();
	if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
		return true;
	} else {
		return false;
	}
}
