CREATE TABLE `test_users` (
 `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(50) NOT NULL,
 `age` tinyint(3) unsigned NOT NULL DEFAULT '0',
 `active` tinyint(4) NOT NULL DEFAULT '0',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
