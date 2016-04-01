<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Global functions
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
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

function is_windows() {
	DLOG();
	if (strtoupper(substr(PHP_OS, 0, 3)) == "WIN") {
		return true;
	} else {
		return false;
	}
}

function mkpw($length = 9, $add_dashes = false, $available_sets = 'luds') {
	$sets = array();
	if (strpos($available_sets, 'l') !== false)
		$sets[] = 'abcdefghjkmnpqrstuvwxyz';
	if (strpos($available_sets, 'u') !== false)
		$sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
	if (strpos($available_sets, 'd') !== false)
		$sets[] = '23456789';
	if (strpos($available_sets, 's') !== false)
		$sets[] = '!@#$%&*?';
	$all = '';
	$password = '';
	foreach ($sets as $set) {
		$password .= $set[array_rand(str_split($set))];
		$all .= $set;
	}
	$all = str_split($all);
	for($i = 0; $i < $length - count($sets); $i++)
		$password .= $all[array_rand($all)];
	$password = str_shuffle($password);
	if (!$add_dashes)
		return $password;
	$dash_len = floor(sqrt($length));
	$dash_str = '';
	while (strlen($password) > $dash_len) {
		$dash_str .= substr($password, 0, $dash_len) . '-';
		$password = substr($password, $dash_len);
	}
	$dash_str .= $password;
	return $dash_str;
}

function strip_tags_and_attributes($html, $tags, $attributes = array()) {
	// Get array representations of the safe tags and attributes:
	// Parse the HTML into a document object:
	$dom = new DOMDocument();
	$dom->loadHTML('<div>' . $html . '</div>');

	// Loop through all of the nodes:
	$stack = new SplStack();
	$stack->push($dom->documentElement);

	while ($stack->count() > 0) {
		// Get the next element for processing:
		$element = $stack->pop();

		// Add all the element's child nodes to the stack:
		foreach ($element->childNodes as $child) {
			if ($child instanceof DOMElement) {
				$stack->push($child);
			}
		}

		// And now, we do the filtering:
		if (in_array(strtolower($element->nodeName), $tags)) {
			// It's an unwanted tag; unwrap it:
			while ($element->hasChildNodes()) {
				$element->parentNode->insertBefore($element->firstChild, $element);
			}

			// Finally, delete the offending element:
			$element->parentNode->removeChild($element);
		} else {
			// The tag is safe; now filter its attributes:
			for($i = 0; $i < $element->attributes->length; $i++) {
				$attribute = $element->attributes->item($i);
				$name = strtolower($attribute->name);

				if (in_array($name, $attributes)) {
					// Found an unsafe attribute; remove it:
					$element->removeAttribute($attribute->name);
					$i--;
				}
			}
		}
	}

	$html = $dom->saveHTML();
	$start = strpos($html, '<div>');
	$end = strrpos($html, '</div>');

	return substr($html, $start + 5, $end - $start - 5);
}

