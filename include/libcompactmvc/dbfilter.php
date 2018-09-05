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
	private $qb;
	private $parent;					// parent element
	
	protected $filter = array();
	protected $comparator = array();
	protected $logic_op = array();
	protected $constraint = array();
	
	const LOGIC_OPERATOR_AND = "AND";
	const LOGIC_OPERATOR_OR = "OR";
	const LOGIC_OPERATOR_XOR = "XOR";
	const LOGIC_OPERATOR_NOT = "NOT";
	
	const COMPARE_EQUAL = "=";
	const COMPARE_NOT_EQUAL = "!=";
	const COMPARE_LIKE = "LIKE";
	const COMPARE_NOT_LIKE = "NOT LIKE";
	const COMPARE_GREATER_THAN = ">";
	const COMPARE_LESS_THAN = "<";
	const COMPARE_GREATER_EQUAL_THAN = ">=";
	const COMPARE_LESS_EQUAL_THAN = "<=";
	const COMPARE_IN = "IN";
	const COMPARE_NOT_IN = "NOT IN";
	
	/**
	 * 
	 * @param array $constraint
	 */
	public function __construct(array $constraint = array()) {
		DLOG(print_r($constraint, true));
		$this->constraint = $constraint;
		$this->filter = array();
		$this->comparator = DbFilter::COMPARE_EQUAL;
		$this->logic_op = DbFilter::LOGIC_OPERATOR_AND;
		$this->qb = new QueryBuilder();
	}
	
	/**
	 * 
	 * @param DbFilter $filter
	 * @return DbFilter
	 */
	public function add_filter(DbFilter $filter) {
		DLOG();
		$filter->set_parent($this);
		$this->filter[] = $filter;
		return $this;
	}

	protected function set_parent(DbFilter $parent) {
		$this->parent = $parent;
		return $this;
	}
	
	public function get_table() {
		$filter = $this;
		while (get_class($filter) != "DbConstraint") {
			$filter = $filter->parent;
		}
		$dto = $filter->get_dto();
		$table = $dto->get_table();
		return $table;
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
		return $this->qb->where_substring($this->get_table(), $this->constraint, $this->filter, $this->comparator, $this->logic_op);
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
	
	/**
	 * DbFilter factory
	 * 
	 * @param string $json
	 * @return DbFilter
	 */
	public static function create_from_json($json) {
		$tmp = json_decode($json, true);
		if (array_key_exists("__type", $tmp)) {
			if (class_exists($tmp["__type"])) {
				if ($tmp["__type"] == "DbConstraint" || $tmp["__type"] == "DbFilter") {
					$tmpobj = json_decode($json, false);
					$ret = new DbFilter();
					foreach ($tmpobj->filter as $filter) {
						$f = DbFilter::create_from_json(json_encode($filter));
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

