<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Table characteristics.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class TableDescription extends DbAccess {
	// static storage to buffer multiple foreign key lookups
	// and reduce traffic between application server and redis cluster
	private static $colinfoarr;
	private static $fkinfoarr;

	public function __construct() {
		parent::__construct();
		self::$colinfoarr = json_decode("{}", true);
		self::$fkinfoarr = json_decode("{}", true);
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function columninfo($tablename) {
		if (array_key_exists($tablename, self::$colinfoarr)) {
			return self::$colinfoarr[$tablename];
		}
		$desc = RedisAdapter::get_instance()->get(REDIS_KEY_TBLDESC_PFX . $tablename);
		if ($desc !== false) {
			$desc = unserialize($desc);
			self::$colinfoarr[$tablename] = $desc;
			return $desc;
		}
		$q = "DESCRIBE " . $tablename;
		$desc = $this->run_query($q, true, true);
		self::$colinfoarr[$tablename] = $desc;
		RedisAdapter::get_instance()->set(REDIS_KEY_TBLDESC_PFX . $tablename, serialize($desc));
		return $desc;
	}

	/**
	 *
	 * @param unknown_type $tablename        	
	 */
	public function fkinfo($tablename) {
		if (array_key_exists($tablename, self::$fkinfoarr)) {
			return self::$fkinfoarr[$tablename];
		}
		$desc = RedisAdapter::get_instance()->get(REDIS_KEY_FKINFO_PFX . $tablename);
		if ($desc !== false) {
			$desc = unserialize($desc);
			self::$fkinfoarr[$tablename] = $desc;
			return $desc;
		}
		$q = "SELECT
					CONCAT(table_name, '.', column_name) as 'fk',
					CONCAT(referenced_table_name, '.', referenced_column_name) as 'ref'
				FROM
					information_schema.key_column_usage
				WHERE
					referenced_table_name IS NOT NULL
				AND table_schema = '" . MYSQL_SCHEMA . "'
				AND table_name = '" . $tablename . "'";
		$desc = $this->run_query($q, true, true);
		self::$fkinfoarr[$tablename] = $desc;
		RedisAdapter::get_instance()->set(REDIS_KEY_FKINFO_PFX . $tablename, serialize($desc));
		return $desc;
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function primary_keys($tablename) {
		$desc = $this->columninfo($tablename);
		$ret = array();
		foreach ($desc as $key => $val) {
			if ($val->Key == "PRI") {
				$ret[] = $val->Field;
			}
		}
		return $ret;
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function columns($tablename) {
		$desc = $this->columninfo($tablename);
		$ret = array();
		foreach ($desc as $key => $val) {
			$ret[] = $val->Field;
		}
		return $ret;
	}

	/**
	 *
	 * @param string $tablename        	
	 */
	public function table_exists($tablename) {
		try {
			$this->columninfo($tablename);
			return true;
		} catch (DBException $e) {
			return false;
		}
	}

}
