<?php

if (isset($GLOBALS['_DBG']) && $GLOBALS['_DBG'])
{
	$mtime = microtime();
	$mtime = explode(" ",$mtime);
	$mtime = $mtime[1] + $mtime[0];
	$endtime = $mtime;
	$totaltime = ($endtime - $starttime);
	$mem_end=memory_get_usage();

	//Always display debug info if using custom debug
	$bootstrap->renderDevel($totaltime, $mem_start, $mem_end);
}

if (DBG_XHPROF &&  extension_loaded('xhprof'))
{
	$xhprof_data = xhprof_disable();
}

if (isset($xhprof_data) && DISPLAY_DEBUG)
{
	$profiler_namespace = 'newgame';  // namespace for your application

	define('XHPROF_ROOT', '/var/www/domains/xhprof');
	include_once XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
	include_once XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

	$xhprof_runs = new XHProfRuns_Default();
	$run_id = $xhprof_runs->save_run($xhprof_data, $profiler_namespace);

	// url to the XHProf UI libraries (change the host name and path)
	$profiler_url = sprintf('http://dev.xhprof.52la.vn/index.php?run=%s&source=%s', $run_id, $profiler_namespace);
	//echo '<a href="'. $profiler_url .'" target="_blank">Profiler output</a>';
	echo '<pre>', '<a href="', $profiler_url, '" target="_blank">Profiler output</a><br />', var_export($xhprof_data, true), '</pre>';
}
