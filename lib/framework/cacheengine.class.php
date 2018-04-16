<?php
class CacheEngine
{
	protected $_memcache;
	protected $_compress = 0;

	function __construct($params)
	{
		$connected = false;
		$this->_memcache = new Memcache();
		$servers = $params['servers'];
		foreach($servers as $server)
		{
			if ($this->_memcache->addServer($server['host'],$server['port'],$server['persistent']))
				$connected = true;
		}

		if (!$connected)
			throw new Exception('Cannot connect to MemCache Servers!');

		if (isset($params['compression'])&&$params['compression']==true)
			$this->_compress = MEMCACHE_COMPRESSED;
	}

	function set($key, $var, $expires = 3600)
	{
		if (!is_numeric($expires))
			$expires = strtotime($expires);

		if ($GLOBALS['_DBG']==1)
		{
			$cacheStats = Registry::get('cachestats');
			$cacheStats['set'][] = $key;
			Registry::set('cachestats',$cacheStats);
		}

		return $this->_memcache->set($key, $var, $this->_compress, time()+$expires);
	}

	function get($key)
	{
		$data = $this->_memcache->get($key);

		if ($GLOBALS['_DBG']==1)
		{
			$cacheStats = Registry::get('cachestats');
			if ($data)
				$cacheStats['hit'][] = $key;
			else
				$cacheStats['miss'][] = $key;
			Registry::set('cachestats',$cacheStats);
		}

		return $data;
	}

	function delete($key)
	{
		return $this->_memcache->delete($key);
	}
}
