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
	
	private function selcols($tablename, $constraint) {
		$selcols = "*";
		if (!is_array($constraint)) {
			if (is_array($this->td->primary_keys($tablename)) && get_class($constraint) == "DbConstraint") {
				if ($constraint->get_query_info()["count"]) {
					$selcols = "COUNT(" . $this->td->primary_keys($tablename)[0] . ") AS count";
				} else {
					$selcols = "*";
				}
			}
		}
		return $selcols;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 * @param unknown_type $constraint
	 */
	public function select($tablename, $constraint = array()) {
		$q = "SELECT " . $this->selcols($tablename, $constraint) . " FROM `" . $tablename . "`";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= " WHERE " . $constraint->get_query_info()["where_string"];
		} else {
			if (count($constraint) > 0) {
				$q .= " WHERE ";
				$q .= $this->where_substring($tablename, $constraint);
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
		$q = "SELECT " . $this->selcols($tablename, $constraint) . " FROM `" . $tablename . "`";
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= " WHERE " . $constraint->get_query_info()["where_string"];
		} else {
			if (count($constraint) > 0) {
				$q .= " WHERE ";
				$q .= $this->where_substring($tablename, $constraint, array(), DbFilter::COMPARE_LIKE);
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
		if (!is_array($constraint) && get_class($constraint) == "DbConstraint") {
			$q .= $constraint->get_query_info()["where_string"];
		} else {
			$q .= $this->where_substring($tablename, $constraint);
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
			$q .= $constraint->get_query_info()["where_string"];
		} else {
			$q .= $this->where_substring($tablename, $constraint);
		}
		DLOG($q);
		return $q;
	}
	
	/**
	 * 
	 * @param array $constraint
	 * @param array $filter
	 * @param unknown $comparator
	 * @param unknown $logic_op
	 * @return string
	 */
	public function where_substring($table = null, $constraint = array(), $filter = array(), $comparator = DbFilter::COMPARE_EQUAL, $logic_op = DbFilter::LOGIC_OPERATOR_AND) {
		if ($table == null) return "1";
		if (count($constraint) == 0 && count($filter) == 0) return "1";
		$desc = $this->td->columninfo($table);
		$first = true;
		$qstr1 = "(";
		foreach ($constraint as $col => $val) {
			foreach ($desc as $k => $v) {
				if ($v->Field == $col) {
					if (!$first) $qstr1 .= $logic_op . " ";
					$first = false;
					if ($comparator == DbFilter::COMPARE_IN || $comparator == DbFilter::COMPARE_NOT_IN) {
						if (!is_array($val)) throw new DBException("IN comparator requires array(s) as column filter.");
						$first2 = true;
						$qstr2 = "(";
						foreach ($val as $k2 => $v2) {
							if (!$first2) $qstr2 .= ", ";
							$first2 = false;
							$qstr2 .= $this->sqlnull($this->escape($v2));
						}
						$qstr2 .= ")";
						$qstr1 .= "`" . $col . "` " . $comparator . " " . $qstr2 . " ";
					} else {
						$qstr1 .= "`" . $col . "` " . $this->comparator($comparator, $val) . " " . $this->sqlnull($this->escape($val)) . " ";
					}
				}
			}
		}
		$qstr2 = "";
		foreach ($filter as $filter) {
			if (!$first) $qstr2 .= $logic_op . " ";
			$first = false;
			$qstr2 .= $filter->get_query_substring();
		}
		$qstr2 .= ")";
		$qstr = $qstr1 . $qstr2;
		DLOG($qstr);
		return $qstr;
	}
	
	/**
	 *
	 * @param unknown $val
	 * @return string|unknown
	 */
	protected function comparator($comparator, $val) {
		if ($comparator == DbFilter::COMPARE_EQUAL)
			return $this->cmpissqlnull($val);
		else if ($comparator == DbFilter::COMPARE_NOT_EQUAL)
			return $this->cmpisnotsqlnull($val);
		else
			return $comparator;
	}
	

}

