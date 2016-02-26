<?php
@include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Cached HTTP request.
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright	Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class CachedHttpRequest {
	private $ttl;

	/**
	 *
	 * @param string $ttl
	 */
	public function __construct($ttl = REDIS_KEY_CACHEDHTTP_TTL) {
		DLOG();
		$this->ttl = $ttl;
	}

	/**
	 *
	 * @param unknown $url
	 */
	public function get($url, $caching = true) {
		DLOG("GET " . $url);
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
		$data = curl_exec($curl);
		RedisAdapter::get_instance()->set($key, $data);
		RedisAdapter::get_instance()->expire($key, $this->ttl);
		return $data;
	}

	/**
	 *
	 * @param unknown $url
	 * @param unknown $vars
	 */
	public function post($url, $vars = array(), $caching = true) {
		DLOG("POST " . $url);
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
		curl_setopt($curl, CURLOPT_POST, count($vars));
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($vars));
		$data = curl_exec($curl);
		RedisAdapter::get_instance()->set($key, $data);
		RedisAdapter::get_instance()->expire($key, $this->ttl);
		return $data;
	}

	/**
	 *
	 * @param unknown $url
	 * @param unknown $vars
	 */
	public function put($url, $vars = array(), $caching = true) {
		DLOG("PUT " . $url);
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
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($vars));
		$data = curl_exec($curl);
		RedisAdapter::get_instance()->set($key, $data);
		RedisAdapter::get_instance()->expire($key, $this->ttl);
		return $data;
	}

	/**
	 *
	 * @param unknown $url
	 */
	public function delete($url, $caching = true) {
		DLOG("DELETE " . $url);
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
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
		$data = curl_exec($curl);
		RedisAdapter::get_instance()->set($key, $data);
		RedisAdapter::get_instance()->expire($key, $this->ttl);
		return $data;
	}

}
