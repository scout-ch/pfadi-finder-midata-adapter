-- phpMyAdmin SQL Dump
-- version 5.0.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 02. Jan 2021 um 18:05
-- Server-Version: 10.4.14-MariaDB
-- PHP-Version: 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `pfadi_finder`
--
CREATE DATABASE IF NOT EXISTS `pfadi_finder` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `pfadi_finder`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `divisions`
--

CREATE TABLE IF NOT EXISTS `divisions` (
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cantonalassociation` varchar(255) NOT NULL,
  `mainpostalcode` varchar(255) NOT NULL,
  `allpostalcodes` varchar(255) NOT NULL,
  `gender` int(11) NOT NULL,
  `pta` tinyint(1) NOT NULL,
  `website` varchar(255) DEFAULT NULL,
  `agegroups` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `locations`
--

CREATE TABLE IF NOT EXISTS `locations` (
  `code` varchar(255) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
COMMIT;

-- --------------------------------------------------------

--
-- Rechtemanagement für User `pf`
--

GRANT SELECT, INSERT, UPDATE, DELETE ON *.* TO `pf`@`%`;
FLUSH PRIVILEGES;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
