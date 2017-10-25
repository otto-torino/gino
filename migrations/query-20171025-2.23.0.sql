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
-- Queries to be made if you maintain the constant URL_SEPARATOR value as "." and not "-". In any case, check the values of the rexp and urls fields in the sys_layout_skin table of the other records. 
UPDATE `sys_layout_skin` SET `rexp` = '#(index.php(\\?evt\\[index.index_page\\])?[^\\[\\]]*)?$#' WHERE `sys_layout_skin`.`id` = 1;
UPDATE `sys_layout_skin` SET `urls` = 'index.php?evt[index.admin_page]' WHERE `sys_layout_skin`.`id` = 3;
UPDATE `sys_layout_skin` SET `rexp` = '#evt\\[\\w+.((manage)|(wrapper))\\w*\\]#' WHERE `sys_layout_skin`.`id` = 4;
UPDATE `sys_layout_skin` SET `urls` = 'index.php?evt[auth.login]' WHERE `sys_layout_skin`.`id` = 5;
UPDATE `sys_layout_skin` SET `rexp` = '#(index.php(\\?evt\\[index.index_page\\])?[^\\[\\]]*)?$#' WHERE `sys_layout_skin`.`id` = 8;

-- Structure query

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
-- Queries to be made if you maintain the constant URL_SEPARATOR value as "." and not "-". In any case, check the values of the rexp and urls fields in the sys_layout_skin table of the other records. 
UPDATE sys_layout_skin SET rexp = '#(index.php(\?evt\[index.index_page\])?[^\[\]]*)?$#' WHERE sys_layout_skin.id = 1;
UPDATE sys_layout_skin SET urls = 'index.php?evt[index.admin_page]' WHERE sys_layout_skin.id = 3;
UPDATE sys_layout_skin SET rexp = '#evt\[\w+.((manage)|(wrapper))\w*\]#' WHERE sys_layout_skin.id = 4;
UPDATE sys_layout_skin SET urls = 'index.php?evt[auth.login]' WHERE sys_layout_skin.id = 5;
UPDATE sys_layout_skin SET rexp = '#(index.php(\?evt\[index.index_page\])?[^\[\]]*)?$#' WHERE sys_layout_skin.id = 8;

-- Structure query

