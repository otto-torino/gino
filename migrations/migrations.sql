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
INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('page', 'can_edit_single_page', 'redazione singole pagine', 'redazione dei contenuti di singole pagine', 1);

-- Structure query
ALTER TABLE `page_entry` ADD `view_last_edit_date` TINYINT(1) NOT NULL DEFAULT '0' AFTER `users`, 
ADD `users_edit` VARCHAR(255) NULL AFTER `view_last_edit_date`;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
INSERT INTO auth_permission ([class], code, label, description, [admin]) VALUES
('page', 'can_edit_single_page', 'redazione singole pagine', 'redazione dei contenuti di singole pagine', 1);

-- Structure query

-- ALTER TABLE
CREATE TABLE page_entry_tmp (
  id int IDENTITY(1, 1),
  category_id int NULL,
  author int NOT NULL,
  creation_date datetime NOT NULL,
  last_edit_date datetime NOT NULL,
  title nvarchar(200) NOT NULL,
  slug nvarchar(200) NOT NULL UNIQUE,
  image nvarchar(200) NULL,
  url_image nvarchar(200) NULL,
  text text NOT NULL,
  tags nvarchar(255) NULL,
  enable_comments tinyint NOT NULL,
  published tinyint NOT NULL,
  social tinyint NOT NULL,
  private tinyint NOT NULL,
  users nvarchar(255) NULL,
  view_last_edit_date tinyint NULL,
  users_edit nvarchar(255) NULL,
  [read] int NOT NULL DEFAULT '0',
  tpl_code text,
  box_tpl_code text,
  CONSTRAINT PK_page_entry_tmp PRIMARY KEY (id)
)

SET IDENTITY_INSERT page_entry_tmp ON

INSERT INTO page_entry_tmp (id, category_id, author, creation_date, last_edit_date, title, slug, image, url_image, text, tags, enable_comments, published, social, private, users, [read], tpl_code, box_tpl_code) 
SELECT id, category_id, author, creation_date, last_edit_date, title, slug, image, url_image, text, tags, enable_comments, published, social, private, users, [read], tpl_code, box_tpl_code FROM page_entry;

SET IDENTITY_INSERT page_entry_tmp OFF

UPDATE page_entry_tmp SET view_last_edit_date='0';

ALTER TABLE page_entry_tmp ALTER COLUMN view_last_edit_date tinyint NOT NULL DEFAULT '0';

DROP TABLE page_entry;

exec sp_rename 'page_entry_tmp', 'page_entry';
go

exec sp_rename 'PK_page_entry_tmp', 'PK_page_entry';
go
