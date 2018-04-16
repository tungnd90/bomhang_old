<?php

//if DISPLAY_DEBUG, show debug information on web
define('DISPLAY_DEBUG', FALSE);

//collect debug information to database or not?
define('COLLECT_DEBUG_INFO', FALSE); //no use this time, always set to FALSE

//Active XHPROF or not?
define('DBG_XHPROF', TRUE);

//Active custom debug or not
define('DBG_CUSTOM', FALSE);

//render develop (default) view or compressed view? Possible value: dev/prod
define('RENDER_VIEW', 'dev');

define('ROUTER_DEFAULT_CONTROLLER', 'frontpages');
define('ROUTER_DEFAULT_ACTION', 'index');

// TungND
define('ADMIN', 1);
define('MOD', 2);
define('MEMBER', 3);

define('TITLE', 'Diễn đàn thapthanh.com');
define('TOPIC_LIST_LIMIT', 10);
define('REPLY_TOPIC_LIMIT', 10);
define('USER_LIST_LIMIT', 20);