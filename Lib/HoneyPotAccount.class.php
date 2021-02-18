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
	class HoneyPotAccount{
		public static function getAccount(){
			$account = file_get_contents("php://input");
			if($account == @base64_encode(base64_decode($account))){
				list($platform,$account) = @explode("\t",base64_decode($account));
				if(!@empty($platform) && !@empty($account)){
					$db = Typecho_Db::get();
					$updatedata = $db->fetchRow($db->select()->from('table.honeypot_log')->where("client_ip = ?",$_SERVER["REMOTE_ADDR"])->limit(1)->order('id', Typecho_Db::SORT_DESC));
					$up = [$platform=>$account];
					$db->query($db->update('table.honeypot_log')->rows(array("platformaccount"=>@serialize($up)))->where('id=?',$updatedata["id"]));
				}
			}
			header("Content-Type:text/javascript");
			print <<<Html
/*
*	判断是否登录
*	isLogin.js
*/
Html;
		}
		public static function execute(){}
	}
?>