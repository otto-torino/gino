-- Versione per SQL Server

--
-- Database: dbgino
--

USE dbgino
GO

-- --------------------------------------------------------

--
-- Table structure for table attachment
--

CREATE TABLE attachment (
  id int IDENTITY(1, 1),
  category		int NOT NULL,
  [file]		nvarchar(100) NOT NULL,
  notes			text,
  insertion_date datetime NOT NULL,
  last_edit_date datetime NOT NULL,
  CONSTRAINT PK_attachment PRIMARY KEY (id)
)

SET IDENTITY_INSERT attachment ON

INSERT INTO attachment ([id], [category], [file], [notes], [insertion_date], [last_edit_date]) VALUES
(1, 1, 'lamp.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(2, 1, 'OSI_logo.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(3, 1, 'github.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(4, 1, 'plugin.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(5, 1, 'github.jpg', 'Logo GitHub', '2014-12-01 16:20:17', '2014-12-01 16:20:17');

SET IDENTITY_INSERT attachment OFF

-- --------------------------------------------------------

--
-- Table structure for table attachment_ctg
--

CREATE TABLE attachment_ctg (
  id int IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  directory nvarchar(20) NOT NULL,
  CONSTRAINT PK_attachment_ctg PRIMARY KEY (id)
)

SET IDENTITY_INSERT attachment_ctg ON

INSERT INTO attachment_ctg (id, name, directory) VALUES
(1, 'gino', 'gino');

SET IDENTITY_INSERT attachment_ctg OFF

-- --------------------------------------------------------

--
-- Table structure for table auth_group
--

CREATE TABLE auth_group (
  id int IDENTITY(1, 1),
  name nvarchar(128) NOT NULL,
  description text,
  CONSTRAINT PK_auth_group PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table auth_group_perm
--

CREATE TABLE auth_group_perm  (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  perm_id smallint NOT NULL
)

-- --------------------------------------------------------

--
-- Table structure for table auth_opt
--

CREATE TABLE auth_opt (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  users_for_page smallint NOT NULL,
  user_more_info tinyint NOT NULL,
  user_card_view tinyint NOT NULL,
  username_as_email tinyint NOT NULL,
  aut_pwd tinyint NOT NULL,
  aut_pwd_length smallint NOT NULL,
  pwd_min_length smallint NOT NULL,
  pwd_max_length smallint NOT NULL,
  pwd_numeric_number int NOT NULL,
  ldap_auth tinyint NOT NULL,
  ldap_auth_only tinyint NOT NULL,
  ldap_single_user nvarchar(50) NULL,
  ldap_auth_password nvarchar(100) NULL,
  CONSTRAINT PK_auth_opt PRIMARY KEY (id)
)

SET IDENTITY_INSERT auth_opt ON

INSERT INTO auth_opt (id, instance, users_for_page, user_more_info, user_card_view, username_as_email, aut_pwd, aut_pwd_length, pwd_min_length, pwd_max_length, pwd_numeric_number, ldap_auth, ldap_auth_only, ldap_single_user, ldap_auth_password) VALUES
(1, 0, 10, 0, 1, 0, 0, 10, 6, 14, 2, 0, 0, NULL, NULL);

SET IDENTITY_INSERT auth_opt OFF

-- --------------------------------------------------------

--
-- Table structure for table auth_permission
--

CREATE TABLE auth_permission (
  id int IDENTITY(1, 1),
  [class] nvarchar(128) NOT NULL,
  code nvarchar(128) NOT NULL,
  label nvarchar(255) NOT NULL,
  description text,
  [admin] tinyint NOT NULL,
  CONSTRAINT PK_auth_permission PRIMARY KEY (id)
)

SET IDENTITY_INSERT auth_permission ON

INSERT INTO auth_permission (id, [class], code, label, description, [admin]) VALUES
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
(21, 'statistics', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1);

SET IDENTITY_INSERT auth_permission OFF

-- --------------------------------------------------------

--
-- Table structure for table auth_registration_profile
--

CREATE TABLE auth_registration_profile (
  id int IDENTITY(1, 1),
  description nvarchar(255) NOT NULL,
  title nvarchar(255) NULL,
  text text,
  terms text,
  auto_enable tinyint NOT NULL,
  add_information tinyint NULL,
  add_information_module_type tinyint NULL,
  add_information_module_id int NULL, 
  CONSTRAINT PK_auth_registration_profile PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table auth_registration_profile_group
--

CREATE TABLE auth_registration_profile_group (
  id int IDENTITY(1, 1),
  registrationprofile_id int NOT NULL,
  group_id int NOT NULL, 
  CONSTRAINT PK_auth_registration_profile_group PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table auth_registration_request
--

CREATE TABLE auth_registration_request (
  id int IDENTITY(1, 1),
  registration_profile int NOT NULL,
  [date] datetime NOT NULL,
  [code] nvarchar(32) NOT NULL,
  firstname nvarchar(255) NOT NULL,
  lastname nvarchar(255) NOT NULL,
  username nvarchar(50) NOT NULL,
  password nvarchar(100) NOT NULL,
  email nvarchar(128) NOT NULL,
  confirmed tinyint NOT NULL DEFAULT '0',
  [user] int NULL, 
  CONSTRAINT PK_auth_registration_request PRIMARY KEY (id)
)
-- --------------------------------------------------------

--
-- Table structure for table auth_user
--

CREATE TABLE auth_user (
  id int IDENTITY(1, 1),
  firstname nvarchar(50) NOT NULL DEFAULT '',
  lastname nvarchar(50) NOT NULL DEFAULT '',
  company nvarchar(100) DEFAULT NULL,
  phone nvarchar(30) DEFAULT NULL,
  fax nvarchar(10) DEFAULT NULL,
  email nvarchar(100) NOT NULL DEFAULT '',
  username nvarchar(50) NOT NULL,
  userpwd nvarchar(100) NOT NULL,
  is_admin tinyint NOT NULL DEFAULT '0',
  address nvarchar(200) DEFAULT NULL,
  cap int DEFAULT NULL,
  city nvarchar(50) DEFAULT NULL,
  nation smallint DEFAULT NULL,
  text text,
  photo nvarchar(50) DEFAULT NULL,
  publication tinyint NOT NULL DEFAULT '0',
  date datetime NOT NULL,
  active tinyint NOT NULL DEFAULT '0',
  ldap tinyint NOT NULL DEFAULT '0',
  CONSTRAINT PK_auth_user PRIMARY KEY (id)
)

SET IDENTITY_INSERT auth_user ON

INSERT INTO auth_user (id, firstname, lastname, company, phone, fax, email, username, userpwd, is_admin, address, cap, city, nation, text, photo, publication, date, active, ldap) VALUES
(1, 'utente', 'amministratore', 'otto srl', '+39 011 8987553', NULL, 'support@otto.to.it', 'admin', '1844156d4166d94387f1a4ad031ca5fa', 1, 'piazza Gran Madre di Dio, 7', 10131, 'Torino', 83, NULL, NULL, 0, '2011-10-10 01:00:00', 1, 0);

SET IDENTITY_INSERT auth_user OFF

-- --------------------------------------------------------

--
-- Table structure for table auth_user_add
--

CREATE TABLE auth_user_add (
  user_id int NOT NULL,
  field1 tinyint NOT NULL DEFAULT '0',
  field2 tinyint NOT NULL DEFAULT '0',
  field3 tinyint NOT NULL DEFAULT '0',
  CONSTRAINT PK_auth_user_add PRIMARY KEY (user_id)
)

-- --------------------------------------------------------

--
-- Table structure for table auth_user_group
--

CREATE TABLE auth_user_group (
  id int IDENTITY(1, 1),
  user_id int NOT NULL,
  group_id int NOT NULL,
  CONSTRAINT PK_auth_user_group PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table auth_user_perm
--

CREATE TABLE auth_user_perm (
  instance int NOT NULL,
  user_id int NOT NULL,
  perm_id smallint NOT NULL
)

-- --------------------------------------------------------

--
-- Table structure for table instruments
--

CREATE TABLE instruments (
  id int IDENTITY(1, 1),
  name nvarchar(200) NOT NULL,
  description text NOT NULL,
  order_list smallint NOT NULL,
  CONSTRAINT PK_instruments PRIMARY KEY (id)
)

SET IDENTITY_INSERT instruments ON

INSERT INTO instruments (id, name, description, order_list) VALUES
(1, 'Collegamenti', 'Elenco interfacce e pagine disponibili', 1),
(2, 'Mime-Type', 'Elenco dei mime type con le estensioni di riferimento', 2);

SET IDENTITY_INSERT instruments OFF

-- --------------------------------------------------------

--
-- Table structure for table instruments_opt
--

CREATE TABLE instruments_opt (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  CONSTRAINT PK_instruments_opt PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table language
--

CREATE TABLE language (
  id int IDENTITY(1, 1),
  label nvarchar(10) NOT NULL,
  language nvarchar(50) NOT NULL DEFAULT '',
  language_code nvarchar(5) NOT NULL DEFAULT '',
  country_code nvarchar(5) NOT NULL,
  active tinyint NOT NULL,
  CONSTRAINT PK_language PRIMARY KEY (id)
)

SET IDENTITY_INSERT language ON

INSERT INTO language (id, label, language, language_code, country_code, active) VALUES
(1, 'ITA', 'italiano', 'it', 'IT', 1),
(2, 'ENG', 'english', 'en', 'US', 1),
(3, 'ESP', 'espanol', 'es', 'ES', 0),
(4, 'FRA', 'français', 'fr', 'FR', 0);

SET IDENTITY_INSERT language OFF

-- --------------------------------------------------------

--
-- Table structure for table language_opt
--

CREATE TABLE language_opt (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  opt_flag tinyint NOT NULL,
  CONSTRAINT PK_language_opt PRIMARY KEY (id)
)

SET IDENTITY_INSERT language_opt ON

INSERT INTO language_opt (id, instance, title, opt_flag) VALUES
(1, 0, 'Lingue', 0);

SET IDENTITY_INSERT language_opt OFF

-- --------------------------------------------------------

--
-- Table structure for table language_translation
--

CREATE TABLE language_translation (
  tbl_id_value int NULL,
  tbl nvarchar(200) NULL,
  field nvarchar(200) NULL,
  language nvarchar(5) NULL,
  text text
)

INSERT INTO language_translation (tbl_id_value, tbl, field, language, text) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table nation
--

CREATE TABLE nation (
  id smallint IDENTITY(1, 1),
  it_IT nvarchar(100) NOT NULL,
  en_US nvarchar(100) NOT NULL,
  fr_FR nvarchar(100) NOT NULL,
  onu date NOT NULL,
  CONSTRAINT PK_nation PRIMARY KEY (id)
)

SET IDENTITY_INSERT nation ON

INSERT INTO nation (id, it_IT, en_US, fr_FR, onu) VALUES
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
(192, 'Zimbabwe', 'Zimbabwe', 'Zimbabwe', '1980-08-25');

SET IDENTITY_INSERT nation OFF

-- --------------------------------------------------------

--
-- Table structure for table page_category
--

CREATE TABLE page_category (
  id int IDENTITY(1, 1),
  name nvarchar(60) NOT NULL,
  description text,
  date datetime NOT NULL,
  CONSTRAINT PK_page_category PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table page_comment
--

CREATE TABLE page_comment (
  id int IDENTITY(1, 1),
  entry int NOT NULL,
  datetime datetime NOT NULL,
  author nvarchar(200) NOT NULL,
  email nvarchar(200) NOT NULL,
  web nvarchar(200) NULL,
  text text NOT NULL,
  notification tinyint NOT NULL,
  reply int NULL,
  published tinyint NOT NULL,
  CONSTRAINT PK_page_comment PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table page_entry
--

CREATE TABLE page_entry (
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
  enable_comments tinyint NOT NULL DEFAULT '0',
  published tinyint NOT NULL DEFAULT '0',
  social tinyint NOT NULL DEFAULT '0',
  private tinyint NOT NULL DEFAULT '0',
  users nvarchar(255) NULL,
  view_last_edit_date tinyint NOT NULL DEFAULT '0',
  users_edit nvarchar(255) NULL,
  [read] int NOT NULL DEFAULT '0',
  tpl_code text,
  box_tpl_code text,
  CONSTRAINT PK_page_entry PRIMARY KEY (id)
)

SET IDENTITY_INSERT page_entry ON

INSERT INTO page_entry (id, category_id, author, creation_date, last_edit_date, title, slug, image, url_image, text, tags, enable_comments, published, social, private, users, view_last_edit_date, users_edit, [read], tpl_code, box_tpl_code) VALUES
(1, NULL, 1, '2011-10-20 12:02:48', '2011-10-20 12:02:48', 'Che cos''è gino CMS', 'gino-CMS', NULL, NULL, '<p>gino CMS è uno dei framework open source sviluppati internamente da Otto, utilizzato al fine di offrire vari servizi ai nostri clienti.</p><p>È un <b>CMS</b>, acronimo di <i>Content Management System</i>, cioè un sistema di gestione dei contenuti web, creato appositamente per facilitarne l''organizzazione e la pubblicazione.</p>', '', 0, 1, 0, 0, '', 0, NULL, 0, NULL, NULL),
(2, NULL, 1, '2011-10-26 17:34:44', '2013-01-09 12:36:54', 'Tecnologia', 'tecnologia', NULL, NULL, '<p>gino nasce ed è ottimizzato per il server model <b>LAMP</b>, tuttavia non è limitato a questi programmi potendo essere utilizzato anche con altri server web, quali ad esempio nginx e IIS, e con SQL Server, in attesa che vengano implementati altri connettori.</p><p><img alt="LAMP logos" class="img-responsive" src="contents/attachment/gino/lamp.jpg" /></p>', '', 0, 1, 0, 0, '', 0, NULL, 0, NULL, NULL),
(3, NULL, 1, '2011-10-28 15:17:39', '2013-01-09 12:42:41', 'Licenza', 'licenza', NULL, NULL, '<p><img alt="OSI approved license" src="contents/attachment/gino/OSI_logo.jpg" style="margin-left: 10px; margin-right: 10px; float: left;" />Alla <a href="http://www.otto.to.it" rel="external">Otto</a> usiamo e produciamo software <a href="http://www.opensource.org/docs/osd" rel="external">open source</a>.</p><p>In particolare, gino CMS viene distribuito con licenza <a href="http://www.opensource.org/licenses/MIT" rel="external">MIT</a> (MIT).</p><p class="null"></p>', '', 0, 1, 0, 0, '', 0, NULL, 0, NULL, NULL),
(4, NULL, 1, '2011-11-01 09:59:14', '2013-01-09 12:45:31', 'Documentazione', 'documentazione', NULL, NULL, '<p>La documentazione e le reference di tutti i file sono ospitate su <b>github</b> sotto forma di <a href="https://github.com/otto-torino/gino/wiki" rel="external">wiki</a> che copre essenzialmente gli aspetti di sviluppo di gino.</p><p></p><p class="null"><img alt="github logo" src="contents/attachment/gino/github.jpg" style="margin-left: 10px; margin-right: 10px; float: left;" />Per una documentazione più ampia, comprendente tutorial e how-to, potete fare riferimento alla pagina dedicata sul <a href="http://gino.otto.to.it" rel="external">sito ufficiale di gino</a>.</p><p class="null"></p>', '', 0, 1, 0, 0, '', 0, NULL, 0, NULL, NULL),
(5, NULL, 1, '2011-11-08 14:05:57', '2013-12-06 16:35:16', 'Estendere gino', 'estendere-gino', NULL, NULL, '<p>
<img alt="plugin" src="contents/attachment/gino/plugin.jpg" style="margin-left: 10px; margin-right: 10px; float: left;" />Le funzionalità di gino possono essere ampliate utilizzando i moduli aggiuntivi disponibili. gino incorpora un meccanismo per il caricamento semplificato e l''aggiornamento di questi moduli.</p>
<p>Per un elenco dei moduli fate riferimento alla pagina sul <a href="http://gino.otto.to.it/" rel="external" title="Il link apre una nuova finestra">sito ufficiale di gino</a>.</p>
<p class="null"></p>', '', 0, 1, 0, 0, '', 0, NULL, 0, NULL, NULL),
(6, NULL, 1, '2015-05-11 15:05:21', '2015-05-12 12:40:08', 'Privacy - Cookie', 'privacy-cookie', NULL, NULL, '<p>Con riferimento all''art. 122 secondo comma del D.lgs. 196/2003 e a seguito delle modalità semplificate per l''informativa e l''acquisizione del consenso per l''uso dei cookie pubblicata sulla Gazzetta Ufficiale n.126 del 3 giugno 2014 e relativo registro dei provvedimenti n.229 dell''8 maggio 2014, si dichiara che:</p>

<p>1 - Il sito web <b>NOMESITO</b> (più avanti "Sito") utilizza i cookie per offrire i propri servizi agli Utenti durante la consultazione delle sue pagine. Titolare del trattamento dei dati è <b>NOMEAZIENDA</b> (informazioni di contatto in fondo ad ogni pagina).</p>

<p>2 - L''informativa è valida solo per il Sito e per gli eventuali domini di secondo e terzo livello correlati, e non per altri siti consultabili tramite link.</p>

<p>3 - Se l''utente non acconsente all''utilizzo dei cookie, non accettando in maniera esplicita i cookie di navigazione, o mediante specifiche configurazioni del browser utilizzato o dei relativi programmi informatici utilizzati per navigare le pagine che compongono il Sito, o modificando le impostazioni nell''uso dei servizi di terze parti utilizzati all''interno del Sito, l''esperienza di navigazione potrebbe essere penalizzata, ed alcune funzionalità potrebbero non essere disponibili.</p>

<p>4 - Il Sito NON fa uso diretto (first-part cookie) di cookie di PROFILAZIONE degli utenti.</p>

<p>5 - Il Sito NON consente l''invio di cookie di PROFILAZIONE di TERZE PARTI (third-part cookie).</p>

<p>6 - Il Sito fa uso diretto esclusivamente di cookie TECNICI per salvare i parametri di sessione e agevolare quindi la navigazione agli utenti.</p>

<p>7 - Il Sito potrà fare uso di cookie TECNICI di terze parti (non tutti i servizi sono per forza attivi):<br />
<br />
<b>Google</b><br />
Il servizio Google Analytics viene utilizzato per raccogliere statistiche ANONIME di accesso, monitorare e analizzare i dati di traffico.<br />
I servizi Google Maps e YouTube sono utilizzati per includere contenuti multimediali all''interno del sito.<br />
<a href="http://www.google.com/policies/technologies/types/" rel="external">Informazioni generali</a> | <a href="http://www.google.com/policies/privacy/" rel="external">Privacy Policy</a> | <a href="http://tools.google.com/dlpage/gaoptout?hl=it" rel="external">Opt Out</a></p>

<p><b>ShareThis</b><br />
Il servizio ShareThis viene utilizzato per facilitare la condivisione dei contenuti sulle più comuni piattaforme social.<br />
<a href="http://www.sharethis.com/legal/privacy/" rel="external">Privacy Policy</a> | <a href="http://www.sharethis.com/legal/privacy/" rel="external">Opt Out</a></p>

<p><b>Disqus</b><br />
Il servizio viene utilizzato per facilitare e migliorare la gestione dei commenti ai contenuti.<br />
<a href="https://help.disqus.com/customer/portal/articles/466259-privacy-policy" rel="external">Privacy Policy</a> | <a href="https://help.disqus.com/customer/portal/articles/1657951" rel="external">Opt Out</a></p>

<p><b>Vimeo</b><br />
Il popolare servizio di streaming video utilizza i cookie per ottimizzare la fruizione dei suoi servizi, e il alcuni casi il Sito può includere video Vimeo.<br />
<a href="https://vimeo.com/cookie_policy" rel="external">Cookie Policy</a></p>

<p><b>Bottoni Social</b><br />
I bottoni social sono bottoni che permettono di rendere più immediata ed agevole la condivisione dei contenuti sulle più comuni piattaforme social. Qui di seguito i dettagli dei principali servizi:</p>\

<p><b>Pulsante +1 e widget sociali di Google+</b> (Google Inc.)<br />
Il pulsante +1 e i widget sociali di Google+ (tra cui i commenti) sono servizi di interazione con il social network Google+, forniti da Google Inc.<br />
Dati personali raccolti: Cookie e Dati di utilizzo.<br />
Luogo del trattamento : USA - <a href="http://www.google.com/intl/it/policies/privacy/" rel="external">Privacy Policy</a></p>

<p><b>Pulsante "Mi Piace" e widget sociali di Facebook</b> (Facebook, Inc.)<br />
Il pulsante "Mi Piace" e i widget sociali di Facebook sono servizi di interazione con il social network Facebook, forniti da Facebook, Inc.<br />
Dati personali raccolti: Cookie e Dati di utilizzo.<br />
Luogo del trattamento : USA - <a href="http://www.facebook.com/privacy/explanation.php" rel="external">Privacy Policy</a></p>

<p><b>Pulsante Tweet e widget sociali di Twitter</b> (Twitter, Inc.)<br />
Il pulsante Tweet e i widget sociali di Twitter sono servizi di interazione con il social network Twitter, forniti da Twitter, Inc.<br />
Dati personali raccolti: Cookie e Dati di utilizzo.<br />
Luogo del trattamento : USA - <a href="http://twitter.com/privacy" rel="external">Privacy Policy</a></p>

<p><b>Pulsante e widget sociali di Linkedin</b> (Linkedin Corp.)<br />
Il pulsante e i widget sociali di Linkedin sono servizi di interazione con il social network Linkedin, forniti da Linkedin Inc.<br />
Dati personali raccolti: Cookie e Dati di navigazione ed utilizzo.<br />
Luogo del Trattamento: USA - <a href="http://www.linkedin.com/static?key=privacy_policy&trk=hb_ft_priv" rel="external">Privacy Policy</a></p>

<p><b>Cookie Script</b><br />
Il Sito utilizza il servizio Cookie Script per l''accettazione dell''utilizzo dei cookies. Se acconsenti all''utilizzo dei cookies, un ulteriore cookie tecnico di nome cookiescriptaccept verrà scritto per ricordare in futuro la tua scelta.<br />
<a href="https://cookie-script.com/privacy-policy-and-disclaimer.html" rel="external">Privacy Policy</a></p>

<p>8 - Questa pagina è raggiungibile mediante un link presente in tutte le pagine del Sito.</p>

<p>9 - Negando il consenso all''utilizzo dei cookie, nessun cookie verrà scritto sul dispositivo dell''utente, eccetto il cookie tecnico di sessione. Sarà ancora possibile navigare il Sito, ma alcune parti di esso potrebbero non funzionare correttamente.</p>

<p> </p>

<p><b>Ma... cosa sono i cookie?</b></p>

<p>I cookie sono file o pacchetti di dati che possono venire salvati sul computer dell''utente (o altro dispositivo abilitato alla navigazione su internet, per esempio smartphone o tablet) quando visita un sito web. Di solito un cookie contiene il nome del sito internet dal quale il cookie stesso proviene, la durata del cookie (ovvero l''indicazione del tempo per il quale il cookie rimarrà memorizzato sul dispositivo), ed un contenuto (numero, stringa, etc.), che gli permette di svolgere la sua funzione.<br />
Per maggiori informazioni visita il sito in lingua inglese <a href="http://aboutcookies.org/." rel="external">aboutcookies.org</a>.</p>

<p> </p>

<p><b>Per cosa si usano i cookie?</b></p>

<p>Si utilizzano i cookie per rendere la navigazione più semplice e per meglio adattare il sito web ai bisogni dell''utente. I cookie possono anche venire usati per aiutare a velocizzare le future esperienze ed attività dell''utente su altri siti web, e si usano per compilare statistiche anonime aggregate che consentono di capire come gli utenti usano i siti in modo da aiutare a migliorare la struttura ed i contenuti di questi siti.</p>

<p> </p>

<p><b>I diversi tipi di cookie</b></p>

<p><b>Cookies Tecnici</b>: sono i cookie che servono a effettuare la navigazione o a fornire un servizio richiesto dall''utente. Non vengono utilizzati per scopi ulteriori e sono normalmente installati direttamente dal gestore del sito web che si sta novigando. Senza il ricorso a tali cookie, alcune operazioni non potrebbero essere compiute o sarebbero più complesse e/o meno sicure, (ad esempio i cookie che consentono di effettuare e mantenere l''identificazione dell''utente nell''ambito della sessione).</p>

<p><b>Cookies di Profilazione</b>: sono i cookie utilizzati per tracciare la navigazione dell''utente in rete e creare profili sui suoi gusti, abitudini, scelte, ecc. Con questi cookie possono essere trasmessi al terminale dell''utente messaggi pubblicitari in linea con le preferenze già manifestate dallo stesso utente nella navigazione online.</p>

<p><b>Cookies di prima parte</b> (first-part cookie) sono i cookie generati e utilizzati direttamente dal soggetto gestore del sito web sul quale l''utente sta navigando.</p>

<p><b>Cookies di terza parte</b> (third-part cookie), sono i cookie generati e gestiti da soggetti diversi dal gestore del sito web sul quale l''utente sta navigando (in forza, di regola, di un contratto tra il titolare del sito web e la terza parte)</p>

<p><b>Cookies di Sessione</b> e <b>Cookies Persistenti</b>:<br />
mentre la differenza tra un cookie di prima parte e un cookie di terzi riguarda il soggetto che controlla l''invio iniziale del cookie sul tuo dispositivo, la differenza tra un cookie di sessione e un cookie persistente riguarda il diverso lasso di tempo per cui un cookie opera. I cookie di sessione sono cookie che tipicamente durano finchè chiudi il tuo internet browser. Quando finisci la tua sessione browser, il cookie scade. I cookies persistenti, come lo stesso nome indica, sono cookie costanti e continuano ad operare dopo che hai chiuso il tuo browser.</p>

<p> </p>

<p><b>Come posso controllare le gestione dei cookie del mio browser?</b></p>

<p>Tutti i moderni browser offrono la possibilita di controllare le impostazioni di privacy, anche per quello che riguarda l''uso dei cookie. In particolare l''utente potrà intervenire sul comportamento generale del browser nei confronti dei cookie (ad esempio instruendolo a NON accettarli in futuro), visualizzare e/o cancellare i cookie già installati.<br />
<br />
Riportiamo qui di seguito le procedure per accedere a queste impostazioni per i browser più utilizzati:<br />
<br />
<a href="https://support.google.com/chrome/answer/95647?hl=it" rel="external">Chrome</a></p>

<p><a href="http://windows.microsoft.com/it-it/windows-vista/block-or-allow-cookies" rel="external">Internet Explorer</a></p>

<p><a href="https://support.mozilla.org/it/kb/Gestione%20dei%20cookie" rel="external">Firefox</a></p>

<p><a href="http://www.opera.com/help/tutorials/security/privacy/" rel="external">Opera</a></p>

<p><a href="https://support.apple.com/kb/PH17191?locale=en_US" rel="external">Safari 6/7</a></p>

<p><a href="https://support.apple.com/kb/PH19214?locale=en_US" rel="external">Safari 8</a></p>

<p><a href="https://support.apple.com/en-us/HT201265" rel="external">Safari mobile</a></p>', NULL, 0, 1, 0, 0, NULL, 0, NULL, 0, NULL, NULL);

SET IDENTITY_INSERT page_entry OFF

-- --------------------------------------------------------

--
-- Table structure for table page_opt
--

CREATE TABLE page_opt (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
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
  CONSTRAINT PK_page_opt PRIMARY KEY (id)
)

SET IDENTITY_INSERT page_opt ON

INSERT INTO page_opt (id, instance, showcase_title, showcase_number, showcase_auto_start, showcase_auto_interval, showcase_tpl_code, entry_tpl_code, box_tpl_code, comment_moderation, comment_notification, newsletter_entries_number, newsletter_tpl_code) VALUES
(1, 0, 'In evidenza', 3, 1, 5000, '<article>
<h1>{{ title }}</h1>
<p>{{ img|class:left }}</p>
{{ text }}
<div class="null"></div>
</article>', '<h1>{{ title }}</h1>
<p>{{ img|class:left }}</p>
{{ text }}
<div class="null"></div>', '<h1>{{ title }}</h1>
<p>{{ img|class:left }}</p>
{{ text }}
<div class="null"></div>', 0, 1, 5, NULL);

SET IDENTITY_INSERT page_opt OFF

-- --------------------------------------------------------

--
-- Table structure for table php_module
--

CREATE TABLE php_module (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  content text NOT NULL,
  CONSTRAINT PK_php_module PRIMARY KEY (id)
)

SET IDENTITY_INSERT php_module ON

INSERT INTO php_module (id, instance, content) VALUES
(1, 6, '$lng = (isset($_SESSION[''lng''])) ? $_SESSION[''lng'']:''it_IT'';
$access = new \Gino\Access();
$registry = \Gino\Registry::instance();

$buffer = "<div class=\"top-bar\">";
$buffer .= "<div class=\"left\">";
if($registry->sysconf->multi_language) {
  $query = "SELECT id, label, language_code, country_code FROM language WHERE active=''1'' ORDER BY language DESC";
  $a = $this->_db->execCustomQuery($query);
  $lng_buffer = array();
  foreach($a as $b) {
    if(isset($_SESSION[''lng''])) {
      $selected = $_SESSION[''lng''] == $b[''language_code''].''_''.$b[''country_code''] ? true : false;
    }
    else {
      $dft_lang_query = "SELECT dft_language FROM sys_conf WHERE id=''1''";
      $c = $this->_db->execCustomQuery($dft_lang_query);
      $dft_lang = $c[0][''dft_language''];
      
      $selected = $b[''id''] == $dft_lang ? true : false;
    }
    if(!$selected) {
      $lng_buffer[]  =  "<a href=\"index.php?lng=".$b[''language_code''].''_''.$b[''country_code'']."\">".\Gino\htmlChars($b[''label''])."</a>";
    }
    else {
      $lng_buffer[]  =  "<a class=\"selected\">".\Gino\htmlChars($b[''label''])."</a>";
    }
  }
  
  $buffer .= implode("", $lng_buffer); 
}
$buffer .= "</div>";
$buffer .= "<div class=\"right\">";
if(!isset($_SESSION[''user_id''])) {
    $buffer .= "<span class=\"link\" onclick=\"login_toggle.toggle();\">"._("Area riservata")."</span>";
    $buffer .= "<div id=\"topbar-login\" style=\"display:none;\">";
    $buffer .= "<div>";
    $buffer .= "<form method=\"post\" action=\"index.php\" style=\"float:right\">";
    $buffer .= "<input type=\"hidden\" name=\"action\" value=\"auth\" />";
    $buffer .= "<div class=\"form-row\">";
    $buffer .= "<label>User</label>";
    $buffer .= "<input type=\"text\" name=\"user\" required />";
    $buffer .= "</div>";
    $buffer .= "<div class=\"form-row\">";
    $buffer .= "<label>Password</label>";
    $buffer .= "<input type=\"password\" name=\"pwd\" required />";
    $buffer .= "</div>";
    $buffer .= "<div class=\"form-row\">";
    $buffer .= "<label></label>";
    $buffer .= "<input type=\"submit\" class=\"generic\" value=\"login\" />";
    $buffer .= "</div>";
    $buffer .= "</form>";
    $buffer .= "<div class=\"null\"></div>";
    $buffer .= "</div>";
    $buffer .= "</div>";
    $buffer .= "<script>var login_toggle = new Fx.Reveal(''topbar-login'');</script>";
}
else {
    $request = \Gino\Http\Request::instance();
    if($request->user->hasPerm(''core'', ''is_staff'')) {
    	$buffer .= "<a href=\\"admin\\">"._("Amministrazione")."</a>";
    }
    $query = "SELECT CONCAT(firstname, '' '', lastname) AS name FROM user_app WHERE user_id=''".$_SESSION[''user_id'']."''";
    $a = $this->_db->execCustomQuery($query);
    $username = $a>0 ? $a[0][''name'']:null;
    $buffer .= "<a href=\"index.php?evt[user-userCard]\"><span title=\""._("Profilo utente")."\" class=\"tooltip\">".$username."</span></a>";
    $buffer .= "<a href=\"index.php?action=logout\">"._("Logout")."</a>";
    $buffer .= "<div class=\"null\"></div>";
}
$buffer .= "</div>";
$buffer .= "<div class=\"clear\"></div>";
$buffer .= "</div>";'),
(2, 9, '$buffer = "<div class=\"top-bar\">";

$index = new \Gino\App\Index\index();

$sysMdls = $index->sysModulesManageArray();
$mdls = $index->modulesManageArray();
if(count($sysMdls)) {	
  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";
  $buffer .= "<select name=''sysmdl_menu'' onchange=\"$onchange\">";
  $buffer .= "<option value=\"\">"._("Sistema")."</option>";
  foreach($sysMdls as $sm) { 
    $buffer .= "<option value=\"".$sm[''name'']."-manage".ucfirst($sm[''name''])."\">".\Gino\htmlChars($sm[''label''])."</option>";
  }
  $buffer .= "</select> ";
}

if(count($mdls)) {
  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";
  $buffer .= "<select name=''mdl_menu'' onchange=\"$onchange\">";
  $buffer .= "<option value=\"\">"._("Moduli")."</option>";
  foreach($mdls as $m) {
    $buffer .= "<option value=\"".$m[''name'']."-manageDoc\">".\Gino\htmlChars($m[''label''])."</option>";
  }
  $buffer .= "</select>";
}

$buffer .= "</div>";');

SET IDENTITY_INSERT php_module OFF

-- --------------------------------------------------------

--
-- Table structure for table php_module_opt
--

CREATE TABLE php_module_opt (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  title_vis tinyint NOT NULL,
  CONSTRAINT PK_php_module_opt PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table search_site_opt
--

CREATE TABLE search_site_opt (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  sys_mdl nvarchar(256) NOT NULL,
  inst_mdl nvarchar(256) NOT NULL,
  view_choices tinyint NOT NULL 
	CONSTRAINT DF_search_site_opt_view_choices DEFAULT '0',
  CONSTRAINT PK_search_site_opt PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table sys_conf
--

CREATE TABLE sys_conf (
  id smallint IDENTITY(1, 1),
  multi_language tinyint NOT NULL,
  dft_language smallint NOT NULL,
  log_access tinyint NOT NULL,
  head_description text NOT NULL,
  head_keywords nvarchar(255) NULL,
  head_title nvarchar(255) NOT NULL,
  google_analytics nvarchar(20) NULL,
  captcha_public nvarchar(64) NULL,
  captcha_private nvarchar(64) NULL,
  sharethis_public_key nvarchar(64) NULL,
  disqus_shortname nvarchar(64) NULL,
  email_admin nvarchar(128) NOT NULL,
  email_from_app nvarchar(100) NULL,
  mobile tinyint NOT NULL DEFAULT '0',
  password_crypt nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_conf_password_crypt CHECK (password_crypt IN('none','sha1','md5')) DEFAULT 'md5',
  enable_cache tinyint NOT NULL, 
  query_cache tinyint NOT NULL 
  	CONSTRAINT DF_sys_conf_query_cache DEFAULT '0', 
  query_cache_time smallint NULL, 
  CONSTRAINT PK_sys_conf PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_conf ON

INSERT INTO sys_conf (id, multi_language, dft_language, log_access, head_description, head_keywords, head_title, google_analytics, captcha_public, captcha_private, email_admin, email_from_app, mobile, password_crypt, enable_cache, query_cache, query_cache_time) VALUES
(1, 1, 1, 1, 'Content Management System', NULL, 'gino CMS', NULL, NULL, NULL, 'kkk@otto.to.it', 'no-reply@otto.to.it', 0, 'md5', 0, 0, NULL);

SET IDENTITY_INSERT sys_conf OFF

-- --------------------------------------------------------

--
-- Table structure for table `sys_gimage`
--

CREATE TABLE sys_gimage (
	id int IDENTITY(1, 1),
	[key] nvarchar(32) NOT NULL,
	path nvarchar(255) NOT NULL,
	width int NOT NULL,
	height int NOT NULL,
	CONSTRAINT PK_sys_gimage PRIMARY KEY (id)
)

CREATE INDEX idx_sys_gimage_key on sys_gimage([key]);

-- --------------------------------------------------------

--
-- Table structure for table sys_graphics
--

CREATE TABLE sys_graphics (
  id smallint IDENTITY(1, 1),
  name nvarchar(50) NOT NULL,
  description nvarchar(100) NOT NULL,
  type tinyint NOT NULL DEFAULT '1',
  image nvarchar(128) NULL,
  html text,
  CONSTRAINT PK_sys_graphics PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_graphics ON

INSERT INTO sys_graphics (id, name, description, type, image, html) VALUES
(1, 'header_public', 'Header pagine pubbliche', 1, 'header.jpg', NULL),
(2, 'header_private', 'Header pagine private', 2, NULL, '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="840" height="160" id="header" align="middle">
	<param name="allowScriptAccess" value="sameDomain" />
	<param name="allowFullScreen" value="false" />
	<param name="wmode" value="transparent">
	<param name="movie" value="_GRAPHICS_/header.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" />
	<embed src="_GRAPHICS_/header.swf" quality="high" bgcolor="#ffffff" width="840" height="160" wmode="transparent" name="header" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object>'),
(3, 'header_admin', 'Header amministrazione', 1, 'header_admin.jpg', 'HEADER'),
(4, 'header_mobile', 'Header dispositivi mobili', 1, 'header_mobile.jpg', NULL),
(5, 'header_adhoc', 'Header ad hoc', 2, 'pf2.jpg', 'HEADER'),
(6, 'footer_public', 'Footer index pubblica', 1, 'footer.jpg', NULL),
(7, 'footer_private', 'Footer index privata', 2, NULL, '<p>header</p>'),
(8, 'footer_admin', 'Footer amministrazione', 1, 'footer_admin.jpg', NULL),
(9, 'footer_mobile', 'Footer dispositivi mobili', 1, 'footer_mobile.jpg', NULL),
(10, 'footer_adhoc', 'Footer ad hoc', 1, NULL, 'FOOTER ADHOC');

SET IDENTITY_INSERT sys_graphics OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_layout_css
--

CREATE TABLE sys_layout_css (
  id int IDENTITY(1, 1),
  filename nvarchar(200) NOT NULL,
  label nvarchar(200) NOT NULL,
  description text NULL,
  CONSTRAINT PK_sys_layout_css PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_css ON

INSERT INTO sys_layout_css (id, filename, label, description) VALUES
(1, 'mobile.css', 'Css per la visione mobile', 'Contiene regole per i dispositivi mobile'),
(2, 'admin.css', 'Css area amministrativa', 'Contiene regole per l''area amministrativa'),
(3, 'gino-blocks-tpl.css', 'Css per template a blocchi', 'Contiene regole css per il layout a blocchi di gino');

SET IDENTITY_INSERT sys_layout_css OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_layout_skin
--

CREATE TABLE sys_layout_skin (
  id int IDENTITY(1, 1),
  label nvarchar(200) NOT NULL,
  session nvarchar(128) NULL,
  rexp nvarchar(200) NULL,
  urls nvarchar(200) NULL,
  template nvarchar(200) NOT NULL,
  css int NULL,
  priority int NOT NULL,
  auth nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_layout_skin_auth CHECK (auth IN('yes','no','')),
  cache bigint NOT NULL DEFAULT '0',
  CONSTRAINT PK_sys_layout_skin PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_skin ON

INSERT INTO sys_layout_skin (id, label, session, rexp, urls, template, css, priority, auth, cache) VALUES
(1, 'Home Pubblica', NULL, '#(index.php(\?evt\[index-index_page\])?[^\[\]]*)?$#', NULL, '2', 0, 9, 'no', 0),
(2, 'Pagine Pubbliche', NULL, '#evt\[(?!index)#', NULL, '3', 0, 7, 'no', 0),
(3, 'Home Amministrazione', NULL, NULL, 'index.php?evt[index-admin_page]', '4', 0, 6, 'yes', 0),
(4, 'Pagine Amministrazione', NULL, '#evt\[\w+-((manage)|(wrapper))\w*\]#', NULL, '5', 2, 5, 'yes', 0),
(5, 'Pagina Autenticazione', NULL, NULL, 'index.php?evt[auth-login]', '3', 0, 3, 'no', 0),
(6, 'Default', NULL, '#^.*$#', NULL, '1', 2, 11, '', 0),
(7, 'Pagine Private', NULL, '#evt\[(?!index)#', NULL, '3', 0, 8, 'yes', 0),
(8, 'Home Privata', NULL, '#(index.php(\?evt\[index-index_page\])?[^\[\]]*)?$#', NULL, '2', 0, 10, 'yes', 0),
(9, 'Pagine Mobile', 'L_mobile=1', '#.*#', NULL, '8', 1, 2, '', 0),
(10, 'Home Mobile', 'L_mobile=1', '#^index.php$#', NULL, '7', 1, 1, '', 0),
(11, '_popup', NULL, '#&_popup=1#', NULL, '6', 2, 4, 'yes', 0);

SET IDENTITY_INSERT sys_layout_skin OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_layout_tpl
--

CREATE TABLE sys_layout_tpl (
  id int IDENTITY(1, 1),
  filename nvarchar(200) NOT NULL,
  label nvarchar(200) NOT NULL,
  description text NOT NULL,
  free tinyint NOT NULL,
  CONSTRAINT PK_sys_layout_tpl PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_tpl ON

INSERT INTO sys_layout_tpl (id, filename, label, description, free) VALUES
(1, 'default.tpl', 'Default', 'Template di default a blocchi', 0),
(2, 'home.php', 'Home', 'Template home page', 1),
(3, 'page.php', 'Pagine', 'Template pagine interne', 1),
(4, 'admin_home.php', 'Home admin', 'Template home area amministrativa', 1),
(5, 'admin_page.php', 'Pagine admin', 'Template pagine area amministrativa', 1),
(6, '_popup.php', '_popup', 'Template per l''inserimento di foreign o m2m contestuali', 1),
(7, 'home_mobile.php', 'Home mobile', 'Template home page dispositivi mobile', 1),
(8, 'pages_mobile.php', 'Pagine mobile', 'Template pagine interne dispositivi mobile', 1);

SET IDENTITY_INSERT sys_layout_tpl OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_layout_tpl_block
--

CREATE TABLE sys_layout_tpl_block (
  id int IDENTITY(1, 1),
  tpl int NOT NULL,
  position smallint NOT NULL,
  width int NOT NULL,
  um tinyint NOT NULL,
  align tinyint NOT NULL,
  rows smallint NOT NULL,
  cols smallint NOT NULL,
  CONSTRAINT PK_sys_layout_tpl_block PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_tpl_block ON

INSERT INTO sys_layout_tpl_block (id, tpl, position, width, um, align, rows, cols) VALUES
(1, 1, 1, 0, 0, 0, 1, 1),
(2, 1, 2, 0, 0, 0, 1, 1),
(3, 1, 3, 960, 1, 2, 1, 2),
(4, 1, 4, 0, 0, 0, 1, 1),
(5, 1, 5, 960, 1, 2, 1, 2);

SET IDENTITY_INSERT sys_layout_tpl_block OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_log_access
--

CREATE TABLE sys_log_access (
  id int IDENTITY(1, 1),
  user_id int NOT NULL,
  date datetime NOT NULL,
  CONSTRAINT PK_sys_log_access PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table sys_menu_opt
--

CREATE TABLE sys_menu_opt (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  cache bigint DEFAULT '0',
  view_admin_voice tinyint NOT NULL DEFAULT '0',
  view_logout_voice tinyint NOT NULL DEFAULT '0',
  CONSTRAINT PK_sys_menu_opt PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_menu_opt ON

INSERT INTO sys_menu_opt (id, instance, title, cache, view_admin_voice, view_logout_voice) VALUES
(1, 4, 'Menu principale', 0, 0, 1),
(2, 5, 'Menu amministrazione', 0, 1, 1);

SET IDENTITY_INSERT sys_menu_opt OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_menu_voices
--

CREATE TABLE sys_menu_voices (
  id int IDENTITY(1, 1),
  instance int NOT NULL,
  parent int NOT NULL,
  label nvarchar(200) NOT NULL,
  url nvarchar(200) NOT NULL,
  type nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_menu_voices_type CHECK (type IN('int','ext')),
  order_list smallint NOT NULL,
  perms nvarchar(255) NOT NULL,
  CONSTRAINT PK_sys_menu_voices PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_menu_voices ON

INSERT INTO sys_menu_voices (id, instance, parent, label, url, type, order_list, perms) VALUES
(1, 4, 0, 'Documentazione', 'page/view/documentazione', 'int', 2, '');

SET IDENTITY_INSERT sys_menu_voices OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_module
--

CREATE TABLE sys_module (
  id int IDENTITY(1, 1),
  label nvarchar(100) NOT NULL,
  name nvarchar(100) NOT NULL,
  module_app int NOT NULL,
  active tinyint NOT NULL,
  description text NULL,
  CONSTRAINT PK_sys_module PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_module ON

INSERT INTO sys_module (id, label, name, module_app, active, description) VALUES
(4, 'Menu principale', 'mainMenu', 10, 1, 'Menu principale'),
(5, 'Menu amministrazione', 'menu_admin', 10, 1, 'Menu area amministrativa'),
(6, 'Top Bar', 'topbar', 14, 1, 'Barra superiore con scelta lingua ed autenticazione'),
(9, 'Top Bar Admin', 'topbaradmin', 14, 1, 'Barra superiore con link diretto all''amministrazione dei singoli moduli');

SET IDENTITY_INSERT sys_module OFF

-- --------------------------------------------------------

--
-- Table structure for table sys_module_app
--

CREATE TABLE sys_module_app (
  id int IDENTITY(1, 1),
  label nvarchar(100) NOT NULL,
  name nvarchar(100) NOT NULL,
  active tinyint NOT NULL DEFAULT '1',
  tbl_name nvarchar(30) NOT NULL,
  instantiable tinyint NOT NULL,
  description text NOT NULL,
  removable tinyint NOT NULL,
  class_version nvarchar(200) NOT NULL,
  CONSTRAINT PK_sys_module_app PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_module_app ON

INSERT INTO sys_module_app (id, label, name, active, tbl_name, instantiable, description, removable, class_version) VALUES
(1, 'Impostazioni', 'sysconf', 1, 'sys_conf', 0, 'Principali impostazioni di sistema', 0, '1.0'),
(2, 'Lingue', 'language', 1, 'language', 0, 'Gestione delle lingue disponibili per le traduzioni', 0, '1.0'),
(3, 'Moduli di sistema', 'sysClass', 1, 'sys_class', 0, 'Modifica, installazione e rimozione dei moduli di sistema', 0, '1.0'),
(4, 'Moduli', 'module', 1, 'sys_module', 0, 'Modifica, installazione e rimozione dei moduli di classi istanziate e moduli funzione', 0, '1.0'),
(6, 'Statistiche', 'statistics', 1, 'sys_stat', 0, 'Statistiche degli accessi all''area privata', 0, '1.0'),
(7, 'Layout', 'layout', 1, 'sys_layout', 0, 'Gestione di css, template, skin ed assegnazione a indirizzi o classi di indirizzi', 0, '1.0'),
(8, 'Header e Footer', 'graphics', 1, 'sys_graphics', 0, 'Gestione personalizzata degli header e footer del sistema', 0, '1.0'),
(9, 'Allegati', 'attached', 1, 'attached', 0, 'Archivi di file con struttura ad albero', 0, '1.0'),
(10, 'Menu', 'menu', 1, 'sys_menu', 1, '', 0, '1.0'),
(11, 'Pagine', 'page', 1, 'page', 0, 'Pagine html con struttura ad albero', 0, '1.0'),
(12, 'Index', 'index', 1, '', 0, '', 0, '1.0'),
(13, 'Ricerca nel sito', 'searchSite', 1, 'search_site', 0, 'Form di ricerca nel sito', 0, '1.0'),
(14, 'phpModuleView', 'phpModuleView', 1, 'php_module', 1, 'Generatore di moduli contenenti codice php', 1, '1.0'),
(15, 'Strumenti', 'instruments', 1, 'instruments', 0, 'Alcuni strumenti, quali l''elenco delle risorse disponibili (con i relativi link) e dei mime type', 0, '1.0'),
(16, 'Autenticazione', 'auth', 1, 'auth', 0, 'Modulo utenti, gruppi e permessi', 0, '1.0'),
(17, 'Funzioni di sistema', 'sysfunc', 1, 'sysfunc', 0, 'Funzioni di sistema', 0, '1.0');

SET IDENTITY_INSERT sys_module_app OFF

-- --------------------------------------------------------

CREATE TABLE sys_tag (
 id int IDENTITY(1, 1),
 tag nvarchar(68) NOT NULL,
 CONSTRAINT PK_sys_tag PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Table structure for table `sys_tag_taggeditem`
--

CREATE TABLE sys_tag_taggeditem (
 id int IDENTITY(1, 1),
 tag_id int NOT NULL,
 content_controller_class nvarchar(255) NOT NULL,
 content_controller_instance int NOT NULL,
 content_class nvarchar(64) NOT NULL,
 content_id int NOT NULL,
 CONSTRAINT PK_sys_tag_taggeditem PRIMARY KEY (id)
)
