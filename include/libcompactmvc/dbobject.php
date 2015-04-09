<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Generic database object.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class DbObject extends DbAccess {
	private $__tablename;
	private $__isnew;

	public function __construct(array $members = array()) {
		parent::__construct();
		foreach ($members as $name => $value) {
			$this->$name = $value;
		}
		$this->__tablename = null;
		$this->__isnew = true;
	}

	public function __call($name, $args) {
		if (is_callable($this->$name)) {
			array_unshift($args, $this);
			return call_user_func_array($this->$name, $args);
		}
	}

	public function table($tablename) {
		if ($this->__tablename != "" && isset($this->__tablename) && $this->__tablename != $tablename) {
			throw new InvalidArgumentException("Table cannot be changed on existing DbObject.");
		}
		$this->__tablename = $tablename;
		return $this;
	}

	public function by($constraint) {
		if (!isset($this->__tablename)) {
			throw new Exception("Unknown table. Cannot create DbObject.");
		}
		$qb = new QueryBuilder();
		$q = $qb->select($this->__tablename, $constraint);
		$res = $this->run_query($q, false, false);
		foreach ($res as $key => $val) {
			$this->$key = $val;
		}
		$this->__isnew = false;
		return $this;
	}

	public function save() {
		if (!isset($this->__tablename)) {
			throw new Exception("Tablename must be set to be able to save new DbObjects to database.");
		}
		$td = new TableDescription();
		$cols = $td->columns($this->__tablename);
		$fields = array();
		foreach ($cols as $key => $val) {
			$fields[$val] = $this->$val;
		}
		$qb = new QueryBuilder();
		if ($this->__isnew) {
			$q = $qb->insert($this->__tablename, $fields);
		} else {
			$pks = $td->primary_keys($this->__tablename);
			$constraint = array();
			foreach ($pks as $key => $val) {
				$constraint[$val] = $this->$val;
			}
			$q = $qb->update($this->__tablename, $fields, $constraint);
		}
		return $this->run_query($q);
	}

	public function delete() {
		$td = new TableDescription();
		$pks = $td->primary_keys($this->__tablename);
		$constraint = array();
		foreach ($pks as $key => $val) {
			$constraint[$val] = $this->$val;
		}
		$qb = new QueryBuilder();
		$q = $qb->delete($this->__tablename, $constraint);
		return $this->run_query($q);
	}

}

