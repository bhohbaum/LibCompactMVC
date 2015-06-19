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
class DbObject extends DbAccess implements JsonSerializable {
	private $__member_variables;
	private $__tablename;
	private $__isnew;
	private $__td;

	/**
	 *
	 * @param array $members
	 * @param unknown_type $isnew
	 */
	public function __construct(array $members = array(), $isnew = true) {
		parent::__construct();
		$this->__member_variables = $members;
		$this->__tablename = null;
		$this->__isnew = $isnew;
		$this->__td = new TableDescription();
	}

	/**
	 *
	 * @param unknown_type $name
	 * @param unknown_type $args
	 */
	public function __call($name, $args) {
		if (is_callable($this->$name)) {
			array_unshift($args, $this);
			return call_user_func_array($this->$name, $args);
		}
	}

	/**
	 *
	 * @param unknown_type $var_name
	 */
	public function __get($var_name) {
		if (!isset($this->__tablename) || $this->__tablename == "") {
			return (array_key_exists($var_name, $this->__member_variables)) ? $this->__member_variables[$var_name] : null;
		}
		$ret = null;
		$this->__td = (isset($this->__td)) ? $this->__td : new TableDescription();
		$fks = $this->__td->fkinfo($this->__tablename);
		foreach ($fks as $fk) {
			$tmp = explode(".", $fk->fk);
			$column = $tmp[1];
			$tmp = explode(".", $fk->ref);
			$reftab = $tmp[0];
			$refcol = $tmp[1];
			if ($column == $var_name) {
				$qb = new QueryBuilder();
				$q = $qb->select($reftab, array($refcol => $this->__member_variables[$var_name]));
				$ret = DbAccess::get_instance(DBA_DEFAULT_CLASS)->run_query($q, true, true, null, $reftab);
			}
		}
		if (count($ret) == 1) {
			$ret = $ret[0];
		}
		if ($ret == null) {
			$ret = (array_key_exists($var_name, $this->__member_variables)) ? $this->__member_variables[$var_name] : null;
		}
		return $ret;
	}

	/**
	 *
	 * @param unknown_type $var_name
	 * @param unknown_type $value
	 */
	public function __set($var_name, $value) {
		$this->__member_variables[$var_name] = $value;
		return $this;
	}

	/**
	 *
	 */
	public function jsonSerialize() {
		$ret = array();
		foreach ($this->__member_variables as $key => $val) {
			$ret[$key] = $this->__get($key);
		}
		return $ret;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @throws InvalidArgumentException
	 */
	public function table($tablename) {
		if ($this->__tablename != "" && isset($this->__tablename) && $this->__tablename != $tablename) {
			throw new InvalidArgumentException("Table can only be set once and can not be changed afterwards.");
		}
		$this->__tablename = $tablename;
		return $this;
	}

	/**
	 * (non-PHPdoc)
	 * @see DbAccess::by()
	 */
	public function by($constraint = array()) {
		if (!isset($this->__tablename)) {
			throw new Exception("Unknown table. Cannot create DbObject.");
		}
		$constraint = ($constraint == null) ? array() : $constraint;
		$qb = new QueryBuilder();
		$q = $qb->select($this->__tablename, $constraint);
		$res = $this->run_query($q, false, false);
		if (!$res) {
			throw new EmptyResultException();
		}
		if (is_array($res)) {
			foreach ($res as $key => $val) {
				$this->$key = $val;
			}
		}
		$this->__isnew = false;
		return $this;
	}

	/**
	 *
	 * @throws Exception
	 */
	public function save() {
		if (!isset($this->__tablename)) {
			throw new Exception("Tablename must be set to be able to save new DbObjects to database.");
		}
		$cols = $this->__td->columns($this->__tablename);
		$pks = $this->__td->primary_keys($this->__tablename);
		$fields = array();
		foreach ($cols as $key => $val) {
			if (array_key_exists($val, $this->__member_variables)) {
				$fields[$val] = $this->__member_variables[$val];
			}
		}
		$qb = new QueryBuilder();
		if ($this->__isnew) {
			$q = $qb->insert($this->__tablename, $fields);
		} else {
			$constraint = array();
			foreach ($pks as $key => $val) {
				if (array_key_exists($val, $this->__member_variables)) {
					$constraint[$val] = $this->__member_variables[$val];
				}
			}
			$q = $qb->update($this->__tablename, $fields, $constraint);
		}
		$ret = $this->run_query($q);
		if ($this->__isnew && count($pks) == 1) {
			$this->__member_variables[$pks[0]] = $ret;
		}
		$this->__isnew = false;
		return $ret;
	}

	/**
	 *
	 */
	public function delete() {
		$pks = $this->__td->primary_keys($this->__tablename);
		$constraint = array();
		foreach ($pks as $key => $val) {
			if (array_key_exists($val, $this->__member_variables)) {
				$constraint[$val] = $this->__member_variables[$val];
			}
		}
		$qb = new QueryBuilder();
		$q = $qb->delete($this->__tablename, $constraint);
		$this->__isnew = true;
		return $this->run_query($q);
	}

	/**
	 *
	 */
	public function to_array() {
		return $this->__member_variables;
	}

}

