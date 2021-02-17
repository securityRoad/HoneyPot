<?php
	if (!defined('__TYPECHO_ROOT_DIR__')) exit;
	class HoneyPotAttack{
		private $dataPacket;
		private $referer;
		private $attackType = [];
		private $honeypot;
		private $isLogin;
		private $routingTable;
		private $page;

		// 构造方法
		public function __construct($dataPacket,$referer=null){
			session_start();
			$this->routingTable = Typecho_Widget::widget('Widget_Options')->routingTable[0];
			$this->page = explode(",",Typecho_Db::get()->fetchRow(Typecho_Db::get()->select(["GROUP_CONCAT(slug,',',cid)"=>"slug"])->from('table.contents'))["slug"]);
			$this->dataPacket = $dataPacket;
			$this->referer = $referer;
			$this->isLogin = is_file(__TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__."/HoneyPot/Lib/HoneyPotCache/".md5("{$_SERVER['REMOTE_ADDR']}isLogin"))?true:false;
			$this->rules = json_decode(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->bugrules);
			$this->honeypot = Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->HoneyPotTpl;
			$this->testing($referer);
		}

		// 检测脆弱性探测
		private function testing(){
			foreach($this->honeypot as $honeypot){
				if(preg_match_all("#((get|post|put|delete)\s*\/".str_replace("HoneyPot","\/",$honeypot)."?|referer\s*:\s*".Typecho_Request::getInstance()->getRequestRoot()."\/".str_replace("HoneyPot","\/",$honeypot).")#i",urldecode($this->dataPacket))){
					$this->attackType[] = "踩到了".str_replace("HoneyPot","",$honeypot)."蜜罐";
				}
			}
			foreach($this->rules as $key => $value){
				if(!$value){
					continue;
				}
				$rule = null;
				if(is_array($value)){
					$rule = str_replace("~","\\",implode('|',$value));
					if(count($value)>1){
						$rule = "({$rule})";
					}
				} else {
					$rule = str_replace("~","\\",$value);
				}
				if(preg_match_all("/{$rule}/i", str_replace(urldecode(urldecode($this->referer)),"",urldecode($this->dataPacket)))){
					$this->attackType[] = "疑似攻击者访问，触发{$key}";
				}
			}
			if(!$this->isExhaustion(Typecho_Request::getInstance()->getPathInfo()) && !preg_match("#".Typecho_Request::getInstance()->getRequestRoot()."#i",urldecode($this->referer)) && $_SERVER["REQUEST_URI"] != "/"){
				$this->attackType[] = "目录/文件/参数枚举";
				if(!isset($_SESSION["exhaustioncount"])){
					$_SESSION["exhaustiondircount"] = 1;
				} else {
					$_SESSION["exhaustiondircount"]++;
				}
			}
			if(preg_match("#^\/".trim(__TYPECHO_ADMIN_DIR__,"/")."\/login.php#i",Typecho_Request::getInstance()->getRequestURI())){
				if(isset($_SESSION["exhaustiondircount"]) && $_SESSION["exhaustiondircount"]>1){
					$this->attackType[] = "攻击者穷举{$_SESSION['exhaustiondircount']}次后找到后台";
				} else {
					unset($_SESSION["exhaustiondircount"]);
				}
			}
			if(Typecho_Request::getInstance()->isPost() && preg_match("#".Typecho_Request::getInstance()->getRequestRoot().__TYPECHO_ADMIN_DIR__."login.php"."#i",urldecode($this->referer))){
				if(!isset($_SESSION["exhaustionpasscount"])){
					$_SESSION["exhaustionpasscount"] = 1;
				} else if($_SESSION["exhaustionpasscount"]>=2){
					$this->attackType[] = "攻击者第{$_SESSION['exhaustionpasscount']}次穷举用户(".Typecho_Request::getInstance()->get("name").")的密码";
					$_SESSION["exhaustionpasscount"]++;
				} else {
					$_SESSION["exhaustionpasscount"]++;
				}
			}
			if($this->isLogin){
				if(isset($_SESSION["exhaustionpasscount"]) && $_SESSION["exhaustionpasscount"]>1){
					if(preg_match("#^\/".trim(__TYPECHO_ADMIN_DIR__,"/")."\/(index|extending).php#i",Typecho_Request::getInstance()->getRequestURI()) && $_SESSION["exhaustionpasscount"] != 9999999999){
						$this->attackType[] = "疑似攻击者登录后台，穷举{$_SESSION['exhaustionpasscount']}次后登录成功";
						$_SESSION["exhaustionpasscount"] == 9999999999;
					} else if($_SESSION["exhaustionpasscount"] == 9999999999){
						$this->attackType[] = "疑似攻击者访问";
					}
				}
			}
			if(empty($this->attackType)){
				$this->attackType[] = "正常访问";
			}
		}

		// 返回漏洞类型
		public function getAttackType(){
			return implode(",",$this->attackType);
		}

		private function isExhaustion($pathInfo)
	    {
	        foreach ($this->routingTable as $key => $route) {
	        	if(strpos($route["widget"],"HoneyPot")!==false){
	        		continue;
	        	}
	            if (preg_match($route['regx'], $pathInfo)) {
	            	foreach($this->page as $page){
	            		if(preg_match("#.*/".$page."(/|\.html)$#i",$pathInfo)){
	            			return true;
	            		}
	            	}
	            }
	        }
	        return false;
	    }
	}
?>