<?php
require_once 'ModelBase.php';

class TopicModel extends ModelBase
{
	protected $_name = 'sp_topics';
	protected $_key = 'tid';
	
	function add($data) {
		$sql  = "INSERT INTO $this->_name($this->_key,category_id,title,content,created,position, image) VALUES (NULL,?,?,?,?,?,?)";
		$values 	= array($data['cid'],$data['title'],$data['content'],$data['created'],$data['position'],$data['image']);
		
		$db = $this->getMasterDb();
		$db->execute($sql,$values);
		return;
		//return $db->getLastInsertId();
	}
	
	function edit($data) {
		$sql = "UPDATE $this->_name SET category_id = ?,title = ?, content = ?,position = ?,image = ? WHERE tid = ?;";
		$values 	= array($data['cid'],$data['title'],$data['content'],$data['position'],$data['image'],$data['tid']);

		$db = $this->getMasterDb();
		$db->execute($sql,$values);
		return;
	}
	
	function getByCategoryId($cid, $page = 1, $limit = 10) {
		$start = ($page - 1)*$limit;
		//$limit+=1;
	
		$sql = "SELECT * FROM $this->_name WHERE category_id = ? AND status = 1 ORDER BY position LIMIT $start,$limit";
		
		$db = $this->getSlaveDb();
		return $db->getResult($sql, $cid);
	}
	
	function hiddenTopic($tid) {
		$sql = "UPDATE  $this->_name SET  `status` =  '0' WHERE  $this->_key = ? LIMIT 1";
		$db = $this->getMasterDb();
		$db->execute($sql,$tid);
	}
	
	function moveTopic($data) {
		$sql = "UPDATE  $this->_name SET  `category_id` =  ? WHERE  $this->_key = ? LIMIT 1";
		$values  = array($data['cid'],$data['tid']);
		
		$db = $this->getMasterDb();
		$db->execute($sql,$values);
	}
	
	function update_topic_cm($data) {
		$sql = "UPDATE  $this->_name SET  total_reply =  (total_reply +1), user_update_name = ?, user_update_id = ?, updated = ? WHERE  $this->_key = ? LIMIT 1";
		$values  = array($data['user_update_name'],$data['user_update_id'],$data['updated'],$data['tid']);
		$db = $this->getMasterDb();
		$db->execute($sql,$values);
	}
	
	function update_topic($id, $e) {
		$update = "";
		if ($e == 'total_reply') {
			$update = " , updated = '". time() . "'";
		}
		$sql = "UPDATE  $this->_name SET  $e =  (`$e` +1) $update WHERE  $this->_key = ? LIMIT 1";

		$db = $this->getMasterDb();
		$db->execute($sql,$id);
	}
	
	function get_last_topic($cid) {
		$sql_count_all 	= "SELECT count(*) FROM $this->_name WHERE category_id IN (SELECT id FROM t2f_categories WHERE cid = ?) AND status = 1 ";
		$sql_count_one 	= "SELECT count(*) FROM $this->_name WHERE category_id IN ? AND status = 1 ";
		$sql = "SELECT tid, user_update_name, title, user_update_id FROM $this->_name WHERE category_id IN (SELECT id FROM t2f_categories WHERE cid = ?) AND status = 1 ORDER BY updated desc LIMIT 1;";
	}
	
	function empty_data() {
		$sql = "TRUNCATE TABLE $this->_name ; TRUNCATE TABLE t2f_replies; TRUNCATE TABLE t2f_likes; TRUNCATE TABLE t2f_polls; TRUNCATE TABLE t2f_answers; TRUNCATE TABLE t2f_votes; ";
		
		$db = $this->getMasterDb();
		$db->execute($sql);
	}
}
?>