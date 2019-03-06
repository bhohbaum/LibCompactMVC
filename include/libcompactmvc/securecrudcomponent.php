<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once ('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * securecrudcomponent.php
 *
 * @author Botho Hohbaum <bhohbaum@googlemail.com>
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum
 * @license BSD License (see LICENSE file in root directory)
 * @link https://github.com/bhohbaum/LibCompactMVC
 */
abstract class SecureCRUDComponent extends CMVCCRUDComponent {
	
	abstract protected function get_user_record();

	protected function pre_run() {
		DLOG();
		parent::pre_run();
		$user = new user();
		try {
			$subject = json_decode($this->__subject, false);
			$user->by(array("id" => $subject->__user));
		} catch (InvalidMemberException $e1) {
			ELOG("Missing user key! Access forbidden!");
			throw new DBException("Missing user key! Access forbidden!", 403);
		} catch (EmptyResultException $e2) {
			ELOG("Invalid user key! Access forbidden!");
			throw new DBException("Invalid user key! Access forbidden!", 403);
		}
	}
	
	protected function post_run() {
		DLOG();
		parent::post_run();
		if (is_array($this->get_response())) {
			$found = false;
			foreach ($this->get_response() as $key => $val) {
				try {
					if ($val->user == null) $found = true;
					else if ($val->user->id != $this->get_subject()->__user) $found = true;
				} catch (Exception $e) {
					$found = true;
				}
			}
			if ($found) {
				ELOG("Array contains foreign/invalid content! Access forbidden!");
				throw new DBException("Array contains foreign/invalid content! Access forbidden!", 403);
			}
		} else if (is_object($this->get_response()) && get_class($this->get_response()) == "eventlog") {
			if ($this->get_response()->user == null) {
				ELOG("Response content does not belong to an existing user! Access forbidden!");
				throw new DBException("Response content does not belong to an existing user! Access forbidden!", 403);
			}
			if ($this->get_response()->user->id != $this->get_subject()->__user) {
				ELOG("Response content does not match the provided user key! Access forbidden!");
				throw new DBException("Response content does not match the provided user key! Access forbidden!", 403);
			}
		} else {
			return;
		}
	}
		
}
