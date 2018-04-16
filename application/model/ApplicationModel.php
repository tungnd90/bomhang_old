<?php
require_once 'ModelBase.php';

class ApplicationModel extends ModelBase
{
    protected $_name = 'os_applications';
	protected $_key = 'aid';
	
	public function getListApp() {
		$sql = 'SELECT aid, title, alias, tutorial_url FROM ' . $this->_name . ' WHERE approved = "Y" ORDER BY `order` ';
		$db = $this->getSlaveDb();
		return $db->getResult($sql);
	}
}
?>