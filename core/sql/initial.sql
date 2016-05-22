-- phpMyAdmin SQL Dump
-- version 4.2.10
-- http://www.phpmyadmin.net
--
-- Host: localhost:8889
-- Generation Time: May 23, 2016 at 12:23 AM
-- Server version: 5.5.38
-- PHP Version: 5.6.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `cj_main`
--

-- --------------------------------------------------------

--
-- Table structure for table `cj_admin_users`
--

CREATE TABLE `cj_admin_users` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inactive',
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tokenGeneratedAt` timestamp NULL DEFAULT NULL,
  `timeZone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Australia/Melbourne',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `cj_admin_users`
--

INSERT INTO `cj_admin_users` (`id`, `name`, `username`, `email`, `phone`, `password`, `status`, `token`, `tokenGeneratedAt`, `timeZone`, `createdAt`, `modifiedAt`, `live`, `archivedAt`) VALUES
(1, 'admin', 'admin', 'youremail@youremail.com', '04166970663', '$2y$10$xQtVTySowkdOB0marP/bkedgBKrkmd.RDwPzl.1NSbKJbr5quL4N2', 'active', NULL, NULL, 'Australia/Melbourne', '2015-11-29 11:41:43', '2016-05-22 22:19:13', '1', NULL);

--
-- Triggers `cj_admin_users`
--
DELIMITER //
CREATE TRIGGER `updateModifiedAtAdminUsers` BEFORE UPDATE ON `cj_admin_users`
 FOR EACH ROW SET NEW.modifiedAt = CURRENT_TIMESTAMP
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cj_group_member_user_xref`
--

CREATE TABLE `cj_group_member_user_xref` (
`id` int(11) unsigned NOT NULL,
  `groupId` int(11) unsigned NOT NULL,
  `memberId` int(11) unsigned NOT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Triggers `cj_group_member_user_xref`
--
DELIMITER //
CREATE TRIGGER `updateModifiedAtGroupMemberUserXref` BEFORE UPDATE ON `cj_group_member_user_xref`
 FOR EACH ROW SET NEW.modifiedAt = CURRENT_TIMESTAMP
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cj_member_groups`
--

CREATE TABLE `cj_member_groups` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `status` enum('active','inactive','suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'active',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Triggers `cj_member_groups`
--
DELIMITER //
CREATE TRIGGER `updateModifiedAtMemberGroups` BEFORE UPDATE ON `cj_member_groups`
 FOR EACH ROW SET NEW.modifiedAt = CURRENT_TIMESTAMP
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `cj_member_users`
--

CREATE TABLE `cj_member_users` (
`id` int(10) unsigned NOT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `parentId` int(10) unsigned NOT NULL DEFAULT '0',
  `status` enum('active','inactive','suspended') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inactive',
  `token` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `tokenGeneratedAt` timestamp NULL DEFAULT NULL,
  `timeZone` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'Australia/Melbourne',
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifiedAt` timestamp NULL DEFAULT NULL,
  `live` enum('1') COLLATE utf8_unicode_ci DEFAULT '1',
  `archivedAt` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=207 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Triggers `cj_member_users`
--
DELIMITER //
CREATE TRIGGER `updateModifiedAtMemberUsers` BEFORE UPDATE ON `cj_member_users`
 FOR EACH ROW SET NEW.modifiedAt = CURRENT_TIMESTAMP
//
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cj_admin_users`
--
ALTER TABLE `cj_admin_users`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cj_group_member_user_xref`
--
ALTER TABLE `cj_group_member_user_xref`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `Unique relation` (`groupId`,`memberId`,`live`), ADD KEY `ehsan_group_member_user_xref_ibfk_2` (`memberId`);

--
-- Indexes for table `cj_member_groups`
--
ALTER TABLE `cj_member_groups`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `Unique Name` (`name`,`live`);

--
-- Indexes for table `cj_member_users`
--
ALTER TABLE `cj_member_users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `Unique Username` (`username`,`live`), ADD UNIQUE KEY `Unique Email` (`email`,`live`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cj_admin_users`
--
ALTER TABLE `cj_admin_users`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT for table `cj_group_member_user_xref`
--
ALTER TABLE `cj_group_member_user_xref`
MODIFY `id` int(11) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `cj_member_groups`
--
ALTER TABLE `cj_member_groups`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=95;
--
-- AUTO_INCREMENT for table `cj_member_users`
--
ALTER TABLE `cj_member_users`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=207;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `cj_group_member_user_xref`
--
ALTER TABLE `cj_group_member_user_xref`
ADD CONSTRAINT `cj_group_member_user_xref_ibfk_1` FOREIGN KEY (`groupId`) REFERENCES `cj_member_groups` (`id`) ON DELETE CASCADE,
ADD CONSTRAINT `cj_group_member_user_xref_ibfk_2` FOREIGN KEY (`memberId`) REFERENCES `cj_member_users` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
