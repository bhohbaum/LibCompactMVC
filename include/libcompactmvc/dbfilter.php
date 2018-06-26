<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Query filter definition.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class DbFilter extends DbAccess implements JsonSerializable {
	protected $filter = array();
	protected $comparator = array();
	protected $logic_op = array();
	protected $constraint = array();
	
	public const LOGIC_OPERATOR_AND = "AND";
	public const LOGIC_OPERATOR_OR = "OR";
	public const LOGIC_OPERATOR_XOR = "XOR";
	public const LOGIC_OPERATOR_NOT = "NOT";
	
	public const COMPARE_EQUAL = "=";
	public const COMPARE_NOT_EQUAL = "!=";
	public const COMPARE_LIKE = "LIKE";
	public const COMPARE_NOT_LIKE = "NOT LIKE";
	public const COMPARE_GREATER_THAN = ">";
	public const COMPARE_SMALLER_THAN = "<";
	
	/**
	 * 
	 * @param array $constraint
	 */
	public function __construct($constraint = array()) {
		DLOG(print_r($constraint, true));
		$this->constraint = $constraint;
		$this->filter = array();
	}
	
	/**
	 * 
	 * @param DbFilter $filter
	 * @return DbFilter
	 */
	public function add_filter(DbFilter $filter) {
		DLOG();
		$this->filter[] = $filter;
		return $this;
	}

	/**
	 * 
	 * @param unknown $column
	 * @param unknown $value
	 * @return DbFilter
	 */
	public function set_column_filter($column, $value) {
		DLOG();
		$this->constraint[$column] = $value;
		return $this;
	}
	
	/**
	 * 
	 * @param unknown $logic_op
	 * @return DbFilter
	 */
	public function set_logical_operator($logic_op) {
		DLOG();
		$this->logic_op = $logic_op;
		return $this;
	}
	
	/**
	 * 
	 * @param unknown $comparator
	 * @return DbFilter
	 */
	public function set_comparator($comparator) {
		DLOG();
		$this->comparator = $comparator;
		return $this;
	}
	
	/**
	 * 
	 * @return string
	 */
	public function get_query_substring() {
		DLOG();
		$first = true;
		$qstr = "(";
		foreach ($this->constraint as $col => $val) {
			if (!$first) $qstr .= $this->logic_op . " ";
			$first = false;
			$qstr .= "`" . $col . "` " . $this->comparator($val) . " " . $this->sqlnull($this->escape($val)) . " ";
		}
		foreach ($this->filter as $filter) {
			if (!$first) $qstr .= $this->logic_op . " ";
			$first = false;
			$qstr .= $filter->get_query_substring();
		}
		$qstr .= ")";
		return $qstr;
	}
	
	/**
	 * 
	 * @param unknown $val
	 * @return string|unknown
	 */
	protected function comparator($val) {
		if ($this->comparator == DbFilter::COMPARE_EQUAL)
			return $this->cmpissqlnull($val);
		else if ($this->comparator == DbFilter::COMPARE_NOT_EQUAL)
			return $this->cmpisnotsqlnull($val);
		else 
			return $this->comparator;
	}


	/**
	 */
	public function jsonSerialize() {
		$base = array();
		$base["filter"] = $this->filter;
		$base["comparator"] = $this->comparator;
		$base["logic_op"] = $this->logic_op;
		$base["constraint"] = $this->constraint;
		$base["__type"] = get_class($this);
		return json_encode($base, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
	
	public static function jsonParse(string $json) {
		$tmp = json_decode($json, true);
		if (array_key_exists("__type", $tmp)) {
			if (class_exists($tmp["__type"])) {
				if ($tmp["__type"] == "DbConstraint" || $tmp["__type"] == "DbFilter") {
					$tmpobj = json_decode($json, false);
					$ret = new DbFilter();
					foreach ($tmpobj->filter as $filter) {
						$f = DbFilter::jsonParse(json_encode($filter));
						if ($f != null) $ret->add_filter($f);
					}
					$ret->comparator = $tmpobj->comparator;
					$ret->logic_op = $tmpobj->logic_op;
					$ret->constraint = $tmpobj->constraint;
				}
			}
		}
		return $ret;
	}
	
}

