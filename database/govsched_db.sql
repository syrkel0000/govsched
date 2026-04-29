-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 29, 2026 at 03:49 PM
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
-- Database: `govsched_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `slot_id` int(11) NOT NULL,
  `office` varchar(50) NOT NULL DEFAULT 'Cabanatuan City',
  `appointment_date` date NOT NULL,
  `request_type` enum('self','other') DEFAULT 'self',
  `full_name` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `for_name` varchar(100) DEFAULT NULL,
  `for_relationship` varchar(50) DEFAULT NULL,
  `is_minor` tinyint(1) DEFAULT 0,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `reference_no` varchar(50) DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `document_id`, `slot_id`, `office`, `appointment_date`, `request_type`, `full_name`, `age`, `birthdate`, `address`, `email`, `contact`, `civil_status`, `gender`, `for_name`, `for_relationship`, `is_minor`, `guardian_name`, `guardian_contact`, `reference_no`, `status`, `created_at`) VALUES
(2, 2, 1, 9, 'Cabanatuan City', '2026-05-01', 'self', 'Mich Dalisay', 19, '2008-03-29', 'palayan', 'dalisay@gmail.com', '09223312121', 'Single', 'Male', NULL, NULL, 0, NULL, NULL, 'GS-69F20BF19E29E', 'pending', '2026-04-29 13:47:29');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`) VALUES
(1, 'Passport'),
(2, 'PSA Birth Certificate'),
(3, 'Driver\'s License'),
(4, 'Marriage Certificate'),
(5, 'SSS'),
(6, 'Pag-IBIG'),
(7, 'PhilHealth'),
(8, 'National ID');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `slot_time` varchar(20) NOT NULL,
  `office` varchar(50) NOT NULL DEFAULT 'Cabanatuan City',
  `max_capacity` int(11) NOT NULL DEFAULT 20
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `slot_time`, `office`, `max_capacity`) VALUES
(9, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(10, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(11, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(12, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(13, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(14, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(15, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(16, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(17, '08:00 AM - 09:00 AM', 'Palayan City', 20),
(18, '09:00 AM - 10:00 AM', 'Palayan City', 20),
(19, '10:00 AM - 11:00 AM', 'Palayan City', 20),
(20, '11:00 AM - 12:00 PM', 'Palayan City', 20),
(21, '01:00 PM - 02:00 PM', 'Palayan City', 20),
(22, '02:00 PM - 03:00 PM', 'Palayan City', 20),
(23, '03:00 PM - 04:00 PM', 'Palayan City', 20),
(24, '04:00 PM - 05:00 PM', 'Palayan City', 20);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('applicant','admin') DEFAULT 'applicant',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Administrator', 'admin@govsched.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2026-04-28 12:19:26'),
(2, 'Mich Dalisay', 'dalisay@gmail.com', '$2y$10$Om/.GJhV3TU4uB6yGvsbYeO9/Uf8HAbXlpcLwmacg0D36mkhpDkq.', 'applicant', '2026-04-28 12:49:47');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `appointments_ibfk_2` (`document_id`),
  ADD KEY `appointments_ibfk_3` (`slot_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `time_slots` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
