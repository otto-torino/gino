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
UPDATE `sys_layout_skin` SET `administrative_area` = '1' WHERE `sys_layout_skin`.`id` = 3;
UPDATE `sys_layout_skin` SET `administrative_area` = '1' WHERE `sys_layout_skin`.`id` = 4;
UPDATE `sys_layout_skin` SET `administrative_area` = '1' WHERE `sys_layout_skin`.`id` = 11;

-- Structure query
ALTER TABLE `sys_layout_skin` ADD `administrative_area` TINYINT(1) NOT NULL DEFAULT '0' AFTER `cache`;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
UPDATE sys_layout_skin SET administrative_area = '1' WHERE id = 3;
UPDATE sys_layout_skin SET administrative_area = '1' WHERE id = 4;
UPDATE sys_layout_skin SET administrative_area = '1' WHERE id = 11;

-- Structure query
ALTER TABLE sys_layout_skin ADD administrative_area TINYINT NOT NULL 
	CONSTRAINT DF_sys_layout_skin_administrative_area DEFAULT '0';
