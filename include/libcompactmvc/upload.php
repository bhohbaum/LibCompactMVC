<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Upload helper
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class Upload {
	private $ul_path;
	private $files_arr;
	private $names_arr;

	public function __construct($path) {
		DLOG();
		$this->ul_path = $path;
		$this->files_arr = $_FILES;
	}

	/**
	 * Saves one ore more files in the upload directory.
	 *
	 * @return array	file names
	 * @throws Exception
	 */
	public function save() {
		DLOG();
		$ret = array();
		$i = 0;
		foreach ($this->files_arr as $key => $value) {
			$this->names_arr[$i++] = $key;
			if ($value['name'] != "") {
				if (!move_uploaded_file($value['tmp_name'], $this->ul_path . "/" . $value['name'])) {
					throw new Exception("Cannot write to upload directory: '" . $this->ul_path . "/" . $value['name'] . "'");
				}
				chmod($this->ul_path . "/" . $value['name'], 0666);
				$ret[] = $this->ul_path . "/" . $value['name'];
			}
		}
		return $ret;
	}

	/**
	 * Saves one or more files in corresponding subdirectories of the upload directory.
	 *
	 * @return array	file names
	 * @throws Exception
	 */
	public function save_sub() {
		DLOG();
		$ret = array();
		foreach ($this->files_arr as $key => $value) {
			if ($value['name'] != "") {
				if (!move_uploaded_file($value['tmp_name'], $this->ul_path . "/" . $key . "/" . $value['name'])) {
					throw new Exception("Cannot write to upload directory: '" . $this->ul_path . "/" . $key . "/" . $value['name'] . "'");
				}
				chmod($this->ul_path . "/" . $key . "/" . $value['name'], 0666);
				$ret[$key][] = $this->ul_path . "/" . $key . "/" . $value['name'];
			}
		}
		return $ret;
	}

	public function get_param_name($index) {
		DLOG();
		if (!isset($index)) {
			return $this->names_arr;
		}
		return $this->names_arr[$index];
	}


}
