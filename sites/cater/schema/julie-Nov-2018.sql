-- MySQL dump 10.16  Distrib 10.1.26-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: JCAT
-- ------------------------------------------------------
-- Server version	10.1.26-MariaDB-0+deb9u1

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
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(125) DEFAULT NULL,
  `telephone` varchar(15) DEFAULT NULL,
  `email` varchar(45) DEFAULT NULL,
  `sendDate` datetime DEFAULT NULL,
  `body` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_UNIQUE` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
INSERT INTO `contact` VALUES (1,'Michael Rynn','0414632854','michael.rynn.500@gmail.com','2014-07-30 23:27:37','This is a test email sent using contact form at\r\nhttp://julies-catering.com.au/contact/index '),(2,'Michael Rynn','0414632854','michael.rynn.500@gmail.com','2014-07-31 01:09:49','Test from server (again).'),(3,'Michael Rynn','0414632854','michael.rynn.500@gmail.com','2014-07-31 01:15:13','Test from server (again).'),(4,'Michael Rynn','9632 8542','michael.rynn.500@gmail.com','2014-12-17 19:15:07','Your website has been updated'),(5,'Andrea Mahoney','0404703590','baerenfaenger@optusnet.com.au','2017-03-24 09:29:56','Hi,\r\nwe would like to celebrate my husband\'s 50th birthday at home and I would like to find out if you have availability to cater for a cocktail party event on the evening of the 29/04/ 2017 for about 40 people.\r\nAlso can you give an idea of cost including someone to serve on the night and what kind of food you do ( maybe you can send a menu)?\r\nThank you. Look forward to hearing from you.\r\nKind Regards,\r\nAndrea'),(6,'Andrea Mahoney','0404703590','baerenfaenger@optusnet.com.au','2017-03-24 09:30:18','Hi,\r\nwe would like to celebrate my husband\'s 50th birthday at home and I would like to find out if you have availability to cater for a cocktail party event on the evening of the 29/04/ 2017 for about 40 people.\r\nAlso can you give an idea of cost including someone to serve on the night and what kind of food you do ( maybe you can send a menu)?\r\nThank you. Look forward to hearing from you.\r\nKind Regards,\r\nAndrea'),(7,'Andrea Mahoney','0404703590','baerenfaenger@optusnet.com.au','2017-03-24 09:30:43','Hi,\r\nwe would like to celebrate my husband\'s 50th birthday at home and I would like to find out if you have availability to cater for a cocktail party event on the evening of the 29/04/ 2017 for about 40 people.\r\nAlso can you give an idea of cost including someone to serve on the night and what kind of food you do ( maybe you can send a menu)?\r\nThank you. Look forward to hearing from you.\r\nKind Regards,\r\nAndrea'),(8,'Erica','0402259326','Rheinberger.erica@gmail.con','2017-04-19 16:13:28','I\'d like a quote for a savoury and sweet afternoon tea on 6 May for about 30 people.   '),(9,'Erica','0402259326','Rheinberger.erica@gmail.con','2017-04-19 16:14:01','I\'d like a quote for a savoury and sweet afternoon tea on 6 May for about 30 people.   '),(10,'Ð˜Ñ€Ð¸Ð½Ð° Ð’Ð¸ÐºÑ‚Ð¾Ñ€Ð¾Ð²Ð½Ð°','87911679913','anacron@mail.ru','2017-04-21 03:02:56','Ð—Ð´Ñ€Ð°Ð²ÑÑ‚Ð²ÑƒÐ¹Ñ‚Ðµ, Ð¿Ñ€ÐµÐ´Ð»Ð°Ð³Ð°ÐµÐ¼ Ð’Ð°Ð¼ ÑƒÑÐ»ÑƒÐ³Ð¸ ÐºÐ¾Ð¼Ð¿Ð»ÐµÐºÑÐ½Ð¾Ð³Ð¾ Ð¿Ñ€Ð¾Ð´Ð²Ð¸Ð¶ÐµÐ½Ð¸Ñ Ð²Ð°ÑˆÐµÐ³Ð¾ ÑÐ°Ð¹Ñ‚Ð°, Ð¿Ð¾Ð´Ñ€Ð¾Ð±Ð½ÐµÐµ Ñ Ð½Ð°ÑˆÐ¸Ð¼Ð¸ ÑƒÑÐ»ÑƒÐ³Ð°Ð¼Ð¸ Ð’Ñ‹ Ð¼Ð¾Ð¶ÐµÑ‚Ðµ Ð¾Ð·Ð½Ð°ÐºÐ¾Ð¼Ð¸Ñ‚ÑŒÑÑ Ð¿Ð¾ ÑÑÑ‹Ð»ÐºÐµ http://www.anacron.ru/  Ð˜Ð·Ð²Ð¸Ð½Ð¸Ñ‚Ðµ Ð·Ð° Ð±ÐµÑÐ¿Ð¾ÐºÐ¾Ð¹ÑÑ‚Ð²Ð¾.'),(11,'Jenny Singh','0287992200','jenny.singh@pmi.com','2017-06-19 12:19:16','Im looking for catering for 75pp on Friday 30th June 2017 @ 12.00-1.00pm\r\n\r\nPlease call to discuss further. \r\n\r\nJenny Singh'),(12,'Jenny McCarthy','0414411227','jennymac278@gmail.com','2017-10-03 18:37:24','I am having a lunch time function at my house in North Rocks on Sunday 29th October and would like to discuss options with you.  It is only a small number, 14 adults and 3 children so I don\'t know if that is too small for you.c'),(13,'Jenny McCarthy','0414411227','jennymac278@gmail.com','2017-10-03 18:38:03','I am having a lunch time function at my house in North Rocks on Sunday 29th October and would like to discuss options with you.  It is only a small number, 14 adults and 3 children so I don\'t know if that is too small for you.c'),(14,'joe','98718065','','2017-10-04 09:42:23',''),(15,'Michael Rynn','0414632854','michael.rynn.500@gmail.com','2017-10-04 10:43:30','Hi Julie! This is a test'),(16,'Michael Rynn','0414632854','michael.rynn@parracan.org','2017-10-04 13:45:15','Hi Julie. This is a test email send'),(17,'Gayle Edwards','98433237','gayle.edwards@health.nsw.gov.au','2017-11-17 12:24:57','We are looking at getting our Christmas lunch catered for. It is for 8 Dec, for approximately12-15 people, half of which are vegetarian. Could you supply a quote on a mixed lunch, perhaps with sandwiches, hot food, and some sweets. Thank you. We are located in Marsden St, Parramatta, and would need food only.'),(18,'Gayle Edwards','98433237','gayle.edwards@health.nsw.gov.au','2017-11-17 12:25:39','We are looking at getting our Christmas lunch catered for. It is for 8 Dec, for approximately12-15 people, half of which are vegetarian. Could you supply a quote on a mixed lunch, perhaps with sandwiches, hot food, and some sweets. Thank you. We are located in Marsden St, Parramatta, and would need food only.'),(19,'Bailly','02 9871 8065','','2017-11-18 15:48:00','Test');
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2018-11-30  0:30:16
