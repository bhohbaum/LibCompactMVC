<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * orm.php
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Media Impression Unit 08
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class ORMClientComponent extends CMVCComponent {
	
	protected function get_component_id() {
		DLOG();
		return "ormclientcomponent";
	}

	protected function main_run() {
		DLOG();
		parent::main_run();
		$td = new TableDescription();
		$tables = $td->get_all_tables();
		$this->get_view()->set_value("tables", $tables);
		foreach ($tables as $table) {
			if (!class_exists($table))
				throw new DBException("Missing DTO class: " . $table);
			$subject = new $table();
			if (!is_subclass_of($subject, "DbObject"))
				throw new DBException("Class " . $table . " must be derived from DbObject.");
			$class = new ReflectionClass($table);
			$methods = array();
			foreach ($class->getMethods() as $method) {
				if ($method->class == $table && 
					$method->name != "get_endpoint" &&
					$method->name != "init" &&
					$method->name != "on_after_load" &&
					$method->name != "on_before_save" &&
					$method->name != "save" &&
					$method->name != "delete" &&
					$method->name != "jsonSerialize")
					$methods[] = $method->name;
			}
			$this->get_view()->set_value("methods_" . $table, $methods);
			$this->get_view()->set_value("endpoint_" . $table, $subject->get_endpoint());
			foreach ($class->getMethods() as $method) {
				if ($method->class == $table)
					$this->get_view()->set_value("method_" . $table. "::" . $method->name, count($method->getParameters()) > 0);
			}
		}
		$this->get_view()->set_template(0, "__ormclient.tpl");
		$this->set_mime_type(MIME_TYPE_JS);
	}

}
