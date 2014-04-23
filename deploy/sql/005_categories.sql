INSERT INTO `blog`. `inserts` ( `name` , `created_at` )
VALUES ( '005_categories', NOW( ) );

CREATE TABLE IF NOT EXISTS `blog`.`categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(50) DEFAULT NULL,
  `name` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

INSERT INTO  `blog`.`categories` ( `id` , `slug` , `name` , `created_at` )
VALUES ( NULL ,  'events',  'Events', NOW( ) );

INSERT INTO  `blog`.`categories` ( `id` , `slug` , `name` , `created_at` )
VALUES ( NULL ,  'news',  'News', NOW( ) );
