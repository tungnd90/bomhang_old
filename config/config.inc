<?php
return array(
  'session_db' => array('host'     => 'localhost',
					 'username' => 'root',
					 'password' => '1',
					 'dbname'   => 'md',
					 'driver_options'  => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")),

  'master_db' => array('host'     => 'localhost',
					 'username' => 'root',
					 'password' => '1',
					 'dbname'   => 'md',
					 'driver_options'  => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")),

  'slave_dbs' => array(
	'slave_db1' => array('host'     => 'localhost',
                    'username' => 'root',
                    'password' => '1',
                    'dbname'   => 'md',
						 'driver_options'  => array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"))
  ),

  'memcache'=>array(
	'servers'=>array(
	  'server_1'=>array(
		'host'=>'localhost',
		'port'=>11211,
		'persistent' => true
	  )
	),
	'compression'=>false
  ),
  'session'=>array(
	'cookie_domain'=>'md.me',
	'name'=>'SESSa32639e00374d719e31425e62cf17ed31',
	'gc_probability'=>1,
	'gc_gc_maxlifetime'=>86400,
	'gc_divisor'=>5000
  )
);