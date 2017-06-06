-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 05, 2016 at 10:49 PM
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
('consumable', 'potion', 'mend wounds', '{"hp":"15"}',1),
('equipment', 'ring', 'dexterity', '{"dexterity":"1"}',1),
('equipment', 'ring', 'magic', '{"maxmana":"1"}',1),
('equipment', 'ring', 'strength', '{"strength":"1"}',1),
('equipment', 'weapon', 'staff', '{"bash":4,"slash":1,"pierce":0,"block":3,"acc":2}',7),
('equipment', 'weapon', 'knife', '{"bash":2,"slash":3,"pierce":0,"block":2,"acc":1}',1),
('equipment', 'weapon', 'shortsword', '{"bash":2,"slash":3,"pierce":1,"block":2,"acc":2}',3),
('equipment', 'weapon', 'broadsword', '{"bash":2,"slash":5,"pierce":1,"block":4,"acc":2}',5),
('equipment', 'weapon', 'handaxe', '{"bash":3,"slash":3,"pierce":0,"block":1,"acc":0}',2),
('equipment', 'weapon', 'waraxe', '{"bash":3,"slash":4,"pierce":0,"block":2,"acc":0}',4),
('equipment', 'weapon', 'cudgel', '{"bash":3,"slash":2,"pierce":1,"block":1,"acc":0}',1),
('equipment', 'weapon', 'mace', '{"bash":4,"slash":2,"pierce":0,"block":1,"acc":0}',4),
('equipment', 'weapon', 'spear', '{"bash":1,"slash":3,"pierce":1,"block":3,"acc":1}',1),
('equipment', 'weapon', 'spetum', '{"bash":1,"slash":4,"pierce":2,"block":3,"acc":1}',3),
('equipment', 'weapon', 'billhook', '{"bash":2,"slash":4,"pierce":2,"block":3,"acc":0}',5),
('equipment', 'weapon', 'demonsword', '{"bash":2,"slash":6,"pierce":1,"block":3,"acc":1}',9),
('equipment', 'weapon', 'demonwhip', '{"bash":3,"slash":5,"pierce":1,"block":0,"acc":0}',9),
('equipment', 'weapon', 'demontrident', '{"bash":1,"slash":6,"pierce":3,"block":3,"acc":0}',9),
('equipment', 'shield', 'wooden', '{"block":3,"armour":0,"pen":0}',1),
('equipment', 'shield', 'kite', '{"block":6,"armour":0,"pen":2}',6),
('equipment', 'helmet', 'steel', '{"block":2,"armour":0,"pen":1}',4),
('equipment', 'armour', 'robes', '{"block":0,"armour":1,"pen":0}',4),
('equipment', 'armour', 'leather', '{"block":0,"armour":2,"pen":0}',2),
('equipment', 'armour', 'chainmail', '{"block":0,"armour":4,"pen":4}',4),
('equipment', 'armour', 'plate', '{"block":4,"armour":4,"pen":8}',6);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
