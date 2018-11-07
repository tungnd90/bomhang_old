<?php
require_once 'ActionBase.php';

class InvestsController extends ActionBase {
    
    public function init(){
		$this->check_user();
		//$this->view->subMenu = $this->adminMenu();
        $this->view->setLayout('default_admin');
    }
	
	public function IndexAction(){
		$t_model = $this->loadModel("InvestModel");
		$topics = $t_model->selectAll("created desc");
		$this->view->topics = $topics;
	}
	
	public function EditAction($id = 0) {
		if ($this->isPost())
		{
            $data['id'] 		= $this->getParam('id');
            $data['name'] 	= $this->getPost('name','0');
            $data['market'] 	= $this->getPost('market','');
            $data['min_price'] 	= $this->getPost('min_price', 1);
            $data['max_step'] 	= $this->getPost('max_step', 1);
            $data['step'] 	    = $this->getPost('step', 1);
            $data['coin'] 	    = $this->getPost('coin', 1);
			$t_model = $this->loadModel("InvestModel");

			$tid = $t_model->edit($data);
			
			$this->redirect("/invests");
		}
		else {
			$tid 		= $this->getParam('id');
			$model	= $this->loadModel("InvestModel");
			$topic	 	= $model->get_by_key($tid);
			$this->view->topic = $topic;
		}
	}
	
	public function AddAction() {
		if ($this->isPost()) {
            $data['name'] 	= $this->getPost('name','0');
            $data['market'] 	= $this->getPost('market','');
            $data['min_price'] 	= $this->getPost('min_price', 1);
            $data['max_step'] 	= $this->getPost('max_step', 1);
            $data['step'] 	    = $this->getPost('step', 1);
            $data['coin'] 	    = $this->getPost('coin', 1);

            $t_model = $this->loadModel("InvestModel");
            $tid = $t_model->add($data);

            $this->redirect("/invests");

		}
	}
	
	public function DeleteAction($id = 0) {
		$tid 			= $this->getParam('id');
		$t_model = $this->loadModel("TopicModel");
		$data 		= $t_model->delete_by_id($tid);

		$this->redirect('/topics');
	}
	

	private function bannerValidate() {
		require_once(APP_LIB . 'ImageLibrary.php');
		//$this->errors = array();

		$timer = time();

		// Define path of large images and thumbnail images
		$root_dir = PUB_PATH . 'img' . DS . 'banners' . DS;

		$timer = time();
		
		// Preparing to Upload
		$tempfile = $_FILES["picture"]["tmp_name"];
		
		//Check file type
		$file_parts = pathinfo($_FILES["picture"]["name"]);
		$filetype = $file_parts['extension'];

		//Check Validate Image
	 	if ( (strtolower($filetype) != 'jpeg') && (strtolower($filetype) != 'jpg') && (strtolower($filetype) != 'png') && (strtolower($filetype) != 'gif') )
	 	{
			$this->view->error = "Chỉ hỗ trợ định dạng ảnh gif, jpg, png";
			return false;
	 	}
		
		$filename = $timer."_".str_replace("%20", "_", trim($_FILES["picture"]["name"]));

		//Get destination file
		$dest = $root_dir . $filename;
		$thumb = $root_dir . "thumb_".$filename;

		// Upload Images to host
		if ($tempfile) {
			if (move_uploaded_file($tempfile, $dest)) {
				$detail = ImageLibrary::imageGetInfo($dest);
				
				$width = 140;
				//$height = $detail['height']*60/$detail['width'];
				$height = 120;
				
				ImageLibrary::imageResize($dest, $thumb, $width, $height);
			} else {
				$this->view->error = "Có lỗi upload lên server";
				return false;
			}
		}

		return $filename;
	}

	private function delete_image($filename) {
		$root_dir = PUB_PATH . 'img' . DS . 'banners' . DS;
		$dest = $root_dir . $filename;
		$thumb = $root_dir . "thumb_".$filename;
		@unlink($dest);
		@unlink($thumb);
	}
}

?>