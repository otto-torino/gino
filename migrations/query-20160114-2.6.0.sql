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
ALTER TABLE `sys_menu_opt` ADD `view_admin_voice` tinyint(1) NOT NULL DEFAULT '0' , ADD `view_logout_voice` tinyint(1) NOT NULL DEFAULT '0' ;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query

-- Structure query
ALTER TABLE sys_menu_opt ADD view_admin_voice tinyint NOT NULL DEFAULT 0;
ALTER TABLE sys_menu_opt ADD view_logout_voice tinyint NOT NULL DEFAULT 0;