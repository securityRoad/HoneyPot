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
	class AdminHoneyPot{
		public static function adminview(){
			static::loginview();
		}

		public static function typechologo(){
			header("Content-Type:image/svg+xml;");
			print file_get_contents(Helper::options()->adminUrl."img/typecho-logo.svg");
		}

		public static function loginview(){
			$url = Helper::options()->siteUrl;
            $otherrules = json_decode(Typecho_Widget::widget('Widget_Options')->plugin('HoneyPot')->otherrules);
            $otherjstpl = "$.ajax({url:'#url',datatype:'jsonp',success:function(res){\$.ajax({url:'/isLogin.js',dataType:'json',data:Base64.encode('#platform'+\"\t\"+res.#param),type:\"POST\",success:function(data){}});}})";
            $other = [];
            foreach($otherrules as $platform => $param){
                $other[] = str_replace(["#url","#platform","#param"],[@$param[0],@$platform,@$param[1]],$otherjstpl);
            }
            $other = implode(";",$other);
			print <<<Html
<!DOCTYPE HTML>
<html class="no-js">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="renderer" content="webkit">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>登录到Hello World - Hello World - Powered by Typecho</title>
        <meta name="robots" content="noindex, nofollow">
        <link rel="stylesheet" href="{$url}/admin/css/normalize.css?v=17.10.30">
<link rel="stylesheet" href="{$url}/admin/css/grid.css?v=17.10.30">
<link rel="stylesheet" href="{$url}/admin/css/style.css?v=17.10.30">
	</head>
    <body class="body-100">
<div class="typecho-login-wrap">
    <div class="typecho-login">
        <h1><a href="http://typecho.org" class="i-logo">Typecho</a></h1>
        <form action="{$url}/action/login?_=ad111b4fcfadbab1f7f5cdb6b9d59bac" method="post" name="login" role="form">
            <p>
                <label for="name" class="sr-only">用户名</label>
                <input type="text" id="name" name="name" value="" placeholder="用户名" class="text-l w-100" autofocus />
            </p>
            <p>
                <label for="password" class="sr-only">密码</label>
                <input type="password" id="password" name="password" class="text-l w-100" placeholder="密码" />
            </p>
            <p class="submit">
                <button type="submit" class="btn btn-l w-100 primary">登录</button>
                <input type="hidden" name="referer" value="{$url}/admin/" />
            </p>
            <p>
                <label for="remember"><input type="checkbox" name="remember" class="checkbox" value="1" id="remember" /> 下次自动登录</label>
            </p>
        </form>
        
        <p class="more-link">
            <a href="{$url}">返回首页</a>
                    </p>
    </div>
</div>
<script src="{$url}/admin/js/jquery.js?v=17.10.30"></script>
<script src="{$url}/admin/js/jquery-ui.js?v=17.10.30"></script>
<script src="{$url}/admin/js/typecho.js?v=17.10.30"></script>
<script src="https://cdn.jsdelivr.net/npm/js-base64@3.6.0/base64.min.js"></script>
<script type="text/javascript">
    $(function(){
        {$other};
    });
</script>
<script>
    (function () {
        \$(document).ready(function() {
            // 处理消息机制
            (function () {
                var prefix = '0fd097d7cb636ab1f1d2279e7dee1233',
                    cookies = {
                        notice      :   \$.cookie(prefix + '__typecho_notice'),
                        noticeType  :   \$.cookie(prefix + '__typecho_notice_type'),
                        highlight   :   \$.cookie(prefix + '__typecho_notice_highlight')
                    },
                    path = '/';

                if (!!cookies.notice && 'success|notice|error'.indexOf(cookies.noticeType) >= 0) {
                    var head = \$('.typecho-head-nav'),
                        p = \$('<div class="message popup ' + cookies.noticeType + '">'
                        + '<ul><li>' + \$.parseJSON(cookies.notice).join('</li><li>') 
                        + '</li></ul></div>'), offset = 0;

                    if (head.length > 0) {
                        p.insertAfter(head);
                        offset = head.outerHeight();
                    } else {
                        p.prependTo(document.body);
                    }

                    function checkScroll () {
                        if (\$(window).scrollTop() >= offset) {
                            p.css({
                                'position'  :   'fixed',
                                'top'       :   0
                            });
                        } else {
                            p.css({
                                'position'  :   'absolute',
                                'top'       :   offset
                            });
                        }
                    }

                    \$(window).scroll(function () {
                        checkScroll();
                    });

                    checkScroll();

                    p.slideDown(function () {
                        var t = \$(this), color = '#C6D880';
                        
                        if (t.hasClass('error')) {
                            color = '#FBC2C4';
                        } else if (t.hasClass('notice')) {
                            color = '#FFD324';
                        }

                        t.effect('highlight', {color : color})
                            .delay(5000).fadeOut(function () {
                            \$(this).remove();
                        });
                    });

                    
                    \$.cookie(prefix + '__typecho_notice', null, {path : path});
                    \$.cookie(prefix + '__typecho_notice_type', null, {path : path});
                }

                if (cookies.highlight) {
                    \$('#' + cookies.highlight).effect('highlight', 1000);
                    \$.cookie(prefix + '__typecho_notice_highlight', null, {path : path});
                }
            })();


            // 导航菜单 tab 聚焦时展开下拉菜单
            (function () {
                \$('#typecho-nav-list').find('.parent a').focus(function() {
                    \$('#typecho-nav-list').find('.child').hide();
                    \$(this).parents('.root').find('.child').show();
                });
                \$('.operate').find('a').focus(function() {
                    \$('#typecho-nav-list').find('.child').hide();
                });
            })();
        });
    })();
</script>
<script>
\$(document).ready(function () {
    \$('#name').focus();
});
</script>
    </body>
</html>
Html;
		}

		public static function normalize(){
			header("Content-Type: text/css;charset=utf-8");
			print file_get_contents(Helper::options()->adminUrl."css/normalize.css");
		}

		public static function grid(){
			header("Content-Type: text/css");
			print file_get_contents(Helper::options()->adminUrl."css/grid.css?v=17.10.30");
		}

		public static function style(){
			header("Content-Type: text/css;charset=utf-8");
            print file_get_contents(Helper::options()->adminUrl."css/style.css?v=17.10.30");
		}

		public static function jquery(){
			header("Content-Type: text/javascript;charset=utf-8");
            print file_get_contents(Helper::options()->adminUrl."js/jquery.js?v=17.10.30");
		}

		public static function jqueryui(){
			header("Content-Type: text/javascript;charset=utf-8");
            print file_get_contents(Helper::options()->adminUrl."js/jquery-ui.js");
		}

		public static function typechojs(){
			header("Content-Type:text/javascript;charset=utf-8");
            print file_get_contents(Helper::options()->adminUrl."js/typecho.js?v=17.10.30");
		}

		public function execute(){
		}
	}
?>