INSERT INTO `blog`. `inserts` ( `name` , `created_at` )
VALUES ( '004_posts', NOW( ) );

CREATE TABLE IF NOT EXISTS `blog`.`posts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `slug` varchar( 255 ) DEFAULT NULL,
  `excerpt` text,
  `body` text,
  `category_id` int(10),
  `status` varchar(10) DEFAULT 'draft',
  `is_deleted` tinyint(1) unsigned DEFAULT '0',
  `post_date` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `modified_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
