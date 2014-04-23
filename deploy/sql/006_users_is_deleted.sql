INSERT INTO `blog`. `inserts` ( `name` , `created_at` )
VALUES ( '006_users_is_deleted', NOW( ) );

ALTER TABLE  `users` ADD  `is_deleted` TINYINT( 1 ) NULL DEFAULT  '0' AFTER  `name` ;