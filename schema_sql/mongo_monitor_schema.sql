/*
SQLyog Professional v10.42 
MySQL - 8.0.19 : Database - mongo_monitor
*********************************************************************
*/


/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`iqj_mongo_monitor` /*!40100 DEFAULT CHARACTER SET utf8 */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `iqj_mongo_monitor`;

/*Table structure for table `mongo_repl_status` */

DROP TABLE IF EXISTS `mongo_repl_status`;

CREATE TABLE `mongo_repl_status` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(30) DEFAULT NULL,
  `tag` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `port` INT DEFAULT NULL,
  `role` VARCHAR(20) DEFAULT NULL,
  `is_alive` VARCHAR(10) DEFAULT NULL,
  `Seconds_Behind_Master` INT DEFAULT NULL,
  `create_time` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_i_t_p` (`ip`,`tag`,`port`)
) ENGINE=INNODB AUTO_INCREMENT=9470 DEFAULT CHARSET=utf8;

/*Table structure for table `mongo_status` */

DROP TABLE IF EXISTS `mongo_status`;

CREATE TABLE `mongo_status` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `tag` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `port` INT DEFAULT NULL,
  `role` VARCHAR(10) DEFAULT NULL,
  `is_alive` VARCHAR(10) DEFAULT NULL,
  `threads_connected` INT DEFAULT NULL,
  `available_connected` INT DEFAULT NULL,
  `qps_select` INT DEFAULT NULL,
  `qps_insert` INT DEFAULT NULL,
  `qps_update` INT DEFAULT NULL,
  `qps_delete` INT DEFAULT NULL,
  `mem_usage` FLOAT DEFAULT NULL,
  `runtime` INT DEFAULT NULL,
  `db_version` VARCHAR(100) DEFAULT NULL,
  `create_time` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `IX_i_t_p` (`ip`,`tag`,`port`)
) ENGINE=INNODB AUTO_INCREMENT=9487 DEFAULT CHARSET=utf8;

/*Table structure for table `mongo_status_history` */

DROP TABLE IF EXISTS `mongo_status_history`;

CREATE TABLE `mongo_status_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ip` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `tag` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `port` INT DEFAULT NULL,
  `role` VARCHAR(10) DEFAULT NULL,
  `is_alive` VARCHAR(10) DEFAULT NULL,
  `threads_connected` INT DEFAULT NULL,
  `available_connected` INT DEFAULT NULL,
  `qps_select` INT DEFAULT NULL,
  `qps_insert` INT DEFAULT NULL,
  `qps_update` INT DEFAULT NULL,
  `qps_delete` INT DEFAULT NULL,
  `mem_usage` FLOAT DEFAULT NULL,
  `runtime` INT DEFAULT NULL,
  `db_version` VARCHAR(100) DEFAULT NULL,
  `create_time` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IX_i_p_t_c` (`ip`,`tag`,`port`,`create_time`)
) ENGINE=INNODB AUTO_INCREMENT=9478 DEFAULT CHARSET=utf8;

/*Table structure for table `mongo_status_info` */

DROP TABLE IF EXISTS `mongo_status_info`;

CREATE TABLE `mongo_status_info` (
  `id` INT NOT NULL AUTO_INCREMENT COMMENT '主键自增ID',
  `ip` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控Mongo的IP地址',
  `tag` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控Mongo的主机名字',
  `user` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控Mongo的用户名',
  `pwd` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控Mongo的密码',
  `port` INT DEFAULT NULL COMMENT '输入被监控Mongo的端口号',
  `authdb` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '输入被监控Mongo的数据库名',
  `monitor` TINYINT DEFAULT '1' COMMENT '0为关闭监控;1为开启监控',
  `send_mail` TINYINT DEFAULT '1' COMMENT '0为关闭邮件报警;1为开启邮件报警',
  `send_mail_to_list` VARCHAR(255) DEFAULT NULL COMMENT '邮件人列表',
  `send_weixin` TINYINT DEFAULT '1' COMMENT '0为关闭微信报警;1为开启微信报警',
  `send_weixin_to_list` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '微信公众号',
  `alarm_alive_status` TINYINT DEFAULT NULL COMMENT '记录主机存活的告警信息，1为已记录',
  `alarm_connection_status` TINYINT DEFAULT NULL COMMENT '记录活动连接数告警信息，1为已记录',
  `threshold_alarm_connection` INT DEFAULT NULL COMMENT '设置连接数阀值',
  `alarm_repl_status` TINYINT DEFAULT NULL COMMENT '记录主从复制告警信息，1为记录主从延迟状态',
  `threshold_alarm_repl` INT DEFAULT NULL COMMENT '设置主从复制延迟阀值',
  PRIMARY KEY (`id`),
  KEY `IX_i_t` (`ip`,`tag`)
) ENGINE=INNODB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COMMENT='监控信息表';

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

