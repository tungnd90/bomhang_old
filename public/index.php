<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('FW_PATH', ROOT . DS . 'lib' . DS . 'framework' . DS);
define('APP_LIB', ROOT . DS . 'application' . DS . 'lib' . DS);
define('APP_PATH',ROOT.DS.'application' . DS);
define('PUB_PATH',ROOT.DS.'public' . DS);

//include website preferences: view mode, debug mode v.v...
require_once(ROOT . DS . 'config' . DS . 'site_prefs.inc');

if (DBG_XHPROF || DBG_CUSTOM || DISPLAY_DEBUG || COLLECT_DEBUG_INFO)
	require_once 'dbg_header.php';

require_once ROOT . DS . 'application' . DS . 'bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->setup();
$bootstrap->run();

if (DBG_XHPROF || DBG_CUSTOM || DISPLAY_DEBUG || COLLECT_DEBUG_INFO)
	require_once 'dbg_footer.php';
