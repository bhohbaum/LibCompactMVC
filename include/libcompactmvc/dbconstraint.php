<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Query constraint definition.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class DbConstraint extends DbFilter implements JsonSerializable {
	protected $order = array();
	protected $limit = array();
	
	public const ORDER_ASCENDING = "ASC";
	public const ORDER_DESCENDING = "DESC";
	
	/**
	 * 
	 * @param array $constraint
	 */
	public function __construct($constraint = array()) {
		DLOG();
		parent::__construct($constraint);
	}
	
		/**
	 * 
	 * @param unknown $column
	 * @param unknown $direction
	 * @return DbConstraint
	 */
	public function order_by($column, $direction) {
		DLOG();
		$this->order[$column] = $direction;
		return $this;
	}
	
	/**
	 * 
	 * @param unknown $start_or_count
	 * @param unknown $opt_count
	 * @return void|DbConstraint
	 */
	public function limit($start_or_count, $opt_count = null) {
		DLOG();
		$this->limit = array();
		if ($start_or_count === null && $opt_count === null) return;
		if ($opt_count == null) {
			$this->limit[0] = $start_or_count;
		} else {
			$this->limit[0] = $start_or_count;
			$this->limit[1] = $opt_count;
		}
		return $this;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see DbFilter::get_query_substring()
	 */
	public function get_query_substring() {
		DLOG();
		$first = true;
		$qstr = parent::get_query_substring() . " ";
		$qstr = ($qstr == "() ") ? "1 " : $qstr;
		if (count($this->order) > 0) {
			$qstr .= "ORDER BY ";
			foreach ($this->order as $col => $dir) {
				if (!$first) $qstr .= ", ";
				$first = false;
				$qstr .= "`" . $col . "` " . $dir;
			}
			$qstr .= " ";
		}
		if (count($this->limit) > 0) {
			if (count($this->limit) == 1) {
				$qstr .= "LIMIT " . $this->limit[0];
			} else if (count($this->limit) == 2) {
				$qstr .= "LIMIT " . $this->limit[0] . ", " . $this->limit[1];
			}
		}
		return $qstr;
	}
	
	/**
	 * 
	 * {@inheritDoc}
	 * @see DbFilter::jsonSerialize()
	 */
	public function jsonSerialize() {
		$base = array();
		$base["filter"] = $this->filter;
		$base["comparator"] = $this->comparator;
		$base["logic_op"] = $this->logic_op;
		$base["constraint"] = $this->constraint;
		$base["order"] = $this->order;
		$base["limit"] = $this->limit;
		$base["__type"] = get_class($this);
		return json_encode($base, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
	}
	
	public static function create_from_json(string $json) {
		$tmp = json_decode($json, true);
		if (array_key_exists("__type", $tmp)) {
			if (class_exists($tmp["__type"])) {
				if ($tmp["__type"] == "DbConstraint" || $tmp["__type"] == "DbFilter") {
					$tmpobj = json_decode($json, false);
					$ret = new DbConstraint();
					foreach ($tmpobj->filter as $filter) {
						$f = DbFilter::create_from_json(json_encode($filter));
						if ($f != null) $ret->add_filter($f);
					}
					$ret->comparator = $tmpobj->comparator;
					$ret->logic_op = $tmpobj->logic_op;
					$ret->constraint = $tmpobj->constraint;
					$ret->order = $tmpobj->order;
					$ret->limit = $tmpobj->limit;
				}
			}
		}
		return $ret;
	}
	
	
}

