<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * cephadapter.php
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
if (extension_loaded("rados")) {
	class CephAdapter extends Singleton {
		private $rados;
		private $ctx;

		protected function __construct() {
			DLOG();
			parent::__construct();
			$this->rados = rados_create();
			rados_conf_read_file($this->rados, CEPH_CONF);
			if (!rados_connect($this->rados)) throw new Exception("Could not connect to rados (ceph cluster)");
			$this->ctx = rados_ioctx_create($this->rados, CEPH_POOL);
		}

		public function __destruct() {
			DLOG();
			parent::__destruct();
			rados_ioctx_destroy($this->ctx);
			rados_shutdown($this->rados);
		}

		/**
		 *
		 * @return CephAdapter
		 */
		public static function get_instance($a = NULL, $b = NULL, $c = NULL, $d = NULL, $e = NULL, $f = NULL, $g = NULL, $h = NULL, $i = NULL, $j = NULL, $k = NULL, $l = NULL, $m = NULL, $n = NULL, $o = NULL, $p = NULL) {
			DLOG();
			return parent::get_instance($a, $b, $c, $d, $e, $f, $g, $h, $i, $j, $k, $l, $m, $n, $o, $p);
		}

		public function put($oid, $buf) {
			DLOG($oid);
			$res = rados_write_full($this->ctx, $oid, $buf);
			return $res;
		}

		public function get($oid) {
			DLOG($oid);
			$buf = rados_read($this->ctx, $oid, CEPH_MAX_OBJ_SIZE);
			if (is_array($buf)) {
				throw new EmptyResultException($buf['errMessage'], $buf['errCode']);
			}
			return $buf;
		}

		public function remove($oid) {
			DLOG($oid);
			$res = rados_remove($this->ctx, $oid);
			return $res;
		}

		public function objects_list() {
			DLOG();
			$res = rados_objects_list($this->ctx);
			return $res;
		}

	}
}
