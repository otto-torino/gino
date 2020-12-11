
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

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buildapp_item`
--
ALTER TABLE `buildapp_item`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buildapp_item`
--
ALTER TABLE `buildapp_item`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('buildapp', 'can_admin', 'amministrazione', 'Amministrazione completa del modulo di creazione applicazioni', 1);
