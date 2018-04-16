<?php
class Controller {

  protected $_controller;
  protected $_action;
  public $view;

  function __construct($controller, $action) {

	$this->_controller = $controller;
	$this->_action = $action;

	$this->view = new View($controller,$action);
	$this->view->_actioncontroller =& $this;
  }

  /**
  * Cache region
  */
  function setCache($key,$data,$lifeTime=1200){
	$cache = Registry::get('memcache');
	$cache->set($key,$data,$lifeTime);
  }

  function getCache($key){
	$cache = Registry::get('memcache');
	return $cache->get($key);
  }

  function delCache($key){
	$cache = Registry::get('memcache');
	return $cache->delete($key);
  }

  /**
  * Request region
  */
  public function getParam($keyName, $default = null) {
	if (isset($_GET[$keyName])) {
	  return $_GET[$keyName];
	} elseif (isset($_POST[$keyName])) {
	  return $_POST[$keyName];
	}

	return $default;
  }

  public function getPost($keyName,$default=null){
	if (isset($_POST[$keyName])) {
	  return $_POST[$keyName];
	}

	return $default;
  }

  public function getParams(){

	return array_merge($_GET,$_POST);
  }

  public function getMethod(){

	return $_SERVER['REQUEST_METHOD'];
  }

  public function isPost() {
	if ('POST' == $this->getMethod()) {
	  return true;
	}

	return false;
  }

  public function isGet() {
	if ('GET' == $this->getMethod()) {
	  return true;
	}

	return false;
  }

  public function getHeader($header)
  {
	// Try to get it from the $_SERVER array first
	$temp = 'HTTP_' . strtoupper(str_replace('-', '_', $header));
	if (!empty($_SERVER[$temp])) {
	  return $_SERVER[$temp];
	}

	// This seems to be the only way to get the Authorization header on
	// Apache
	if (function_exists('apache_request_headers')) {
	  $headers = apache_request_headers();
	  if (!empty($headers[$header])) {
		return $headers[$header];
	  }
	}

	return false;
  }

	public function isXmlHttpRequest()
	{
		return ($this->getHeader('X_REQUESTED_WITH') == 'XMLHttpRequest');
	}

	function __destruct() {
		//$this->view->render();
	}
  
	function debug($array) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';

		return;
	}
  
	function redirect($url, $status = null) {
		if (!empty($status)) {
			$codes = array(
				100 => 'Continue',
				101 => 'Switching Protocols',
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				307 => 'Temporary Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Time-out',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Large',
				415 => 'Unsupported Media Type',
				416 => 'Requested range not satisfiable',
				417 => 'Expectation Failed',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Time-out'
			);
			if (is_string($status)) {
				$codes = array_flip($codes);
			}

			if (isset($codes[$status])) {
				$code = $msg = $codes[$status];
				if (is_numeric($status)) {
					$code = $status;
				}
				if (is_string($status)) {
					$msg = $status;
				}
				$status = "HTTP/1.1 {$code} {$msg}";
			} else {
				$status = null;
			}
		}
  
		if (!empty($status)) {
			header($status);
		}
		if ($url !== null) {
			header('Location: ' . $url);
		}
	}
	
	function getIpAddress()
	{
		if (!empty($_SERVER['HTTP_CLIENT_IP']))  //check ip from share internet
		{
		  $ip=$_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   
		//to check ip is pass from proxy
		{
		  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
		  $ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	function go_back($default = null) {
		$url = (empty($_SERVER['HTTP_REFERER']) ) ? "/" : $_SERVER['HTTP_REFERER'];
		
		header('Location: ' . $url);
	}
	
	function curPageURL() {
		$pageURL = 'http';
		
		if (!empty($_SERVER['HTTPS']) || @$_SERVER['HTTPS'] == 'on') {
			$pageURL .= "s";
		}
		
		$pageURL .= "://";
		
		if ($_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		} else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		}
		
		return $pageURL;
	}

}
