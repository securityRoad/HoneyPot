<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Safe之路打造HoneyPot插件为个人安全插件及日志管理，方便管控
 * 
 * @package HoneyPot
 * @author Only_rain
 * @version 1.0
 * @link http://github.com/securityRoad
 */
class HoneyPot_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        $msg = static::install();
        Helper::addPanel(1, 'HoneyPot/HoneyPotconsole.php', _t('HoneyPot控制台'), _t('HoneyPot插件控制台'), 'subscriber');
        Typecho_Plugin::factory('admin/menu.php')->navBar = array(__CLASS__, 'render');
        Typecho_Plugin::factory('admin/footer.php')->end = array(__CLASS__, 'adminFooter');
        Typecho_Plugin::factory('Widget_Archive')->footer = array(__CLASS__, 'addjs');
        Helper::addRoute("Account", "/isLogin.js", "HoneyPotAccount", "getAccount");
        return $msg;
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){
        if(Typecho_Db::get()->fetchRow(Typecho_Db::get()->select()->from('table.honeypot_log')->where("client_ip = ?",$_SERVER["REMOTE_ADDR"])->where("vulnerability like '%攻击者%'"))){
            header("Location:".__TYPECHO_ADMIN_DIR__);
            exit;
        }
        $config = Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot');
        if ($config->clear == 1) {
            $db = Typecho_Db::get();
            $db->query("DROP TABLE `{$db->getPrefix()}honeypot_log`", Typecho_Db::WRITE);
        }
        Helper::removePanel(1, 'HoneyPot/HoneyPotconsole.php');
        Helper::removeRoute("Account");
        if($HoneyPotTpl = Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->HoneyPotTpl){
            foreach($HoneyPotTpl as $honeypot){
                if($honeypot == "AdminHoneyPot"){
                    Helper::removeRoute($honeypot);
                    Helper::removeRoute("{$honeypot}login");
                    Helper::removeRoute("{$honeypot}normalizecss");
                    Helper::removeRoute("{$honeypot}gridcss");
                    Helper::removeRoute("{$honeypot}stylecss");
                    Helper::removeRoute("{$honeypot}jqueryjs");
                    Helper::removeRoute("{$honeypot}jqueryjs");
                    Helper::removeRoute("{$honeypot}jqueryuijs");
                    Helper::removeRoute("{$honeypot}typechojs");
                    Helper::removeRoute("{$honeypot}logo");
                } else {
                    Helper::removeRoute($honeypot);
                    Helper::removeRoute("{$honeypot}view");
                    Helper::removeRoute("{$honeypot}view1");
                }
            }
        }
        
        file_put_contents(__TYPECHO_ROOT_DIR__."/config.inc.php",str_replace("\nrequire_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HoneyPot/Honeypot.php';","",file_get_contents(__TYPECHO_ROOT_DIR__."/config.inc.php")));
        $dbfile = file_get_contents(__TYPECHO_ROOT_DIR__."/var/Typecho/Db/Query.php");
        if(preg_match_all("#const\s*KEYWORDS\s*=\s*['|\"](\S*)['|\"]#i",$dbfile, $matchs)){
        	$dbfile = str_replace("|NOTLIKE","",$dbfile);
        	file_put_contents(__TYPECHO_ROOT_DIR__."/var/Typecho/Db/Query.php",$dbfile);
        }
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        if(Typecho_Db::get()->fetchRow(Typecho_Db::get()->select()->from('table.honeypot_log')->where("client_ip = ?",$_SERVER["REMOTE_ADDR"])->where("vulnerability like '%攻击者%'"))){
            header("Location:".__TYPECHO_ADMIN_DIR__);
            exit;
        }
        $capture = new Typecho_Widget_Helper_Form_Element_Radio('pluginget',
            array('1' => _t('捕获'),
            '0' => _t('取消')),
            '0', _t('捕获本插件日志'));
        $clear = new Typecho_Widget_Helper_Form_Element_Radio('clear',
            array('1' => _t('是'),
            '0' => _t('否')),
            '0', _t('禁用时清空并删除数据表'));
        $block = new Typecho_Widget_Helper_Form_Element_Radio('block',
            array('1' => _t('是'),
            '0' => _t('否')),
            '0', _t('攻击者URL跳转'));
        $HoneyPotTpl = new Typecho_Widget_Helper_Form_Element_Checkbox('HoneyPotTpl',
            array('AdminHoneyPot' => _t('Admin蜜罐模板'),
            'ThinkPHPHoneyPot' => _t('ThinkPHP蜜罐')),
            array("AdminHoneyPot","ThinkPHPHoneyPot"),
            "蜜罐配置 (只读)"
        );
        $loginthreshold = new Typecho_Widget_Helper_Form_Element_Select(
            'loginthreshold', array(
                'three' => '3',
                'six' => '6'
            ), 'three', _t('后台登录阈值'));
        $filethreshold = new Typecho_Widget_Helper_Form_Element_Select(
            'filethreshold', array(
                'three' => '3',
                'six' => '6'
            ), 'three', _t('后台登录界面枚举阈值'));
        $vulnerability = new Typecho_Widget_Helper_Form_Element_Textarea('bugrules', NULL, '{"SQL注入": ["(?:(union(.*?)select))","(?:(?:current_)user|database|schema|connection_id)~s*~(","into(~s+)+(?:dump|out)file~s*"],"XSS":["(onmouseover|onerror|onload|onabort|onblur|onchange|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onresize|onselect|onsubmit|onunload)~=","~<(iframe|script|body|img|layer|div|meta|style|base|object|input)"]}', _t('漏洞匹配规则配置(请自行检测JSON格式)请将\\写为~'));

        $other = new Typecho_Widget_Helper_Form_Element_Textarea('otherrules', NULL, '{"baidu":["url","user"]}', _t('第三方账号获取规则配置(请自行检测JSON格式)\\写为~'));
        $form->addInput($capture);
        $form->addInput($clear);
        $form->addInput($block);
        $form->addInput($HoneyPotTpl);
        $form->addInput($loginthreshold);
        $form->addInput($filethreshold);
        $form->addInput($vulnerability);
        $form->addInput($other);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render()
    {
        $loginfile = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__."/HoneyPot/Lib/HoneyPotCache/".md5("{$_SERVER['REMOTE_ADDR']}isLogin");
        if(!is_file($loginfile) && Typecho_Widget::widget('Widget_User')->hasLogin()){
            file_put_contents($loginfile,"");
        }
        // 获取路由表
        $routingTable = Typecho_Widget::widget('Widget_Options')->routingTable;
        if($HoneyPotTpl = Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->HoneyPotTpl){
            foreach($HoneyPotTpl as $honeypot){
                if(class_exists($honeypot)){
                    if(!array_key_exists($honeypot,$routingTable)){
                        if($honeypot == "AdminHoneyPot"){
                            Helper::addRoute($honeypot, "/admin", $honeypot, "adminview");
                            Helper::addRoute("{$honeypot}login", "/admin/login.php", $honeypot, "loginview");
                            Helper::addRoute("{$honeypot}normalizecss", "/admin/css/normalize.css", $honeypot, "normalize");
                            Helper::addRoute("{$honeypot}gridcss", "/admin/css/grid.css", $honeypot, "grid");
                            Helper::addRoute("{$honeypot}stylecss", "/admin/css/style.css", $honeypot, "style");
                            Helper::addRoute("{$honeypot}jqueryjs", "/admin/js/jquery.js", $honeypot, "jquery");
                            Helper::addRoute("{$honeypot}jqueryjs", "/admin/js/jquery.js", $honeypot, "jquery");
                            Helper::addRoute("{$honeypot}jqueryuijs", "/admin/js/jquery-ui.js", $honeypot, "jqueryui");
                            Helper::addRoute("{$honeypot}typechojs", "/admin/js/typecho.js", $honeypot, "typechojs");
                            Helper::addRoute("{$honeypot}logo", "/admin/img/typecho-logo.svg", $honeypot, "typechologo");
                        } else {
                            Helper::addRoute($honeypot, "/".strtolower(str_replace("HoneyPot","",$honeypot))."/", $honeypot, "index");
                            Helper::addRoute($honeypot."view", "/".strtolower(str_replace("HoneyPot","",$honeypot))."/index/index", $honeypot, "indexview");
                            Helper::addRoute($honeypot."view1", "/".strtolower(str_replace("HoneyPot","",$honeypot))."/index", $honeypot, "indexview");
                        }
                    }
                }
            }
        }
    }

    /*
    *   插件安装
    */
    public static function install(){
        $dbfile = file_get_contents(__TYPECHO_ROOT_DIR__."/var/Typecho/Db/Query.php");
        if(preg_match_all("#const\s*KEYWORDS\s*=\s*['|\"](\S*)['|\"]#i",$dbfile, $matchs)){
        	$dbfile = str_replace($matchs[1][0],"{$matchs[1][0]}|NOTLIKE",$dbfile);
        	file_put_contents(__TYPECHO_ROOT_DIR__."/var/Typecho/Db/Query.php",$dbfile);
        }
        if (substr(trim(dirname(__FILE__), '/'), -8) != 'HoneyPot') {
            throw new Typecho_Plugin_Exception(_t('插件目录名必须为HoneyPot'));
        }
        $db = Typecho_Db::get();
        $adapterName = $db->getAdapterName();
        file_put_contents(__TYPECHO_ROOT_DIR__."/config.inc.php", "\nrequire_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HoneyPot/Honeypot.php';" , FILE_APPEND);
        if (strpos($adapterName, 'Mysql') !== false) {
            $prefix  = $db->getPrefix();
            $scripts = file_get_contents(__DIR__.'/sql/honeypot.sql');
            try {
                $configLink = '<a href="' . Helper::options()->adminUrl . 'options-plugin.php?config=HoneyPot">' . _t('前往设置') . '</a>';
                # 初始化数据库如果不存在
                if (!$db->fetchRow($db->query("SHOW TABLES LIKE '{$prefix}honeypot_log';", Typecho_Db::READ))){
                    $db->query($scripts, Typecho_Db::WRITE);
                    $msg = _t('成功创建数据表，插件启用成功') . $configLink;
                    return $msg;
                }
            } catch (Typecho_Db_Exception $e) {
                throw new Typecho_Plugin_Exception(_t('数据表建立失败，插件启用失败，错误信息：%s。', $e->getMessage()));
            }
        }
    }

    public static function adminFooter()
    {
        $url = $_SERVER['PHP_SELF'];
        $filename = substr($url, strrpos($url, '/') + 1);
        if ($filename == 'index.php') {
            echo '<script>
$(document).ready(function() {
  $("#start-link").append("<li><a href=\"';
            Helper::options()->adminUrl('extending.php?panel=HoneyPot/HoneyPotconsole.php');
            echo '\">' . _t('HoneyPot控制台') . '</a></li>");
});
</script>';
        }
    }
    public static function addjs(){
        $loginfile = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__."/HoneyPot/Lib/HoneyPotCache/".md5("{$_SERVER['REMOTE_ADDR']}isLogin");
        if(!Typecho_Widget::widget('Widget_User')->hasLogin() && is_file($loginfile)){
            unlink($loginfile);
            unset($_SESSION["exhaustionpasscount"]);
        }
        $filename = __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__."/HoneyPot/Lib/HoneyPotCache/{$_SERVER["REMOTE_ADDR"]}.php";
        if(!is_file($filename)){
            return;
        }
        $time = unserialize(str_replace(["/","*","<?php ","?>"],"",file_get_contents($filename)))["time"];
        if((time()-60*60) >= $time){
            unlink($filename);
        } else {
            $otherrules = json_decode(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->otherrules);
            $otherjstpl = "$.ajax({url:'#url',datatype:'jsonp',success:function(res){\$.ajax({url:'/isLogin.js',dataType:'json',data:Base64.encode('#platform'+\"\t\"+res.#param),type:\"POST\",success:function(data){}});}})";
            $other = [];
            foreach($otherrules as $platform => $param){
                $other[] = str_replace(["#url","#platform","#param"],[@$param[0],@$platform,@$param[1]],$otherjstpl);
            }
            $other = implode(";",$other);
            print <<<Html
        <script src="http://libs.baidu.com/jquery/2.0.0/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/js-base64@3.6.0/base64.min.js"></script>
        <script type="text/javascript">
            $(function(){
                {$other}
            });
        </script>
Html;
        }
    }
    public function execute(){}
}
