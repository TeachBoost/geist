INSERT INTO `blog`.`inserts` ( `name` , `created_at` )
VALUES ( '006_users_is_deleted', NOW( ) );

ALTER TABLE  `blog`.`users` ADD  `is_deleted` TINYINT( 1 ) NULL DEFAULT  '0' AFTER  `name` ;