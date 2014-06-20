-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 20, 2014 at 09:53 AM
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

--
-- Dumping data for table `accounting`
--

INSERT INTO `accounting` (`ID`, `Username`, `ServiceType`, `FramedProtocol`, `NASPort`, `NASPortType`, `CallingStationID`, `CalledStationID`, `NASPortID`, `AcctSessionID`, `FramedIPAddress`, `AcctAuthentic`, `EventTimestamp`, `NASIdentifier`, `NASIPAddress`, `AcctDelayTime`, `AcctSessionTime`, `AcctInputOctets`, `AcctInputGigawords`, `AcctInputPackets`, `AcctOutputOctets`, `AcctOutputGigawords`, `AcctOutputPackets`, `AcctStatusType`, `AcctTerminateCause`, `PeriodKey`) VALUES
(1, 'madhavi2', 1, 111, '123qwe', 111, '111qqq', 'qqq111', '111www', 'www222', '222www', 222, '2014-06-01 00:00:00', '1111wwwww', 'www222', 222, 111, 123, 321, 221, 112, 332, 1123, 3321, 1234, '33');

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
(1, 'rrr1', '12', 0);

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
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `clients_to_realms`
--

INSERT INTO `clients_to_realms` (`ID`, `ClientID`, `RealmID`, `Disabled`, `Comment`) VALUES
(1, 1, 1, 0, ''),
(2, 1, 1, 0, ''),
(3, 1, 1, 0, '');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `client_attributes`
--

INSERT INTO `client_attributes` (`ID`, `ClientID`, `Name`, `Operator`, `Value`, `Disabled`) VALUES
(1, 1, 'yui', '0', '123', 1);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`ID`, `Name`, `Priority`, `Disabled`, `Comment`) VALUES
(4, 'g1', 0, 0, '');

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
(2, 1, 'qwqw', '6', 'qwqwqwqw', 1);

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
(1, 'realm1', 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `realm_attributes`
--

INSERT INTO `realm_attributes` (`ID`, `RealmID`, `Name`, `Operator`, `Value`, `Disabled`) VALUES
(1, 1, 'wewewe', '8', '1212', 0);

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
(1, 1, '2014-06-20 05:29:53', 1, 123, '2014-06-30 18:30:00', '2014-07-31 18:30:00', 0, '0000-00-00 00:00:00'),
(2, 1, '2014-06-20 01:59:25', 2, 333, '2014-05-31 18:30:00', '2014-06-30 18:30:00', 0, '0000-00-00 00:00:00'),
(4, 2, '2014-06-20 02:33:29', 1, 345, '2014-06-30 18:30:00', '2014-07-31 18:30:00', 0, '0000-00-00 00:00:00'),
(5, 5, '2014-06-20 04:04:58', 1, 123, '2014-05-31 18:30:00', '2014-06-30 18:30:00', 0, '0000-00-00 00:00:00');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Username`, `Disabled`) VALUES
(1, 'madhavi', 0),
(2, 'add-63kvupt', 0),
(4, 'madhavi1', 0),
(5, 'madhavi2', 0);

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `users_to_groups`
--

INSERT INTO `users_to_groups` (`ID`, `UserID`, `GroupID`, `Disabled`, `Comment`) VALUES
(4, 1, 4, 0, '');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `user_attributes`
--

INSERT INTO `user_attributes` (`ID`, `UserID`, `Name`, `Operator`, `Value`, `Disabled`, `modifier`) VALUES
(1, 1, 'User-Password', '2', '123', 0, ''),
(3, 2, 'User-Password', '2', 'somlnvc', 0, ''),
(6, 1, 'Traffic Limit', '0', '0.2', 0, 'Seconds'),
(7, 5, 'User-Password', '2', '123456', 0, '');

-- --------------------------------------------------------

--
-- Table structure for table `wisp_locations`
--

CREATE TABLE IF NOT EXISTS `wisp_locations` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `wisp_locations`
--

INSERT INTO `wisp_locations` (`ID`, `Name`) VALUES
(3, 'Indore'),
(2, 'Ujjain');

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `wisp_userdata`
--

INSERT INTO `wisp_userdata` (`ID`, `UserID`, `LocationID`, `FirstName`, `LastName`, `Email`, `Phone`) VALUES
(1, 1, 3, 'madhavi', 'shah', 'madhavi@centiva.co', 1234567890),
(2, 2, 0, '', '', '', 0),
(4, 5, 0, '', '', '', 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
