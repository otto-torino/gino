-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generato il: 11 Ott, 2011 at 12:03 PM
-- Versione MySQL: 5.0.51
-- Versione PHP: 5.2.6-1+lenny4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `dbgino`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `attached`
--

CREATE TABLE IF NOT EXISTS `attached` (
  `id` int(11) NOT NULL auto_increment,
  `category` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

--
-- Dump dei dati per la tabella `attached`
--

INSERT INTO `attached` (`id`, `category`, `name`) VALUES
(1, 1, 'lamp.jpg'),
(2, 1, 'OSI_logo.jpg'),
(3, 1, 'github.jpg'),
(4, 1, 'plugin.jpg');

-- --------------------------------------------------------

--
-- Struttura della tabella `attached_ctg`
--

CREATE TABLE IF NOT EXISTS `attached_ctg` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `directory` varchar(20) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `attached_ctg`
--

INSERT INTO `attached_ctg` (`id`, `name`, `directory`) VALUES
(1, 'various', 'c1');

-- --------------------------------------------------------

--
-- Struttura della tabella `attached_grp`
--

CREATE TABLE IF NOT EXISTS `attached_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `attached_grp`
--

INSERT INTO `attached_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono gli allegati.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `attached_opt`
--

CREATE TABLE IF NOT EXISTS `attached_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `opt_ctg` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `attached_opt`
--

INSERT INTO `attached_opt` (`id`, `instance`, `title`, `opt_ctg`) VALUES
(1, 0, 'Allegati', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `attached_usr`
--

CREATE TABLE IF NOT EXISTS `attached_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `attached_usr`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `language`
--

CREATE TABLE IF NOT EXISTS `language` (
  `label` varchar(10) NOT NULL,
  `language` varchar(50) NOT NULL default '',
  `code` varchar(5) NOT NULL default '',
  `main` enum('no','yes') NOT NULL default 'no',
  `active` enum('no','yes') NOT NULL default 'yes',
  `flag` varchar(20) default NULL,
  PRIMARY KEY  (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `language`
--

INSERT INTO `language` (`label`, `language`, `code`, `main`, `active`, `flag`) VALUES
('ITA', 'italiano', 'it_IT', 'yes', 'yes', NULL),
('ENG', 'english', 'en_US', 'no', 'yes', NULL),
('ESP', 'espanol', 'es_ES', 'no', 'yes', NULL),
('FRA', 'français', 'fr_FR', 'no', 'yes', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `language_opt`
--

CREATE TABLE IF NOT EXISTS `language_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `opt_flag` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `language_opt`
--

INSERT INTO `language_opt` (`id`, `instance`, `title`, `opt_flag`) VALUES
(1, 0, 'Lingue', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `language_translation`
--

CREATE TABLE IF NOT EXISTS `language_translation` (
  `tbl_id_value` int(11) default NULL,
  `tbl` varchar(200) default NULL,
  `field` varchar(200) default NULL,
  `language` varchar(5) default NULL,
  `text` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `language_translation`
--

INSERT INTO `language_translation` (`tbl_id_value`, `tbl`, `field`, `language`, `text`) VALUES
(1, 'page_layout', 'name', 'en_US', 'text only'),
(2, 'page_layout', 'name', 'en_US', 'media only'),
(3, 'page_layout', 'name', 'en_US', 'media left - text right'),
(4, 'page_layout', 'name', 'en_US', 'text left - media right'),
(5, 'page_layout', 'name', 'en_US', 'link to file'),
(8, 'page_layout', 'name', 'en_US', 'by file'),
(9, 'page_layout', 'name', 'en_US', 'by html code');

-- --------------------------------------------------------

--
-- Struttura della tabella `nation`
--

CREATE TABLE IF NOT EXISTS `nation` (
  `id` smallint(4) NOT NULL auto_increment,
  `it_IT` varchar(100) NOT NULL,
  `en_US` varchar(100) NOT NULL,
  `fr_FR` varchar(100) NOT NULL,
  `onu` date NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=193 ;

--
-- Dump dei dati per la tabella `nation`
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
(192, 'Zimbabwe', 'Zimbabwe', 'Zimbabwe', '1980-08-25');

-- --------------------------------------------------------

--
-- Struttura della tabella `page`
--

CREATE TABLE IF NOT EXISTS `page` (
  `item_id` int(11) NOT NULL auto_increment,
  `module` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `title` varchar(200) NOT NULL,
  `subtitle` text NOT NULL,
  `view_title` enum('yes','no') NOT NULL default 'yes',
  `social` enum('yes','no') NOT NULL default 'no',
  `cache` int(16) default '0',
  PRIMARY KEY  (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dump dei dati per la tabella `page`
--

INSERT INTO `page` (`item_id`, `module`, `parent`, `date`, `title`, `subtitle`, `view_title`, `social`, `cache`) VALUES
(4, 10, 0, '2011-09-16 10:06:30', 'Gino CMS', '', 'yes', 'no', 0),
(5, 11, 0, '2011-09-16 11:33:18', 'Tecnologia', '', 'yes', 'no', 0),
(7, 13, 0, '2011-09-16 15:43:40', 'Licenza', '', 'yes', 'no', 0),
(8, 14, 0, '2011-09-16 16:12:21', 'Documentazione', '', 'yes', 'no', 0),
(9, 15, 0, '2011-09-16 16:34:49', 'Estendere Gino', '', 'yes', 'no', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `page_block`
--

CREATE TABLE IF NOT EXISTS `page_block` (
  `content_id` int(11) NOT NULL auto_increment,
  `item` int(11) NOT NULL default '0',
  `layout` smallint(1) NOT NULL,
  `text` text,
  `img` varchar(100) default NULL,
  `link` varchar(100) default NULL,
  `filename` varchar(100) default NULL,
  `order_list` int(2) NOT NULL default '0',
  PRIMARY KEY  (`content_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dump dei dati per la tabella `page_block`
--

INSERT INTO `page_block` (`content_id`, `item`, `layout`, `text`, `img`, `link`, `filename`, `order_list`) VALUES
(3, 4, 1, '<p>\r\n	Gino CMS è uno dei <b>framework open source</b> sviluppati internamente da Otto, che utilizziamo per offrire servizi ai nostri clienti.</p>\r\n<p>\r\n	È un <b>CMS</b>, acronimo di <b>Content Management System</b>, cioè un sistema di gestione dei contenuti web creato appositamente per <b>facilitarne la gestione</b>.</p>', '', '', '', 1),
(4, 5, 1, '<p>\r\n	<b>Gino</b> nasce ed è ottimizzato per il buon vecchio server model <b>LAMP</b>.</p>\r\n<p>\r\n	<img alt="LAMP logos" src="contents/attached/c1/lamp.jpg" style="width: 300px; height: 259px; " /></p>', '', '', '', 1),
(6, 7, 1, '<p>\r\n	<img alt="OSI approved license" src="contents/attached/c1/OSI_logo.jpg" style="margin-left: 10px; margin-right: 10px; float: left; width: 100px; height: 137px; " />Alla <a href="http://www.otto.to.it" rel="external">Otto</a> usiamo e produciamo software <a href="http://www.opensource.org/docs/osd" rel="external">open source</a>. In particolare, Gino CMS viene distribuito con licenza <a href="http://www.opensource.org/licenses/MIT" rel="external">MIT</a> (MIT).</p>\r\n<p class="null">\r\n	 </p>', '', '', '', 1),
(7, 8, 1, '<p>\r\n	Per iniziare ad esplorare le viscere del codice di Gino non c''è niente di meglio che tuffarsi nel <a href="#">wiki</a> che manuteniamo su <b>github</b>.</p>\r\n<p class="null">\r\n	<img alt="github logo" src="contents/attached/c1/github.jpg" style="margin-left: 10px; margin-right: 10px; float: left; width: 120px; height: 161px; " />Esiste anche un <a href="#">manuale utente</a>, che copre invece le problematiche di uso pratico dal backoffice.</p>\r\n<p class="null">\r\n	 </p>', '', '', '', 1),
(8, 9, 1, '<p>\r\n	<img alt="plugin" src="contents/attached/c1/plugin.jpg" style="margin-left: 10px; margin-right: 10px; float: left; width: 128px; height: 128px; " />Le funzionalità di Gino possono essere notevolmente espanse utilizzando i numerosi <b>moduli aggiuntivi</b> disponibili. Gino incorpora un meccanismo per il <b>caricamento</b> semplificato e l''<b>aggiornamento</b> dei moduli aggiuntivi.</p>\r\n<p class="null">\r\n	 </p>', '', '', '', 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `page_block_add`
--

CREATE TABLE IF NOT EXISTS `page_block_add` (
  `id` int(11) NOT NULL auto_increment,
  `content_id` int(11) NOT NULL,
  `media_width` int(4) NOT NULL,
  `media_height` int(4) NOT NULL,
  `media_alt_text` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=9 ;

--
-- Dump dei dati per la tabella `page_block_add`
--

INSERT INTO `page_block_add` (`id`, `content_id`, `media_width`, `media_height`, `media_alt_text`) VALUES
(3, 3, 0, 0, ''),
(4, 4, 0, 0, ''),
(6, 6, 0, 0, ''),
(7, 7, 0, 0, ''),
(8, 8, 0, 0, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `page_block_file`
--

CREATE TABLE IF NOT EXISTS `page_block_file` (
  `id` int(11) NOT NULL auto_increment,
  `reference` int(11) NOT NULL,
  `filename` varchar(100) NOT NULL,
  `description` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `page_block_file`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `page_grp`
--

CREATE TABLE IF NOT EXISTS `page_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dump dei dati per la tabella `page_grp`
--

INSERT INTO `page_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'redazione', 'Gestisce la redazione delle pagine: inserimento, modifica ed eliminazione.', 'no'),
(3, 'redazione contenuti', 'Gestisce i contenuti delle pagine.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `page_layout`
--

CREATE TABLE IF NOT EXISTS `page_layout` (
  `id` smallint(1) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `img` varchar(20) NOT NULL,
  `default_value` enum('no','yes') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

--
-- Dump dei dati per la tabella `page_layout`
--

INSERT INTO `page_layout` (`id`, `name`, `img`, `default_value`) VALUES
(1, 'solo testo', '', 'yes'),
(2, 'solo media (immagini e video)', '', 'no'),
(3, 'media a sinistra - testo a destra', '', 'no'),
(4, 'testo a sinistra - media a destra', '', 'no'),
(5, 'link a un file', '', 'no'),
(8, 'da file', '', 'no'),
(9, 'da codice html', '', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `page_opt`
--

CREATE TABLE IF NOT EXISTS `page_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `block_title` tinyint(1) NOT NULL default '0',
  `block_chars` int(6) NOT NULL,
  `read_all` tinyint(1) NOT NULL default '0',
  `block_media` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `page_opt`
--

INSERT INTO `page_opt` (`id`, `instance`, `title`, `block_title`, `block_chars`, `read_all`, `block_media`) VALUES
(1, 0, 'Pagine', 0, 300, 1, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `page_usr`
--

CREATE TABLE IF NOT EXISTS `page_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `page_usr`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `php_module`
--

CREATE TABLE IF NOT EXISTS `php_module` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `php_module`
--

INSERT INTO `php_module` (`id`, `instance`, `content`) VALUES
(1, 6, '$lng = (isset($_SESSION[''lng''])) ? $_SESSION[''lng'']:''it_IT'';\r\n$access = new access();\r\n \r\n$buffer = "<div class=\\"topBar\\">";\r\n$buffer .= "<div class=\\"left\\">";\r\nif(pub::variable(''multi_language'')==''yes'') {\r\n  $query = "SELECT label, code, main FROM language WHERE active=''yes'' ORDER BY main DESC";\r\n  $a = $this->_db->selectquery($query);\r\n  $lng_buffer = array();\r\n  foreach($a as $b) {\r\n    if(isset($_SESSION[''lng''])) {\r\n      $selected = $_SESSION[''lng''] == $b[''code''] ? true : false;\r\n    }\r\n    else\r\n      $selected = $b[''main''] == ''yes'' ? true : false;\r\n    \r\n    if(!$selected) \r\n      $lng_buffer[]  =  "<a href=\\"index.php?lng=".$b[''code'']."\\">".htmlChars($b[''label''])."</a>";\r\n    else \r\n      $lng_buffer[]  =  "<a class=\\"selected\\">".htmlChars($b[''label''])."</a>";\r\n  }\r\n  \r\n  $buffer .= implode("", $lng_buffer); \r\n}\r\n$buffer .= "</div>";\r\nif(!isset($_SESSION[''userId''])) {\r\n    $buffer .= "<span class=\\"link\\" onclick=\\"login_toggle.toggle();\\">"._("Area riservata")."</span>";\r\n    $buffer .= "<div id=\\"login_registered\\" style=\\"display:none;\\">";\r\n    $buffer .= "<div>";\r\n    $buffer .= "<form method=\\"post\\" action=\\"index.php\\" style=\\"float:right\\">";\r\n    $buffer .= "<input type=\\"hidden\\" name=\\"action\\" value=\\"auth\\" />";\r\n    $buffer .= "<table class=\\"flt\\">";\r\n    $buffer .= "<tr>";\r\n    $buffer .= "<td class=\\"fl_label\\">User</td>";\r\n    $buffer .= "<td class=\\"fl_field\\"><input type=\\"text\\" name=\\"user\\" /></td>";\r\n    $buffer .= "</tr>";\r\n    $buffer .= "<tr>";\r\n    $buffer .= "<td class=\\"fl_label\\">Password</td>";\r\n    $buffer .= "<td class=\\"fl_field\\"><input type=\\"password\\" name=\\"pwd\\" /></td>";\r\n    $buffer .= "</tr>";\r\n    $buffer .= "<tr>";\r\n    $buffer .= "<td class=\\"fl_label\\"></td>";\r\n    $buffer .= "<td class=\\"fl_field\\"><input type=\\"submit\\" class=\\"generic\\" value=\\"login\\" /></td>";\r\n    $buffer .= "</tr>";\r\n    $buffer .= "</table>";\r\n    $buffer .= "</form>";\r\n    $buffer .= "<div class=\\"null\\"></div>";\r\n    $buffer .= "</div>";\r\n    $buffer .= "</div>";\r\n    $buffer .= "<script>var login_toggle = new Fx.Reveal(''login_registered'');</script>";\r\n}\r\nelse {\r\n    $admin_link = false;\r\n    if($access->getAccessAdmin()) {\r\n        $buffer .= "<a class=\\"aTopBar no_border\\" href=\\"admin.php\\">"._("Amministrazione")."</a>";\r\n        $admin_link = true;\r\n    }\r\n    $query = "SELECT CONCAT(firstname, '' '', lastname) AS name FROM user_app WHERE user_id=''".$_SESSION[''userId'']."''";\r\n    $a = $this->_db->selectquery($query);\r\n    $username = $a>0 ? $a[0][''name'']:null;\r\n    $buffer .= "<a class=\\"aTopBar".($admin_link ? "" : " no_border")."\\" href=\\"index.php?evt[user-userCard]\\"><span title=\\""._("Profilo utente")."\\" class=\\"tooltip\\">".$username."</span></a>";\r\n    $buffer .= "<a class=\\"aTopBar\\" href=\\"index.php?action=logout\\">"._("Logout")."</a>";\r\n    $buffer .= "<div class=\\"null\\"></div>";\r\n}\r\n$buffer .= "</div>";'),
(2, 9, '$buffer = "<div class=\\"topBar\\">";\r\n\r\n$index = new index();\r\n\r\n$sysMdls = $index->sysModulesManageArray();\r\n$mdls = $index->modulesManageArray();\r\n \r\nif(count($sysMdls)) {	\r\n  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";\r\n  $buffer .= "<select name=''sysmdl_menu'' onchange=\\"$onchange\\">";\r\n  $buffer .= "<option value=\\"\\">"._("Sistema")."</option>";\r\n  foreach($sysMdls as $sm) { \r\n    $buffer .= "<option value=\\"".$sm[''name'']."-manage".ucfirst($sm[''name''])."\\">".htmlChars($sm[''label''])."</option>";\r\n  }\r\n  $buffer .= "</select> ";\r\n}\r\n				\r\nif(count($mdls)) {\r\n  $onchange = "location.href=''$this->_home?evt[''+$(this).value+'']'';";\r\n  $buffer .= "<select name=''mdl_menu'' onchange=\\"$onchange\\">";	\r\n  $buffer .= "<option value=\\"\\">"._("Moduli")."</option>";\r\n  foreach($mdls as $m) {\r\n    $buffer .= "<option value=\\"".$m[''name'']."-manageDoc\\">".htmlChars($m[''label''])."</option>";\r\n  }	\r\n  $buffer .= "</select>";\r\n}\r\n\r\n$buffer .= "</div>";');

-- --------------------------------------------------------

--
-- Struttura della tabella `php_module_grp`
--

CREATE TABLE IF NOT EXISTS `php_module_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `php_module_grp`
--

INSERT INTO `php_module_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono i moduli php.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `php_module_opt`
--

CREATE TABLE IF NOT EXISTS `php_module_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(200) NOT NULL,
  `title` varchar(200) NOT NULL,
  `title_vis` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `php_module_opt`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `php_module_usr`
--

CREATE TABLE IF NOT EXISTS `php_module_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `php_module_usr`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `search_site_opt`
--

CREATE TABLE IF NOT EXISTS `search_site_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `template` text NOT NULL,
  `sys_mdl` varchar(256) NOT NULL,
  `inst_mdl` varchar(256) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `search_site_opt`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_conf`
--

CREATE TABLE IF NOT EXISTS `sys_conf` (
  `id` int(2) NOT NULL auto_increment,
  `user_role` int(1) NOT NULL,
  `admin_role` int(1) NOT NULL,
  `multi_language` enum('yes','no') NOT NULL default 'no',
  `dft_language` varchar(5) NOT NULL,
  `precharge_mdl_url` enum('yes','no') NOT NULL,
  `log_access` enum('yes','no') NOT NULL default 'no',
  `head_description` varchar(255) NOT NULL,
  `head_keywords` varchar(255) NOT NULL,
  `head_title` varchar(255) NOT NULL,
  `google_analytics` varchar(20) NOT NULL,
  `captcha_public` varchar(64) default NULL,
  `captcha_private` varchar(64) default NULL,
  `email_name` varchar(100) default NULL,
  `email_from_app` varchar(100) default NULL,
  `mobile` enum('yes','no') NOT NULL default 'no',
  `password_crypt` enum('none','sha1','md5') default 'none',
  `email_admin` varchar(100) default NULL,
  `enable_cache` int(1) NOT NULL,
  `permalinks` enum('yes','no') NOT NULL default 'yes',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `sys_conf`
--

INSERT INTO `sys_conf` (`id`, `user_role`, `admin_role`, `multi_language`, `dft_language`, `precharge_mdl_url`, `log_access`, `head_description`, `head_keywords`, `head_title`, `google_analytics`, `captcha_public`, `captcha_private`, `email_name`, `email_from_app`, `mobile`, `password_crypt`, `email_admin`, `enable_cache`, `permalinks`) VALUES
(1, 4, 2, 'no', 'it_IT', 'yes', 'yes', '', '', 'Gino 1.0b', '', '', '', '', 'no-reply@otto.to.it', 'yes', 'md5', 'support@otto.to.it', 0, 'yes');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_graphics`
--

CREATE TABLE IF NOT EXISTS `sys_graphics` (
  `id` smallint(2) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `type` int(1) NOT NULL default '1',
  `image` varchar(128) NOT NULL,
  `html` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC AUTO_INCREMENT=11 ;

--
-- Dump dei dati per la tabella `sys_graphics`
--

INSERT INTO `sys_graphics` (`id`, `name`, `description`, `type`, `image`, `html`) VALUES
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

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_graphics_grp`
--

CREATE TABLE IF NOT EXISTS `sys_graphics_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `sys_graphics_grp`
--

INSERT INTO `sys_graphics_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Personalizzano l''header e il footer del sito.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_graphics_usr`
--

CREATE TABLE IF NOT EXISTS `sys_graphics_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `sys_graphics_usr`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_image`
--

CREATE TABLE IF NOT EXISTS `sys_image` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `sys_image`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_image_grp`
--

CREATE TABLE IF NOT EXISTS `sys_image_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `sys_image_grp`
--

INSERT INTO `sys_image_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_image_usr`
--

CREATE TABLE IF NOT EXISTS `sys_image_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `sys_image_usr`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_layout_css`
--

CREATE TABLE IF NOT EXISTS `sys_layout_css` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(200) NOT NULL,
  `label` varchar(200) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `sys_layout_css`
--

INSERT INTO `sys_layout_css` (`id`, `filename`, `label`, `description`) VALUES
(1, 'mobile.css', 'Css per la visione mobile', ''),
(2, 'admin.css', 'Css area amministrativa', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_layout_grp`
--

CREATE TABLE IF NOT EXISTS `sys_layout_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `sys_layout_grp`
--

INSERT INTO `sys_layout_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_layout_skin`
--

CREATE TABLE IF NOT EXISTS `sys_layout_skin` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(200) NOT NULL,
  `rexp` varchar(200) NOT NULL,
  `urls` varchar(2000) NOT NULL,
  `template` varchar(200) NOT NULL,
  `css` int(200) NOT NULL,
  `priority` int(11) NOT NULL,
  `auth` enum('yes','no','') NOT NULL,
  `cache` int(16) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=11 ;

--
-- Dump dei dati per la tabella `sys_layout_skin`
--

INSERT INTO `sys_layout_skin` (`id`, `label`, `rexp`, `urls`, `template`, `css`, `priority`, `auth`, `cache`) VALUES
(1, 'Home Pubblica', '#index.php(\\?evt\\[index-index_page\\])?[^\\[\\]]*$#', '', '2', 0, 8, 'no', 0),
(2, 'Pagine Pubbliche', '#evt\\[(?!index)#', '', '3', 0, 6, 'no', 0),
(3, 'Home Amministrazione', '', 'index.php?evt[index-admin_page]', '5', 2, 5, 'yes', 0),
(4, 'Pagine Amministrazione', '#evt\\[\\w+-((manage)|(wrapper))\\w*\\]#', '', '6', 2, 4, 'yes', 0),
(5, 'Pagina Autenticazione', '', 'index.php?evt[index-auth_page]', '4', 0, 3, 'no', 0),
(6, 'Default', '#^.*$#', '', '1', 0, 10, '', 0),
(7, 'Pagine Private', '#evt\\[(?!index)#', '', '3', 0, 7, 'yes', 0),
(8, 'Home Privata', '#index.php(\\?evt\\[index-index_page\\])?[^\\[\\]]*$#', '', '2', 0, 9, 'yes', 0),
(9, 'Pagine Mobile', '#mobile=1(&.*)?$#', '', '8', 1, 2, '', 0),
(10, 'Home Mobile', '#mobile=1(&.*)?$#', '', '7', 1, 1, 'yes', 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_layout_tpl`
--

CREATE TABLE IF NOT EXISTS `sys_layout_tpl` (
  `id` int(11) NOT NULL auto_increment,
  `filename` varchar(200) NOT NULL,
  `label` varchar(200) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dump dei dati per la tabella `sys_layout_tpl`
--

INSERT INTO `sys_layout_tpl` (`id`, `filename`, `label`, `description`) VALUES
(1, 'default.tpl', 'Default', ''),
(2, 'home.tpl', 'Home', ''),
(3, 'pages.tpl', 'Pagine', ''),
(4, 'auth_page.tpl', 'Pagina Autenticazione', ''),
(5, 'home_admin.tpl', 'Home Area Amministrativa', ''),
(6, 'admin_pages.tpl', 'Pagine Area Amministrativa', ''),
(7, 'home_mobile.tpl', 'Home Mobile', ''),
(8, 'mobile_pages.tpl', 'Pagine Mobile', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_layout_tpl_block`
--

CREATE TABLE IF NOT EXISTS `sys_layout_tpl_block` (
  `id` int(11) NOT NULL auto_increment,
  `tpl` int(4) NOT NULL,
  `position` int(2) NOT NULL,
  `width` int(4) NOT NULL,
  `um` int(1) NOT NULL,
  `align` int(1) NOT NULL,
  `rows` int(2) NOT NULL,
  `cols` int(2) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=62 ;

--
-- Dump dei dati per la tabella `sys_layout_tpl_block`
--

INSERT INTO `sys_layout_tpl_block` (`id`, `tpl`, `position`, `width`, `um`, `align`, `rows`, `cols`) VALUES
(52, 1, 2, 0, 0, 0, 1, 1),
(51, 1, 1, 0, 0, 0, 1, 1),
(50, 7, 1, 480, 1, 2, 1, 1),
(48, 6, 4, 0, 0, 0, 1, 1),
(47, 6, 3, 960, 1, 2, 1, 1),
(46, 6, 2, 0, 0, 0, 1, 1),
(45, 6, 1, 0, 0, 0, 1, 1),
(30, 2, 1, 0, 0, 0, 1, 1),
(31, 2, 2, 0, 0, 0, 1, 1),
(32, 2, 3, 960, 1, 2, 3, 4),
(33, 2, 4, 0, 0, 0, 1, 1),
(34, 2, 5, 960, 1, 2, 1, 2),
(35, 3, 1, 0, 0, 0, 1, 1),
(36, 3, 2, 0, 0, 0, 1, 1),
(37, 3, 3, 960, 1, 2, 2, 4),
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

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_layout_usr`
--

CREATE TABLE IF NOT EXISTS `sys_layout_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `sys_layout_usr`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_log_access`
--

CREATE TABLE IF NOT EXISTS `sys_log_access` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `date` datetime default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `sys_log_access`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_menu_grp`
--

CREATE TABLE IF NOT EXISTS `sys_menu_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `sys_menu_grp`
--

INSERT INTO `sys_menu_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono le voci di menu.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_menu_opt`
--

CREATE TABLE IF NOT EXISTS `sys_menu_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `vis_title` tinyint(1) NOT NULL,
  `home_voice` varchar(50) NOT NULL,
  `admin_voice` varchar(50) NOT NULL,
  `logout_voice` varchar(50) NOT NULL,
  `horizontal` tinyint(1) NOT NULL,
  `click_event` tinyint(1) NOT NULL,
  `initShowIcon` tinyint(1) NOT NULL,
  `path_to_sel` tinyint(1) NOT NULL,
  `cache` int(16) default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8 ;

--
-- Dump dei dati per la tabella `sys_menu_opt`
--

INSERT INTO `sys_menu_opt` (`id`, `instance`, `title`, `vis_title`, `home_voice`, `admin_voice`, `logout_voice`, `horizontal`, `click_event`, `initShowIcon`, `path_to_sel`, `cache`) VALUES
(6, 4, 'Menu principale', 0, 'Home', 'Amministrazione', 'Logout', 1, 0, 0, 0, 0),
(7, 5, 'Menu amministrazione', 0, 'Home', 'Amministrazione', 'Logout', 1, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_menu_usr`
--

CREATE TABLE IF NOT EXISTS `sys_menu_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `sys_menu_usr`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_menu_voices`
--

CREATE TABLE IF NOT EXISTS `sys_menu_voices` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `parent` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `link` varchar(200) NOT NULL,
  `type` enum('int','ext') NOT NULL,
  `role1` int(1) NOT NULL,
  `orderList` int(3) NOT NULL,
  `authView` int(1) NOT NULL,
  `reference` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `sys_menu_voices`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `sys_module`
--

CREATE TABLE IF NOT EXISTS `sys_module` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(100) character set latin1 NOT NULL,
  `name` varchar(100) character set latin1 NOT NULL,
  `class` varchar(200) NOT NULL,
  `type` enum('page','class','func') character set latin1 NOT NULL default 'page',
  `role1` smallint(2) NOT NULL,
  `role2` smallint(2) NOT NULL,
  `role3` smallint(2) NOT NULL,
  `directory` varchar(200) character set latin1 default NULL,
  `masquerade` enum('yes','no') character set latin1 NOT NULL default 'yes',
  `role_group` int(2) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dump dei dati per la tabella `sys_module`
--

INSERT INTO `sys_module` (`id`, `label`, `name`, `class`, `type`, `role1`, `role2`, `role3`, `directory`, `masquerade`, `role_group`, `description`) VALUES
(1, 'Autenticazione formato tabella', 'tableLogin', '', 'func', 5, 5, 5, NULL, 'no', 0, 'Boxino di autenticazione in formato tabella'),
(2, 'Autenticazione', 'Autenticazione', '', 'func', 5, 5, 5, NULL, 'no', 0, 'Boxino di autenticazione'),
(3, 'Credits', 'credits', '', 'func', 5, 5, 5, NULL, 'no', 0, 'Credits'),
(4, 'Menu principale', 'mainMenu', 'menu', 'class', 5, 5, 5, NULL, 'no', 0, 'Menu principale'),
(5, 'Menu amministrazione', 'menu_admin', 'menu', 'class', 5, 5, 5, NULL, 'no', 0, 'Menu area amministrativa'),
(6, 'Top Bar', 'topbar', 'phpModuleView', 'class', 5, 5, 5, NULL, 'no', 0, 'Barra superiore con scelta lingua ed autenticazione'),
(9, 'Top Bar Admin', 'topbaradmin', 'phpModuleView', 'class', 4, 5, 5, NULL, 'no', 0, 'Barra superiore con link diretto aall''amministrazione dei singoli moduli'),
(10, 'Gino CMS', '', '', 'page', 5, 5, 5, '10', 'no', 0, ''),
(11, 'Tecnologia', '', '', 'page', 5, 5, 5, '11', 'no', 0, ''),
(13, 'Licenza', '', '', 'page', 5, 5, 5, '13', 'no', 0, ''),
(14, 'Documentazione', '', '', 'page', 5, 5, 5, '14', 'no', 0, ''),
(15, 'Estendere Gino', '', '', 'page', 5, 5, 5, '15', 'no', 0, '');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_module_app`
--

CREATE TABLE IF NOT EXISTS `sys_module_app` (
  `id` int(11) NOT NULL auto_increment,
  `label` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('class','page','func') NOT NULL,
  `role1` smallint(2) NOT NULL,
  `role2` smallint(2) NOT NULL,
  `role3` smallint(2) NOT NULL,
  `masquerade` enum('yes','no') NOT NULL default 'yes',
  `role_group` int(2) NOT NULL,
  `tbl_name` varchar(30) NOT NULL,
  `order_list` smallint(2) NOT NULL,
  `instance` enum('yes','no') NOT NULL default 'no',
  `description` text NOT NULL,
  `removable` enum('yes','no') NOT NULL,
  `class_version` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dump dei dati per la tabella `sys_module_app`
--

INSERT INTO `sys_module_app` (`id`, `label`, `name`, `type`, `role1`, `role2`, `role3`, `masquerade`, `role_group`, `tbl_name`, `order_list`, `instance`, `description`, `removable`, `class_version`) VALUES
(1, 'Impostazioni', 'sysconf', 'class', 2, 2, 2, 'no', 0, 'sys_conf', 1, 'no', 'Principali impostazioni di sistema', 'no', '0.9b'),
(2, 'Lingue', 'language', 'class', 5, 2, 2, 'no', 0, 'language', 2, 'no', 'Gestione delle lingue disponibili per le traduzioni', 'no', '0.9b'),
(3, 'Moduli di sistema', 'sysClass', 'class', 2, 2, 2, 'no', 0, 'sys_class', 3, 'no', 'Modifica, installazione e rimozione dei moduli di sistema', 'no', '0.9b'),
(4, 'Moduli', 'module', 'class', 2, 2, 2, 'no', 0, 'sys_module', 4, 'no', 'Modifica, installazione e rimozione dei moduli di classi istanziate e moduli funzione', 'no', '0.9b'),
(5, 'Utenti', 'user', 'class', 5, 5, 4, 'no', 1, 'user', 5, 'no', 'Gestione degli utenti di sistema', 'no', '0.9b'),
(6, 'Statistiche', 'statistics', 'class', 2, 2, 2, 'no', 1, 'sys_stat', 6, 'no', 'Statistiche degli accessi all''area privata', 'no', '0.9b'),
(7, 'Layout', 'layout', 'class', 2, 2, 2, 'no', 1, 'sys_layout', 7, 'no', 'Gestione di css, template, skin ed assegnazione a indirizzi o classi di indirizzi', 'no', '0.9b'),
(8, 'Header e Footer', 'graphics', 'class', 5, 2, 2, 'no', 1, 'sys_graphics', 8, 'no', 'Gestione personalizzata degli header e footer del sistema', 'no', '0.9b'),
(9, 'Allegati', 'attached', 'class', 4, 5, 4, 'no', 1, 'attached', 9, 'no', 'Archivi di file con struttura ad albero', 'no', '0.9b'),
(10, 'Menu', 'menu', 'class', 5, 5, 4, 'no', 1, 'sys_menu', 10, 'yes', '', 'no', '0.9b'),
(11, 'Pagine', 'page', 'class', 5, 5, 4, 'no', 1, 'page', 11, 'no', 'Pagine html con struttura ad albero', 'no', '0.9b'),
(12, 'Index', 'index', 'class', 5, 4, 4, 'no', 1, 'news', 12, 'no', '', 'no', '0.9b'),
(13, 'Generatore di immagini', 'imageGenerator', 'class', 2, 2, 2, 'no', 1, 'sys_image', 13, 'no', 'Generatore di immagini ', 'no', '0.9b'),
(14, 'Ricerca nel sito', 'searchSite', 'class', 5, 5, 5, 'no', 1, 'search_site', 14, 'no', 'Form di ricerca nel sito', 'no', '1.0'),
(15, 'phpModuleView', 'phpModuleView', 'class', 0, 0, 0, 'no', 1, 'php_module', 15, 'yes', 'Generatore di moduli contenenti codice php', 'yes', '0.9b');

-- --------------------------------------------------------

--
-- Struttura della tabella `sys_stat_opt`
--

CREATE TABLE IF NOT EXISTS `sys_stat_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `sys_stat_opt`
--

INSERT INTO `sys_stat_opt` (`id`, `instance`, `title`) VALUES
(1, 0, 'Statistiche');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_add`
--

CREATE TABLE IF NOT EXISTS `user_add` (
  `user_id` int(11) NOT NULL,
  `field1` enum('yes','no') NOT NULL default 'no',
  `field2` enum('yes','no') NOT NULL default 'no',
  `field3` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `user_add`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `user_app`
--

CREATE TABLE IF NOT EXISTS `user_app` (
  `user_id` int(11) NOT NULL auto_increment,
  `firstname` varchar(50) NOT NULL default '',
  `lastname` varchar(50) NOT NULL default '',
  `company` varchar(100) default NULL,
  `phone` varchar(30) default NULL,
  `fax` varchar(30) default NULL,
  `email` varchar(100) NOT NULL default '',
  `username` varchar(50) NOT NULL,
  `userpwd` varchar(100) NOT NULL,
  `address` varchar(200) default NULL,
  `cap` int(5) default NULL,
  `city` varchar(50) default NULL,
  `nation` smallint(4) default NULL,
  `text` text,
  `photo` varchar(50) default NULL,
  `pub` enum('no','yes') NOT NULL default 'no',
  `role` smallint(2) NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `valid` enum('yes','no') NOT NULL default 'yes',
  `privacy` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `user_app`
--

INSERT INTO `user_app` (`user_id`, `firstname`, `lastname`, `company`, `phone`, `fax`, `email`, `username`, `userpwd`, `address`, `cap`, `city`, `nation`, `text`, `photo`, `pub`, `role`, `date`, `valid`, `privacy`) VALUES
(1, 'utente', 'amministratore', 'otto srl', '+39 011 8987553', '', 'support@otto.to.it', 'amministratore', '1844156d4166d94387f1a4ad031ca5fa', 'via Mazzini 37', 10123, 'Torino', 83, '', '', 'yes', 1, '2011-10-10 01:00:00', 'yes', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_email`
--

CREATE TABLE IF NOT EXISTS `user_email` (
  `id` int(11) NOT NULL auto_increment,
  `ref_function` smallint(2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `text` text NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `ref_function` (`ref_function`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `user_email`
--

INSERT INTO `user_email` (`id`, `ref_function`, `description`, `subject`, `text`) VALUES
(1, 1, 'email inviata a un utente quando si registra autonomamente e viene automaticamente attivato', '', ''),
(2, 2, 'email inviata a un utente quando si registra autonomamente e non viene automaticamente attivato', '', '');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_grp`
--

CREATE TABLE IF NOT EXISTS `user_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dump dei dati per la tabella `user_grp`
--

INSERT INTO `user_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi. Possono inserire, modificare ed eliminare utenti con livello di accesso inferiore al loro.', 'no'),
(2, 'assistenti', 'Gestiscono gli utenti. Possono inserire e modificare utenti. Hanno restrizioni sulla modifica dei livelli di accesso e delle password. Non possono eliminare nessun utente.', 'no');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_opt`
--

CREATE TABLE IF NOT EXISTS `user_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `more_info` tinyint(1) NOT NULL,
  `media_info` tinyint(1) NOT NULL,
  `user_card_view` tinyint(1) NOT NULL,
  `aut_valid` tinyint(1) NOT NULL,
  `users_for_page` int(3) NOT NULL,
  `aut_registration` tinyint(1) NOT NULL,
  `mod_email` tinyint(1) NOT NULL,
  `username_email` tinyint(1) NOT NULL,
  `aut_pwd` tinyint(1) NOT NULL,
  `pwd_length` int(2) NOT NULL,
  `pwd_min_length` int(2) NOT NULL,
  `pwd_max_length` int(2) NOT NULL,
  `pwd_number` int(10) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dump dei dati per la tabella `user_opt`
--

INSERT INTO `user_opt` (`id`, `instance`, `title`, `more_info`, `media_info`, `user_card_view`, `aut_valid`, `users_for_page`, `aut_registration`, `mod_email`, `username_email`, `aut_pwd`, `pwd_length`, `pwd_min_length`, `pwd_max_length`, `pwd_number`) VALUES
(1, 0, 'Utenti', 0, 1, 1, 1, 10, 0, 1, 0, 0, 10, 6, 14, 2);

-- --------------------------------------------------------

--
-- Struttura della tabella `user_registration`
--

CREATE TABLE IF NOT EXISTS `user_registration` (
  `id` int(11) NOT NULL auto_increment,
  `user_id` int(11) default NULL,
  `session` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Dump dei dati per la tabella `user_registration`
--


-- --------------------------------------------------------

--
-- Struttura della tabella `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `role_id` smallint(2) NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `identifier` varchar(10) NOT NULL default '',
  `default_value` enum('no','yes') NOT NULL default 'no',
  PRIMARY KEY  (`role_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dump dei dati per la tabella `user_role`
--

INSERT INTO `user_role` (`role_id`, `name`, `identifier`, `default_value`) VALUES
(1, 'system administrator', 'sysadmin', 'no'),
(2, 'administrator', 'admin', 'no'),
(3, 'poweruser', 'power', 'no'),
(4, 'user', 'user', 'no'),
(5, 'free access', 'free', 'yes');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_usr`
--

CREATE TABLE IF NOT EXISTS `user_usr` (
  `instance` int(11) NOT NULL,
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Dump dei dati per la tabella `user_usr`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
