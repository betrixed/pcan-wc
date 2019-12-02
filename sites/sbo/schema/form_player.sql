CREATE TABLE `form_player` (
  `id` int(11) NOT NULL,
  `created_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `name` int(11) NOT NULL COMMENT 'Full Name',
  `email` tinytext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Email Address',
  `phone` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Phone',
  `instrument` tinytext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Main Instrument',
  `experience` tinytext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Level/Duration of experience',
  `history` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'General Music Experience'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
