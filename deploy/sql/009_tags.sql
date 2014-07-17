INSERT INTO `blog`. `inserts` ( `name` , `created_at` )
VALUES ( '009_tags', NOW( ) );

ALTER TABLE  `blog`.`posts` ADD  `tags` TEXT NULL DEFAULT NULL AFTER  `body` ;