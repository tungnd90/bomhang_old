<?php
require_once 'ModelBase.php';

class InvestModel extends ModelBase
{
	protected $_name = 'invests';
	protected $_key = 'id';
	
	function add($data) {
		$sql  = "INSERT INTO $this->_name($this->_key,name,market,min_price,max_step,step,coin,created) VALUES (NULL,?,?,?,?,?,?,?,?)";
		$values 	= array($data['name'],$data['market'],$data['min_price'],$data['max_step'],$data['step'], $data['coin'], time());
		
		$db = $this->getMasterDb();
		$db->execute($sql,$values);
		return;
		//return $db->getLastInsertId();
	}
	
	function edit($data) {
		$sql = "UPDATE $this->_name SET name = ?,market = ?, min_price = ?,max_step = ?,step = ?,coin = ? WHERE id = ?;";
        $values 	= array($data['name'],$data['market'],$data['min_price'],$data['max_step'],$data['step'], $data['coin'], $data['id']);

		$db = $this->getMasterDb();
		$db->execute($sql,$values);
		return;
	}

}
?>