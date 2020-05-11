# MongoDB ReplSet Monitor
# 简介：
一款面向研发人员查看的MongoDB图形可视化监控工具，借鉴了Percona PMM Grafana以及官方自带的mongostat工具输出的监控指标项，去掉了一些不必要看不懂的监控项，目前采集了数据库连接数、QPS/TPS、内存使用率统计，副本集replset状态信息和同步复制延迟时长。

采用远程连接方式进去获取数据，所以无需要在数据库服务器端部署相关agent或计划任务，可实现微信和邮件报警。

注：监控环境为MongoDB 3.2以上版本，2.X版本未测试。

Mongo状态监控 
![image](https://s2.51cto.com/images/20200509/1589019055331742.png)

点击图表，可以查看历史曲线图

1、连接数
![image](https://s2.51cto.com/images/20200511/1589167815267374.png)

2、QPS
![image](https://s2.51cto.com/images/20200511/1589167824217720.png)

# 一、环境搭建

1、php-mysql驱动安装

shell> yum install -y php-pear php-devel php gcc openssl openssl-devel cyrus-sasl cyrus-sasl-devel httpd mysql php-mysql

2、php-mongo驱动安装：

shell> pecl install mongo

把extension=mongo.so加入到/etc/php.ini最后一行。

重启httpd服务，service httpd restart

（注：如果通过pecl安装报错，请参考以下链接，进行源码安装。PHP 5.4版本对应的驱动版本是mongodb-1.3.4.tgz

https://www.runoob.com/mongodb/mongodb-install-php-driver.html ）


3、创建mongodb超级用户权限（监控采集数据时使用）

首先我们在被监控的数据库端创建授权帐号，允许采集器服务器能连接到Mongodb数据库。由于需要执行命令db.runCommand({serverStatus:1,repl:1}).repl和db.adminCommand( { replSetGetStatus: 1 } ).members，所以需要授予root角色，授权方式如下所示：

    > use admin
    > db.createUser({user:"admin",pwd:"123456",roles:[{role:"root",db:"admin"}]})
    
 
# 二、mongo_monitor部署

把https://github.com/hcymysql/mongo_monitor/archive/master.zip安装包解压缩到 /var/www/html/目录下

cd /var/www/html/mongo_monitor/

chmod 755 ./mail/sendEmail

chmod 755 ./weixin/wechat.py

（注：邮件和微信报警调用的第三方工具，所以这里要赋予可执行权限755）

1、导入Mongo Monitor监控工具表结构（mongo_monitor库）

cd /var/www/html/mongo_monitor/

    mysql -uroot -p123456 < mongo_monitor_schema.sql

2、录入被监控主机的信息

    INSERT INTO `mongo_status_info`
    (ip,tag,USER,pwd,PORT,authdb,send_mail_to_list,send_weixin_to_list,threshold_alarm_connection,threshold_alarm_repl)
    VALUES('10.10.159.31','MongoDB测试机1','admin','hechunyang','27017','admin','hechunyang','hechunyang@126.com',1000,60);

注，以下字段可以按照需求变更：

ip字段含义：输入被监控Mongo的IP地址

tag字段含义：输入被监控Mongo的业务名字

user字段含义：输入被监控Mongo的用户名（ROOT权限）

pwd字段含义：输入被监控Mongo的密码

port字段含义：输入被监控MySQL的端口号

authdb字段含义：输入被监控Mongo的数据库登录权限认证库名

monitor字段含义：0为关闭监控（也不采集数据，直接跳过）;1为开启监控（采集数据）

send_mail字段含义：0为关闭邮件报警;1为开启邮件报警

send_mail_to_list字段含义：邮件人列表

send_weixin字段含义：0为关闭微信报警;1为开启微信报警

send_weixin_to_list字段含义：微信公众号

threshold_alarm_connection字段含义：设置连接数阀值（单位个）

threshold_alarm_repl字段含义：设置主从复制延迟阀值（单位秒）


3、修改conn.php配置文件

# vim /var/www/html/mongo_monitor/conn.php

$con = mysqli_connect("127.0.0.1","admin","hechunyang","mongo_monitor","3306") or die("数据库链接错误".mysql_error());

改成你的Mongo Monitor监控工具表结构（mongo_monitor库）连接信息

4、修改邮件报警信息

# cd /var/www/html/mongo_monitor/mail/

# vim mail.php

system("./mail/sendEmail -f chunyang_he@139.com -t '{$this->send_mail_to_list}' -s 
smtp.139.com:25 -u '{$this->alarm_subject}' -o message-charset=utf8 -o message-content-type=html -m '报警信息：<br><font 
color='#FF0000'>{$this->alarm_info}</font>' -xu chunyang_he@139.com -xp 
'123456' -o tls=no");

改成你的发件人地址，账号密码，里面的变量不用修改。


5、修改微信报警信息

# cd /var/www/html/mongo_monitor/weixin/

# vim wechat.py

微信企业号设置移步

https://github.com/X-Mars/Zabbix-Alert-WeChat/blob/master/README.md 看此教程配置。

6、定时任务每分钟抓取一次

# crontab -l
    */1 * * * * cd /var/www/html/mongo_monitor; /usr/bin/php /var/www/html/mongo_monitor/check_mongo_status.php > /dev/null 2 >&1
    
    */1 * * * * cd /var/www/html/mongo_monitor; /usr/bin/php /var/www/html/mongo_monitor/check_mongo_repl.php > /dev/null 2 >&1
    
# check_mongo_status.php（用来采集被监控端Mongo状态信息和触发报警）

# check_mongo_repl.php（用来采集被监控端Mongo主从复制信息和触发报警）


7、更改页面自动刷新频率

# vim mongo_replset_monitor.php

    http-equiv="refresh" content="600"

默认页面每600秒自动刷新一次。


8、页面访问

http://yourIP/mongo_monitor/mongo_replset_monitor.php

加一个超链接，可方便地接入你们的自动化运维平台里。
    
    
