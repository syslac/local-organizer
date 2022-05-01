-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2022 at 02:48 PM
-- Server version: 10.3.29-MariaDB
-- PHP Version: 7.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `local_organizer`
--
CREATE DATABASE IF NOT EXISTS `local_organizer` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `local_organizer`;

-- --------------------------------------------------------

--
-- Table structure for table `lo_bookmarks`
--

CREATE TABLE `lo_bookmarks` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `link` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_bookmarks_tags`
--

CREATE TABLE `lo_bookmarks_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_bookmark` int(11) UNSIGNED DEFAULT NULL,
  `id_tag` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_groceries`
--

CREATE TABLE `lo_groceries` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_priority` int(11) UNSIGNED DEFAULT NULL,
  `qty` decimal(10,2) UNSIGNED NOT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deadline` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_groceries_tags`
--

CREATE TABLE `lo_groceries_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_item` int(11) UNSIGNED DEFAULT NULL,
  `id_tag` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_modules`
--

CREATE TABLE `lo_modules` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `module_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_table` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_class` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_priority`
--

CREATE TABLE `lo_priority` (
  `id` int(11) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `priority_num` int(2) UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_projects`
--

CREATE TABLE `lo_projects` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `codename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_projects_links`
--

CREATE TABLE `lo_projects_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_project` int(11) UNSIGNED DEFAULT NULL,
  `id_bookmark` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_project_tags`
--

CREATE TABLE `lo_project_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_project` int(11) UNSIGNED DEFAULT NULL,
  `id_tag` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_tags`
--

CREATE TABLE `lo_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_todo`
--

CREATE TABLE `lo_todo` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `due_date` date DEFAULT NULL,
  `id_priority` int(11) UNSIGNED DEFAULT NULL,
  `done` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_todo_tags`
--

CREATE TABLE `lo_todo_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_todo` int(11) UNSIGNED NOT NULL,
  `id_tag` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_user`
--

CREATE TABLE `lo_user` (
  `id` int(11) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_wishlist`
--

CREATE TABLE `lo_wishlist` (
  `id` int(11) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `id_for_user` int(11) UNSIGNED NOT NULL,
  `item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `id_priority` int(11) UNSIGNED DEFAULT NULL,
  `deadline` date DEFAULT NULL,
  `bought` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_wishlist_tags`
--

CREATE TABLE `lo_wishlist_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_tag` int(11) UNSIGNED DEFAULT NULL,
  `id_wishlist` int(11) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_writing_prompts`
--

CREATE TABLE `lo_writing_prompts` (
  `id` int(10) UNSIGNED NOT NULL,
  `is_deleted` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_writing_prompts_links`
--

CREATE TABLE `lo_writing_prompts_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_bookmark` int(11) UNSIGNED NOT NULL,
  `id_prompt` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lo_writing_prompts_tags`
--

CREATE TABLE `lo_writing_prompts_tags` (
  `id` int(10) UNSIGNED NOT NULL,
  `id_prompt` int(11) UNSIGNED NOT NULL,
  `id_tag` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `lo_bookmarks`
--
ALTER TABLE `lo_bookmarks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_bookmarks_tags`
--
ALTER TABLE `lo_bookmarks_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bookmark` (`id_bookmark`),
  ADD KEY `id_tag` (`id_tag`);

--
-- Indexes for table `lo_groceries`
--
ALTER TABLE `lo_groceries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item` (`item`),
  ADD KEY `id_priority` (`id_priority`),
  ADD KEY `deadline` (`deadline`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_groceries_tags`
--
ALTER TABLE `lo_groceries_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_item` (`id_item`),
  ADD KEY `id_tag` (`id_tag`);

--
-- Indexes for table `lo_modules`
--
ALTER TABLE `lo_modules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_priority`
--
ALTER TABLE `lo_priority`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `priority_num_2` (`priority_num`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_projects`
--
ALTER TABLE `lo_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_projects_links`
--
ALTER TABLE `lo_projects_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_project` (`id_project`),
  ADD KEY `id_bookmark` (`id_bookmark`);

--
-- Indexes for table `lo_project_tags`
--
ALTER TABLE `lo_project_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_project` (`id_project`),
  ADD KEY `id_tag` (`id_tag`);

--
-- Indexes for table `lo_tags`
--
ALTER TABLE `lo_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tag` (`name`);

--
-- Indexes for table `lo_todo`
--
ALTER TABLE `lo_todo`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_priority` (`id_priority`),
  ADD KEY `due_date` (`due_date`),
  ADD KEY `done` (`done`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_todo_tags`
--
ALTER TABLE `lo_todo_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_todo` (`id_todo`),
  ADD KEY `id_tag` (`id_tag`);

--
-- Indexes for table `lo_user`
--
ALTER TABLE `lo_user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user` (`name`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_wishlist`
--
ALTER TABLE `lo_wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `priority_2` (`id_priority`,`price`,`id_for_user`),
  ADD KEY `for` (`id_for_user`),
  ADD KEY `price` (`price`),
  ADD KEY `priority` (`id_priority`),
  ADD KEY `deadline` (`deadline`),
  ADD KEY `bought` (`bought`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_wishlist_tags`
--
ALTER TABLE `lo_wishlist_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tag` (`id_tag`),
  ADD KEY `id_wishlist_item` (`id_wishlist`);

--
-- Indexes for table `lo_writing_prompts`
--
ALTER TABLE `lo_writing_prompts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `is_deleted` (`is_deleted`);

--
-- Indexes for table `lo_writing_prompts_links`
--
ALTER TABLE `lo_writing_prompts_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_bookmark` (`id_bookmark`),
  ADD KEY `id_prompt` (`id_prompt`);

--
-- Indexes for table `lo_writing_prompts_tags`
--
ALTER TABLE `lo_writing_prompts_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_prompt` (`id_prompt`),
  ADD KEY `id_tag` (`id_tag`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lo_bookmarks`
--
ALTER TABLE `lo_bookmarks`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_bookmarks_tags`
--
ALTER TABLE `lo_bookmarks_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_groceries`
--
ALTER TABLE `lo_groceries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_groceries_tags`
--
ALTER TABLE `lo_groceries_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_modules`
--
ALTER TABLE `lo_modules`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_priority`
--
ALTER TABLE `lo_priority`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_projects`
--
ALTER TABLE `lo_projects`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_projects_links`
--
ALTER TABLE `lo_projects_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_project_tags`
--
ALTER TABLE `lo_project_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_tags`
--
ALTER TABLE `lo_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_todo`
--
ALTER TABLE `lo_todo`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_todo_tags`
--
ALTER TABLE `lo_todo_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_user`
--
ALTER TABLE `lo_user`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_wishlist`
--
ALTER TABLE `lo_wishlist`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_wishlist_tags`
--
ALTER TABLE `lo_wishlist_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_writing_prompts`
--
ALTER TABLE `lo_writing_prompts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_writing_prompts_links`
--
ALTER TABLE `lo_writing_prompts_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lo_writing_prompts_tags`
--
ALTER TABLE `lo_writing_prompts_tags`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lo_bookmarks_tags`
--
ALTER TABLE `lo_bookmarks_tags`
  ADD CONSTRAINT `lo_bookmarks_tags_ibfk_1` FOREIGN KEY (`id_bookmark`) REFERENCES `lo_bookmarks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_bookmarks_tags_ibfk_2` FOREIGN KEY (`id_tag`) REFERENCES `lo_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_groceries`
--
ALTER TABLE `lo_groceries`
  ADD CONSTRAINT `lo_groceries_ibfk_1` FOREIGN KEY (`id_priority`) REFERENCES `lo_priority` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lo_groceries_tags`
--
ALTER TABLE `lo_groceries_tags`
  ADD CONSTRAINT `lo_groceries_tags_ibfk_1` FOREIGN KEY (`id_item`) REFERENCES `lo_groceries` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_groceries_tags_ibfk_2` FOREIGN KEY (`id_tag`) REFERENCES `lo_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_projects_links`
--
ALTER TABLE `lo_projects_links`
  ADD CONSTRAINT `lo_projects_links_ibfk_1` FOREIGN KEY (`id_bookmark`) REFERENCES `lo_bookmarks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_projects_links_ibfk_2` FOREIGN KEY (`id_project`) REFERENCES `lo_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_project_tags`
--
ALTER TABLE `lo_project_tags`
  ADD CONSTRAINT `lo_project_tags_ibfk_1` FOREIGN KEY (`id_project`) REFERENCES `lo_projects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_project_tags_ibfk_2` FOREIGN KEY (`id_tag`) REFERENCES `lo_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_todo`
--
ALTER TABLE `lo_todo`
  ADD CONSTRAINT `lo_todo_ibfk_1` FOREIGN KEY (`id_priority`) REFERENCES `lo_priority` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lo_todo_tags`
--
ALTER TABLE `lo_todo_tags`
  ADD CONSTRAINT `lo_todo_tags_ibfk_1` FOREIGN KEY (`id_tag`) REFERENCES `lo_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_todo_tags_ibfk_2` FOREIGN KEY (`id_todo`) REFERENCES `lo_todo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_wishlist`
--
ALTER TABLE `lo_wishlist`
  ADD CONSTRAINT `lo_wishlist_ibfk_1` FOREIGN KEY (`id_for_user`) REFERENCES `lo_user` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_wishlist_ibfk_2` FOREIGN KEY (`id_priority`) REFERENCES `lo_priority` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `lo_wishlist_tags`
--
ALTER TABLE `lo_wishlist_tags`
  ADD CONSTRAINT `lo_wishlist_tags_ibfk_1` FOREIGN KEY (`id_tag`) REFERENCES `lo_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_wishlist_tags_ibfk_2` FOREIGN KEY (`id_wishlist`) REFERENCES `lo_wishlist` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_writing_prompts_links`
--
ALTER TABLE `lo_writing_prompts_links`
  ADD CONSTRAINT `lo_writing_prompts_links_ibfk_1` FOREIGN KEY (`id_bookmark`) REFERENCES `lo_bookmarks` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_writing_prompts_links_ibfk_2` FOREIGN KEY (`id_prompt`) REFERENCES `lo_writing_prompts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lo_writing_prompts_tags`
--
ALTER TABLE `lo_writing_prompts_tags`
  ADD CONSTRAINT `lo_writing_prompts_tags_ibfk_1` FOREIGN KEY (`id_prompt`) REFERENCES `lo_writing_prompts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `lo_writing_prompts_tags_ibfk_2` FOREIGN KEY (`id_tag`) REFERENCES `lo_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
