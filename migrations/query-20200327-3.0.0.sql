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

--
-- Table structure for table `post_category`
--

CREATE TABLE `post_category` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text,
  `image` varchar(200) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `post_item`
--

CREATE TABLE `post_item` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `insertion_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `date` date NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `text` text,
  `tags` varchar(255) DEFAULT NULL,
  `img` varchar(100) DEFAULT NULL,
  `attachment` varchar(100) DEFAULT NULL,
  `private` tinyint(1) NOT NULL DEFAULT '0',
  `social` tinyint(1) NOT NULL DEFAULT '0',
  `slideshow` tinyint(1) NOT NULL DEFAULT '0',
  `published` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `post_item_category`
--

CREATE TABLE `post_item_category` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `post_opt`
--

CREATE TABLE `post_opt` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `last_post_number` int(11) NOT NULL,
  `last_slideshow_view` tinyint(1) NOT NULL DEFAULT '0',
  `last_slideshow_number` tinyint(2) DEFAULT NULL,
  `list_nfp` smallint(2) DEFAULT NULL,
  `showcase_post_number` smallint(2) DEFAULT NULL,
  `showcase_auto_start` tinyint(1) NOT NULL,
  `showcase_auto_interval` int(8) NOT NULL,
  `evidence_number` smallint(2) DEFAULT NULL,
  `evidence_auto_start` tinyint(1) DEFAULT NULL,
  `evidence_auto_interval` int(8) DEFAULT NULL,
  `image_width` smallint(4) DEFAULT NULL,
  `newsletter_post_number` smallint(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `post_category`
--
ALTER TABLE `post_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `post_item`
--
ALTER TABLE `post_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `post_item_category`
--
ALTER TABLE `post_item_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `post_opt`
--
ALTER TABLE `post_opt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `post_category`
--
ALTER TABLE `post_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `post_item`
--
ALTER TABLE `post_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `post_item_category`
--
ALTER TABLE `post_item_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `post_opt`
--
ALTER TABLE `post_opt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



-- --------------------------------------------------------

--
-- Table structure for table `calendar_category`
--

CREATE TABLE `calendar_category` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL DEFAULT '0',
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_item`
--

CREATE TABLE `calendar_item` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `date` date NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `duration` smallint(4) NOT NULL DEFAULT '1',
  `description` text,
  `time_start` time NOT NULL,
  `time_end` time NOT NULL,
  `place` int(11) DEFAULT NULL,
  `author` int(11) NOT NULL,
  `insertion_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `calendar_item_category`
--

CREATE TABLE `calendar_item_category` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_opt`
--

CREATE TABLE `calendar_opt` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `monday_first_week_day` tinyint(1) NOT NULL,
  `day_chars` tinyint(2) NOT NULL,
  `open_modal` tinyint(1) NOT NULL,
  `items_for_page` smallint(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `calendar_place`
--

CREATE TABLE `calendar_place` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `calendar_category`
--
ALTER TABLE `calendar_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `calendar_item`
--
ALTER TABLE `calendar_item`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `calendar_item_category`
--
ALTER TABLE `calendar_item_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendar_opt`
--
ALTER TABLE `calendar_opt`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `calendar_place`
--
ALTER TABLE `calendar_place`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `calendar_category`
--
ALTER TABLE `calendar_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `calendar_item`
--
ALTER TABLE `calendar_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `calendar_item_category`
--
ALTER TABLE `calendar_item_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `calendar_opt`
--
ALTER TABLE `calendar_opt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `calendar_place`
--
ALTER TABLE `calendar_place`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('post', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1),
('post', 'can_publish', 'pubblicazione', 'pubblica ed elimina i post', 1),
( 'post', 'can_write', 'redazione', 'inserisce e modifica i post ma non li può pubblicare o eliminare', 1),
('post', 'can_view_private', 'visualizzazione post privati', 'visualizzazione dei post privati', 0),
('calendar', 'can_admin', 'amministrazione', 'Amministrazione completa del modulo', 1);

-- --------------------------------------------------------

INSERT INTO `sys_layout_tpl` (`filename`, `label`, `description`, `free`) VALUES
('login.php', 'Login', 'Pagina di login', 1);

DROP TABLE sys_layout_tpl_block";

-- --------------------------------------------------------

-- INSTALL THE POST AND CALENDAR APPLICATIONS
-- run the first query and retrieve the id value to insert in the second query

-- INSERT INTO `sys_module_app` (`id`, `label`, `name`, `active`, `tbl_name`, `instantiable`, `description`, `removable`, `class_version`) VALUES (19, 'Post', 'post', '1', 'post', '1', 'Gestore di post con categorie', '1', '3.0.0');
-- INSERT INTO `sys_module` (`id`, `label`, `name`, `module_app`, `active`, `description`) VALUES (NULL, 'Articoli', 'article', [ID-sys_module_app], '1', 'Gestore di articoli');

-- INSERT INTO `sys_module_app` (`id`, `label`, `name`, `active`, `tbl_name`, `instantiable`, `description`, `removable`, `class_version`) VALUES (20, 'Calendar', 'calendar', '1', 'calendar', '1', 'Calendario appuntamenti', '1', '1.0.0');
-- INSERT INTO `sys_module` (`id`, `label`, `name`, `module_app`, `active`, `description`) VALUES (NULL, 'Calendario', 'cal', [ID-sys_module_app], '1', NULL);






