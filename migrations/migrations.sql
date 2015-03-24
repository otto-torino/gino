-- 
-- Set of query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 
-- Data query: changes of labels or translations (no essential)
-- Structure query: changes of structure

-- @TODO AGGIORNARE DUMP SQL SERVER

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------

-- Data query
UPDATE `auth_user` SET `username` = 'admin' WHERE `id` = 1;

-- Structure query
ALTER TABLE `auth_opt` DROP `self_registration`;
ALTER TABLE `auth_opt` DROP `self_registration_active`;
DROP TABLE `auth_user_registration`;

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

-- Data query
UPDATE auth_user SET username = 'admin' WHERE id = 1;

-- Structure query
ALTER TABLE auth_opt DROP COLUMN self_registration, self_registration_active;
DROP TABLE auth_user_registration;

CREATE TABLE auth_registration_profile (
  id int IDENTITY(1, 1),
  description nvarchar(255) NOT NULL,
  title nvarchar(255) NULL,
  text text,
  terms text,
  auto_enable tinyint NOT NULL,
  add_information tinyint NULL,
  add_information_module_type tinyint NULL,
  add_information_module_id int NULL, 
  CONSTRAINT PK_auth_registration_profile PRIMARY KEY (id)
)

CREATE TABLE auth_registration_profile_group (
  id int IDENTITY(1, 1),
  registrationprofile_id int NOT NULL,
  group_id int NOT NULL, 
  CONSTRAINT PK_auth_registration_profile_group PRIMARY KEY (id)
)

CREATE TABLE auth_registration_request (
  id int IDENTITY(1, 1),
  registration_profile int NOT NULL,
  [date] datetime NOT NULL,
  [code] nvarchar(32) NOT NULL,
  firstname nvarchar(255) NOT NULL,
  lastname nvarchar(255) NOT NULL,
  username nvarchar(50) NOT NULL,
  password nvarchar(100) NOT NULL,
  email nvarchar(128) NOT NULL,
  confirmed tinyint NOT NULL DEFAULT '0',
  [user] int NULL, 
  CONSTRAINT PK_auth_registration_request PRIMARY KEY (id)
)

