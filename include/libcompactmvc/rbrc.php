<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Request Based Response Cache
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
class RBRC {
	private static $instance;
	private $redis;
	private $rhash;
	public $log;

	private function __construct($rdata, $observe_headers) {
		DLOG(__METHOD__);
		$this->redis = new Redis();
		$this->redis->connect(REDIS_HOST, REDIS_PORT);
		if ($observe_headers) {
			$this->rhash = md5(serialize($rdata) . serialize(apache_request_headers()));
		} else {
			$this->rhash = md5(serialize($rdata));
		}
	}

	/**
	 *
	 * @return returns the instance of this class. this is a singleton. there can only be one instance per derived class.
	 */
	public static function get_instance($rdata, $observe_headers = true) {
		DLOG(__METHOD__);
		if (!isset(self::$instance)) {
			$name = get_class($this);
			self::$instance = new $name($rdata, $observe_headers);
		}
		return self::$instance;
	}

	public function put($data) {
		$this->redis->set($this->rhash, $data);
		$this->redis->expire($this->rhash, REDIS_KEY_RCACHE_TTL);
	}

	public function get() {
		$data = $this->redis->get($this->rhash);
		$this->redis->expire($this->rhash, REDIS_KEY_RCACHE_TTL);
		return $data;
	}

}