-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2026 at 11:36 AM
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
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `alert_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `alert_type` varchar(20) NOT NULL DEFAULT 'NORMAL',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`alert_id`, `user_id`, `title`, `message`, `alert_type`, `created_at`) VALUES
(1, 1, 'All systems normal', 'No active critical alerts detected.', 'NORMAL', '2026-10-16 10:07:41'),
(2, 1, 'Water level is normal', 'Reservoir is within the configured safe range.', 'NORMAL', '2026-10-16 10:07:41'),
(3, 1, 'System check completed', 'All monitored sensors are functioning.', 'INFO', '2026-10-16 10:07:41');

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
(14, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-01 03:25:34'),
(15, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-03 06:09:11'),
(16, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-03 13:03:34'),
(17, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-03 13:16:55'),
(18, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:23:37'),
(19, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:30:37'),
(20, 3, '1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:30:39'),
(21, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:30:40'),
(22, 3, '1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:30:41'),
(23, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:30:42'),
(24, 3, '1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:30:43'),
(25, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:30:44'),
(26, 3, '1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:30:45'),
(27, 3, '1', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:30:46'),
(28, 3, '1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:30:47'),
(29, 3, '0', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:40:13'),
(30, 3, 'Pump 1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:40:17'),
(31, 3, '0', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:40:18'),
(32, 3, 'Pump 1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:40:24'),
(33, 3, '0', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:40:25'),
(34, 3, 'Pump 1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:40:27'),
(35, 3, '0', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:40:28'),
(36, 3, 'Pump 1', 'Pump Started', NULL, NULL, 'Completed', '2026-10-22 08:41:31'),
(37, 3, '0', 'Pump Stopped', 15, 80.00, 'Completed', '2026-10-22 08:41:53');

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
(1, 'Main Pump', 'STOPPED', '2026-10-01 02:01:38');

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
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`alert_id`);

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
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `alert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `history`
--
ALTER TABLE `history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

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
