# Typecho 蜜罐/日志审计安全插件

[![PHP Version](https://img.shields.io/badge/php-%3E%3D5.6-8892BF.svg)](http://www.php.net/)
![HoneyPot Plugin Version](https://img.shields.io/badge/HoneyPot%20Version-v1.0-red.svg)
![HoneyPot Plugin Package Size](https://img.shields.io/badge/Compressed%20Package%20Size-92KB-blue.svg)

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
![插件配置](https://github.com/securityRoad/images/raw/main/20210217154421.png)
![数据归并](https://raw.githubusercontent.com/securityRoad/images/main/202102171608251.png)
![日志页面](https://raw.githubusercontent.com/securityRoad/images/main/20210217161138.png)
![](https://raw.githubusercontent.com/securityRoad/images/main/20210217161627.png)
