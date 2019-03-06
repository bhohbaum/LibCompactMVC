<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Request Based Response Cache
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Media Impression Unit 08
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class RBRC {
	private static $instance;
	private $rhash;

	/**
	 *
	 * @param unknown_type $rdata        	
	 * @param unknown_type $observe_headers        	
	 */
	private function __construct($rdata, $observe_headers) {
		DLOG();
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
		DLOG();
		if (!isset(self::$instance)) {
			self::$instance = new RBRC($rdata, $observe_headers);
		}
		return self::$instance;
	}

	/**
	 *
	 * @param unknown_type $data        	
	 */
	public function put($data) {
		RedisAdapter::get_instance()->set($this->rhash, $data);
		RedisAdapter::get_instance()->expire($this->rhash, REDIS_KEY_RCACHE_TTL);
	}

	/**
	 */
	public function get() {
		$data = RedisAdapter::get_instance()->get($this->rhash);
		RedisAdapter::get_instance()->expire($this->rhash, REDIS_KEY_RCACHE_TTL);
		return $data;
	}

}