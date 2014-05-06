INSERT INTO `blog`. `inserts` ( `name` , `created_at` )
VALUES ( '008_spotlights_category', NOW( ) );

INSERT INTO `blog`.`categories` (`id`, `slug`, `name`, `created_at`) 
VALUES (NULL, 'spotlights', 'Customer Spotlights', NULL);