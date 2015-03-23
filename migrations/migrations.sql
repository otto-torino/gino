-- 
-- Query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------
UPDATE `auth_user` SET `username` = 'admin' WHERE `id` = 1;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------
UPDATE auth_user SET username = 'admin' WHERE id = 1;
