-- MySQL dump 10.19  Distrib 10.3.39-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: griffith
-- ------------------------------------------------------
-- Server version	10.3.39-MariaDB

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
-- Table structure for table `achannels`
--

DROP TABLE IF EXISTS `achannels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `achannels` (
  `achannel_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`achannel_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `acodecs`
--

DROP TABLE IF EXISTS `acodecs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `acodecs` (
  `acodec_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`acodec_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `collections` (
  `collection_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `loaned` tinyint(1) NOT NULL,
  PRIMARY KEY (`collection_id`),
  UNIQUE KEY `name` (`name`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`loaned` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `configuration` (
  `param` varchar(16) NOT NULL,
  `value` varchar(128) NOT NULL,
  PRIMARY KEY (`param`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `filters`
--

DROP TABLE IF EXISTS `filters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `filters` (
  `name` varchar(64) NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `languages` (
  `lang_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`lang_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loans`
--

DROP TABLE IF EXISTS `loans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loans` (
  `loan_id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  `volume_id` int(11) DEFAULT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  PRIMARY KEY (`loan_id`),
  KEY `person_id` (`person_id`),
  KEY `movie_id` (`movie_id`),
  KEY `volume_id` (`volume_id`),
  KEY `collection_id` (`collection_id`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`person_id`),
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`),
  CONSTRAINT `loans_ibfk_3` FOREIGN KEY (`volume_id`) REFERENCES `volumes` (`volume_id`),
  CONSTRAINT `loans_ibfk_4` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`collection_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media`
--

DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media` (
  `medium_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`medium_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movie_lang`
--

DROP TABLE IF EXISTS `movie_lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movie_lang` (
  `ml_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` smallint(6) DEFAULT NULL,
  `movie_id` int(11) NOT NULL,
  `lang_id` int(11) NOT NULL,
  `acodec_id` int(11) DEFAULT NULL,
  `achannel_id` int(11) DEFAULT NULL,
  `subformat_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`ml_id`),
  KEY `movie_id` (`movie_id`),
  KEY `lang_id` (`lang_id`),
  KEY `acodec_id` (`acodec_id`),
  KEY `achannel_id` (`achannel_id`),
  KEY `subformat_id` (`subformat_id`),
  CONSTRAINT `movie_lang_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`),
  CONSTRAINT `movie_lang_ibfk_2` FOREIGN KEY (`lang_id`) REFERENCES `languages` (`lang_id`),
  CONSTRAINT `movie_lang_ibfk_3` FOREIGN KEY (`acodec_id`) REFERENCES `acodecs` (`acodec_id`),
  CONSTRAINT `movie_lang_ibfk_4` FOREIGN KEY (`achannel_id`) REFERENCES `achannels` (`achannel_id`),
  CONSTRAINT `movie_lang_ibfk_5` FOREIGN KEY (`subformat_id`) REFERENCES `subformats` (`subformat_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movie_tag`
--

DROP TABLE IF EXISTS `movie_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movie_tag` (
  `mt_id` int(11) NOT NULL AUTO_INCREMENT,
  `movie_id` int(11) DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`mt_id`),
  KEY `movie_id` (`movie_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `movie_tag_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`),
  CONSTRAINT `movie_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(11) NOT NULL,
  `collection_id` int(11) DEFAULT NULL,
  `volume_id` int(11) DEFAULT NULL,
  `medium_id` int(11) DEFAULT NULL,
  `ratio_id` int(11) DEFAULT NULL,
  `vcodec_id` int(11) DEFAULT NULL,
  `poster_md5` varchar(32) DEFAULT NULL,
  `loaned` tinyint(1) NOT NULL,
  `seen` tinyint(1) NOT NULL,
  `rating` smallint(6) DEFAULT NULL,
  `color` smallint(6) DEFAULT NULL,
  `cond` smallint(6) DEFAULT NULL,
  `layers` smallint(6) DEFAULT NULL,
  `region` smallint(6) DEFAULT NULL,
  `media_num` smallint(6) DEFAULT NULL,
  `runtime` smallint(6) DEFAULT NULL,
  `year` smallint(6) DEFAULT NULL,
  `width` smallint(6) DEFAULT NULL,
  `height` smallint(6) DEFAULT NULL,
  `barcode` varchar(32) DEFAULT NULL,
  `o_title` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `director` varchar(255) DEFAULT NULL,
  `screenplay` varchar(255) DEFAULT NULL,
  `cameraman` varchar(255) DEFAULT NULL,
  `o_site` varchar(255) DEFAULT NULL,
  `site` varchar(255) DEFAULT NULL,
  `trailer` varchar(255) DEFAULT NULL,
  `country` varchar(128) DEFAULT NULL,
  `genre` varchar(128) DEFAULT NULL,
  `studio` varchar(128) DEFAULT NULL,
  `classification` varchar(128) DEFAULT NULL,
  `cast` text DEFAULT NULL,
  `plot` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `image` varchar(128) DEFAULT NULL,
  `created` datetime DEFAULT NULL,
  `updated` datetime DEFAULT NULL,
  PRIMARY KEY (`movie_id`),
  UNIQUE KEY `ix_movies_number` (`number`),
  KEY `collection_id` (`collection_id`),
  KEY `volume_id` (`volume_id`),
  KEY `medium_id` (`medium_id`),
  KEY `ratio_id` (`ratio_id`),
  KEY `vcodec_id` (`vcodec_id`),
  KEY `poster_md5` (`poster_md5`),
  KEY `ix_movies_o_title` (`o_title`),
  KEY `ix_movies_title` (`title`),
  CONSTRAINT `movies_ibfk_1` FOREIGN KEY (`collection_id`) REFERENCES `collections` (`collection_id`),
  CONSTRAINT `movies_ibfk_2` FOREIGN KEY (`volume_id`) REFERENCES `volumes` (`volume_id`),
  CONSTRAINT `movies_ibfk_3` FOREIGN KEY (`medium_id`) REFERENCES `media` (`medium_id`),
  CONSTRAINT `movies_ibfk_4` FOREIGN KEY (`ratio_id`) REFERENCES `ratios` (`ratio_id`),
  CONSTRAINT `movies_ibfk_5` FOREIGN KEY (`vcodec_id`) REFERENCES `vcodecs` (`vcodec_id`),
  CONSTRAINT `movies_ibfk_6` FOREIGN KEY (`poster_md5`) REFERENCES `posters` (`md5sum`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`loaned` in (0,1)),
  CONSTRAINT `CONSTRAINT_2` CHECK (`seen` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `people` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(128) DEFAULT NULL,
  `phone` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`person_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `posters`
--

DROP TABLE IF EXISTS `posters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `posters` (
  `md5sum` varchar(32) NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`md5sum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ratios`
--

DROP TABLE IF EXISTS `ratios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ratios` (
  `ratio_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(5) NOT NULL,
  PRIMARY KEY (`ratio_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subformats`
--

DROP TABLE IF EXISTS `subformats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subformats` (
  `subformat_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`subformat_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vcodecs`
--

DROP TABLE IF EXISTS `vcodecs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vcodecs` (
  `vcodec_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  PRIMARY KEY (`vcodec_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `volumes`
--

DROP TABLE IF EXISTS `volumes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `volumes` (
  `volume_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `loaned` tinyint(1) NOT NULL,
  PRIMARY KEY (`volume_id`),
  UNIQUE KEY `name` (`name`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`loaned` in (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-08 23:00:09
