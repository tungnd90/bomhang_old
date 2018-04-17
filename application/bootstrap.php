<?php
$GLOBALS['_DBG'] = 1;
function show404($msg = '')
{
	header("HTTP/1.0 404 Not Found");
	if ( ! isset($GLOBALS['_DBG']) || $GLOBALS['_DBG'] != 1)
	{
		$msg = '';
	}
	die('<html><head><title>404 Not Found</title></head><body><h1>Not Found</h1><p><p>' . $msg . '</p><p>Please go <a href="javascript: history.back(1)">back</a> and try again.</p><hr /><p>Powered by: <a href="http://www.'.DOMAIN_NAME.'">'.DOMAIN_NAME.'</a></p></body></html>');
}

function showError($msg = '')
{
	header('HTTP/1.1 500 Internal Server Error');
	if ( ! isset($GLOBALS['_DBG']) || $GLOBALS['_DBG'] != 1)
	{
		$msg = '';
	}
	die('<html><head><title>500 Internal Server Error</title></head><body><h1>Internal Error</h1><p>' . $msg . '</p><hr /><p>Powered by: <a href="http://www.'.DOMAIN_NAME.'">' . DOMAIN_NAME . '</a></p></body></html>');
}

class Bootstrap
{

	public function __construct()
	{
		//require core framework
		require_once FW_PATH . DS . 'dbadapter.class.php';
		require_once FW_PATH . DS . 'cacheengine.class.php';
		//require_once FW_PATH . DS . 'sessionmanager.class.php';
		require_once FW_PATH . DS . 'router.class.php';
		require_once FW_PATH . DS . 'registry.class.php';
		require_once FW_PATH . DS . 'ClassFactory.php';
		require_once FW_PATH . DS . 'view.class.php';
		require_once FW_PATH . DS . 'controller.class.php';
	}

	public function setup()
	{
		$this->loadConfig();
		$this->setupEnvironment();
		$this->setupDatabase();
		session_start();
//		$this->setupMemcache();
		//$this->setupSession2();
		//$this->getSession();
	}

	private function loadConfig()
	{
		require ROOT . DS . 'config' . DS . 'const.inc';
		$config = require ROOT . DS . 'config' . DS . 'config.inc';
		Registry::set('config',$config);
	}

	private function setupEnvironment()
	{
		if (isset($GLOBALS['_DBG']) && $GLOBALS['_DBG'])
		{
			error_reporting(E_ALL);
			ini_set('display_errors','On');
			ini_set('display_startup_errors', 1);
			return;
		}

		$GLOBALS['_DBG'] = 0;
		$d = date("Ymd", time());
		$dbg = $d . "dbg";

		if (isset($_COOKIE['_DBG']))
		{
			if ($_COOKIE['_DBG'] == $dbg)
			{
				$GLOBALS['_DBG'] = 1;
			}
		}

		if (isset($_GET['_DBG']))
		{
			$config = Registry::get('config');
			$cookie_domain = $config['session']['cookie_domain'];
			if ($_GET['_DBG'] == $dbg && $GLOBALS['_DBG'] == 0)
			{
				$GLOBALS['_DBG'] = 1;
				setcookie("_DBG", $dbg, time() + 1800, "/", $cookie_domain); //debug for  the next 30 minutes
			}
			elseif ($GLOBALS['_DBG'] == 1 && $_GET['_DBG'] != $dbg) //trick to delete debug cookie
			{
				$GLOBALS['_DBG'] = 0;
				setcookie("_DBG", $dbg, time() - 1800, "/", $cookie_domain);
			}
		}

		if ($GLOBALS['_DBG'] == 1)
		{
			$GLOBALS['mem_start'] = memory_get_usage();
			$mtime = microtime();
			$mtime = explode(" ", $mtime);
			$mtime = $mtime[1] + $mtime[0];
			$GLOBALS['starttime'] = $mtime;
			error_reporting(E_ALL);
			ini_set('display_errors','On');
		}
		else
		{
			error_reporting(E_ERROR | E_WARNING | E_PARSE);
			ini_set('display_errors','Off');
			ini_set('log_errors', 'On');
		}

		ini_set('display_startup_errors', $GLOBALS['_DBG'] == 1);
	}

	private function setupDatabase()
	{
		$config = Registry::get('config');

		Registry::set('master_db', new DbAdapter($config['master_db'],$GLOBALS['_DBG']==1));
		unset($config['master_db']);

		Registry::set('session_db', new DbAdapter($config['session_db'],$GLOBALS['_DBG']==1));
		unset($config['session_db']);

		$slave_dbs = array();
		foreach ($config['slave_dbs'] as $key=>$value)
		{
			$slave_dbs[$key] = new DbAdapter($value,$GLOBALS['_DBG']==1);
		}
		Registry::set('slave_dbs',$slave_dbs);
		unset($config['slave_dbs']);

		Registry::set('config', $config);
	}

	private function setupMemcache()
	{
		$config = Registry::get('config');
		$memcache_params = $config['memcache'];
		unset($config['memcache']);
		Registry::set('config',$config);

		$cacheClass = new CacheEngine($memcache_params);
		Registry::set('memcache',$cacheClass);

		if ($GLOBALS['_DBG']==1)
		{
			$cacheStats = array();
			$cacheStats['hit'] = array();
			$cacheStats['miss'] = array();
			$cacheStats['set'] = array();
			Registry::set('cachestats',$cacheStats);
		}
	}

	private function dispatch($dispatch, $actionName)
	{
		if (method_exists($dispatch, "init"))
			$dispatch->init();

		call_user_func_array(array($dispatch, $actionName), array());
		call_user_func_array(array($dispatch->view, 'render'), array());
	}

	public function run()
	{
		$r = new Router();
		//$r->map('/tutorial/:id/:status', array('controller' => 'frontpages', 'action' => 'index'));

		$r->map('/categories', array('controller' => 'categories'));
		$r->map('/topics', array('controller' => 'topics'));
		$r->map('/articles', array('controller' => 'frontpages', 'action' => 'index', 'id'=>1));
		$r->map('/blog', array('controller' => 'frontpages', 'action' => 'index', 'id'=>2));
		$r->map('/view/:id', array('controller' => 'frontpages', 'action' => 'view'));
		$r->map('/category/:id', array('controller' => 'frontpages', 'action' => 'index'));
		$r->default_routes();
		
	
		if ($r->execute())
		{
			//Error if invalid Controller or Action Name
			if (!preg_match('#^[A-Za-z0-9_-]+$#', $r->controller_name) || !preg_match('#^[A-Za-z_][A-Za-z0-9_-]*$#', $r->action))
				show404('Invalid Controller / Action Name: ' . $r->controller_name . ' / ' . $r->action);

			$controllerName = $r->controller_name.'Controller';
			$actionName = ucfirst($r->action).'Action';

			 if (!@include_once(ROOT . DS . 'application' . DS . 'controller' . DS . $controllerName . '.php'))
				show404('Controller File not existed: ' . ROOT . DS . 'application' . DS . 'controller' . DS . $controllerName . '.php');

			$dispatcher = new $controllerName($r->controller, $r->action);

			if (!method_exists($dispatcher, $actionName))
				show404('Action "' . $actionName . '" not found in Controller "' . $controllerName . '"');

			$this->dispatch($dispatcher, $actionName);
		}
		else
			show404('No router found.');
	}
	
	function getSession() {
		$config = Registry::get('config');
		$session_options = $config['session'];
		
		if (!isset($_COOKIE[$session_options['name']])) {
			//$this->setupSession2();
		}
		else {
			$sid = $_COOKIE[$session_options['name']];
			
			$sessionManager = new SessionManager();
			$sessionManager->db = Registry::get('session_db');
			
			session_set_save_handler(
					array(&$sessionManager, 'open'),
					array(&$sessionManager, 'close'),
					array(&$sessionManager, 'read'),
					array(&$sessionManager, 'write'),
					array(&$sessionManager, 'destroy'),
					array(&$sessionManager, 'gc')
			);

			foreach($session_options as $key=>$value){
				ini_set("session.$key", $value);
			}
			
			session_start();

			unset($config['session']);
			Registry::set('config',$config);
		}
		
	}

	private function setupSession2()
	{
		$config = Registry::get('config');
		$session_options = $config['session'];

		$sessionManager = new SessionManager();
		$sessionManager->db = Registry::get('session_db');
		session_set_save_handler(
				array(&$sessionManager, 'open'),
				array(&$sessionManager, 'close'),
				array(&$sessionManager, 'read'),
				array(&$sessionManager, 'write'),
				array(&$sessionManager, 'destroy'),
				array(&$sessionManager, 'gc')
		);

		foreach($session_options as $key=>$value){
			ini_set("session.$key", $value);
		}

		if ($_SERVER['REQUEST_METHOD']=='POST')
		{
			if (isset($_POST[session_name()]))
			{
				$sid=$_POST[session_name()];
				$_COOKIE[session_name()]=$sid;
				session_id($sid);
			}
		}

		session_start();

		unset($config['session']);
		Registry::set('config',$config);
	}

	private function setupSession(){
	$config = Registry::get('config');
	$session_options = $config['session'];

	foreach($session_options as $key=>$value){
		ini_set("session.$key", $value);
	}


	$sessionManager = new SM_Memcache(Registry::get('memcache'));
	session_set_save_handler(
			array(&$sessionManager, 'open'),
			array(&$sessionManager, 'close'),
			array(&$sessionManager, 'read'),
			array(&$sessionManager, 'write'),
			array(&$sessionManager, 'destroy'),
			array(&$sessionManager, 'gc')
	);

	//Registry::set('session_save_handler',$sessionManager);

	/*
	if ($_SERVER['REQUEST_METHOD']=='POST')
	{
	  if (isset($_POST[session_name()]))
	  {
		$sid=$_POST[session_name()];
		$_COOKIE[session_name()]=$sid;
		session_id($sid);
	  }
	}
	*/

	session_start();

	unset($config['session']);
	Registry::set('config',$config);
  }

	public function renderDevel($totaltime, $mem_start, $mem_end)
	{
		echo '<pre>';
		echo '<br /><br />';
		echo '<table border="1px" cellpadding="0" cellspacing="0"><tr><th>Queries</th><th >Params</th><th>Time</th></tr>';

		$profilers = Registry::get('session_db')->getProfilers();
		if (is_array($profilers) && count($profilers)>0)
		{
			echo '<tr><td colspan="3" style="background:#FFF">Core Database</td></tr>';
			foreach($profilers as $profiler)
				echo '<tr><td>',$profiler->query,'</td><td>',($profiler->params!='')?var_dump($profiler->params):'&nbsp;','</td><td>',round($profiler->time,4),'</td></tr>';
		}

		$profilers = Registry::get('master_db')->getProfilers();
		if (is_array($profilers) && count($profilers)>0)
		{
			echo '<tr><td colspan="3" style="background:#FFF">Master Database</td></tr>';
			foreach($profilers as $profiler)
				echo '<tr><td>',$profiler->query,'</td><td>',($profiler->params!='')?var_dump($profiler->params):'&nbsp;','</td><td>',round($profiler->time,4),'</td></tr>';
		}

		$adapters = Registry::get('slave_dbs');
		foreach ($adapters as $key=>$value)
		{
			$profilers = $value->getProfilers();
			if (is_array($profilers) && count($profilers)>0)
			{
				echo '<tr><td colspan="3" style="background:#FFF">',$key,'</td></tr>';
				foreach($profilers as $profiler)
					echo '<tr><td>',$profiler->query,'</td><td>',($profiler->params!='')?var_dump($profiler->params):'&nbsp;','</td><td>',round($profiler->time,4),'</td></tr>';
			}
		}

		echo '</table>';
		if (!empty($cacheStats)) {
		$cacheStats = Registry::get('cachestats');
			foreach ($cacheStats as $key=>$value)
			{
				echo '<strong>Cache ',$key,': ',count($value),'</strong>';
				if (count($value)>0)
				{
					echo '<ul style="margin:0px">';
					foreach($value as $cacheKey)
					echo '<li>',$cacheKey,'</li>';
					echo '</ul>';
				}
				else
					echo '<br />';
			}
		}
		echo '<div style="font-weight: bold">This page was created in ' . number_format($totaltime, 4) . ' seconds</div>';
		echo '<div>The memory allocated is ' . number_format($mem_start/1024/1024, 2) . '/' . number_format($mem_end/1024/1024, 2) . '</div>';
		echo '</pre>';
	}
}
