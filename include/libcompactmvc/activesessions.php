<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * activesessions.php
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
class ActiveSessions extends Singleton {

	protected function __construct() {
		DLOG();
		parent::__construct();
		$this->update();
	}

	/**
	 *
	 * @return ActiveSessions returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($a = null, $b = null, $c = null, $d = null, $e = null, $f = null, $g = null, $h = null, $i = null, $j = null, $k = null, $l = null, $m = null, $n = null, $o = null, $p = null) {
		return parent::get_instance($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
	}

	public function update() {
		DLOG();
		$ra = RedisAdapter::get_instance();
		$key = "ACTIVESESSIONS_" . Session::get_instance()->get_id();
		if ($key == "ACTIVESESSIONS_")
			return;
		$val = $ra->get($key);
		if (is_numeric($val))
			$val += ACTIVESESSIONS_HIT_INCR;
		else
			$val = ACTIVESESSIONS_HIT_INCR;
		$val = ($val > ACTIVESESSIONS_MAX_HITS) ? ACTIVESESSIONS_MAX_HITS : $val;
		$ra->set($key, $val);
		$ra->expire($key, $val);
		DLOG("Setting key ". $key . " to " . $val);
	}

	public function get_session_count() {
		DLOG();
		$ids = array();
		$keys = RedisAdapter::get_instance()->keys("ACTIVESESSIONS_*");
		$count = 0;
		foreach ($keys as $k) {
			if (RedisAdapter::get_instance()->get($k) > ACTIVESESSIONS_MIN_HITS)
				$count++;
		}
		return $count;
	}

}
