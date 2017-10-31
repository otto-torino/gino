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
ALTER TABLE `sys_layout_skin` ADD `highest` TINYINT(1) NOT NULL DEFAULT '0' AFTER `urls`;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query

-- Structure query
CREATE TABLE sys_layout_skin_tmp (
  id int IDENTITY(1, 1),
  label nvarchar(200) NOT NULL,
  session nvarchar(128) NULL,
  rexp nvarchar(200) NULL,
  urls nvarchar(200) NULL,
  highest tinyint NULL,
  template nvarchar(200) NOT NULL,
  css int NULL,
  priority int NOT NULL,
  auth nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_layout_skin_auth CHECK (auth IN('yes','no','')),
  cache bigint NOT NULL DEFAULT '0',
  CONSTRAINT PK_sys_layout_skin_tmp PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_skin_tmp ON

INSERT INTO sys_layout_skin_tmp (id, label, session, rexp, urls, template, css, priority, auth, cache) 
SELECT id, label, session, rexp, urls, template, css, priority, auth, cache FROM sys_layout_skin;

SET IDENTITY_INSERT sys_layout_skin_tmp OFF

UPDATE sys_layout_skin_tmp SET highest='0';

ALTER TABLE sys_layout_skin_tmp ALTER COLUMN highest tinyint NOT NULL DEFAULT '0';

DROP TABLE sys_layout_skin;

exec sp_rename 'sys_layout_skin_tmp', 'sys_layout_skin';
go

exec sp_rename 'PK_sys_layout_skin_tmp', 'PK_sys_layout_skin';
go
