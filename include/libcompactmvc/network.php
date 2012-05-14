<?php
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;


class Network {
	
	/**
	 * @return returns the real client IP, even if a proxy is used
	 */
	public static function get_real_client_ip() {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	
	
	
}


?>