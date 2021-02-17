<?php
	if (!defined('__TYPECHO_ROOT_DIR__')) exit;
	class HoneyPotController{
		private static $instance;
		private $_method;//方法
		private $_model;//模型
		private $_view;
		private $pageSize;
		private $total;

		// 构造方法
		private function __construct($_method){
			$this->_method = $_method;
			$this->_model = Typecho_Db::get();
			$this->pageSize = 8;
			$this->total = $this->_model->fetchRow($this->_model->select('count(1) AS count')->from('table.honeypot_log'))["count"];
			$this->_view = new HoneyPotView($this->pageSize,$this->total);
		}

		// 单例入口
		public static function build($_method){
			if(!(self::$instance instanceof self))
			{
				self::$instance = new self($_method);
			}
			return self::$instance;
		}

		// 初始化
		public function initialization(){
			if(method_exists(__CLASS__,$this->_method) && in_array($this->_method,["index","detailed"])){
				call_user_func([__CLASS__,$this->_method]);
			} else {
				call_user_func([__CLASS__,"index"]);
			}
		}

		public function index(){
			$model = $this->_model->select()->from('table.honeypot_log');
			if(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->pluginget == 0){
				$model = $model->where("url NOT LIKE '%HoneyPot%'");
			}
			if($ip = Typecho_Request::getInstance()->get("attack","all")){
				if($ip!="all")
					$model = $model->where("client_ip = ?",$ip);
			}
			$logs = $this->screen($this->_model->fetchAll($model->order('id', Typecho_Db::SORT_DESC)));
			$this->_view->setPage($this->pageSize,count($logs));
			$pagelog = [];
			for($i=(Typecho_Request::getInstance()->get("page",1)-1)*$this->pageSize;$i<($this->pageSize+Typecho_Request::getInstance()->get("page",1)-1);$i++){
				if(array_key_exists($i, $logs)){
					$pagelog[] = $logs[$i];
				}
			}
			$honeylogs = $this->_model->fetchAll($this->_model->query("select * from {$this->_model->getPrefix()}honeypot_log where `url` NOT LIKE '%.ico%' and `url` NOT LIKE '%.css%' and `url` NOT LIKE '%.svg%' and `vulnerability` like '%蜜罐%' order by id DESC limit 1"));
			$honeylogs[0]["total"] = $this->_model->fetchRow($this->_model->select(["COUNT(client_ip)"=>"total"])->from('table.honeypot_log')->where("vulnerability like '%蜜罐%'"))["total"];
			$honeylogs[0]["client_ip"] = $this->_model->fetchRow($this->_model->select(["GROUP_CONCAT(DISTINCT(client_ip))"=>"attackip"])->from('table.honeypot_log')->where("vulnerability like '%蜜罐%'")->order('id', Typecho_Db::SORT_DESC))["attackip"];
			$clientIps = $this->_model->fetchRow($this->_model->select(["GROUP_CONCAT(DISTINCT(client_ip))"=>"attackip"],[])->from('table.honeypot_log')->order('id', Typecho_Db::SORT_DESC))["attackip"];
			$ipAccount = [];
			foreach(explode(",",$clientIps) as $ip){
				$ipAccount[$ip] = $this->_model->fetchRow($this->_model->select(["GROUP_CONCAT(DISTINCT(platformaccount))"=>"account"])->from('table.honeypot_log')->where("client_ip = ?",$ip));
			}
			$this->_view->assign("ipandaccount",$ipAccount)->assign("clientips",explode(",",$clientIps))->assign("honeylogs",$honeylogs)->assign("tbody",$pagelog)->fetch(__FUNCTION__);
		}

		public function detailed(){
			$model = $this->_model->select()->from('table.honeypot_log')->where("url !='%2Ffavicon.ico'");
			$total = $this->_model->select(["COUNT(*)"=>"total"])->from('table.honeypot_log')->where("url !='%2Ffavicon.ico'");
			if(Typecho_Request::getInstance()->get("attack","all") == "all" && Typecho_Request::getInstance()->get("bugtype","all") == "all"){
				if(Typecho_Request::getInstance()->get("isContain",0)){
					$model = $model->where("vulnerability like '%蜜罐%'");
					$total = $total->where("vulnerability like '%蜜罐%'");
				} else {
					if(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->pluginget == 0){
						$model = $model->where("url NOT LIKE '%HoneyPot%'")->where("vulnerability NOT LIKE '%蜜罐%'");
						$total = $total->where("url NOT LIKE '%HoneyPot%'")->where("vulnerability NOT LIKE '%蜜罐%'");
					} else {
						$model = $model->where("vulnerability NOT LIKE '%蜜罐%'");
						$total = $total->where("vulnerability NOT LIKE '%蜜罐%'");
					}
				}
			} else if(Typecho_Request::getInstance()->get("attack","all") != "all" && Typecho_Request::getInstance()->get("bugtype","all") == "all"){
				if(Typecho_Request::getInstance()->get("isContain",0)){
					$model = $model->where("vulnerability like '%蜜罐%'");
					$model = $model->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"));
					$total = $total->where("vulnerability like '%蜜罐%'");
					$total = $total->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"));
				} else {
					if(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->pluginget == 0){
						$model = $model->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"))->where("vulnerability NOT LIKE '%蜜罐%'")->where("url NOT LIKE '%HoneyPot%'");
						$total = $total->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"))->where("vulnerability NOT LIKE '%蜜罐%'")->where("url NOT LIKE '%HoneyPot%'");
					} else {
						$model = $model->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"))->where("vulnerability NOT LIKE '%蜜罐%'");
						$total = $total->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"))->where("vulnerability NOT LIKE '%蜜罐%'");
					}
				}
			} else if(Typecho_Request::getInstance()->get("attack","all") == "all" && Typecho_Request::getInstance()->get("bugtype","all") != "all"){
				if(Typecho_Request::getInstance()->get("isContain",0)){
					$model = $model->where("vulnerability like '%蜜罐%'")->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%");
					$total = $total->where("vulnerability like '%蜜罐%'")->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%");
				} else {
					$model = $model->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%");
					$total = $total->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%");
				}
			} else if(Typecho_Request::getInstance()->get("attack","all") != "all" && Typecho_Request::getInstance()->get("bugtype","all") != "all"){
				if(Typecho_Request::getInstance()->get("isContain",0)){
					$model = $model->where("vulnerability like '%蜜罐%'")->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%")->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"));
					$total = $total->where("vulnerability like '%蜜罐%'")->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%")->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"));
				} else {
					$model = $model->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%")->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"));
					$total = $total->where("vulnerability like ?","%".Typecho_Request::getInstance()->get("bugtype","all")."%")->where("client_ip = ?",Typecho_Request::getInstance()->get("attack","all"));
				}
			}
			$logs = $this->_model->fetchAll($model->offset((Typecho_Request::getInstance()->get("page",1)-1)*$this->pageSize)->limit($this->pageSize)->order('id', Typecho_Db::SORT_DESC));
			$clientIps = $this->_model->fetchRow($this->_model->select(["GROUP_CONCAT(DISTINCT(client_ip))"=>"attackip"])->from('table.honeypot_log')->order('id', Typecho_Db::SORT_DESC))["attackip"];
			$this->_view->setPage($this->pageSize,$this->_model->fetchRow($total)["total"]);
			$this->_view->assign("clientips",explode(",",$clientIps))->assign("tbody",$logs)->fetch(__FUNCTION__);
		}

		private function screen($logs){
			$clientips = [];
			$log = [];
			foreach($logs as $key => $value){
				if(!in_array($value["client_ip"], $clientips) && strpos($value["url"],"ico")===false){
					$value["total"] = $this->_model->fetchRow($this->_model->select(["COUNT(client_ip)"=>"total"])->from('table.honeypot_log')->where("client_ip = ?",$value["client_ip"]))["total"];
					$log[] = $value;
					$clientips[] = $value["client_ip"];
				}
			}
			return $log;
		}
	}
?>