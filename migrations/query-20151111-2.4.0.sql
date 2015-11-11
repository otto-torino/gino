-- 
-- Set of query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 
-- Data query: changes of labels or translations (no essential)
-- Structure query: changes of structure

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------

-- Data query
DELETE FROM auth_permission WHERE id='5';

-- Structure query
ALTER TABLE `sys_module` CHANGE `description` `description` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
DELETE FROM auth_permission WHERE id='5';

-- Structure query
ALTER TABLE sys_module ALTER COLUMN description TEXT;
