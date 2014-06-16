-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 16, 2014 at 02:22 PM
-- Server version: 5.6.12
-- PHP Version: 5.5.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `smradius_webgui`
--
CREATE DATABASE IF NOT EXISTS `smradius_webgui` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `smradius_webgui`;

-- --------------------------------------------------------

--
-- Table structure for table `accounting`
--

CREATE TABLE IF NOT EXISTS `accounting` (
  `ID` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `ServiceType` int(11) NOT NULL,
  `FramedProtocol` int(11) NOT NULL,
  `NASPort` varchar(255) NOT NULL,
  `NASPortType` int(11) NOT NULL,
  `CallingStationID` varchar(255) NOT NULL,
  `CalledStationID` varchar(255) NOT NULL,
  `NASPortID` varchar(255) NOT NULL,
  `AcctSessionID` varchar(255) NOT NULL,
  `FramedIPAddress` varchar(16) NOT NULL,
  `AcctAuthentic` int(11) NOT NULL,
  `EventTimestamp` datetime NOT NULL,
  `NASIdentifier` varchar(255) NOT NULL,
  `NASIPAddress` varchar(16) NOT NULL,
  `AcctDelayTime` int(11) NOT NULL,
  `AcctSessionTime` int(11) NOT NULL,
  `AcctInputOctets` int(11) NOT NULL,
  `AcctInputGigawords` int(11) NOT NULL,
  `AcctInputPackets` int(11) NOT NULL,
  `AcctOutputOctets` int(11) NOT NULL,
  `AcctOutputGigawords` int(11) NOT NULL,
  `AcctOutputPackets` int(11) NOT NULL,
  `AcctStatusType` int(11) NOT NULL,
  `AcctTerminateCause` int(11) NOT NULL,
  `PeriodKey` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `accounting_summary`
--

CREATE TABLE IF NOT EXISTS `accounting_summary` (
  `ID` int(11) NOT NULL,
  `Username` varchar(255) NOT NULL,
  `PeriodKey` varchar(255) NOT NULL,
  `TotalSessionTime` int(11) NOT NULL,
  `TotalInput` int(11) NOT NULL,
  `TotalOutput` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE IF NOT EXISTS `clients` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `AccessList` varchar(255) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`ID`, `Name`, `AccessList`, `Disabled`) VALUES
(1, 'ddd', 'sss', 0);

-- --------------------------------------------------------

--
-- Table structure for table `clients_to_realms`
--

CREATE TABLE IF NOT EXISTS `clients_to_realms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ClientID` int(11) NOT NULL,
  `RealmID` int(11) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  `Comment` varchar(1024) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ClientID` (`ClientID`),
  UNIQUE KEY `RealmID` (`RealmID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `clients_to_realms`
--

INSERT INTO `clients_to_realms` (`ID`, `ClientID`, `RealmID`, `Disabled`, `Comment`) VALUES
(1, 1, 1, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `client_attributes`
--

CREATE TABLE IF NOT EXISTS `client_attributes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ClientID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Operator` varchar(4) NOT NULL,
  `Value` varchar(255) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `client_attributes`
--

INSERT INTO `client_attributes` (`ID`, `ClientID`, `Name`, `Operator`, `Value`, `Disabled`) VALUES
(1, 1, 'yyyy', '10', '333', 1);

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE IF NOT EXISTS `groups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Priority` smallint(6) NOT NULL,
  `Disabled` smallint(1) NOT NULL DEFAULT '0',
  `Comment` varchar(1024) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`ID`, `Name`, `Priority`, `Disabled`, `Comment`) VALUES
(1, 'g1', 0, 0, ''),
(2, 'g2', 0, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `group_attributes`
--

CREATE TABLE IF NOT EXISTS `group_attributes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `GroupID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Operator` varchar(4) NOT NULL,
  `Value` varchar(255) NOT NULL,
  `Disabled` smallint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `group_attributes`
--

INSERT INTO `group_attributes` (`ID`, `GroupID`, `Name`, `Operator`, `Value`, `Disabled`) VALUES
(1, 1, '', '0', '', 0),
(2, 1, 'bbb', '5', '12', 1);

-- --------------------------------------------------------

--
-- Table structure for table `realms`
--

CREATE TABLE IF NOT EXISTS `realms` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `realms`
--

INSERT INTO `realms` (`ID`, `Name`, `Disabled`) VALUES
(1, 'qqq', 0);

-- --------------------------------------------------------

--
-- Table structure for table `realm_attributes`
--

CREATE TABLE IF NOT EXISTS `realm_attributes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `RealmID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Operator` varchar(4) NOT NULL,
  `Value` varchar(255) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `realm_attributes`
--

INSERT INTO `realm_attributes` (`ID`, `RealmID`, `Name`, `Operator`, `Value`, `Disabled`) VALUES
(1, 1, 'www', '10', '222', 1);

-- --------------------------------------------------------

--
-- Table structure for table `topups`
--

CREATE TABLE IF NOT EXISTS `topups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Type` int(1) NOT NULL COMMENT '1 = traffic topup, 2 = uptime topup',
  `Value` int(11) NOT NULL,
  `ValidFrom` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ValidTo` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `Depleted` smallint(6) NOT NULL DEFAULT '0',
  `SMAdminDepletedOn` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `topups`
--

INSERT INTO `topups` (`ID`, `UserID`, `Timestamp`, `Type`, `Value`, `ValidFrom`, `ValidTo`, `Depleted`, `SMAdminDepletedOn`) VALUES
(5, 3, '2014-06-09 09:26:59', 1, 1234, '2014-05-31 18:30:00', '2014-06-30 18:30:00', 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `topups_summary`
--

CREATE TABLE IF NOT EXISTS `topups_summary` (
  `ID` int(11) NOT NULL,
  `TopupID` int(11) NOT NULL,
  `PeriodKey` varchar(255) NOT NULL,
  `Balance` int(11) NOT NULL,
  `Depleted` smallint(6) NOT NULL DEFAULT '0',
  `SMAdminDepletedOn` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Username` varchar(255) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Username` (`Username`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Username`, `Disabled`) VALUES
(3, 'amit3', 0),
(4, 'amit', 0),
(9, 'wrtt4tdrlhx', 0),
(10, 'wrttfh7x8eg', 0),
(11, 'wrttpe7kxa1', 0),
(12, 'wrttqfkwmhk', 0),
(13, 'wrtt4uwjytt', 0),
(14, 'wrttjtu0y5m', 0),
(15, 'wrtt6510pna', 0),
(16, 'wrttluyvdil', 0),
(17, 'wrttwnatj2j', 0),
(18, 'wrttwze8j2t', 0),
(19, 'wrttn3gahgt', 0),
(20, 'wrttj7k6xp4', 0),
(23, 'bbbbb9hv5p4i', 0),
(24, 'bbbbbn0drpkx', 0);

-- --------------------------------------------------------

--
-- Table structure for table `users_data`
--

CREATE TABLE IF NOT EXISTS `users_data` (
  `ID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `LastUpdated` datetime NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Value` varchar(255) NOT NULL,
  UNIQUE KEY `UserID` (`UserID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users_to_groups`
--

CREATE TABLE IF NOT EXISTS `users_to_groups` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `GroupID` int(11) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  `Comment` varchar(1024) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=45 ;

--
-- Dumping data for table `users_to_groups`
--

INSERT INTO `users_to_groups` (`ID`, `UserID`, `GroupID`, `Disabled`, `Comment`) VALUES
(34, 23, 1, 0, ''),
(35, 24, 1, 0, ''),
(44, 3, 1, 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `user_attributes`
--

CREATE TABLE IF NOT EXISTS `user_attributes` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Operator` varchar(4) NOT NULL,
  `Value` varchar(255) NOT NULL,
  `Disabled` int(1) NOT NULL DEFAULT '0',
  `modifier` varchar(250) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=85 ;

--
-- Dumping data for table `user_attributes`
--

INSERT INTO `user_attributes` (`ID`, `UserID`, `Name`, `Operator`, `Value`, `Disabled`, `modifier`) VALUES
(7, 3, 'User-Password', '2', 'k83ibsn', 0, ''),
(10, 4, 'User-Password', '2', '3wkac18', 0, ''),
(11, 5, 'User-Password', '2', 'qgm8z5o', 0, ''),
(15, 9, 'User-Password', '2', 'ujpf9lv', 0, ''),
(16, 10, 'User-Password', '2', 'gekpn2y', 0, ''),
(17, 11, 'User-Password', '2', '9hotr3z', 0, ''),
(18, 12, 'User-Password', '2', 'lmrrq17', 0, ''),
(19, 13, 'User-Password', '2', 'mull2z5', 0, ''),
(20, 14, 'User-Password', '2', 'bdwmpd4', 0, ''),
(21, 15, 'User-Password', '2', 'e7urs53', 0, ''),
(22, 16, 'User-Password', '2', '2ex3hkg', 0, ''),
(23, 17, 'User-Password', '2', 'zgk3e4l', 0, ''),
(24, 18, 'User-Password', '2', 'nb4gfs9', 0, ''),
(25, 19, 'User-Password', '2', 'q83ffd9', 0, ''),
(26, 20, 'User-Password', '2', '7hnljir', 0, ''),
(41, 22, 'User-Password', '2', 'p8a9z1u', 0, ''),
(42, 22, 'Uptime Limit', '2', '123', 0, ''),
(43, 22, 'IP Address', '5', '2141000', 0, ''),
(44, 22, 'IP Address', '3', '30000', 0, ''),
(45, 23, 'User-Password', '2', 'vzns4e1', 0, ''),
(46, 23, 'Uptime Limit', '2', '123', 0, ''),
(47, 23, 'IP Address', '5', '2141000', 0, ''),
(48, 23, 'IP Address', '3', '30000', 0, ''),
(49, 24, 'User-Password', '2', 'w5ft7ut', 0, ''),
(50, 24, 'Uptime Limit', '2', '123', 0, ''),
(51, 24, 'IP Address', '5', '2141000', 0, ''),
(52, 24, 'IP Address', '3', '30000', 0, ''),
(81, 3, 'Uptime Limit', '2', '123', 0, ''),
(82, 3, 'IP Address', '5', '222', 0, ''),
(83, 3, 'IP Address', '3', '30000', 0, ''),
(84, 3, 'Traffic Limit', '11', '6733980', 0, 'Hours');

-- --------------------------------------------------------

--
-- Table structure for table `wisp_locations`
--

CREATE TABLE IF NOT EXISTS `wisp_locations` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `wisp_locations`
--

INSERT INTO `wisp_locations` (`ID`, `Name`) VALUES
(4, 'bhopal'),
(3, 'indore'),
(1, 'ujjain');

-- --------------------------------------------------------

--
-- Table structure for table `wisp_userdata`
--

CREATE TABLE IF NOT EXISTS `wisp_userdata` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `UserID` int(11) NOT NULL,
  `LocationID` int(11) NOT NULL,
  `FirstName` varchar(255) NOT NULL,
  `LastName` varchar(255) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Phone` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `wisp_userdata`
--

INSERT INTO `wisp_userdata` (`ID`, `UserID`, `LocationID`, `FirstName`, `LastName`, `Email`, `Phone`) VALUES
(3, 3, 0, '', '', '', 0),
(4, 4, 0, '', '', '', 0),
(5, 5, 0, 'aaa123', 'kkk', '', 1234567890),
(9, 9, 0, '', '', '', 0),
(10, 10, 0, '', '', '', 0),
(11, 11, 0, '', '', '', 0),
(12, 12, 0, '', '', '', 0),
(13, 13, 0, '', '', '', 0),
(14, 14, 0, '', '', '', 0),
(15, 15, 0, '', '', '', 0),
(16, 16, 0, '', '', '', 0),
(17, 17, 0, '', '', '', 0),
(18, 18, 0, '', '', '', 0),
(19, 19, 0, '', '', '', 0),
(20, 20, 0, '', '', '', 0),
(22, 22, 0, '', '', '', 0),
(23, 23, 0, '', '', '', 0),
(24, 24, 0, '', '', '', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
