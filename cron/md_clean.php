<?php
/*
 ********************************************************************
 * TASK: Convert data PhpBB forum to Tamtay Forum
 * AUTHOR: TungND
 * Created: 27-06-2013
 * Description: Convert category topic, reply, poll, poll vote in PhpBB to Tamtay forum
 ********************************************************************
 */
 
define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('HELPER_PATH', ROOT . DS . 'application' . DS . 'helpers' . DS);
$dbConfig = require_once('database_inc.php');

// connect to db phpBB
$connect	= mysql_connect('localhost', $dbConfig['md']['username'], $dbConfig['md']['password']) or die('Could not connect: ' . mysql_error());
$db			= mysql_select_db($dbConfig['md']['database'], $connect) or die('Could not select database.');


$exp = strtotime("-1 day", time());
mysql_query("DELETE FROM `stats` WHERE time < ".$exp);

mysql_close($connect);

die("DONE");



?>