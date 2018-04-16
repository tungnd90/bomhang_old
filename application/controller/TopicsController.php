<?php
require_once 'ActionBase.php';

class TopicsController extends ActionBase {
    
    public function init(){
		$this->check_user();
		//$this->view->subMenu = $this->adminMenu();
        $this->view->setLayout('default_admin');
    }
	
	public function IndexAction(){
		$t_model = $this->loadModel("TopicModel");
		$c_model = $this->loadModel("CategoryModel");
		
		$topics = $t_model->selectAll("created desc, total_view desc");
		$list_cate = $c_model->getList();
		
		$this->view->topics = $topics;
		$this->view->list_cate = $list_cate;
		
	}
	
	public function EditAction($id = 0) {
		if ($this->isPost())
		{
			if (!empty ($_FILES["picture"]["name"])) {
				$path = $this->bannerValidate();
				$data['image'] 	= $path;
				$old_file 	= $this->getPost('old_file','');
				
				$this->delete_image($old_file);
			}
			else {
				$data['image'] 	= $this->getPost('old_file','');
			}

			$data['tid'] 	= $this->getPost('tid','0');
			$data['cid'] 	= $this->getPost('cid','0');
			$data['title'] 	= $this->getPost('title','');
			$content 	= $this->getPost('content','');
			$data['position'] 	= $this->getPost('position',1);
			$data['created'] 	= time();
			$data['content'] = str_replace(array(' .',' ,'), array('.',','), $content);
			$t_model = $this->loadModel("TopicModel");

			$tid = $t_model->edit($data);
			
			$this->redirect("/topics");
		}
		else {
			$tid 		= $this->getParam('id');
			$model	= $this->loadModel("TopicModel");
			$c_model = $this->loadModel("CategoryModel");
			
			$topic	 	= $model->get_by_key($tid);
			$categories = $c_model->getList("_",true);
			
			$this->view->categories = $categories;
			$this->view->mce_width = 900;
			$this->view->mce_height = 500;
			$this->view->topic = $topic;
		}
	}
	
	public function AddAction() {
		if ($this->isPost()) {
			$path = $this->bannerValidate();
			
			if ($path) {
				$data['cid'] 	= $this->getPost('cid','0');
				$data['title'] 	= $this->getPost('title','');
				$content 	= $this->getPost('content','');
				$data['position'] 	= $this->getPost('position', 1);
				$data['created'] 	= time();
				$data['content'] = str_replace(array(' .',' ,'), array('.',','), $content);
				$data['image'] 	= $path;

				$t_model = $this->loadModel("TopicModel");
				$tid = $t_model->add($data);

				$this->redirect("/topics");
			}
		}
		else {
			$c_model = $this->loadModel("CategoryModel");
			$categories = $c_model->getList("_",true);
			$this->view->categories = $categories;
			$this->view->mce_width = 900;
			$this->view->mce_height = 500;
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