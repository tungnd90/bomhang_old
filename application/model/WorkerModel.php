<?php
require_once 'ModelBase.php';

class WorkerModel extends ModelBase
{
	protected $_name = 'workers';
	protected $_key = 'id';
	
	function getByMdId($mdId) {
		$sql = "SELECT * FROM $this->_name WHERE md_id = ?";
		
		$db = $this->getSlaveDb();
		return $db->getResult($sql, $mdId);
	}
}
?>