-- 
-- Set of query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 
-- Data query: changes of labels or translations (no essential)
-- Structure query: changes of structure

-- @TODO UPDATE SQL SERVER DUMP

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------

-- Data query

-- Structure query
ALTER TABLE `sys_conf` CHANGE `password_crypt` `password_crypt` ENUM('none','sha1','md5') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'md5';

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query

-- Structure query
