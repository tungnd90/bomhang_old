<?php
require_once 'ActionBase.php';

class ToolsController extends ActionBase {
    
    public function init(){
		$this->check_user();
		$this->api_key = BITTREX_KEY;
		$this->api_secret = BITTREX_TOKEN;
		//$this->view->subMenu = $this->adminMenu();
        $this->view->setLayout('');
    }

    public function IndexAction() {
        $t_model = $this->loadModel("InvestModel");
        $invests = $t_model->selectAll("created desc");

        foreach ($invests as $i) {
            echo $this->getMarket($i->market);
            echo "<br />";
            $market = json_encode($this->getMarket($i->market), true);
            print_r($market);
            echo $market->result;
            die();
            if ($i->current_step == -1) {
                echo "mua vao lan dau";
            }
            else {
                if ($i->buy) {
                    $target_sell = $i->min_price + ($i->min_price*(($i->step/100)*($i->current_step+1)));
                }

                if ($i->sell) {
                    $target_buy = $i->min_price + ($i->min_price*(($i->step/100)*($i->current_step-1)));
                    if ($target_buy < $i->min_price) break;
                }
            }



//            echo ($market);
//            print_r($i);
        }

//        echo $this->getMarket("BTC-ETH");
        die();
    }

	
	public function OrderAction($id = 0) {
        $apikey= BITTREX_KEY;
        $apisecret= BITTREX_TOKEN;
        $nonce=time();
        $uri='https://bittrex.com/api/v1.1/market/getopenorders?apikey='.$apikey.'&nonce='.$nonce;
        $sign=hash_hmac('sha512',$uri,$apisecret);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        $execResult = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($execResult);

        echo "<pre>";
        print_r($obj);
        die();
	}

    public function BalanceAction($id = 0) {
        $nonce=time();
        $uri='https://bittrex.com/api/v1.1/account/getbalances?apikey='.$this->api_key.'&nonce='.$nonce;
        $sign=hash_hmac('sha512',$uri,$this->api_secret);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        $execResult = curl_exec($ch);
        curl_close($ch);
        $obj = json_decode($execResult);

        echo $obj;
//        echo "<pre>";
//        print_r(json_decode($obj, true));
        die();
    }

    private function getMarket($market) {
        $nonce=time();
        $uri='https://bittrex.com/api/v1.1/public/getticker?market='.$market.'&apikey='.$this->api_key.'&nonce='.$nonce;
        $sign=hash_hmac('sha512',$uri,$this->api_secret);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('apisign:'.$sign));
        $execResult = curl_exec($ch);
        curl_close($ch);
        $obj = ($execResult);
        //{"success":true,"message":"","result":{"Bid":0.03341015,"Ask":0.03358766,"Last":0.03358766}}
        return $obj;
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