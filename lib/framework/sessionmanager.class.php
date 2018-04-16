<?php
class SessionManager {
  public $db;
  public $session_enabled=true;

  public function init()
  {
	$this->session_enabled=true;
  }

  public function open($save_path, $name)
  {
	return true;
  }

  public function close()
  {
	return true;
  }

  public function read($id)
  {
	if (!$this->session_enabled) {
	  $user = $this->getAnonymousUser();
	  Registry::set('user', $user);
	  return "";
	}
	register_shutdown_function('session_write_close');
	$u=Registry::get('user');
	
	if (!isset($_COOKIE[session_name()])) {
	  $user = $this->getAnonymousUser();
	  Registry::set('user', $user);
	  return "";
	}
	$sql="SELECT s.* FROM sessions s WHERE s.sid = ?";
	$s=$this->db->getRow($sql, $id);
	
	if ($s && $s->uid > 0) {
		$u = new stdClass();
		$u->uid = $s->uid;
		$u->username = $s->name;                        
		$u->session = $s->session;                                        
	} else {
		$session = isset($s->session) ? $s->session : '';
		$u = $this->getAnonymousUser($session);
	}

	Registry::set('user', $u);
	return $u->session;    
  }

  public function write($id, $sessionData)
  {
	return true;
	if (!$this->session_enabled) {
	  return true;
	}
	$u=Registry::get('user');
	if (empty($_COOKIE[session_name()]) && empty($sessionData)) {
	  return TRUE;
	}
	if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} else {
			$ip = $_SERVER["REMOTE_ADDR"];
		}
	$sql="SELECT sid FROM sessions WHERE sid = ?";
	$sid=$this->db->getValue($sql, $id);

	if ($sid) {
	  if ($u->uid || $sessionData || count($_COOKIE)) {
		$data = array(
			'uid'      => $u->uid,
			'hostname'      => $ip,
			'timestamp'    => time(),
			'session'    => $sessionData,
			'name' => @$u->name
			);
		$this->db->update('sessions', $data, "sid = '" . $id . "'");
	  }
	} else {
	  $data = array(
		  'sid'      => $id,
		  'uid'      => $u->uid,
		  'hostname'      => $ip,
		  'timestamp'    => time(),
		  'session'    => $sessionData,
		  'name' => @$u->name
		  );
		  
	  $this->db->insert("sessions", $data);
	}
	return true;
  }

  public function destroy($id)
  {
	if (!$this->session_enabled) {
	  return true;
	}
	$sql="DELETE FROM sessions WHERE sid=?";
	$this->db->execute($sql, $id);
	return true;
  }

  public function gc($maxLifetime)
  {
	if (!$this->session_enabled) {
	  return true;
	}
	$sql="DELETE FROM sessions WHERE timestamp < ?";
	$this->db->execute($sql, time()-$maxLifetime);
	return true;
  }

  public function getAnonymousUser($session='')
  {
	$user = new stdClass();
	$user->uid = 0;
	$user->hostname = $_SERVER['REMOTE_ADDR'];
	$user->session = $session;
	$user->roles = array();
	$user->perms = array();
	$user->roles_loaded = FALSE;
	$user->perms_loaded = FALSE;
	 $user->name = ''; 
	return $user;
  }

  public function updateLastLogin($uid)
  {
	if (!$this->session_enabled) {
	  return;
	}
	$sql="UPDATE users SET login = ? WHERE uid = ?";
	$this->db->execute($sql, array(time(), $uid));
  }

  public function regenerateSessionID()
  {
	if (!$this->session_enabled) {
	  return;
	}
	$old_session_id = session_id();
	session_regenerate_id();
	$sql="UPDATE sessions SET sid = ? WHERE sid = ?";
	$this->db->execute($sql, array(session_id(), $old_session_id));
  }

  private function unpack($obj) {
	if (isset($obj->data) && $data = unserialize($obj->data)) {
	  foreach ($data as $key => $value) {
		if (!isset($obj->$key)) {
		  $obj->$key = $value;
		}
	  }
	}
	return $obj;
  }
}
