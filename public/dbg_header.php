<?php

if (DBG_XHPROF &&  extension_loaded('xhprof'))
	xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);

//if (DBG_CUSTOM)
//{
	//$GLOBALS['_DBG'] = 1;
	$mem_start = memory_get_usage();
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = $mtime[1] + $mtime[0];
	$starttime = $mtime;
//}