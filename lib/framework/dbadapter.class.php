<?php

class DbAdapter{

  private $_pdoObject = null;

  protected $_fetchMode = PDO::FETCH_OBJ;
  protected $_connectionStr = null;
  protected $_driverOptions = array();

  private $_username = null;
  private $_password = null;

  private $_enable_profile = false;
  private $_profilers = null;

  public function __construct($db_params, $enable_profile=false){
	$this->_username = $db_params['username'];
	$this->_password = $db_params['password'];
	$this->_connectionStr = 'mysql:dbname='.$db_params['dbname'].';host='.$db_params['host'];
	if (isset($db_params['driver_options']))
	  $this->_driverOptions = $db_params['driver_options'];

	if ($enable_profile){
	  $this->_enable_profile = true;
	  $this->_profilers = array();
	}
  }

  private function _connect(){
	//|| $this->_password == null
	
	if ($this->_connectionStr == null || $this->_username == null )
	  throw new PDOException('Cannot connect to database with empty parameters');

	if ($this->_pdoObject != null)
	  return;

	if ($this->_enable_profile){
	  $stime = microtime();
	  $stime = explode(" ",$stime);
	  $stime = $stime[1] + $stime[0];
	}

	$this->_pdoObject = new PDO($this->_connectionStr, $this->_username,$this->_password,$this->_driverOptions);

	if ($this->_enable_profile){
	  $etime = microtime();
	  $etime = explode(" ",$etime);
	  $etime = $etime[1] + $etime[0];
	  $profile = new stdClass();
	  $profile->query = 'Connect';
	  $profile->params = '';
	  $profile->time = $etime - $stime;
	  $this->_profilers[] = $profile;
	}
  }

  public function getProfilers(){
	if ($this->_enable_profile)
	  return $this->_profilers;
	else
	  return false;
  }

  /**
   * Execute a statement and returns number of effected rows
   *
   * Should be used for query which doesn't return resultset
   *
   * @param   string  $sql    SQL statement
   * @param   array   $params A single value or an array of values
   * @return  integer number of effected rows
   */
  public function execute($sql, $params = array())
  {
	  $statement = $this->_query($sql, $params);
	  return $statement->rowCount();
  }

  /**
   * Execute a statement and returns a single value
   *
   * @param   string  $sql    SQL statement
   * @param   array   $params A single value or an array of values
   * @return  mixed
   */
  public function getValue($sql, $params = array())
  {
	  $statement = $this->_query($sql, $params);
	  return $statement->fetchColumn(0);
  }

  /**
   * Execute a statement and returns the first row
   *
   * @param   string  $sql    SQL statement
   * @param   array   $params A single value or an array of values
   * @return  array   A result row
   */
  public function getRow($sql, $params = array())
  {
	  $statement = $this->_query($sql, $params);
	  return $statement->fetch($this->_fetchMode);
  }

  /**
   * Execute a statement and returns row(s) as 2D array
   *
   * @param   string  $sql    SQL statement
   * @param   array   $params A single value or an array of values
   * @return  array   Result rows
   */
  public function getResult($sql, $params = array())
  {
	  $statement = $this->_query($sql, $params);
	  return $statement->fetchAll($this->_fetchMode);
  }

  public function getLastInsertId($sequenceName = "")
  {
	  return $this->_pdoObject->lastInsertId($sequenceName);
  }

  public function setFetchMode($fetchMode)
  {
	  $this->_connect();
	  $this->_fetchMode = $fetchMode;
  }

  public function getPDOObject()
  {
	  $this->_connect();
	  return $this->_pdoObject;
  }

  public function beginTransaction()
  {
	  $this->_connect();
	  $this->_pdoObject->beginTransaction();
  }

  public function commitTransaction()
  {
	  $this->_pdoObject->commit();
  }

  public function rollbackTransaction()
  {
	  $this->_pdoObject->rollBack();
  }

  public function setDriverOptions(array $options)
  {
	  $this->_driverOptions = $options;
  }

  /**
   * Prepare and returns a PDOStatement
   *
   * @param   string  $sql SQL statement
   * @param   array   $params Parameters. A single value or an array of values
   * @return  PDOStatement
   */
  private function _query($sql, $params = array())
  {
	  if($this->_pdoObject == null) {
		  $this->_connect();
	  }

	  if ($this->_enable_profile){
		$stime = microtime();
		$stime = explode(" ",$stime);
		$stime = $stime[1] + $stime[0];
	  }

	  $statement = $this->_pdoObject->prepare($sql, $this->_driverOptions);

	  if (! $statement) {
		  $errorInfo = $this->_pdoObject->errorInfo();
		  throw new PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
	  }

	  $paramsConverted = (is_array($params) ? ($params) : (array ($params )));

	  if ((! $statement->execute($paramsConverted)) || ($statement->errorCode() != '00000')) {
		  $errorInfo = $statement->errorInfo();
		  throw new PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
	  }

	  if ($this->_enable_profile){
		$etime = microtime();
		$etime = explode(" ",$etime);
		$etime = $etime[1] + $etime[0];
		$profile = new stdClass();
		$profile->query = $sql;
		$profile->params = print_r($params,true);
		$profile->time = $etime - $stime;
		$this->_profilers[] = $profile;
	  }

	  return $statement;
  }

  public function insert($table, array $bind)
  {
	if($this->_pdoObject == null) {
		$this->_connect();
	}

	// extract and quote col names from the array keys
	$cols = array();
	$vals = array();
	foreach ($bind as $col => $val) {
	  $cols[] = '`'.$col.'`';
	  $vals[] = $this->_pdoObject->quote($val);
	}

	// build the statement
	$sql = "INSERT INTO "
		 . $table
		 . ' (' . implode(', ', $cols) . ') '
		 . 'VALUES (' . implode(', ', $vals) . ')';

	// execute the statement and return the number of affected rows
	$result = $this->execute($sql);

	return $result;
  }

  public function update($table, array $bind, $where = '')
  {
	if($this->_pdoObject == null) {
		$this->_connect();
	}

	$set = array();
	foreach ($bind as $col => $val) {
	  $set[] = '`'.$col.'`' . ' = ' . $this->_pdoObject->quote($val);
	}

	if (is_array($where)) {
	  $where = implode(' AND ', $where);
	}

	// build the statement
	$sql = "UPDATE "
		 . $table
		 . ' SET ' . implode(', ', $set)
		 . (($where) ? " WHERE $where" : '');

	// execute the statement and return the number of affected rows
	$result = $this->execute($sql);

	return $result;
  }
}