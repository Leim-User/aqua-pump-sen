-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 01, 2026 at 07:53 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aqua_pump`
--

-- --------------------------------------------------------

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `history_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `pump_id` varchar(100) NOT NULL,
  `event` varchar(100) NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `water_used` decimal(10,2) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `history`
--

INSERT INTO `history` (`history_id`, `user_id`, `pump_id`, `event`, `duration`, `water_used`, `status`, `created_at`) VALUES
(3, 3, 'PUMP-01', 'Pump Stopped', 15, 80.00, 'Completed', '2026-09-28 16:36:14'),
(4, 3, 'PUMP-02', 'Pump Started', 10, 75.00, 'Completed', '2026-09-28 16:37:33'),
(5, 3, 'PUMP-03', 'Pump Stopped', 25, 80.00, 'Completed', '2026-09-28 16:38:01'),
(6, 3, 'PUMP-04', 'Pump Started', 15, 120.00, 'Completed', '2026-09-28 16:38:43'),
(7, 3, 'PUMP-05', 'Pump Stopped', 7, 20.00, 'Completed', '2026-09-28 16:39:23'),
(8, 3, 'PUMP-06', 'Pump Started', 20, 10.00, 'Completed', '2026-09-28 17:04:38'),
(9, 3, 'PUMP-06', 'Pump Started', 20, 10.00, 'Completed', '2026-09-28 17:05:52'),
(10, 4, 'PUMP-06', 'Pump Started', 20, 10.00, 'Completed', '2026-09-29 02:38:24'),
(11, 4, 'PUMP-01', 'Pump Started', 25, 12.00, 'Completed', '2026-09-29 02:39:02'),
(12, 4, 'PUMP-01', 'Pump Stopped', 10, 5.00, 'Completed', '2026-09-29 02:39:27'),
(13, 4, 'PUMP-03', 'Pump Stopped', 15, 7.00, 'Completed', '2026-09-29 02:39:57'),
(14, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-01 03:25:34');

-- --------------------------------------------------------

--
-- Table structure for table `pumps`
--

CREATE TABLE `pumps` (
  `pump_id` int(11) NOT NULL,
  `pump_name` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'STOPPED',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pumps`
--

INSERT INTO `pumps` (`pump_id`, `pump_name`, `status`, `created_at`) VALUES
(1, 'Main Pump', 'RUNNING', '2026-10-01 02:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`) VALUES
(1, 'Lummies', 'lum.mbonenwi@ictuniversity.edu.cm', '12345678'),
(3, 'Henri', 'lema@henri.cm', '$2y$10$zp7pHfgNKsynJrROkfhYCONCfgCIkyxhDgzrgb2MPzFl4Sclle45K'),
(4, 'Luscher Lema', 'luscher.esdras@ictuniversity.edu.cm', '$2y$10$o2owCj6J9Bo9xeoRO7jOxukupxTPUfJZiHfMXGDh7lMgMHMx/.z.S');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `fk_history_user` (`user_id`);

--
-- Indexes for table `pumps`
--
ALTER TABLE `pumps`
  ADD PRIMARY KEY (`pump_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pumps`
--
ALTER TABLE `pumps`
  MODIFY `pump_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `history`
--
ALTER TABLE `history`
  ADD CONSTRAINT `fk_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
