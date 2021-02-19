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
	if(Typecho_Db::get()->fetchRow(Typecho_Db::get()->select(["COUNT(*)"=>"total"])->from('table.honeypot_log')->where("client_ip = ?",$_SERVER["REMOTE_ADDR"])->where("vulnerability like '%攻击者%'"))["total"]>3){
        header("Location:".__TYPECHO_ADMIN_DIR__);
        exit;
    }
	include_once 'common.php';
	include 'header.php';
	include 'menu.php';
	HoneyPotController::build($request->get("action","index"))->initialization();
	include 'copyright.php';
	include 'common-js.php';
	include 'table-js.php';
?>