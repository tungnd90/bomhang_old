<?php
class View {

  protected $_variables = array();
  protected $_controller;
  public $_actioncontroller;
  protected $_action;
  protected $_rendered = false;
  protected $_layout = 'default';

  protected $_title = '';
  protected $_assets = array('js'=>array(),'css'=>array());

  protected $_layout_path;
  protected $_view_path;

  function __construct($controller,$action, $layout_path = '', $view_path = '') {
	$this->_controller = $controller;
	$this->_action = $action;

	if ($layout_path == '')
		$this->_layout_path = ROOT . DS . 'application' . DS . 'layouts' ;
	else
		$this->_layout_path = $layout_path;

	if ($view_path == '')
		$this->_view_path = ROOT . DS . 'application' . DS . 'views';
  }

	public function setLayoutPath($layout_path = '')
	{
		$this->_layout_path = $layout_path;
	}

	public function setViewPath($view_path = '')
	{
		$this->_view_path = $view_path;
	}

  public function getActionController(){
	return $this->_actioncontroller;
  }

  public function __set($name,$value) {
	$this->_variables[$name] = $value;
  }

  public function __get($name){
	if (array_key_exists($name,$this->_variables))
	  return $this->_variables[$name];
	else
	  return null;
  }

  public function __isset($name){
	return isset($this->_variables[$name]);
  }

  public function setLayout($layoutName){
	$this->_layout = $layoutName;
  }

  public function disableLayout(){
	$this->setLayout('');
  }

  public function setNoRender(){
	$this->_rendered = true;
  }

  public function setTitle($title){
	$this->_title = $title;
  }

  public function getTitle(){
	return $this->_title;
  }

  public function headScript(){
	$h = '';
	foreach ($this->_assets['js'] as $js)
	  $h.= "<script type='text/javascript' src='".$js."'></script>\n";
	return $h;
  }

  public function headStylesheet(){
	$h = '';
	foreach ($this->_assets['css'] as $css)

	  $h.= "<link href='".$css."' rel='stylesheet' type='text/css' />\n";
	return $h;
  }

  private function addAsset($type,$value){
	if (in_array($value,$this->_assets[$type]))
	  return false;
	else {
	  $this->_assets[$type][] = $value;
	  return true;
	}
  }

  private function removeAsset($type,$value){
	if (!in_array($value,$this->_assets[$type]))
	  return false;
	else {
	  unset($this->_assets[$type][$value]);
	  return true;
	}
  }

  public function addJavascript($value){
	return $this->addAsset('js',$value);
  }

  public function addStylesheet($value){
	return $this->addAsset('css',$value);
  }

  function partial($template){
	if (!isset($template)||$template=='')
	  return '';
	ob_start();

	include ($this->_view_path . DS . $template . '.phtml');
	$rs = ob_get_contents();

	ob_end_clean();

	return $rs;
  }

  /** Display Template **/
  function render($template='') {
	if ($this->_rendered)
	  return;

	ob_start();

	if ($template=='')
	  include ($this->_view_path . DS . $this->_controller . DS . $this->_action . '.phtml');
	else
	  include ($this->_view_path . DS . $template . '.phtml');
	$this->content = ob_get_contents();

	ob_end_clean();

	if ($this->_layout!='')
	  include ($this->_layout_path . DS . $this->_layout. '.phtml');
	else
	  echo $this->content;

	$this->_rendered = true;
  }
  
  function element($name)
  {
		$file_name 	= $name . '.phtml';
		$path 		= $this->_view_path . DS . 'elements' . DS . $file_name;
		
		if (file_exists($path)) {
			include_once($path);
		}
		else {
			echo "ERROR: \"$path\" doesn't exists.";
		}
		return;
  }
  
	function url($url) {
		$http = (empty($_SERVER['HTTPS'])) ? "http://" : "https://";
		$url = str_replace(array("http://","https://"),"",$url);
		return $http . $url;
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
  
	function debug($array) {
		echo '<pre>';
		print_r($array);
		echo '</pre>';

		return;
	}
}
