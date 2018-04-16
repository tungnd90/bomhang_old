<?php
require_once 'ModelBase.php';

class BannerModel extends ModelBase
{
	protected $_name = 't2f_banners';
	protected $_key = 'bid';

	public function add($data) {
		$sql  		= "INSERT INTO $this->_name($this->_key, name, filename, created, url) VALUES (NULL,?,?,?,?)";
		$values 	= array($data['name'], $data['filename'],$data['created'],$data['url']);
	
		$db = $this->getMasterDb();
		$db->execute($sql,$values);
		
		return true;
	}
	
	public function edit($data) {
		$db_m = $this->getMasterDb();
		$values = array();
		
		$sql = "UPDATE $this->_name SET ";
		
		if (!empty($data['filename'])) {
			$sql .= "filename = ? , ";
			$values[] = $data['filename'];
		}
		
		$sql .= "name = ?  ,";
		$sql .= "url = ?  ";
		
		$values[] = $data['name'];
		$values[] = $data['url'];
		
		$sql .= "WHERE bid = ?";
		$values[] = $data['bid'];

		$db_m->execute($sql,$values);
		
		return true;
	}
	
}
?>