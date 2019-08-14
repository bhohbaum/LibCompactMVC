<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * cmvcsystem.php
 *
 * @author		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class CMVCSystem extends CMVCComponent {
	private $bridf = "./include/resources/config/orm_ep_base_route_id.txt";
	
	protected function get_component_id() {
		DLOG();
		return "sys";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		try {
			$this->dispatch_method($this->path(1));
		} catch (InvalidMemberException $e) {
			$this->get_view()->activate("main");
			$this->get_view()->add_template("__syshelp.tpl");
		}
	}
	
	protected function exec_gendto($is_second = false) {
		DLOG();
		$changed = false;
		if (!file_exists($this->bridf)) {
			$brid = readline("Please enter the base route id for where the endpoints are located: ");
			echo_flush("\n");
			file_put_contents($this->bridf, $brid);
		}
		$brid = file_get_contents($this->bridf);
		$bridarr = explode(".", $brid);
		$briddepth = count($bridarr);
		$view = new View();
		$view->add_template("__dtotemplate.tpl");
		$view->set_value("brid", $brid);
		$view->set_value("bridarr", $bridarr);
		$view->set_value("briddepth", $briddepth);
		$td = new TableDescription();
		$tables = $td->get_all_tables();
		$addtables = array();
		foreach ($tables as $table) {
			if (!class_exists($table)) {
				echo_flush("No DTO class found for table: " . $table . "\n");
				$fname = "./application/dba/" . $table . ".php";
				if (file_exists($fname)) {
					echo_flush("...but file exists: " . $fname . "\n");
					echo_flush("\nSituation must be resolved manually! Exiting...\n\n");
					return;
				}
				$view->set_value("table", $table);
				$code = $view->render(false);
				echo_flush("Writing skeleton class to file: " . $fname . "\n");
				file_put_contents($fname, $code);
				$changed = true;
				$addtables[] = $table;
			}
		}
		$this->get_view()->activate("newrouting");
		$this->get_view()->set_value("tables", $addtables);
		$this->get_view()->set_value("bridarr", $bridarr);
		$this->get_view()->set_value("briddepth", $briddepth);
		$this->get_view()->add_template("__syshelp.tpl");
		$quit = false;
		if (!$is_second) {
			while ($changed && !$quit) {
				echo_flush("\n");
				$response = readline("One or more DTO classes where added, do you also want to setup the corresponding endpoints? (yes/no) ");
				echo_flush("\n");
				if (str_contains(strtolower($response), "y")) {
					$this->exec_genep(true);
					$quit = true;
				}
				if (str_contains(strtolower($response), "n")) {
					$quit = true;
				}
				if (!$quit) echo_flush("Invalid input!\n");
			}
		}
	}
	
	protected function exec_genep($is_second = false) {
		DLOG();
		$changed = false;
		$view = new View();
		$view->add_template("__dtoeptemplate.tpl");
		$td = new TableDescription();
		$tables = $td->get_all_tables();
		$addtables = array();
		foreach ($tables as $table) {
			if (!class_exists("EP" . $table)) {
				echo_flush("No endpoint class found for table: " . $table . "\n");
				$fname = "./application/component/ep" . $table . ".php";
				if (file_exists($fname)) {
					echo_flush("...but file exists: " . $fname . "\n");
					echo_flush("\nSituation must be resolved manually! Exiting...\n\n");
					return;
				}
				$view->set_value("table", $table);
				$code = $view->render(false);
				echo_flush("Writing skeleton class to file: " . $fname . "\n");
				file_put_contents($fname, $code);
				$changed = true;
				$addtables[] = $table;
			}
		}
		$quit = false;
		if (!$is_second) {
			while ($changed && !$quit) {
				echo_flush("\n");
				$response = readline("One or more endpoint classes where added, do you also want to setup the DTOs? (yes/no) ");
				echo_flush("\n");
				if (str_contains(strtolower($response), "y")) {
					$this->exec_gendto(true);
					$quit = true;
				}
				if (str_contains(strtolower($response), "n")) {
					$quit = true;
				}
				if (!$quit) echo_flush("Invalid input!\n");
			}
		}
	}

}
