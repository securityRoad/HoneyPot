<?php
	if (!defined('__TYPECHO_ROOT_DIR__')) exit;
	class HoneyPotClassLoader{
		private static $instance;
		private static $class = [
			"HoneyPotCollection"			=> __DIR__."/HoneyPotCollection.class.php",
			"HoneyPot"						=> __DIR__."/HoneyPot.class.php",
			"HoneyPotAttack"					=> __DIR__."/HoneyPotAttack.class.php",
			"HoneyPotView"					=> __DIR__."/HoneyPotView.class.php",
			"HoneyPotController"			=> __DIR__."/HoneyPotController.class.php",
			"AdminHoneyPot"					=> __DIR__."/HoneyPotView/AdminHoneyPot.class.php",
			"ThinkPHPHoneyPot"				=> __DIR__."/HoneyPotView/ThinkPHPHoneyPot.class.php",
			"HoneyPotAccount"				=> __DIR__."/HoneyPotAccount.class.php"
		];
		private function __construct(){
			spl_autoload_register([$this,"_loadClass"]);
		}

		// 单例入口
		public static function run(){
			if(!(self::$instance instanceof self))
			{
				self::$instance = new self();
			}
			return self::$instance;
		}

		// 自动加载类方法
		private function _loadClass($class){
			if(!array_key_exists($class, static::$class)){
				return;
			}
			if(isset(static::$class[$class]) && is_file(static::$class[$class])){
				require_once static::$class[$class];
			}
		}
	}
?>