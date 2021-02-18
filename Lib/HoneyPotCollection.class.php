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
	class HoneyPotCollection{
		private static $instance;
		// 数据包
		private $dataPacket;
		// POST数据
		private $dataPost;
		// GET数据
		private $dataGet;
		// 来源url
		private $referer;
		// 漏洞类型
		private $vulnerability;
		// 客户端ip
		private $clientIp;
		// 服务端ip
		private $serverIp;
		// 服务端端口
		private $serverPort;
		// 当前url
		private $url;
		// 数据库句柄
		private $link;
		// location
		private $location = ["https://www.baidu.com","https://www.google.com","https://www.so.com","https://cn.bing.com"];

		// 单例入口
		public static function run(){
			if(!(self::$instance instanceof self))
			{
				self::$instance = new self();
			}
			return self::$instance;
		}

		// 构造方法
		private function __construct(){
			$this->initialization();
		}

		// 初始化数据
		private function initialization(){
			$this->initDb();
			$this->initGetData();
			$this->initPostData();
			$this->initdataPacket();
			$this->initReferer();
			$this->initClientIp();
			$this->initServerIp();
			$this->initServerPort();
			$this->initUrl();
			$this->screeningAttack();
			$this->addLogs();
			$this->enumeration();
		}

		// 获取get数据
		private function initGetData(){
			$this->dataGet = !@empty(http_build_query($_GET))?http_build_query($_GET):NULL;
		}

		// 获取POST数据
		private function initPostData(){
			if($_POST){
				$this->dataPost = http_build_query($_POST);
			} else {
				$this->dataPost = @htmlspecialchars(file_get_contents("php://input"));
			}
		}

		// 获取数据包
		private function initdataPacket(){
			$headers = [];
			if (!function_exists('getallheaders'))   
			{
				foreach ($_SERVER as $name => $value)
				{
					if (substr($name, 0, 5) == 'HTTP_')
					{
						$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
					}
				}
			} else {
				$headers = getallheaders();
			}
			$headers[$_SERVER["REQUEST_METHOD"]] = $_SERVER["REQUEST_URI"];
			foreach(array_reverse($headers) as $header=>$value){
				if(!$value){
					continue;
				}
				$this->dataPacket .= htmlspecialchars($header).((in_array($header,array("GET","POST","DELETE","PUT")))?" ":":").htmlspecialchars($value)."\n";
			}
			if(array_key_exists("POST",$headers)){
				$this->dataPacket .= "\n".$this->dataPost;
			}
		}

		// 获取来源地址
		private function initReferer(){
			$this->referer = $_SERVER["HTTP_REFERER"]?urlencode($_SERVER["HTTP_REFERER"]):"";
		}

		// 获取客户端ip
		private function initClientIp(){
			$this->clientIp = $_SERVER["REMOTE_ADDR"];
		}

		// 获取服务端ip
		private function initServerIp(){
			$this->serverIp = $_SERVER["SERVER_ADDR"];
		}

		// 获取服务端开放端口
		private function initServerPort(){
			$this->serverPort = $_SERVER["SERVER_PORT"];
		}
		// 获取URL
		private function initUrl(){
			$this->url = $_SERVER["REQUEST_URI"]?urlencode($_SERVER["REQUEST_URI"]):"";
		}

		// 筛选攻击
		private function screeningAttack(){
			$attck = new HoneyPotAttack($this->dataPacket,$this->referer);
			$this->vulnerability = $attck->getAttackType();
		}

		// 获取数据库model
		private function initDb(){
			$this->link = Typecho_Db::get();
		}

		// 插入数据
		private function addLogs(){
			$insert = $this->link->insert('table.honeypot_log')->rows(
				array(
					"data_packet" => $this->dataPacket,
					"client_ip" => $this->clientIp,
					"server_ip" => $this->serverIp,
					"server_port" => $this->serverPort,
					"url" => $this->url,
					"post_data" => $this->dataPost,
					"get_data" => $this->dataGet,
					"vulnerability" => $this->vulnerability,
					"referer" => $this->referer,
					"time" => time()
				));
			$this->link->query($insert);
			if(!strstr($this->vulnerability,"正常访问") && !strstr($this->vulnerability,"蜜罐")){
				$this->attackLocation();
			}
		}

		private function enumeration(){
			$db = Typecho_Db::get();
			$starttime = time();
			$entime = $starttime-0.25;
			$filename = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__."/HoneyPot/Lib/HoneyPotCache/{$this->clientIp}.php";
			if($db->fetchRow($db->query("select count(*) as total from `{$db->getPrefix()}honeypot_log` where client_ip='".addslashes($this->clientIp)."' AND `time` BETWEEN {$entime} AND {$starttime}"))["total"] >= 10){
				if(!is_file($filename)){
					file_put_contents($filename,"<?php /*".serialize(["time"=>time()])."*/?>");
				}
				$this->attackLocation();
			}
		}

		private function attackLocation(){
			if(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->block == 1){
				// print __TYPECHO_ADMIN_DIR__."options-plugin.php?config=HoneyPot";
				// print urldecode($this->url);
				// exit;
				if(!preg_match("#action\/plugins-edit\?config=HoneyPot#i",urldecode($this->url))){
					header("Location:".$this->location[rand(0,count($this->location)-1)]);
					exit();
				}
			}
		}

	}
?>