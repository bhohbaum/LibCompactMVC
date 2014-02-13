<?php 
@include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Pages superclass
 * 
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @license LGPL version 3
 * @link http://www.gnu.org/licenses/lgpl.html
 */
abstract class Page {
	
	private $ob;
	
	public $view;
	
	/**
	 * @var DbAccess db
	 */
	public $db;
	
	public $redirect;
	
	protected function retrieve_data() {}
	protected function run_page_logic() {}
	
	protected function retrieve_data_get() {}
	protected function retrieve_data_post() {}
	protected function retrieve_data_put() {}
	protected function retrieve_data_delete() {}
	
	protected function run_page_logic_get() {}
	protected function run_page_logic_post() {}
	protected function run_page_logic_put() {}
	protected function run_page_logic_delete() {}
	
	public function __construct() {
		$this->view = new View();
	}
	
	protected function request($var) {
		switch ($this->method()) {
			case 'GET':
				$data = $_REQUEST;
				break;
			case 'POST':
				$data = $_REQUEST;
				break;
			case 'PUT':
				parse_str(file_get_contents('php://input'), $put_vars);
				$data = $put_vars;
				break;
			case 'DELETE':
				$data = $_REQUEST;
				break;
		}
		return isset($data[$var]) ? $data[$var] : null;
	}
	
	protected function method() {
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}
	
	protected function json_response($obj) {
		$this->view->clear();
		$this->view->add_template("out.tpl");
		$this->view->set_value("out", UTF8::encode(json_encode($obj)));
	}
	
	protected function binary_response($obj) {
		$this->view->clear();
		$this->view->add_template("out.tpl");
		$this->view->set_value("out", $obj);
	}
	
	protected function response_code($code) {
		if (function_exists('http_response_code')) {
			$code = http_response_code($code);
		} else {
			if ($code !== null) {
				switch ($code) {
					case 100: $text = 'Continue'; break;
					case 101: $text = 'Switching Protocols'; break;
					case 200: $text = 'OK'; break;
					case 201: $text = 'Created'; break;
					case 202: $text = 'Accepted'; break;
					case 203: $text = 'Non-Authoritative Information'; break;
					case 204: $text = 'No Content'; break;
					case 205: $text = 'Reset Content'; break;
					case 206: $text = 'Partial Content'; break;
					case 300: $text = 'Multiple Choices'; break;
					case 301: $text = 'Moved Permanently'; break;
					case 302: $text = 'Moved Temporarily'; break;
					case 303: $text = 'See Other'; break;
					case 304: $text = 'Not Modified'; break;
					case 305: $text = 'Use Proxy'; break;
					case 400: $text = 'Bad Request'; break;
					case 401: $text = 'Unauthorized'; break;
					case 402: $text = 'Payment Required'; break;
					case 403: $text = 'Forbidden'; break;
					case 404: $text = 'Not Found'; break;
					case 405: $text = 'Method Not Allowed'; break;
					case 406: $text = 'Not Acceptable'; break;
					case 407: $text = 'Proxy Authentication Required'; break;
					case 408: $text = 'Request Time-out'; break;
					case 409: $text = 'Conflict'; break;
					case 410: $text = 'Gone'; break;
					case 411: $text = 'Length Required'; break;
					case 412: $text = 'Precondition Failed'; break;
					case 413: $text = 'Request Entity Too Large'; break;
					case 414: $text = 'Request-URI Too Large'; break;
					case 415: $text = 'Unsupported Media Type'; break;
					case 500: $text = 'Internal Server Error'; break;
					case 501: $text = 'Not Implemented'; break;
					case 502: $text = 'Bad Gateway'; break;
					case 503: $text = 'Service Unavailable'; break;
					case 504: $text = 'Gateway Time-out'; break;
					case 505: $text = 'HTTP Version not supported'; break;
					default:
						exit('Unknown http status code "' . htmlentities($code) . '"');
						break;
				}
				$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
				header($protocol . ' ' . $code . ' ' . $text);
				$GLOBALS['http_response_code'] = $code;
			} else {
				$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
			}
		}
		return $code;
	}

	public function run() {
		$this->redirect = "";
		$this->db = DbAccess::get_instance();
		if (!isset($this->view)) {
			$this->view = new View();
		}
		switch ($this->method()) {
			case 'GET':
				$this->retrieve_data_get();
				break;
			case 'POST':
				$this->retrieve_data_post();
				break;
			case 'PUT':
				$this->retrieve_data_put();
				break;
			case 'DELETE':
				$this->retrieve_data_delete();
				break;
		}
		$this->retrieve_data();
		switch ($this->method()) {
			case 'GET':
				$this->run_page_logic_get();
				break;
			case 'POST':
				$this->run_page_logic_post();
				break;
			case 'PUT':
				$this->run_page_logic_put();
				break;
			case 'DELETE':
				$this->run_page_logic_delete();
				break;
		}
		$this->run_page_logic();
		// after the page logic has been executed, we don't need
		// the database any more. all information should be stored 
		// in variables by now.
		unset($this->db);
		// if we have a redirect, we don't want the current template(s) to be displayed
		if ($this->redirect == "") {
			$this->ob = $this->view->render();
		}
	}
	
	public function get_ob() {
		return $this->ob;
	}
	
}

?>