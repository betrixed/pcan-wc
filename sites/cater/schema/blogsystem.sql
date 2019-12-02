-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 06, 2018 at 09:43 AM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.2.12

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `pcan`
--

-- --------------------------------------------------------

--
-- Table structure for table `blog`
--

DROP TABLE IF EXISTS `blog`;
CREATE TABLE `blog` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(144) NOT NULL,
  `article` text,
  `title_clean` varchar(144) NOT NULL,
  `author_id` int(10) UNSIGNED DEFAULT NULL,
  `date_published` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '0',
  `comments` tinyint(1) NOT NULL DEFAULT '1',
  `featured` tinyint(1) NOT NULL DEFAULT '0',
  `style` varchar(30) NOT NULL DEFAULT 'noclass',
  `issue` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_category`
--

DROP TABLE IF EXISTS `blog_category`;
CREATE TABLE `blog_category` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(45) NOT NULL,
  `name_clean` varchar(45) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `date_created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_comment`
--

DROP TABLE IF EXISTS `blog_comment`;
CREATE TABLE `blog_comment` (
  `id` int(10) UNSIGNED NOT NULL,
  `blog_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `head_id` int(10) UNSIGNED DEFAULT NULL,
  `reply_to_id` int(10) UNSIGNED DEFAULT NULL,
  `title` varchar(127) DEFAULT NULL,
  `comment` text NOT NULL,
  `mark_read` tinyint(1) DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `date_comment` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_meta`
--

DROP TABLE IF EXISTS `blog_meta`;
CREATE TABLE `blog_meta` (
  `blog_id` int(10) UNSIGNED NOT NULL,
  `meta_id` int(10) UNSIGNED NOT NULL,
  `content` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='add meta tags to blog pages';

-- --------------------------------------------------------

--
-- Table structure for table `blog_style`
--

DROP TABLE IF EXISTS `blog_style`;
CREATE TABLE `blog_style` (
  `style_class` varchar(30) NOT NULL,
  `style_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `blog_to_category`
--

DROP TABLE IF EXISTS `blog_to_category`;
CREATE TABLE `blog_to_category` (
  `category_id` int(10) UNSIGNED NOT NULL,
  `blog_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `gallery`
--

DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery` (
  `id` int(10) UNSIGNED NOT NULL,
  `path` varchar(250) NOT NULL,
  `description` text,
  `name` varchar(250) NOT NULL,
  `last_upload` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `image`
--

DROP TABLE IF EXISTS `image`;
CREATE TABLE `image` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  `galleryid` int(10) UNSIGNED NOT NULL,
  `mime_type` varchar(30) DEFAULT NULL,
  `date_upload` datetime DEFAULT NULL,
  `file_size` int(10) UNSIGNED DEFAULT NULL,
  `description` text,
  `tiedimage` int(10) UNSIGNED DEFAULT NULL,
  `size_str` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `img_gallery`
--

DROP TABLE IF EXISTS `img_gallery`;
CREATE TABLE `img_gallery` (
  `imageid` int(10) UNSIGNED NOT NULL,
  `galleryid` int(10) UNSIGNED NOT NULL,
  `visible` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `id` int(10) UNSIGNED NOT NULL,
  `url` varchar(255) NOT NULL,
  `summary` text,
  `title` varchar(255) NOT NULL,
  `sitename` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `urltype` varchar(12) DEFAULT NULL,
  `refid` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `linktogallery`
--

DROP TABLE IF EXISTS `linktogallery`;
CREATE TABLE `linktogallery` (
  `gallid` int(11) NOT NULL,
  `linkid` int(11) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `link_gallery`
--

DROP TABLE IF EXISTS `link_gallery`;
CREATE TABLE `link_gallery` (
  `id` int(11) NOT NULL,
  `name` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` tinytext COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_on` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `meta`
--

DROP TABLE IF EXISTS `meta`;
CREATE TABLE `meta` (
  `id` int(10) UNSIGNED NOT NULL,
  `meta_name` varchar(20) NOT NULL,
  `template` varchar(80) DEFAULT NULL,
  `data_limit` int(11) DEFAULT NULL,
  `display` tinyint(1) DEFAULT '0',
  `prefixSite` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `title_clean_UNIQUE` (`title_clean`),
  ADD KEY `fk_blog_post_1_idx` (`author_id`),
  ADD KEY `title1` (`title`),
  ADD KEY `style` (`style`),
  ADD KEY `date_published` (`date_published`);

--
-- Indexes for table `blog_category`
--
ALTER TABLE `blog_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `index2` (`name_clean`);

--
-- Indexes for table `blog_comment`
--
ALTER TABLE `blog_comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_comment_1` (`blog_id`),
  ADD KEY `fk_comment_2_idx` (`user_id`);

--
-- Indexes for table `blog_meta`
--
ALTER TABLE `blog_meta`
  ADD PRIMARY KEY (`blog_id`,`meta_id`),
  ADD KEY `meta_id_idx` (`meta_id`);

--
-- Indexes for table `blog_style`
--
ALTER TABLE `blog_style`
  ADD PRIMARY KEY (`style_class`),
  ADD UNIQUE KEY `style_name_ix` (`style_name`);

--
-- Indexes for table `blog_to_category`
--
ALTER TABLE `blog_to_category`
  ADD PRIMARY KEY (`category_id`,`blog_id`),
  ADD KEY `fk_blog_to_category_2_idx` (`blog_id`);

--
-- Indexes for table `gallery`
--
ALTER TABLE `gallery`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD UNIQUE KEY `name_UNIQUE` (`name`);

--
-- Indexes for table `image`
--
ALTER TABLE `image`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_mygallery_idx` (`galleryid`);

--
-- Indexes for table `img_gallery`
--
ALTER TABLE `img_gallery`
  ADD PRIMARY KEY (`imageid`,`galleryid`),
  ADD KEY `fk_img_gallery_2_idx` (`galleryid`);

--
-- Indexes for table `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date_created_ix` (`date_created`),
  ADD KEY `url_uix` (`url`);

--
-- Indexes for table `linktogallery`
--
ALTER TABLE `linktogallery`
  ADD PRIMARY KEY (`gallid`,`linkid`);

--
-- Indexes for table `link_gallery`
--
ALTER TABLE `link_gallery`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_category`
--
ALTER TABLE `blog_category`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `blog_comment`
--
ALTER TABLE `blog_comment`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `gallery`
--
ALTER TABLE `gallery`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `image`
--
ALTER TABLE `image`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `links`
--
ALTER TABLE `links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `link_gallery`
--
ALTER TABLE `link_gallery`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `meta`
--
ALTER TABLE `meta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`style`) REFERENCES `blog_style` (`style_class`),
  ADD CONSTRAINT `fk_blog_post_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE NO ACTION;

--
-- Constraints for table `blog_comment`
--
ALTER TABLE `blog_comment`
  ADD CONSTRAINT `fk_comment_1` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_comment_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `blog_meta`
--
ALTER TABLE `blog_meta`
  ADD CONSTRAINT `blog_id` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `meta_id` FOREIGN KEY (`meta_id`) REFERENCES `meta` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints for table `blog_to_category`
--
ALTER TABLE `blog_to_category`
  ADD CONSTRAINT `fk_blog_to_category_1` FOREIGN KEY (`category_id`) REFERENCES `blog_category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_blog_to_category_2` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `image`
--
ALTER TABLE `image`
  ADD CONSTRAINT `fk_mygallery` FOREIGN KEY (`galleryid`) REFERENCES `gallery` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `img_gallery`
--
ALTER TABLE `img_gallery`
  ADD CONSTRAINT `fk_img_gallery_1` FOREIGN KEY (`imageid`) REFERENCES `image` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `fk_img_gallery_2` FOREIGN KEY (`galleryid`) REFERENCES `gallery` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
