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

	/**
	 * Overwrite this method to define the table that shall be operated on.
	 *
	 * @return String Table name to operate on
	 */
	abstract protected function get_table_name();

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
		$subject = new $table();
		$subject->by(array(
				$pk => $this->param(1)
		));
		$this->json_response($subject);
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
		$pk = $pk[0];
		$subject = new $table();
		if ($this->param(1) != "undefined")
			$subject->by(array(
					$pk => $this->param(1)
			));
		try {
			if (is_callable(array(
					$subject,
					$this->param(2)
			))) {
				$method = $this->param(2);
				try {
					$res = $subject->$method(json_decode($this->data, true));
				} catch (InvalidMemberException $e4) {
					$res = $subject->$method();
				}
				$this->json_response($res);
				return;
			} else {
				$subject->{$this->param(2)} = $this->data;
			}
		} catch (InvalidMemberException $e1) {
			DTOTool::copy($this, $subject);
			try {
				$subject->{$pk} = $this->{$pk};
			} catch (InvalidMemberException $e2) {
				$subject->{$pk} = $this->param(1);
			}
		}
		$subject->save();
		$this->json_response($subject);
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
		$subject = new $table();
		DTOTool::copy($this, $subject);
		try {
			$subject->{$pk} = $this->{$pk};
		} catch (InvalidMemberException $e2) {
			$subject->{$pk} = $this->param(1);
		}
		$subject->save();
		$this->json_response($subject);
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
		$subject = new $table();
		$subject->by(array(
				$pk => $this->param(1)
		));
		$subject->delete();
	}

}
