# Typecho :honeybee: 蜜罐 :honey_pot: /日志审计安全插件

[![PHP Version](https://img.shields.io/badge/php-%3E%3D5.6-8892BF.svg)](http://www.php.net/)
![HoneyPot Plugin Version](https://img.shields.io/badge/HoneyPot%20Version-v1.0-red.svg)
![HoneyPot Plugin Package Size](https://img.shields.io/badge/Compressed%20Package%20Size-24KB-blue.svg)

![](https://ss0.bdstatic.com/70cFvHSh_Q1YnxGkpoWK1HF6hhy/it/u=2216788854,1851210222&fm=26&gp=0.jpg)

### 现有功能概括
- 蜜罐功能
- 日志采集（不依赖WEB容器的日志采集）
- 数据包捕获
- 第三方账号获取
- 捕获攻击行为
- 自定义攻击行为
- 自定义第三方账号获取
- 触发攻击行为URL跳转（payload无法执行）
- 暴力穷举（目录/文件/参数/管理登录）识别
- 插件安全化（识别到攻击身份登录后台后无法操作本插件）

### 待完善功能
- 静态文件请求捕获（WEB容器日志）
- Payload打包
- 账号密码字典打包
- 数据备份

### 图示
![](https://gitee.com/securityRoad/images/raw/main/202102171544211.png)
![](https://gitee.com/securityRoad/images/raw/main/202102171608251.png)
![](https://gitee.com/securityRoad/images/raw/main/20210217161138.png)
![](https://gitee.com/securityRoad/images/raw/main/20210217161627.png)
![](https://gitee.com/securityRoad/images/raw/main/20210217190854.png)

### 使用说明
1. 给网站添加伪静态
    - Nginx
      ```Nginx
        location / {
           if (!-e $request_filename) {
           rewrite  ^(.*)$  /index.php?s=$1  last;
           break;
        }
      ```
    - Apache在根目录下修改.htaccess文件内容如下
  
      ```Apache
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ /index.php/$1 [L]
      ```
2. 更改管理目录，不含admin关键字，config.inc.php以及目录名称
      ```php
        /** 后台路径(相对路径) */
        define('__TYPECHO_ADMIN_DIR__', '/manager/');
      ```
3. 将HoneyPot包放到/站点根目录/usr/plugins目录下

4. 插件会添加如下代码到config.inc.php文件中
    ```php
      require_once __TYPECHO_ROOT_DIR__.__TYPECHO_PLUGIN_DIR__.'/HoneyPot/Honeypot.php';
    ```
5. Typecho不支持not like查询，插件会将notlike关键字写入到Query.php
    ```php
       /** 数据库关键字 */
      const KEYWORDS = '*PRIMARY|AND|OR|LIKE|BINARY|BY|DISTINCT|AS|IN|IS|NULL|NOTLIKE';
    ```

[gitee地址](https://gitee.com/securityRoad/HoneyPot)
