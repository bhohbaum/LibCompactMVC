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
	private $__fk_resolution;
	private $__fk_obj_cache;
	
	/**
	 *
	 * @return string Endpoint URL for this DTO
	 */
	public function get_endpoint() {
		throw new DBException("get_endpoint() has to be implemented in all DTO classes! Method is missing in class '" . get_class($this) . "'.");
	}

	/**
	 * This method is called from the constructor when an object is created.
	 */
	protected function init() {
		DLOG();
		$tablename = $this->get_table();
		if ($tablename == null) $tablename = get_class($this);
		$this->table($tablename);
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
	 * @param array() $members:
	 *        	array or DbObject
	 * @param bool $isnew
	 */
	public function __construct($members = array(), $isnew = true) {
		parent::__construct();
		$this->__fk_resolution = true;
		$this->__fk_obj_cache = array();
		if (is_array($members))
			$this->__member_variables = $members;
		else if (is_object($members) && is_subclass_of($members, "DbObject"))
			$this->__member_variables = $members->to_array();
		$this->__tablename = null;
		$this->__isnew = $isnew;
		$this->__td = new TableDescription();
		$this->__type = get_class($this);
		$this->init();
		if (!$isnew)
			$this->on_after_load();
	}

	/**
	 *
	 * @param string $var_name
	 */
	public function __get($var_name) {
		if (!isset($this->__tablename) || $this->__tablename == "") {
			return (array_key_exists($var_name, $this->__member_variables)) ? $this->__member_variables[$var_name] : null;
		}
		$ret = null;
		if ($this->__fk_resolution) {
			if (array_key_exists($var_name, $this->__fk_obj_cache)) {
				$ret = $this->__fk_obj_cache[$var_name];
			} else {
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
						$ret = $this->run_query($q, true, true, null, $reftab, false);
						if (count($ret) == 1) {
							$ret = $ret[0];
						}
						$this->__fk_obj_cache[$var_name] = $ret;
					}
				}
			}
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
	 * @return DbObject
	 */
	public function __set($var_name, $value) {
		$this->__member_variables[$var_name] = $value;
		return $this;
	}

	/**
	 */
	public function jsonSerialize() {
		$ret = array();
		if (!isset($this->__fk_resolution))
			$this->__fk_resolution = true;
		if (!isset($this->__fk_obj_cache))
			$this->__fk_obj_cache = array();
		foreach ($this->__member_variables as $key => $val) {
			$val = $this->__get($key);
			$ret[$key] = (!is_string($val)) ? $val : UTF8::encode($val);
		}
		return $ret;
	}

	/**
	 *
	 * @param string $tablename
	 * @throws InvalidArgumentException
	 * @return DbObject
	 */
	public function table($tablename) {
		if ($this->__tablename != "" && isset($this->__tablename) && $this->__tablename != $tablename) {
			throw new InvalidArgumentException("Table can only be set once and can not be changed afterwards.");
		}
		$this->__tablename = $tablename;
		if ($tablename != "DbObject") {
			$pks = $this->__td->primary_keys($this->__tablename);
			$this->__pk = (count($pks) > 0) ? $pks[0] : null;
		}
		return $this;
	}

	/**
	 * Returns the name of the table this object operates on.
	 *
	 * @return string Table name
	 */
	public function get_table() {
		DLOG();
		return $this->__tablename;
	}

	/**
	 *
	 * @param array $constraint
	 * @return DbObject
	 */
	public function by($constraint = array()) {
		if (!isset($this->__tablename)) {
			throw new Exception("Invalid call: No table selected.");
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
	 * @return DbObject
	 */
	public function save() {
		$this->on_before_save();
		if (!isset($this->__tablename)) {
			throw new Exception("Invalid call: No table selected.");
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
			$this->by(array($pks[0] => $ret));
		}
		$this->__isnew = false;
		return $this;
	}

	/**
	 * Delete the current record
	 *
	 * @return DbObject
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
	 * Get all records from the table
	 *
	 * @return DbObject[]
	 */
	public function all() {
		DLOG();
		return $this->by_table($this->__tablename, array());
	}

	/**
	 * Get all records from the table where the given constraint matches
	 *
	 * @param
	 *        	array Constraint array
	 * @return DbObject[] Result records
	 */
	public function all_by($constraint = array()) {
		DLOG();
		return $this->by_table($this->__tablename, $constraint);
	}

	/**
	 * Get all records from the table where the given constraint matches
	 *
	 * @param
	 *        	array Constraint array
	 * @return DbObject[] Result records
	 */
	public function all_like($constraint = array()) {
		DLOG();
		return $this->by_table($this->__tablename, $constraint, true);
	}

	/**
	 * Convert this object to an array
	 *
	 * @return array()
	 */
	public function to_array() {
		return $this->__member_variables;
	}

	/**
	 * Enable/Disable foreign key resolution
	 *
	 * @param bool $enabled
	 */
	public function fk_resolution($enabled = true) {
		DLOG();
		if ($enabled !== true && $enabled !== false)
			throw new InvalidArgumentException("Boolean expected", 500);
		$this->__fk_resolution = $enabled;
	}

	/**
	 * Tells if the foreign key resolution is enabled or not
	 *
	 * @return bool
	 */
	public function fk_resolution_enabled() {
		DLOG();
		return $this->__fk_resolution;
	}

}

