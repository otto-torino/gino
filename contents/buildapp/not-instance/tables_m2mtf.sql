
--
-- Table structure for table `{{TABLEKEY}}_category`
--

CREATE TABLE `{{TABLEKEY}}_category` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `{{TABLEKEY}}_{{MODELREFERENCE}}`
--

CREATE TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}` (
  `id` int(11) NOT NULL,
  `insertion_date` datetime NOT NULL,
  `last_edit_date` datetime NOT NULL,
  `date` date NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `description` text,
  `tags` varchar(255) DEFAULT NULL,
  `published` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `{{TABLEKEY}}_{{MODELREFERENCE}}_category`
--

CREATE TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}_category` (
  `id` int(11) NOT NULL,
  `{{MODELREFERENCE}}_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `{{TABLEKEY}}_{{MODELREFERENCE}}_{{M2MTFMODELREFERENCE}}`
--

CREATE TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}_{{M2MTFMODELREFERENCE}}` (
  `id` int(11) NOT NULL,
  `{{MODELREFERENCE}}_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `file` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `{{TABLEKEY}}_opt`
--

CREATE TABLE `{{TABLEKEY}}_opt` (
  `id` int(11) NOT NULL,
  `instance` int(11) NOT NULL,
  `items_for_page` smallint(3) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Indexes for dumped tables
--

--
-- Indexes for table `{{TABLEKEY}}_category`
--
ALTER TABLE `{{TABLEKEY}}_category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `{{TABLEKEY}}_{{MODELREFERENCE}}`
--
ALTER TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `{{TABLEKEY}}_{{MODELREFERENCE}}_category`
--
ALTER TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}_category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `{{TABLEKEY}}_{{MODELREFERENCE}}_{{M2MTFMODELREFERENCE}}`
--
ALTER TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}_{{M2MTFMODELREFERENCE}}`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `{{TABLEKEY}}_opt`
--
ALTER TABLE `{{TABLEKEY}}_opt`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `{{TABLEKEY}}_category`
--
ALTER TABLE `{{TABLEKEY}}_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `{{TABLEKEY}}_{{MODELREFERENCE}}`
--
ALTER TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `{{TABLEKEY}}_{{MODELREFERENCE}}_category`
--
ALTER TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `{{TABLEKEY}}_{{MODELREFERENCE}}_{{M2MTFMODELREFERENCE}}`
--
ALTER TABLE `{{TABLEKEY}}_{{MODELREFERENCE}}_{{M2MTFMODELREFERENCE}}`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `{{TABLEKEY}}_opt`
--
ALTER TABLE `{{TABLEKEY}}_opt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('{{TABLEKEY}}', 'can_admin', 'Amministrazione modulo', 'Amministrazione completa del modulo', 1);

