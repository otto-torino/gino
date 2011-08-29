
--
-- Table structure for table `menu_grp`
--

CREATE TABLE IF NOT EXISTS `menu_grp` (
  `id` int(2) NOT NULL auto_increment,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `no_admin` enum('yes','no') NOT NULL default 'no',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `menu_grp`
--

INSERT INTO `menu_grp` (`id`, `name`, `description`, `no_admin`) VALUES
(1, 'responsabili', 'Gestiscono l''assegnazione degli utenti ai singoli gruppi.', 'no'),
(2, 'assistenti', 'Gestiscono le voci di menu.', 'no');

-- --------------------------------------------------------

--
-- Table structure for table `menu_opt`
--

CREATE TABLE IF NOT EXISTS `menu_opt` (
  `id` int(11) NOT NULL auto_increment,
  `instance` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `vis_title` tinyint(1) NOT NULL,
  `home_voice` tinyint(1) NOT NULL,
  `admin_voice` tinyint(1) NOT NULL,
  `logout_voice` tinyint(1) NOT NULL,
  `horizontal` tinyint(1) NOT NULL,
  `click_event` tinyint(1) NOT NULL,
  `initShowIcon` tinyint(1) NOT NULL,
  `path_to_sel` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `menu_opt`
--

INSERT INTO `menu_opt` (`id`, `instance`, `title`, `vis_title`, `home_voice`, `admin_voice`, `logout_voice`, `horizontal`, `click_event`, `initShowIcon`, `path_to_sel`) VALUES
(1, 'advancedMenu', 'Menu', 0, 1, 1, 0, 0, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `menu_usr`
--

CREATE TABLE IF NOT EXISTS `menu_usr` (
  `group_id` int(2) NOT NULL,
  `user_id` int(11) NOT NULL,
  `instance` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `menu_usr`
--


-- --------------------------------------------------------

--
-- Table structure for table `menu_voices`
--

CREATE TABLE IF NOT EXISTS `menu_voices` (
  `id` int(11) NOT NULL auto_increment,
  `menuInstance` varchar(200) NOT NULL,
  `parent` int(11) NOT NULL,
  `label` varchar(200) NOT NULL,
  `link` varchar(200) NOT NULL,
  `type` enum('int','ext') NOT NULL,
  `role1` int(1) NOT NULL,
  `orderList` int(3) NOT NULL,
  `authView` int(1) NOT NULL,
  `reference` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



