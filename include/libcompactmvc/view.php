<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

// This class is used for template handling.
// It loads the templates, fills them with values and generates the output
// into a buffer that can be retrieved.

/**
 * Template handling
 *
 * @author      Botho Hohbaum <bhohbaum@googlemail.com>
 * @package     LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum 11.02.2014
 * @link		http://www.adrodev.de
 */
class View {

	private $comp;
	private $vals;
	private $tpls; 

	public function __construct() {

	}

	public function activate($comp_name) {
		$this->comp[$comp_name] = true;
	}

	public function deactivate($comp_name) {
		$this->comp[$comp_name] = false;
	}

	public function is_active($comp_name) {
		if (isset($this->comp[$comp_name])) {
			return $this->comp[$comp_name];
		} else {
			return false;
		}
	}

	public function set_value($key, $value) {
		$this->vals[$key] = $value;
	}

	public function get_value($key) {
		if (isset($this->vals[$key])) {
			return $this->vals[$key];
		} else {
			return "";
		}
	}
	
	public function set_template($index, $name) {
		$this->tpls[$index] = $name;
	}
	
	public function add_template($name) {
		$this->tpls[] = $name;
	}
	
	public function get_templates() {
		return $this->tpls;
	}
	
	public function clear() {
		$this->comp = array();
		$this->vals = array();
		$this->tpls = array();
	}
	
	public function render() {
		$out = "";
		if (DEBUG == 0) {
			@ob_end_clean();
		}
		ob_start();
		if (count($this->tpls) > 0) {
			foreach ($this->tpls as $t) {
				$file = "./templates/".$t;
				if (file_exists($file)) {
					if (DEBUG == 0) {
						@include($file);
					} else {
						include($file);
					}
				} else {
					ob_end_clean();
					die("Could not find template file: ".$file);
				}
			}
		}
		$out = ob_get_contents();
		ob_end_clean();
		if (DEBUG == 0) {
			@ob_start();
		}
		return $out;
	}
	
	private function include_template($tpl_name) {
		$file = "./templates/".$tpl_name;
		if (file_exists($file)) {
			include($file);
		} else {
			die("Could not find template file: ".$file);
		}
	}
	
	
	
}

?>