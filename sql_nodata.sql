/*
SQLyog Ultimate v12.09 (64 bit)
MySQL - 10.1.31-MariaDB : Database - mh
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
CREATE DATABASE /*!32312 IF NOT EXISTS*/`mh` /*!40100 DEFAULT CHARACTER SET utf8 */;

USE `mh`;

/*Table structure for table `mh` */

DROP TABLE IF EXISTS `mh`;

CREATE TABLE `mh` (
  `auther` varchar(200) DEFAULT NULL,
  `cjid` varchar(200) DEFAULT NULL,
  `cjname` varchar(200) DEFAULT NULL,
  `cjstatus` varchar(200) DEFAULT NULL,
  `cover` varchar(200) DEFAULT NULL,
  `create_time` varchar(200) DEFAULT NULL,
  `desc` varchar(200) DEFAULT NULL,
  `diyu_id` varchar(200) DEFAULT NULL,
  `duzhequn_id` varchar(200) DEFAULT NULL,
  `h` varchar(200) DEFAULT NULL,
  `id` varchar(200) DEFAULT NULL,
  `maid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `image` varchar(200) DEFAULT NULL,
  `isfree` varchar(200) DEFAULT NULL,
  `ishot` varchar(200) DEFAULT NULL,
  `isjingpin` varchar(200) DEFAULT NULL,
  `isnew` varchar(200) DEFAULT NULL,
  `issole` varchar(200) DEFAULT NULL,
  `keyword` varchar(200) DEFAULT NULL,
  `lanmu_id` varchar(200) DEFAULT NULL,
  `last_chapter` varchar(200) DEFAULT NULL,
  `last_chapter_title` varchar(200) DEFAULT NULL,
  `mark` varchar(200) DEFAULT NULL,
  `mhstatus` varchar(200) DEFAULT NULL,
  `pingfen` varchar(200) DEFAULT NULL,
  `searchnums` varchar(200) DEFAULT NULL,
  `sort` varchar(200) DEFAULT NULL,
  `status` varchar(200) DEFAULT NULL,
  `ticai` varchar(200) DEFAULT NULL,
  `ticai_id` varchar(200) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `tjswitch` varchar(200) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `update_time` varchar(200) DEFAULT NULL,
  `view` varchar(200) DEFAULT NULL,
  `vipcanread` varchar(200) DEFAULT NULL,
  `xianmian` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`maid`)
) ENGINE=InnoDB AUTO_INCREMENT=517 DEFAULT CHARSET=utf8;

/*Table structure for table `mh_zj` */

DROP TABLE IF EXISTS `mh_zj`;

CREATE TABLE `mh_zj` (
  `cjid` varchar(200) DEFAULT NULL,
  `cjname` varchar(200) DEFAULT NULL,
  `cjstatus` varchar(200) DEFAULT NULL,
  `content` varchar(200) DEFAULT NULL,
  `create_time` varchar(200) DEFAULT NULL,
  `id` varchar(200) DEFAULT NULL,
  `image` varchar(200) DEFAULT NULL,
  `isvip` varchar(200) DEFAULT NULL,
  `manhua_id` varchar(200) DEFAULT NULL,
  `score` varchar(200) DEFAULT NULL,
  `sort` varchar(200) DEFAULT NULL,
  `switch` varchar(200) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `type` varchar(200) DEFAULT NULL,
  `update_time` varchar(200) DEFAULT NULL,
  `view` varchar(200) DEFAULT NULL,
  `maid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `adate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `mhid` varchar(200) DEFAULT NULL,
  `buy` varbinary(200) DEFAULT NULL,
  `dir_str` varchar(200) DEFAULT NULL,
  `image_suffix` varchar(200) DEFAULT NULL,
  `pic_count` int(10) DEFAULT '0',
  `image_start_index` int(2) DEFAULT '1',
  `image_suffix_check` int(2) DEFAULT '0' COMMENT '1是已经检查',
  PRIMARY KEY (`maid`)
) ENGINE=InnoDB AUTO_INCREMENT=17686 DEFAULT CHARSET=utf8;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
