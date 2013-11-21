
--
-- Database: dbgino
--

USE dbgino
GO

-- --------------------------------------------------------

--
-- Table structure for table attached
--

CREATE TABLE attached (
  id			int IDENTITY(1, 1),
  category		int NOT NULL,
  [file]			nvarchar(100) NOT NULL,
  notes			nvarchar(max),
  insertion_date datetime NOT NULL,
  last_edit_date datetime NOT NULL,
  PRIMARY KEY (id)
)
GO

SET IDENTITY_INSERT attached ON

INSERT INTO attached ([id], [category], [file], [notes], [insertion_date], [last_edit_date]) VALUES
(1, 1, 'lamp.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(2, 1, 'OSI_logo.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(3, 1, 'github.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37'),
(4, 1, 'plugin.jpg', NULL, '2013-04-03 16:20:37', '2013-04-03 16:20:37');

SET IDENTITY_INSERT attached OFF

-- --------------------------------------------------------

--
-- Table structure for table attached_ctg
--

CREATE TABLE attached_ctg (
  id			int IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  directory nvarchar(20) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT attached_ctg ON

INSERT INTO attached_ctg (id, name, directory) VALUES
(1, 'various', 'c1');

SET IDENTITY_INSERT attached_ctg OFF

-- --------------------------------------------------------

--
-- Table structure for table attached_grp
--

CREATE TABLE attached_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_attached_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT attached_grp ON

INSERT INTO attached_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono gli allegati.', 'no');

SET IDENTITY_INSERT attached_grp OFF

-- --------------------------------------------------------

--
-- Table structure for table attached_usr
--

CREATE TABLE attached_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)


-- --------------------------------------------------------

--
-- Struttura della tabella instruments
--

CREATE TABLE instruments (
  id			int IDENTITY(1, 1),
  name nvarchar(200) NOT NULL,
  description nvarchar(max) NOT NULL,
  order_list smallint NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT instruments ON

INSERT INTO instruments (id, name, description, order_list) VALUES
(1, 'Collegamenti', 'Elenco interfacce e pagine disponibili', 1),
(2, 'Mime-Type', 'Elenco dei mime type con le estensioni di riferimento', 2);

SET IDENTITY_INSERT instruments OFF

-- --------------------------------------------------------

--
-- Struttura della tabella instruments_grp
--

CREATE TABLE instruments_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_instruments_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT instruments_grp ON

INSERT INTO instruments_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi. Possono inserire, modificare ed eliminare utenti con livello di accesso inferiore al loro.', 'no'),
(2, 'utilizzatori', 'Visualizzano gli strumenti', 'no');

SET IDENTITY_INSERT instruments_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella instruments_opt
--

CREATE TABLE instruments_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella instruments_usr
--

CREATE TABLE instruments_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)

-- --------------------------------------------------------

--
-- Struttura della tabella language
--

CREATE TABLE language (
  label nvarchar(10) NOT NULL,
  language nvarchar(50) NOT NULL DEFAULT '',
  code nvarchar(5) NOT NULL DEFAULT '',
  main nvarchar(5) NOT NULL 
  	CONSTRAINT CK_language_main CHECK (main IN('no','yes')) DEFAULT 'no',
  active nvarchar(5) NOT NULL 
  	CONSTRAINT CK_language_active CHECK (active IN('no','yes')) DEFAULT 'yes',
  flag nvarchar(20) DEFAULT NULL,
  PRIMARY KEY (code)
)

INSERT INTO language (label, language, code, main, active, flag) VALUES
('ITA', 'italiano', 'it_IT', 'yes', 'yes', NULL),
('ENG', 'english', 'en_US', 'no', 'yes', NULL),
('ESP', 'espanol', 'es_ES', 'no', 'yes', NULL),
('FRA', 'français', 'fr_FR', 'no', 'yes', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella language_opt
--

CREATE TABLE language_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  opt_flag tinyint NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT language_opt ON

INSERT INTO language_opt (id, instance, title, opt_flag) VALUES
(1, 0, 'Lingue', 0);

SET IDENTITY_INSERT language_opt OFF

-- --------------------------------------------------------

--
-- Struttura della tabella language_translation
--

CREATE TABLE language_translation (
  tbl_id_value int DEFAULT NULL,
  tbl nvarchar(200) DEFAULT NULL,
  field nvarchar(200) DEFAULT NULL,
  language nvarchar(5) DEFAULT NULL,
  text nvarchar(max)
)

INSERT INTO language_translation (tbl_id_value, tbl, field, language, text) VALUES
(1, 'page_layout', 'name', 'en_US', 'visual editor'),
(2, 'page_layout', 'name', 'en_US', 'media (image/video)'),
(3, 'page_layout', 'name', 'en_US', 'media left - editor right'),
(4, 'page_layout', 'name', 'en_US', 'editor left - media right'),
(5, 'page_layout', 'name', 'en_US', 'link to file'),
(8, 'page_layout', 'name', 'en_US', 'by file'),
(9, 'page_layout', 'name', 'en_US', 'by html code');

-- --------------------------------------------------------

--
-- Struttura della tabella nation
--

CREATE TABLE nation (
  id			smallint IDENTITY(1, 1),
  it_IT nvarchar(100) NOT NULL,
  en_US nvarchar(100) NOT NULL,
  fr_FR nvarchar(100) NOT NULL,
  onu date NOT NULL,
  PRIMARY KEY (id)
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
-- Struttura della tabella page_category
--

CREATE TABLE page_category (
  id			int IDENTITY(1, 1),
  name nvarchar(60) NOT NULL,
  description nvarchar(max),
  date datetime NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella page_comment
--

CREATE TABLE page_comment (
  id			int IDENTITY(1, 1),
  entry int NOT NULL,
  datetime datetime NOT NULL,
  author nvarchar(200) NOT NULL,
  email nvarchar(200) NOT NULL,
  web nvarchar(200) DEFAULT NULL,
  text nvarchar(max) NOT NULL,
  notification tinyint NOT NULL,
  reply int DEFAULT NULL,
  published tinyint NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella page_entry
--

CREATE TABLE page_entry (
  id			int IDENTITY(1, 1),
  category_id int NOT NULL,
  author int NOT NULL,
  creation_date datetime NOT NULL,
  last_edit_date datetime NOT NULL,
  title nvarchar(200) NOT NULL,
  slug nvarchar(200) NOT NULL UNIQUE,
  image nvarchar(200) DEFAULT NULL,
  url_image nvarchar(200) DEFAULT NULL,
  text nvarchar(max) NOT NULL,
  tags nvarchar(255) DEFAULT NULL,
  enable_comments tinyint NOT NULL,
  published tinyint NOT NULL,
  social tinyint NOT NULL,
  private tinyint NOT NULL,
  users nvarchar(255) NOT NULL,
  [read] int NOT NULL,
  tpl_code nvarchar(max),
  box_tpl_code nvarchar(max),
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT page_entry ON

INSERT INTO page_entry (id, category_id, author, creation_date, last_edit_date, title, slug, image, url_image, text, tags, enable_comments, published, social, private, users, [read], tpl_code, box_tpl_code) VALUES
(4, 0, 1, '2011-10-20 12:02:48', '2011-10-20 12:02:48', 'Che cos''è gino CMS', 'gino-CMS', NULL, NULL, '<p>\r\ngino CMS è uno dei framework open source sviluppati internamente da Otto, utilizzato al fine di offrire vari servizi ai nostri clienti.</p><p>È un <b>CMS</b>, acronimo di <i>Content Management System</i>, cioè un sistema di gestione dei contenuti web, creato appositamente per facilitarne l''organizzazione e la pubblicazione.</p>', '', 1, 1, 0, 0, '', 0, NULL, NULL),
(5, 0, 1, '2011-10-26 17:34:44', '2013-01-09 12:36:54', 'Tecnologia', 'tecnologia', NULL, NULL, '<p>gino nasce ed è ottimizzato per il buon vecchio server model <b>LAMP</b>.</p><p><img alt="LAMP logos" src="contents/attached/c1/lamp.jpg" /></p>', '', 1, 1, 0, 0, '', 0, NULL, NULL),
(7, 0, 1, '2011-10-28 15:17:39', '2013-01-09 12:42:41', 'Licenza', 'licenza', NULL, NULL, '<p><img alt="OSI approved license" src="contents/attached/c1/OSI_logo.jpg" style="margin-left: 10px; margin-right: 10px; float: left;" />Alla <a href="http://www.otto.to.it" rel="external">Otto</a> usiamo e produciamo software <a href="http://www.opensource.org/docs/osd" rel="external">open source</a>.</p><p>In particolare, gino CMS viene distribuito con licenza <a href="http://www.opensource.org/licenses/MIT" rel="external">MIT</a> (MIT).</p><p class="null"></p>', '', 1, 1, 0, 0, '', 0, NULL, NULL),
(8, 0, 1, '2011-11-01 09:59:14', '2013-01-09 12:45:31', 'Documentazione', 'documentazione', NULL, NULL, '<p>La documentazione e le reference di tutti i file sono ospitate su <b>github</b> sotto forma di <a href="https://github.com/otto-torino/gino/wiki" rel="external">wiki</a> che copre essenzialmente gli aspetti di sviluppo di gino.</p><p></p><p class="null"><img alt="github logo" src="contents/attached/c1/github.jpg" style="margin-left: 10px; margin-right: 10px; float: left;" />Per una documentazione più ampia, comprendente tutorial e how-to, potete fare riferimento alla pagina dedicata sul <a href="http://gino.otto.to.it" rel="external">sito ufficiale di gino</a>.</p><p class="null"></p>', '', 1, 1, 0, 0, '', 0, NULL, NULL),
(9, 0, 1, '2011-11-08 14:05:57', '2013-01-09 12:48:07', 'Estendere gino', 'estendere-gino', NULL, NULL, '<p><img alt="plugin" src="contents/attached/c1/plugin.jpg" style="margin-left: 10px; margin-right: 10px; float: left;" />Le funzionalità di gino possono essere ampliate utilizzando i moduli aggiuntivi disponibili. gino incorpora un meccanismo per il caricamento semplificato e l''aggiornamento di questi moduli.</p><p>Per un elenco dei moduli fate riferimento alla pagina sul <a href="http://gino.otto.to.it/" rel="external" title="Il link apre una nuova finestra">sito ufficiale di gino</a>.</p><p class="null"></p>', '', 1, 1, 0, 0, '', 0, NULL, NULL);

SET IDENTITY_INSERT page_entry OFF


-- --------------------------------------------------------

--
-- Struttura della tabella page_entry_tag
--

CREATE TABLE page_entry_tag (
  id			int IDENTITY(1, 1),
  entry int NOT NULL,
  tag int NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella page_grp
--

CREATE TABLE page_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_page_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT page_grp ON

INSERT INTO page_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'pubblicatori', 'Gestiscono la pubblicazione di pagine e commenti e la redazione', 'no'),
(3, 'redattori', 'Gestiscono la redazione', 'no'),
(4, 'utenti pagine private', 'Accedono alle pagine che sono state salvate come private', 'no');

SET IDENTITY_INSERT page_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella page_opt
--

CREATE TABLE page_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  last_title nvarchar(200) NOT NULL,
  archive_title nvarchar(200) NOT NULL,
  showcase_title nvarchar(200) NOT NULL,
  cloud_title nvarchar(200) NOT NULL,
  last_number smallint NOT NULL,
  last_tpl_code nvarchar(max) NOT NULL,
  showcase_number smallint NOT NULL,
  showcase_auto_start tinyint NOT NULL,
  showcase_auto_interval int NOT NULL,
  showcase_tpl_code nvarchar(max) NOT NULL,
  archive_efp int NOT NULL,
  archive_tpl_code nvarchar(max) NOT NULL,
  entry_tpl_code nvarchar(max) NOT NULL,
  box_tpl_code nvarchar(max) NOT NULL,
  comment_moderation tinyint NOT NULL,
  comment_notification tinyint NOT NULL,
  newsletter_entries_number smallint NOT NULL,
  newsletter_tpl_code nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT page_opt ON

INSERT INTO page_opt (id, instance, last_title, archive_title, showcase_title, cloud_title, last_number, last_tpl_code, showcase_number, showcase_auto_start, showcase_auto_interval, showcase_tpl_code, archive_efp, archive_tpl_code, entry_tpl_code, box_tpl_code, comment_moderation, comment_notification, newsletter_entries_number, newsletter_tpl_code) VALUES
(1, 0, 'Ultime pagine pubblicate', 'Pagine', 'In evidenza', 'Categorie', 3, '<article>\r\n<div class="left" style="padding-left:10px;">\r\n<h1>{{ title|link }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text|chars:300}}\r\n<div class="null"></div>\r\n<aside>\r\n<time><span class="date">{{ creation_date }}<span><br /><span class="time">{{ creation_time }}</span></time><p>\r\n{{ author_img|class:author }}</p>\r\n<p>Letto {{ read }} volte | Commenti ({{ comments }}) | <span class="tags">Tags: {{ tags }}</span>\r\n</p>\r\n</aside>\r\n</div>\r\n<div class="null"></div>\r\n</article>', 3, 1, 5000, '<article>\r\n<div class="left" style="padding-left:10px;">\r\n<h1>{{ title|link }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text|chars:300}}\r\n<div class="null"></div>\r\n<aside>\r\n<time><span class="date">{{ creation_date }}<span><br /><span class="time">{{ creation_time }}</span></time><p>\r\n{{ author_img|class:author }}</p>\r\n<p>Letto {{ read }} volte | Commenti ({{ comments }}) | <span class="tags">Tags: {{ tags }}</span>\r\n</p>\r\n</aside>\r\n</div>\r\n<div class="null"></div>\r\n</article>', 5, '<article>\r\n<div class="left" style="padding-left:10px;">\r\n<h1>{{ title|link }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text|chars:300}}\r\n<div class="null"></div>\r\n<aside>\r\n<time><span class="date">{{ creation_date }}<span><br /><span class="time">{{ creation_time }}</span></time><p>\r\n{{ author_img|class:author }}</p>\r\n<p>Letto {{ read }} volte | Commenti ({{ comments }}) | <span class="tags">Tags: {{ tags }}</span>\r\n</p>\r\n{{ social}}\r\n</aside>\r\n</div>\r\n<div class="null"></div>\r\n</article>', '<div class="left" style="padding-left:10px;">\r\n<h1>{{ title|link }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text }}\r\n<aside>\r\n<time><span class="date">{{ creation_date }}<span><br /><span class="time">{{ creation_time }}</span></time><p>\r\n{{ author_img|class:author }}</p>\r\n{{ social}}\r\n<p>Letto {{ read }} volte | Commenti ({{ comments }}) | <span class="tags">Tags: {{ tags }}</span>\r\n</p>\r\n</aside>\r\n</div>\r\n<div class="null"></div>', '<div class="left" style="padding-left:4px;">\r\n<h1>{{ title|link }}</h1>\r\n<p>{{ img|class:left }}</p>\r\n{{ text }}\r\n</div>\r\n<div class="null"></div>', 0, 1, 5, '');

SET IDENTITY_INSERT page_opt OFF

-- --------------------------------------------------------

--
-- Struttura della tabella page_tag
--

CREATE TABLE page_tag (
  id			int IDENTITY(1, 1),
  name nvarchar(128) NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella page_usr
--

CREATE TABLE page_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)

--
-- Dump dei dati per la tabella page_usr
--


-- --------------------------------------------------------

--
-- Struttura della tabella php_module
--

CREATE TABLE php_module (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  content nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT php_module ON

INSERT INTO php_module (id, instance, content) VALUES
(1, 6, '$lng = (isset($_SESSION[''lng''])) ? $_SESSION[''lng'']:''it_IT'';\r\n$access = new access();\r\n \r\n$buffer = "<div class=\\"topBar\\">";\r\n$buffer .= "<div class=\\"left\\">";\r\nif(pub::variable(''multi_language'')==''yes'') {\r\n  $query = "SELECT label, code, main FROM language WHERE active=''yes'' ORDER BY main DESC";\r\n  $a = $this->_db->selectquery($query);\r\n  $lng_buffer = array();\r\n  foreach($a as $b) {\r\n    if(isset($_SESSION[''lng''])) {\r\n      $selected = $_SESSION[''lng''] == $b[''code''] ? true : false;\r\n    }\r\n    else\r\n      $selected = $b[''main''] == ''yes'' ? true : false;\r\n    \r\n    if(!$selected) \r\n      $lng_buffer[]  =  "<a href=\\"index.php?lng=".$b[''code'']."\\">".htmlChars($b[''label''])."</a>";\r\n    else \r\n      $lng_buffer[]  =  "<a class=\\"selected\\">".htmlChars($b[''label''])."</a>";\r\n  }\r\n  \r\n  $buffer .= implode("", $lng_buffer); \r\n}\r\n$buffer .= "</div>";\r\nif(!isset($_SESSION[''userId''])) {\r\n    $buffer .= "<span class=\\"link\\" onclick=\\"login_toggle.toggle();\\">"._("Area riservata")."</span>";\r\n    $buffer .= "<div id=\\"login_registered\\" style=\\"display:none;\\">";\r\n    $buffer .= "<div>";\r\n    $buffer .= "<form method=\\"post\\" action=\\"index.php\\" style=\\"float:right\\">";\r\n    $buffer .= "<input type=\\"hidden\\" name=\\"action\\" value=\\"auth\\" />";\r\n    $buffer .= "<table class=\\"flt\\">";\r\n    $buffer .= "<tr>";\r\n    $buffer .= "<td class=\\"fl_label\\">User</td>";\r\n    $buffer .= "<td class=\\"fl_field\\"><input type=\\"text\\" name=\\"user\\" /></td>";\r\n    $buffer .= "</tr>";\r\n    $buffer .= "<tr>";\r\n    $buffer .= "<td class=\\"fl_label\\">Password</td>";\r\n    $buffer .= "<td class=\\"fl_field\\"><input type=\\"password\\" name=\\"pwd\\" /></td>";\r\n    $buffer .= "</tr>";\r\n    $buffer .= "<tr>";\r\n    $buffer .= "<td class=\\"fl_label\\"></td>";\r\n    $buffer .= "<td class=\\"fl_field\\"><input type=\\"submit\\" class=\\"generic\\" value=\\"login\\" /></td>";\r\n    $buffer .= "</tr>";\r\n    $buffer .= "</table>";\r\n    $buffer .= "</form>";\r\n    $buffer .= "<div class=\\"null\\"></div>";\r\n    $buffer .= "</div>";\r\n    $buffer .= "</div>";\r\n    $buffer .= "<script>var login_toggle = new Fx.Reveal(''login_registered'');</script>";\r\n}\r\nelse {\r\n    $admin_link = false;\r\n    if($access->getAccessAdmin()) {\r\n        $buffer .= "<a class=\\"aTopBar no_border\\" href=\\"admin.php\\">"._("Amministrazione")."</a>";\r\n        $admin_link = true;\r\n    }\r\n    $query = "SELECT CONCAT(firstname, '' '', lastname) AS name FROM user_app WHERE user_id=''".$_SESSION[''userId'']."''";\r\n    $a = $this->_db->selectquery($query);\r\n    $username = $a>0 ? $a[0][''name'']:null;\r\n    $buffer .= "<a class=\\"aTopBar".($admin_link ? "" : " no_border")."\\" href=\\"index.php?evt[user-userCard]\\"><span title=\\""._("Profilo utente")."\\" class=\\"tooltip\\">".$username."</span></a>";\r\n    $buffer .= "<a class=\\"aTopBar\\" href=\\"index.php?action=logout\\">"._("Logout")."</a>";\r\n    $buffer .= "<div class=\\"null\\"></div>";\r\n}\r\n$buffer .= "</div>";'),
(2, 9, '$buffer = "<div class=\\"topBar\\">";\r\n\r\n$index = new index();\r\n\r\n$sysMdls = $index->sysModulesManageArray();\r\n$mdls = $index->modulesManageArray();\r\n \r\nif(count($sysMdls)) {	\r\n  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";\r\n  $buffer .= "<select name=''sysmdl_menu'' onchange=\\"$onchange\\">";\r\n  $buffer .= "<option value=\\"\\">"._("Sistema")."</option>";\r\n  foreach($sysMdls as $sm) { \r\n    $buffer .= "<option value=\\"".$sm[''name'']."-manage".ucfirst($sm[''name''])."\\">".htmlChars($sm[''label''])."</option>";\r\n  }\r\n  $buffer .= "</select> ";\r\n}\r\n				\r\nif(count($mdls)) {\r\n  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";\r\n  $buffer .= "<select name=''mdl_menu'' onchange=\\"$onchange\\">";	\r\n  $buffer .= "<option value=\\"\\">"._("Moduli")."</option>";\r\n  foreach($mdls as $m) {\r\n    $buffer .= "<option value=\\"".$m[''name'']."-manageDoc\\">".htmlChars($m[''label''])."</option>";\r\n  }	\r\n  $buffer .= "</select>";\r\n}\r\n\r\n$buffer .= "</div>";');

SET IDENTITY_INSERT php_module OFF

-- --------------------------------------------------------

--
-- Struttura della tabella php_module_grp
--

CREATE TABLE php_module_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_php_module_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT php_module_grp ON

INSERT INTO php_module_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono i moduli php.', 'no');

SET IDENTITY_INSERT php_module_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella php_module_opt
--

CREATE TABLE php_module_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  title_vis tinyint NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella php_module_usr
--

CREATE TABLE php_module_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)

-- --------------------------------------------------------

--
-- Struttura della tabella search_site_opt
--

CREATE TABLE search_site_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  template nvarchar(max) NOT NULL,
  sys_mdl nvarchar(256) NOT NULL,
  inst_mdl nvarchar(256) NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella sys_conf
--

CREATE TABLE sys_conf (
  id			smallint IDENTITY(1, 1),
  user_role tinyint NOT NULL,
  admin_role tinyint NOT NULL,
  multi_language nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_conf_multi_language CHECK (multi_language IN('yes','no')) DEFAULT 'no',
  dft_language nvarchar(5) NOT NULL,
  precharge_mdl_url nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_conf_precharge_mdl_url CHECK (precharge_mdl_url IN('yes','no')),
  log_access nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_conf_log_access CHECK (log_access IN('yes','no')) DEFAULT 'no',
  head_description nvarchar(255) NOT NULL,
  head_keywords nvarchar(255) NOT NULL,
  head_title nvarchar(255) NOT NULL,
  google_analytics nvarchar(20) NOT NULL,
  captcha_public nvarchar(64) DEFAULT NULL,
  captcha_private nvarchar(64) DEFAULT NULL,
  email_name nvarchar(100) DEFAULT NULL,
  email_from_app nvarchar(100) DEFAULT NULL,
  mobile nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_conf_mobile CHECK (mobile IN('yes','no')) DEFAULT 'no',
  password_crypt nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_conf_password_crypt CHECK (password_crypt IN('none','sha1','md5')) DEFAULT 'none',
  email_admin nvarchar(100) DEFAULT NULL,
  enable_cache tinyint NOT NULL,
  permalinks nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_conf_permalinks CHECK (permalinks IN('yes','no')) DEFAULT 'yes',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_conf ON

INSERT INTO sys_conf (id, user_role, admin_role, multi_language, dft_language, precharge_mdl_url, log_access, head_description, head_keywords, head_title, google_analytics, captcha_public, captcha_private, email_name, email_from_app, mobile, password_crypt, email_admin, enable_cache, permalinks) VALUES
(1, 4, 2, 'no', 'it_IT', 'yes', 'yes', '', '', 'gino CMS', '', '', '', '', 'no-reply@otto.to.it', 'yes', 'md5', 'support@otto.to.it', 0, 'yes');

SET IDENTITY_INSERT sys_conf OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_graphics
--

CREATE TABLE sys_graphics (
  id			smallint IDENTITY(1, 1),
  name nvarchar(50) NOT NULL,
  description nvarchar(100) NOT NULL,
  type tinyint NOT NULL DEFAULT '1',
  image nvarchar(128) NOT NULL,
  html nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_graphics ON

INSERT INTO sys_graphics (id, name, description, type, image, html) VALUES
(1, 'header_public', 'Header pagine pubbliche', 1, 'header.jpg', ''),
(2, 'header_private', 'Header pagine private', 2, '', '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0" width="840" height="160" id="header" align="middle">\r\n	<param name="allowScriptAccess" value="sameDomain" />\r\n	<param name="allowFullScreen" value="false" />\r\n        <param name="wmode" value="transparent">\r\n	<param name="movie" value="_GRAPHICS_/header.swf" /><param name="quality" value="high" /><param name="bgcolor" value="#ffffff" /><embed src="_GRAPHICS_/header.swf" quality="high" bgcolor="#ffffff" width="840" height="160" wmode="transparent" name="header" align="middle" allowScriptAccess="sameDomain" allowFullScreen="false" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />\r\n	</object>'),
(3, 'header_admin', 'Header amministrazione', 1, 'header_admin.jpg', 'HEADER'),
(4, 'header_mobile', 'Header dispositivi mobili', 1, 'header_mobile.jpg', ''),
(5, 'header_adhoc', 'Header ad hoc', 2, 'pf2.jpg', 'HEADER'),
(6, 'footer_public', 'Footer index pubblica', 1, 'footer.jpg', ''),
(7, 'footer_private', 'Footer index privata', 2, '', '<p>\r\nheader\r\n</p>'),
(8, 'footer_admin', 'Footer amministrazione', 1, 'footer_admin.jpg', ''),
(9, 'footer_mobile', 'Footer dispositivi mobili', 1, 'footer_mobile.jpg', ''),
(10, 'footer_adhoc', 'Footer ad hoc', 1, '', 'FOOTER ADHOC');

SET IDENTITY_INSERT sys_graphics OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_graphics_grp
--

CREATE TABLE sys_graphics_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_graphics_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_graphics_grp ON

INSERT INTO sys_graphics_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Personalizzano l''header e il footer del sito.', 'no');

SET IDENTITY_INSERT sys_graphics_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_graphics_usr
--

CREATE TABLE sys_graphics_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)

-- --------------------------------------------------------

--
-- Struttura della tabella sys_image
--

CREATE TABLE sys_image (
  id			int IDENTITY(1, 1),
  name nvarchar(200) NOT NULL,
  description nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella sys_image_grp
--

CREATE TABLE sys_image_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_image_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_image_grp ON

INSERT INTO sys_image_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no');

SET IDENTITY_INSERT sys_image_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_image_usr
--

CREATE TABLE sys_image_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)

-- --------------------------------------------------------

--
-- Struttura della tabella sys_layout_css
--

CREATE TABLE sys_layout_css (
  id			int IDENTITY(1, 1),
  filename nvarchar(200) NOT NULL,
  label nvarchar(200) NOT NULL,
  description nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_css ON

INSERT INTO sys_layout_css (id, filename, label, description) VALUES
(1, 'mobile.css', 'Css per la visione mobile', ''),
(2, 'admin.css', 'Css area amministrativa', ''),
(3, 'home.css', 'Css gino base', 'Personalizza homepage e pagine di gino base');

SET IDENTITY_INSERT sys_layout_css OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_layout_grp
--

CREATE TABLE sys_layout_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_layout_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_grp ON

INSERT INTO sys_layout_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no');

SET IDENTITY_INSERT sys_layout_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_layout_skin
--

CREATE TABLE sys_layout_skin (
  id			int IDENTITY(1, 1),
  label nvarchar(200) NOT NULL,
  session nvarchar(128) NOT NULL,
  rexp nvarchar(200) NOT NULL,
  urls nvarchar(200) NOT NULL,
  template nvarchar(200) NOT NULL,
  css int NOT NULL,
  priority int NOT NULL,
  auth nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_layout_skin_auth CHECK (auth IN('yes','no','')),
  cache bigint NOT NULL DEFAULT '0',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_skin ON

INSERT INTO sys_layout_skin (id, label, session, rexp, urls, template, css, priority, auth, cache) VALUES
(1, 'Home Pubblica', '', '#index.php(\\?evt\\[index-index_page\\])?[^\\[\\]]*$#', '', '2', 3, 8, 'no', 0),
(2, 'Pagine Pubbliche', '', '#evt\\[(?!index)#', '', '3', 3, 6, 'no', 0),
(3, 'Home Amministrazione', '', '', 'index.php?evt[index-admin_page]', '5', 2, 5, 'yes', 0),
(4, 'Pagine Amministrazione', '', '#evt\\[\\w+-((manage)|(wrapper))\\w*\\]#', '', '6', 2, 4, 'yes', 0),
(5, 'Pagina Autenticazione', '', '', 'index.php?evt[index-auth_page]', '4', 3, 3, 'no', 0),
(6, 'Default', '', '#^.*$#', '', '1', 0, 10, '', 0),
(7, 'Pagine Private', '', '#evt\\[(?!index)#', '', '3', 3, 7, 'yes', 0),
(8, 'Home Privata', '', '#index.php(\\?evt\\[index-index_page\\])?[^\\[\\]]*$#', '', '2', 3, 9, 'yes', 0),
(9, 'Pagine Mobile', 'mobile=1', '#.*#', '', '8', 1, 2, '', 0),
(10, 'Home Mobile', 'mobile=1', '', 'index.php?mobile=1', '7', 1, 1, '', 0);

SET IDENTITY_INSERT sys_layout_skin OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_layout_tpl
--

CREATE TABLE sys_layout_tpl (
  id			int IDENTITY(1, 1),
  filename nvarchar(200) NOT NULL,
  label nvarchar(200) NOT NULL,
  description nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_tpl ON

INSERT INTO sys_layout_tpl (id, filename, label, description) VALUES
(1, 'default.tpl', 'Default', ''),
(2, 'home.tpl', 'Home', ''),
(3, 'pages.tpl', 'Pagine', ''),
(4, 'auth_page.tpl', 'Pagina Autenticazione', ''),
(5, 'home_admin.tpl', 'Home Area Amministrativa', ''),
(6, 'admin_pages.tpl', 'Pagine Area Amministrativa', ''),
(7, 'home_mobile.tpl', 'Home Mobile', ''),
(8, 'mobile_pages.tpl', 'Pagine Mobile', '');

SET IDENTITY_INSERT sys_layout_tpl OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_layout_tpl_block
--

CREATE TABLE sys_layout_tpl_block (
  id			int IDENTITY(1, 1),
  tpl int NOT NULL,
  position smallint NOT NULL,
  width int NOT NULL,
  um tinyint NOT NULL,
  align tinyint NOT NULL,
  rows smallint NOT NULL,
  cols smallint NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_layout_tpl_block ON

INSERT INTO sys_layout_tpl_block (id, tpl, position, width, um, align, rows, cols) VALUES
(52, 1, 2, 0, 0, 0, 1, 1),
(51, 1, 1, 0, 0, 0, 1, 1),
(50, 7, 1, 480, 1, 2, 1, 1),
(48, 6, 4, 0, 0, 0, 1, 1),
(47, 6, 3, 960, 1, 2, 1, 1),
(46, 6, 2, 0, 0, 0, 1, 1),
(45, 6, 1, 0, 0, 0, 1, 1),
(30, 2, 1, 0, 0, 0, 1, 1),
(31, 2, 2, 0, 0, 0, 1, 1),
(32, 2, 3, 960, 1, 2, 3, 3),
(33, 2, 4, 0, 0, 0, 1, 1),
(34, 2, 5, 960, 1, 2, 1, 2),
(35, 3, 1, 0, 0, 0, 1, 1),
(36, 3, 2, 0, 0, 0, 1, 1),
(37, 3, 3, 960, 1, 2, 2, 2),
(38, 3, 4, 0, 0, 0, 1, 1),
(39, 3, 5, 960, 1, 2, 1, 2),
(40, 5, 1, 0, 0, 0, 1, 1),
(41, 5, 2, 0, 0, 0, 1, 1),
(42, 5, 3, 960, 1, 2, 1, 1),
(43, 5, 4, 0, 0, 0, 1, 1),
(44, 5, 5, 960, 1, 2, 1, 2),
(49, 6, 5, 960, 1, 2, 1, 2),
(53, 1, 3, 960, 1, 2, 1, 2),
(54, 1, 4, 0, 0, 0, 1, 1),
(55, 1, 5, 960, 1, 2, 1, 2),
(56, 8, 1, 480, 1, 2, 1, 1),
(57, 4, 1, 0, 0, 0, 1, 1),
(58, 4, 2, 0, 0, 0, 1, 1),
(59, 4, 3, 960, 1, 2, 1, 4),
(60, 4, 4, 0, 0, 0, 1, 1),
(61, 4, 5, 960, 1, 2, 1, 2);

SET IDENTITY_INSERT sys_layout_tpl_block OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_layout_usr
--

CREATE TABLE sys_layout_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)

-- --------------------------------------------------------

--
-- Struttura della tabella sys_log_access
--

CREATE TABLE sys_log_access (
  id			int IDENTITY(1, 1),
  user_id int DEFAULT NULL,
  date datetime DEFAULT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_log_access ON

INSERT INTO sys_log_access (id, user_id, date) VALUES
(1, 1, '2012-07-30 14:46:56');

SET IDENTITY_INSERT sys_log_access OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_menu_grp
--

CREATE TABLE sys_menu_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_menu_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_menu_grp ON

INSERT INTO sys_menu_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono le voci di menu.', 'no');

SET IDENTITY_INSERT sys_menu_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_menu_opt
--

CREATE TABLE sys_menu_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  vis_title tinyint NOT NULL,
  home_voice nvarchar(50) NOT NULL,
  admin_voice nvarchar(50) NOT NULL,
  logout_voice nvarchar(50) NOT NULL,
  horizontal tinyint NOT NULL,
  click_event tinyint NOT NULL,
  initShowIcon tinyint NOT NULL,
  path_to_sel tinyint NOT NULL,
  cache bigint DEFAULT '0',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_menu_opt ON

INSERT INTO sys_menu_opt (id, instance, title, vis_title, home_voice, admin_voice, logout_voice, horizontal, click_event, initShowIcon, path_to_sel, cache) VALUES
(6, 4, 'Menu principale', 0, 'Home', 'Amministrazione', 'Logout', 1, 0, 0, 0, 0),
(7, 5, 'Menu amministrazione', 0, 'Home', 'Amministrazione', 'Logout', 1, 0, 0, 0, 0);

SET IDENTITY_INSERT sys_menu_opt OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_menu_usr
--

CREATE TABLE sys_menu_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)

-- --------------------------------------------------------

--
-- Struttura della tabella sys_menu_voices
--

CREATE TABLE sys_menu_voices (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  parent int NOT NULL,
  label nvarchar(200) NOT NULL,
  link nvarchar(200) NOT NULL,
  type nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_menu_voices_type CHECK (type IN('int','ext')),
  role1 tinyint NOT NULL,
  orderList smallint NOT NULL,
  authView tinyint NOT NULL,
  reference nvarchar(200) NOT NULL,
  voice nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_menu_voices_voice CHECK (voice IN('class','page')) DEFAULT 'page',
  page_id int NOT NULL,
  PRIMARY KEY (id)
)


-- --------------------------------------------------------

--
-- Struttura della tabella sys_module
--

CREATE TABLE sys_module (
  id			int IDENTITY(1, 1),
  label nvarchar(100) NOT NULL,
  name nvarchar(100) NOT NULL,
  class nvarchar(200) NOT NULL,
  type nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_module_type CHECK (type IN('class','func')) DEFAULT 'class',
  role1 smallint NOT NULL,
  role2 smallint NOT NULL,
  role3 smallint NOT NULL,
  directory nvarchar(200) DEFAULT NULL,
  masquerade nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_module_masquerade CHECK (masquerade IN('yes','no')) DEFAULT 'yes',
  role_group smallint NOT NULL,
  description nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_module ON

INSERT INTO sys_module (id, label, name, class, type, role1, role2, role3, directory, masquerade, role_group, description) VALUES
(1, 'Autenticazione formato tabella', 'tableLogin', '', 'func', 5, 5, 5, NULL, 'no', 0, 'Boxino di autenticazione in formato tabella'),
(2, 'Autenticazione', 'Autenticazione', '', 'func', 5, 5, 5, NULL, 'no', 0, 'Boxino di autenticazione'),
(3, 'Credits', 'credits', '', 'func', 5, 5, 5, NULL, 'no', 0, 'Credits'),
(4, 'Menu principale', 'mainMenu', 'menu', 'class', 5, 5, 5, NULL, 'no', 0, 'Menu principale'),
(5, 'Menu amministrazione', 'menu_admin', 'menu', 'class', 5, 5, 5, NULL, 'no', 0, 'Menu area amministrativa'),
(6, 'Top Bar', 'topbar', 'phpModuleView', 'class', 5, 5, 5, NULL, 'no', 0, 'Barra superiore con scelta lingua ed autenticazione'),
(9, 'Top Bar Admin', 'topbaradmin', 'phpModuleView', 'class', 4, 5, 5, NULL, 'no', 0, 'Barra superiore con link diretto all''amministrazione dei singoli moduli');

SET IDENTITY_INSERT sys_module OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_module_app
--

CREATE TABLE sys_module_app (
  id			int IDENTITY(1, 1),
  label nvarchar(100) NOT NULL,
  name nvarchar(100) NOT NULL,
  type nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_module_app_type CHECK (type IN('class','func')),
  role1 smallint NOT NULL,
  role2 smallint NOT NULL,
  role3 smallint NOT NULL,
  masquerade nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_module_app_masquerade CHECK (masquerade IN('yes','no')) DEFAULT 'yes',
  role_group smallint NOT NULL,
  tbl_name nvarchar(30) NOT NULL,
  order_list smallint NOT NULL,
  instance nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_module_app_instance CHECK (instance IN('yes','no')) DEFAULT 'no',
  description nvarchar(max) NOT NULL,
  removable nvarchar(5) NOT NULL 
  	CONSTRAINT CK_sys_module_app_removable CHECK (removable IN('yes','no')),
  class_version nvarchar(200) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_module_app ON

INSERT INTO sys_module_app (id, label, name, type, role1, role2, role3, masquerade, role_group, tbl_name, order_list, instance, description, removable, class_version) VALUES
(1, 'Impostazioni', 'sysconf', 'class', 2, 2, 2, 'no', 0, 'sys_conf', 1, 'no', 'Principali impostazioni di sistema', 'no', '1.0'),
(2, 'Lingue', 'language', 'class', 5, 2, 2, 'no', 0, 'language', 2, 'no', 'Gestione delle lingue disponibili per le traduzioni', 'no', '1.0'),
(3, 'Moduli di sistema', 'sysClass', 'class', 2, 2, 2, 'no', 0, 'sys_class', 3, 'no', 'Modifica, installazione e rimozione dei moduli di sistema', 'no', '1.0'),
(4, 'Moduli', 'module', 'class', 2, 2, 2, 'no', 0, 'sys_module', 4, 'no', 'Modifica, installazione e rimozione dei moduli di classi istanziate e moduli funzione', 'no', '1.0'),
(5, 'Utenti', 'user', 'class', 5, 5, 4, 'no', 1, 'user', 5, 'no', 'Gestione degli utenti di sistema', 'no', '1.0'),
(6, 'Statistiche', 'statistics', 'class', 2, 2, 2, 'no', 1, 'sys_stat', 6, 'no', 'Statistiche degli accessi all''area privata', 'no', '1.0'),
(7, 'Layout', 'layout', 'class', 2, 2, 2, 'no', 1, 'sys_layout', 7, 'no', 'Gestione di css, template, skin ed assegnazione a indirizzi o classi di indirizzi', 'no', '1.0'),
(8, 'Header e Footer', 'graphics', 'class', 5, 2, 2, 'no', 1, 'sys_graphics', 8, 'no', 'Gestione personalizzata degli header e footer del sistema', 'no', '1.0'),
(9, 'Allegati', 'attached', 'class', 4, 5, 4, 'no', 1, 'attached', 9, 'no', 'Archivi di file con struttura ad albero', 'no', '1.0'),
(10, 'Menu', 'menu', 'class', 5, 5, 4, 'no', 1, 'sys_menu', 10, 'yes', '', 'no', '1.0'),
(11, 'Pagine', 'page', 'class', 5, 5, 4, 'no', 1, 'page', 11, 'no', 'Pagine html con struttura ad albero', 'no', '1.0'),
(12, 'Index', 'index', 'class', 5, 4, 4, 'no', 1, '', 12, 'no', '', 'no', '1.0'),
(13, 'Generatore di immagini', 'imageGenerator', 'class', 2, 2, 2, 'no', 1, 'sys_image', 13, 'no', 'Generatore di immagini ', 'no', '1.0'),
(14, 'Ricerca nel sito', 'searchSite', 'class', 5, 5, 5, 'no', 1, 'search_site', 14, 'no', 'Form di ricerca nel sito', 'no', '1.0'),
(15, 'phpModuleView', 'phpModuleView', 'class', 0, 0, 0, 'no', 1, 'php_module', 15, 'yes', 'Generatore di moduli contenenti codice php', 'yes', '1.0'),
(16, 'Strumenti', 'instruments', 'class', 4, 4, 4, 'no', 1, 'instruments', 16, 'no', 'Alcuni strumenti, quali l''elenco delle risorse disponibili (con i relativi link) e dei mime type', 'no', '1.0');

SET IDENTITY_INSERT sys_module_app OFF

-- --------------------------------------------------------

--
-- Struttura della tabella sys_stat_opt
--

CREATE TABLE sys_stat_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT sys_stat_opt ON

INSERT INTO sys_stat_opt (id, instance, title) VALUES
(1, 0, 'Statistiche');

SET IDENTITY_INSERT sys_stat_opt OFF

-- --------------------------------------------------------

--
-- Struttura della tabella user_add
--

CREATE TABLE user_add (
  user_id int NOT NULL,
  field1 nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_add_field1 CHECK (field1 IN('yes','no')) DEFAULT 'no',
  field2 nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_add_field2 CHECK (field2 IN('yes','no')) DEFAULT 'no',
  field3 nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_add_field3 CHECK (field3 IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (user_id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella user_app
--

CREATE TABLE user_app (
  user_id			int IDENTITY(1, 1),
  firstname nvarchar(50) NOT NULL DEFAULT '',
  lastname nvarchar(50) NOT NULL DEFAULT '',
  company nvarchar(100) DEFAULT NULL,
  phone nvarchar(30) DEFAULT NULL,
  fax nvarchar(30) DEFAULT NULL,
  email nvarchar(100) NOT NULL DEFAULT '',
  username nvarchar(50) NOT NULL,
  userpwd nvarchar(100) NOT NULL,
  address nvarchar(200) DEFAULT NULL,
  cap int DEFAULT NULL,
  city nvarchar(50) DEFAULT NULL,
  nation smallint DEFAULT NULL,
  text nvarchar(max),
  photo nvarchar(50) DEFAULT NULL,
  pub nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_app_pub CHECK (pub IN('no','yes')) DEFAULT 'no',
  role smallint NOT NULL DEFAULT '0',
  date datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  valid nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_app_valid CHECK (valid IN('yes','no')) DEFAULT 'yes',
  privacy nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_app_privacy CHECK (privacy IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (user_id)
)

SET IDENTITY_INSERT user_app ON

INSERT INTO user_app (user_id, firstname, lastname, company, phone, fax, email, username, userpwd, address, cap, city, nation, text, photo, pub, role, date, valid, privacy) VALUES
(1, 'utente', 'amministratore', 'otto srl', '+39 011 8987553', '', 'support@otto.to.it', 'amministratore', '1844156d4166d94387f1a4ad031ca5fa', 'via Mazzini 37', 10123, 'Torino', 83, '', '', 'yes', 1, '2011-10-10 01:00:00', 'yes', 'no');

SET IDENTITY_INSERT user_app OFF

-- --------------------------------------------------------

--
-- Struttura della tabella user_email
--

CREATE TABLE user_email (
  id			int IDENTITY(1, 1),
  ref_function smallint NOT NULL UNIQUE,
  description nvarchar(255) NOT NULL,
  subject nvarchar(200) NOT NULL,
  text nvarchar(max) NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT user_email ON

INSERT INTO user_email (id, ref_function, description, subject, text) VALUES
(1, 1, 'email inviata a un utente quando si registra autonomamente e viene automaticamente attivato', '', ''),
(2, 2, 'email inviata a un utente quando si registra autonomamente e non viene automaticamente attivato', '', '');

SET IDENTITY_INSERT user_email OFF

-- --------------------------------------------------------

--
-- Struttura della tabella user_grp
--

CREATE TABLE user_grp (
  id			smallint IDENTITY(1, 1),
  name nvarchar(100) NOT NULL,
  description nvarchar(max) NOT NULL,
  no_admin nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_grp_no_admin CHECK (no_admin IN('yes','no')) DEFAULT 'no',
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT user_grp ON

INSERT INTO user_grp (id, name, description, no_admin) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi. Possono inserire, modificare ed eliminare utenti con livello di accesso inferiore al loro.', 'no'),
(2, 'assistenti', 'Gestiscono gli utenti. Possono inserire e modificare utenti. Hanno restrizioni sulla modifica dei livelli di accesso e delle password. Non possono eliminare nessun utente.', 'no');

SET IDENTITY_INSERT user_grp OFF

-- --------------------------------------------------------

--
-- Struttura della tabella user_opt
--

CREATE TABLE user_opt (
  id			int IDENTITY(1, 1),
  instance int NOT NULL,
  title nvarchar(200) NOT NULL,
  more_info tinyint NOT NULL,
  media_info tinyint NOT NULL,
  user_card_view tinyint NOT NULL,
  aut_valid tinyint NOT NULL,
  users_for_page smallint NOT NULL,
  aut_registration tinyint NOT NULL,
  mod_email tinyint NOT NULL,
  username_email tinyint NOT NULL,
  aut_pwd tinyint NOT NULL,
  pwd_length smallint NOT NULL,
  pwd_min_length smallint NOT NULL,
  pwd_max_length smallint NOT NULL,
  pwd_number int NOT NULL,
  PRIMARY KEY (id)
)

SET IDENTITY_INSERT user_opt ON

INSERT INTO user_opt (id, instance, title, more_info, media_info, user_card_view, aut_valid, users_for_page, aut_registration, mod_email, username_email, aut_pwd, pwd_length, pwd_min_length, pwd_max_length, pwd_number) VALUES
(1, 0, 'Utenti', 0, 1, 1, 1, 10, 0, 1, 0, 0, 10, 6, 14, 2);

SET IDENTITY_INSERT user_opt OFF

-- --------------------------------------------------------

--
-- Struttura della tabella user_registration
--

CREATE TABLE user_registration (
  id			int IDENTITY(1, 1),
  user_id int DEFAULT NULL,
  session nvarchar(50) DEFAULT NULL,
  PRIMARY KEY (id)
)

-- --------------------------------------------------------

--
-- Struttura della tabella user_role
--

CREATE TABLE user_role (
  role_id			smallint IDENTITY(1, 1),
  name nvarchar(20) NOT NULL DEFAULT '',
  identifier nvarchar(10) NOT NULL DEFAULT '',
  default_value nvarchar(5) NOT NULL 
  	CONSTRAINT CK_user_role_default_value CHECK (default_value IN('no','yes')) DEFAULT 'no',
  PRIMARY KEY (role_id)
)

SET IDENTITY_INSERT user_role ON

INSERT INTO user_role (role_id, name, identifier, default_value) VALUES
(1, 'system administrator', 'sysadmin', 'no'),
(2, 'administrator', 'admin', 'no'),
(3, 'poweruser', 'power', 'no'),
(4, 'user', 'user', 'no'),
(5, 'free access', 'free', 'yes');

SET IDENTITY_INSERT user_role OFF

-- --------------------------------------------------------

--
-- Struttura della tabella user_usr
--

CREATE TABLE user_usr (
  instance int NOT NULL,
  group_id smallint NOT NULL,
  user_id int NOT NULL
)
