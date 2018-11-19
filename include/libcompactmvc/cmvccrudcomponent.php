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
	private $subject;
	private $response;
	private $called_method;
	
	protected function get_subject() {
		return $this->subject;
	}
	
	protected function get_response() {
		return $this->response;
	}

	protected function get_called_method() {
		return $this->called_method;
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
		$this->response = $obj;
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
		$this->subject = new $table();
		$this->subject->by(array(
				$pk => $this->param(1)
		));
		$this->json_response($this->subject);
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
		$this->subject = new $table();
		if ($this->param(1) != "undefined") {
			$this->subject->by(array(
					$pk => $this->param(1)
			));
		}
		try {
			$subject = json_decode($this->__subject, false);
			DTOTool::copy($subject, $this->subject);
		} catch (InvalidMemberException $e5) {
// 			WLOG("Unable to populate subject. Neither id to load subject from DB nor member data from client where provided.");
		}
		try {
			if (is_callable(array(
					$this->subject,
					$this->param(2)
			))) {
				$method = $this->param(2);
				try {
					$param = null;
					$tmp = json_decode($this->data, true);
					if (is_array($tmp) && array_key_exists("__type", $tmp)) {
						$pclass = $tmp["__type"];
						if (class_exists($pclass)) {
							if ($tmp["__type"] == "DbConstraint") {
								$param = DbConstraint::create_from_json($this->data);
							} else {
								$param = new $pclass;
								$data = json_decode($this->data);
								DTOTool::copy($data, $param);
							}
						}
					}
					if ($param == null) {
						$param = json_decode($this->data, true);
					}
					$res = $this->subject->$method($param);
				} catch (InvalidMemberException $e4) {
					$res = $this->subject->$method();
				}
				$this->called_method = $method;
				$this->json_response($res);
				return;
			} else {
				if ($this->param(2) == null)
					throw new InvalidMemberException('$this->param(2) is null, doing full copy...');
				$this->subject->{$this->param(2)} = $this->data;
			}
		} catch (InvalidMemberException $e1) {
			DTOTool::copy($this, $this->subject);
			try {
				$this->subject->{$pk} = $this->{$pk};
			} catch (InvalidMemberException $e2) {
				try {
					$this->subject->{$pk} = $this->param(1);
				} catch (InvalidMemberException $e6) {
				}
			}
		}
		$this->subject->save();
		$this->json_response($this->subject);
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
		$this->subject = new $table();
		DTOTool::copy($this, $this->subject);
		try {
			$this->subject->{$pk} = $this->{$pk};
		} catch (InvalidMemberException $e2) {
			try {
				$this->subject->{$pk} = $this->param(1);
			} catch (InvalidMemberException $e) {
				unset($this->subject->{$pk});
			}
		}
		$this->subject->save();
		$this->json_response($this->subject);
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
		$this->subject = new $table();
		$this->subject->by(array(
				$pk => $this->param(1)
		));
		$this->subject->delete();
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
