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
	class ThinkPHPHoneyPot{
		public static function index(){
			header("Location:/thinkphp/index/index");
		}

		public function indexview(){
			print <<<Html
<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V 5.0.24<br/><span style="font-size:30px;">14载初心不改 - 你值得信赖的PHP框架</span></p><span style="font-size:25px;">[ V5.0.24 版本由 <a href="https://www.yisu.com/" target="yisu">亿速云</a> 独家赞助发布 ]</span></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="ee9b1aa918103c4fc"></think>
Html;
		}

		public function execute(){}

	}
?>