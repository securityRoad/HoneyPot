<?php
	/**
	 * Safe之路打造HoneyPot插件为个人安全插件及日志管理，方便管控
	 * 
	 * @package HoneyPot
	 * @author Only_rain
	 * @version 1.0
	 * @link http://github.com/securityRoad
	 */
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
			$this->isLogin = isset($_SESSION["isLogin"])?true:false;
			$this->loginthreshold = (Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->loginthreshold == "three")?3:6;
			$this->filethreshold = (Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->filethreshold == "three")?3:6;
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
					$this->attackType[] = "疑似攻击者触发{$key}";
				}
			}
			if(!$this->isExhaustion(Typecho_Request::getInstance()->getPathInfo()) && !preg_match("#".Typecho_Request::getInstance()->getRequestRoot()."#i",urldecode($this->referer)) && $_SERVER["REQUEST_URI"] != "/"){
				$this->attackType[] = "目录/文件/参数枚举";
				if(!isset($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustiondircount"])){
					$_SESSION[$_SERVER['REMOTE_ADDR']."exhaustiondircount"] = 1;
				} else {
					$_SESSION[$_SERVER['REMOTE_ADDR']."exhaustiondircount"]++;
				}
			}
			if(preg_match("#^\/".trim(__TYPECHO_ADMIN_DIR__,"/")."\/login.php#i",Typecho_Request::getInstance()->getRequestURI())){
				if(isset($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustiondircount"]) && $_SESSION[$_SERVER['REMOTE_ADDR']."exhaustiondircount"]>$this->filethreshold && !isset($_SESSION[$_SERVER['REMOTE_ADDR']."loginpageinit"])){
					$this->attackType[] = "疑似攻击者目录/文件/参数枚举".$_SESSION[$_SERVER['REMOTE_ADDR'].'exhaustiondircount']."次后找到后台";
					unset($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustiondircount"]);
					$_SESSION[$_SERVER['REMOTE_ADDR']."loginpageinit"] = 1;
				} else if(isset($_SESSION[$_SERVER['REMOTE_ADDR']."loginpageinit"])){
					$this->attackType[] = "疑似攻击者进入登录页面";
				} else {
					$this->attackType[] = "进入后台登录页面";
					unset($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustiondircount"]);
				}
			}
			if(Typecho_Request::getInstance()->isPost() && preg_match("#".Typecho_Request::getInstance()->getRequestRoot().__TYPECHO_ADMIN_DIR__."login.php#i",urldecode($this->referer))){
				if(!isset($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"])){
					$_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"] = 1;
				} else if($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"]>$this->loginthreshold){
					$this->attackType[] = "疑似攻击者第".($_SESSION[$_SERVER['REMOTE_ADDR'].'exhaustionpasscount']-$this->loginthreshold)."次暴力穷举用户(".Typecho_Request::getInstance()->get("name","未知").")的密码";
					$_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"]++;
				} else {
					$_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"]++;
				}
			}
			if($this->isLogin){
				if(isset($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"]) && $_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"]>$this->loginthreshold){
					$this->attackType[] = "疑似攻击者暴力穷举".$_SESSION[$_SERVER['REMOTE_ADDR'].'exhaustionpasscount']."次后登录成功";
					unset($_SESSION[$_SERVER['REMOTE_ADDR']."exhaustionpasscount"]);
				}
				if (preg_match("#^\/".trim(__TYPECHO_ADMIN_DIR__,"/")."\/.*#i",Typecho_Request::getInstance()->getRequestURI()) && isset($_SESSION[$_SERVER['REMOTE_ADDR']."loginpageinit"])){
					$this->attackType[] = "疑似攻击者后台任意操作";
				} else if(isset($_SESSION[$_SERVER['REMOTE_ADDR']."loginpageinit"])){
					$this->attackType[] = "疑似攻击者页面信息探测";
				} else if(preg_match("#\/action\/.*#i",Typecho_Request::getInstance()->getRequestURI())){
					unset($this->attackType);
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

		private function isExhaustion($pathInfo){
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