-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 06, 2016 at 11:35 PM
-- Server version: 5.7.11
-- PHP Version: 5.6.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

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
  `damage` smallint(6) NOT NULL,
  `accuracy` smallint(6) NOT NULL,
  `evasion` smallint(6) NOT NULL,
  `armour` smallint(6) NOT NULL,
  `inventory` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `creatures`
--

INSERT INTO `creatures` (`creaturetype`, `creatureset`, `level`, `maxhp`, `regen`, `damage`, `accuracy`, `evasion`, `armour`, `inventory`) VALUES
('player', 'none', 1, 20, 1, 6, 4, 2, 2, 1),
('rat', 'animal', 1, 6, 1, 4, 4, 2, 0, 0),
('centipede', 'animal', 1, 10, 1, 4, 5, 0, 2, 0),
('spider', 'animal', 2, 12, 1, 6, 4, 2, 0, 0),
('scorpion', 'animal', 2, 12, 1, 6, 4, 0, 2, 0),
('goblin', 'goblinoid', 1, 8, 1, 5, 5, 1, 0, 1),
('orc', 'goblinoid', 3, 15, 1, 7, 4, 1, 0, 1),
('wolf', 'animal', 4, 16, 1, 9, 6, 3, 0, 0),
('skeleton', 'undead', 3, 20, 0, 12, 3, 0, 3, 1),
('bear', 'animal', 5, 25, 1, 12, 5, 2, 0, 0),
('imp', 'demonic', 4, 16, 2, 12, 6, 4, 0, 1),
('wraith', 'undead', 5, 25, 0, 12, 6, 0, 6, 1),
('troll', 'goblinoid', 7, 50, 5, 15, 4, 1, 0, 1),
('demon', 'demonic', 7, 35, 2, 18, 6, 2, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `category` tinytext NOT NULL,
  `class` tinytext NOT NULL,
  `name` tinytext NOT NULL,
  `affects` text NOT NULL,
  `rarity` smallint(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`category`, `class`, `name`, `affects`, `rarity`) VALUES
('consumable', 'potion', 'mend wounds', '{"hp":"8"}', 1),
('equipment', 'ring', 'regeneration', '{"regen":"1"}', 3),
('equipment', 'ring', 'slaying', '{"accuracy":"1","damage":"1"}', 5),
('equipment', 'ring', 'warding', '{"evasion":"1"}', 1),
('equipment', 'weapon', 'staff', '{"accuracy":2,"damage":1}', 1),
('equipment', 'weapon', 'shortsword', '{"accuracy":2,"damage":2}', 2),
('equipment', 'weapon', 'broadsword', '{"accuracy":1,"damage":4}', 5),
('equipment', 'weapon', 'handaxe', '{"accuracy":1,"damage":3}', 3),
('equipment', 'weapon', 'waraxe', '{"accuracy":0,"damage":4}', 4),
('equipment', 'weapon', 'cudgel', '{"accuracy":1,"damage":3}', 1),
('equipment', 'weapon', 'mace', '{"accuracy":0,"damage":4}', 3),
('equipment', 'weapon', 'spear', '{"accuracy":2,"damage":1}', 1),
('equipment', 'weapon', 'billhook', '{"accuracy":2,"damage":3}', 4),
('equipment', 'shield', 'wooden', '{"armour":3}', 1),
('equipment', 'shield', 'kite', '{"armour":5}', 5),
('equipment', 'helmet', 'hide', '{"armour":1}', 1),
('equipment', 'helmet', 'full', '{"armour":2}', 4),
('equipment', 'armour', 'robes', '{"armour":0,"evasion":0}', 1),
('equipment', 'armour', 'leather', '{"armour":2,"evasion":0}', 1),
('equipment', 'armour', 'chainmail', '{"armour":4,"evasion":-1}', 3),
('equipment', 'armour', 'plate', '{"armour":8,"evasion":-3}', 6);

-- --------------------------------------------------------

--
-- Table structure for table `levelschema`
--

CREATE TABLE `levelschema` (
  `areatype` tinytext NOT NULL,
  `mapsize` int(11) NOT NULL,
  `depth` int(11) NOT NULL,
  `roomsize` int(11) NOT NULL,
  `rooms` int(11) NOT NULL,
  `lists` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `levelschema`
--

INSERT INTO `levelschema` (`areatype`, `mapsize`, `depth`, `roomsize`, `rooms`, `lists`) VALUES
('dungeon', 75, 1, 15, 6, 'dud'),
('dungeon', 75, 2, 15, 8, 'dud'),
('dungeon', 75, 3, 15, 10, 'dud'),
('dungeon', 75, 4, 15, 10, 'dud'),
('dungeon', 75, 5, 15, 10, 'dud');

-- --------------------------------------------------------

--
-- Table structure for table `spells`
--

CREATE TABLE `spells` (
  `school` tinytext NOT NULL,
  `nature` tinytext NOT NULL,
  `name` tinytext NOT NULL,
  `level` int(11) NOT NULL,
  `cost` int(11) NOT NULL,
  `power` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `spells`
--

INSERT INTO `spells` (`school`, `nature`, `name`, `level`, `cost`, `power`) VALUES
('lightning', 'damage', 'shock', 1, 1, 1),
('lightning', 'damage', 'lightning', 3, 3, 3),
('fire', 'damage', 'burn', 1, 1, 1),
('fire', 'damage', 'fireball', 3, 3, 3),
('frost', 'damage', 'chill', 1, 1, 1),
('frost', 'damage', 'freeze', 3, 3, 3),
('arcane', 'translocation', 'blink', 2, 2, 2);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
