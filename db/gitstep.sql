-- phpMyAdmin SQL Dump
-- version 3.5.4
--
-- Generation Time: Nov 20, 2012 at 02:38 PM
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `Repos`
--

INSERT INTO `Repos` (`id`, `name`, `description`) VALUES
(1, 'markysrepo', 'Marchi''s first repos'),
(2, 'malavvsrepo', 'malavv''s first repo'),
(13, 'NewAwesomeRepo', 'Description complÃ¨te du nouveau repo super awesome que j''ai crÃ©Ã©.'),
(14, 'AutreRepo', 'sakljds;lakjh askl klajsd'),
(15, 'dasffadsf', 'asdfasdfasdf');

-- --------------------------------------------------------

--
-- Table structure for table `UserRepoPerms`
--

CREATE TABLE IF NOT EXISTS `UserRepoPerms` (
  `user` int(11) NOT NULL,
  `Repo` int(11) NOT NULL,
  `Perm` int(11) NOT NULL,
  PRIMARY KEY (`user`,`Repo`),
  KEY `Repo` (`Repo`),
  KEY `Perm` (`Perm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `UserRepoPerms`
--

INSERT INTO `UserRepoPerms` (`user`, `Repo`, `Perm`) VALUES
(2, 2, 1),
(1, 1, 2),
(1, 2, 3),
(1, 13, 3),
(1, 14, 3),
(1, 15, 3),
(2, 1, 3);

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
  `notStudent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`id`, `openid`, `name`, `email`, `isstudent`, `pubkey`, `username`, `notStudent`) VALUES
(1, 'https://www.google.com/accounts/o8/id?id=AItOawlDvByvDssHRv0wAzbND5rz_L9bgxbzx9g ', 'Maxime Lavigne', 'maxime.lavigne@polymtl.ca', 0, 'ssh-rsa AAAAB3NzaC1yc2EAAAABJQAAAgB4Thor3X3VLyc7FIxolVRox242A1yXfDjGOpQv8s5OB26eknCsxs69M9Mv5oFUvACTxpLxprLXYVD0xTATLmvO+9eSOFMU2iq68xUmvd2oIw4UBFyPEs9hXl/wFnqJMauCuw9+GSt8c0YRRkOLntovNtdug9n8mBhex0yBBzAg2hs2slshK7zTz0S0AIvB9FFHpRuEadBW544UiUUqmDzgSXaeAnV4MQuRnimzh9tdb0pVlIGyQ6ve/gE8qx82udL/1jjQ/DjAbdQT0L+uhrgRtvmdq9eeMSunjC+MBFoMtls5PbyJJlM8gc1tX/ySgAEyoTQbdl78mtkO1/ZNyx6V7P7thXciP6SUdcRN5gLDpvMGzB6Xp1VJ2bMXbLElb4Wbsfsn7JM94d2H660V2A3eO8SaJogvcEHnzCCy1+MAFNbdbGPRGwt1QpWQJkXKTtp7bMWkGZBlkYRSuAhXt7uEppjD9e161RYdKAwqz0emI15srk4DtmOt50Pp6GCc5isyJRAH1AOHI8apYDA8Hi4We10r6CLswFbUh8CDnSSIislA8q7pxFiBilHmdYHsTQ3NYiNjcELTn0ybeedU2C9rvn7hxx1SjqrsHaYakqq+qxsPHRom3ystdJNFOYy1aN9Ivb7ZFUJSevfHE5qmKDNOKfBu7nMzojCcQzbMEWtYyQ== imported-openssh-key', 'malavv', 0),
(2, '------------------------', 'Simon Marchi sdfsd', 'simon.marchi@polymtl.ca sdfsdf', 1, 'loldsaoojfsdaljkgfhasldjkgfaskldfgasjkldfgaskldgflasdgflkasdgfljkasdfsadfasdfasdfsadfsda sdfsdf', 'markyssdfsdf', 0);

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
