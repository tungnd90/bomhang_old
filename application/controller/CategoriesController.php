<?php
require_once 'ActionBase.php';

class CategoriesController extends ActionBase {
    
    public function init(){
		$this->check_user();
		//$this->view->subMenu = $this->adminMenu();
        $this->view->setLayout('default_admin');
    }
	
	public function IndexAction(){
		$c_model = $this->loadModel("CategoryModel");
		$categories = $c_model->getList();
		
		$this->view->categories = $categories;
	}
	
	public function ListAction() {
		$c_model = $this->loadModel("CategoryModel");
		$categories = $c_model->getList("_",true);
		
		$this->view->categories = $categories;
	}
	
	public function  AddAction() {
		if ($this->isPost())
		{
			$name 		= $this->getPost('title','');
			$parent_id 	= $this->getPost('parent_id','0');
			$description = $this->getPost('description','');
			$type 			= $this->getPost('type','0');
			$parent 		= $this->getPost('parent','0');
			$vip_action	= $this->getPost('vip_action','0');
			$glink	= $this->getPost('glink','');
			$m_title	= $this->getPost('m_title','');
			$m_desc	= $this->getPost('m_desc','');
			$m_key	= $this->getPost('m_key','');
			
			if ($parent == 0) $parent_id = 0;
			
			$data = array('name'=>$name,
									'parent_id'=>$parent_id,
									'description'=>$description,
									'vip_action'=>$vip_action,
									'm_key'=>$m_key,
									'glink'=>$glink,
									'm_title'=>$m_title,
									'm_desc'=>$m_desc,
									'type'=>$type
								);
			
			$c_model = $this->loadModel("CategoryModel");
			$c_model->add($data);
		}
		
		$this->redirect('/categories/index');
	}
	
	public function EditAction() {
		if ($this->isPost())
		{
			$cid 				= $this->getPost('cid','0');
			$name 		= $this->getPost('edit_name','');
			$parent_id 	= $this->getPost('position','0');
			$c_pos		 	= $this->getPost('c_pos','0');
			$description = $this->getPost('edit_description','');
			$type 			= $this->getPost('edit_type','0');
			$vip_action	= $this->getPost('edit_vip_action','0');
			$glink	= $this->getPost('edit_glink','');
			$m_title	= $this->getPost('edit_m_title','');
			$m_desc	= $this->getPost('edit_m_desc','');
			$m_key	= $this->getPost('edit_m_key','');
			
			$data = array('cid' => $cid,
									'name'=>$name,
									'description'=>$description,
									'c_pos'=>$c_pos,
									'vip_action'=>$vip_action,
									'm_key'=>$m_key,
									'glink'=>$glink,
									'm_title'=>$m_title,
									'm_desc'=>$m_desc,
									'type'=>$type
								);
									
			if ($parent_id >= 0) $data['parent_id'] = $parent_id;
			
			$c_model = $this->loadModel("CategoryModel");
			$c_model->edit($data);
			
		}
		$this->redirect('/categories/index');
	}
	
	public function LoadInfoAction($id = 0) {
		$cid 			= $this->getParam('id');
		$c_model = $this->loadModel("CategoryModel");
		$data 		= $c_model->get_by_key($cid);
		
		$str = $data->cid . ",--," . $data->position . ",--," . $data->description . ",--," . $data->type. ",--," . $data->vip_action. ",--," . $data->glink. ",--," . $data->m_title. ",--," . $data->m_desc. ",--," . $data->m_key;
		
		die($str);
	}
	
	public function DeleteAction($id = 0) {
		$cid 			= $this->getParam('id');
		$c_model = $this->loadModel("CategoryModel");
		$data 		= $c_model->delete_by_id($cid);
		
		$this->redirect('/categories/index');
	}
	
	public function Reset_nowAction() {
		$c_model = $this->loadModel("CategoryModel");
		$c_model->reset_total();
		
		$t_model = $this->loadModel("TopicModel");
		$t_model->empty_data();
		die();
	}
	
	public function Reset_by_cidAction($id = 0) {
		$cid 			= $this->getParam('id');
		$c_model = $this->loadModel("CategoryModel");
		$c_model->reset_total($cid);
	} 
	
	public function Count_downAction($id = null, $status = null) {
		$c_data['cid'] = $this->getParam('id');
		$c_data['parent_id'] = $this->getParam('status');
		print_r($c_data);
		$c_model = $this->loadModel("CategoryModel");
		$c_model->total_down($c_data);
		die();
	}
	
	public function Count_upAction($id = null, $status = null) {
		$c_data['cid'] = $this->getParam('id');
		$c_data['parent_id'] = $this->getParam('status');
		print_r($c_data);
		$c_model = $this->loadModel("CategoryModel");
		$c_model->total_up($c_data);
		die();
	}
}

?>