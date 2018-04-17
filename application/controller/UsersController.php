<?php
require_once 'ActionBase.php';

class UsersController extends ActionBase {
    
    public function init(){
		$group = array(1 => 'Admin',
								2 => 'Mod',
								3 => 'Member'
					);
		$this->view->group = $group;
		
		$status = array(0 => 'Deactivate ',
								1 => 'Active',
					);
		$this->view->status = $status;
		
		//$this->view->subMenu = $this->adminMenu();
        $this->view->setLayout('default_admin');
    }
	
	public function IndexAction(){
		$this->check_user();

		$model = $this->loadModel("UserModel");
		$users = $model->selectAll();

		$this->view->users = $users;
	}

	public function AddAction() {
		if ($this->isPost())
		{	
			$username = $this->getPost('username','');
			$n_pass = $this->getPost('new_password','');
			$cf_pass = $this->getPost('cf_password','');
            $md_name = $this->getPost('md_name','');
            $md_id = (int)$this->getPost('md_id','');
            $md_key = $this->getPost('md_key','');
			$error = '';

			if (empty($n_pass) || empty($cf_pass) || empty($username)) {
				$error = 'Please complete all information';
			}
			else if (strlen($n_pass) < 6) {
				$error = 'Password is too short';
			}
			else if ($n_pass != $cf_pass) {
				$error = 'Password do not match';
			}
			else {
				$model	= $this->loadModel("UserModel");
				$data = array(
							'username'=>$username,
							'password'=>md5($n_pass),
							'created'=> time(),
                            'md_id' => $md_id,
                            'md_name'=> $md_name,
                            'md_key'=> $md_key
						);

				$model->add($data);
				$this->redirect("/users");
			}
			
			$this->view->error = $error;
		}
	}

    public function EditAction($id = 0) {
        $this->check_user();

        if ($this->isPost())
        {
            $uid = $this->getPost('uid','0');
            $md_name = $this->getPost('md_name','');
            $md_id = (int)$this->getPost('md_id','');
            $md_key = $this->getPost('md_key','');
            $error = '';

            if ($uid < 1) {
                $error = 'An error has occurred';
            }
            else if (empty($md_id) || empty($md_name) || empty($md_key)) {
                $error = 'Please complete all information';
            }
            else {
                $model	= $this->loadModel("UserModel");
                $data = array(
                    'md_id' => $md_id,
                    'md_name'=> $md_name,
                    'md_key'=> $md_key
                );

                $model->update_by_key($uid, $data);
                $this->redirect("/users");
            }

            $this->view->error = $error;
        }

        $uid 	= $this->getParam('id');
        $model	= $this->loadModel("UserModel");
        $user	= $model->get_by_key($uid);

        $this->view->user = $user;

    }
	
	public function PassAction($id = 0) {
		$this->check_user();

		if ($this->isPost())
		{	
			$uid = $this->getPost('uid','0');
			$n_pass = $this->getPost('new_password','');
			$cf_pass = $this->getPost('cf_password','');
			$error = '';

			if ($uid < 1) {
				$error = 'An error has occurred';
			}
			else if (empty($n_pass) || empty($cf_pass)) {
				$error = 'Please complete all information';
			}
			else if (strlen($n_pass) < 6) {
				$error = 'Password is too short';
			}
			else if ($n_pass != $cf_pass) {
				$error = 'Password do not match';
			}
			else {
				$model	= $this->loadModel("UserModel");
				$data = array(
							'id'=>$uid,
							'value'=>md5($n_pass)
						);

				$model->update_data($data,'password');
				$this->redirect("/users");
			}
			
			$this->view->error = $error;
		}

		$uid 	= $this->getParam('id');
		$model	= $this->loadModel("UserModel");
		$user	= $model->get_by_key($uid);
		
		$this->view->user = $user;

	}
	
	public function DeleteAction($id = 0) {
		$this->check_user();

		$uid 		= $this->getParam('id');
		$c_model = $this->loadModel("UserModel");
		$data 		= $c_model->delete_by_id($uid);
		
		$this->redirect('/users');
	}
	
	public function BlockAction($id = 0, $status = 1) {
		$this->check_user();

		$uid 		= $this->getParam('id');
		$status 	= ($this->getParam('status') == 1) ? 0 : 1;
		
		
		$data = array('id'=>$uid,
								'value'=>$status
							);
								
		$model	= $this->loadModel("UserModel");
		$model->update_data($data,'status');
		
		$this->go_back();
	}
	
	
	public function LoginAction() {
		$this->view->setLayout('admin_login');

		if ($this->isPost())
		{	
			$username = $this->getPost('username','');
			$password = $this->getPost('password','');
			$error = '';

			if (empty($username) || empty($password)) {
				$error = 'Please enter your username and password';
			}
			else {
				$data = array(
							'username'=>$username,
							'password'=>md5($password)
						);

				$model	= $this->loadModel("UserModel");
				$users = $model->getUserLogin($data);

				if (empty($users)) {
					$error = "Username or password incorrect";
				}
				else if ($users->status != 1) {
					$error = "This account is locked";
				}
				else {
					$_SESSION['User'] = $users;
					$this->redirect("/users");
				}
			}
			
			$this->view->error = $error;
		}

		//$this->view->setNoRender();
	}

	public function logoutAction() {
		$this->view->setNoRender();

		unset($_SESSION['User']);
		$this->redirect("/users/login");
	}
}

?>