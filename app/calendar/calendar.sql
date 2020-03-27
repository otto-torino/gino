-- phpMyAdmin SQL Dump
-- Application: calendar

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
('calendar', 'can_admin', 'amministrazione', 'Amministrazione completa del modulo', 1);

