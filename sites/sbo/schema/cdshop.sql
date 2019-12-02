-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 16, 2019 at 06:35 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.3.1

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
-- Table structure for table `cddetail`
--

CREATE TABLE `cddetail` (
  `transactionId` int(10) UNSIGNED NOT NULL,
  `galleryImgId` int(10) UNSIGNED NOT NULL,
  `itemId` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED DEFAULT NULL,
  `attrString` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cddetail`
--

INSERT INTO `cddetail` (`transactionId`, `galleryImgId`, `itemId`, `title`, `price`, `quantity`, `attrString`) VALUES
(1, 47, 'item-47', 'Rassipuha', '20.00', 1, 'weight|0'),
(1, 49, 'item-49', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(2, 52, 'item-52', 'Rassipuha', '20.00', 1, 'weight|0'),
(3, 51, 'item-51', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(3, 52, 'item-52', 'Rassipuha', '20.00', 1, 'weight|0'),
(3, 53, 'item-53', 'Mail Troika', '20.00', 1, 'weight|0'),
(3, 54, 'item-54', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(3, 55, 'item-55', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(11, 52, 'item-52', 'Rassipuha', '20.00', 1, 'weight|0'),
(11, 53, 'item-53', 'Mail Troika', '20.00', 1, 'weight|0'),
(15, 51, 'item-51', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(15, 52, 'item-52', 'Rassipuha', '20.00', 1, 'weight|0'),
(15, 53, 'item-53', 'Mail Troika', '20.00', 1, 'weight|0'),
(15, 54, 'item-54', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(15, 55, 'item-55', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(18, 72, 'item-72', 'Rassipuha', '20.00', 1, 'weight|0'),
(18, 74, 'item-74', 'Mail Troika', '20.00', 1, 'weight|0'),
(19, 72, 'item-72', 'Rassipuha', '20.00', 1, 'weight|0'),
(19, 74, 'item-74', 'Mail Troika', '20.00', 1, 'weight|0'),
(20, 72, 'item-72', 'Rassipuha', '20.00', 1, 'weight|0'),
(20, 74, 'item-74', 'Mail Troika', '20.00', 1, 'weight|0'),
(21, 76, 'item-76', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(22, 76, 'item-76', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(23, 76, 'item-76', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(23, 77, 'item-77', 'Rassipuha', '20.00', 1, 'weight|0'),
(23, 78, 'item-78', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(23, 79, 'item-79', 'Mail Troika', '20.00', 1, 'weight|0'),
(23, 80, 'item-80', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(24, 77, 'item-77', 'Rassipuha', '20.00', 2, 'weight|0'),
(27, 76, 'item-76', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(33, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(33, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(34, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(34, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(35, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(37, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(37, 107, 'item-107', 'Rassipuha', '20.00', 1, 'weight|0'),
(37, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(37, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(37, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(38, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(38, 107, 'item-107', 'Rassipuha', '20.00', 1, 'weight|0'),
(38, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(38, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(38, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(39, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(45, 107, 'item-107', 'Rassipuha', '20.00', 1, 'weight|0'),
(67, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(71, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(71, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(72, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(72, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(73, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(73, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(74, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(75, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(76, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(77, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(77, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(78, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(78, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(78, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(79, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(79, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(79, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(80, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(87, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(88, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(89, 107, 'item-107', 'Rassipuha', '20.00', 1, 'weight|0'),
(89, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(90, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(109, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(109, 107, 'item-107', 'Rassipuha', '20.00', 1, 'weight|0'),
(111, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(111, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(114, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(116, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(116, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(156, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(244, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(244, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(244, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(244, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(245, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(245, 107, 'item-107', 'Rassipuha', '20.00', 1, 'weight|0'),
(245, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(245, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(245, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(274, 106, 'item-106', 'Russian Tapestries', '25.00', 4, 'weight|0'),
(275, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(332, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(332, 107, 'item-107', 'Rassipuha', '20.00', 1, 'weight|0'),
(332, 108, 'item-108', 'Dawn of Russia', '20.00', 1, 'weight|0'),
(332, 109, 'item-109', 'Mail Troika', '20.00', 1, 'weight|0'),
(332, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(347, 110, 'item-110', 'Old Linden Tree', '20.00', 1, 'weight|0'),
(372, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(376, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0'),
(379, 106, 'item-106', 'Russian Tapestries', '25.00', 1, 'weight|0');

-- --------------------------------------------------------

--
-- Table structure for table `cditems`
--

CREATE TABLE `cditems` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `picture` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cost` decimal(13,4) NOT NULL,
  `stock` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `article_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cditems`
--

INSERT INTO `cditems` (`id`, `title`, `picture`, `cost`, `stock`, `description`, `position`, `article_id`) VALUES
(1, 'Russian Tapestries', '/sbo/cd-image/russian_tapestries.jpg', '25.0000', 10, '', 0, 1339),
(2, 'Dawn of Russia', '/sbo/cd-image/dawn_of_russia.jpg', '20.0000', 10, '', 3, 1340),
(3, 'Mail Troika', '/sbo/cd-image/mail_troika.jpg', '20.0000', 10, '', 2, 1341),
(4, 'Rassipuha', '/sbo/cd-image/rassipuha.jpg', '20.0000', 10, '', 1, 1338),
(5, 'Old Linden Tree', '/sbo/cd-image/old_linden_tree.jpg', '20.0000', 10, '', 4, 1342);

-- --------------------------------------------------------

--
-- Table structure for table `cdmethods`
--

CREATE TABLE `cdmethods` (
  `id` int(11) NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `handle` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `enabled` tinyint(4) NOT NULL DEFAULT '1',
  `method_key` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cdmethods`
--

INSERT INTO `cdmethods` (`id`, `name`, `handle`, `label`, `enabled`, `method_key`) VALUES
(1, 'Email', 'email', 'Email / Bank Transfer', 1, 'sales@balalaika.com');

-- --------------------------------------------------------

--
-- Table structure for table `cdtrans`
--

CREATE TABLE `cdtrans` (
  `id` int(10) UNSIGNED NOT NULL,
  `invoice` varchar(255) DEFAULT NULL,
  `customerId` int(10) UNSIGNED DEFAULT NULL,
  `method` varchar(255) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` mediumtext,
  `city` varchar(255) DEFAULT NULL,
  `postal_code` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `discount_code` varchar(255) DEFAULT NULL,
  `quantity` int(10) UNSIGNED DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `shipping` decimal(10,2) DEFAULT NULL,
  `shipping_value` varchar(100) DEFAULT NULL,
  `discount` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `amountPaid` decimal(10,2) DEFAULT '0.00',
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_added` datetime DEFAULT NULL,
  `currency` varchar(20) DEFAULT NULL,
  `currencySymbol` varchar(20) DEFAULT NULL,
  `downloadUrlSent` tinyint(4) DEFAULT NULL,
  `gatewayTransactionId` varchar(255) DEFAULT NULL,
  `cc_name` varchar(255) DEFAULT NULL,
  `cc_no` varchar(255) DEFAULT NULL,
  `ccv` varchar(10) DEFAULT NULL,
  `cc_expiry` varchar(10) DEFAULT NULL,
  `cc_comments` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cdtrans`
--

INSERT INTO `cdtrans` (`id`, `invoice`, `customerId`, `method`, `status`, `first_name`, `last_name`, `phone`, `email`, `address`, `city`, `postal_code`, `country`, `region`, `discount_code`, `quantity`, `subtotal`, `shipping`, `shipping_value`, `discount`, `tax`, `total`, `amountPaid`, `datetime`, `date_added`, `currency`, `currencySymbol`, `downloadUrlSent`, `gatewayTransactionId`, `cc_name`, `cc_no`, `ccv`, `cc_expiry`, `cc_comments`) VALUES
(1, '1408332452', 1, 'email', NULL, 'Sam', 'Minton', '', 'sam@minton.id.au', '82 Burry', 'Burry', '25675', 'AU', 'NSW', '', 2, '40.00', '7.50', 'Standard Shipping', '0.00', '3.64', '47.50', '0.00', '2014-08-18 03:27:32', '2014-08-18 13:27:32', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, '1408333644', 1, 'paypal', NULL, 'Sam', 'Minton', '', 'sam@minton.id.au', '82 Burry', 'Burry', '2576', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '1.82', '27.50', '0.00', '2014-08-18 03:47:24', '2014-08-18 13:47:24', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '1409014036', NULL, 'paypal', NULL, 'bruce', 'barker', '0418402712', 'brucexbarker@gmail.com', '8 Railside ave', 'baego', '2574', 'AU', 'NSW', 'Huh ?', 5, '105.00', '7.50', 'Standard Shipping', '20.00', '9.55', '85.00', '0.00', '2014-08-26 00:47:16', '2014-08-26 10:47:16', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, '1410676922', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-09-14 06:42:02', '2014-09-14 16:42:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, '1410713120', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-09-14 16:45:20', '2014-09-15 02:45:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, '1410797426', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-09-15 16:10:26', '2014-09-16 02:10:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, '1410797703', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-09-15 16:15:03', '2014-09-16 02:15:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, '1411382650', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-09-22 10:44:10', '2014-09-22 20:44:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, '1411423835', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-09-22 22:10:35', '2014-09-23 08:10:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, '1412210349', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-10-02 00:39:09', '2014-10-02 10:39:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, '1412489856', NULL, 'email', NULL, 'Leo', 'Glockemann', '0298762883', 'leog@iinet.net.au', '8 Second Ave', 'Epping', '2121', 'AU', 'NSW', '', 2, '40.00', '7.50', 'Standard Shipping', '5.00', '3.64', '42.50', '0.00', '2014-10-05 06:17:36', '2014-10-05 17:17:36', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, '1412901972', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-10-10 00:46:12', '2014-10-10 11:46:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, '1412901974', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-10-10 00:46:14', '2014-10-10 11:46:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, '1413113063', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-10-12 11:24:23', '2014-10-12 22:24:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, '1413462822', NULL, 'email', NULL, 'bruce', 'barker', '02 46843667', 'brucexbarker@gmail.com', '8  Railside ave', 'Bargo', '2574', 'AU', 'NSW', '', 5, '105.00', '0.00', 'Standard Shipping', '20.00', '9.55', '85.00', '0.00', '2014-10-16 12:33:42', '2014-10-16 23:33:42', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, '1413503189', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-10-16 23:46:29', '2014-10-17 10:46:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, '1413800655', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-10-20 10:24:15', '2014-10-20 21:24:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, '1414126837', 1, 'email', NULL, 'Sam', 'Minton', '0404445844', 'sam@purecreative.com.au', '82 Burrdadoo rd', 'bowral', '2576', 'AU', 'NSW', '', 2, '40.00', '7.50', 'Standard Shipping', '5.00', '3.64', '42.50', '0.00', '2014-10-24 05:00:37', '2014-10-24 16:00:37', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, '1414127557', 1, 'email', NULL, 'Sam', 'Minton', '0404445844', 'sam@purecreative.com.au', '82 Burr..', 'boo', '2345', 'AU', 'NSW', '', 2, '40.00', '7.50', 'Standard Shipping', '5.00', '3.64', '42.50', '0.00', '2014-10-24 05:12:37', '2014-10-24 16:12:37', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, '1414127821', 1, 'paypal', NULL, 'Sam', 'Minton', '', 'sam@purecreative.com.au', '82 bbb', 'bbb', '2576', 'AU', 'NT', '', 2, '40.00', '7.50', 'Standard Shipping', '5.00', '0.00', '42.50', '0.00', '2014-10-24 05:17:01', '2014-10-24 16:17:01', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, '1414196684', NULL, 'email', NULL, 'Bruce testing', 'Still testing', '46843667', 'brucexbarker@gmail.com', '8 Railside ave', 'bargo', '2574', 'AU', 'AAT', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2014-10-25 00:24:44', '2014-10-25 11:24:44', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, '1414876997', NULL, 'email', NULL, 'Lucia', 'Smith', '0408 397 177', 'lucia@alto.com.au', '6 Water Street', 'WAHROONGA', '2076', 'AU', 'NSW', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '2.27', '32.50', '0.00', '2014-11-01 21:23:17', '2014-11-02 08:23:17', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, '1414897345', NULL, 'email', NULL, 'g', 'g', '572', 'jk@', 'g', 'g', '12', 'AU', 'AAT', '', 5, '105.00', '0.00', 'Standard Shipping', '20.00', '0.00', '85.00', '0.00', '2014-11-02 03:02:25', '2014-11-02 14:02:25', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, '1415155190', NULL, 'email', NULL, 'Leo', 'Glockemann', '02 98762883', 'leog@iinet.net.au', '8 Second Ave', 'Epping', '2121', 'AU', 'NSW', '', 2, '40.00', '7.50', 'Standard Shipping', '5.00', '3.64', '42.50', '0.00', '2014-11-05 02:39:50', '2014-11-05 13:39:50', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, '1415178238', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-11-05 09:03:58', '2014-11-05 20:03:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, '1415238268', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-11-06 01:44:28', '2014-11-06 12:44:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, '1415419048', NULL, 'email', NULL, 'brusov', 'barkovich', '223232', 'brucexbarker@gmail.com', 'yes', 'bargo', '2574', 'AU', 'AAT', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2014-11-08 03:57:28', '2014-11-08 14:57:28', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(28, '1415729648', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-11-11 18:14:08', '2014-11-12 05:14:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(29, '1416610450', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-11-21 22:54:10', '2014-11-22 09:54:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(30, '1416614049', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-11-21 23:54:09', '2014-11-22 10:54:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(31, '1416946637', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-11-25 20:17:17', '2014-11-26 07:17:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(32, '1417201770', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-11-28 19:09:30', '2014-11-29 06:09:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(33, '1418205822', NULL, 'email', NULL, 'Nika', 'Lange', '0408223944', 'nikilange@yahoo.com.au', '176 Bay Road', 'Lane Cove  NSW', '2066', '', '', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2014-12-10 10:03:42', '2014-12-10 21:03:42', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(34, '1418205824', NULL, 'email', NULL, 'Nika', 'Lange', '0408223944', 'nikilange@yahoo.com.au', '176 Bay Road', 'Lane Cove  NSW', '2066', '', '', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2014-12-10 10:03:44', '2014-12-10 21:03:44', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(35, '1418432453', 2, 'email', NULL, 'bruce testing', 'barker', '0418402712', 'brucexbarker@gmail.com', '8 railside ave', 'bargo', '2574', '', '', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2014-12-13 01:00:53', '2014-12-13 12:00:53', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(36, '1418483757', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-12-13 15:15:57', '2014-12-14 02:15:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(37, '1418512170', NULL, 'email', NULL, 'TEST Bruce', 'Barker', '0418402712', 'brucexbarker@gmail.com', '8 Railside Ave', 'Bargo', '2574', 'AU', 'NSW', '', 5, '105.00', '0.00', 'Standard Shipping', '20.00', '0.00', '85.00', '0.00', '2014-12-13 23:09:30', '2014-12-14 10:09:30', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(38, '1418681417', NULL, 'email', NULL, 'Steve', 'Husband', '0407159874', 'stevomyrtus@icloud.com', '232 Hill Road', 'Mothar Mountain', '4570', '', '', '', 5, '105.00', '0.00', 'Standard Shipping', '20.00', '0.00', '85.00', '0.00', '2014-12-15 22:10:17', '2014-12-16 09:10:17', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(39, '1419054621', NULL, 'email', NULL, 'm', 'm', '', '@', 'm', 'm', 'm', '', '', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2014-12-20 05:50:21', '2014-12-20 16:50:21', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(40, '1419849036', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-12-29 10:30:36', '2014-12-29 21:30:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(41, '1419849038', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2014-12-29 10:30:38', '2014-12-29 21:30:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(42, '1420307649', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-01-03 17:54:09', '2015-01-04 04:54:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(43, '1420327366', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-01-03 23:22:46', '2015-01-04 10:22:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(44, '1420341012', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-01-04 03:10:12', '2015-01-04 14:10:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(45, '1420600612', NULL, 'email', NULL, 'Carole', 'Weissman', '7034330544', 'jpwphilly@att.net', '1137 Bandy Run Rd', 'Herndon', '20170', '', '', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2015-01-07 03:16:52', '2015-01-07 14:16:52', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(46, '1421116562', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-01-13 02:36:02', '2015-01-13 13:36:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(47, '1421120561', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-01-13 03:42:41', '2015-01-13 14:42:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(48, '1421181567', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-01-13 20:39:27', '2015-01-14 07:39:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(49, '1422462531', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-01-28 16:28:51', '2015-01-29 03:28:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(50, '1424579834', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-02-22 04:37:14', '2015-02-22 15:37:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(51, '1425279446', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-02 06:57:26', '2015-03-02 17:57:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(52, '1427157744', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 00:42:24', '2015-03-24 11:42:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(53, '1427161693', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 01:48:13', '2015-03-24 12:48:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(54, '1427170990', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 04:23:10', '2015-03-24 15:23:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(55, '1427182397', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 07:33:17', '2015-03-24 18:33:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(56, '1427230419', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 20:53:39', '2015-03-25 07:53:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(57, '1427233693', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 21:48:13', '2015-03-25 08:48:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(58, '1427237306', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 22:48:26', '2015-03-25 09:48:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(59, '1427240636', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-24 23:43:56', '2015-03-25 10:43:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(60, '1427244791', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-25 00:53:11', '2015-03-25 11:53:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(61, '1427429310', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-03-27 04:08:30', '2015-03-27 15:08:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(62, '1428677242', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-04-10 14:47:22', '2015-04-11 00:47:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(63, '1428677290', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-04-10 14:48:10', '2015-04-11 00:48:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(64, '1429481007', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-04-19 22:03:27', '2015-04-20 08:03:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(65, '1431113224', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-05-08 19:27:04', '2015-05-09 05:27:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(66, '1431129425', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-05-08 23:57:05', '2015-05-09 09:57:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(67, '1431503423', NULL, 'email', NULL, 'Laurence', 'Gluch', '02 9371 4247', 'l.gluch@lycos.com', '43 Reina Street, North Bondi', 'Sydney', '2026', 'AU', '', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2015-05-13 07:50:23', '2015-05-13 17:50:23', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(68, '1432418768', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-05-23 22:06:08', '2015-05-24 08:06:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(69, '1432480313', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-05-24 15:11:53', '2015-05-25 01:11:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(70, '1432746128', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-05-27 17:02:08', '2015-05-28 03:02:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(71, '1432814100', NULL, 'email', NULL, 'Lynette', 'Gaze', '', 'Lynnettegaze@gmail.com', '106 Waterview street', 'Mona Vale', '2103', 'AU', 'NSW', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2015-05-28 11:55:00', '2015-05-28 21:55:00', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(72, '1432892745', NULL, 'email', NULL, 'Lynette', 'Gaze', '0400489100', 'Lynnettegaze@gmail.com', '106 Waterview street', 'Mona Vale', '2103', 'AU', 'NSW', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2015-05-29 09:45:45', '2015-05-29 19:45:45', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(73, '1432945499', NULL, 'email', NULL, 'Bruce', 'Barker', '0418402712', 'brucexbarker@gmail.com', '8 Railside Ave', 'bargo', '2574', 'AU', 'NSW', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2015-05-30 00:24:59', '2015-05-30 10:24:59', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(74, '1433143869', NULL, 'email', NULL, 'Michael', 'Rynn', '0414632854', 'michael.rynn.500@gmail.com', '500 Guildford Rd', 'Guildford', '2161', 'AU', 'NSW', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2015-06-01 07:31:09', '2015-06-01 17:31:09', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(75, '1433149636', 1, 'email', NULL, 'Sam', 'Minton', '', 'sam@purecreative.com.au', 'bowral', 'bowral', '2222', 'AU', 'NSW', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2015-06-01 09:07:16', '2015-06-01 19:07:16', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(76, '1433149701', 1, 'email', NULL, 'Sam', 'Minton', '', 'sam@purecreative.com.au', 'bowral', 'bowral', '2222', 'AU', '', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2015-06-01 09:08:21', '2015-06-01 19:08:21', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(77, '1433150034', 1, 'email', NULL, 'Sam', 'Minton', '', 'sam@purecreative.com.au', 'bowral', 'bowral', '2222', 'AU', '', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2015-06-01 09:13:54', '2015-06-01 19:13:54', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(78, '1433194932', 1, 'email', NULL, 'Sam', 'Minton', '', 'sam@purecreative.com.au', 'test', 'test', '1234', 'AU', 'NSW', '', 3, '65.00', '7.50', 'Standard Shipping', '7.50', '0.00', '65.00', '0.00', '2015-06-01 21:42:12', '2015-06-02 07:42:12', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(79, '1433284442', 1, 'email', NULL, 'Sam', 'Minton', '', 'sam@purecreative.com.au', 'test', 'test', '1234', 'AU', 'NSW', '', 3, '65.00', '7.50', 'Standard Shipping', '7.50', '0.00', '65.00', '0.00', '2015-06-02 22:34:02', '2015-06-03 08:34:02', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(80, '1433985608', NULL, 'email', NULL, 'TEST', 'TESTING', '0418402712', 'brucexbarker@gmail.com', 'BARGO', 'BARGO', '2574', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2015-06-11 01:20:08', '2015-06-11 11:20:08', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(81, '1433999053', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-06-11 05:04:13', '2015-06-11 15:04:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(82, '1433999093', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-06-11 05:04:53', '2015-06-11 15:04:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(83, '1433999125', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-06-11 05:05:25', '2015-06-11 15:05:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(84, '1434143509', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-06-12 21:11:49', '2015-06-13 07:11:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(85, '1434319611', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-06-14 22:06:51', '2015-06-15 08:06:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(86, '1434334311', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-06-15 02:11:51', '2015-06-15 12:11:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(87, '1434593097', NULL, 'email', NULL, 'Michaeln', 'Ryn', '0414632854', 'michael.rynn.500@gmail.com', '500 Guildford Rd', 'Guildford', '2161', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2015-06-18 02:04:57', '2015-06-18 12:04:57', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(88, '1434628725', NULL, 'email', NULL, 'bruce', 'barker', '0418402712', 'brucexbarker@gmail.com', 'bargo', 'sydney', '2574', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2015-06-18 11:58:45', '2015-06-18 21:58:45', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(89, '1434672127', NULL, 'email', NULL, 'BRUCE', 'BAKER', '0418402712', 'brucexbarker@gmail.com', 'BARGO', 'BARGO', '2574', 'AU', 'NSW', '', 2, '40.00', '7.50', 'Standard Shipping', '5.00', '0.00', '42.50', '0.00', '2015-06-19 00:02:07', '2015-06-19 10:02:07', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(90, '1434872266', NULL, 'email', NULL, 'BRUCE', 'BARKER', '0418402712', 'brucexbarker@gmail.com', '8 RAILSIDE AVE', 'BARGO', '2574', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2015-06-21 07:37:46', '2015-06-21 17:37:46', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(91, '1435640511', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-06-30 05:01:51', '2015-06-30 15:01:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(92, '1436065606', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-07-05 03:06:46', '2015-07-05 13:06:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(93, '1436114213', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-07-05 16:36:53', '2015-07-06 02:36:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(94, '1436290610', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-07-07 17:36:50', '2015-07-08 03:36:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(95, '1436303210', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-07-07 21:06:50', '2015-07-08 07:06:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, '1436308012', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-07-07 22:26:52', '2015-07-08 08:26:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(97, '1436329610', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-07-08 04:26:50', '2015-07-08 14:26:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(98, '1438123339', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-07-28 22:42:19', '2015-07-29 08:42:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(99, '1439274964', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-11 06:36:04', '2015-08-11 16:36:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, '1439274966', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-11 06:36:06', '2015-08-11 16:36:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, '1439642571', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-15 12:42:51', '2015-08-15 22:42:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(102, '1440211509', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-22 02:45:09', '2015-08-22 12:45:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(103, '1440212871', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-22 03:07:51', '2015-08-22 13:07:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(104, '1440218319', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-22 04:38:39', '2015-08-22 14:38:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(105, '1440219889', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-22 05:04:49', '2015-08-22 15:04:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(106, '1440240792', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-22 10:53:12', '2015-08-22 20:53:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(107, '1440255775', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-22 15:02:55', '2015-08-23 01:02:55', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(108, '1440569271', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-08-26 06:07:51', '2015-08-26 16:07:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(109, '1441282310', NULL, 'email', NULL, 'Kate', 'Ohar', '', 'Kateohar@gmail.com', '87 Queenscliff drive', 'Woodbine', '2560', 'AU', 'NSW', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2015-09-03 12:11:50', '2015-09-03 22:11:50', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(110, '1441361925', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-09-04 10:18:45', '2015-09-04 20:18:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(111, '1442902352', NULL, 'email', NULL, 'Kate', 'OHar', '98295000', 'kateohar@gmail.com', '87 Queenscliff Drive', 'Woodbine', '2560', 'AU', 'NSW', '', 2, '45.00', '7.50', 'Standard Shipping', '5.00', '0.00', '47.50', '0.00', '2015-09-22 06:12:32', '2015-09-22 16:12:32', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(112, '1443073790', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-09-24 05:49:50', '2015-09-24 15:49:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(113, '1444102132', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-10-06 03:28:52', '2015-10-06 14:28:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(114, '1444471666', NULL, 'email', NULL, 'Paul', 'Taubert', '61267713364', 'tenzin05@bigpond.net.au', 'Unit 9, 196-202 Barney Street', 'Armidale', '2350', 'AU', 'NSW', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2015-10-10 10:07:46', '2015-10-10 21:07:46', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(115, '1446332942', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-10-31 23:09:02', '2015-11-01 10:09:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(116, '1447655894', NULL, 'email', NULL, 'Miklos', 'Hollo', '', 'ozshoppingbiz@gmail.com', '11 Elliot Close', 'Bathurst', '2795', 'AU', 'NSW', '', 2, '40.00', '7.50', 'Standard Shipping', '5.00', '0.00', '42.50', '0.00', '2015-11-16 06:38:14', '2015-11-16 17:38:14', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(117, '1447876807', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-11-18 20:00:07', '2015-11-19 07:00:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(118, '1447895722', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-11-19 01:15:22', '2015-11-19 12:15:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(119, '1447895740', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-11-19 01:15:40', '2015-11-19 12:15:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(120, '1448831863', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-11-29 21:17:43', '2015-11-30 08:17:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(121, '1450025850', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2015-12-13 16:57:30', '2015-12-14 03:57:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(122, '1453167987', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-19 01:46:27', '2016-01-19 12:46:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(123, '1453853230', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-27 00:07:10', '2016-01-27 11:07:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(124, '1453853295', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-27 00:08:15', '2016-01-27 11:08:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(125, '1453853309', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-27 00:08:29', '2016-01-27 11:08:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(126, '1453971769', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-28 09:02:49', '2016-01-28 20:02:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(127, '1453974889', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-28 09:54:49', '2016-01-28 20:54:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(128, '1453980905', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-28 11:35:05', '2016-01-28 22:35:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(129, '1453981034', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-01-28 11:37:14', '2016-01-28 22:37:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(130, '1455231090', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-02-11 22:51:30', '2016-02-12 09:51:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(131, '1455258079', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-02-12 06:21:19', '2016-02-12 17:21:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(132, '1457380625', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-03-07 19:57:05', '2016-03-08 06:57:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(133, '1457403421', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-03-08 02:17:01', '2016-03-08 13:17:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(134, '1457868219', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-03-13 11:23:39', '2016-03-13 22:23:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(135, '1458264703', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-03-18 01:31:43', '2016-03-18 12:31:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(136, '1459936936', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-06 10:02:16', '2016-04-06 20:02:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(137, '1460189247', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-09 08:07:27', '2016-04-09 18:07:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(138, '1460241006', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-09 22:30:06', '2016-04-10 08:30:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(139, '1460433236', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-12 03:53:56', '2016-04-12 13:53:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(140, '1460691064', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-15 03:31:04', '2016-04-15 13:31:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(141, '1460762603', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-15 23:23:23', '2016-04-16 09:23:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(142, '1461067830', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-19 12:10:30', '2016-04-19 22:10:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(143, '1461261338', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-21 17:55:38', '2016-04-22 03:55:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(144, '1461295788', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-22 03:29:48', '2016-04-22 13:29:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(145, '1461516924', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-24 16:55:24', '2016-04-25 02:55:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(146, '1461845390', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-28 12:09:50', '2016-04-28 22:09:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(147, '1461982486', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-04-30 02:14:46', '2016-04-30 12:14:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(148, '1462250721', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-03 04:45:21', '2016-05-03 14:45:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(149, '1462250780', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-03 04:46:20', '2016-05-03 14:46:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(150, '1462250825', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-03 04:47:05', '2016-05-03 14:47:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(151, '1462499104', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-06 01:45:04', '2016-05-06 11:45:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(152, '1462608647', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-07 08:10:47', '2016-05-07 18:10:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(153, '1462641603', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-07 17:20:03', '2016-05-08 03:20:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(154, '1462777230', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-09 07:00:30', '2016-05-09 17:00:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(155, '1462843790', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-10 01:29:50', '2016-05-10 11:29:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(156, '1462865996', NULL, 'email', NULL, 'Virginia', 'Howard', '0419822278', 'vehoward@bigpond.com.au', '15 Wolger Road', 'Mosman', '2088', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2016-05-10 07:39:56', '2016-05-10 17:39:56', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(157, '1462871392', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-10 09:09:52', '2016-05-10 19:09:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(158, '1462932289', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-11 02:04:49', '2016-05-11 12:04:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(159, '1462943120', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-11 05:05:20', '2016-05-11 15:05:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(160, '1463089514', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-12 21:45:14', '2016-05-13 07:45:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(161, '1463136126', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-13 10:42:06', '2016-05-13 20:42:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(162, '1463358891', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-16 00:34:51', '2016-05-16 10:34:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(163, '1463390408', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-16 09:20:08', '2016-05-16 19:20:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(164, '1463393697', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-16 10:14:57', '2016-05-16 20:14:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(165, '1463458129', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-17 04:08:49', '2016-05-17 14:08:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(166, '1463460908', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-17 04:55:08', '2016-05-17 14:55:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(167, '1463463426', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-17 05:37:06', '2016-05-17 15:37:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(168, '1463466061', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-17 06:21:01', '2016-05-17 16:21:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(169, '1463469287', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-17 07:14:47', '2016-05-17 17:14:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(170, '1463494489', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-17 14:14:49', '2016-05-18 00:14:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(171, '1463495405', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-17 14:30:05', '2016-05-18 00:30:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(172, '1463543800', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-18 03:56:40', '2016-05-18 13:56:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(173, '1463597986', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-18 18:59:46', '2016-05-19 04:59:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(174, '1464650388', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-05-30 23:19:48', '2016-05-31 09:19:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(175, '1465167069', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-05 22:51:09', '2016-06-06 08:51:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(176, '1465167423', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-05 22:57:03', '2016-06-06 08:57:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(177, '1465167519', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-05 22:58:39', '2016-06-06 08:58:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(178, '1465552984', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-10 10:03:04', '2016-06-10 20:03:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(179, '1465552997', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-10 10:03:17', '2016-06-10 20:03:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(180, '1465553122', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-10 10:05:22', '2016-06-10 20:05:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(181, '1465553123', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-10 10:05:23', '2016-06-10 20:05:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(182, '1465553173', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-10 10:06:13', '2016-06-10 20:06:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(183, '1465553176', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-10 10:06:16', '2016-06-10 20:06:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(184, '1465713904', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-12 06:45:04', '2016-06-12 16:45:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(185, '1465734253', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-12 12:24:13', '2016-06-12 22:24:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(186, '1465831925', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-13 15:32:05', '2016-06-14 01:32:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(187, '1467083472', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-06-28 03:11:12', '2016-06-28 13:11:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(188, '1469350276', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-07-24 08:51:16', '2016-07-24 18:51:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(189, '1469449363', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-07-25 12:22:43', '2016-07-25 22:22:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(190, '1469449374', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-07-25 12:22:54', '2016-07-25 22:22:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(191, '1469449629', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-07-25 12:27:09', '2016-07-25 22:27:09', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(192, '1469449630', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-07-25 12:27:10', '2016-07-25 22:27:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(193, '1469449859', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-07-25 12:30:59', '2016-07-25 22:30:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(194, '1469449866', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-07-25 12:31:06', '2016-07-25 22:31:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(195, '1470789482', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-08-10 00:38:02', '2016-08-10 10:38:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(196, '1470875333', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-08-11 00:28:53', '2016-08-11 10:28:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(197, '1473885817', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-09-14 20:43:37', '2016-09-15 06:43:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(198, '1474661354', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-09-23 20:09:14', '2016-09-24 06:09:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(199, '1475781526', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-10-06 19:18:46', '2016-10-07 06:18:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(200, '1475781540', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-10-06 19:19:00', '2016-10-07 06:19:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(201, '1475781658', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-10-06 19:20:58', '2016-10-07 06:20:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(202, '1475781661', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-10-06 19:21:01', '2016-10-07 06:21:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `cdtrans` (`id`, `invoice`, `customerId`, `method`, `status`, `first_name`, `last_name`, `phone`, `email`, `address`, `city`, `postal_code`, `country`, `region`, `discount_code`, `quantity`, `subtotal`, `shipping`, `shipping_value`, `discount`, `tax`, `total`, `amountPaid`, `datetime`, `date_added`, `currency`, `currencySymbol`, `downloadUrlSent`, `gatewayTransactionId`, `cc_name`, `cc_no`, `ccv`, `cc_expiry`, `cc_comments`) VALUES
(203, '1475781807', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-10-06 19:23:27', '2016-10-07 06:23:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(204, '1475781811', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-10-06 19:23:31', '2016-10-07 06:23:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(205, '1476508982', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-10-15 05:23:02', '2016-10-15 16:23:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(206, '1478662270', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-11-09 03:31:10', '2016-11-09 14:31:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(207, '1479229298', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-11-15 17:01:38', '2016-11-16 04:01:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(208, '1479235633', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-11-15 18:47:13', '2016-11-16 05:47:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(209, '1479329117', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-11-16 20:45:17', '2016-11-17 07:45:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(210, '1479384399', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-11-17 12:06:39', '2016-11-17 23:06:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(211, '1479398625', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-11-17 16:03:45', '2016-11-18 03:03:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(212, '1482778542', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-12-26 18:55:42', '2016-12-26 18:55:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(213, '1482850059', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2016-12-27 14:47:39', '2016-12-27 14:47:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(214, '1486587116', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-08 20:51:56', '2017-02-08 20:51:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(215, '1486587121', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-08 20:52:01', '2017-02-08 20:52:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(216, '1486587254', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-08 20:54:14', '2017-02-08 20:54:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(217, '1486587258', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-08 20:54:18', '2017-02-08 20:54:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(218, '1486587397', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-08 20:56:37', '2017-02-08 20:56:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(219, '1486587402', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-08 20:56:42', '2017-02-08 20:56:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(220, '1486805964', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-11 09:39:24', '2017-02-11 09:39:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(221, '1486977271', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-13 09:14:31', '2017-02-13 09:14:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(222, '1486993871', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-13 13:51:11', '2017-02-13 13:51:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(223, '1487097467', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-14 18:37:47', '2017-02-14 18:37:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(224, '1487338379', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-17 13:32:59', '2017-02-17 13:32:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(225, '1487364173', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-17 20:42:53', '2017-02-17 20:42:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(226, '1487422104', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-18 12:48:24', '2017-02-18 12:48:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(227, '1487453261', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-18 21:27:41', '2017-02-18 21:27:41', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(228, '1487454323', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-18 21:45:23', '2017-02-18 21:45:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(229, '1487454574', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-18 21:49:34', '2017-02-18 21:49:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(230, '1487502766', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-19 11:12:46', '2017-02-19 11:12:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(231, '1488019334', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-25 10:42:14', '2017-02-25 10:42:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(232, '1488164265', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-27 02:57:45', '2017-02-27 02:57:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(233, '1488175960', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-27 06:12:40', '2017-02-27 06:12:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(234, '1488183168', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-27 08:12:48', '2017-02-27 08:12:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(235, '1488228491', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-02-27 20:48:11', '2017-02-27 20:48:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(236, '1488383269', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-03-01 15:47:49', '2017-03-01 15:47:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(237, '1492814288', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-04-21 22:38:08', '2017-04-21 22:38:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(238, '1492814294', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-04-21 22:38:14', '2017-04-21 22:38:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(239, '1492814404', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-04-21 22:40:04', '2017-04-21 22:40:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(240, '1492814443', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-04-21 22:40:43', '2017-04-21 22:40:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(241, '1492814605', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-04-21 22:43:25', '2017-04-21 22:43:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(242, '1492814609', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-04-21 22:43:29', '2017-04-21 22:43:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(243, '1493225855', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-04-26 16:57:35', '2017-04-26 16:57:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(244, '1493451672', NULL, 'email', NULL, 'Anne', 'Semple', '0466182037', 'asemple@mta.ca', '9/44 Pittwater Road', 'Gladesville', '2111', 'AU', 'NSW', '', 4, '85.00', '0.00', 'Standard Shipping', '7.50', '0.00', '77.50', '0.00', '2017-04-29 07:41:12', '2017-04-29 07:41:12', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(245, '1493704470', NULL, 'email', NULL, 'Julian', 'Wales', '03 9387 7106', 'jw@conradheatsinks.com', '36 Victoria Street', 'Brunswick East', '3057', 'AU', 'VIC', '', 5, '105.00', '0.00', 'Standard Shipping', '20.00', '0.00', '85.00', '0.00', '2017-05-02 05:54:30', '2017-05-02 05:54:30', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(246, '1495671783', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-05-25 00:23:03', '2017-05-25 00:23:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(247, '1495815645', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-05-26 16:20:45', '2017-05-26 16:20:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(248, '1498259410', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-06-23 23:10:10', '2017-06-23 23:10:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(249, '1503839670', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-08-27 13:14:30', '2017-08-27 13:14:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(250, '1503866952', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-08-27 20:49:12', '2017-08-27 20:49:12', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(251, '1504452464', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-09-03 15:27:44', '2017-09-03 15:27:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(252, '1504590966', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-09-05 05:56:06', '2017-09-05 05:56:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(253, '1508837491', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-10-24 09:31:31', '2017-10-24 09:31:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(254, '1509044418', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-10-26 19:00:18', '2017-10-26 19:00:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(255, '1509258857', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-10-29 06:34:17', '2017-10-29 06:34:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(256, '1511724967', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-11-26 19:36:07', '2017-11-26 19:36:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(257, '1511724984', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2017-11-26 19:36:24', '2017-11-26 19:36:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(258, '1515771932', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-01-12 15:45:32', '2018-01-12 15:45:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(259, '1515772057', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-01-12 15:47:37', '2018-01-12 15:47:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(260, '1516520322', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-01-21 07:38:42', '2018-01-21 07:38:42', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(261, '1518078785', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-02-08 08:33:05', '2018-02-08 08:33:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(262, '1518613987', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-02-14 13:13:07', '2018-02-14 13:13:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(263, '1519001490', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-02-19 00:51:30', '2018-02-19 00:51:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(264, '1519001576', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-02-19 00:52:56', '2018-02-19 00:52:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(265, '1519403447', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-02-23 16:30:47', '2018-02-23 16:30:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(266, '1519922323', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-01 16:38:43', '2018-03-01 16:38:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(267, '1519923550', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-01 16:59:10', '2018-03-01 16:59:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(268, '1520600542', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-09 13:02:22', '2018-03-09 13:02:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(269, '1520616114', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-09 17:21:54', '2018-03-09 17:21:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(270, '1520648103', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-10 02:15:03', '2018-03-10 02:15:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(271, '1520774087', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-11 13:14:47', '2018-03-11 13:14:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(272, '1520778530', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-11 14:28:50', '2018-03-11 14:28:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(273, '1520946602', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-03-13 13:10:02', '2018-03-13 13:10:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(274, '1521535364', NULL, 'email', NULL, 'Ereena', 'Somov', '', 'ereena.somov@ru.zurich.com', 'c/- Irina Apollonov, 41 Wallace Street,', 'Burwood', '2134', 'AU', 'NSW', '', 4, '100.00', '0.00', 'Standard Shipping', '7.50', '0.00', '92.50', '0.00', '2018-03-20 08:42:44', '2018-03-20 08:42:44', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(275, '1523624873', NULL, 'email', NULL, 'Andy', 'Kabanoff', '0405220591', 'anddywildpig@hotmail.com', '10 Palmgrove Place', 'North Avoca', '2260', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2018-04-13 13:07:53', '2018-04-13 13:07:53', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(276, '1526832089', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-05-20 16:01:29', '2018-05-20 16:01:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(277, '1526838982', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-05-20 17:56:22', '2018-05-20 17:56:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(278, '1527264360', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-05-25 16:06:00', '2018-05-25 16:06:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(279, '1527592358', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-05-29 11:12:38', '2018-05-29 11:12:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(280, '1527942658', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-06-02 12:30:58', '2018-06-02 12:30:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(281, '1527959530', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-06-02 17:12:10', '2018-06-02 17:12:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(282, '1527959548', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-06-02 17:12:28', '2018-06-02 17:12:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(283, '1528720846', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-06-11 12:40:46', '2018-06-11 12:40:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(284, '1528720884', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-06-11 12:41:24', '2018-06-11 12:41:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(285, '1530670037', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-04 02:07:17', '2018-07-04 02:07:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(286, '1530737506', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-04 20:51:46', '2018-07-04 20:51:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(287, '1530879108', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-06 12:11:48', '2018-07-06 12:11:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(288, '1531070111', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-08 17:15:11', '2018-07-08 17:15:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(289, '1531070196', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-08 17:16:36', '2018-07-08 17:16:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(290, '1531150607', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-09 15:36:47', '2018-07-09 15:36:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(291, '1532516547', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-25 11:02:27', '2018-07-25 11:02:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(292, '1533053435', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-07-31 16:10:35', '2018-07-31 16:10:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(293, '1533137816', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-01 15:36:56', '2018-08-01 15:36:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(294, '1533138250', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-01 15:44:10', '2018-08-01 15:44:10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(295, '1533148060', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-01 18:27:40', '2018-08-01 18:27:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(296, '1533157864', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-01 21:11:04', '2018-08-01 21:11:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(297, '1533161239', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-01 22:07:19', '2018-08-01 22:07:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(298, '1533164522', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-01 23:02:02', '2018-08-01 23:02:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(299, '1533168524', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-02 00:08:44', '2018-08-02 00:08:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(300, '1533197190', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-02 08:06:30', '2018-08-02 08:06:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(301, '1533419436', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-04 21:50:36', '2018-08-04 21:50:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(302, '1533605406', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-07 01:30:06', '2018-08-07 01:30:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(303, '1533937988', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-10 21:53:08', '2018-08-10 21:53:08', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(304, '1533938017', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-10 21:53:37', '2018-08-10 21:53:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(305, '1533940524', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-10 22:35:24', '2018-08-10 22:35:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(306, '1533973549', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-11 07:45:49', '2018-08-11 07:45:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(307, '1533973606', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-11 07:46:46', '2018-08-11 07:46:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(308, '1534094454', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-12 17:20:54', '2018-08-12 17:20:54', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(309, '1534094481', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-12 17:21:21', '2018-08-12 17:21:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(310, '1534109484', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-12 21:31:24', '2018-08-12 21:31:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(311, '1534109541', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-12 21:32:21', '2018-08-12 21:32:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(312, '1534121191', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-13 00:46:31', '2018-08-13 00:46:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(313, '1534121206', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-13 00:46:46', '2018-08-13 00:46:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(314, '1534150595', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-13 08:56:35', '2018-08-13 08:56:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(315, '1534156680', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-13 10:38:00', '2018-08-13 10:38:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(316, '1534157364', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-13 10:49:24', '2018-08-13 10:49:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(317, '1534273585', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-14 19:06:25', '2018-08-14 19:06:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(318, '1534321954', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-15 08:32:34', '2018-08-15 08:32:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(319, '1534331181', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-15 11:06:21', '2018-08-15 11:06:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(320, '1534585908', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-18 09:51:48', '2018-08-18 09:51:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(321, '1534885696', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-21 21:08:16', '2018-08-21 21:08:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(322, '1534887811', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-21 21:43:31', '2018-08-21 21:43:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(323, '1535057883', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-23 20:58:03', '2018-08-23 20:58:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(324, '1535700427', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-08-31 07:27:07', '2018-08-31 07:27:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(325, '1536216782', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-06 06:53:02', '2018-09-06 06:53:02', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(326, '1536399616', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-08 09:40:16', '2018-09-08 09:40:16', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(327, '1536403021', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-08 10:37:01', '2018-09-08 10:37:01', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(328, '1536418639', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-08 14:57:19', '2018-09-08 14:57:19', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(329, '1536421687', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-08 15:48:07', '2018-09-08 15:48:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(330, '1536441164', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-08 21:12:44', '2018-09-08 21:12:44', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(331, '1536506833', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-09 15:27:13', '2018-09-09 15:27:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(332, '1536754299', NULL, 'email', NULL, 'Ward', 'Keebaugh', '0408077448', 'skeebaug@bigpond.net.au', '3/23 Mercer Road', 'Armadale, Victoria', '3144', 'AU', '', '', 5, '105.00', '0.00', 'Standard Shipping', '20.00', '0.00', '85.00', '0.00', '2018-09-12 12:11:39', '2018-09-12 12:11:39', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(333, '1536767160', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-12 15:46:00', '2018-09-12 15:46:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(334, '1537301937', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-18 20:18:57', '2018-09-18 20:18:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(335, '1538130995', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-28 10:36:35', '2018-09-28 10:36:35', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(336, '1538131127', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-28 10:38:47', '2018-09-28 10:38:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(337, '1538239359', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-29 16:42:39', '2018-09-29 16:42:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(338, '1538239430', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-09-29 16:43:50', '2018-09-29 16:43:50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(339, '1538388666', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-01 10:11:06', '2018-10-01 10:11:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(340, '1538597265', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-03 20:07:45', '2018-10-03 20:07:45', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(341, '1538597332', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-03 20:08:52', '2018-10-03 20:08:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(342, '1539200364', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-10 19:39:24', '2018-10-10 19:39:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(343, '1539308121', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-12 01:35:21', '2018-10-12 01:35:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(344, '1539308140', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-12 01:35:40', '2018-10-12 01:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(345, '1539403770', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-13 04:09:30', '2018-10-13 04:09:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(346, '1539526836', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-14 14:20:36', '2018-10-14 14:20:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(347, '1540595427', NULL, 'email', NULL, 'Michael', 'Rynn', '0414632854', 'michaelrynn@optusnet.com.au', '500 Guildford Rd', 'Guildford', '2161', 'AU', 'NSW', '', 1, '20.00', '7.50', 'Standard Shipping', '0.00', '0.00', '27.50', '0.00', '2018-10-26 23:10:27', '2018-10-26 23:10:27', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(348, '1540863143', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-30 01:32:23', '2018-10-30 01:32:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(349, '1540863218', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-10-30 01:33:38', '2018-10-30 01:33:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(350, '1541170769', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-02 14:59:29', '2018-11-02 14:59:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(351, '1541621782', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-07 20:16:22', '2018-11-07 20:16:22', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(352, '1541740290', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-09 05:11:30', '2018-11-09 05:11:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(353, '1542037455', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-12 15:44:15', '2018-11-12 15:44:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(354, '1542261086', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-15 05:51:26', '2018-11-15 05:51:26', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(355, '1542272063', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-15 08:54:23', '2018-11-15 08:54:23', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(356, '1542272127', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-15 08:55:27', '2018-11-15 08:55:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(357, '1542297483', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-15 15:58:03', '2018-11-15 15:58:03', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(358, '1542297532', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-15 15:58:52', '2018-11-15 15:58:52', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(359, '1542299811', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-15 16:36:51', '2018-11-15 16:36:51', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(360, '1542376176', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-16 13:49:36', '2018-11-16 13:49:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(361, '1542404017', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-16 21:33:37', '2018-11-16 21:33:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(362, '1542404028', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-16 21:33:48', '2018-11-16 21:33:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(363, '1542473869', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-17 16:57:49', '2018-11-17 16:57:49', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(364, '1542474151', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-17 17:02:31', '2018-11-17 17:02:31', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(365, '1543337208', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-27 16:46:48', '2018-11-27 16:46:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(366, '1543341933', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-27 18:05:33', '2018-11-27 18:05:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(367, '1543440833', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-28 21:33:53', '2018-11-28 21:33:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(368, '1543535226', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-11-29 23:47:06', '2018-11-29 23:47:06', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(369, '1544106233', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-06 14:23:53', '2018-12-06 14:23:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(370, '1544140751', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-06 23:59:11', '2018-12-06 23:59:11', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(371, '1544792977', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-14 13:09:37', '2018-12-14 13:09:37', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(372, '1544815050', NULL, 'email', NULL, 'James', 'Christofides', '0403737949', 'jameschristofides@hotmail.com', '15, Hargrave St', 'Paddington', '2021', 'AU', 'NSW', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2018-12-14 19:17:30', '2018-12-14 19:17:30', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(373, '1544956710', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-16 10:38:30', '2018-12-16 10:38:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(374, '1544967869', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-16 13:44:29', '2018-12-16 13:44:29', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(375, '1544989154', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-16 19:39:14', '2018-12-16 19:39:14', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(376, '1545046464', NULL, 'email', NULL, 'James', 'Christofides', '0403737949', 'jameschristofides@hotmail.com', '15 Hargrave Street', 'Paddington', '2021', 'AU', 'NSW', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2018-12-17 11:34:24', '2018-12-17 11:34:24', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(377, '1545249508', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-19 19:58:28', '2018-12-19 19:58:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(378, '1545400919', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-21 14:01:59', '2018-12-21 14:01:59', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(379, '1545617979', NULL, 'email', NULL, 'Ruth', 'Rush', '0747826831', 'qr2@bigpond.com', 'PO Box 1003', 'Ayr', '4807', 'AU', 'QLD', '', 1, '25.00', '7.50', 'Standard Shipping', '0.00', '0.00', '32.50', '0.00', '2018-12-24 02:19:39', '2018-12-24 02:19:39', 'AUD', '$', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(380, '1545756448', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-25 16:47:28', '2018-12-25 16:47:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(381, '1545776945', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2018-12-25 22:29:05', '2018-12-25 22:29:05', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(382, '1546621067', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2019-01-04 16:57:47', '2019-01-04 16:57:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(383, '1546644558', NULL, NULL, NULL, '', '', '', '', '', '', '', '', '', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0.00', '2019-01-04 23:29:18', '2019-01-04 23:29:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cddetail`
--
ALTER TABLE `cddetail`
  ADD PRIMARY KEY (`transactionId`,`galleryImgId`);

--
-- Indexes for table `cditems`
--
ALTER TABLE `cditems`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cdmethods`
--
ALTER TABLE `cdmethods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cdtrans`
--
ALTER TABLE `cdtrans`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cditems`
--
ALTER TABLE `cditems`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cdmethods`
--
ALTER TABLE `cdmethods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cdtrans`
--
ALTER TABLE `cdtrans`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=384;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
