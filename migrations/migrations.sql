-- 
-- Query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 

-- @TODO AGGIORNARE DUMP SQL SERVER

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------
UPDATE `auth_user` SET `username` = 'admin' WHERE `id` = 1;

ALTER TABLE 'auth_opt' DELETE COLUMN 'self_registration';
ALTER TABLE 'auth_opt' DELETE COLUMN 'self_registration_active';

CREATE TABLE IF NOT EXISTS `auth_registration_profile` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `text` text,
  `terms` text,
  `auto_enable` tinyint(1) NOT NULL,
  `add_information` tinyint(1) DEFAULT NULL,
  `add_information_module_type` tinyint(1) DEFAULT NULL,
  `add_information_module_id` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `auth_registration_profile`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `auth_registration_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `auth_registration_profile_group` (
  `id` int(11) NOT NULL,
  `registrationprofile_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `auth_registration_profile_group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `auth_registration_profile_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `auth_registration_request` (
  `id` int(11) NOT NULL,
  `registration_profile` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `code` varchar(32) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(128) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `user` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `auth_registration_request`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `auth_registration_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------
UPDATE auth_user SET username = 'admin' WHERE id = 1;


