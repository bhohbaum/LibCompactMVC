<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Redis Adapter
 * With additional variable cache.
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
	private $data;

	private function __construct() {
		DLOG();
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT);
		$this->data = array();
	}

	public static function get_instance() {
		if (!isset(self::$instance)) {
			self::$instance = new RedisAdapter();
		}
		return self::$instance;
	}

	public function get($key, $use_local_cache = true) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '")');
		if ($use_local_cache) {
			if (array_key_exists($key, $this->data)) {
				return $this->data[$key];
			}
		}
		return $this->redis->get($key);
	}

	public function set($key, $val, $use_local_cache = true) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '", <content>)');
		if ($use_local_cache) {
			$this->data[$key] = $val;
		}
		return $this->redis->set($key, $val);
	}

	public function expire($key, $ttl) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '", ' . $ttl . ')');
		return $this->redis->expire($key, $ttl);
	}

	public function keys($key) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '")');
		$keys = $this->redis->keys($key);
		foreach ($keys as $k => $v) {
			$keys[$k] = substr($v, strlen(REDIS_KEY_PREFIX));
		}
		return $keys;
	}

	public function delete($key) {
		$key = REDIS_KEY_PREFIX . $key;
		DLOG('("' . $key . '")');
		unset($this->data[$key]);
		return $this->redis->delete($key);
	}

}
