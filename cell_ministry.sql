-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2018 at 03:54 PM
-- Server version: 10.1.34-MariaDB
-- PHP Version: 7.2.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cell_ministry`
--

-- --------------------------------------------------------

--
-- Table structure for table `cells`
--

CREATE TABLE `cells` (
  `id` int(11) NOT NULL,
  `cell_name` varchar(255) NOT NULL,
  `cell_leader` varchar(255) NOT NULL,
  `cell_members` int(11) NOT NULL DEFAULT '1',
  `pfcc` varchar(255) NOT NULL,
  `date_started` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `cell_leaders`
--

CREATE TABLE `cell_leaders` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `members` int(11) NOT NULL DEFAULT '1',
  `cell_name` varchar(255) NOT NULL,
  `reports` int(11) NOT NULL DEFAULT '0',
  `location` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `cell_leaders`
--

INSERT INTO `cell_leaders` (`id`, `username`, `password`, `title`, `name`, `surname`, `members`, `cell_name`, `reports`, `location`) VALUES
(1, 'test@test.com', '$2y$10$GbLa4EcrI1E4h1nlxCq6YOoxRMkbut5kLen3Knu3y.PcezmfbmdDW', 'Brother', 'Chris', 'Test', 1, 'Online Cell', 3, 'Online via GoToMeeting');

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `surname` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cell_number` varchar(255) NOT NULL,
  `cell_group` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL,
  `chapter` varchar(255) NOT NULL,
  `attendance` int(11) NOT NULL DEFAULT '0',
  `invites` int(11) NOT NULL DEFAULT '0',
  `joined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `birthday` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `title`, `name`, `surname`, `email`, `cell_number`, `cell_group`, `group_name`, `chapter`, `attendance`, `invites`, `joined`, `birthday`) VALUES
(1, 'Brother', 'Lebo', 'Petane', 'lebopetane@gmail.com', '0783837421', 'Online Cell', 'I', 'UWC', 0, 0, '2018-09-08 03:06:19', '1995-09-16 22:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `cell_name` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `title` varchar(255) NOT NULL,
  `note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `cell_name` varchar(255) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `attendance` int(11) NOT NULL,
  `first_timers` int(11) NOT NULL,
  `new_converts` int(11) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `holy_ghost_filled` int(11) NOT NULL,
  `offering` int(11) NOT NULL,
  `summary` text NOT NULL,
  `duration` varchar(255) NOT NULL DEFAULT '1hr 20min 20sec'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `cell_name`, `date`, `attendance`, `first_timers`, `new_converts`, `topic`, `location`, `holy_ghost_filled`, `offering`, `summary`, `duration`) VALUES
(1, 'Online Cell', '2018-09-08 04:27:11', 2, 0, 0, 'The you of God', 'Online via GoToMeeting', 0, 0, 'Today we spoke about the you of God, what it means to be the tabernacle of God.', '1hr 20min 20sec'),
(2, 'Online Cell', '2018-10-29 20:06:43', 15, 6, 1, 'Eternal life', 'Online Via GoToMeeting', 0, 0, 'Today we discussed on eternal life. Eternity is the life of God and from the word of God we discovered that eternal life cannot perish , apart from its durability which is forever we learnt when you have eternal life your life cannot be subdued', '1hr 20min 20sec'),
(7, 'Online Cell', '2018-12-06 12:50:11', 10, 3, 2, 'My Third cell', 'Online via GoToMeeting', 1, 0, 'This is my third cell that i hosted', '0hr(s) 0min(s) 3sec');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cells`
--
ALTER TABLE `cells`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cell_leaders`
--
ALTER TABLE `cell_leaders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cells`
--
ALTER TABLE `cells`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cell_leaders`
--
ALTER TABLE `cell_leaders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
