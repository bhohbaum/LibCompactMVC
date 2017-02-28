<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Generic database object.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class DbObject extends DbAccess implements JsonSerializable {
	private $__member_variables;
	private $__tablename;
	private $__isnew;
	private $__td;

	/**
	 * This method is called from the constructor when an object is created.
	 */
	protected function init() {
		DLOG();
	}

	/**
	 * This method is called before a save operation.
	 */
	protected function on_before_save() {
		DLOG();
	}

	/**
	 * This method is called after a load operation.
	 */
	protected function on_after_load() {
		DLOG();
	}

	/**
	 *
	 * @param unknown_type $members:
	 *        	array or DbObject
	 * @param unknown_type $isnew
	 */
	public function __construct($members = array(), $isnew = true) {
		parent::__construct();
		if (is_array($members))
			$this->__member_variables = $members;
		else if (is_object($members) && is_subclass_of($members, "DbObject"))
			$this->__member_variables = $members->to_array();
		$this->__tablename = null;
		$this->__isnew = $isnew;
		$this->__td = new TableDescription();
		$this->init();
		if (!$isnew)
			$this->on_after_load();
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
				$q = $qb->select($reftab, array(
						$refcol => $this->__member_variables[$var_name]
				));
				$ret = DbAccess::get_instance(DBA_DEFAULT_CLASS)->run_query($q, true, true, null, $reftab, false);
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
	 */
	public function jsonSerialize() {
		$ret = array();
		foreach ($this->__member_variables as $key => $val) {
			$val = $this->__get($key);
			$ret[$key] = (!is_string($val)) ? $val : UTF8::encode($val);
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
	 *
	 * @see DbAccess::by()
	 */
	public function by($constraint = array()) {
		if (!isset($this->__tablename)) {
			throw new Exception("Unknown table. Query can not be built .");
		}
		$constraint = ($constraint == null) ? array() : $constraint;
		$qb = new QueryBuilder();
		$q = $qb->select($this->__tablename, $constraint);
		$res = $this->run_query($q, false, false, null, $this->__tablename, false);
		if (!$res) {
			throw new EmptyResultException("Query: " . $q);
		}
		if (is_array($res)) {
			foreach ($res as $key => $val) {
				$this->$key = $val;
			}
		}
		$this->__isnew = false;
		$this->on_after_load();
		return $this;
	}

	/**
	 *
	 * @throws Exception
	 */
	public function save() {
		$this->on_before_save();
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
		$ret = $this->run_query($q, false, false, null, $this->__tablename, true);
		if ($this->__isnew && count($pks) == 1) {
			$this->__member_variables[$pks[0]] = $ret;
		}
		$this->__isnew = false;
		return $this;
	}

	/**
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
		$this->__member_variables = array();
		$this->run_query($q, false, false, null, $this->__tablename, true);
		return $this;
	}

	/**
	 */
	public function to_array() {
		return $this->__member_variables;
	}

}

