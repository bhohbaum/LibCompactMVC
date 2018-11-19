<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Redis Adapter
 * With additional variable cache.
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
if (!extension_loaded("redis")) {
	class Redis {
		private $redisdir;
		private $content;

		public function __construct() {
			DLOG("WARNING! Redis is running in fallback (file) mode!");
			$this->redisdir = TEMP_DIR . "/redis.dat/";
			if (!is_dir($this->redisdir))
				mkdir($this->redisdir);
		}

		public function __destruct() {
		}

		public function connect($host, $port) {
		}

		public function get($key) {
			$fname = $this->redisdir . md5($key);
			if (!file_exists($fname))
				return false;
			$ttl = filemtime($fname);
			if ($ttl < time()) {
				unlink($fname);
				return false;
			}
			$val = file_get_contents($this->redisdir . md5($key));
			touch($fname, $ttl);
			return $val;
		}

		public function set($key, $val) {
			$fname = $this->redisdir . md5($key);
			file_put_contents($fname, $val);
			touch($fname, time() + 3600 * 24 * 356 * 10);
		}

		public function expire($key, $ttl) {
			touch($this->redisdir . md5($key), time() + $ttl);
		}

		public function delete($key) {
			@unlink($this->redisdir . md5($key));
		}

		public function keys($filter) {
			$regex = "/" . str_replace("*", ".*", $filter) . "/";
			$resarr = array();
			if ($handle = opendir($this->redisdir)) {
				while (false !== ($entry = readdir($handle))) {
					if ($entry != "." && $entry != "..") {
						echo "$entry\n";
						preg_match($regex, $entry, $outarray);
						if ($outarray[0] == $entry)
							$resarr[] = $entry;
					}
				}
				closedir($handle);
			}
			return $resarr;
		}
	
	}
}
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
		return @$this->redis->set($key, $val);
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
	
	public function flushall() {
		DLOG();
		return $this->redis->flushAll();
	}
	
	public function flushdb() {
		DLOG();
		return $this->redis->flushDB();
	}
	
}
