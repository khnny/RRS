-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 05:22 AM
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
-- Database: `rrs`
--

-- --------------------------------------------------------

--
-- Table structure for table `adminpanel_users`
--

CREATE TABLE `adminpanel_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('staff','admin','super_admin') NOT NULL DEFAULT 'staff',
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `adminpanel_users`
--

INSERT INTO `adminpanel_users` (`id`, `username`, `password_hash`, `role`, `full_name`, `email`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$kvaYjV2sNf4Di/IyOyKG5u.uJLE/WK65NYu6oEZPmNzrbmswLmyhG', 'super_admin', 'Administrator', 'admin@yourdomain.com', '2025-04-23 15:23:10', '2025-04-25 02:56:32');

-- --------------------------------------------------------

--
-- Table structure for table `table_booking`
--

CREATE TABLE `table_booking` (
  `id` int(80) NOT NULL,
  `table_type` varchar(30) NOT NULL,
  `name` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `phone` int(30) NOT NULL,
  `date` int(30) NOT NULL,
  `people_count` varchar(30) NOT NULL,
  `table_location` varchar(30) NOT NULL,
  `table_preference` varchar(30) DEFAULT NULL,
  `special_requests_text` varchar(30) DEFAULT NULL,
  `time` varchar(30) NOT NULL,
  `status` varchar(30) NOT NULL,
  `created_at` varchar(30) DEFAULT NULL,
  `physical_table_id` int(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_booking`
--

INSERT INTO `table_booking` (`id`, `table_type`, `name`, `email`, `phone`, `date`, `people_count`, `table_location`, `table_preference`, `special_requests_text`, `time`, `status`, `created_at`, `physical_table_id`) VALUES
(38, '', 'ronald', 'khenausan3@gmail.com', 2147483647, 2025, '4', '', NULL, 'hey', '18:00', 'pending', '2025-04-24 17:35:50', 7),
(39, '', 'khen andrie', 'khenausan3@gmail.com', 2147483647, 2025, '3', '', NULL, '', '17:00', 'pending', '2025-04-24 17:41:02', 12);

-- --------------------------------------------------------

--
-- Table structure for table `table_mapping`
--

CREATE TABLE `table_mapping` (
  `id` int(11) NOT NULL,
  `capacity` varchar(20) DEFAULT NULL,
  `location` varchar(20) NOT NULL,
  `availability` varchar(20) DEFAULT NULL,
  `table_type` varchar(20) NOT NULL,
  `table_preference` varchar(20) DEFAULT NULL,
  `physical_table_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `table_mapping`
--

INSERT INTO `table_mapping` (`id`, `capacity`, `location`, `availability`, `table_type`, `table_preference`, `physical_table_id`) VALUES
(2, '2', '', 'available', '', NULL, 0),
(3, '2', '', 'available', '', NULL, 0),
(4, '2', '', 'available', '', NULL, 0),
(5, '4', '', 'available', '', NULL, 0),
(6, '4', '', 'available', '', NULL, 0),
(7, '4', '', 'available', '', NULL, 0),
(8, '4', '', 'available', '', NULL, 0),
(9, '6', '', 'available', '', NULL, 0),
(10, '6', '', 'available', '', NULL, 0),
(11, '6', '', 'available', '', NULL, 0),
(12, '6', '', 'available', '', NULL, 0),
(13, '8', '', 'available', '', NULL, 0),
(14, '8', '', 'available', '', NULL, 0),
(15, '8', '', 'available', '', NULL, 0),
(16, '8', '', 'available', '', NULL, 0),
(19, '10', '', 'available', '', NULL, 0),
(21, '2', 'outdoor', 'available', '', NULL, 123214),
(22, '2', 'outdoor', 'available', '', NULL, 123214);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `adminpanel_users`
--
ALTER TABLE `adminpanel_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `table_booking`
--
ALTER TABLE `table_booking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `table_mapping`
--
ALTER TABLE `table_mapping`
  ADD PRIMARY KEY (`id`,`physical_table_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `adminpanel_users`
--
ALTER TABLE `adminpanel_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `table_booking`
--
ALTER TABLE `table_booking`
  MODIFY `id` int(80) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `table_mapping`
--
ALTER TABLE `table_mapping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
