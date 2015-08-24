<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Redis Adapter
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class RedisAdapter {
	private static $instance;
	private $redis;
	public $log;

	private function __construct() {
		DLOG();
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT);
	}

	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new RedisAdapter();
		}
		return self::$instance;
	}

	public function get($key) {
		DLOG(__METHOD__ . '("' . $key . '")');
		return $this->redis->get($key);
	}

	public function set($key, $val) {
		DLOG(__METHOD__ . '("' . $key . '", <content>)');
		return $this->redis->set($key, $val);
	}

	public function expire($key, $ttl) {
		DLOG(__METHOD__ . '("' . $key . '", ' . $ttl . ')');
		return $this->redis->expire($key, $ttl);
	}

	public function keys($key) {
		DLOG(__METHOD__ . '("' . $key . '")');
		return $this->redis->keys($key);
	}

	public function delete($key) {
		DLOG(__METHOD__ . '("' . $key . '")');
		return $this->redis->delete($key);
	}

}
