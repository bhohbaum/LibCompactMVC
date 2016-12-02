<?php
if (file_exists('../libcompactmvc.php')) include_once('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Controller super class
 *
 * @author Botho Hohbaum (bhohbaum@googlemail.com)
 * @package LibCompactMVC
 * @copyright Copyright (c) Botho Hohbaum 24.01.2012
 * @license LGPL version 3
 * @link https://github.com/bhohbaum/libcompactmvc
 */
abstract class CMVCController extends InputSanitizer {
	private $ob;
	private static $rbrc;
	private $cmp_disp_base;
	private $mime_type;
	public $view;

	/**
	 *
	 * @var DbAccess db
	 */
	public $db;

	public $redirect;

	/**
	 */
	public function __construct() {
		DLOG();
		parent::__construct();
		$this->view = new View();
		$this->mime_type = MIME_TYPE_HTML;
	}

	/**
	 * Has to return the name of the DBA class.
	 *
	 * @return String
	 */
	protected function dba() {
		DLOG();
		return (defined("DBA_DEFAULT_CLASS")) ? DBA_DEFAULT_CLASS : "DbAccess";
	}

	// Legacy API

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function retrieve_data() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function run_page_logic() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function retrieve_data_get() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function retrieve_data_post() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function retrieve_data_put() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function retrieve_data_delete() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function retrieve_data_exec() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function run_page_logic_get() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function run_page_logic_post() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function run_page_logic_put() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function run_page_logic_delete() {
		DLOG();
	}

	/**
	 *
	 * @deprecated
	 *
	 */
	protected function run_page_logic_exec() {
		DLOG();
	}

	// New API
	protected function pre_run() {
		DLOG();
	}

	protected function pre_run_get() {
		DLOG();
	}

	protected function pre_run_post() {
		DLOG();
	}

	protected function pre_run_put() {
		DLOG();
	}

	protected function pre_run_delete() {
		DLOG();
	}

	protected function pre_run_exec() {
		DLOG();
	}

	protected function main_run() {
		DLOG();
	}

	protected function main_run_get() {
		DLOG();
	}

	protected function main_run_post() {
		DLOG();
	}

	protected function main_run_put() {
		DLOG();
	}

	protected function main_run_delete() {
		DLOG();
	}

	protected function main_run_exec() {
		DLOG();
	}

	protected function post_run() {
		DLOG();
	}

	protected function post_run_get() {
		DLOG();
	}

	protected function post_run_post() {
		DLOG();
	}

	protected function post_run_put() {
		DLOG();
	}

	protected function post_run_delete() {
		DLOG();
	}

	protected function post_run_exec() {
		DLOG();
	}

	/**
	 * Exception handler
	 *
	 * @param Exception $e
	 */
	protected function exception_handler($e) {
		DLOG();
		throw $e;
	}

	/**
	 */
	protected function get_raw_input() {
		return CMVCController::$request_data_raw;
	}

	/**
	 */
	protected function method() {
		if (php_sapi_name() == "cli") {
			$method = (getenv("METHOD") !== false) ? getenv("METHOD") : "exec";
		} else {
			$method = $_SERVER['REQUEST_METHOD'];
		}
		$method = strtoupper($method);
		DLOG($method);
		return $method;
	}

	/**
	 *
	 * @param unknown_type $obj
	 */
	protected function json_response($obj) {
		DLOG(UTF8::encode(json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
		$this->mime_type = MIME_TYPE_JSON;
		$this->view->clear();
		$this->view->add_template("out.tpl");
		$this->view->set_value("out", UTF8::encode(json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)));
	}

	/**
	 *
	 * @param unknown_type $obj
	 */
	protected function binary_response($obj, $mime = MIME_TYPE_OCTET_STREAM) {
		DLOG();
		$this->mime_type = $mime;
		$this->content_type_isset = true;
		$this->view->clear();
		$this->view->add_template("out.tpl");
		$this->view->set_value("out", $obj);
	}

	/**
	 *
	 * @param unknown_type $code
	 * @throws Exception
	 */
	protected function response_code($code = null) {
		DLOG("(" . $code . ")");
		if (function_exists('http_response_code')) {
			$code = http_response_code($code);
		} else {
			if ($code !== null) {
				switch ($code) {
					case 100:
						$text = 'Continue';
						break;
					case 101:
						$text = 'Switching Protocols';
						break;
					case 200:
						$text = 'OK';
						break;
					case 201:
						$text = 'Created';
						break;
					case 202:
						$text = 'Accepted';
						break;
					case 203:
						$text = 'Non-Authoritative Information';
						break;
					case 204:
						$text = 'No Content';
						break;
					case 205:
						$text = 'Reset Content';
						break;
					case 206:
						$text = 'Partial Content';
						break;
					case 300:
						$text = 'Multiple Choices';
						break;
					case 301:
						$text = 'Moved Permanently';
						break;
					case 302:
						$text = 'Moved Temporarily';
						break;
					case 303:
						$text = 'See Other';
						break;
					case 304:
						$text = 'Not Modified';
						break;
					case 305:
						$text = 'Use Proxy';
						break;
					case 400:
						$text = 'Bad Request';
						break;
					case 401:
						$text = 'Unauthorized';
						break;
					case 402:
						$text = 'Payment Required';
						break;
					case 403:
						$text = 'Forbidden';
						break;
					case 404:
						$text = 'Not Found';
						break;
					case 405:
						$text = 'Method Not Allowed';
						break;
					case 406:
						$text = 'Not Acceptable';
						break;
					case 407:
						$text = 'Proxy Authentication Required';
						break;
					case 408:
						$text = 'Request Time-out';
						break;
					case 409:
						$text = 'Conflict';
						break;
					case 410:
						$text = 'Gone';
						break;
					case 411:
						$text = 'Length Required';
						break;
					case 412:
						$text = 'Precondition Failed';
						break;
					case 413:
						$text = 'Request Entity Too Large';
						break;
					case 414:
						$text = 'Request-URI Too Large';
						break;
					case 415:
						$text = 'Unsupported Media Type';
						break;
					case 500:
						$text = 'Internal Server Error';
						break;
					case 501:
						$text = 'Not Implemented';
						break;
					case 502:
						$text = 'Bad Gateway';
						break;
					case 503:
						$text = 'Service Unavailable';
						break;
					case 504:
						$text = 'Gateway Time-out';
						break;
					case 505:
						$text = 'HTTP Version not supported';
						break;
					default :
						throw new Exception('Unknown http status code "' . htmlentities($code) . '"', $code);
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

	/**
	 *
	 * @param unknown_type $observe_headers
	 * @throws RBRCException
	 */
	protected function rbrc($observe_headers = true) {
		DLOG();
		self::$rbrc = RBRC::get_instance($this->request(), $observe_headers);
		if (self::$rbrc->get()) {
			$this->view->clear();
			$this->view->add_template("out.tpl");
			$this->view->set_value("out", self::$rbrc->get());
			$this->ob = $this->view->render();
			throw new RBRCException();
		}
	}

	/**
	 * Proxy method to $this->view->set_component($key, CMVCController $component).
	 * Required to use one component multiple times.
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	protected function set_component($key, CMVCComponent $component) {
		DLOG("(" . $key . ", " . get_class($component) . ")");
		$this->view->set_component($key, $component);
	}

	/**
	 * Proxy method to $this->view->set_component($key, CMVCController $component).
	 * Associates the component with its own ID.
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	protected function add_component(CMVCComponent $component) {
		DLOG(get_class($component));
		$this->view->set_component($component->get_component_id(), $component);
	}

	/**
	 * Sets string for component selection.
	 *
	 * @param String $base
	 */
	protected function set_component_dispatch_base($base) {
		DLOG($base);
		$this->cmp_disp_base = $base;
	}

	/**
	 * Input-controlled component dispatcher.
	 *
	 * @param CMVCComponent $component
	 *        	Component to be dispatched.
	 */
	protected function dispatch_component(CMVCComponent $component) {
		DLOG(get_class($component));
		if ($this->cmp_disp_base == $component->get_component_id())
			$this->add_component($component);
	}

	/**
	 * Input-controlled component dispatcher.
	 *
	 * @return CMVCComponent the dispatched component object
	 */
	protected function get_dispatched_component() {
		DLOG();
		return $this->view->get_component($this->cmp_disp_base);
	}

	/**
	 * Calls the corresponding functions.
	 *
	 * @param String $var
	 */
	protected function dispatch($var) {
		DLOG($var);
		$method = strtolower($this->method());
		$func = $method . "_" . $var;
		if (is_callable(array(
				$this,
				$func
		)))
			$this->$func();
		if (is_callable(array(
				$this,
				$var
		)))
			$this->$var();
	}

	/**
	 */
	public function run() {
		DLOG();
		DLOG(var_export($_REQUEST, true));
		$this->redirect = "";
		$this->db = DbAccess::get_instance($this->dba());
		if (!isset($this->view)) {
			$this->view = new View();
		}
		// Legacy API
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
			case 'EXEC':
				$this->retrieve_data_exec();
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
			case 'EXEC':
				$this->run_page_logic_exec();
				break;
		}
		$this->run_page_logic();
		// New API
		switch ($this->method()) {
			case 'GET':
				$this->pre_run_get();
				break;
			case 'POST':
				$this->pre_run_post();
				break;
			case 'PUT':
				$this->pre_run_put();
				break;
			case 'DELETE':
				$this->pre_run_delete();
				break;
			case 'EXEC':
				$this->pre_run_exec();
				break;
		}
		$this->pre_run();
		switch ($this->method()) {
			case 'GET':
				$this->main_run_get();
				break;
			case 'POST':
				$this->main_run_post();
				break;
			case 'PUT':
				$this->main_run_put();
				break;
			case 'DELETE':
				$this->main_run_delete();
				break;
			case 'EXEC':
				$this->main_run_exec();
				break;
		}
		$this->main_run();
		switch ($this->method()) {
			case 'GET':
				$this->post_run_get();
				break;
			case 'POST':
				$this->post_run_post();
				break;
			case 'PUT':
				$this->post_run_put();
				break;
			case 'DELETE':
				$this->post_run_delete();
				break;
			case 'EXEC':
				$this->post_run_exec();
				break;
		}
		$this->post_run();

		// If we have a redirect, we don't want the current template(s) to be generated.
		if ($this->redirect == "") {
			$this->ob = $this->view->render();
			if (isset(self::$rbrc)) {
				self::$rbrc->put($this->ob);
			}
		}
	}

	/**
	 * Run the exception handler method
	 *
	 * @param Exception $e
	 *        	the exception
	 */
	public function run_exception_handler($e) {
		DLOG("Exception " . $e->getCode() . " '" . $e->getMessage() . "'");
		if ($e instanceof RedirectException) {
			$this->response_code($e->getCode());
			if ($e->is_internal()) {
				$this->redirect = $e->getMessage();
			} else {
				header("Location: " . $e->getMessage());
			}
		} else {
			try {
				$this->exception_handler($e);
			} catch (RedirectException $e0) {
				if ($e0->is_internal()) {
					$this->redirect = $e0->getMessage();
				} else {
					header("Location: " . $e0->getMessage());
				}
				return;
			} catch (Exception $e1) {
				$this->ob = $this->view->render();
				$this->response_code($e->getCode());
				throw $e1;
			}
			$this->ob = $this->view->render();
			$this->response_code($e->getCode());
		}
	}

	/**
	 * Returns the output buffer of the current controller
	 *
	 * @return String Rendered content
	 */
	public function get_ob() {
		DLOG();
		return $this->ob;
	}

	/**
	 *
	 * @param String $mime_type
	 *        	the mime type of the current controllers output.
	 */
	protected function set_mime_type($mime_type) {
		DLOG($mime_type);
		$this->mime_type = $mime_type;
	}

	/**
	 */
	public function get_mime_type() {
		DLOG("Return: " . $this->mime_type);
		return $this->mime_type;
	}

}
