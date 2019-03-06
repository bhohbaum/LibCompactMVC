<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * CRUD Component super class
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
abstract class CMVCCRUDComponent extends CMVCComponent {
	private $__m_subject;
	private $__m_object;
	private $__m_response;
	private $__m_called_method;
	
	protected function get_subject() {
		return $this->__m_subject;
	}
	
	protected function get_object() {
		return $this->__m_object;
	}
	
	protected function get_response() {
		return $this->__m_response;
	}

	protected function get_called_method() {
		return $this->__m_called_method;
	}

	/**
	 * Overwrite this method to define the table that shall be operated on.
	 *
	 * @return String Table name to operate on
	 */
	protected function get_table_name() {
		DLOG();
		$td = new TableDescription();
		$found = false;
		foreach ($td->get_all_tables() as $table) {
			if ($table == $this->get_component_id()) {
				$found = true;
				break;
			}
		}
		if (!$found)
			throw new Exception("Table does not exist: " . $this->get_component_id(), 500);
		return $this->get_component_id();
	}
	
	protected function json_response($obj) {
		DLOG();
		$this->__m_response = $obj;
		parent::json_response($obj);
	}
		

	/**
	 * Get record by id
	 */
	protected function main_run_get() {
		DLOG();
		parent::main_run_get();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = $pk[0];
		$this->__m_subject = new $table();
		$this->__m_subject->by(array(
				$pk => $this->param(1)
		));
		$this->json_response($this->__m_subject);
	}

	/**
	 * Update record, call ORM methods
	 */
	protected function main_run_post() {
		DLOG();
		parent::main_run_post();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = (is_array($pk) && count($pk) > 0) ? $pk[0] : "id";
		$this->__m_subject = new $table();
		if ($this->param(1) != "undefined") {
			$this->__m_subject->by(array(
					$pk => $this->param(1)
			));
		}
		DTOTool::copy($this, $this->__m_subject);
		try {
			$subject = json_decode($this->__subject, false);
			DTOTool::copy($subject, $this->__m_subject);
		} catch (InvalidMemberException $e5) {
		}
		try {
			if (is_callable(array(
					$this->__m_subject,
					$this->param(2)
			))) {
				$method = $this->param(2);
				try {
					$param = null;
					$this->__m_object = json_decode($this->__object, true);
					if (is_array($this->__m_object) && array_key_exists("__type", $this->__m_object)) {
						$pclass = $this->__m_object["__type"];
						if (class_exists($pclass)) {
							if ($this->__m_object["__type"] == "DbConstraint") {
								$param = DbConstraint::create_from_json($this->__object);
							} else {
								$param = new $pclass;
								$data = json_decode($this->__object);
								DTOTool::copy($data, $param);
							}
						}
					}
					if ($param == null) {
						$param = json_decode($this->__object, true);
					}
					$res = $this->__m_subject->$method($param);
				} catch (InvalidMemberException $e4) {
					$res = $this->__m_subject->$method();
				}
				$this->__m_called_method = $method;
				$this->json_response($res);
				return;
			} else {
				if ($this->param(2) == null)
					throw new InvalidMemberException('$this->param(2) is null, doing full copy...');
				$this->__m_subject->{$this->param(2)} = $this->__object;
				$this->__m_object = $this->__object;
			}
		} catch (InvalidMemberException $e1) {
			try {
				$this->__m_subject->{$pk} = $this->{$pk};
			} catch (InvalidMemberException $e2) {
				try {
					$this->__m_subject->{$pk} = $this->param(1);
				} catch (InvalidMemberException $e6) {
				}
			}
		}
		$this->__m_subject->save();
		$this->json_response($this->__m_subject);
	}

	/**
	 * Create new record
	 */
	protected function main_run_put() {
		DLOG();
		parent::main_run_put();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = $pk[0];
		$this->__m_subject = new $table();
		DTOTool::copy($this, $this->__m_subject);
		try {
			$subject = json_decode($this->__subject, false);
			DTOTool::copy($subject, $this->__m_subject);
		} catch (InvalidMemberException $e5) {
		}
		try {
			$this->__m_subject->{$pk} = $this->{$pk};
		} catch (InvalidMemberException $e2) {
			try {
				$this->__m_subject->{$pk} = $this->param(1);
			} catch (InvalidMemberException $e) {
				unset($this->__m_subject->{$pk});
			}
		}
		$this->__m_subject->save();
		$this->json_response($this->__m_subject);
	}

	/**
	 * Delete record
	 */
	protected function main_run_delete() {
		DLOG();
		parent::main_run_delete();
		$table = $this->get_table_name();
		$td = new TableDescription();
		$pk = $td->primary_keys($table);
		$pk = $pk[0];
		$this->__m_subject = new $table();
		$this->__m_subject->by(array(
				$pk => $this->param(1)
		));
		$this->__m_subject->delete();
	}
	
	/**
	 * Do not print stack trace in API environment, catch and return exception json-serialized instead.
	 *
	 * {@inheritdoc}
	 * @see CMVCController::exception_handler()
	 */
	protected function exception_handler(Exception $e) {
		DLOG(print_r($e, true));
		$this->json_response(array(
				"message" => $e->getMessage(),
				"trace" => $e->getTrace(), 
				"code" => $e->getCode()
		));
	}
	
}
