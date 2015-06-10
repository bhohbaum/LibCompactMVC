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
		DLOG(__METHOD__);
		$redis = new Redis();
		$redis->connect(REDIS_HOST, REDIS_PORT);
	}

	public function get_instance() {
		DLOG(__METHOD__);
		if (!isset(self::$instance)) {
			self::$instance = new RedisAdapter();
		}
		return self::$instance;
	}

	public function get($key) {
		DLOG(__METHOD__);
		return $this->redis->get($key);
	}

	public function set($key, $val) {
		DLOG(__METHOD__);
		return $this->redis->get($key, $val);
	}

	public function expire($key, $ttl) {
		DLOG(__METHOD__);
		return $this->redis->expire($key, $ttl);
	}

	public function keys($key) {
		DLOG(__METHOD__);
		return $this->redis->keys($key);
	}

}
