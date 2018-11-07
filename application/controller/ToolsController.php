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
            $data = (string)$this->getMarket($i->market);
            echo $bid = $this->getValue("Bid", $data);
            echo $ask = $this->getValue("Ask", $data);

            print_r($data);

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
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','apisign:'.$sign));
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
	
	private function getValue($key, $str) {
        $str_key = '/"'.$key.'":(\d*\.\d+)/';
        if (!preg_match($str_key, $str, $matches)) {
            throw new Exception('Unofficial API is broken or user not found');
        }

        echo $matches[1];
    }
}

?>