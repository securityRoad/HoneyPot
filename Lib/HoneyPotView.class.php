<?php
    if (!defined('__TYPECHO_ROOT_DIR__')) exit;
	class HoneyPotView{
        private $variable = [];
        private $pageNum;
        private $style = "#teamnewslist{height:600px;overflow:scroll;scrollbar-width: none; /* firefox */-ms-overflow-style: none; /* IE 10+ */overflow-x: hidden;overflow-y: auto;}#teamnewslist ol{list-style:none;margin-left: 36px;padding-left: 14px;border-left: 2px solid #eee;font-size: 18px;color: #666;}#teamnewslist b{font-size: 12px;font-weight: normal;color: #999;display: block;position: relative;margin-bottom:5px;}#teamnewslist b::after{position: absolute;top: 6px;left: -22px;content: '';width: 14px;height: 14px;border-radius: 50%;background-color: #fff;border: 2px solid #ccc;box-shadow: 2px 2px 0 rgba(255,255,255,1), -2px -2px 0 rgba(255,255,255,1)}#teamnewslist li{list-style:none;margin: 0 0 20px 0;line-height: 100%;}#teamnewslist li:hover{color: #555;}#teamnewslist li:hover b::after{border-color: #C01E22;}#teamnewslist li:hover b{color: #C01E22;}.element{max-height:0;overflow:hidden;transition:max-height .3s;}.check:checked ~ .element {max-height:1500px;}.check {position:absolute;clip:rect(0 0 0 0);}.check:checked ~ .check-in {display:none;}.check:checked ~ .check-out {display:inline-block;}.check-out {display:none;}.check-in,.check-out {color:#34538b;cursor:pointer;}
";

        public function __construct($pageSize,$total){
            $this->total = $total;
            $this->pageNum = ceil($total/$pageSize);
            $this->pluginurl = Typecho_Common::url('options-plugin.php?' .http_build_query(
                        array(
                        'config' => 'HoneyPot',
                    )),
            Typecho_Widget::widget('Widget_Options')->adminUrl);
        }

		public function index(){
			return <<<Html
<style type="text/css">
{$this->style}
</style>
<div class="main">
    <div class="body container">
        <div class="typecho-page-title">
           <h2>HoneyPot控制台</h2>
        </div>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12">
                <ul class="typecho-option-tabs fix-tabs clearfix">
                    <li class="current"><a href="#">{$this->bar('访问日志')}</a></li>
                    <li><a href="{$this->pluginurl}">{$this->bar('插件设置')}</a></li>
                </ul>
            </div>
        </div>
        {$this->indexlist()}
    </div>
</div>
Html;
		}

        public function detailed(){
            $addrip = null;
            if($addr = Typecho_Request::getInstance()->get("attack","all")){
                if($addr == "all"){
                    $addrip = "所有";
                } else {
                    $addrip = (filter_var(Typecho_Request::getInstance()->get("attack"), FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)?"(外网地址)":"(内网地址)").$addr;
                }
            }
            return <<<Html
<style type="text/css">
{$this->style}
</style>
<div class="main" >
    <div class="body container">
        <div class="typecho-page-title">
           <h2>HoneyPot控制台</h2>
        </div>
        <div class="row typecho-page-main" role="main">
            <div class="col-mb-12">
                <ul class="typecho-option-tabs fix-tabs clearfix">
                    <li class="current"><a href="#">{$this->bar($addrip.' 访问记录')}</a></li>
                    <li><a href="{$this->pluginurl}">{$this->bar('插件设置')}</a></li>
                </ul>
            </div>
        </div>
        {$this->detailedlist()}
    </div>
</div>
Html;
        }

		private function indexlist(){
            $honeypotlog = "";
            $request = Typecho_Request::getInstance();
            $url = Typecho_Common::url('extending.php?' .http_build_query(
                        array(
                        'panel' => 'HoneyPot/HoneyPotconsole.php',
                        'action' => 'detailed'
                    )),
            Typecho_Widget::widget('Widget_Options')->adminUrl);
            if(array_key_exists("honeylogs",$this->variable)){
                if($this->variable["honeylogs"][0]["total"]>0){
                    $honeylogs .= "<li>";
                    foreach ($this->variable["honeylogs"] as $value) {
                        $honeylogs .= "<b><a href=\"javascript::void();\">最后踩罐：".date('Y年m月d日 H:i:s',$value['time'])."</a></b>";
                        $honeylogs .= "<p><span>访问地址 : </span><a href=\"{$url}&attack={$value['client_ip']}\">".htmlspecialchars(urldecode($value['url']))."</a></p>";
                        $honeylogs .= "<p><span>踩罐者IP : </span>";
                        array_map(function($ip) use (&$honeylogs,$url){
                            $ipcontent = ["url"=>"","intranet"=>"","Account"=>""];
                            if(!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
                                $ipcontent["url"] = "{$url}&attack={$ip}&isContain=1";
                                $ipcontent["intranet"] = "内网地址";
                            } else {
                                $ipcontent["url"] = "https://tool.chinaz.com/ipwhois/?q={$ip}";
                                $ipcontent["intranet"] = "外网地址";
                            }
                            if(array_key_exists("ipandaccount",$this->variable)){
                                $account = null;
                                if(isset($this->variable["ipandaccount"][$ip]) && !empty($this->variable["ipandaccount"][$ip]["account"])){
                                    $account =  explode(",",ltrim($this->variable["ipandaccount"][$ip]["account"],","));
                                    $account = array_map(function($account){
                                        if($account){
                                            $account = unserialize($account);
                                            $key = array_keys($account)[0];
                                            return "{$key}:{$account[$key]}";
                                        }
                                    },$account);
                                    $ipcontent["content"] .= @implode(',',$account);
                                }
                                $honeylogs .= "<a href=\"{$ipcontent['url']}\">{$ip}({$ipcontent['intranet']})&emsp;";
                                if($ipcontent["content"]){
                                    $honeylogs .= "第三方账号({$ipcontent['content']})";
                                }
                                $honeylogs .= "</a>&nbsp;&nbsp;";
                            }
                        },explode(',',$value['client_ip']));
                        $honeylogs .= "</p>";
                        // 保留地址判断
                        
                        $honeylogs .= "<p><span>踩罐次数 : </span><a href=\"#\">{$value['total']}</a></p>";
                    }
                    $honeylogs .= "</li>";
                }
            }
            $list = "";
            if(array_key_exists("clientips",$this->variable)){
                foreach($this->variable["clientips"] as $ip){
                        $list .= "<option value=\"{$ip}\"".($request->get("attack")==$ip?'selected="true"':"").">{$ip}</option>";
                }
            }
			return <<<Html
			<div class="col-mb-12 typecho-list">
                <div class="typecho-list-operate clearfix">
                    <form method="get" action="{$url}" class="search-form">
                        <div class="search" role="search">
                            <input type="hidden" value="HoneyPot/HoneyPotconsole.php" name="panel" />
                            <input type="hidden" value="index" name="action" />
                            <input type="hidden" name="page" value="{$request->get("page",1)}">
                            <label for="attack">IP筛选</label>
                            <select name="attack" id="attack">
                                <option value="all">所有</option>
                                {$list}
                            </select>
                            &emsp;
                            <button type="submit" class="btn btn-s">{$this->bar('筛选')}</button>
                        </div>
                    </form>
                </div>
                <div id="teamnewslist">
                    <ol>
                        {$honeylogs}
                        {$this->tbody()}
                    </ol>
                </div>
                {$this->page()}
            </div>
Html;
		}

        private function detailedlist(){
            $url = Typecho_Common::url('extending.php?' .http_build_query(
                        array(
                        'panel' => 'HoneyPot/HoneyPotconsole.php',
                        'action' => 'detailed'
                    )),
            Typecho_Widget::widget('Widget_Options')->adminUrl);
            $request = Typecho_Request::getInstance();
            $isContain = $request->get("isContain",0)?'checked="true"':'';
            $rules = (array)json_decode(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->bugrules);
            $list = "";
            $rules["正常访问"] = "";
            $rules["目录/文件/参数枚举"] = "";
            $rules["暴力穷举"] = "";
            $rules["疑似攻击者"] = "";
            foreach($rules as $name => $value){
                $list .= "<option value=\"{$name}\"".($request->get("bugtype")==$name?'selected="true"':"").">{$name}</option>";
            }
            $ips = "";
            if(array_key_exists("clientips",$this->variable)){
                foreach($this->variable["clientips"] as $ip){
                        $ips .= "<option value=\"{$ip}\"".($request->get("attack")==$ip?'selected="true"':"").">{$ip}</option>";
                }
            }
            return <<<Html
            <div class="col-mb-12 typecho-list">
                <div class="typecho-list-operate clearfix">
                    <form method="get" action="{$url}" class="search-form">
                        <div class="search" role="search">
                            <input type="hidden" value="HoneyPot/HoneyPotconsole.php" name="panel" />
                            <input type="hidden" value="detailed" name="action" />
                            <input type="hidden" name="page" value="{$request->get("page",1)}">
                            <span>
                                <label for="isContain">踩罐</label>
                                <input name="isContain" type="checkbox" value="1" id="isContain" {$isContain}>
                            </span>
                            &emsp;
                            <label for="attack">IP筛选</label>
                            <select name="attack" id="attack">
                                <option value="all">所有</option>
                                {$ips}
                            </select>
                            &emsp;
                            <label for="bugtype">脆弱性探测</label>
                            <select name="bugtype" id="bugtype">
                                <option value="all">所有</option>
                                {$list}

                            </select>
                            &emsp;
                            <button type="submit" class="btn btn-s">{$this->bar('筛选')}</button>
                        </div>
                    </form>
                </div>
                <div id="teamnewslist">
                    <ol>
                        {$this->tbody()}
                    </ol>
                </div>
                {$this->page()}
            </div>
Html;
        }

        private function tbody(){
            $url = Typecho_Common::url('extending.php?' .http_build_query(
                        array(
                        'panel' => 'HoneyPot/HoneyPotconsole.php',
                        'action' => 'detailed'
                    )),
            Typecho_Widget::widget('Widget_Options')->adminUrl);
            if(array_key_exists("tbody", $this->variable)){
                $tbody = "";
                foreach($this->variable["tbody"] as $key=>$value){
                    $tbody .= "<li>";
                    if(Typecho_Request::getInstance()->get("action") == "detailed"){
                        $tbody .= "<b><a href=\"javascript::void();\">时间：".date('Y年m月d日 H:i:s',$value['time'])."</a></b>";
                        if(!Typecho_Request::getInstance()->get("attack") || Typecho_Request::getInstance()->get("attack")=="all"){
                            if(!filter_var($value['client_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))
                                $tbody .= "<p><span>来访者IP : </span><a href=\"{$url}&attack={$value['client_ip']}\">{$value['client_ip']}(内网地址)</a></p>";
                            else
                                $tbody .= "<p><span>来访者IP : </span><a target=\"_blank\" href=\"https://tool.chinaz.com/ipwhois/?q={$value['client_ip']}\">{$value['client_ip']}(公网地址)</a></p>";
                        }
                        if(strstr($value['vulnerability'],"正常访问")){
                            $tbody .= "<p> {$value['vulnerability']}</p>";
                        } else {
                            $tbody .= "<p> 攻击者探测脆弱性： {$value['vulnerability']}</p>";
                        }
                        $tbody .= "<p> 访问地址： ".htmlspecialchars(urldecode($value['url']))."</p>";
                        $tbody .= "<input class=\"check\" id=\"check{$key}\" type=\"checkbox\"><div class=\"element\">";
                        if($value['post_data'])
                            $tbody .= "<p> POST DATA： ".str_replace("&amp;","&",htmlspecialchars(urldecode($value['post_data'])))."</p>";
                        if($value['get_data'])
                            $tbody .= "<p> GET DATA： ".str_replace("&amp;","&",htmlspecialchars(urldecode($value['get_data'])))."</p>";
                        if($value['referer'])
                            $tbody .= "<p> 访问来源： ".str_replace("&amp;","&",htmlspecialchars(urldecode($value['referer'])))."</p>";
                        $tbody .= "<pre style=\"background:#272822;padding:.8em;margin:0.4em;line-height:1.5;word-wrap:break-word;border-radius:.8em;word-break:normal; color:#fd971f;\">".str_replace("&amp;","&",htmlspecialchars(urldecode($value['data_packet'])))."</pre>";
                        $tbody .= "</div>";
                        $tbody .= '<label for="check'.$key.'" class="check-in">展开↓</label><label for="check'.$key.'" class="check-out">收起↑</label>';
                    } else {
                        $ipcontent = "";
                        if(array_key_exists("ipandaccount",$this->variable)){
                            $account = null;
                            if(isset($this->variable["ipandaccount"][$value['client_ip']]) && !empty($this->variable["ipandaccount"][$value['client_ip']]["account"])){
                                $account =  explode(",",ltrim($this->variable["ipandaccount"][$value['client_ip']]["account"],","));
                                $account = array_map(function($account){
                                    if($account){
                                        $account = unserialize($account);
                                        $key = array_keys($account)[0];
                                        return "{$key}:{$account[$key]}";
                                    }
                                },$account);
                                $ipcontent = @implode(',',$account);
                            }
                        }
                        $tbody .= "<b><a href=\"javascript::void();\">最近访问：".date('Y年m月d日 H:i:s',$value['time'])."</a></b>";
                        $tbody .= "<p><span>访问地址 : </span><a href=\"{$url}&attack={$value['client_ip']}\">".htmlspecialchars(urldecode($value['url']))."</a></p>";
                        // 保留地址判断
                        if(!filter_var($value['client_ip'], FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)){
                            $tbody .= "<p><span>来访者IP : </span><a href=\"{$url}&attack={$value['client_ip']}\">{$value['client_ip']}(内网地址)</a></p>";
                        } else {
                            $tbody .= "<p><span>来访者IP : </span><a target=\"_blank\" href=\"https://tool.chinaz.com/ipwhois/?q={$value['client_ip']}\">{$value['client_ip']}(公网地址)</a></p>";
                        }
                        if($ipcontent){
                            $tbody .= "<p><span>第三方账号 : </span></span><a href=\"#\">{$ipcontent}</a></p>";
                        }
                        $tbody .= "<p><span>访问次数 : </span><a href=\"{$url}&attack={$value['client_ip']}\">{$value['total']}</a></p>";
                    }
                    $tbody .= "</li>";
                }
                return $tbody;
            }
        }

		private function bar($title){
			ob_start();
			_e($title);
			$ob_content = ob_get_contents();
			ob_end_clean();
			return $ob_content;
		}

        // 设置变量
        public function assign($variable,$value){
            if(!array_key_exists($variable,$this->variable))
                $this->variable[$variable] = $value;
            return $this;
        }

        // 模板解析
        public function fetch($tpl){
            if(method_exists(__CLASS__,$tpl)){
                print call_user_func([__CLASS__,$tpl]);
            }
        }

        public function setPage($pageSize,$total){
            $this->pageNum = ceil($total/$pageSize);
            $this->total = $total;
        }

        // 分页
        private function page()
        {
            $page = "";
            $currentpage = Typecho_Request::getInstance()->get("page",1);
            if($this->pageNum>=$currentpage){
                if($currentpage>1){
                    if(Typecho_Request::getInstance()->get("action") == "index")
                        $page .= "<li><a href=\"".$this->buildUrl(Typecho_Request::getInstance()->get("page",1)-1)."&action=index&attack=".Typecho_Request::getInstance()->get("attack","all")."\">&laquo;</a></li>";
                    else
                        $page .= "<li><a href=\"".$this->buildUrl(Typecho_Request::getInstance()->get("page",1)-1)."&action=detailed&attack=".Typecho_Request::getInstance()->get("attack")."&isContain=".Typecho_Request::getInstance()->get("isContain",0)."&bugtype=".Typecho_Request::getInstance()->get("bugtype","all")."\">&laquo;</a></li>";
                }
                $total = $this->total;
                $prevs = $currentpage-1;
                if($prevs <= 0) {
                    $prevs = 1;
                }
                $next = $currentpage + 5;
                if($next > $total) {
                    $next = $total;
                }
                for ($i = $prevs; $i < $currentpage - 1; $i++) {
                    if(Typecho_Request::getInstance()->get("action") == "index")
                        $page .= "<li><a href=\"".$this->buildUrl($i)."&action=index&attack=".Typecho_Request::getInstance()->get("attack","all")."\">{$i}</a></li>";
                    else
                        $page .= "<li><a href=\"".$this->buildUrl($i)."&action=detailed&attack=".Typecho_Request::getInstance()->get("attack")."&isContain=".Typecho_Request::getInstance()->get("isContain",0)."&bugtype=".Typecho_Request::getInstance()->get("bugtype","all")."\">{$i}</a></li>";
                }
                $page .= "<li class=\"current\"><a href=\"#\">".Typecho_Request::getInstance()->get("page",1)."</a></li>";
                for ($i = $currentpage + 1; $i < $next; $i++) {
                    if($this->pageNum>=$i)
                        if(Typecho_Request::getInstance()->get("action") == "index")
                            $page .= "<li><a href=\"".$this->buildUrl($i)."&action=index&attack=".Typecho_Request::getInstance()->get("attack","all")."\">{$i}</a></li>";
                        else
                            $page .= "<li><a href=\"".$this->buildUrl($i)."&action=detailed&attack=".Typecho_Request::getInstance()->get("attack")."&isContain=".Typecho_Request::getInstance()->get("isContain",0)."&bugtype=".Typecho_Request::getInstance()->get("bugtype","all")."\">{$i}</a></li>";
                }
                if($this->pageNum>$currentpage)
                    if(Typecho_Request::getInstance()->get("action") == "index")
                        $page .= "<li><a href=\"".((Typecho_Request::getInstance()->get("page",1)+1)<=$this->pageNum?$this->buildUrl(Typecho_Request::getInstance()->get("page",1)+1):"#")."&action=index&attack=".Typecho_Request::getInstance()->get("attack","all")."\">&raquo;</a></li>";
                    else
                        $page .= "<li><a href=\"".((Typecho_Request::getInstance()->get("page",1)+1)<=$this->pageNum?$this->buildUrl(Typecho_Request::getInstance()->get("page",1)+1):"#")."&action=detailed&attack=".Typecho_Request::getInstance()->get("attack")."&isContain=".Typecho_Request::getInstance()->get("isContain",0)."&bugtype=".Typecho_Request::getInstance()->get("bugtype","all")."\">&raquo;</a></li>";
            } else {
                if(Typecho_Request::getInstance()->get("action") == "index")
                    header("Location:".$this->buildUrl(1)."&action=index&attack=".Typecho_Request::getInstance()->get("attack","all"));
                else
                    header("Location:".$this->buildUrl(1)."&action=detailed&attack=all&bugtype=all");
            }
            return <<<Html
            <ul class="typecho-pager">
                {$page}
            </ul>
Html;
        }

        private function buildUrl($page)
        {
            $url = Typecho_Common::url('extending.php?' . http_build_query(
                array(
                    'panel' => 'HoneyPot/HoneyPotconsole.php',
                    'page' => $page
                )),
            Typecho_Widget::widget('Widget_Options')->adminUrl);
            return $url;
        }
	}
?>