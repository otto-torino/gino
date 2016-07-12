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
ALTER TABLE `search_site_opt` ADD `view_choices` TINYINT(1) NOT NULL DEFAULT '0';

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query

-- Structure query
ALTER TABLE search_site_opt ADD view_choices TINYINT NOT NULL 
	CONSTRAINT DF_search_site_opt_view_choices DEFAULT '0';
