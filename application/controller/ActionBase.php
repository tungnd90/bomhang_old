<?php

class ActionBase extends Controller {
	
	public function init()
	{
		$this->_sweepFlashData();
		$this->_markFlashData();
		$this->check_user();
	}
	
	public function isAdmin()
    {
        $user = $this->getUser();
        $admins = array(448=>'sonlecong',1076396 => 'MOD_06',1076421 => 'MOD_09',993928 => 'MOD_04',1076407 => 'MOD_07',1077337 => 'MOD_11',1511956 => 'MOD_12',
    949691 => 'MOD_01',950260 => 'MOD_02',981825 => 'MOD_03',1075980=>'MOD_05',1076416=>'MOD_08',1076425=>'MOD_10',10041104=>'tungnd_90',42642=>'vutrungkien86');
        if (array_key_exists($user->uid, $admins)){
            return TRUE;    
        }
        return FALSE;
    }

	public function loadModel($name)
	{
		return ClassFactory::getInstance()->get_model($name);
	}
    
	public function isLogin()
	{
		$user=$this->getUser();
		return ($user->uid>0);
	}

	public function getUser()
	{
		return Registry::get("user");
	}

	public function redirect($url)
	{
		header("Location: ".$url);
		exit();
	}

	public function flashData($item)
	{
		$key = $item . ':old:';
		if (isset($_SESSION['flash'][$key])){
			return $_SESSION['flash'][$key];
		}
		return NULL;
	}

	public function setFlashData($item, $value)
	{
		$key = $item . ':new:';
		$_SESSION['flash'][$key] = $value;
	}

	private function _markFlashData()
	{
		if (isset($_SESSION['flash'])){
			foreach($_SESSION['flash'] as $key => $value){
				$newKey = str_replace(':new:', ':old:', $key);
				$_SESSION['flash'][$newKey] = $value;
				unset($_SESSION['flash'][$key]);
			}
		}
	}

	private function _sweepFlashData()
	{
		if (isset($_SESSION['flash'])){
			foreach($_SESSION['flash'] as $key => $value){
				if (strpos($key, ':old:') !== FALSE){
					unset($_SESSION['flash'][$key]);
				}
			}
		}
	}
    public function listGame(){
       $g_model = $this->loadModel("GamesModel");
       $games = $this->getCache('games_public');
       if(!$games){
          $games = $g_model->getAllGamesPublic();
          $this->setCache('games_public',$games); 
       }
       foreach ($games as $game){
           $game_cat[$game->cid][] = $game;
       }
       return $game_cat; 
    }
    //lazy load user roles
  public function loadUserRoles(){
    $u = Registry::get("user");
    if ($u->uid == 0) {
      return;
    }
    if ($u->roles_loaded) {
      return;
    }
    $roleModel = $this->loadModel("RoleModel");
    $roles = $roleModel->getUserRoles($u->uid);
        if ($roles) {
            foreach ($roles as $role) {
                $u->roles[] = $role->rid;
            }
        }
    $u->roles_loaded = TRUE;
    Registry::set("user", $u);
  }

  //Lazy load user permissions
  public function loadUserPermissions()
  {
    $u = Registry::get("user");
    if ($u->uid == 0) {
      return;
    }
    if ($u->perms_loaded) {
      return;
    }
    if ( ! $u->roles_loaded) {
      $this->loadUserRoles();
    }
    $permissionModel = $this->loadModel('PermissionModel');
    $perms = $permissionModel->getUserPermissions($u->uid, array_values($u->roles));
    if ($perms) {
      foreach ($perms as $p) {
        $arrPerms = explode(',',$p->perm);
        if (is_array($arrPerms)){
          foreach($arrPerms as $perm){
            $perm = trim($perm);
            if ($perm!='' && !in_array($perm, $u->perms))
              $u->perms[] = $perm;
          }
        }
      }
    }
    $u->perms_loaded = TRUE;
    Registry::set("user", $u);
  }

  //check current user permission
  public function hasPerm($perm)
  {
    $u = Registry::get("user");
    if ( ! $u->perms_loaded)
    {
      $this->loadUserPermissions();
    }
    if (in_array($perm, $u->perms)) {
      return TRUE;
    }
    return FALSE;
  }

  public function isMod(){
    return $this->hasPerm(MOD_GAME_PERM);
  }
  
  function check_user() {
    if (empty($_SESSION['User'])) {
      $this->redirect('/users/login');
    }
  }
  
	public function getListApp() {
		$key="list_apps";
		$data=$this->getCache($key);
		if (empty($data)) {
			$a_model = $this->loadModel("ApplicationModel");
			$data = $a_model->getListApp();
			$this->setCache($key, $data);
		}

		return $data;
	}
	
	function render_content($template){
		if (!isset($template)||$template=='')
		  return '';
		ob_start();

		include ($template);
		$rs = ob_get_contents();

		ob_end_clean();

		return $rs;
	}
}
