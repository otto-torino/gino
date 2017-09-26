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
('buildapp', 'can_admin', 'amministrazione', 'Amministrazione completa del modulo di creazione applicazioni', 1);

INSERT INTO `sys_module_app` (`id`, `label`, `name`, `active`, `tbl_name`, `instantiable`, `description`, `removable`, `class_version`) VALUES 
(NULL, 'Creazione App', 'buildapp', 1, 'buildapp', 0, 'Genera una applicazione predefinita pronta per essere personalizzata e installata in gino', 0, '1.0.0');

-- Structure query
CREATE TABLE `buildapp_item` (
  `id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL,
  `label` varchar(200) NOT NULL,
  `controller_name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `istantiable` tinyint(1) NOT NULL DEFAULT '0',
  `model_name` varchar(50) NOT NULL,
  `model_label` VARCHAR(100) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `buildapp_item`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `buildapp_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

-- Data query
INSERT INTO auth_permission (class, code, label, description, [admin]) VALUES
('buildapp', 'can_admin', 'amministrazione', 'Amministrazione completa del modulo di creazione applicazioni', 1);

INSERT INTO sys_module_app (label, name, active, tbl_name, instantiable, description, removable, class_version) VALUES 
('Creazione App', 'buildapp', 1, 'buildapp', 0, 'Genera una applicazione predefinita pronta per essere personalizzata e installata in gino', 0, '1.0.0');

-- Structure query
CREATE TABLE buildapp_item (
  id int IDENTITY(1, 1),
  creation_date datetime NOT NULL,
  label nvarchar(200) NOT NULL,
  controller_name nvarchar(50) NOT NULL,
  description text NOT NULL,
  istantiable tinyint NOT NULL DEFAULT '0',
  model_name nvarchar(50) NOT NULL,
  model_label nvarchar(100) NOT NULL,
  CONSTRAINT PK_buildapp_item PRIMARY KEY (id)
)
