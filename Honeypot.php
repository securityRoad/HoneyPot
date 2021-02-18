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
	include_once __DIR__."/Lib/HoneyPotClassLoader.class.php";
	HoneyPotClassLoader::run();
	HoneyPotCollection::run();
?>