<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Simple PHP implementation of an SMTP client.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class TableDescription extends DbAccess {
	private $redis;
	public $log;

	public function __construct() {
		parent::__construct();
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT);
	}

	public function columninfo($tablename) {
		DLOG(__METHOD__);
		$desc = $this->redis->get(REDIS_KEY_TBLDESC_PFX . $tablename);
		if ($desc !== false) {
			$desc = unserialize($desc);
			DLOG("Returning table description for table " . $tablename . " from cache.");
			return $desc;
		}
		DLOG("Reading table description from database.");
		$q = "DESCRIBE " . $tablename;
		$desc = $this->run_query($q, true, true);
		$this->redis->set(REDIS_KEY_TBLDESC_PFX . $tablename, serialize($desc));
		return $desc;
	}

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

	public function columns($tablename) {
		$desc = $this->columninfo($tablename);
		$ret = array();
		foreach ($desc as $key => $val) {
			$ret[] = $val->Field;
		}
		return $ret;
	}

}
