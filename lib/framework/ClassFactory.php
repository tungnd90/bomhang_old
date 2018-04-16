<?php
if (!defined('APP_LIB') || !defined('ROOT'))
	die("ROOT and APP_LIB constant must be defined before");

class ClassFactory
{
	private static $_instance;

	private $_libs;
	private $_models;

	private function __construct()
	{
		$this->_libs = array();
		$this->_models = array();
	}

	public static function getInstance(){
		if (isset(self::$_instance)){
			return self::$_instance;
		}
		else
		{
			$className = __CLASS__;
			self::$_instance = new $className;
			return self::$_instance;;
		}
	}

	/**
	* Factory a model based on its name
	*
	* @param mixed $name
	*/
	public function get_model($name)
	{
		if (array_key_exists($name, $this->_models))
		{
			return $this->_models[$name];
		}
		else
		{
			$filename = ROOT . DS . 'application' . DS . 'model' . DS . $name . '.php';
			require_once $filename;
			$inst = new $name();
			$this->_models[$name]=$inst;
			return $inst;
		}
	}

	/**
	* factory a library class based on its class name and namespace
	*
	* @param mixed $name class name: e.g 'email'
	* @param mixed $ns namespace: e.g 'validator'
	*/
	public function get_lib($name, $ns = '')
	{
		$key = $ns . DS . $name;
		if (array_key_exists($key, $this->_libs))
		{
			return $this->_libs[$key];
		}
		else
		{
			$fileName = APP_LIB . DS . $key . '.php';
			require_once $fileName;
			$inst=new $name();
			$this->_libs[$key]=$inst;
			return $inst;
		}
	}
}