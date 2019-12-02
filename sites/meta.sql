-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 09, 2018 at 01:59 AM
-- Server version: 10.1.36-MariaDB
-- PHP Version: 7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `balalaik_sbo`
--

-- --------------------------------------------------------

--
-- Table structure for table `meta`
--


--
-- Constraints for table `blog_meta`
--
ALTER TABLE `blog_meta`
  DROP FOREIGN KEY `meta_id`;
COMMIT;


DROP TABLE IF EXISTS `meta`;
CREATE TABLE `meta` (
  `id` int(10) UNSIGNED NOT NULL,
  `meta_name` varchar(20) NOT NULL,
  `attr` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `template` varchar(80) DEFAULT NULL,
  `data_limit` int(11) DEFAULT NULL,
  `display` tinyint(1) DEFAULT '0',
  `prefixSite` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `meta`
--

INSERT INTO `meta` (`id`, `meta_name`, `attr`, `template`, `data_limit`, `display`, `prefixSite`) VALUES
(1, 'description', 1, '<meta name=\'description\' content=\'{}\' />', 155, 1, 0),
(2, 'author', 1, '<meta name=\'author\' content=\'{}\' />', 50, 1, 0),
(3, 'keywords', 1, '<meta name=\'keywords\' content=\'{}\' />', 200, 1, 0),
(4, 'og:title', 2, '<meta property=\'og:title\'  content=\'{}\' />', 155, 0, 0),
(5, 'og:image', 2, '<meta property=\'og:image\'  content=\'{}\' />', 200, 0, 1),
(6, 'og:description', 2, '<meta property=\'og:description\'  content=\'{}\' />', 200, 0, 0),
(7, 'original-source', 1, '<meta name=\'original-source\' content=\'{}\'>', 200, 1, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `meta`
--
ALTER TABLE `meta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_meta_UNIQUE` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `meta`
--
ALTER TABLE `meta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

--
-- Constraints for table `blog_meta`
--
ALTER TABLE `blog_meta`
  ADD CONSTRAINT `meta_id` FOREIGN KEY (`meta_id`) REFERENCES `meta` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;
COMMIT;