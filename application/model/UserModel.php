<?php
require_once 'ModelBase.php';

class UserModel extends ModelBase
{
	protected $_name = 'users';
	protected $_key = 'uid';
	
	public function getUserLogin($data) {
		$sql = "SELECT uid, username, status FROM $this->_name WHERE username = ? AND password = ? LIMIT 1";

		$db = $this->getSlaveDb();
		return $db->getRow($sql, array($data['username'], $data['password']));
	}

	public function add($data) {
		$sql  		= "INSERT INTO $this->_name($this->_key, username, password, group_id, status, created, md_name, md_id, md_key) VALUES (NULL,?,?,1,1,?,?,?,?)";
		$values 	= array( $data['username'],$data['password'],$data['created'],$data['md_name'],$data['md_id'],$data['md_key']);
	
		$db = $this->getMasterDb();
		$db->execute($sql,$values);
	}
	
	public function getVipPoint($uid) {
		$sql = "SELECT vip_point FROM users WHERE $this->_key = ? ";
		$db = $this->getTamtayCore();
		return $db->getValue($sql, $uid);
	}

	public function total_change($uid, $obj, $e = null) {
		$count = "$obj = ($obj+1)";
		if (!empty($e) && $e == "down") {
			$count = "$obj = ($obj-1)";
		}
	
		$db_m = $this->getMasterDb();
		$sql = "UPDATE $this->_name SET $count WHERE $this->_key = ?";
		
		$db_m->execute($sql,$uid);
	}
	
	public function getListVipPoint($ids)
	{
		$sql = "SELECT uid, vip_point FROM users WHERE " . $this->_key . ' IN (';
		$db = $this->getTamtayCore();
		if (!is_array($ids))
			$ids = array($ids);
		$itm = array_fill(0, count($ids),'?');
		$sql .= implode(',', $ids) . ')';
		$lists = $db->getResult($sql, $ids);
		$arr = array();

		if (!empty($lists)) {
			foreach ($lists as $li) {
				$arr[$li->uid] = $li->vip_point;
			}
		}
		
		return $arr;
	}

    public function get_by_md_name($name = "")
    {
        $sql = 'SELECT md_id FROM users WHERE md_name = ?';
        $db = $this->getSlaveDb();
        return $db->getRow($sql, $name);
    }
}
?>