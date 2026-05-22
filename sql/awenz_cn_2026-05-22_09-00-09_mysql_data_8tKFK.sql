-- MySQL dump 10.13  Distrib 5.7.44, for Linux (x86_64)
--
-- Host: localhost    Database: awenz_cn
-- ------------------------------------------------------
-- Server version	5.7.44-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `nav_apply`
--

DROP TABLE IF EXISTS `nav_apply`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nav_apply` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT '站点名称',
  `url` varchar(255) NOT NULL COMMENT '站点链接',
  `desc` text NOT NULL COMMENT '站点描述',
  `contact` varchar(100) NOT NULL COMMENT '联系方式',
  `cate_id` int(11) NOT NULL COMMENT '申请分类',
  `status` tinyint(1) DEFAULT '0' COMMENT '0待审核/1通过/2拒绝',
  `create_time` datetime DEFAULT CURRENT_TIMESTAMP COMMENT '申请时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COMMENT='网址申请表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_apply`
--

LOCK TABLES `nav_apply` WRITE;
/*!40000 ALTER TABLE `nav_apply` DISABLE KEYS */;
INSERT INTO `nav_apply` VALUES (1,'青禾演示界面','https://qinghe.xiaozheng.asia/','111','2297418679',1,1,'2026-01-12 13:25:42'),(9,'透明图片转换','https://ezremove.ai/zh/transparent-background/','透明图片转换','2297418679',10,1,'2026-01-13 18:04:30'),(10,'ico格式转换','https://www.xbgjw.com/ico','png转ico','2297418679',10,1,'2026-01-13 18:04:56'),(11,'康养云端','https://ky.awenz.cn/','康养云端演示界面','2297418679',1,1,'2026-01-13 18:09:07'),(12,'导航网后台','https://awenz.cn/','后台地址','2297418679',1,1,'2026-01-13 18:09:35'),(13,'康养云端管理端','https://ky.awenz.cn/admin.html','管理端','2297418679',1,1,'2026-01-13 18:13:31'),(14,'百度翻译','https://fanyi.baidu.com/mtpe-individual/transText#/','翻译','2297418679',10,1,'2026-01-13 21:10:23'),(15,'云盘','https://cloud.awenz.cn/','云盘','2297418679@qq.com',1,1,'2026-01-13 22:37:32'),(16,'百度','https://baidu.com','百度','2297418679',10,1,'2026-03-04 15:47:21'),(17,'360','http://360.cn','360','2297418679',10,1,'2026-03-04 15:58:29'),(18,'启梦对话平台','https://agent.awenz.cn','对接腾讯云智能体平台的智能体','2297418679',1,1,'2026-04-16 10:00:45'),(19,'临时邮箱','https://mails.luckyous.com','付费临时邮箱','2297418679',10,1,'2026-04-30 08:39:39'),(20,'API中转站','https://zz.imzr.top','GPT，Claude中转站','2297418679@qq.com',10,1,'2026-04-30 08:40:23'),(21,'超级智能体','http://chat.awenz.cn','个人','2297418679',1,1,'2026-05-22 08:53:05'),(22,'作品集','http://awenz.cn','个人','2297418679@qq.com',1,1,'2026-05-22 08:53:32');
/*!40000 ALTER TABLE `nav_apply` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nav_cate`
--

DROP TABLE IF EXISTS `nav_cate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nav_cate` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT '分类名称',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COMMENT='导航分类表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_cate`
--

LOCK TABLES `nav_cate` WRITE;
/*!40000 ALTER TABLE `nav_cate` DISABLE KEYS */;
INSERT INTO `nav_cate` VALUES (1,'个人开发',1,'2026-01-12 00:47:46'),(3,'娱乐休闲',3,'2026-01-12 00:47:46'),(4,'学习教育',4,'2026-01-12 00:47:46'),(10,'工具资源',2,'2026-01-12 00:50:10'),(14,'演示界面',0,'2026-01-13 18:15:35');
/*!40000 ALTER TABLE `nav_cate` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nav_notice`
--

DROP TABLE IF EXISTS `nav_notice`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nav_notice` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL COMMENT '公告标题',
  `content` text NOT NULL COMMENT '公告内容',
  `is_show` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否显示（1是/0否）',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='公告表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_notice`
--

LOCK TABLES `nav_notice` WRITE;
/*!40000 ALTER TABLE `nav_notice` DISABLE KEYS */;
INSERT INTO `nav_notice` VALUES (1,'欢迎使用瓜娃子导航','如果查找不到你需要的网页可向我们提交申请哦！',1,'2026-01-12 01:11:22','2026-03-04 03:28:08'),(2,'测试','测试',1,'2026-03-04 02:02:59','2026-03-04 03:18:57'),(3,'嗨喽','可以交互友链，欢迎大家提交收录申请哦！',1,'2026-03-04 03:29:08','2026-03-04 03:29:08');
/*!40000 ALTER TABLE `nav_notice` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nav_site`
--

DROP TABLE IF EXISTS `nav_site`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nav_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cate_id` int(11) NOT NULL COMMENT '分类ID',
  `name` varchar(100) NOT NULL COMMENT '站点名称',
  `url` varchar(255) NOT NULL COMMENT '站点链接',
  `desc` varchar(255) DEFAULT '' COMMENT '站点描述',
  `sort` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `click_num` int(11) NOT NULL DEFAULT '0' COMMENT '点击量',
  `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `icon` varchar(255) DEFAULT 'default.png' COMMENT '站点图标',
  `ico_url` varchar(255) DEFAULT NULL COMMENT '站点ICO地址',
  PRIMARY KEY (`id`),
  KEY `cate_id` (`cate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COMMENT='导航站点表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_site`
--

LOCK TABLES `nav_site` WRITE;
/*!40000 ALTER TABLE `nav_site` DISABLE KEYS */;
INSERT INTO `nav_site` VALUES (1,14,'教育之瞳','https://eye.xiaozheng.asia/','演示界面',1,124,'2026-01-12 00:47:46','default.png',NULL),(4,3,'B站','https://www.bilibili.com','年轻人的视频社区',1,115,'2026-01-12 00:47:46','default.png',NULL),(5,4,'菜鸟教程','https://www.runoob.com','编程学习教程',1,118,'2026-01-12 00:47:46','default.png',NULL),(16,1,'AI助手平台','https://ai.xiaozheng.asia/','支持DeepSeek和Qwen',0,125,'2026-01-12 00:56:21','default.png',NULL),(17,14,'青禾演示界面','https://qinghe.xiaozheng.asia/','演示',999,115,'2026-01-12 13:28:41','default.png',NULL),(18,10,'导航网','https://awenz.cn/','导航网首页',10,105,'2026-01-12 13:32:35','default.png',''),(19,1,'导航网','https://awenz.cn/','导航网',10,108,'2026-01-12 13:33:31','default.png',''),(20,3,'抖音','https://www.douyin.com/','抖音',999,116,'2026-01-12 16:33:46','default.png',NULL),(21,1,'个人网','http://xiaozheng.asia/','个人网',999,114,'2026-01-12 16:34:48','default.png',NULL),(22,10,'ico格式转换','https://www.xbgjw.com/ico','png转ico',999,96,'2026-01-13 18:08:14','default.png',NULL),(23,10,'透明图片转换','https://ezremove.ai/zh/transparent-background/','透明图片转换',999,104,'2026-01-13 18:08:17','default.png',NULL),(24,1,'导航网后台','https://awenz.cn/admin','后台地址',11,123,'2026-01-13 18:09:50','default.png',NULL),(25,14,'康养云端','https://ky.awenz.cn/','演示界面',999,137,'2026-01-13 18:09:52','default.png',NULL),(26,14,'康养云端管理端','https://ky.awenz.cn/admin','管理端nadmin123',999,110,'2026-01-13 18:13:36','default.png',NULL),(27,10,'百度翻译','https://fanyi.baidu.com/mtpe-individual/transText#/','翻译',999,97,'2026-01-13 21:44:54','default.png',NULL),(28,1,'云盘','https://cloud.awenz.cn/','云盘',999,113,'2026-01-13 22:37:37','default.png',NULL),(29,1,'康养云端-用户端','https://ky.awenz.cn/agent','用户端',999,74,'2026-02-16 15:17:35','default.png',NULL),(32,10,'360','http://360.cn','360',999,61,'2026-03-04 16:00:39','default.png',NULL),(33,10,'百度','https://baidu.com','百度',999,67,'2026-03-04 16:00:41','default.png',NULL),(34,10,'百度','https://baidu.com','百度',999,65,'2026-03-04 16:05:46','default.png',NULL),(35,10,'百度','https://baidu.com','百度',999,73,'2026-03-04 16:05:51','default.png',NULL),(36,1,'启梦对话平台','https://agent.awenz.cn','对接腾讯云智能体平台的智能体',998,30,'2026-04-16 10:00:58','default.png',NULL),(37,1,'康养云端-门户端','https://ky.awenz.cn/','门户端',1000,24,'2026-04-16 10:08:49','default.png',NULL),(38,1,'康养云端-管理端','https://ky.awenz.cn/','康养云端管理端',1001,23,'2026-04-16 10:09:31','default.png',NULL),(39,10,'API中转站','https://zz.imzr.top','GPT，Claude中转站',999,25,'2026-04-30 08:40:52','default.png',NULL),(40,10,'临时邮箱','https://mails.luckyous.com','付费临时邮箱',999,22,'2026-04-30 08:40:56','default.png',NULL),(41,1,'作品集','http://awenz.cn','个人',999,0,'2026-05-22 08:54:03','default.png',NULL),(42,1,'超级智能体','http://chat.awenz.cn','个人',999,0,'2026-05-22 08:54:06','default.png',NULL);
/*!40000 ALTER TABLE `nav_site` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `nav_system`
--

DROP TABLE IF EXISTS `nav_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `nav_system` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `config_key` varchar(50) NOT NULL COMMENT '配置键名',
  `config_value` text NOT NULL COMMENT '配置值',
  `config_desc` varchar(100) NOT NULL COMMENT '配置描述',
  PRIMARY KEY (`id`),
  UNIQUE KEY `config_key` (`config_key`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='系统配置表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `nav_system`
--

LOCK TABLES `nav_system` WRITE;
/*!40000 ALTER TABLE `nav_system` DISABLE KEYS */;
INSERT INTO `nav_system` VALUES (1,'site_name','瓜娃子导航 - awenz.cn','网站名称'),(2,'site_desc','欢迎大家提交申请！','网站描述'),(3,'site_icp','粤ICP备2024322061号-2','备案号'),(4,'footer_text','© 2026 awenz.cn 保留所有权利','底部版权文字'),(5,'notice_status','1','公告是否显示（1显示/0隐藏）'),(6,'notice_title','欢迎使用瓜娃子导航','公告标题'),(7,'notice_content','这是默认公告内容，管理员可在后台修改','公告内容'),(8,'notice_delay','5','弹窗自动关闭延迟（秒）'),(9,'admin_nav_title','瓜娃子管理端','后台顶部导航文字'),(10,'site_domain','awenz.cn','自动添加'),(11,'site_favicon','uploads/system/favicon_6964981ae2baf.ico','网站全局ICO图标（favicon）'),(12,'site_logo','uploads/system/logo_69a73531787d1.png','首页logo图片地址');
/*!40000 ALTER TABLE `nav_system` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'awenz_cn'
--

--
-- Dumping routines for database 'awenz_cn'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-22  9:00:09
