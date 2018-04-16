<?php
require_once 'ActionBase.php';

class BannersController extends ActionBase {
    
    public function init(){
		$this->check_user();
		$status = array(0 => 'Ẩn ',
								1 => 'Hiện',
					);
		$this->view->status = $status;
		
		//$this->view->subMenu = $this->adminMenu();
        $this->view->setLayout('default_admin');
    }
	
	public function IndexAction(){
		$model = $this->loadModel("BannerModel");
		$banners = $model->selectAll();

		$this->view->banners = $banners;
	}
	
	public function AddAction() {
		
		if ($this->isPost()){
			$path = $this->bannerValidate();
			
			if ($path) {
					$data = array();
					$data['name'] 			= $this->getPost('name','');
					$data['url'] 			= $this->getPost('url','');
					$data['filename'] 	= $path;
					$data['created'] 		= time();
					
					$model	= $this->loadModel("BannerModel");
					if ($model->add($data)) {
						$this->view->error = "Thêm ảnh banner thành công.";
						$this->redirect("/banners");
					}
					else {
						$this->view->error = "Có lỗi trong quá trình lưu thông tin.";
					}
			}
			
		}
	}
	
	public function EditAction($id = 0) {
		$bid 		= $this->getParam('id');
		if ($this->isPost())
		{
			$data = array();
			if (!empty ($_FILES["picture"]["name"])) {
				$path = $this->bannerValidate();
				$data['filename'] 	= $path;
				$old_file 	= $this->getPost('old_file','');
				
				$this->delete_image($old_file);
			}
			
			$data['name'] 	= $this->getPost('name','');
			$data['url'] 		= $this->getPost('url','');
			$data['bid'] 		= $bid;
			
			$model	= $this->loadModel("BannerModel");
			if ($model->edit($data)) {
				$this->view->error = "Thay đổi thông tin ảnh banner thành công.";
				$this->redirect("/banners");
			}
			else {
				$this->view->error = "Có lỗi trong quá trình lưu thông tin.";
			}
		}
		else {
			$model	= $this->loadModel("BannerModel");
			$banner	 	= $model->get_by_key($bid);
			
			$this->view->banner = $banner;
		}
	}
	
	private function delete_image($filename) {
		$root_dir = PUB_PATH . 'img' . DS . 'banners' . DS;
		$dest = $root_dir . $filename;
		$thumb = $root_dir . "thumb_".$filename;
		unlink($dest);
		unlink($thumb);
	}
	
	public function DeleteAction($id = 0) {
		$bid 		= $this->getParam('id');
		$model 	= $this->loadModel("BannerModel");

		$old_file	= $model->get_a_field("filename",$bid);
		$this->delete_image($old_file);
		
		$data 		= $model->delete_by_id($bid);
		
		$this->redirect('/banners');
	}
	
	public function ChangeAction($id = 0, $status = 1) {
		$bid 		= $this->getParam('id');
		$status 	= ($this->getParam('status') == 1) ? 0 : 1;
		
		
		$data = array('id'=>$bid,
								'value'=>$status
							);
								
		$model	= $this->loadModel("BannerModel");
		$model->update_data($data,'status');
		
		$this->go_back();
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
		echo $filetype;
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
				
				$width = 480;
				//$height = $detail['height']*60/$detail['width'];
				$height = 160;
				
				ImageLibrary::imageResize($dest, $thumb, $width, $height);
			} else {
				$this->view->error = "Có lỗi upload lên server";
				return false;
			}
		}

		return $filename;
	}
}

?>