-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 11:32 PM
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
  `branch_id` int(11) DEFAULT NULL,
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

INSERT INTO `appointments` (`id`, `user_id`, `document_id`, `slot_id`, `branch_id`, `office`, `appointment_date`, `request_type`, `full_name`, `age`, `birthdate`, `address`, `email`, `contact`, `civil_status`, `gender`, `for_name`, `for_relationship`, `is_minor`, `guardian_name`, `guardian_contact`, `reference_no`, `status`, `created_at`) VALUES
(5, 5, 1, 1, 1, 'PSA - Harrison Building', '2026-05-05', 'self', 'Juan dela Cruz', 28, '1998-03-10', 'Brgy. Dicarma, Cabanatuan City', 'juan@demo.com', '09171234501', 'Single', 'Male', NULL, NULL, 0, NULL, NULL, 'GS-A1B2C3D4', 'confirmed', '2026-05-04 21:21:02'),
(6, 6, 2, 2, 1, 'PSA - Harrison Building', '2026-05-05', 'self', 'Maria Santos', 32, '1994-07-15', 'Brgy. Aduas, Cabanatuan City', 'maria@demo.com', '09181234502', 'Married', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-B2C3D4E5', 'confirmed', '2026-05-04 21:21:02'),
(7, 7, 4, 3, 1, 'PSA - Harrison Building', '2026-05-05', 'self', 'Jose Reyes', 45, '1981-01-22', 'Brgy. Sta. Teresita, Cabanatuan City', 'jose@demo.com', '09191234503', 'Married', 'Male', NULL, NULL, 0, NULL, NULL, 'GS-C3D4E5F6', 'pending', '2026-05-04 21:21:02'),
(8, 8, 5, 17, 3, 'LTO - Cabanatuan District Office', '2026-05-05', 'self', 'Ana Garcia', 23, '2003-04-18', 'Brgy. Zulueta, Cabanatuan City', 'ana@demo.com', '09201234504', 'Single', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-D4E5F6G7', 'confirmed', '2026-05-04 21:21:02'),
(9, 9, 6, 18, 3, 'LTO - Cabanatuan District Office', '2026-05-05', 'self', 'Pedro Bautista', 38, '1988-09-05', 'Brgy. Kapitan Pepe, Cabanatuan City', 'pedro@demo.com', '09211234505', 'Married', 'Male', NULL, NULL, 0, NULL, NULL, 'GS-E5F6G7H8', 'pending', '2026-05-04 21:21:02'),
(10, 10, 1, 9, 2, 'PSA - NE Pacific Mall', '2026-05-06', 'self', 'Rosa Fernandez', 29, '1997-11-30', 'Brgy. Caalibangbangan, Cabanatuan City', 'rosa@demo.com', '09221234506', 'Single', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-F6G7H8I9', 'confirmed', '2026-05-04 21:21:02'),
(11, 11, 3, 10, 2, 'PSA - NE Pacific Mall', '2026-05-06', 'self', 'Carlos Mendoza', 52, '1974-06-08', 'Brgy. Pagas, Cabanatuan City', 'carlos@demo.com', '09231234507', 'Widowed', 'Male', NULL, NULL, 0, NULL, NULL, 'GS-G7H8I9J0', 'pending', '2026-05-04 21:21:02'),
(12, 12, 8, 49, 7, 'SSS - Cabanatuan City Branch', '2026-05-06', 'self', 'Liza Villanueva', 26, '2000-02-14', 'Brgy. Hermogenes, Cabanatuan City', 'liza@demo.com', '09241234508', 'Single', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-H8I9J0K1', 'confirmed', '2026-05-04 21:21:02'),
(13, 13, 12, 57, 8, 'Pag-IBIG - Cabanatuan City Branch', '2026-05-06', 'other', 'Ramon Aquino', 41, '1985-08-20', 'Brgy. Bonifacio, Cabanatuan City', 'ramon@demo.com', '09251234509', 'Married', 'Male', 'Sofia Aquino', 'Wife', 0, NULL, NULL, 'GS-I9J0K1L2', 'pending', '2026-05-04 21:21:02'),
(14, 14, 15, 73, 10, 'PhilHealth - Cabanatuan City Branch', '2026-05-07', 'self', 'Elena Castillo', 35, '1991-05-25', 'Brgy. Rizdelis, Cabanatuan City', 'elena@demo.com', '09261234510', 'Married', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-J0K1L2M3', 'confirmed', '2026-05-04 21:21:02'),
(15, 2, 18, 81, 11, 'PhilSys - Harrison Building', '2026-05-07', 'self', 'Mich Dalisay', 22, '2004-03-12', 'Brgy. Cama Juan, Cabanatuan City', 'dalisay@gmail.com', '09271234511', 'Single', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-K1L2M3N4', 'pending', '2026-05-04 21:21:02'),
(16, 3, 6, 25, 4, 'LTO - NE Pacific Mall (DLRC)', '2026-05-07', 'self', 'Karel Gorjas', 30, '1996-10-07', 'Brgy. Lourdes, Cabanatuan City', 'gorjaskarel@gmail.com', '09281234512', 'Single', 'Male', NULL, NULL, 0, NULL, NULL, 'GS-L2M3N4O5', 'confirmed', '2026-05-04 21:21:02'),
(17, 5, 14, 65, 9, 'Pag-IBIG - Palayan City Branch', '2026-05-07', 'other', 'Juan dela Cruz', 28, '1998-03-10', 'Brgy. Dicarma, Cabanatuan City', 'juan@demo.com', '09171234501', 'Single', 'Male', 'Ricardo dela Cruz', 'Father', 0, NULL, NULL, 'GS-M3N4O5P6', 'cancelled', '2026-05-04 21:21:02'),
(18, 6, 2, 5, 1, 'PSA - Harrison Building', '2026-05-08', 'self', 'Maria Santos', 32, '1994-07-15', 'Brgy. Aduas, Cabanatuan City', 'maria@demo.com', '09181234502', 'Married', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-N4O5P6Q7', 'pending', '2026-05-04 21:21:02'),
(19, 8, 9, 50, 7, 'SSS - Cabanatuan City Branch', '2026-05-08', 'self', 'Ana Garcia', 23, '2003-04-18', 'Brgy. Zulueta, Cabanatuan City', 'ana@demo.com', '09201234504', 'Single', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-O5P6Q7R8', 'confirmed', '2026-05-04 21:21:02'),
(20, 10, 7, 33, 5, 'LTO - Nueva Ecija Licensing Center', '2026-05-08', 'self', 'Rosa Fernandez', 29, '1997-11-30', 'Brgy. Caalibangbangan, Cabanatuan City', 'rosa@demo.com', '09221234506', 'Single', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-P6Q7R8S9', 'pending', '2026-05-04 21:21:02'),
(21, 11, 16, 74, 10, 'PhilHealth - Cabanatuan City Branch', '2026-05-08', 'other', 'Carlos Mendoza', 52, '1974-06-08', 'Brgy. Pagas, Cabanatuan City', 'carlos@demo.com', '09231234507', 'Widowed', 'Male', 'Miguel Mendoza', 'Son', 1, 'Carlos Mendoza Sr.', '09231234507', 'GS-Q7R8S9T0', 'confirmed', '2026-05-04 21:21:02'),
(22, 12, 5, 41, 6, 'LTO - Palayan Extension Office', '2026-05-08', 'self', 'Liza Villanueva', 26, '2000-02-14', 'Brgy. Hermogenes, Cabanatuan City', 'liza@demo.com', '09241234508', 'Single', 'Female', NULL, NULL, 0, NULL, NULL, 'GS-R8S9T0U1', 'pending', '2026-05-04 21:21:02'),
(23, 13, 3, 13, 2, 'PSA - NE Pacific Mall', '2026-05-08', 'other', 'Ramon Aquino', 41, '1985-08-20', 'Brgy. Bonifacio, Cabanatuan City', 'ramon@demo.com', '09251234509', 'Married', 'Male', 'Linda Aquino', 'Mother', 0, NULL, NULL, 'GS-S9T0U1V2', 'cancelled', '2026-05-04 21:21:02');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `agency` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `address` text NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `agency`, `name`, `address`, `contact`, `city`, `created_at`) VALUES
(1, 'PSA', 'PSA - Harrison Building', '3F Harrison Building, Brgy. Dicarma, Maharlika Highway', '(044) 456-9329 / (044) 940-9213', 'Cabanatuan City', '2026-05-02 01:30:05'),
(2, 'PSA', 'PSA - NE Pacific Mall', 'NE Pacific Mall, Km 111 Maharlika Highway, Brgy. H. Concepcion', '(044) 940-1382', 'Cabanatuan City', '2026-05-02 01:30:05'),
(3, 'LTO', 'LTO - Cabanatuan District Office', 'Sumacab Este, Cabanatuan City', NULL, 'Cabanatuan City', '2026-05-02 01:30:05'),
(4, 'LTO', 'LTO - NE Pacific Mall (DLRC)', 'NE Pacific Mall, Maharlika Highway, Cabanatuan City', NULL, 'Cabanatuan City', '2026-05-02 01:30:05'),
(5, 'LTO', 'LTO - Nueva Ecija Licensing Center', 'Emilio Vergara Highway, Cabanatuan City', NULL, 'Cabanatuan City', '2026-05-02 01:30:05'),
(6, 'LTO', 'LTO - Palayan Extension Office', 'Brgy. Nueva Ecija, Caimito, Palayan City', NULL, 'Palayan City', '2026-05-02 01:30:05'),
(7, 'SSS', 'SSS - Cabanatuan City Branch', 'NE Pacific Shopping Center, Km. 111 Maharlika Highway', '(044) 463-0691 / (044) 463-3996', 'Cabanatuan City', '2026-05-02 01:30:05'),
(8, 'Pag-IBIG', 'Pag-IBIG - Cabanatuan City Branch', 'Duran Bldg., Quezon District, Maharlika Highway', '(044) 600-1225', 'Cabanatuan City', '2026-05-02 01:30:05'),
(9, 'Pag-IBIG', 'Pag-IBIG - Palayan City Branch', 'Ground Floor, Government Center Building, Palayan City Business Hub', '0938-144-2333', 'Palayan City', '2026-05-02 01:30:05'),
(10, 'PhilHealth', 'PhilHealth - Cabanatuan City Branch', 'AH26 (Maharlika Highway), Cabanatuan City', '(044) 464-1479', 'Cabanatuan City', '2026-05-02 01:30:05'),
(11, 'PhilSys', 'PhilSys - Harrison Building', 'Ground Floor, Harrison Bldg., Brgy. Dicarma, Maharlika Highway', NULL, 'Cabanatuan City', '2026-05-02 01:30:05');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `agency` varchar(100) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `name`, `agency`) VALUES
(1, 'Birth Certificate', 'PSA'),
(2, 'Marriage Certificate', 'PSA'),
(3, 'Death Certificate', 'PSA'),
(4, 'CENOMAR', 'PSA'),
(5, 'Driver\'s License Application', 'LTO'),
(6, 'License Renewal', 'LTO'),
(7, 'Student Permit', 'LTO'),
(8, 'Membership Registration', 'SSS'),
(9, 'SSS ID / UMID', 'SSS'),
(10, 'Member Data Record (MDR)', 'SSS'),
(11, 'Number Application', 'SSS'),
(12, 'Membership Registration', 'Pag-IBIG'),
(13, 'MDF (Member Data Form)', 'Pag-IBIG'),
(14, 'Loan Application', 'Pag-IBIG'),
(15, 'Membership Registration', 'PhilHealth'),
(16, 'PhilHealth ID', 'PhilHealth'),
(17, 'Member Data Record (MDR)', 'PhilHealth'),
(18, 'National ID Registration', 'PhilSys'),
(19, 'ORCR', 'LTO');

-- --------------------------------------------------------

--
-- Table structure for table `time_slots`
--

CREATE TABLE `time_slots` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `slot_time` varchar(20) NOT NULL,
  `office` varchar(50) NOT NULL DEFAULT 'Cabanatuan City',
  `max_capacity` int(11) NOT NULL DEFAULT 20
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_slots`
--

INSERT INTO `time_slots` (`id`, `branch_id`, `slot_time`, `office`, `max_capacity`) VALUES
(1, 1, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(2, 1, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(3, 1, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(4, 1, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(5, 1, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(6, 1, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(7, 1, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(8, 1, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(9, 2, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(10, 2, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(11, 2, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(12, 2, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(13, 2, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(14, 2, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(15, 2, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(16, 2, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(17, 3, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(18, 3, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(19, 3, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(20, 3, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(21, 3, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(22, 3, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(23, 3, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(24, 3, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(25, 4, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(26, 4, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(27, 4, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(28, 4, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(29, 4, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(30, 4, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(31, 4, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(32, 4, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(33, 5, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(34, 5, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(35, 5, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(36, 5, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(37, 5, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(38, 5, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(39, 5, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(40, 5, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(41, 6, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(42, 6, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(43, 6, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(44, 6, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(45, 6, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(46, 6, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(47, 6, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(48, 6, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(49, 7, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(50, 7, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(51, 7, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(52, 7, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(53, 7, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(54, 7, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(55, 7, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(56, 7, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(57, 8, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(58, 8, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(59, 8, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(60, 8, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(61, 8, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(62, 8, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(63, 8, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(64, 8, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(65, 9, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(66, 9, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(67, 9, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(68, 9, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(69, 9, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(70, 9, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(71, 9, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(72, 9, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(73, 10, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(74, 10, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(75, 10, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(76, 10, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(77, 10, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(78, 10, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(79, 10, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(80, 10, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20),
(81, 11, '08:00 AM - 09:00 AM', 'Cabanatuan City', 20),
(82, 11, '09:00 AM - 10:00 AM', 'Cabanatuan City', 20),
(83, 11, '10:00 AM - 11:00 AM', 'Cabanatuan City', 20),
(84, 11, '11:00 AM - 12:00 PM', 'Cabanatuan City', 20),
(85, 11, '01:00 PM - 02:00 PM', 'Cabanatuan City', 20),
(86, 11, '02:00 PM - 03:00 PM', 'Cabanatuan City', 20),
(87, 11, '03:00 PM - 04:00 PM', 'Cabanatuan City', 20),
(88, 11, '04:00 PM - 05:00 PM', 'Cabanatuan City', 20);

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
(1, 'Administrator', 'admin@govsched.com', '$2y$10$fHgmdrDODe2ghkhrnHTgqurdqtSGGJwseDae3XTYpZW63abavIV22', 'admin', '2026-04-28 12:19:26'),
(2, 'Mich Dalisay', 'dalisay@gmail.com', '$2y$10$Om/.GJhV3TU4uB6yGvsbYeO9/Uf8HAbXlpcLwmacg0D36mkhpDkq.', 'applicant', '2026-04-28 12:49:47'),
(3, 'Karel Gorjas', 'gorjaskarel@gmail.com', '$2y$10$bIM1CBESdQgghxd2fW9MIOaKxSsSuk6V36E.UUZj5gnlU9Hm.ca.K', 'applicant', '2026-04-29 13:53:33'),
(4, 'Admin 2 Nga-nga', 'admin2@govsched.com', '$2y$10$4hWuntIx9c/thz6Klc5fnut0PsrmzddziNPEruLbz2okj/LY3znOC', 'admin', '2026-04-29 14:09:57'),
(5, 'Juan dela Cruz', 'juan@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(6, 'Maria Santos', 'maria@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(7, 'Jose Reyes', 'jose@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(8, 'Ana Garcia', 'ana@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(9, 'Pedro Bautista', 'pedro@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(10, 'Rosa Fernandez', 'rosa@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(11, 'Carlos Mendoza', 'carlos@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(12, 'Liza Villanueva', 'liza@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(13, 'Ramon Aquino', 'ramon@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56'),
(14, 'Elena Castillo', 'elena@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uXvTGjT7K', 'applicant', '2026-05-04 21:13:56');

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
  ADD KEY `appointments_ibfk_3` (`slot_id`),
  ADD KEY `fk_appt_branch` (`branch_id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_slot_branch` (`branch_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `time_slots`
--
ALTER TABLE `time_slots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`document_id`) REFERENCES `documents` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`slot_id`) REFERENCES `time_slots` (`id`),
  ADD CONSTRAINT `fk_appt_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `time_slots`
--
ALTER TABLE `time_slots`
  ADD CONSTRAINT `fk_slot_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
