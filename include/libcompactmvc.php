<?php
define('LIBCOMPACTMVC', true);
define('LIBCOMPACTMVC_ENTRY', defined('LIBCOMPACTMVC') || die);

function cpf_include($fname) {
	$basepath = dirname(dirname(__FILE__)."../");
	
	$dirs_up = array(
					"./",
					"../",
					"../../",
					"../../../",
					"../../../../",
					"../../../../../"
				);
	
	// Put all directories into this array, where source files shall be included.
	// This function is intended to work from everywhere.
	$dirs_down = array(
					"include/",
					"include/libcompactmvc/",
					"include/pages/"
				);
	
	foreach ($dirs_up as $u) {
		foreach ($dirs_down as $d) {
			// if directory of index.php or below
			$f = dirname($u.$d.$fname)."/".basename($u.$d.$fname);
//			if (strlen($f) != (strlen(str_replace($basepath, "", $f)))) {
				// and file exists
				if (file_exists($f)) {
					// include it once
					include_once($f);
				}
//			}
		}
	}
}

// first include the configuration
cpf_include('config.php');

if (defined(DEBUG) && (DEBUG == 0)) {
	ob_start();
}

// HTMLDesign Lib
cpf_include('actiondispatcher.php');
cpf_include('centermap.php');
cpf_include('dbaccess.php');
cpf_include('googlemaps.php');
cpf_include('htmlmail.php');
cpf_include('log.php');
cpf_include('map_radius.php');
cpf_include('network.php');
cpf_include('page.php');
cpf_include('session.php');
cpf_include('smtp.php');
cpf_include('socket.php');
cpf_include('upload.php');
cpf_include('utf8.php');
cpf_include('validator.php');
cpf_include('view.php');

// pages
cpf_include('ajax.php');
cpf_include('test.php');



if (defined(DEBUG) && (DEBUG == 0)) {
	ob_end_clean();
}
	

?>