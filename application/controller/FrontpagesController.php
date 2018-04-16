<?php
require_once 'ActionBase.php';

class FrontpagesController extends ActionBase {
    
    public function init(){
		$this->view->currentURL = $this->curPageURL();
		$this->view->setLayout('alphaart');

		/* get category */
		$c_model = $this->loadModel("CategoryModel");
		$lists = $c_model->getMainList();
		$this->view->lists = $lists;

		$cates = array();
		foreach ($lists as $l) {
			$cates[$l->cid] = $l->name;
		}
		$this->view->cates = $cates;

		/* get top 10 topic */
		$t_model = $this->loadModel("TopicModel");
		$tops = $t_model->selectAll("total_view desc", 0, 10, "", "tid, title");
		$this->view->tops = $tops;
    }
	
	public function IndexAction($id = 0){
		$id 	= (int)$this->getParam('id',0);
		$page = (int)$this->getParam('page',1);
		if($page==0) $page = 1;
		$limit = 7;
		$start = ($page - 1)*$limit;
    	
		$conditions = '';
		$order = "created desc";

		if ($id == 0) $order = "total_view desc";
		else $conditions = "category_id = " . $id;

		$t_model = $this->loadModel("TopicModel");
		$topics = $t_model->selectAll($order, $start, $limit, $conditions, "tid, title, content, created, category_id, image");
		$this->view->topics = $topics;
		$this->view->cid = $id;

		$count = $t_model->count($conditions);
		$url = explode("?",$this->curPageURL());
		$currentURL = $url[0];
		$this->view->totalItems = $count;
		$page_total 			= ceil($this->view->totalItems/$limit);
		$this->view->totalPages = $page_total;
		$this->view->currentPage = $page;
		$this->view->url 		= $currentURL;
		$this->view->ext_param 	= @$ext_params;
		$this->view->pager 		= $this->view->partial('pager');
		$this->view->obj		= "bài viết";
	}
	
	public function ViewAction($id = null) {
		$tid 	= (int)$this->getParam('id',0);

		// select topic data where id
		$t_model = $this->loadModel("TopicModel");
		$topic	= $t_model->get_by_key($tid);

		if (empty($topic)) {
			$this->redirect("/");
		}

		$t_model->update_topic($tid,'total_view');
		$this->view->topic = $topic;
		$this->view->h_title = $topic->title;
	}
	
	
}
