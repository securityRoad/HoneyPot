<?php
	if (!defined('__TYPECHO_ROOT_DIR__')) exit;
	if($db->fetchRow($db->select()->from('table.honeypot_log')->where("client_ip = ?",$_SERVER["REMOTE_ADDR"])->where("vulnerability like '%攻击者%'"))){
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