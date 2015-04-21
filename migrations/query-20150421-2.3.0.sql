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

-- Structure query
ALTER TABLE `sys_conf` ADD `query_cache` TINYINT(1) NOT NULL DEFAULT '0', ADD `query_cache_time` SMALLINT(4) NULL;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query

-- Structure query
ALTER TABLE sys_conf ADD query_cache TINYINT NOT NULL 
	CONSTRAINT DF_sys_conf_query_cache DEFAULT '0';
ALTER TABLE sys_conf ADD query_cache_time SMALLINT NULL;
