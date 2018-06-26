<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * SQL query builder.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class QueryBuilder extends DbAccess {
	private $td;

	/**
	 */
	public function __construct() {
		parent::__construct();
		$this->td = new TableDescription();
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function select($tablename, $constraint = array()) {
		$q = "SELECT * FROM `" . $tablename . "`";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= " WHERE " . $constraint->get_query_substring();
		} else {
			if (count($constraint) > 0) {
				$q .= " WHERE";
				$desc = $this->td->columninfo($tablename);
				foreach ($desc as $key => $val) {
					if (array_key_exists($val->Field, $constraint)) {
						$q .= " " . $val->Field . " " . $this->cmpissqlnull($this->escape($constraint[$val->Field])) . " " . $this->sqlnull($this->escape($constraint[$val->Field])) . " AND ";
					}
				}
				$q = substr($q, 0, strlen($q) - 5);
			}
		}
		DLOG($q);
		return $q;
	}
	
	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function like($tablename, $constraint = array()) {
		$q = "SELECT * FROM `" . $tablename . "`";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= " WHERE " . $constraint->get_query_substring();
		} else {
			if (count($constraint) > 0) {
				$q .= " WHERE";
				$desc = $this->td->columninfo($tablename);
				foreach ($desc as $key => $val) {
					if (array_key_exists($val->Field, $constraint)) {
						$q .= " " . $val->Field . " LIKE " . $this->sqlnull($this->escape($constraint[$val->Field]), true) . " AND ";
					}
				}
				$q = substr($q, 0, strlen($q) - 5);
			}
		}
		DLOG($q);
		return $q;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $fields
	 */
	public function insert($tablename, $fields) {
		$nofields = true;
		$desc = $this->td->columninfo($tablename);
		$q = "INSERT INTO `" . $tablename . "` (";
		foreach ($desc as $key => $val) {
			if (array_key_exists($val->Field, $fields)) {
				$q .= "`" . $val->Field . "`, ";
				$nofields = false;
			}
		}
		$q = substr($q, 0, strlen($q) - 2);
		if ($nofields) {
			return $q . " () VALUES ()";
		}
		$q .= ") VALUES (";
		foreach ($desc as $key => $val) {
			if (array_key_exists($val->Field, $fields)) {
				$q .= $this->sqlnull($this->escape($fields[$val->Field])) . ", ";
			}
		}
		$q = substr($q, 0, strlen($q) - 2);
		$q .= ")";
		DLOG($q);
		return $q;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $fields
	 * @param unknown_type $constraint
	 */
	public function update($tablename, $fields, $constraint = array()) {
		$desc = $this->td->columninfo($tablename);
		$q = "UPDATE `" . $tablename . "` SET ";
		foreach ($desc as $key => $val) {
			if (array_key_exists($val->Field, $fields)) {
				$q .= "`" . $val->Field . "` = " . $this->sqlnull($this->escape($fields[$val->Field])) . ", ";
			}
		}
		$q = substr($q, 0, strlen($q) - 2);
		$q .= " WHERE ";
		$noconstraint = true;
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= $constraint->get_query_substring();
			$noconstraint = false;
		} else {
			foreach ($desc as $key => $val) {
				if (array_key_exists($val->Field, $constraint)) {
					$noconstraint = false;
					$q .= $val->Field . " " . $this->cmpissqlnull($this->escape($constraint[$val->Field])) . " " . $this->sqlnull($this->escape($constraint[$val->Field])) . " AND ";
				}
			}
			$q = substr($q, 0, strlen($q) - 5);
		}
		if ($noconstraint) {
			throw new InvalidArgumentException("Constraint missing. Query: '" . $q . "'");
		}
		DLOG($q);
		return $q;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function delete($tablename, $constraint = array()) {
		$desc = $this->td->columninfo($tablename);
		$q = "DELETE FROM `" . $tablename . "` WHERE ";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= $constraint->get_query_substring();
		} else {
			foreach ($desc as $key => $val) {
				if (array_key_exists($val->Field, $constraint)) {
					$q .= $val->Field . " " . $this->cmpissqlnull($this->escape($constraint[$val->Field])) . " " . $this->sqlnull($this->escape($constraint[$val->Field])) . " AND ";
				}
			}
		}
		$q = substr($q, 0, strlen($q) - 5);
		DLOG($q);
		return $q;
	}

}
