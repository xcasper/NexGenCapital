-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Mar 22, 2014 at 04:17 PM
-- Server version: 5.6.12-log
-- PHP Version: 5.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `nexgen`
--
CREATE DATABASE IF NOT EXISTS `nexgen` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `nexgen`;

-- --------------------------------------------------------

--
-- Table structure for table `team_achievements`
--

CREATE TABLE IF NOT EXISTS `team_achievements` (
  `achievement_id` int(7) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `points` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `team_achievements`
--

INSERT INTO `team_achievements` (`achievement_id`, `name`, `description`, `points`) VALUES
(1, 'Registration Badge', 'For registering', 5);

-- --------------------------------------------------------

--
-- Table structure for table `team_comments`
--

CREATE TABLE IF NOT EXISTS `team_comments` (
  `user_id` int(255) NOT NULL,
  `commenter_id` int(255) NOT NULL,
  `comment` varchar(255) NOT NULL,
  `rating` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_commercial`
--

CREATE TABLE IF NOT EXISTS `team_commercial` (
  `user_id` int(255) NOT NULL,
  `password` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_log`
--

CREATE TABLE IF NOT EXISTS `team_log` (
  `user_id` int(255) NOT NULL,
  `search_query` varchar(255) NOT NULL,
  `date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_references`
--

CREATE TABLE IF NOT EXISTS `team_references` (
  `user_id` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `number` int(12) NOT NULL,
  `relationship` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_resume`
--

CREATE TABLE IF NOT EXISTS `team_resume` (
  `user_id` int(255) NOT NULL,
  `experience` varchar(255) NOT NULL,
  `custom_field` varchar(255) NOT NULL,
  `disability` int(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_skills`
--

CREATE TABLE IF NOT EXISTS `team_skills` (
  `skill_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `team_skills`
--

INSERT INTO `team_skills` (`skill_id`, `name`, `description`) VALUES
(1, 'Military Experience', 'Served in the military.'),
(2, 'Microsoft Certification', 'Microsoft Certification');

-- --------------------------------------------------------

--
-- Table structure for table `team_state`
--

CREATE TABLE IF NOT EXISTS `team_state` (
  `user_id` int(255) NOT NULL,
  `password` int(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_user_achievements`
--

CREATE TABLE IF NOT EXISTS `team_user_achievements` (
  `user_id` int(255) NOT NULL,
  `achievement_id` int(20) NOT NULL,
  `time` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_user_skills`
--

CREATE TABLE IF NOT EXISTS `team_user_skills` (
  `user_id` int(255) NOT NULL,
  `skill_id` int(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_voting`
--

CREATE TABLE IF NOT EXISTS `team_voting` (
  `user_id` int(255) NOT NULL,
  `voted_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team_work_history`
--

CREATE TABLE IF NOT EXISTS `team_work_history` (
  `user_id` int(255) NOT NULL,
  `work_name` varchar(50) NOT NULL,
  `work_start` int(255) NOT NULL,
  `work_end` int(255) NOT NULL,
  `reason` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
