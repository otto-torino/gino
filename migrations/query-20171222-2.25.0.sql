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
ALTER TABLE `buildapp_item` ADD `m2mtf` TINYINT(1) NOT NULL AFTER `model_label`, 
ADD `m2mtf_name` VARCHAR(50) NULL AFTER `m2mtf`, 
ADD `m2mtf_model_name` VARCHAR(50) NULL AFTER `m2mtf_name`,
ADD `m2mtf_model_label` VARCHAR(100) NULL AFTER `m2mtf_model_name`;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query

-- Structure query
ALTER TABLE buildapp_item ADD m2mtf tinyint NOT NULL DEFAULT 0;
ALTER TABLE buildapp_item ADD m2mtf_name nvarchar(50) NULL;
ALTER TABLE buildapp_item ADD m2mtf_model_name nvarchar(50) NULL;
ALTER TABLE buildapp_item ADD m2mtf_model_label nvarchar(100) NULL;
