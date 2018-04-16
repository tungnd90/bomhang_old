<?php
require_once 'ModelBase.php';

class CategoryModel extends ModelBase
{

	protected $_name = 'sp_categories';
	protected $_key = 'cid';

	public function getAll($fields = "*"){
		$sql = "SELECT $fields FROM $this->_name ORDER BY position";
		$db = $this->getSlaveDb();
		return $db->getResult($sql);
	}
	
	public function add($data) {
		
		$db = $this->getSlaveDb();
		$db_m = $this->getMasterDb();
			
		// neu la category cha, position se la vi tri cuoi cung
		if ($data['parent_id'] == 0) {
			$sql = "SELECT max(position) as max FROM $this->_name";
			$max = $db->getValue($sql);
		}
		else {
			// neu la category con, update lai cac position de hien thi cho dung
			
			$sql = "SELECT max(position) as max FROM $this->_name WHERE parent_id = ? OR $this->_key = ?";
			$max = $db->getValue($sql,array($data['parent_id'],$data['parent_id']));
			
			// cau lenh sql dung de update lai position cua cac category co position sau category se duoc them vao. Moi position se + them 1.
			$update = "UPDATE $this->_name SET position = (position+1) WHERE position > ?";
			$db_m->execute($update, $max);
		}
		
		$position = $max + 1;

		$sql_add  = "INSERT INTO $this->_name(cid,name,parent_id,position,description,type, vip_action,glink,m_title,m_desc, m_key) VALUES (NULL,?,?,?,?,?,?,?,?,?,?)";
		$values 	= array($data['name'],$data['parent_id'],$position,$data['description'],$data['type'],$data['vip_action'],$data['glink'],$data['m_title'],$data['m_desc'],$data['m_key']);
		
		$db_m->execute($sql_add,$values);
	
		return;
	}
	
	public function edit($data) {
		$db_m = $this->getMasterDb();
		
		$values = array();
		
		$sql = "UPDATE $this->_name SET ";

		if (!empty($data['parent_id'])) {
			$db = $this->getSlaveDb();
			
			/*
			// neu la category con, update lai cac position de hien thi cho dung
			
			$sql = "SELECT max(position) as max FROM $this->_name WHERE parent_id = ?";
			$max = $db->getValue($sql,$data['parent_id']);

			if (empty($max)) {
				$sql = "SELECT position as max FROM $this->_name WHERE $this->_key = ?";
				$max = $db->getValue($sql,$data['parent_id']);
			}
			
			// cau lenh sql dung de update lai position cua cac category co position sau category se duoc them vao. Moi position se + them 1.
			$update = "UPDATE $this->_name SET position = (position+1) WHERE position > ?";
			$db_m->execute($update, $max);
		*/
			
			
			$sql_count = "SELECT max(position) as max FROM $this->_name WHERE parent_id = ? OR $this->_key = ?";
			$max = $db->getValue($sql_count,array($data['parent_id'],$data['parent_id']));
			
			// cau lenh sql dung de update lai position cua cac category co position sau category se duoc them vao. Moi position se + them 1.
			$update = "UPDATE $this->_name SET position = (position+1) WHERE position > ?";
			$db_m->execute($update, $max);
			
			$position = $max + 1;

			$sql .= "parent_id = ? ,";
			$sql .= "position = ? ,";
			$values[] = $data['parent_id'];
			$values[] = $position;
		}
		
		$sql .= "name = ? , ";
		$sql .= "description = ? , ";
		$sql .= "type = ? , ";
		$sql .= "vip_action = ?  , ";
		$sql .= "glink = ?  , ";
		$sql .= "m_title = ?  , ";
		$sql .= "m_desc = ?  , ";
		$sql .= "m_key = ? ";
		
		$values[] = $data['name'];
		$values[] = $data['description'];
		$values[] = $data['type'];
		$values[] = $data['vip_action'];
		$values[] = $data['glink'];
		$values[] = $data['m_title'];
		$values[] = $data['m_desc'];
		$values[] = $data['m_key'];
		
		$sql .= "WHERE cid = ?";
		$values[] = $data['cid'];

		$db_m->execute($sql,$values);

	}
	
	public function getMainList($group_id = 0, $vip = "") {
		$type = "";
		if ($group_id != 1 && $group_id != 2) {
			$type = " AND type = 0 ";
		}
	
		$sql 	= "SELECT * FROM $this->_name WHERE 1=1 $type $vip ORDER BY position";
		$db 		= $this->getSlaveDb();
		return $db->getResult($sql);
	}
	
	public function getToArray() {
		$find = "cid, name";

		$sql 		= "SELECT $find FROM $this->_name ORDER BY position";
		$db 		= $this->getSlaveDb();
		$data 	= $db->getResult($sql);
		
		$arr = array();
		foreach ($data as $d) {
			$arr[$d->cid] = $d->name;
		}
		return $arr;
	}
	
	public function getList($prefix = "_", $all = false, $condition = "") {
		$find = "cid, name, parent_id";
		if ($condition) $find = $condition;
	
		$sql 		= "SELECT $find FROM $this->_name ORDER BY position";
		$db 		= $this->getSlaveDb();
		$data 	= $db->getResult($sql);
		
		$array 	= array();
		$level	= 0;
		$last_parent = 0;
		$list_parent = array();
		
		foreach ($data as $da) {
			$key 		= $da->cid;
			$value 	= "";
			
			if ($da->parent_id == 0)
			{
				$level	= 0;
				$last_parent = 0;
				$list_parent = array();
			}
			else
			{
					if ( $da->parent_id != $last_parent ) 
					{
						$list_parent[] = $da->parent_id;
						$list_parent = array_unique($list_parent);
						
						if ( $da->parent_id > $last_parent ) 
						{
							$level++;
						}
						else {
							for ($i = count($list_parent)-1 ; $i >= 0; $i-- ) {
								if ($da->parent_id != @$list_parent[$i]) $level--;
							}
						}
						
					}
					
					$last_parent = $da->parent_id;
					
					for ($i = 0; $i < $level; $i++) 
					{
						$value .= $prefix;
					}
			}
			
			$value 	.= $da->name;
			
			if ($all) {
				$da->name = $value;
				$array[] = $da;
			}
			else {
				$array[$key] = $value;
			}
		}

		return $array;
	}

	public function getByParent($cid) {
		$sql = "SELECT * FROM $this->_name WHERE parent_id = ?";
		$db = $this->getSlaveDb();
		return $db->getResult($sql, $cid);
	}
	
	public function update_last($data, $e = null) {
		$values = array();
		$values[] = $data['last_topic_id'];
		$values[] = $data['last_topic_name'];
		$values[] = $data['last_user_post'];
		$values[] = $data['last_user_id_post'];
		$values[] = $data['updated'];
		
		$count = '';
		if (!empty($e)) {
			if ($e == 'topic') {
				$count = " , total_post = (total_post+1) ";
			}
			else if ($e == 'move') {
				$count = " , total_post = (total_post-1) ";
			}
		}
		
		$values[] = $data['cid'];
		$values[] = (empty($data['parent_id'])) ? 0 : $data['parent_id'];

		$db_m = $this->getMasterDb();
		$sql = "UPDATE $this->_name SET last_topic_id = ? , last_topic_name = ?, last_user_post = ? , last_user_id_post = ?, updated = ? $count  WHERE $this->_key IN ( ? , ? )";
		$db_m->execute($sql,$values);
	}
	
	public function total_down($data) {
		$db_m = $this->getMasterDb();
		$sql = "UPDATE $this->_name SET total_post = (total_post-1)  WHERE $this->_key IN ( ? , ? )";
		
		$values = array();
		$values[] = $data['cid'];
		$values[] = (empty($data['parent_id'])) ? 0 : $data['parent_id'];
		$db_m->execute($sql,$values);
	}
	
	public function total_up($data) {
		$db_m = $this->getMasterDb();
		$sql = "UPDATE $this->_name SET total_post = (total_post+1)  WHERE $this->_key IN ( ? , ? )";
		
		$values = array();
		$values[] = $data['cid'];
		$values[] = (empty($data['parent_id'])) ? 0 : $data['parent_id'];
		$db_m->execute($sql,$values);
	}
	
	function getCategoryTreeIDs($id) {
		$sql = "SELECT cid, name, parent_id FROM $this->_name WHERE cid= ? LIMIT 1";
		$db = $this->getSlaveDb();
		$row = $db->getResult($sql,$id);

		$path = array();
		$path[] = $row[0]->cid ."|||". $row[0]->name."|||".$row[0]->parent_id;
		if ($row[0]->parent_id > 0 ) {
			$path = array_merge($this->getCategoryTreeIDs($row[0]->parent_id), $path);
		}
		
		return $path;
	}
	
	public function getVipAction($cid) {
		$sql = "SELECT vip_action FROM $this->_name WHERE $this->_key = ?";
		$db = $this->getSlaveDb();
		return $db->getValue($sql, $cid);
	}
	
	public function reset_total($cid = null) {
		$where = "";
		if (!empty($cid)) {
			$where = "WHERE $this->_key = ?";
		}
	
		$sql = "UPDATE  `t2f_categories` SET  `last_topic_id` =  '0', `last_user_id_post` =  '0', `total_post` =  '0', `last_topic_name` =  '', `last_user_post` =  '' $where";
		$db_m = $this->getMasterDb();
		$db_m->execute($sql,$cid);
	}
	
	public function update_last_title($data) {
		$sql = "UPDATE  `t2f_categories` SET  `last_topic_name` =  ? WHERE `last_topic_id` = ? ";
		$values = array($data['value'],$data['id']);
		
		$db_m = $this->getMasterDb();
		$db_m->execute($sql,$values);
	}
}