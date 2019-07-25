<?php
if (file_exists('../libcompactmvc.php'))
	include_once ('../libcompactmvc.php');
LIBCOMPACTMVC_ENTRY;

/**
 * Controller super class
 *
 * @author 		Botho Hohbaum <bhohbaum@googlemail.com>
 * @package		LibCompactMVC
 * @copyright   Copyright (c) Botho Hohbaum
 * @license 	BSD License (see LICENSE file in root directory)
 * @link		https://github.com/bhohbaum/LibCompactMVC
 */
abstract class CMVCController extends InputSanitizer {
	private $__ob;
	private static $__rbrc;
	private $__cmp_disp_base;
	private $__mime_type;
	private $__redirect;
	private $__caching;

	/**
	 *
	 * @var View $__view
	 */
	private $__view;

	/**
	 *
	 * @var DbAccess db
	 */
	private $__db;

	/**
	 */
	public function __construct() {
		DLOG();
		parent::__construct();
		$this->__view = new View();
		$this->__mime_type = MIME_TYPE_HTML;
		$this->__caching = CACHING_ENABLED;
	}

	/**
	 * Has to return the name of the DBA class.
	 * Overwrite this method if your controller requires a different DbAccess object from get_db().
	 *
	 * @return String
	 */
	protected function dba() {
		DLOG();
		return (defined("DBA_DEFAULT_CLASS")) ? DBA_DEFAULT_CLASS : "DbAccess";
	}

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
	protected function exception_handler(Exception $e) {
		DLOG(get_class($e));
		throw $e;
	}

	/**
	 */
	protected function get_raw_input() {
		return CMVCController::$request_data_raw;
	}

	/**
	 */
	protected function get_method() {
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
	 * @param Object $obj
	 */
	protected function json_response($obj) {
		$json = UTF8::encode(json_encode($obj, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
		$this->__mime_type = MIME_TYPE_JSON;
		$this->__view->clear();
		$this->__view->add_template("__out.tpl");
		$this->__view->set_value("out", $json);
	}

	/**
	 *
	 * @param unknown_type $obj
	 */
	protected function binary_response($obj, $mime = MIME_TYPE_OCTET_STREAM) {
		DLOG();
		$this->__mime_type = $mime;
		$this->__view->clear();
		$this->__view->add_template("__out.tpl");
		$this->__view->set_value("out", $obj);
	}

	/**
	 * Shorthand method to return the dispatched components output.
	 *
	 * @return Boolean true if a matching component was found, false otherwise.
	 */
	protected function component_response() {
		DLOG();
		if ($this->get_dispatched_component() != null) {
			$this->set_caching(false);
			$this->binary_response($this->get_dispatched_component()->get_ob(), $this->get_dispatched_component()->get_mime_type());
			return true;
		} else {
			return false;
		}
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
		self::$__rbrc = RBRC::get_instance($this->request(), $observe_headers);
		if (self::$__rbrc->get()) {
			$this->__view->clear();
			$this->__view->add_template("__out.tpl");
			$this->__view->set_value("out", self::$__rbrc->get());
			$this->__ob = $this->__view->render($this->__caching);
			throw new RBRCException();
		}
	}

	/**
	 * Proxy method to $this->get_view()->set_component($key, CMVCController $component).
	 * Required to use one component multiple times.
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	protected function set_component($key, CMVCComponent $component) {
		DLOG("(" . $key . ", " . get_class($component) . ")");
		$this->__view->set_component($key, $component);
	}

	/**
	 * Proxy method to $this->get_view()->set_component($key, CMVCController $component).
	 * Associates the component with its own ID.
	 *
	 * @param String $key
	 * @param CMVCController $component
	 */
	protected function add_component(CMVCComponent $component) {
		DLOG(get_class($component));
		$this->__view->set_component($component->get_component_id(), $component);
	}

	/**
	 * Sets string for component selection.
	 *
	 * @param String $base
	 */
	protected function set_component_dispatch_base($base) {
		DLOG($base);
		$this->__cmp_disp_base = $base;
	}

	/**
	 * Input-controlled component dispatcher.
	 *
	 * @param CMVCComponent $component
	 *        	Component to be dispatched.
	 */
	protected function dispatch_component(CMVCComponent $component) {
		DLOG(get_class($component));
		if ($this->__cmp_disp_base == $component->get_component_id())
			$this->add_component($component);
	}

	/**
	 * Get the component object that was selected / will be selected based on the component dispatch base.
	 *
	 * @return CMVCComponent the dispatched component object
	 */
	protected function get_dispatched_component() {
		DLOG();
		return $this->__view->get_component($this->__cmp_disp_base);
	}

	/**
	 * Call methods based on the input that is provided.
	 *
	 * @param String $var
	 * @return Boolean true if a matching method was found, false otherwise.
	 */
	protected function dispatch_method($var) {
		DLOG($var);
		$method = strtolower($this->get_method());
		$func = $method . "_" . $var;
		if (is_callable(array(
				$this,
				$func
		))) {
			$this->$func();
			return true;
		}
		if (is_callable(array(
				$this,
				$var
		))) {
			$this->$var();
			return true;
		}
		return false;
	}

	/**
	 * Executes controller methods depending on request type.
	 */
	public function run() {
		DLOG();
		DLOG(var_export($_REQUEST, true));
		$this->__redirect = "";
		$this->__db = DbAccess::get_instance($this->dba());
		if (!isset($this->__view)) {
			$this->__view = new View();
		}
		try {
			switch ($this->get_method()) {
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
			switch ($this->get_method()) {
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
			switch ($this->get_method()) {
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
		} catch (Exception $e) {
			$this->run_exception_handler($e);
		}

		// If we have a redirect, we don't want the current template(s) to be generated.
		if ($this->__redirect == "") {
			$this->__ob = $this->__view->render($this->__caching);
			if (isset(self::$__rbrc)) {
				self::$__rbrc->put($this->__ob);
			}
		}
	}

	/**
	 * Run the exception handler method
	 *
	 * @param Exception $e
	 *        	the exception
	 */
	private function run_exception_handler($e) {
		DLOG("Exception " . $e->getCode() . " '" . $e->getMessage() . "'");
		if ($e instanceof RedirectException) {
			$this->response_code(is_numeric($e->getCode()) ? $e->getCode() : 301);
			if ($e->is_internal()) {
				$this->__redirect = $e->getMessage();
				DLOG("INTERNAL REDIRECT: " . $this->__redirect);
			} else {
				header("Location: " . $e->getMessage());
			}
			throw $e;
		} else {
			try {
				$this->response_code(is_numeric($e->getCode()) ? $e->getCode() : 500);
				$this->exception_handler($e);
			} catch (RedirectException $e0) {
				if ($e0->is_internal()) {
					$this->__redirect = $e0->getMessage();
					DLOG("INTERNAL REDIRECT: " . $this->__redirect);
				} else {
					header("Location: " . $e0->getMessage());
				}
				throw $e0;
			} catch (Exception $e1) {
				$this->__ob = $this->__view->render($this->__caching);
				throw $e1;
			}
			$this->__ob = $this->__view->render($this->__caching);
		}
	}

	/**
	 * Returns the output buffer of the current controller
	 *
	 * @return String Rendered content
	 */
	public function get_ob() {
		DLOG();
		return $this->__ob;
	}

	/**
	 *
	 * @param String $mime_type
	 *        	the mime type of the current controllers output.
	 */
	protected function set_mime_type($mime_type) {
		DLOG($mime_type);
		$this->__mime_type = $mime_type;
	}
	
	protected function set_caching($caching = CACHING_ENABLED) {
		DLOG($caching);
		$this->__caching = $caching;
	}

	protected function get_db() {
		DLOG();
		return $this->__db;
	}

	/**
	 */
	public function get_mime_type() {
		DLOG("Return: " . $this->__mime_type);
		return $this->__mime_type;
	}

	public function get_view() {
		DLOG();
		return $this->__view;
	}

	public function get_redirect() {
		DLOG();
		return $this->__redirect;
	}

}
