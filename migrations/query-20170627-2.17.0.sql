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
INSERT INTO `nation` (`id`, `it_IT`, `en_US`, `fr_FR`, `onu`) VALUES (NULL, 'Repubblica di Cina (Taiwan)', 'Republic of China (Taiwan)', 'République de Chine (Taiwan)', NULL);

UPDATE `page_opt` SET `last_title` = 'Pagine recenti', `last_number` = '10', `last_tpl_code` = '<article>\r\n<h1>{{ title|link }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text|chars:300 }}\r\n<div class=\"null\"></div>\r\n</article>' WHERE `page_opt`.`id` = 1;

-- Structure query
ALTER TABLE `nation` CHANGE `onu` `onu` DATE NULL;

ALTER TABLE `page_opt` 
	ADD `last_title` VARCHAR(200) NOT NULL AFTER `instance`, 
	ADD `last_number` TINYINT(2) NOT NULL AFTER `last_title`, 
	ADD `last_tpl_code` TEXT NOT NULL AFTER `last_number`;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
INSERT INTO nation (it_IT, en_US, fr_FR, onu) VALUES ('Repubblica di Cina (Taiwan)', 'Republic of China (Taiwan)', 'République de Chine (Taiwan)', NULL);

UPDATE page_opt SET last_title = 'Pagine recenti', last_number = '10', last_tpl_code = '<article>
<h1>{{ title|link }}</h1>
<p>{{ img|class:left }}</p>
{{ text|chars:300 }}
<div class=\"null\"></div>
</article>' WHERE page_opt.id = 1;

-- Structure query
ALTER TABLE nation ALTER COLUMN onu DATE NULL;

CREATE TABLE page_opt_tmp (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  last_title nvarchar(200) NULL, 
  last_number tinyint NULL, 
  last_tpl_code text NULL,
  showcase_title nvarchar(200) NOT NULL,
  showcase_number smallint NOT NULL,
  showcase_auto_start tinyint NOT NULL,
  showcase_auto_interval int NOT NULL,
  showcase_tpl_code text NOT NULL,
  entry_tpl_code text NOT NULL,
  box_tpl_code text NOT NULL,
  comment_moderation tinyint NOT NULL,
  comment_notification tinyint NOT NULL,
  newsletter_entries_number smallint NOT NULL,
  newsletter_tpl_code text,
  CONSTRAINT PK_page_opt_tmp PRIMARY KEY (id)
)

SET IDENTITY_INSERT page_opt_tmp ON

INSERT INTO page_opt_tmp (id, instance, showcase_title, showcase_number, showcase_auto_start, showcase_auto_interval, showcase_tpl_code, entry_tpl_code, box_tpl_code, comment_moderation, comment_notification, newsletter_entries_number, newsletter_tpl_code) 
SELECT id, instance, showcase_title, showcase_number, showcase_auto_start, showcase_auto_interval, showcase_tpl_code, entry_tpl_code, box_tpl_code, comment_moderation, comment_notification, newsletter_entries_number, newsletter_tpl_code FROM page_opt;

SET IDENTITY_INSERT page_opt_tmp OFF

UPDATE page_opt_tmp SET last_title='Pagine recenti', last_number=10, last_tpl_code='<article>
<h1>{{ title|link }}</h1>
<p>{{ img|class:left }}</p>
{{ text|chars:300 }}
<div class=\"null\"></div>
</article>';

ALTER TABLE page_opt_tmp ALTER COLUMN last_title nvarchar(200) NOT NULL, 
	last_number tinyint NOT NULL, 
	last_tpl_code text NOT NULL;

DROP TABLE page_opt;

exec sp_rename 'page_opt_tmp', 'page_opt';
go

exec sp_rename 'PK_page_opt_tmp', 'PK_page_opt';
go
