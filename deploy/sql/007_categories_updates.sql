INSERT INTO `blog`. `inserts` ( `name` , `created_at` )
VALUES ( '007_categories_updates', NOW( ) );

DROP TABLE IF EXISTS `blog`.`categories`;
CREATE TABLE IF NOT EXISTS `blog`.`categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

INSERT INTO `blog`.`categories` (`id`, `slug`, `name`, `created_at`) VALUES
(1, 'events', 'Events', NOW( ) ),
(2, 'news', 'News', NOW( ) ),
(3, 'release-notes', 'Release Notes', NOW( ) ),
(4, 'product-updates', 'Product Updates', NOW( ) ),
(5, 'tips-tricks', 'Tips & Tricks', NOW( ) ),
(6, 'education', 'Education', NOW( ) ),
(7, 'technology', 'Technology', NOW( ) ),
(8, 'webinars', 'Webinars', NOW( ) ),
(9, 'ilc', 'ILC', NOW( ) );