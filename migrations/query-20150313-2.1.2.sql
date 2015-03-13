-- 
-- Query to migrate the database to a new version
-- 
-- @filename: query-[date new tag yyyymmdd]-[version gino].sql
-- @database: MySQL, SQL Server
-- 

---------------------------------------------------------
---------------------------------------------------------
-- ATTENTION! Only query translation, not of structure --
---------------------------------------------------------
---------------------------------------------------------

-- --------------------------------------------------------
-- MySQL
-- --------------------------------------------------------

UPDATE `sys_conf` SET `dft_language` = '1' WHERE `sys_conf`.`id` = 1;

INSERT INTO `language_translation` (`tbl_id_value`, `tbl`, `field`, `language`, `text`) VALUES
(4, 'page_entry', 'title', 'en_US', 'Documentation'),
(4, 'page_entry', 'text', 'en_US', '<p>The documentation and reference are hosted on <b>github</b> as a <a href="https://github.com/otto-torino/gino/wiki" rel="external">wiki</a> that covers essentially the aspects of development of gino.</p>\n\n<p><img alt="github logo" src="contents/attachment/gino/github.jpg" style="float:left; margin-left:10px; margin-right:10px" />For more extensive documentation, including tutorials and how-to, you can refer to the relevant page on the <a href="http://gino.otto.to.it" rel="external">official website of gino</a>.</p>'),
(1, 'page_entry', 'title', 'en_US', 'About gino CMS'),
(1, 'page_entry', 'text', 'en_US', '<p>gino CMS is one of the open source framework developed by Otto, used to provide various services to our customers.</p>\n\n<p>It is a CMS, which is a web content management system, specifically designed to facilitate the organization and publication of web content.</p>'),
(3, 'page_entry', 'title', 'en_US', 'License'),
(3, 'page_entry', 'text', 'en_US', '<p><img alt="OSI approved license" src="contents/attachment/gino/OSI_logo.jpg" style="float:left; margin-left:10px; margin-right:10px" /> <a href="http://www.otto.to.it" rel="external">Otto</a> use and produce <a href="http://www.opensource.org/docs/osd" rel="external">open source</a> software.</p>\n\n<p>Specifically, gino CMS is distributed under the <a href="http://www.opensource.org/licenses/MIT" rel="external">MIT</a> license (MIT).</p>\n\n<p> </p>'),
(2, 'page_entry', 'text', 'en_US', '<p>gino was born and is optimized for the <b>LAMP</b> server model, however is not limited to these programs could also be used with other web servers, such as nginx and IIS, and SQL Server, pending implemented other connectors.</p>\n\n<p><img alt="LAMP logos" class="img-responsive" src="contents/attachment/gino/lamp.jpg" /></p>'),
(2, 'page_entry', 'title', 'en_US', 'Technology'),
(5, 'page_entry', 'title', 'en_US', 'Extend gino'),
(5, 'page_entry', 'text', 'en_US', '<p><img alt="plugin" src="contents/attachment/gino/plugin.jpg" style="float:left; margin-left:10px; margin-right:10px" />The gino functionality can be extended by using available additional modules. gino incorporates a simplified mechanism for loading and updating of these modules.</p>\n\n<p>For a list of the modules refer to page on the <a href="http://gino.otto.to.it/" rel="external">official website of gino</a>.</p>\n\n<p> </p>'),
(3, 'sys_menu_voices', 'label', 'en_US', 'DOCUMENTATION'),
(2, 'sys_menu_voices', 'label', 'en_US', 'Administration'),
(2, 'sys_module_app', 'label', 'en_US', 'Languages'),
(2, 'sys_module_app', 'description', 'en_US', 'Management of the languages available for translation'),
(1, 'sys_module_app', 'description', 'en_US', 'Main system settings'),
(3, 'sys_module_app', 'label', 'en_US', 'System modules'),
(3, 'sys_module_app', 'description', 'en_US', 'Modification, installation and removal of system modules'),
(4, 'sys_module_app', 'description', 'en_US', 'Modification, installation and removal modules of classes instantiated and function modules'),
(6, 'sys_module_app', 'label', 'en_US', 'Statistics'),
(6, 'sys_module_app', 'description', 'en_US', 'Statistics of access to the private area'),
(7, 'sys_module_app', 'description', 'en_US', 'Management of css, templates, skins and assigning to addresses or to classes of addresses'),
(8, 'sys_module_app', 'description', 'en_US', 'Personalized management of the header and footer of the system'),
(8, 'sys_module_app', 'label', 'en_US', 'Header and Footer'),
(9, 'sys_module_app', 'label', 'en_US', 'Attachments'),
(9, 'sys_module_app', 'description', 'en_US', 'File archives with tree'),
(11, 'sys_module_app', 'label', 'en_US', 'Pages'),
(11, 'sys_module_app', 'description', 'en_US', 'Html pages with tree'),
(13, 'sys_module_app', 'label', 'en_US', 'Site search'),
(13, 'sys_module_app', 'description', 'en_US', 'Search form on the site'),
(14, 'sys_module_app', 'description', 'en_US', 'Generator of modules containing php code'),
(15, 'sys_module_app', 'label', 'en_US', 'Instruments'),
(15, 'sys_module_app', 'description', 'en_US', 'Some tools, such as the list of available resources (with links) and mime type'),
(16, 'sys_module_app', 'label', 'en_US', 'Authentication'),
(16, 'sys_module_app', 'description', 'en_US', 'Module users, groups and permissions'),
(17, 'sys_module_app', 'label', 'en_US', 'System functions'),
(17, 'sys_module_app', 'description', 'en_US', 'System functions'),
(4, 'sys_module_app', 'label', 'en_US', 'Modules'),
(5, 'sys_module', 'description', 'en_US', 'Menu administrative area'),
(5, 'sys_module', 'label', 'en_US', 'Menu administration'),
(4, 'sys_module', 'description', 'en_US', 'Main menu'),
(6, 'sys_module', 'description', 'en_US', 'Top bar with choice language and authentication'),
(9, 'sys_module', 'description', 'en_US', 'Top bar with direct link to the administration of the individual modules');

UPDATE `language` SET `active` = '0' WHERE `language`.`id` = 3;
UPDATE `language` SET `active` = '0' WHERE `language`.`id` = 4;

-- --------------------------------------------------------
-- SQLServer
-- --------------------------------------------------------

UPDATE sys_conf SET dft_language = '1' WHERE sys_conf.id = 1;

INSERT INTO language_translation (tbl_id_value, tbl, field, language, text) VALUES
(4, 'page_entry', 'title', 'en_US', 'Documentation'),
(4, 'page_entry', 'text', 'en_US', '<p>The documentation and reference are hosted on <b>github</b> as a <a href="https://github.com/otto-torino/gino/wiki" rel="external">wiki</a> that covers essentially the aspects of development of gino.</p>\n\n<p><img alt="github logo" src="contents/attachment/gino/github.jpg" style="float:left; margin-left:10px; margin-right:10px" />For more extensive documentation, including tutorials and how-to, you can refer to the relevant page on the <a href="http://gino.otto.to.it" rel="external">official website of gino</a>.</p>'),
(1, 'page_entry', 'title', 'en_US', 'About gino CMS'),
(1, 'page_entry', 'text', 'en_US', '<p>gino CMS is one of the open source framework developed by Otto, used to provide various services to our customers.</p>\n\n<p>It is a CMS, which is a web content management system, specifically designed to facilitate the organization and publication of web content.</p>'),
(3, 'page_entry', 'title', 'en_US', 'License'),
(3, 'page_entry', 'text', 'en_US', '<p><img alt="OSI approved license" src="contents/attachment/gino/OSI_logo.jpg" style="float:left; margin-left:10px; margin-right:10px" /> <a href="http://www.otto.to.it" rel="external">Otto</a> use and produce <a href="http://www.opensource.org/docs/osd" rel="external">open source</a> software.</p>\n\n<p>Specifically, gino CMS is distributed under the <a href="http://www.opensource.org/licenses/MIT" rel="external">MIT</a> license (MIT).</p>\n\n<p> </p>'),
(2, 'page_entry', 'text', 'en_US', '<p>gino was born and is optimized for the <b>LAMP</b> server model, however is not limited to these programs could also be used with other web servers, such as nginx and IIS, and SQL Server, pending implemented other connectors.</p>\n\n<p><img alt="LAMP logos" class="img-responsive" src="contents/attachment/gino/lamp.jpg" /></p>'),
(2, 'page_entry', 'title', 'en_US', 'Technology'),
(5, 'page_entry', 'title', 'en_US', 'Extend gino'),
(5, 'page_entry', 'text', 'en_US', '<p><img alt="plugin" src="contents/attachment/gino/plugin.jpg" style="float:left; margin-left:10px; margin-right:10px" />The gino functionality can be extended by using available additional modules. gino incorporates a simplified mechanism for loading and updating of these modules.</p>\n\n<p>For a list of the modules refer to page on the <a href="http://gino.otto.to.it/" rel="external">official website of gino</a>.</p>\n\n<p> </p>'),
(3, 'sys_menu_voices', 'label', 'en_US', 'DOCUMENTATION'),
(2, 'sys_menu_voices', 'label', 'en_US', 'Administration'),
(2, 'sys_module_app', 'label', 'en_US', 'Languages'),
(2, 'sys_module_app', 'description', 'en_US', 'Management of the languages available for translation'),
(1, 'sys_module_app', 'description', 'en_US', 'Main system settings'),
(3, 'sys_module_app', 'label', 'en_US', 'System modules'),
(3, 'sys_module_app', 'description', 'en_US', 'Modification, installation and removal of system modules'),
(4, 'sys_module_app', 'description', 'en_US', 'Modification, installation and removal modules of classes instantiated and function modules'),
(6, 'sys_module_app', 'label', 'en_US', 'Statistics'),
(6, 'sys_module_app', 'description', 'en_US', 'Statistics of access to the private area'),
(7, 'sys_module_app', 'description', 'en_US', 'Management of css, templates, skins and assigning to addresses or to classes of addresses'),
(8, 'sys_module_app', 'description', 'en_US', 'Personalized management of the header and footer of the system'),
(8, 'sys_module_app', 'label', 'en_US', 'Header and Footer'),
(9, 'sys_module_app', 'label', 'en_US', 'Attachments'),
(9, 'sys_module_app', 'description', 'en_US', 'File archives with tree'),
(11, 'sys_module_app', 'label', 'en_US', 'Pages'),
(11, 'sys_module_app', 'description', 'en_US', 'Html pages with tree'),
(13, 'sys_module_app', 'label', 'en_US', 'Site search'),
(13, 'sys_module_app', 'description', 'en_US', 'Search form on the site'),
(14, 'sys_module_app', 'description', 'en_US', 'Generator of modules containing php code'),
(15, 'sys_module_app', 'label', 'en_US', 'Instruments'),
(15, 'sys_module_app', 'description', 'en_US', 'Some tools, such as the list of available resources (with links) and mime type'),
(16, 'sys_module_app', 'label', 'en_US', 'Authentication'),
(16, 'sys_module_app', 'description', 'en_US', 'Module users, groups and permissions'),
(17, 'sys_module_app', 'label', 'en_US', 'System functions'),
(17, 'sys_module_app', 'description', 'en_US', 'System functions'),
(4, 'sys_module_app', 'label', 'en_US', 'Modules'),
(5, 'sys_module', 'description', 'en_US', 'Menu administrative area'),
(5, 'sys_module', 'label', 'en_US', 'Menu administration'),
(4, 'sys_module', 'description', 'en_US', 'Main menu'),
(6, 'sys_module', 'description', 'en_US', 'Top bar with choice language and authentication'),
(9, 'sys_module', 'description', 'en_US', 'Top bar with direct link to the administration of the individual modules');

UPDATE language SET active = '0' WHERE language.id = 3;
UPDATE language SET active = '0' WHERE language.id = 4;
