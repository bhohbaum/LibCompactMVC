<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Table characteristics.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class TableDescription extends DbAccess {

	public function __construct() {
		parent::__construct();
	}

	/**
	 *
	 * @param unknown_type $tablename
	 */
	public function columninfo($tablename) {
		$desc = RedisAdapter::get_instance()->get(REDIS_KEY_TBLDESC_PFX . $tablename);
		if ($desc !== false) {
			$desc = unserialize($desc);
			return $desc;
		}
		$q = "DESCRIBE " . $tablename;
		$desc = $this->run_query($q, true, true);
		RedisAdapter::get_instance()->set(REDIS_KEY_TBLDESC_PFX . $tablename, serialize($desc));
		return $desc;
	}

	/**
	 *
	 * @param unknown_type $tablename
	 */
	public function fkinfo($tablename) {
		$desc = RedisAdapter::get_instance()->get(REDIS_KEY_FKINFO_PFX . $tablename);
		if ($desc !== false) {
			$desc = unserialize($desc);
			return $desc;
		}
		$q = "SELECT
				    CONCAT(table_name, '.', column_name) as 'fk',
				    CONCAT(referenced_table_name, '.', referenced_column_name) as 'ref'
				FROM
				    information_schema.key_column_usage
				WHERE
				    referenced_table_name IS NOT NULL
				AND table_schema = '" . MYSQL_DB . "'
				AND table_name = '" . $tablename . "'";
		$desc = $this->run_query($q, true, true);
		RedisAdapter::get_instance()->set(REDIS_KEY_FKINFO_PFX . $tablename, serialize($desc));
		return $desc;
	}

	/**
	 *
	 * @param unknown_type $tablename
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
	 * @param unknown_type $tablename
	 */
	public function columns($tablename) {
		$desc = $this->columninfo($tablename);
		$ret = array();
		foreach ($desc as $key => $val) {
			$ret[] = $val->Field;
		}
		return $ret;
	}

}
