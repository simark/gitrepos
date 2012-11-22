-- phpMyAdmin SQL Dump
-- version 3.5.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 22, 2012 at 03:42 AM
-- Server version: 5.5.28-0ubuntu0.12.04.2
-- PHP Version: 5.3.10-1ubuntu3.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `gitrepos`
--

-- --------------------------------------------------------

--
-- Table structure for table `Perms`
--

CREATE TABLE IF NOT EXISTS `Perms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `Perms`
--

INSERT INTO `Perms` (`id`, `name`) VALUES
(1, 'R'),
(2, 'RW'),
(3, 'RW+');

-- --------------------------------------------------------

--
-- Table structure for table `Repos`
--

CREATE TABLE IF NOT EXISTS `Repos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `description` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=25 ;

-- --------------------------------------------------------

--
-- Table structure for table `UserRepoPerms`
--

CREATE TABLE IF NOT EXISTS `UserRepoPerms` (
  `user` int(11) NOT NULL,
  `Repo` int(11) NOT NULL,
  `Perm` int(11) NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `is_owner` tinyint(1) NOT NULL,
  PRIMARY KEY (`user`,`Repo`),
  KEY `Repo` (`Repo`),
  KEY `Perm` (`Perm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE IF NOT EXISTS `Users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(250) NOT NULL COMMENT 'OpenID''s id string.',
  `name` varchar(250) NOT NULL COMMENT 'Complete Name',
  `email` varchar(250) NOT NULL,
  `isstudent` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Was verified as a student.',
  `pubkey` text NOT NULL COMMENT 'Public Key',
  `username` varchar(100) NOT NULL COMMENT 'Username nospaces',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=14 ;

-- --------------------------------------------------------

--
-- Table structure for table `Validation`
--

CREATE TABLE IF NOT EXISTS `Validation` (
  `user` int(11) NOT NULL COMMENT 'Foreing user id.',
  `code` varchar(100) NOT NULL COMMENT 'Verrification''s code.',
  PRIMARY KEY (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `UserRepoPerms`
--
ALTER TABLE `UserRepoPerms`
  ADD CONSTRAINT `UserRepoPerms_ibfk_1` FOREIGN KEY (`user`) REFERENCES `Users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserRepoPerms_ibfk_2` FOREIGN KEY (`Repo`) REFERENCES `Repos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `UserRepoPerms_ibfk_3` FOREIGN KEY (`Perm`) REFERENCES `Perms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `Validation`
--
ALTER TABLE `Validation`
  ADD CONSTRAINT `Validation_ibfk_1` FOREIGN KEY (`user`) REFERENCES `Users` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
