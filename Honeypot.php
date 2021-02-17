<?php
	if (!defined('__TYPECHO_ROOT_DIR__')) exit;
	include_once __DIR__."/Lib/HoneyPotClassLoader.class.php";
	HoneyPotClassLoader::run();
	HoneyPotCollection::run();
?>