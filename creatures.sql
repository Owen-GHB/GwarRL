-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 04, 2016 at 02:59 PM
-- Server version: 5.7.11
-- PHP Version: 5.6.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gwarl`
--

-- --------------------------------------------------------

--
-- Table structure for table `creatures`
--

CREATE TABLE `creatures` (
  `creaturetype` tinytext NOT NULL,
  `creatureset` tinytext NOT NULL,
  `level` smallint(6) NOT NULL,
  `maxhp` smallint(6) NOT NULL,
  `regen` smallint(6) NOT NULL,
  `strength` smallint(6) NOT NULL,
  `dexterity` smallint(6) NOT NULL,
  `armour` smallint(6) NOT NULL,
  `inventory` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `creatures`
--

INSERT INTO `creatures` (`creaturetype`, `creatureset`, `level`, `maxhp`, `regen`, `strength`, `dexterity`, `armour`, `inventory`) VALUES
('player', 'none', 1, 20, 1, 5, 5, 0, 1),
('rat', 'animal', 1, 8, 1, 3, 3, 0, 0),
('centipede', 'animal', 2, 12, 1, 1, 2, 2, 0),
('spider', 'animal', 3, 12, 1, 3, 5, 0, 0),
('wolf', 'animal', 4, 20, 1, 6, 7, 1, 0),
('scorpion', 'animal', 5, 16, 1, 7, 5, 3, 0),
('bear', 'animal', 6, 30, 1, 10, 6, 2, 0),
('deutero', 'animal', 7, 35, 1, 12, 4, 3, 0),
('manticore', 'animal', 8, 40, 1, 15, 7, 1, 0),
('phoenix', 'animal', 9, 25, 1, 18, 7, 0, 0),
('goblin', 'goblinoid', 1, 10, 1, 3, 4, 0, 1),
('goblinsoldier', 'goblinoid', 2, 12, 1, 4, 5, 1, 1),
('orcslave', 'goblinoid', 3, 18, 1, 6, 5, 0, 1),
('goblinshaman', 'goblinoid', 4, 16, 1, 5, 6, 2, 1),
('orc', 'goblinoid', 5, 25, 1, 7, 5, 1, 1),
('orcsoldier', 'goblinoid', 6, 30, 1, 9, 6, 4, 1),
('orcshaman', 'goblinoid', 7, 30, 1, 7, 7, 1, 1),
('troll', 'goblinoid', 8, 60, 5, 16, 3, 2, 0),
('orccaptain', 'goblinoid', 9, 50, 1, 10, 7, 6, 1),
('skeleton', 'undead', 4, 25, 0, 9, 3, 0, 1),
('mummy', 'undead', 5, 35, 0, 9, 3, 0, 0),
('wraith', 'undead', 6, 30, 0, 4, 9, 0, 0),
('ghoul', 'undead', 7, 30, 3, 9, 7, 3, 0),
('banshee', 'undead', 8, 40, 0, 8, 9, 0, 0),
('sentinel', 'undead', 9, 40, 0, 12, 9, 5, 0),
('imp', 'demonic', 4, 20, 2, 5, 10, 0, 0),
('demon', 'demonic', 6, 35, 2, 8, 9, 3, 1),
('baron', 'demonic', 8, 40, 2, 10, 8, 3, 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
