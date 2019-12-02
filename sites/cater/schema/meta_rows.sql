INSERT INTO `meta` (`id`, `meta_name`, `template`, `data_limit`, `display`, `prefixSite`) VALUES
(1, 'description', '<meta name=\'description\' content=\'{}\' />', 155, 1, 0),
(2, 'author', '<meta name=\'author\' content=\'{}\' />', 50, 1, 0),
(3, 'keywords', '<meta name=\'keywords\' content=\'{}\' />', 200, 1, 0),
(4, 'og:title', '<meta property=\'og:title\'  content=\'{}\' />', 155, 1, 0),
(5, 'og:image', '<meta property=\'og:image\'  content=\'{}\' />', 200, 0, 1),
(6, 'og:description', '<meta property=\'og:description\'  content=\'{}\' />', 200, 0, 0),
(7, 'original-source', '<meta name=\'original-source\' content=\'{}\'>', 200, 1, 0),
(8, 'og:url', '<meta property=\"og:url\" content=\"{}\" />', 100, 1, 0),
(9, 'og:type', '<meta property=\"og:type\" content=\"{}\" />', 30, 1, 0);
COMMIT;