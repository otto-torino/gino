
--
-- Database: `dbgino`
--

-- --------------------------------------------------------

--
-- Table structure for table `attachment`
--

CREATE TABLE IF NOT EXISTS `attachment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL,
  `file` varchar(100) NOT NULL,
  `notes` text,
  `insertion_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `attachment`
--

INSERT INTO `attachment` (`id`, `category`, `file`, `notes`, `insertion_date`, `last_edit_date`) VALUES
(1, 1, 'lamp.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(2, 1, 'OSI_logo.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(3, 1, 'brightness_controller.png', '', '2013-04-03 16:20:37', '2013-12-06 16:51:55'),
(4, 1, 'plugin.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(5, 1, 'github.jpg', 'Logo GitHub', '2014-12-01 16:20:17', '2014-12-01 16:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `attachment_ctg`
--

CREATE TABLE IF NOT EXISTS `attachment_ctg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `directory` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `attachment_ctg`
--

INSERT INTO `attachment_ctg` (`id`, `name`, `directory`) VALUES
(1, 'gino', 'gino');

-- --------------------------------------------------------

--
-- Table structure for table `auth_group`
--

CREATE TABLE IF NOT EXISTS `auth_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `auth_group_perm`
--

CREATE TABLE IF NOT EXISTS `auth_group_perm` (
  `instance` int(11) NOT NULL,
  `group_id` smallint(6) NOT NULL,
  `perm_id` smallint(6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `auth_opt`
--

CREATE TABLE IF NOT EXISTS `auth_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `users_for_page` smallint(3) NOT NULL,
  `user_more_info` tinyint(1) NOT NULL,
  `user_card_view` tinyint(1) NOT NULL,
  `username_as_email` tinyint(1) NOT NULL,
  `aut_pwd` tinyint(1) NOT NULL,
  `aut_pwd_length` smallint(2) NOT NULL,
  `pwd_min_length` smallint(2) NOT NULL,
  `pwd_max_length` smallint(2) NOT NULL,
  `pwd_numeric_number` int(10) NOT NULL,
  `ldap_auth` tinyint(1) NOT NULL,
  `ldap_auth_only` tinyint(1) NOT NULL,
  `ldap_single_user` varchar(50) NULL,
  `ldap_auth_password` varchar(100) NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `auth_opt`
--

INSERT INTO `auth_opt` (`id`, `instance`, `users_for_page`, `user_more_info`, `user_card_view`, `username_as_email`, `aut_pwd`, `aut_pwd_length`, `pwd_min_length`, `pwd_max_length`, `pwd_numeric_number`, `ldap_auth`, `ldap_auth_only`, `ldap_single_user`, `ldap_auth_password`) VALUES
(1, 0, 10, 0, 1, 0, 0, 10, 6, 14, 2, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `auth_permission`
--

CREATE TABLE IF NOT EXISTS `auth_permission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class` varchar(128) NOT NULL,
  `code` varchar(128) NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` text,
  `admin` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=23 ;

--
-- Dumping data for table `auth_permission`
--

INSERT INTO `auth_permission` (`id`, `class`, `code`, `label`, `description`, `admin`) VALUES
(1, 'core', 'is_logged', 'login effettuato', 'Utente che ha effettuato il login', 0),
(2, 'core', 'is_staff', 'appartenenza allo staff', 'Possibilità di accedere all''area amministrativa', 1),
(3, 'attachment', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(4, 'auth', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(6, 'instruments', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(7, 'instruments', 'can_view', 'visualizzazione', 'visualizzazione degli strumenti', 1),
(8, 'language', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(9, 'page', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(10, 'page', 'can_publish', 'pubblicazione', 'Pubblicazione di pagine e commenti e redazione contenuti', 1),
(11, 'page', 'can_edit', 'redazione', 'redazione dei contenuti', 1),
(12, 'page', 'can_edit_single_page', 'redazione singole pagine', 'redazione dei contenuti di singole pagine', 1),
(13, 'page', 'can_view_private', 'visualizzazione pagine private', 'visualizzazione di pagine che sono state salvate come private', 0),
(14, 'phpModuleView', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(15, 'searchSite', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(16, 'sysConf', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(17, 'graphics', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(18, 'layout', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(19, 'menu', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(20, 'menu', 'can_edit', 'redazione', 'inserimento modifica ed eliminazione di voci di menu.', 1),
(21, 'statistics', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
(22, 'buildapp', 'can_admin', 'amministrazione', 'Amministrazione completa del modulo di creazione applicazioni', 1);

-- --------------------------------------------------------

--
-- Table structure for table `auth_registration_profile`
--

CREATE TABLE IF NOT EXISTS `auth_registration_profile` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `text` text,
  `terms` text,
  `auto_enable` tinyint(1) NOT NULL,
  `add_information` tinyint(1) DEFAULT NULL,
  `add_information_module_type` tinyint(1) DEFAULT NULL,
  `add_information_module_id` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `auth_registration_profile`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `auth_registration_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_registration_profile_group`
--

CREATE TABLE IF NOT EXISTS `auth_registration_profile_group` (
  `id` int(11) NOT NULL,
  `registrationprofile_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `auth_registration_profile_group`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `auth_registration_profile_group`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_registration_request`
--

CREATE TABLE IF NOT EXISTS `auth_registration_request` (
  `id` int(11) NOT NULL,
  `registration_profile` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `code` varchar(32) NOT NULL,
  `firstname` varchar(255) NOT NULL,
  `lastname` varchar(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `email` varchar(128) NOT NULL,
  `confirmed` tinyint(1) NOT NULL DEFAULT '0',
  `user` int(11) DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

ALTER TABLE `auth_registration_request`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `auth_registration_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Table structure for table `auth_user`
--

CREATE TABLE IF NOT EXISTS `auth_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `firstname` varchar(50) NOT NULL DEFAULT '',
  `lastname` varchar(50) NOT NULL DEFAULT '',
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `fax` varchar(30) DEFAULT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `username` varchar(50) NOT NULL,
  `userpwd` varchar(100) NOT NULL,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `address` varchar(200) DEFAULT NULL,
  `cap` int(5) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `nation` smallint(4) DEFAULT NULL,
  `text` text,
  `photo` varchar(50) DEFAULT NULL,
  `publication` tinyint(1) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `ldap` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `auth_user`
--

INSERT INTO `auth_user` (`id`, `firstname`, `lastname`, `company`, `phone`, `fax`, `email`, `username`, `userpwd`, `is_admin`, `address`, `cap`, `city`, `nation`, `text`, `photo`, `publication`, `date`, `active`, `ldap`) VALUES
(1, 'utente', 'amministratore', 'otto srl', '+39 011 8987553', '', 'support@otto.to.it', 'admin', '1844156d4166d94387f1a4ad031ca5fa', 1, 'piazza Gran Madre di Dio, 7', 10131, 'Torino', 83, '', '', 2, '2011-10-10 01:00:00', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `auth_user_add`
--

CREATE TABLE IF NOT EXISTS `auth_user_add` (
  `user_id` int(11) NOT NULL,
  `field1` tinyint(1) NOT NULL DEFAULT '0',
  `field2` tinyint(1) NOT NULL DEFAULT '0',
  `field3` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `auth_user_group`
--

CREATE TABLE IF NOT EXISTS `auth_user_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `auth_user_perm`
--

CREATE TABLE IF NOT EXISTS `auth_user_perm` (
  `instance` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `perm_id` smallint(6) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `buildapp_item`
--

CREATE TABLE `buildapp_item` (
  `id` int(11) NOT NULL,
  `creation_date` datetime NOT NULL,
  `label` varchar(200) NOT NULL,
  `controller_name` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `istantiable` tinyint(1) NOT NULL DEFAULT '0',
  `model_name` varchar(50) NOT NULL,
  `model_label` VARCHAR(100) NOT NULL,
  `m2mtf` TINYINT(1) NOT NULL,
  `m2mtf_name` VARCHAR(50) NULL,
  `m2mtf_model_name` VARCHAR(50) NULL,
  `m2mtf_model_label` VARCHAR(100) NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `buildapp_item`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `buildapp_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `instruments`
--

CREATE TABLE IF NOT EXISTS `instruments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `order_list` smallint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `instruments`
--

INSERT INTO `instruments` (`id`, `name`, `description`, `order_list`) VALUES
(1, 'Collegamenti', 'Elenco interfacce e pagine disponibili', 1),
(2, 'Mime-Type', 'Elenco dei mime type con le estensioni di riferimento', 2);

-- --------------------------------------------------------

--
-- Table structure for table `instruments_opt`
--

CREATE TABLE IF NOT EXISTS `instruments_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `id` int(2) NOT NULL AUTO_INCREMENT,
  `label` varchar(10) NOT NULL,
  `language` varchar(50) NOT NULL DEFAULT '',
  `language_code` varchar(5) NOT NULL DEFAULT '',
  `country_code` varchar(5) NOT NULL,
  `active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `label`, `language`, `language_code`, `country_code`, `active`) VALUES
(1, 'ITA', 'italiano', 'it', 'IT', 1),
(2, 'ENG', 'english', 'en', 'US', 1),
(3, 'ESP', 'espanol', 'es', 'ES', 0),
(4, 'FRA', 'français', 'fr', 'FR', 0);

-- --------------------------------------------------------

--
-- Table structure for table `language_opt`
--

CREATE TABLE IF NOT EXISTS `language_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `opt_flag` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `language_opt`
--

INSERT INTO `language_opt` (`id`, `instance`, `title`, `opt_flag`) VALUES
(1, 0, 'Lingue', 0);

-- --------------------------------------------------------

--
-- Table structure for table `language_translation`
--

CREATE TABLE IF NOT EXISTS `language_translation` (
  `tbl_id_value` int(11) DEFAULT NULL,
  `tbl` varchar(200) DEFAULT NULL,
  `field` varchar(200) DEFAULT NULL,
  `language` varchar(5) DEFAULT NULL,
  `text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dumping data for table `language_translation`
--

INSERT INTO `language_translation` (`tbl_id_value`, `tbl`, `field`, `language`, `text`) VALUES
(1, 'page_layout', 'name', 'en_US', 'visual editor'),
(2, 'page_layout', 'name', 'en_US', 'media (image/video)'),
(3, 'page_layout', 'name', 'en_US', 'media left - editor right'),
(4, 'page_layout', 'name', 'en_US', 'editor left - media right'),
(5, 'page_layout', 'name', 'en_US', 'link to file'),
(8, 'page_layout', 'name', 'en_US', 'by file'),
(9, 'page_layout', 'name', 'en_US', 'by html code'),
(1, 'sys_conf', 'head_title', 'en_US', 'GINO ENG'),
(1, 'sys_module_app', 'label', 'en_US', 'Settings'),
(1, 'sys_conf', 'head_title', 'es_ES', 'ESP'),
(1, 'sys_conf', 'head_title', 'fr_FR', 'FRENCH'),
(4, 'sys_module', 'label', 'en_US', 'Main menu'),
(10, 'sys_graphics', 'description', 'en_US', 'footer engli'),
(10, 'sys_graphics', 'html', 'en_US', 'textarea english f'),
(10, 'sys_graphics', 'html', 'en_US', 'textarea english f'),
(4, 'language_opt', 'title', 'en_US', 'Languages'),
(4, 'language_opt', 'title', 'es_ES', 'Idiomas'),
(2, 'page_entry', 'title', 'en_US', 'Documentation'),
(2, 'page_entry', 'text', 'en_US', '<p>The documentation and reference are hosted on <b>github</b> as a <a href=\"https://github.com/otto-torino/gino/wiki\" rel=\"external\">wiki</a> that covers essentially the aspects of development of gino.</p>\n\n<p><img alt=\"github logo\" src=\"contents/attachment/gino/github.jpg\" style=\"float:left; margin-left:10px; margin-right:10px\" />For more extensive documentation, including tutorials and how-to, you can refer to the relevant page on the <a href=\"http://gino.otto.to.it\" rel=\"external\">official website of gino</a>.</p>'),
(1, 'page_entry', 'title', 'en_US', 'About gino CMS'),
(1, 'page_entry', 'text', 'en_US', '<p>gino CMS is an <a href=\"http://www.opensource.org/docs/osd\" rel=\"external\">open source</a> framework developed by <a href=\"http://www.otto.to.it\" rel=\"external\">Otto</a> and distributed under the <a href=\"http://www.opensource.org/licenses/MIT\" rel=\"external\">MIT license</a> (MIT).</p>\n\n<p><img alt=\"OSI approved license\" src=\"contents/attachment/gino/OSI_logo.jpg\" style=\"float:left; margin-left:10px; margin-right:10px\" />It is a <strong>CMS</strong>, which stands for Content Management System, created specifically to facilitate its organization and publication of web content.</p>'),
(3, 'page_entry', 'title', 'en_US', 'Implementations'),
(3, 'page_entry', 'text', 'en_US', '<p><img alt=\"plugin\" src=\"contents/attachment/gino/plugin.jpg\" style=\"float:left; margin-left:10px; margin-right:10px\" />The functionality of gino can be expanded using available <strong>additional modules</strong>, or by creating applications directly with the help of the application \"Creation App\" present in the administrative area.</p>\n\n<p>These modules are easily loaded and updated using the \"System modules\" application. For a list of available modules refer to the page on the <a href=\"http://gino.otto.to.it/\" rel=\"external\">official website of gino</a>.</p>\n\n<p>In addition, <a href=\"https://getcomposer.org/\" target=\"_blank\">Composer</a> is also used to manage some external libraries. Composer is a Dependency Manager for PHP, which is a tool for managing dependencies in PHP; in practice it allows to declare the libraries on which the project depends, managing them for us.</p>\n\n<p>To use Composer correctly it is recommended to refer to the gino <a href=\"https://github.com/otto-torino/gino/wiki\" rel=\"external\">wiki</a> and its official documentation; on the <a href=\"https://getcomposer.org/doc/00-intro.md\" target=\"_blank\">Getting Started</a> page of the project you will find all the information on the installation procedure.<br />\nIt should also be noted that it is advisable to install only the necessary external packages with the Composer, as the space required may be important.</p>'),
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

-- --------------------------------------------------------

--
-- Table structure for table `nation`
--

CREATE TABLE IF NOT EXISTS `nation` (
  `id` smallint(4) NOT NULL AUTO_INCREMENT,
  `it_IT` varchar(100) NOT NULL,
  `en_US` varchar(100) NOT NULL,
  `fr_FR` varchar(100) NOT NULL,
  `onu` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=194 ;

--
-- Dumping data for table `nation`
--

INSERT INTO `nation` (`id`, `it_IT`, `en_US`, `fr_FR`, `onu`) VALUES
(1, 'Afghanistan', 'Afghanistan', 'Afghanistan', '1946-11-19'),
(2, 'Albania', 'Albania', 'Albanie', '1955-12-14'),
(3, 'Algeria', 'Algeria', 'Algérie', '1962-10-08'),
(4, 'Andorra', 'Andorra', 'Andorre', '1993-07-28'),
(5, 'Angola', 'Angola', 'Angola', '1976-12-01'),
(6, 'Antigua and Barbuda', 'Antigua and Barbuda', 'Antigua-et-Barbuda', '1981-11-11'),
(7, 'Argentina', 'Argentina', 'Argentine', '1945-10-24'),
(8, 'Armenia', 'Armenia', 'Arménie', '1992-03-02'),
(9, 'Australia', 'Australia', 'Australie', '1945-11-01'),
(10, 'Austria', 'Austria', 'Autriche', '1955-12-14'),
(11, 'Azerbaijan', 'Azerbaijan', 'Azerbaïdjan', '1992-03-02'),
(12, 'Bahamas', 'Bahamas', 'Bahamas', '1973-09-18'),
(13, 'Bahrein', 'Bahrain', 'Bahreïn', '1971-09-21'),
(14, 'Bangladesh', 'Bangladesh', 'Bangladesh', '1974-09-17'),
(15, 'Barbados', 'Barbados', 'Barbade', '1966-12-09'),
(16, 'Bielorussia', 'Belarus', 'Bélarus', '1945-10-24'),
(17, 'Belgio', 'Belgium', 'Belgique', '1945-12-27'),
(18, 'Belize', 'Belize', 'Belize', '1981-09-25'),
(19, 'Benin', 'Benin', 'Bénin', '1960-09-20'),
(20, 'Bhutan', 'Bhutan', 'Bhoutan', '1971-09-21'),
(21, 'Bolivia', 'Bolivia', 'Bolivie', '1945-11-14'),
(22, 'Bosnia Erzegovina', 'Bosnia and Herzegovina', 'Bosnie-Herzégovine', '1992-05-22'),
(23, 'Botswana', 'Botswana', 'Botswana', '1966-10-17'),
(24, 'Brasile', 'Brazil', 'Brésil', '1945-10-24'),
(25, 'Brunei Darussalam', 'Brunei Darussalam', 'Brunéi Darussalam', '1984-09-21'),
(26, 'Bulgaria', 'Bulgaria', 'Bulgarie', '1955-12-14'),
(27, 'Burkina Faso', 'Burkina Faso', 'Burkina Faso', '1960-09-20'),
(28, 'Burundi', 'Burundi', 'Burundi', '1962-09-18'),
(29, 'Cambogia', 'Cambodia', 'Cambodge', '1955-12-14'),
(30, 'Camerun', 'Cameroon', 'Cameroun', '1960-09-20'),
(31, 'Canada', 'Canada', 'Canada', '1945-11-09'),
(32, 'Capo Verde', 'Cape Verde', 'Cap-Vert', '1975-09-16'),
(33, 'Repubblica Centrafricana', 'Central African Republic', 'République centrafricaine', '1960-09-20'),
(34, 'Ciad', 'Chad', 'Tchad', '1960-09-20'),
(35, 'Cile', 'Chile', 'Chili', '1945-10-24'),
(36, 'Cina', 'China', 'Chine', '1945-10-24'),
(37, 'Colombia', 'Colombia', 'Colombie', '1945-11-05'),
(38, 'Comore', 'Comoros', 'Comores', '1975-11-12'),
(39, 'Congo', 'Congo (Republic of the)', 'Congo', '1960-09-20'),
(40, 'Costa Rica', 'Costa Rica', 'Costa Rica', '1945-11-02'),
(41, 'Costa d''Avorio', 'Côte d''Ivoire', 'Côte d''Ivoire', '1960-09-20'),
(42, 'Croazia', 'Croatia', 'Croatie', '1992-05-22'),
(43, 'Cuba', 'Cuba', 'Cuba', '1945-10-24'),
(44, 'Cipro', 'Cyprus', 'Chypre', '1960-09-20'),
(45, 'Repubblica Ceca', 'Czech Republic', 'République tchèque', '1993-01-19'),
(46, 'Repubblica Democratica Popolare di Corea', 'Democratic People''s Republic of Korea', 'République populaire démocratique de Corée', '1991-09-17'),
(47, 'Repubblica Democratica del Congo', 'Democratic Republic of the Congo', 'République démocratique du Congo', '1960-09-20'),
(48, 'Danimarca', 'Denmark', 'Danemark', '1945-10-24'),
(49, 'Gibuti', 'Djibouti', 'Djibouti', '1977-09-20'),
(50, 'Dominica', 'Dominica', 'Dominique', '1978-12-18'),
(51, 'Repubblica Dominicana', 'Dominican Republic', 'République dominicaine', '1945-10-24'),
(52, 'Ecuador', 'Ecuador', 'Equateur', '1945-12-21'),
(53, 'Egitto', 'Egypt', 'Égypte', '1945-10-24'),
(54, 'El Salvador', 'El Salvador', 'El Salvador', '1945-10-24'),
(55, 'Guinea Equatoriale', 'Equatorial Guinea', 'Guinée équatoriale', '1968-11-12'),
(56, 'Eritrea', 'Eritrea', 'Érythrée', '1993-05-28'),
(57, 'Estonia', 'Estonia', 'Estonie', '1991-09-17'),
(58, 'Etiopia', 'Ethiopia', 'Éthiopie', '1945-11-13'),
(59, 'Figi', 'Fiji', 'Fidji', '1970-10-13'),
(60, 'Finlandia', 'Finland', 'Finlande', '1955-12-14'),
(61, 'Francia', 'France', 'France', '1945-10-24'),
(62, 'Gabon', 'Gabon', 'Gabon', '1960-09-20'),
(63, 'Gambia', 'Gambia', 'Gambie', '1965-09-21'),
(64, 'Georgia', 'Georgia', 'Géorgie', '1992-07-31'),
(65, 'Germania', 'Germany', 'Allemagne', '1973-09-18'),
(66, 'Ghana', 'Ghana', 'Ghana', '1957-03-08'),
(67, 'Grecia', 'Greece', 'Grèce', '1945-10-25'),
(68, 'Grenada', 'Grenada', 'Grenade', '1974-09-17'),
(69, 'Guatemala', 'Guatemala', 'Guatemala', '1945-11-21'),
(70, 'Guinea', 'Guinea', 'Guineé', '1958-12-12'),
(71, 'Guinea-Bissau', 'Guinea-Bissau', 'Guinée-Bissau', '1974-09-17'),
(72, 'Guyana', 'Guyana', 'Guyana', '1966-09-20'),
(73, 'Haiti', 'Haiti', 'Haïti', '1945-10-24'),
(74, 'Honduras', 'Honduras', 'Honduras', '1945-12-17'),
(75, 'Ungheria', 'Hungary', 'Hongrie', '1955-12-14'),
(76, 'Islanda', 'Iceland', 'Islande', '1946-11-19'),
(77, 'India', 'India', 'Inde', '1945-10-30'),
(78, 'Indonesia', 'Indonesia', 'Indonésie', '1950-09-28'),
(79, 'Iran', 'Iran (Islamic Republic of)', 'Iran', '1945-10-24'),
(80, 'Iraq', 'Iraq', 'Iraq', '1945-12-21'),
(81, 'Irlanda', 'Ireland', 'Irlande', '1955-12-14'),
(82, 'Israele', 'Israel', 'Israël', '1949-05-11'),
(83, 'Italia', 'Italy', 'Italie', '1955-12-14'),
(84, 'Giamaica', 'Jamaica', 'Jamaïque', '1962-09-18'),
(85, 'Giappone', 'Japan', 'Japon', '1956-12-18'),
(86, 'Giordania', 'Jordan', 'Jordanie', '1955-12-14'),
(87, 'Kazakistan', 'Kazakhstan', 'Kazakhstan', '1992-03-02'),
(88, 'Kenya', 'Kenya', 'Kenya', '1963-12-16'),
(89, 'Kiribati', 'Kiribati', 'Kiribati', '1999-09-14'),
(90, 'Kuwait', 'Kuwait', 'Koweït', '1963-05-14'),
(91, 'Kirghizistan', 'Kyrgyzstan', 'Kirghizistan', '1992-03-02'),
(92, 'Repubblica Popolare Democratica del Laos', 'Lao People''s Democratic Republic', 'République démocratique populaire lao', '1955-12-14'),
(93, 'Lettonia', 'Latvia', 'Lettonie', '1991-09-17'),
(94, 'Libano', 'Lebanon', 'Liban', '1945-10-24'),
(95, 'Lesotho', 'Lesotho', 'Lesotho', '1966-10-17'),
(96, 'Liberia', 'Liberia', 'Libéria', '1945-11-02'),
(97, 'Jam_h_riyya Araba di Libia', 'Libyan Arab Jamahiriya', 'Jamahiriya arabe libyenne', '1955-12-14'),
(98, 'Liechtenstein', 'Liechtenstein', 'Liechtenstein', '1990-09-18'),
(99, 'Lituania', 'Lithuania', 'Lituanie', '1991-09-17'),
(100, 'Lussemburgo', 'Luxembourg', 'Luxembourg', '1945-10-24'),
(101, 'Madagascar', 'Madagascar', 'Madagascar', '1960-09-20'),
(102, 'Malawi', 'Malawi', 'Malawi', '1964-12-01'),
(103, 'Malesia', 'Malaysia', 'Malaisie', '1957-09-17'),
(104, 'Maldive', 'Maldives', 'Maldives', '1965-09-21'),
(105, 'Mali', 'Mali', 'Mali', '1960-09-28'),
(106, 'Malta', 'Malta', 'Malte', '1964-12-01'),
(107, 'Isole Marshall', 'Marshall Islands', 'Îles Marshall', '1991-09-17'),
(108, 'Mauritania', 'Mauritania', 'Mauritanie', '1961-10-27'),
(109, 'Mauritius', 'Mauritius', 'Maurice', '1968-04-24'),
(110, 'Messico', 'Mexico', 'Mexique', '1945-11-07'),
(111, 'Micronesia', 'Micronesia (Federated States of)', 'Micronésie', '1991-09-17'),
(112, 'Monaco', 'Monaco', 'Monaco', '1993-05-28'),
(113, 'Mongolia', 'Mongolia', 'Mongolie', '1961-10-27'),
(114, 'Montenegro', 'Montenegro', 'Montenegro', '2006-06-28'),
(115, 'Marocco', 'Morocco', 'Maroc', '1956-11-12'),
(116, 'Mozambico', 'Mozambique', 'Mozambique', '1975-09-16'),
(117, 'Myanmar', 'Myanmar', 'Myanmar', '1948-04-19'),
(118, 'Namibia', 'Namibia', 'Namibie', '1990-04-23'),
(119, 'Nauru', 'Nauru', 'Nauru', '1999-09-14'),
(120, 'Nepal', 'Nepal', 'Népal', '1955-12-14'),
(121, 'Paesi Bassi', 'Netherlands', 'Pays-Bas', '1945-12-10'),
(122, 'Nuova Zelanda', 'New Zealand', 'Nouvelle-Zélande', '1945-10-24'),
(123, 'Nicaragua', 'Nicaragua', 'Nicaragua', '1945-10-24'),
(124, 'Niger', 'Niger', 'Niger', '1960-09-20'),
(125, 'Nigeria', 'Nigeria', 'Nigéria', '1960-10-07'),
(126, 'Norvegia', 'Norway', 'Norvège', '1945-11-27'),
(127, 'Oman', 'Oman', 'Oman', '1971-10-07'),
(128, 'Pakistan', 'Pakistan', 'Pakistan', '1947-09-30'),
(129, 'Palau', 'Palau', 'Palaos', '1994-12-15'),
(130, 'Panama', 'Panama', 'Panama', '1945-11-13'),
(131, 'Papua Nuova Guinea', 'Papua New Guinea', 'Papouasie-Nouvelle-Guinée', '1975-10-10'),
(132, 'Paraguay', 'Paraguay', 'Paraguay', '1945-10-24'),
(133, 'Peru', 'Peru', 'Pérou', '1945-10-31'),
(134, 'Filippine', 'Philippines', 'Philippines', '1945-10-24'),
(135, 'Polonia', 'Poland', 'Pologne', '1945-10-24'),
(136, 'Portogallo', 'Portugal', 'Portugal', '1955-12-14'),
(137, 'Qatar', 'Qatar', 'Qatar', '1971-09-21'),
(138, 'Repubblica di Corea', 'Republic of Korea', 'République de Corée', '1991-09-17'),
(139, 'Repubblica di Moldova', 'Republic of Moldova', 'République de Moldova', '1992-03-02'),
(140, 'Romania', 'Romania', 'Roumanie', '1955-12-14'),
(141, 'Federazione Russa', 'Russian Federation', 'Fédération de Russie', '1945-10-24'),
(142, 'Ruanda', 'Rwanda', 'Rwanda', '1962-09-18'),
(143, 'Saint Kitts e Nevis', 'Saint Kitts and Nevis', 'Saint-Kitts-et-Nevis', '1983-09-23'),
(144, 'Santa Lucia', 'Saint Lucia', 'Sainte-Lucie', '1979-09-18'),
(145, 'Saint Vincent e le Grenadine', 'Saint Vincent and the Grenadines', 'Saint-Vincent-et-les-Grenadines', '1980-09-16'),
(146, 'Samoa', 'Samoa', 'Samoa', '1976-12-15'),
(147, 'San Marino', 'San Marino', 'Saint-Marin', '1992-03-02'),
(148, 'Sao Tome e Principe', 'Sao Tome and Principe', 'Sao Tomé-et-Principe', '1975-09-16'),
(149, 'Arabia Saudita', 'Saudi Arabia', 'Arabie saoudite', '1945-10-24'),
(150, 'Senegal', 'Senegal', 'Sénégal', '1960-09-28'),
(151, 'Serbia', 'Serbia', 'Serbie', '2000-11-01'),
(152, 'Seychelles', 'Seychelles', 'Seychelles', '1976-09-21'),
(153, 'Sierra Leone', 'Sierra Leone', 'Sierra Leone', '1961-09-27'),
(154, 'Singapore', 'Singapore', 'Singapour', '1965-09-21'),
(155, 'Slovacchia', 'Slovakia', 'Slovaquie', '1993-01-19'),
(156, 'Slovenia', 'Slovenia', 'Slovénie', '1992-05-22'),
(157, 'Isole Salomone', 'Solomon Islands', 'Îles Salomon', '1978-09-19'),
(158, 'Somalia', 'Somalia', 'Somalie', '1960-09-20'),
(159, 'Sud Africa', 'South Africa', 'Afrique du Sud', '1945-11-07'),
(160, 'Spagna', 'Spain', 'Espagne', '1955-12-14'),
(161, 'Sri Lanka', 'Sri Lanka', 'Sri Lanka', '1955-12-14'),
(162, 'Sudan', 'Sudan', 'Soudan', '1956-11-12'),
(163, 'Suriname', 'Suriname', 'Suriname', '1975-12-04'),
(164, 'Swaziland', 'Swaziland', 'Swaziland', '1968-09-24'),
(165, 'Svezia', 'Sweden', 'Suède', '1946-11-19'),
(166, 'Svizzera', 'Switzerland', 'Suisse', '2002-09-10'),
(167, 'Repubblica Araba di Siria', 'Syrian Arab Republic', 'République arabe syrienne', '1945-10-24'),
(168, 'Tagikistan', 'Tajikistan', 'Tadjikistan', '1992-03-02'),
(169, 'Tailandia', 'Thailand', 'Thaïlande', '1946-12-16'),
(170, 'Repubblica di Macedonia', 'The former Yugoslav Republic of Macedonia', 'ex-République yougoslave de Macédoine', '1993-04-08'),
(171, 'Timor Est', 'Timor-Leste', 'Timor oriental', '2002-09-27'),
(172, 'Togo', 'Togo', 'Togo', '1960-09-20'),
(173, 'Tonga', 'Tonga', 'Tonga', '1999-09-14'),
(174, 'Trinidad e Tobago', 'Trinidad and Tobago', 'Trinité-et-Tobago', '1962-09-18'),
(175, 'Tunisia', 'Tunisia', 'Tunisie', '1956-11-12'),
(176, 'Turchia', 'Turkey', 'Turquie', '1945-10-24'),
(177, 'Turkmenistan', 'Turkmenistan', 'Turkménistan', '1992-03-02'),
(178, 'Tuvalu', 'Tuvalu', 'Tuvalu', '2000-09-05'),
(179, 'Uganda', 'Uganda', 'Ouganda', '1962-10-25'),
(180, 'Ucraina', 'Ukraine', 'Ukraine', '1945-10-24'),
(181, 'Emirati Arabi Uniti', 'United Arab Emirates', 'Emirats arabes unis', '1971-12-09'),
(182, 'Regno Unito', 'United Kingdom of Great Britain and Northern Ireland', 'Royaume-Uni', '1945-10-24'),
(183, 'Tanzania', 'United Republic of Tanzania', 'Tanzanie', '1961-12-14'),
(184, 'Stati Uniti d''America', 'United States of America', 'Etats-Unis', '1945-10-24'),
(185, 'Uruguay', 'Uruguay', 'Uruguay', '1945-12-18'),
(186, 'Uzbekistan', 'Uzbekistan', 'Ouzbékistan', '1992-03-02'),
(187, 'Vanuatu', 'Vanuatu', 'Vanuatu', '1981-09-15'),
(188, 'Venezuela', 'Venezuela (Bolivarian Republic of)', 'Vénézuela', '1945-11-15'),
(189, 'Viet Nam', 'Viet Nam', 'Viet Nam', '1977-09-20'),
(190, 'Yemen', 'Yemen', 'Yémen', '1947-09-30'),
(191, 'Zambia', 'Zambia', 'Zambie', '1964-12-01'),
(192, 'Zimbabwe', 'Zimbabwe', 'Zimbabwe', '1980-08-25'),
(193, 'Repubblica di Cina (Taiwan)', 'Republic of China (Taiwan)', 'République de Chine (Taiwan)', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_category`
--

CREATE TABLE IF NOT EXISTS `page_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `description` text,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `page_comment`
--

CREATE TABLE IF NOT EXISTS `page_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entry` int(11) NOT NULL,
  `datetime` datetime NOT NULL,
  `author` varchar(200) NOT NULL,
  `email` varchar(200) NOT NULL,
  `web` varchar(200) DEFAULT NULL,
  `text` text NOT NULL,
  `notification` tinyint(1) NOT NULL,
  `reply` int(11) DEFAULT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `page_entry`
--

CREATE TABLE IF NOT EXISTS `page_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `author` int(11) NOT NULL,
  `creation_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `image` varchar(200) DEFAULT NULL,
  `url_image` varchar(200) DEFAULT NULL,
  `text` text NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `enable_comments` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0',
  `social` tinyint(1) NOT NULL DEFAULT '0',
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `users` varchar(255) DEFAULT NULL,
  `view_last_edit_date` tinyint(1) NOT NULL DEFAULT '0',
  `users_edit` VARCHAR(255) NULL,
  `read` int(11) NOT NULL DEFAULT '0',
  `tpl_code` text,
  `box_tpl_code` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `page_entry`
--

INSERT INTO `page_entry` (`id`, `category_id`, `author`, `creation_date`, `last_edit_date`, `title`, `slug`, `image`, `url_image`, `text`, `tags`, `enable_comments`, `published`, `social`, `private`, `users`, `view_last_edit_date`, `users_edit`, `read`, `tpl_code`, `box_tpl_code`) VALUES
(1, 0, 1, '2011-10-20 12:02:48', '2019-01-08 16:16:49', 'About gino', 'about-gino', '', '', '<p>gino CMS è un framework <a href=\"http://www.opensource.org/docs/osd\" rel=\"external\">open source</a> sviluppato da <a href=\"http://www.otto.to.it\" rel=\"external\">Otto</a> e distribuito con licenza <a href=\"http://www.opensource.org/licenses/MIT\" rel=\"external\">MIT</a> (MIT).</p>\r\n\r\n<p><img alt=\"OSI approved license\" src=\"contents/attachment/gino/OSI_logo.jpg\" style=\"float:left; margin-left:10px; margin-right:10px\" />È un <strong>CMS</strong>, acronimo di <em>Content Management System</em>, ovvero un sistema di gestione dei contenuti web, creato appositamente per facilitarne l\'organizzazione e la pubblicazione.</p>\r\n\r\n<p>Le funzionalità di gino possono essere ampliate utilizzando i <strong>moduli aggiuntivi</strong> disponibili, oppure creando direttamente applicazioni con l\'ausilio dell\'applicazione \"Creazione App\" presente nell\'area amministrativa.</p>\r\n\r\n<p>Questi moduli vengono caricati e aggiornati facilmente utilizzando l\'applicazione \"Moduli di sistema\". Per un elenco dei moduli disponibili fate riferimento alla pagina sul <a href=\"http://gino.otto.to.it/\" rel=\"external\" title=\"Il link apre una nuova finestra\">sito ufficiale di gino</a>.</p>\r\n\r\n<p>In gino viene inoltre utilizzato <a href=\"https://getcomposer.org/\" target=\"_blank\">Composer</a> per gestire alcune librerie esterne. Composer è un Dependency Manager per PHP, ovvero è uno strumento per la gestione delle dipendenze in PHP; in pratica permette di dichiarare le librerie da cui dipende il progetto gestendole per noi.</p>\r\n\r\n<p>Per utilizzare correttamente Composer è consigliato fare riferimento al <a href=\"https://github.com/otto-torino/gino/wiki\" rel=\"external\">wiki</a> di gino e alla sua documentazione ufficiale; nella pagina <a href=\"https://getcomposer.org/doc/00-intro.md\" target=\"_blank\">Getting Started</a> del progetto si trovano tutte le indicazioni sulla procedura di installazione.<br />\r\nOccorre inoltre tenere presente che conviene installare col Composer soltanto i pacchetti esterni necessari, in quanto lo spazio richiesto potrebbe essere importante.</p>', '', 0, 1, 0, 0, NULL, 0, NULL, 0, '', ''),
(2, NULL, 1, '2011-11-01 09:59:14', '2018-01-15 14:00:19', 'Documentazione', 'documentazione', NULL, NULL, '<p>La documentazione e le reference di tutti i file sono ospitate su <strong>github</strong> sotto forma di <a href="https://github.com/otto-torino/gino/wiki" rel="external">wiki</a> che copre essenzialmente gli aspetti di sviluppo di gino.</p>\r\n\r\n<p><img alt="github logo" src="contents/attachment/gino/github.jpg" style="float:left; margin-left:10px; margin-right:10px" />Per una documentazione più ampia, comprendente tutorial e how-to, potete fare riferimento alla pagina dedicata sul <a href="http://gino.otto.to.it" rel="external">sito ufficiale di gino</a>.</p>', '', 0, 1, 0, 0, NULL, 0, NULL, 0, NULL, NULL),
(4, NULL, 1, '2015-05-11 15:05:21', '2015-05-12 12:40:08', 'Privacy - Cookie', 'privacy-cookie', NULL, NULL, '<p>Con riferimento all''art. 122 secondo comma del D.lgs. 196/2003 e a seguito delle modalità semplificate per l''informativa e l''acquisizione del consenso per l''uso dei cookie pubblicata sulla Gazzetta Ufficiale n.126 del 3 giugno 2014 e relativo registro dei provvedimenti n.229 dell''8 maggio 2014, si dichiara che:</p>\r\n\r\n<p>1 - Il sito web <b>NOMESITO</b> (più avanti "Sito") utilizza i cookie per offrire i propri servizi agli Utenti durante la consultazione delle sue pagine. Titolare del trattamento dei dati è <b>NOMEAZIENDA</b> (informazioni di contatto in fondo ad ogni pagina).</p>\r\n\r\n<p>2 - L''informativa è valida solo per il Sito e per gli eventuali domini di secondo e terzo livello correlati, e non per altri siti consultabili tramite link.</p>\r\n\r\n<p>3 - Se l''utente non acconsente all''utilizzo dei cookie, non accettando in maniera esplicita i cookie di navigazione, o mediante specifiche configurazioni del browser utilizzato o dei relativi programmi informatici utilizzati per navigare le pagine che compongono il Sito, o modificando le impostazioni nell''uso dei servizi di terze parti utilizzati all''interno del Sito, l''esperienza di navigazione potrebbe essere penalizzata, ed alcune funzionalità potrebbero non essere disponibili.</p>\r\n\r\n<p>4 - Il Sito NON fa uso diretto (first-part cookie) di cookie di PROFILAZIONE degli utenti.</p>\r\n\r\n<p>5 - Il Sito NON consente l''invio di cookie di PROFILAZIONE di TERZE PARTI (third-part cookie).</p>\r\n\r\n<p>6 - Il Sito fa uso diretto esclusivamente di cookie TECNICI per salvare i parametri di sessione e agevolare quindi la navigazione agli utenti.</p>\r\n\r\n<p>7 - Il Sito potrà fare uso di cookie TECNICI di terze parti (non tutti i servizi sono per forza attivi):<br />\r\n<br />\r\n<b>Google</b><br />\r\nIl servizio Google Analytics viene utilizzato per raccogliere statistiche ANONIME di accesso, monitorare e analizzare i dati di traffico.<br />\r\nI servizi Google Maps e YouTube sono utilizzati per includere contenuti multimediali all''interno del sito.<br />\r\n<a href="http://www.google.com/policies/technologies/types/" rel="external">Informazioni generali</a> | <a href="http://www.google.com/policies/privacy/" rel="external">Privacy Policy</a> | <a href="http://tools.google.com/dlpage/gaoptout?hl=it" rel="external">Opt Out</a></p>\r\n\r\n<p><b>ShareThis</b><br />\r\nIl servizio ShareThis viene utilizzato per facilitare la condivisione dei contenuti sulle più comuni piattaforme social.<br />\r\n<a href="http://www.sharethis.com/legal/privacy/" rel="external">Privacy Policy</a> | <a href="http://www.sharethis.com/legal/privacy/" rel="external">Opt Out</a></p>\r\n\r\n<p><b>Disqus</b><br />\r\nIl servizio viene utilizzato per facilitare e migliorare la gestione dei commenti ai contenuti.<br />\r\n<a href="https://help.disqus.com/customer/portal/articles/466259-privacy-policy" rel="external">Privacy Policy</a> | <a href="https://help.disqus.com/customer/portal/articles/1657951" rel="external">Opt Out</a></p>\r\n\r\n<p><b>Vimeo</b><br />\r\nIl popolare servizio di streaming video utilizza i cookie per ottimizzare la fruizione dei suoi servizi, e il alcuni casi il Sito può includere video Vimeo.<br />\r\n<a href="https://vimeo.com/cookie_policy" rel="external">Cookie Policy</a></p>\r\n\r\n<p><b>Bottoni Social</b><br />\r\nI bottoni social sono bottoni che permettono di rendere più immediata ed agevole la condivisione dei contenuti sulle più comuni piattaforme social. Qui di seguito i dettagli dei principali servizi:</p>\r\n\r\n<p><b>Pulsante +1 e widget sociali di Google+</b> (Google Inc.)<br />\r\nIl pulsante +1 e i widget sociali di Google+ (tra cui i commenti) sono servizi di interazione con il social network Google+, forniti da Google Inc.<br />\r\nDati personali raccolti: Cookie e Dati di utilizzo.<br />\r\nLuogo del trattamento : USA - <a href="http://www.google.com/intl/it/policies/privacy/" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Pulsante "Mi Piace" e widget sociali di Facebook</b> (Facebook, Inc.)<br />\r\nIl pulsante "Mi Piace" e i widget sociali di Facebook sono servizi di interazione con il social network Facebook, forniti da Facebook, Inc.<br />\r\nDati personali raccolti: Cookie e Dati di utilizzo.<br />\r\nLuogo del trattamento : USA - <a href="http://www.facebook.com/privacy/explanation.php" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Pulsante Tweet e widget sociali di Twitter</b> (Twitter, Inc.)<br />\r\nIl pulsante Tweet e i widget sociali di Twitter sono servizi di interazione con il social network Twitter, forniti da Twitter, Inc.<br />\r\nDati personali raccolti: Cookie e Dati di utilizzo.<br />\r\nLuogo del trattamento : USA - <a href="http://twitter.com/privacy" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Pulsante e widget sociali di Linkedin</b> (Linkedin Corp.)<br />\r\nIl pulsante e i widget sociali di Linkedin sono servizi di interazione con il social network Linkedin, forniti da Linkedin Inc.<br />\r\nDati personali raccolti: Cookie e Dati di navigazione ed utilizzo.<br />\r\nLuogo del Trattamento: USA - <a href="http://www.linkedin.com/static?key=privacy_policy&trk=hb_ft_priv" rel="external">Privacy Policy</a></p>\r\n\r\n<p><b>Cookie Script</b><br />\r\nIl Sito utilizza il servizio Cookie Script per l''accettazione dell''utilizzo dei cookies. Se acconsenti all''utilizzo dei cookies, un ulteriore cookie tecnico di nome cookiescriptaccept verrà scritto per ricordare in futuro la tua scelta.<br />\r\n<a href="https://cookie-script.com/privacy-policy-and-disclaimer.html" rel="external">Privacy Policy</a></p>\r\n\r\n<p>8 - Questa pagina è raggiungibile mediante un link presente in tutte le pagine del Sito.</p>\r\n\r\n<p>9 - Negando il consenso all''utilizzo dei cookie, nessun cookie verrà scritto sul dispositivo dell''utente, eccetto il cookie tecnico di sessione. Sarà ancora possibile navigare il Sito, ma alcune parti di esso potrebbero non funzionare correttamente.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>Ma... cosa sono i cookie?</b></p>\r\n\r\n<p>I cookie sono file o pacchetti di dati che possono venire salvati sul computer dell''utente (o altro dispositivo abilitato alla navigazione su internet, per esempio smartphone o tablet) quando visita un sito web. Di solito un cookie contiene il nome del sito internet dal quale il cookie stesso proviene, la durata del cookie (ovvero l''indicazione del tempo per il quale il cookie rimarrà memorizzato sul dispositivo), ed un contenuto (numero, stringa, etc.), che gli permette di svolgere la sua funzione.<br />\r\nPer maggiori informazioni visita il sito in lingua inglese <a href="http://aboutcookies.org/." rel="external">aboutcookies.org</a>.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>Per cosa si usano i cookie?</b></p>\r\n\r\n<p>Si utilizzano i cookie per rendere la navigazione più semplice e per meglio adattare il sito web ai bisogni dell''utente. I cookie possono anche venire usati per aiutare a velocizzare le future esperienze ed attività dell''utente su altri siti web, e si usano per compilare statistiche anonime aggregate che consentono di capire come gli utenti usano i siti in modo da aiutare a migliorare la struttura ed i contenuti di questi siti.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>I diversi tipi di cookie</b></p>\r\n\r\n<p><b>Cookies Tecnici</b>: sono i cookie che servono a effettuare la navigazione o a fornire un servizio richiesto dall''utente. Non vengono utilizzati per scopi ulteriori e sono normalmente installati direttamente dal gestore del sito web che si sta novigando. Senza il ricorso a tali cookie, alcune operazioni non potrebbero essere compiute o sarebbero più complesse e/o meno sicure, (ad esempio i cookie che consentono di effettuare e mantenere l''identificazione dell''utente nell''ambito della sessione).</p>\r\n\r\n<p><b>Cookies di Profilazione</b>: sono i cookie utilizzati per tracciare la navigazione dell''utente in rete e creare profili sui suoi gusti, abitudini, scelte, ecc. Con questi cookie possono essere trasmessi al terminale dell''utente messaggi pubblicitari in linea con le preferenze già manifestate dallo stesso utente nella navigazione online.</p>\r\n\r\n<p><b>Cookies di prima parte</b> (first-part cookie) sono i cookie generati e utilizzati direttamente dal soggetto gestore del sito web sul quale l''utente sta navigando.</p>\r\n\r\n<p><b>Cookies di terza parte</b> (third-part cookie), sono i cookie generati e gestiti da soggetti diversi dal gestore del sito web sul quale l''utente sta navigando (in forza, di regola, di un contratto tra il titolare del sito web e la terza parte)</p>\r\n\r\n<p><b>Cookies di Sessione</b> e <b>Cookies Persistenti</b>:<br />\r\nmentre la differenza tra un cookie di prima parte e un cookie di terzi riguarda il soggetto che controlla l''invio iniziale del cookie sul tuo dispositivo, la differenza tra un cookie di sessione e un cookie persistente riguarda il diverso lasso di tempo per cui un cookie opera. I cookie di sessione sono cookie che tipicamente durano finchè chiudi il tuo internet browser. Quando finisci la tua sessione browser, il cookie scade. I cookies persistenti, come lo stesso nome indica, sono cookie costanti e continuano ad operare dopo che hai chiuso il tuo browser.</p>\r\n\r\n<p> </p>\r\n\r\n<p><b>Come posso controllare le gestione dei cookie del mio browser?</b></p>\r\n\r\n<p>Tutti i moderni browser offrono la possibilita di controllare le impostazioni di privacy, anche per quello che riguarda l''uso dei cookie. In particolare l''utente potrà intervenire sul comportamento generale del browser nei confronti dei cookie (ad esempio instruendolo a NON accettarli in futuro), visualizzare e/o cancellare i cookie già installati.<br />\r\n<br />\r\nRiportiamo qui di seguito le procedure per accedere a queste impostazioni per i browser più utilizzati:<br />\r\n<br />\r\n<a href="https://support.google.com/chrome/answer/95647?hl=it" rel="external">Chrome</a></p>\r\n\r\n<p><a href=\"https://support.microsoft.com/en-us/help/17442/windows-internet-explorer-delete-manage-cookies\" rel=\"external\">Internet Explorer</a></p>\r\n\r\n<p><a href="https://support.mozilla.org/it/kb/Gestione%20dei%20cookie" rel="external">Firefox</a></p>\r\n\r\n<p><a href="http://www.opera.com/help/tutorials/security/privacy/" rel="external">Opera</a></p>\r\n\r\n<p><a href=\"https://support.apple.com/kb/PH21411?viewlocale=en_US&locale=en_US\" rel=\"external\">Safari</a></p>\r\n\r\n<p><a href="https://support.apple.com/en-us/HT201265" rel="external">Safari mobile</a></p>', NULL, 0, 1, 0, 0, NULL, 0, NULL, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `page_opt`
--

CREATE TABLE IF NOT EXISTS `page_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `last_title` VARCHAR(200) NOT NULL, 
  `last_number` TINYINT(2) NOT NULL, 
  `last_tpl_code` TEXT NOT NULL,
  `showcase_title` varchar(200) NOT NULL,
  `showcase_number` smallint(3) NOT NULL,
  `showcase_auto_start` tinyint(1) NOT NULL,
  `showcase_auto_interval` int(5) NOT NULL,
  `showcase_tpl_code` text NOT NULL,
  `entry_tpl_code` text NOT NULL,
  `box_tpl_code` text NOT NULL,
  `comment_moderation` tinyint(1) NOT NULL,
  `comment_notification` tinyint(1) NOT NULL,
  `newsletter_entries_number` smallint(2) NOT NULL,
  `newsletter_tpl_code` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `page_opt`
--

INSERT INTO `page_opt` (`id`, `instance`, `last_title`, `last_number`, `last_tpl_code`, `showcase_title`, `showcase_number`, `showcase_auto_start`, `showcase_auto_interval`, `showcase_tpl_code`, `entry_tpl_code`, `box_tpl_code`, `comment_moderation`, `comment_notification`, `newsletter_entries_number`, `newsletter_tpl_code`) VALUES
(1, 0, 'Pagine recenti', 10, '<article>\r\n<h1>{{ title|link }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text|chars:300 }}\r\n<div class=\"null\"></div>\r\n</article>', 'In evidenza', 3, 1, 5000, '<article>\r\n<h1>{{ title }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text }}\r\n<div class="null"></div>\r\n</article>', '<h1>{{ title }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text }}\r\n<div class="null"></div>', '<h1>{{ title }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text }}\r\n<div class="null"></div>', 0, 1, 5, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `php_module`
--

CREATE TABLE IF NOT EXISTS `php_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `php_module`
--

INSERT INTO `php_module` (`id`, `instance`, `content`) VALUES
(1, 6, '$lng = (isset($_SESSION[''lng''])) ? $_SESSION[''lng'']:''it_IT'';\r\n$access = new \\Gino\\Access();\r\n$registry = \\Gino\\Registry::instance();\r\n\r\n$buffer = "<div class=\\"top-bar\\">";\r\n$buffer .= "<div class=\\"left\\">";\r\nif($registry->sysconf->multi_language) {\r\n  $query = "SELECT id, label, language_code, country_code FROM language WHERE active=''1'' ORDER BY language DESC";\r\n  $a = $this->_db->execCustomQuery($query);\r\n  $lng_buffer = array();\r\n  foreach($a as $b) {\r\n    if(isset($_SESSION[''lng''])) {\r\n      $selected = $_SESSION[''lng''] == $b[''language_code''].''_''.$b[''country_code''] ? true : false;\r\n    }\r\n    else {\r\n      $dft_lang_query = "SELECT dft_language FROM sys_conf WHERE id=''1''";\r\n      $c = $this->_db->execCustomQuery($dft_lang_query);\r\n      $dft_lang = $c[0][''dft_language''];\r\n      \r\n      $selected = $b[''id''] == $dft_lang ? true : false;\r\n    }\r\n    if(!$selected) {\r\n      $lng_buffer[]  =  "<a href=\\"index.php?lng=".$b[''language_code''].''_''.$b[''country_code'']."\\">".\\Gino\\htmlChars($b[''label''])."</a>";\r\n    }\r\n    else {\r\n      $lng_buffer[]  =  "<a class=\\"selected\\">".\\Gino\\htmlChars($b[''label''])."</a>";\r\n    }\r\n  }\r\n  \r\n  $buffer .= implode("", $lng_buffer); \r\n}\r\n$buffer .= "</div>";\r\n$buffer .= "<div class=\\"right\\">";\r\nif(!isset($_SESSION[''user_id''])) {\r\n    $buffer .= "<span class=\\"link\\" onclick=\\"login_toggle.toggle();\\">"._("Area riservata")."</span>";\r\n    $buffer .= "<div id=\\"topbar-login\\" style=\\"display:none;\\">";\r\n    $buffer .= "<div>";\r\n    $buffer .= "<form method=\\"post\\" action=\\"index.php\\" style=\\"float:right\\">";\r\n    $buffer .= "<input type=\\"hidden\\" name=\\"action\\" value=\\"auth\\" />";\r\n    $buffer .= "<div class=\\"form-row\\">";\r\n    $buffer .= "<label>User</label>";\r\n    $buffer .= "<input type=\\"text\\" name=\\"user\\" required />";\r\n    $buffer .= "</div>";\r\n    $buffer .= "<div class=\\"form-row\\">";\r\n    $buffer .= "<label>Password</label>";\r\n    $buffer .= "<input type=\\"password\\" name=\\"pwd\\" required />";\r\n    $buffer .= "</div>";\r\n    $buffer .= "<div class=\\"form-row\\">";\r\n    $buffer .= "<label></label>";\r\n    $buffer .= "<input type=\\"submit\\" class=\\"generic\\" value=\\"login\\" />";\r\n    $buffer .= "</div>";\r\n    $buffer .= "</form>";\r\n    $buffer .= "<div class=\\"null\\"></div>";\r\n    $buffer .= "</div>";\r\n    $buffer .= "</div>";\r\n    $buffer .= "<script>var login_toggle = new Fx.Reveal(''topbar-login'');</script>";\r\n}\r\nelse {\r\n    $request = \\Gino\\Http\\Request::instance();\r\n	if($request->user->hasPerm(''core'', ''is_staff'')) {\r\n        $buffer .= "<a href=\\"admin\\">"._("Amministrazione")."</a>";\r\n	}\r\n    $query = "SELECT CONCAT(firstname, '' '', lastname) AS name FROM user_app WHERE user_id=''".$_SESSION[''user_id'']."''";\r\n    $a = $this->_db->execCustomQuery($query);\r\n    $username = $a>0 ? $a[0][''name'']:null;\r\n    $buffer .= "<a href=\\"index.php?evt[user-userCard]\\"><span title=\\""._("Profilo utente")."\\" class=\\"tooltip\\">".$username."</span></a>";\r\n    $buffer .= "<a href=\\"index.php?action=logout\\">"._("Logout")."</a>";\r\n    $buffer .= "<div class=\\"null\\"></div>";\r\n}\r\n$buffer .= "</div>";\r\n$buffer .= "<div class=\\"clear\\"></div>";\r\n$buffer .= "</div>";'),
(2, 9, '$buffer = "<div class=\\"top-bar\\">";\r\n\r\n$index = new \\Gino\\App\\Index\\index();\r\n\r\n$sysMdls = $index->sysModulesManageArray();\r\n$mdls = $index->modulesManageArray();\r\n \r\nif(count($sysMdls)) {	\r\n  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";\r\n  $buffer .= "<select name=''sysmdl_menu'' onchange=\\"$onchange\\">";\r\n  $buffer .= "<option value=\\"\\">"._("Sistema")."</option>";\r\n  foreach($sysMdls as $sm) { \r\n    $buffer .= "<option value=\\"".$sm[''name'']."-manage".ucfirst($sm[''name''])."\\">".\\Gino\\htmlChars($sm[''label''])."</option>";\r\n  }\r\n  $buffer .= "</select> ";\r\n}\r\n				\r\nif(count($mdls)) {\r\n  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";\r\n  $buffer .= "<select name=''mdl_menu'' onchange=\\"$onchange\\">";	\r\n  $buffer .= "<option value=\\"\\">"._("Moduli")."</option>";\r\n  foreach($mdls as $m) {\r\n    $buffer .= "<option value=\\"".$m[''name'']."-manageDoc\\">".\\Gino\\htmlChars($m[''label''])."</option>";\r\n  }	\r\n  $buffer .= "</select>";\r\n}\r\n\r\n$buffer .= "</div>";');

-- --------------------------------------------------------

--
-- Table structure for table `php_module_opt`
--

CREATE TABLE IF NOT EXISTS `php_module_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `title_vis` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `search_site_opt`
--

CREATE TABLE IF NOT EXISTS `search_site_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `sys_mdl` varchar(256) NOT NULL,
  `inst_mdl` varchar(256) NOT NULL,
  `view_choices` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_conf`
--

CREATE TABLE IF NOT EXISTS `sys_conf` (
  `id` smallint(2) NOT NULL AUTO_INCREMENT,
  `multi_language` tinyint(1) NOT NULL,
  `dft_language` smallint(2) NOT NULL,
  `log_access` tinyint(1) NOT NULL,
  `head_description` text NOT NULL,
  `head_keywords` varchar(255) DEFAULT NULL,
  `head_title` varchar(255) NOT NULL,
  `google_analytics` varchar(20) DEFAULT NULL,
  `captcha_public` varchar(64) DEFAULT NULL,
  `captcha_private` varchar(64) DEFAULT NULL,
  `sharethis_public_key` varchar(64) DEFAULT NULL,
  `disqus_shortname` varchar(64) DEFAULT NULL,
  `email_admin` varchar(128) NOT NULL,
  `email_from_app` varchar(100) DEFAULT NULL,
  `mobile` tinyint(1) NOT NULL DEFAULT '0',
  `password_crypt` enum('none','sha1','md5') NOT NULL DEFAULT 'md5',
  `enable_cache` tinyint(1) NOT NULL, 
  `query_cache` tinyint(1) NOT NULL DEFAULT '0', 
  `query_cache_time` smallint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `sys_conf`
--

INSERT INTO `sys_conf` (`id`, `multi_language`, `dft_language`, `log_access`, `head_description`, `head_keywords`, `head_title`, `google_analytics`, `captcha_public`, `captcha_private`, `email_admin`, `email_from_app`, `mobile`, `password_crypt`, `enable_cache`, `query_cache`, `query_cache_time`) VALUES
(1, 1, 1, 1, 'Content Management System', '', 'gino CMS', '', '', '', 'kkk@otto.to.it', 'no-reply@otto.to.it', 0, 'md5', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sys_gimage`
--

CREATE TABLE IF NOT EXISTS `sys_gimage` (
`id` int(11) NOT NULL,
  `key` varchar(32) NOT NULL,
  `path` varchar(255) NOT NULL,
  `width` int(8) NOT NULL,
  `height` int(8) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sys_gimage`
--
ALTER TABLE `sys_gimage`
 ADD PRIMARY KEY (`id`), ADD KEY `key` (`key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sys_gimage`
--
ALTER TABLE `sys_gimage`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

--
-- Table structure for table `sys_graphics`
--

CREATE TABLE IF NOT EXISTS `sys_graphics` (
  `id` smallint(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `image` varchar(128) DEFAULT NULL,
  `html` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=3 ;

--
-- Dumping data for table `sys_graphics`
--

INSERT INTO `sys_graphics` (`id`, `name`, `description`, `type`, `image`, `html`) VALUES
(1, 'header', 'Header', 2, '', ''),
(2, 'footer', 'Footer', 2, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `sys_layout_css`
--

CREATE TABLE IF NOT EXISTS `sys_layout_css` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(200) NOT NULL,
  `label` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `sys_layout_css`
--

INSERT INTO `sys_layout_css` (`id`, `filename`, `label`, `description`) VALUES
(1, 'mobile.css', 'Css per la visione mobile', 'Contiene regole per i dispositivi mobile'),
(2, 'admin.css', 'Css area amministrativa', 'Contiene regole per l''area amministrativa'),
(3, 'gino-blocks-tpl.css', 'Css per template a blocchi', 'Contiene regole css per il layout a blocchi di gino');

-- --------------------------------------------------------

--
-- Table structure for table `sys_layout_skin`
--

CREATE TABLE IF NOT EXISTS `sys_layout_skin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(200) NOT NULL,
  `session` varchar(128) DEFAULT NULL,
  `rexp` varchar(200) DEFAULT NULL,
  `urls` varchar(200) DEFAULT NULL,
  `highest` tinyint(1) NOT NULL DEFAULT '0',
  `template` varchar(200) NOT NULL,
  `css` int(11) DEFAULT NULL,
  `priority` int(11) NOT NULL,
  `auth` enum('yes','no','') NOT NULL,
  `cache` bigint(16) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `sys_layout_skin`
--

INSERT INTO `sys_layout_skin` (`id`, `label`, `session`, `rexp`, `urls`, `highest`, `template`, `css`, `priority`, `auth`, `cache`) VALUES
(1, 'Home Pubblica', NULL, '#(index.php(\\?evt\\[index.index_page\\])?[^\\[\\]]*)?$#', NULL, 0, '2', 0, 9, 'no', 0),
(2, 'Pagine Pubbliche', NULL, '#evt\\[(?!index)#', NULL, 0, '3', 0, 7, 'no', 0),
(3, 'Home Amministrazione', NULL, NULL, 'index.php?evt[index.admin_page]', 0, '4', 0, 6, 'yes', 0),
(4, 'Pagine Amministrazione', NULL, '#evt\\[\\w+.((manage)|(wrapper))\\w*\\]#', NULL, 0, '5', NULL, 5, 'yes', 0),
(5, 'Pagina Autenticazione', NULL, NULL, 'index.php?evt[auth.login]', 0, '3', 0, 3, 'no', 0),
(6, 'Default', NULL, '#^.*$#', NULL, 0, '1', NULL, 11, '', 0),
(7, 'Pagine Private', NULL, '#evt\\[(?!index)#', NULL, 0, '3', 0, 8, 'yes', 0),
(8, 'Home Privata', NULL, '#(index.php(\\?evt\\[index.index_page\\])?[^\\[\\]]*)?$#', NULL, 0, '2', 0, 10, 'yes', 0),
(9, 'Pagine Mobile', 'L_mobile=1', '#.*#', NULL, 0, '8', 1, 2, '', 0),
(10, 'Home Mobile', 'L_mobile=1', '#^index.php$#', NULL, 0, '7', 1, 1, '', 0),
(11, '_popup', NULL, '#&_popup=1#', NULL, 0, '6', 2, 4, 'yes', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sys_layout_tpl`
--

CREATE TABLE IF NOT EXISTS `sys_layout_tpl` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(200) NOT NULL,
  `label` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `free` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `sys_layout_tpl`
--

INSERT INTO `sys_layout_tpl` (`id`, `filename`, `label`, `description`, `free`) VALUES
(1, 'default.tpl', 'Default', 'Template di default a blocchi', 0),
(2, 'home.php', 'Home', 'Template home page', 1),
(3, 'page.php', 'Pagine', 'Template pagine interne', 1),
(4, 'admin_home.php', 'Home admin', 'Template home area amministrativa', 1),
(5, 'admin_page.php', 'Pagine admin', 'Template pagine area amministrativa', 1),
(6, '_popup.php', '_popup', 'Template per l''inserimento di foreign o m2m contestuali', 1),
(7, 'home_mobile.php', 'Home mobile', 'Template home page dispositivi mobile', 1),
(8, 'pages_mobile.php', 'Pagine mobile', 'Template pagine interne dispositivi mobile', 1);

-- --------------------------------------------------------

--
-- Table structure for table `sys_layout_tpl_block`
--

CREATE TABLE IF NOT EXISTS `sys_layout_tpl_block` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tpl` int(4) NOT NULL,
  `position` smallint(2) NOT NULL,
  `width` int(4) NOT NULL,
  `um` tinyint(1) NOT NULL,
  `align` tinyint(1) NOT NULL,
  `rows` smallint(2) NOT NULL,
  `cols` smallint(2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `sys_layout_tpl_block`
--

INSERT INTO `sys_layout_tpl_block` (`id`, `tpl`, `position`, `width`, `um`, `align`, `rows`, `cols`) VALUES
(1, 1, 1, 0, 0, 0, 1, 1),
(2, 1, 2, 0, 0, 0, 1, 1),
(3, 1, 3, 960, 1, 2, 1, 2),
(4, 1, 4, 0, 0, 0, 1, 1),
(5, 1, 5, 960, 1, 2, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `sys_log_access`
--

CREATE TABLE IF NOT EXISTS `sys_log_access` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_opt`
--

CREATE TABLE IF NOT EXISTS `sys_menu_opt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `cache` bigint(16) DEFAULT '0',
  `view_admin_voice` tinyint(1) NOT NULL DEFAULT '0',
  `view_logout_voice` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `sys_menu_opt`
--

INSERT INTO `sys_menu_opt` (`id`, `instance`, `title`, `cache`, `view_admin_voice`, `view_logout_voice`) VALUES
(1, 4, 'Menu principale', 0, 0, 1),
(2, 5, 'Menu amministrazione', 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sys_menu_voices`
--

CREATE TABLE IF NOT EXISTS `sys_menu_voices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `instance` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `url` varchar(200) NOT NULL,
  `type` enum('int','ext') NOT NULL,
  `order_list` smallint(3) NOT NULL,
  `perms` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `sys_menu_voices`
--

INSERT INTO `sys_menu_voices` (`id`, `instance`, `parent`, `label`, `url`, `type`, `order_list`, `perms`) VALUES
(1, 4, 0, 'Documentazione', 'page/view/documentazione', 'int', 2, '');

-- --------------------------------------------------------

--
-- Table structure for table `sys_module`
--

CREATE TABLE IF NOT EXISTS `sys_module` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `module_app` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dumping data for table `sys_module`
--

INSERT INTO `sys_module` (`id`, `label`, `name`, `module_app`, `active`, `description`) VALUES
(4, 'Menu principale', 'mainMenu', 10, 1, 'Menu principale'),
(5, 'Menu amministrazione', 'menu_admin', 10, 1, 'Menu area amministrativa'),
(6, 'Top Bar', 'topbar', 14, 1, 'Barra superiore con scelta lingua ed autenticazione'),
(9, 'Top Bar Admin', 'topbaradmin', 14, 1, 'Barra superiore con link diretto all''amministrazione dei singoli moduli');

-- --------------------------------------------------------

--
-- Table structure for table `sys_module_app`
--

CREATE TABLE IF NOT EXISTS `sys_module_app` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `tbl_name` varchar(30) NOT NULL,
  `instantiable` tinyint(1) NOT NULL,
  `description` text NOT NULL,
  `removable` tinyint(1) NOT NULL,
  `class_version` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=19 ;

--
-- Dumping data for table `sys_module_app`
--

INSERT INTO `sys_module_app` (`id`, `label`, `name`, `active`, `tbl_name`, `instantiable`, `description`, `removable`, `class_version`) VALUES
(1, 'Impostazioni', 'sysconf', 1, 'sys_conf', 0, 'Principali impostazioni di sistema', 0, '1.0'),
(2, 'Lingue', 'language', 1, 'language', 0, 'Gestione delle lingue disponibili per le traduzioni', 0, '1.0'),
(3, 'Moduli di sistema', 'sysClass', 1, 'sys_class', 0, 'Modifica, installazione e rimozione dei moduli di sistema', 0, '1.0'),
(4, 'Moduli', 'module', 1, 'sys_module', 0, 'Modifica, installazione e rimozione dei moduli di classi istanziate e moduli funzione', 0, '1.0'),
(6, 'Statistiche', 'statistics', 1, 'sys_stat', 0, 'Statistiche degli accessi all''area privata', 0, '1.0'),
(7, 'Layout', 'layout', 1, 'sys_layout', 0, 'Gestione di css, template, skin ed assegnazione a indirizzi o classi di indirizzi', 0, '1.0'),
(8, 'Header e Footer', 'graphics', 1, 'sys_graphics', 0, 'Gestione personalizzata degli header e footer del sistema', 0, '1.0'),
(9, 'Allegati', 'attachment', 1, 'attachment', 0, 'Archivi di file con struttura ad albero', 0, '1.0'),
(10, 'Menu', 'menu', 1, 'sys_menu', 1, '', 0, '1.0'),
(11, 'Pagine', 'page', 1, 'page', 0, 'Pagine html con struttura ad albero', 0, '1.0'),
(12, 'Index', 'index', 1, '', 0, '', 0, '1.0'),
(13, 'Ricerca nel sito', 'searchSite', 1, 'search_site', 0, 'Form di ricerca nel sito', 0, '1.0'),
(14, 'phpModuleView', 'phpModuleView', 1, 'php_module', 1, 'Generatore di moduli contenenti codice php', 1, '1.0'),
(15, 'Strumenti', 'instruments', 1, 'instruments', 0, 'Alcuni strumenti, quali l''elenco delle risorse disponibili (con i relativi link) e dei mime type', 0, '1.0'),
(16, 'Autenticazione', 'auth', 1, 'auth', 0, 'Modulo utenti, gruppi e permessi', 0, '1.0'),
(17, 'Funzioni di sistema', 'sysfunc', 1, 'sysfunc', 0, 'Funzioni di sistema', 0, '1.0'),
(18, 'Creazione App', 'buildapp', 1, 'buildapp', 0, 'Genera una applicazione predefinita pronta per essere personalizzata e installata in gino', 0, '1.0.0');

--
-- Table structure for table `sys_tag`
--

CREATE TABLE IF NOT EXISTS `sys_tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(68) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Table structure for table `sys_tag_taggeditem`
--

CREATE TABLE IF NOT EXISTS `sys_tag_taggeditem` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_id` int(11) NOT NULL,
  `content_controller_class` varchar(255) NOT NULL,
  `content_controller_instance` int(11) NOT NULL,
  `content_class` varchar(64) NOT NULL,
  `content_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_tagid` (`tag_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
