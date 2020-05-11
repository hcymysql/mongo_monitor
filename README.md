# MongoDB ReplSet Monitor
# 简介：
一款面向研发人员查看的MongoDB图形可视化监控工具，借鉴了Percona PMM Grafana以及官方自带的mongostat工具输出的监控指标项，去掉了一些不必要看不懂的监控项，目前采集了数据库连接数、QPS/TPS、内存使用率统计，副本集replset状态信息和同步复制延迟时长，采用远程连接方式进去获取数据，所以无需要在数据库服务器端部署相关agent或计划任务，可实现微信和邮件报警。


Mongo状态监控 
![image](https://raw.githubusercontent.com/hcymysql/mongo_monitor/master/demo_image/%E9%A6%96%E9%A1%B5.png)

点击图表，可以查看历史曲线图

1、连接数
![image](https://raw.githubusercontent.com/hcymysql/mongo_monitor/master/demo_image/%E8%BF%9E%E6%8E%A5%E6%95%B0.png)

2、QPS
![image](https://raw.githubusercontent.com/hcymysql/mongo_monitor/master/demo_image/QPS.png)

# 环境搭建

1、php-mysql驱动安装

shell> yum install -y php-pear php-devel php gcc openssl openssl-devel cyrus-sasl cyrus-sasl-devel httpd mysql php-mysql

2、php-mongo驱动安装：

shell> pecl install mongo

把extension=mongo.so加入到/etc/php.ini最后一行。

重启httpd服务，service httpd restart

3、创建mongodb超级用户权限（监控采集数据时使用）

首先我们在被监控的数据库端创建授权帐号，允许采集器服务器能连接到Mongodb数据库。由于需要执行命令db.runCommand({serverStatus:1,repl:1}).repl和db.adminCommand( { replSetGetStatus: 1 } ).members，所以需要授予root角色，授权方式如下所示：

    > use admin
    > db.createUser({user:"admin",pwd:"123456",roles:[{role:"root",db:"admin"}]})
    
 
