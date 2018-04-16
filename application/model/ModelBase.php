<?php

class ModelBase {

	protected $_name = '';
	protected $_key = '';

	protected $_where = '';
	protected $_order = '';
	protected $_where_arg = array();
	protected $_limit = '';
	protected $_selected_columns = '';

	protected function getMasterDb()
	{
		return Registry::get('master_db');
	}
	
	protected function getTamtayCore()
	{
		return Registry::get('tamtay_core');
	}

	protected function getCoreDb()
	{
		return Registry::get('core_db');
	}

	protected function getSlaveDb($key='')
	{
		$slaves = Registry::get('slave_dbs');
		if ($key=='')
		$key = array_rand($slaves);

		return $slaves[$key];
	}
	
	public function delete_by_key($ids)
	{
		$sql = 'DELETE FROM ' . $this->_name . ' WHERE ' . $this->_key . ' IN (';
		$db = $this->getMasterDb();
		if (!is_array($ids))
			$ids = array($ids);
		$itm = array_fill(0, count($ids),'?');
		$sql .= implode(',', $ids) . ')';
		return $db->execute($sql, $ids);
	}

	public function get_by_keys($ids)
	{
		$sql = 'SELECT * FROM ' . $this->_name . ' WHERE ' . $this->_key . ' IN (';
		$db = $this->getMasterDb();
		if (!is_array($ids))
			$ids = array($ids);
		$itm = array_fill(0, count($ids),'?');
		$sql .= implode(',', $ids) . ')';
		return $db->getResult($sql, $ids);
	}

	public function reset_query()
	{
		$this->_where_arg = array();
		$this->_where = '';
		$this->_order = '';
		$this->_limit = '';
		$this->_selected_columns = '';
	}

	public function add_where($key, $value, $opt='=')
	{
		if ($this->_where != '')
			$this->_where .= ' AND ';
		$this->_where .= ' '.$key.$opt.'? ';
		$this->_where_arg[] = $value;
		return $this;
	}

	public function add_order($key, $order='')
	{
		if ($this->_order != '')
			$this->_order .= ', ';
		$this->_order .= $key.' '.$order;
		return $this;
	}

	public function add_limit($start, $limit)
	{
		$start = (int)$start;
		$limit = (int)$limit;
		$this->_limit = " LIMIT $start, $limit";
		return $this;
	}

	public function add_columns($columns)
	{
		if ($this->_selected_columns != '')
			$this->_selected_columns .= ', ';
		$this->_selected_columns .= $columns;
	}

	public function get_select_query($for_count = false, $alias = '', $start = 0, $limit = 0)
	{
		if ($limit != 0)
			$this->add_limit($start, $limit);

		$cols = ' * ';
		if ($this->_selected_columns != '')
			$cols = $this->_selected_columns;

		$sql = 'SELECT ' . $cols . ' FROM ' . $this->_name;
		if ($alias != '')
			$sql .= " $alias";

		if ($this->_where != '')
			$sql .= ' WHERE '.$this->_where;

		if (!$for_count)
		{
			if ($this->_order != '')
				$sql .= ' ORDER BY ' . $this->_order;

			if ($this->_limit != '')
				$sql .= $this->_limit;
		}

		return $sql;
	}

	public function update_by_key($key, $data)
	{
		$key = (int)$key;
		return $this->update($data, $this->_key .'=' . $key);
	}

	public function select_row($alias = '')
	{
		$sql = $this->get_select_query(false, $alias);
		$db = $this->getSlaveDb();
		return $db->getRow($sql, $this->_where_arg);
	}

	public function select($alias = '', $start = 0, $limit = 0)
	{
		$sql = $this->get_select_query(false, $alias, $start, $limit);
		$db = $this->getSlaveDb();
		return $db->getResult($sql, $this->_where_arg);
	}

	public function get_by_key($id=0, $fields = "*")
	{
		$sql = 'SELECT ' . $fields . ' FROM ' . $this->_name . ' WHERE ' . $this->_key . '=?';
		$db = $this->getSlaveDb();
		return $db->getRow($sql, $id);
	}

	public function count_select($alias = '')
	{
		$this->_selected_columns = ' count(*) as c ';
		$sql = $this->get_select_query(true, $alias);
		$db = $this->getSlaveDb();
		return $db->getValue($sql, $this->_where_arg);
	}

	public function selectAll($order = '', $start = 0, $limit = 0, $conditions = '', $fields = "*")
	{
		$sql = "SELECT $fields FROM " . $this->_name;
		if ($conditions != "") 
			$sql .= ' WHERE ' . $conditions;
		if ($order != '')
			$sql .= ' ORDER BY ' . $order;
		if ($limit != 0)
			$sql .= " LIMIT $start, $limit" ;

		$db = $this->getSlaveDb();

		return $db->getResult($sql);
	}

	public function count($conditions = "")
	{
		$where = ($conditions) ? "WHERE " . $conditions : "";
		$sql = 'SELECT count(*) FROM '.$this->_name . ' ' . $where;

		$db = $this->getSlaveDb();

		return $db->getValue($sql);
	}

	public function update($data, $where = '')
	{
		$db = $this->getMasterDb();
		return $db->update($this->_name, $data, $where);
	}
	
	public function delete_by_id($id) {
		$db = $this->getMasterDb();
		$sql = "DELETE FROM $this->_name WHERE $this->_key = ?";
		$db->execute($sql,$id);
	}
	
	public function get_a_field($field, $id) {
		$sql = "SELECT $field FROM $this->_name WHERE $this->_key = ?";
		$db = $this->getSlaveDb();

		return $db->getValue($sql,$id);
	}
	
	public function update_data($data,$obj) {
		$sql = "UPDATE $this->_name SET $obj = ?  WHERE $this->_key = ?";
		$values 	= array($data['value'],$data['id']);

		$db = $this->getMasterDb();
		$db->execute($sql,$values);

		return true;
	}
	
	public function last_record($fields = "*", $order = "id desc") {
		$sql = "SELECT $fields FROM $this->_name ORDER BY $order LIMIT 1";
		$db = $this->getSlaveDb();

		return $db->getResult($sql);
	}
}