<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Cached HTTP request.
 *
 * @author 		Botho Hohbaum (bhohbaum@googlemail.com)
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
class CachedHttpRequest {
	private $ttl;
	private $caching_enabled;

	/**
	 *
	 * @param string $ttl        	
	 */
	public function __construct($ttl = REDIS_KEY_CACHEDHTTP_TTL) {
		DLOG();
		$this->ttl = $ttl;
		$this->caching_enabled = true;
	}

	/**
	 *
	 * @param String $url        	
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function get($url, $caching = "default") {
		DLOG("GET " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 *
	 * @param unknown $url        	
	 * @param unknown $vars        	
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function post($url, $vars = array(), $caching = "default") {
		DLOG("POST " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize(array(
				$url,
				$vars
		)));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		curl_setopt($curl, CURLOPT_POST, count($vars));
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($vars));
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 *
	 * @param unknown $url        	
	 * @param unknown $vars        	
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function put($url, $vars = array(), $caching = "default") {
		DLOG("PUT " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($vars));
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 *
	 * @param String $url
	 *        	URL to send the request to.
	 * @param Boolean $caching
	 *        	Cache this request or not.
	 */
	public function delete($url, $caching = "default") {
		DLOG("DELETE " . $url);
		if ($caching == "default")
			$caching = $this->caching_enabled;
		$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
		if ($caching) {
			$data = RedisAdapter::get_instance()->get($key);
			if ($data !== false) {
				RedisAdapter::get_instance()->expire($key, $this->ttl);
				return $data;
			}
		}
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		if (defined('PROXY_CONFIG') && defined('PROXY_PORT')) {
			curl_setopt($curl, CURLOPT_PROXY, PROXY_CONFIG);
			curl_setopt($curl, CURLOPT_PROXYPORT, PROXY_PORT);
		}
		if (defined('SSL_VERIFYPEER')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, SSL_VERIFYPEER);
		}
		if (defined('SSL_VERIFYHOST')) {
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, SSL_VERIFYHOST);
		}
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		$data = curl_exec($curl);
		if ($data === false)
			DLOG(curl_error($curl));
		if ($caching && ($data !== false)) {
			RedisAdapter::get_instance()->set($key, $data);
			RedisAdapter::get_instance()->expire($key, $this->ttl);
		}
		return $data;
	}

	/**
	 * Delete one or all entries from cache
	 *
	 * @param string $url
	 *        	URL of the cache entry
	 */
	public function flush($url = null) {
		DLOG("FLUSH " . (($url == null) ? "ALL" : $url));
		if ($url != null) {
			$key = REDIS_KEY_CACHEDHTTP_PFX . md5(serialize($url));
			RedisAdapter::get_instance()->delete($key);
		} else {
			$keys = RedisAdapter::get_instance()->keys(REDIS_KEY_CACHEDHTTP_PFX . "*");
			foreach ($keys as $k) {
				RedisAdapter::get_instance()->delete($k);
			}
		}
	}

	/**
	 *
	 * @param boolean $caching
	 *        	Set the caching mode. Give null here to just retrieve the current status.
	 */
	public function caching_enabled($caching = null) {
		if ($caching !== null) {
			$this->caching_enabled = $caching;
		}
		return $this->caching_enabled;
	}

}
