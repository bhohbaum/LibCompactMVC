<?php
if (file_exists('../../include/libcompactmvc.php'))
	include_once ('../../include/libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * securecrudcomponent.php
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class SecureCRUDComponent extends CMVCCRUDComponent {
	private $__auth_token_supported = true;
	
	/********************************************************************************************************
	 * The following methods MUST be overwritten to ensure everybody can only access his own data,
	 * based on PHP session or what ever determines the user object, that comes out of
	 * get_current_user_record().
	 ********************************************************************************************************/
	
	/**
	 * Return the user DTO that is currently logged in
	 */
	abstract protected function get_current_user_record();
	
	/**
	 * Return the referenced user DTO belonging to $dto
	 * 
	 * @param DbObject $dto
	 */
	abstract protected function get_user_for_dto($dto);
	
	/**
	 * Return an array of publicly callable method namess of this endpoint, that do not require authentication.
	 */
	abstract protected function get_public_methods();
	
	/**
	 * Table name in which the user records are stored
	 */
	abstract protected function get_user_table_name();

	/********************************************************************************************************
	 * The following methods CAN be overwritten, to support an additional auth token. For a working 
	 * authentication the current valid token must be stored in a column in the user table. Further the
	 * token has to be sent in the request in a special objekt member field or a variable like a GET 
	 * parameter.
	 ********************************************************************************************************/
	
	/**
	 * Name of the variable that carries the auth token within the requests
	 */
	protected function get_auth_token_varname() {
		DLOG();
		$this->__auth_token_supported = false;
	}
	
	/**
	 * Name of the column that holds the valid token in the user table.
	 */
	protected function get_auth_token_colname() {
		DLOG();
		$this->__auth_token_supported = false;
	}
	
	
	
	/********************************************************************************************************
	 * Implementation.
	 ********************************************************************************************************/
	protected function dto_belongs_to_user($dto, $user) {
		DLOG();
		$duser = $this->get_user_for_dto($dto);
		return $duser->{$duser->__pk} == $user->{$user->__pk};
	}
	
	protected function get_expected_dto_type() {
		DLOG();
		return $this->get_table_name();
	}
	
	protected function pre_run() {
		DLOG();
		parent::pre_run();
		$atvn = $this->get_auth_token_varname();
		$atcn = $this->get_auth_token_colname();
		$utable = $this->get_user_table_name();
		$stype = (isset($this->__subject->__type) && $this->__subject->__type != null && is_string($this->__subject->__type)) ? $this->__subject->__type : "";
		$otype = (isset($this->__object) && isset($this->__object->__type) && $this->__object->__type != null && is_string($this->__object->__type)) ? $this->__object->__type : "";
		try {
			$method = $this->path(2);
			$subject = $this->get_subject();
			if (is_callable(array(
					$subject,
					$method
			))) {
				DLOG("Checking access rights for requested RPC: " . $stype . "::" . $method . "(" . $otype . ") HTTP verb: " . $this->get_http_verb());
				foreach ($this->get_public_methods() as $pm) {
					if ($method == $pm) return;
				}
				if ($this->__auth_token_supported) {
					$user = new $utable();
					try {
						try {
							$user->by(array($atcn => $subject->$atvn));
						} catch (InvalidMemberException $e) {
							$user->by(array($atcn => $this->$atvn));
						}
					} catch (InvalidMemberException $e1) {
						$msg = "Missing user key! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					} catch (EmptyResultException $e2) {
						$msg = "Invalid user key! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					}
				}
			}
		} catch (DBException $e3) {
			throw $e3;
		} catch (InvalidMemberException $e4) {
			// that's ok, we possibly have a create/update action:
		}
	}
	
	protected function post_run() {
		DLOG();
		parent::post_run();
		$atvn = $this->get_auth_token_varname();
		$atcn = $this->get_auth_token_colname();
		if (is_array($this->get_response())) {
			$found = false;
			foreach ($this->get_response() as $key => $val) {
				try {
					$dusr = $this->get_user_for_dto($val);
					if ($dusr == null) {
						$found = true;
					} else if ($this->__auth_token_supported && $dusr->{$atcn} != $this->get_subject()->$atvn) {
						$found = true;
					} else if (!$this->dto_belongs_to_user($val, $this->get_current_user_record())) {
						$found = true;
					}
				} catch (Exception $e) {
					$found = true;
				}
			}
			if ($found) {
				$msg = "Array contains foreign/invalid content! Access forbidden!";
				ELOG($msg);
				throw new DBException($msg, 403);
			}
		} else if (is_object($this->get_response()) && get_class($this->get_response()) == $this->get_expected_dto_type()) {
			$cm = $this->get_called_method();
			foreach ($this->get_public_methods() as $pm) {
				if ($cm == $pm) return;
			}
			if ($this->get_user_for_dto($this->get_response()) == null) {
				$msg = "Response content does not belong to an existing user! Access forbidden!";
				ELOG($msg);
				throw new DBException($msg, 403);
			}
			if ($this->__auth_token_supported) {
				try {
					if ($this->get_user_for_dto($this->get_response())->$atcn != $this->get_subject()->$atvn) {
						$msg = "Response content does not match the provided user key! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					}
				} catch (InvalidMemberException $e) {
					try {
						if ($this->get_user_for_dto($this->get_response())->$atcn != $this->$atvn) {
							$msg = "Response content does not match the provided user key! Access forbidden!";
							ELOG($msg);
							throw new DBException($msg, 403);
						}
					} catch (InvalidMemberException $e) {
						$msg = "Auth token is misssing! Access forbidden!";
						ELOG($msg);
						throw new DBException($msg, 403);
					}
				}
			}
		}
	}
	
	
}
