<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Mutex
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 01.01.2016
 * @license LGPL version 3
 * @link https://github.com/bhohbaum
 */
class Mutex {
	private $key;
	private $token;
	private $delay;
	private $maxwait;

	public function __construct($key) {
		$this->key = $key;
		$this->token = md5(microtime() . rand(0, 99999999));
		$this->delay = 2;
		$this->register();
	}

	public function __destruct() {
		$this->unregister();
	}

	public function lock($maxwait = 60) {
		echo("Lock\n");
		$start = time();
		$this->maxwait = $maxwait;
		while (time() < $start + $maxwait) {
			if (count($this->get_requests()) == 0) {
				$this->set_request();
// 				usleep($this->delay * 5000);
				if (count($this->get_requests()) == 1) {
					if (count($this->get_acks()) + 1 == count($this->get_registrations())) {
						return;
					}
				}
			}
			if (count($this->get_requests()) == 1) {
				if (!$this->is_ack_set() && !$this->is_request_set()) {
					$this->set_ack();
				}
			}
			if (count($this->get_requests()) > 1) {
				echo("Increasing delay: " . $this->delay . "\n");
				$this->delay += 1;
			}
			$this->unlock();
			usleep(rand(0, $this->delay * 500));
		}
		throw new MutexException("max wait time elapsed", 500);
	}

	public function unlock() {
		echo("UnLock\n");
		foreach ($this->get_acks() as $ack) {
			echo("Deleting " . $ack . "\n");
			RedisAdapter::get_instance()->delete($ack, false);
		}
		foreach ($this->get_requests() as $request) {
			echo("Deleting " . $request . "\n");
			RedisAdapter::get_instance()->delete($request, false);
		}
	}

	private function register() {
		echo("Registering " . REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->set(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token, 1, false);
		RedisAdapter::get_instance()->expire(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $elem->token, $this->maxwait);
	}

	private function unregister() {
		echo("Unregistering " . REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->delete(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_" . $this->token, false);
	}

	private function get_registrations() {
		return RedisAdapter::get_instance()->keys(REDIS_KEY_MUTEX_PFX . "REGISTRATION_" . $this->key . "_*", false);
	}

	private function set_request() {
		echo("Setting request " . REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->set(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token, false);
	}

	private function del_request() {
		echo("Deleting request " . REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->delete(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token, false);
	}

	private function get_requests() {
		return RedisAdapter::get_instance()->keys(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_*", false);
	}

	private function is_request_set() {
		return (RedisAdapter::get_instance()->get(REDIS_KEY_MUTEX_PFX . "REQ_" . $this->key . "_" . $this->token, false) != null);
	}

	private function set_ack() {
		echo("Set ACK " . REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->set(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token, false);
	}

	private function del_ack() {
		echo("Del ACK " . REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token . "\n");
		RedisAdapter::get_instance()->delete(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token, false);
	}

	private function get_acks() {
		return RedisAdapter::get_instance()->keys(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_*", false);
	}

	private function is_ack_set() {
		return (RedisAdapter::get_instance()->get(REDIS_KEY_MUTEX_PFX . "ACK_" . $this->key . "_" . $this->token, false) != null);
	}

}
