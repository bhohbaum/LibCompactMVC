<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * XML to JSON
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class XmlToJson {

	public static function ParseURI($url) {
		$fileContents = file_get_contents($url);
		$fileContents = str_replace(array(
				"\n",
				"\r",
				"\t"
		), '', $fileContents);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents);
		$json = json_encode($simpleXml);
		return $json;
	}

	public static function ParseData($data) {
		$fileContents = $data;
		$fileContents = str_replace(array(
				"\n",
				"\r",
				"\t"
		), '', $fileContents);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents);
		$json = json_encode($simpleXml);
		return $json;
	}

}
