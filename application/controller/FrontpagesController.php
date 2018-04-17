<?php
require_once 'ActionBase.php';

class FrontpagesController extends ActionBase {

    public function init(){
        $this->view->currentURL = $this->curPageURL();
        $this->view->setLayout('alphaart');
    }

    public function IndexAction($id = 0){

    }

    public function ViewAction($id = null) {
        $id 	= $this->getParam('id',"");

        $model	= $this->loadModel("UserModel");
        $user	= $model->get_by_md_name($id);

        if ($user) {
            // select topic data where id
            $w_model = $this->loadModel("WorkerModel");
            $workers = $w_model->getByMdId($user->md_id);

            if (!empty($workers)) {
                $data = array();
                foreach ($workers as $w) {
                    $s_model = $this->loadModel("StatsModel");
                    $stats = $s_model->getByWorkerId($w->id);

                    $data[$w->id]['name'] = $w->worker;
                    $data[$w->id]['id'] = $w->id;
                    if ($stats) {
                        foreach ($stats as $s) {
                            $data[$w->id]['data'][] = array("label" => date("Y-m-d H:i", $s->time), "y" => $s->hashrate);
                        }
                    }
                }
            }

            $this->view->data = $data;
        }
        else {
            $this->redirect("/");
        }
    }


}
