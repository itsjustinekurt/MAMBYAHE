-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 07, 2025 at 01:29 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `user_auth`
--

-- --------------------------------------------------------

--
-- Table structure for table `associations`
--

CREATE TABLE `associations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `chairman` varchar(255) DEFAULT NULL,
  `chairman_contact` varchar(100) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `num_members` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `associations`
--

INSERT INTO `associations` (`id`, `name`, `chairman`, `chairman_contact`, `address`, `num_members`, `created_at`) VALUES
(1, 'TAYTODA', 'Chito de Luna', '09271180685', 'Tayamaan', 0, '2025-05-05 05:57:47'),
(2, 'BSCTODA', 'Michael Manalo', '09123456789', 'Balansay', 0, '2025-05-05 07:51:13');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) DEFAULT NULL,
  `passenger_name` varchar(100) NOT NULL,
  `pickup` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `seats` int(11) NOT NULL,
  `fare` decimal(10,2) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','completed','cancelled') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `booked_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_method` enum('cash','card') NOT NULL DEFAULT 'cash',
  `driver_response_time` timestamp NULL DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `passenger_id`, `passenger_name`, `pickup`, `destination`, `seats`, `fare`, `driver_id`, `status`, `rejection_reason`, `booked_at`, `payment_method`, `driver_response_time`, `created_at`) VALUES
(44, NULL, 'grasya', 'Poblacion', 'CATV', 1, 15.00, 7, 'pending', NULL, '2025-05-02 11:45:57', 'cash', NULL, '2025-05-04 22:06:59'),
(45, NULL, 'grasya', 'Poblacion', 'CATV', 1, 15.00, 7, 'pending', NULL, '2025-05-03 16:27:03', 'cash', NULL, '2025-05-04 22:06:59'),
(46, NULL, 'grasya', 'Poblacion', 'CATV', 1, 15.00, 7, 'pending', NULL, '2025-05-03 17:55:14', 'cash', NULL, '2025-05-04 22:06:59'),
(47, NULL, 'grasya', 'Poblacion', 'CATV', 1, 15.00, 7, 'pending', NULL, '2025-05-03 18:12:48', 'cash', NULL, '2025-05-04 22:06:59'),
(49, NULL, 'grasya', 'Poblacion', 'CATV', 3, 45.00, 7, 'accepted', NULL, '2025-05-03 18:48:53', 'cash', '2025-05-04 14:50:03', '2025-05-04 22:06:59'),
(50, NULL, 'grasya', 'Poblacion', 'Boribor', 2, 28.00, 7, 'cancelled', NULL, '2025-05-03 19:19:06', 'cash', '2025-05-03 19:24:43', '2025-05-04 22:06:59'),
(51, NULL, 'grasya', 'Poblacion', 'CATV', 1, 15.00, 7, 'cancelled', NULL, '2025-05-04 08:27:16', 'cash', '2025-05-04 08:28:16', '2025-05-04 22:06:59'),
(52, NULL, 'grasya', 'Poblacion', 'Provincial Capitol', 4, 52.00, 7, 'cancelled', NULL, '2025-05-04 08:38:03', 'cash', '2025-05-04 08:38:37', '2025-05-04 22:06:59'),
(53, NULL, 'grasya', 'Payompon', 'Hospital', 3, 42.00, 7, 'cancelled', NULL, '2025-05-04 09:24:07', 'cash', '2025-05-04 09:30:22', '2025-05-04 22:06:59'),
(54, NULL, 'grasya', 'Poblacion', 'Dike', 4, 52.00, 7, 'cancelled', NULL, '2025-05-04 11:15:09', 'cash', '2025-05-04 11:15:58', '2025-05-04 22:06:59'),
(55, 1, 'grasya', 'Poblacion', 'CATV', 1, 15.00, 7, 'accepted', NULL, '2025-05-04 14:53:33', 'cash', '2025-05-04 15:48:26', '2025-05-04 22:53:33'),
(56, 1, 'grasya', 'Poblacion', 'CATV', 1, 15.00, 7, 'cancelled', NULL, '2025-05-05 00:36:56', 'cash', '2025-05-05 00:38:01', '2025-05-05 08:36:56'),
(57, 1, 'grasya', 'Payompon', 'Hospital', 1, 14.00, 7, 'pending', NULL, '2025-05-05 03:55:32', 'cash', NULL, '2025-05-05 11:55:32');

-- --------------------------------------------------------

--
-- Table structure for table `chairman`
--

CREATE TABLE `chairman` (
  `chairman_id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `chairman`
--

INSERT INTO `chairman` (`chairman_id`, `fullname`, `username`, `password`) VALUES
(1, 'clarito', 'clarito', '123');

-- --------------------------------------------------------

--
-- Table structure for table `driver`
--

CREATE TABLE `driver` (
  `driver_id` int(11) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `franchise_no` varchar(100) DEFAULT NULL,
  `or_no` varchar(100) DEFAULT NULL,
  `make` varchar(100) DEFAULT NULL,
  `motor_no` varchar(100) DEFAULT NULL,
  `chassis_no` varchar(100) DEFAULT NULL,
  `plate_no` varchar(50) DEFAULT NULL,
  `toda` varchar(255) NOT NULL,
  `gov_id_type` text NOT NULL,
  `gov_id_picture` varchar(255) DEFAULT NULL,
  `is_online` enum('online','offline') DEFAULT 'offline',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `role` enum('passenger','driver') DEFAULT 'driver',
  `profile_pic` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT '',
  `suspension_end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `driver`
--

INSERT INTO `driver` (`driver_id`, `fullname`, `username`, `password`, `nationality`, `dob`, `address`, `franchise_no`, `or_no`, `make`, `motor_no`, `chassis_no`, `plate_no`, `toda`, `gov_id_type`, `gov_id_picture`, `is_online`, `created_at`, `status`, `role`, `profile_pic`, `phone`, `suspension_end`) VALUES
(2, 'Chito De Luna', 'chitohey', '$2y$10$akmkam42cFsZNygge6vguupI4GswhlCjgNWVKQc.U1wNNvsve3ykq', 'Filipino', '1996-09-22', 'Tayamaan', '9324', '32432', 'Toyota', '324543f', '34df5', '223g4d', 'taytoda', '', 'uploads/driver_ids/68038def3b138_Pictubbbre1.png', 'online', '2025-04-19 11:50:07', 'rejected', 'driver', NULL, '', NULL),
(3, 'Juan Dela Cruz', 'juan1', 'hashedpass', 'Filipino', '1980-01-01', '123 St', 'FR123', 'OR456', 'Yamaha', 'MN001', 'CH001', 'XYZ123', '', 'Driver\'s License', 'id1.jpg', '', '2025-04-21 01:19:42', 'pending', 'driver', NULL, '', NULL),
(4, 'Pedro Santos', 'pedro2', 'hashedpass', 'Filipino', '1985-03-10', '456 Ave', 'FR124', 'OR457', 'Honda', 'MN002', 'CH002', 'ABC789', '', 'UMID', 'id2.jpg', '', '2025-04-21 01:19:42', 'pending', 'driver', NULL, '', NULL),
(5, 'Maria Lopez', 'maria3', 'hashedpass', 'Filipino', '1990-07-25', '789 Blvd', 'FR125', 'OR458', 'Suzuki', 'MN003', 'CH003', 'LMN456', '', 'PhilHealth', 'id3.jpg', '', '2025-04-21 01:19:42', 'rejected', 'driver', NULL, '', NULL),
(6, 'Barok Bot', 'bito', '$2y$10$gyaTr7P6N4GEy/jFog2UVuPJcTFt2V2Lxw/PkiVXg444LFDlgvKXO', 'Filipino', '2001-09-12', 'Balansay', '5674', '809', 'Suzuki', '34809543', '232', '76523', '', '', 'uploads/driver_ids/6812d7af268b1_Screenshot 2025-03-30 160719.png', '', '2025-05-01 02:08:47', 'rejected', 'driver', NULL, '', NULL),
(7, 'Dong Wa', 'dowoo', '$2y$10$aTRVf0DqV6KsBsLTOKpsCej/JsMNKO5QznkA.IVPyJaSF4p2BGz3e', 'American', '1993-10-03', 'Biringan', '24432', '4324', 'BARAKo', '6554', '76', '46tg435', 'BSCTODA', '', 'uploads/driver_ids/6812dcf7bde7e_Screenshot 2025-03-30 160816.png', 'online', '2025-05-01 02:31:19', 'approved', 'driver', 'driver_7_1746402706.jpg', '09271180685', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `driver_locations`
--

CREATE TABLE `driver_locations` (
  `location_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `driver_locations`
--

INSERT INTO `driver_locations` (`location_id`, `driver_id`, `latitude`, `longitude`, `updated_at`) VALUES
(1, 7, 13.22702300, 120.56574200, '2025-05-02 04:24:11'),
(2, 7, 13.22702300, 120.56574200, '2025-05-02 05:13:55'),
(3, 7, 13.22702300, 120.56574200, '2025-05-02 05:21:17'),
(4, 7, 13.22702300, 120.56574200, '2025-05-02 05:34:17'),
(5, 7, 13.22702300, 120.56574200, '2025-05-02 05:34:19'),
(6, 7, 13.22702300, 120.56574200, '2025-05-02 05:38:54'),
(7, 7, 13.22702300, 120.56574200, '2025-05-02 05:40:37'),
(8, 7, 13.22702300, 120.56574200, '2025-05-02 05:43:25'),
(9, 7, 13.22702300, 120.56574200, '2025-05-02 05:45:07'),
(10, 7, 13.22702300, 120.56574200, '2025-05-02 05:51:59'),
(11, 7, 13.22702300, 120.56574200, '2025-05-02 05:54:41'),
(12, 7, 13.22702300, 120.56574200, '2025-05-02 05:58:37'),
(13, 7, 13.22702300, 120.56574200, '2025-05-02 06:00:14'),
(14, 7, 13.22702300, 120.56574200, '2025-05-02 06:01:23'),
(15, 7, 13.22702300, 120.56574200, '2025-05-02 06:02:02'),
(16, 7, 13.22702300, 120.56574200, '2025-05-02 06:08:24'),
(17, 7, 13.22702300, 120.56574200, '2025-05-02 06:18:47'),
(18, 7, 13.22702300, 120.56574200, '2025-05-02 06:24:10'),
(19, 7, 13.22702300, 120.56574200, '2025-05-02 06:26:20'),
(20, 7, 13.22702300, 120.56574200, '2025-05-02 06:33:27'),
(21, 7, 13.22702300, 120.56574200, '2025-05-02 06:36:38'),
(22, 7, 13.22702300, 120.56574200, '2025-05-02 06:47:02'),
(23, 7, 13.22702300, 120.56574200, '2025-05-02 06:59:33'),
(24, 7, 13.22702300, 120.56574200, '2025-05-02 07:12:47'),
(25, 7, 13.22702300, 120.56574200, '2025-05-02 07:13:22'),
(26, 7, 13.22702300, 120.56574200, '2025-05-02 07:15:35'),
(27, 7, 13.22702300, 120.56574200, '2025-05-02 07:15:47'),
(28, 7, 13.22702300, 120.56574200, '2025-05-02 07:15:52'),
(29, 7, 13.22702300, 120.56574200, '2025-05-02 07:27:27'),
(30, 7, 13.22702300, 120.56574200, '2025-05-02 07:28:51'),
(31, 7, 13.22702300, 120.56574200, '2025-05-02 08:12:07'),
(32, 7, 13.22702300, 120.56574200, '2025-05-02 08:15:09'),
(33, 7, 13.22702300, 120.56574200, '2025-05-02 08:20:45'),
(34, 7, 13.22702300, 120.56574200, '2025-05-02 08:21:24'),
(35, 7, 13.22702300, 120.56574200, '2025-05-02 08:32:30'),
(36, 7, 13.22702300, 120.56574200, '2025-05-02 08:34:41'),
(37, 7, 13.22702300, 120.56574200, '2025-05-02 08:43:54'),
(38, 7, 13.22702300, 120.56574200, '2025-05-02 08:49:26'),
(39, 7, 13.22702300, 120.56574200, '2025-05-02 08:52:22'),
(40, 7, 13.22702300, 120.56574200, '2025-05-02 08:54:38'),
(41, 7, 13.22702300, 120.56574200, '2025-05-02 09:00:34'),
(42, 7, 13.22702300, 120.56574200, '2025-05-02 09:10:56'),
(43, 7, 13.22702300, 120.56574200, '2025-05-02 09:13:21'),
(44, 7, 13.22702300, 120.56574200, '2025-05-02 09:16:47'),
(45, 7, 13.22702300, 120.56574200, '2025-05-02 09:19:33'),
(46, 7, 13.22702300, 120.56574200, '2025-05-02 09:20:28'),
(47, 7, 13.22702300, 120.56574200, '2025-05-02 09:20:29'),
(48, 7, 13.22702300, 120.56574200, '2025-05-02 09:21:35'),
(49, 7, 13.22702300, 120.56574200, '2025-05-02 09:21:47'),
(50, 7, 13.22702300, 120.56574200, '2025-05-02 09:30:42'),
(51, 7, 13.22702300, 120.56574200, '2025-05-02 09:31:38'),
(52, 7, 13.22702300, 120.56574200, '2025-05-02 09:32:39'),
(53, 7, 13.22702300, 120.56574200, '2025-05-02 09:34:23'),
(54, 7, 13.22702300, 120.56574200, '2025-05-02 09:35:59'),
(55, 7, 13.22702300, 120.56574200, '2025-05-02 09:37:09'),
(56, 7, 13.22702300, 120.56574200, '2025-05-02 09:38:05'),
(57, 7, 13.22702300, 120.56574200, '2025-05-02 09:39:35'),
(58, 7, 13.22702300, 120.56574200, '2025-05-02 09:40:36'),
(59, 7, 13.22702300, 120.56574200, '2025-05-02 09:40:45'),
(60, 7, 13.22702300, 120.56574200, '2025-05-02 09:43:53'),
(61, 7, 13.22702300, 120.56574200, '2025-05-02 09:46:28'),
(62, 7, 13.22702300, 120.56574200, '2025-05-02 09:49:17'),
(63, 7, 13.22702300, 120.56574200, '2025-05-02 09:51:51'),
(64, 7, 13.22702300, 120.56574200, '2025-05-02 09:51:57'),
(65, 7, 13.22702300, 120.56574200, '2025-05-02 09:58:37'),
(66, 7, 13.22702300, 120.56574200, '2025-05-02 09:59:33'),
(67, 7, 13.22702300, 120.56574200, '2025-05-02 09:59:58'),
(68, 7, 13.22702300, 120.56574200, '2025-05-02 10:03:21'),
(69, 7, 13.22702300, 120.56574200, '2025-05-02 10:03:49'),
(70, 7, 13.22702300, 120.56574200, '2025-05-02 10:07:47'),
(71, 7, 13.22702300, 120.56574200, '2025-05-02 10:08:06'),
(72, 7, 13.22702300, 120.56574200, '2025-05-02 10:10:52'),
(73, 7, 13.22702300, 120.56574200, '2025-05-02 10:27:41'),
(74, 7, 13.22702300, 120.56574200, '2025-05-02 10:34:25'),
(75, 7, 13.22702300, 120.56574200, '2025-05-02 10:37:45'),
(76, 7, 13.22702300, 120.56574200, '2025-05-02 10:39:14'),
(77, 7, 13.22702300, 120.56574200, '2025-05-02 11:13:55'),
(78, 7, 13.22702300, 120.56574200, '2025-05-02 11:14:05'),
(79, 7, 13.22702300, 120.56574200, '2025-05-02 11:16:10'),
(80, 7, 13.22702300, 120.56574200, '2025-05-02 11:20:11'),
(81, 7, 13.22702300, 120.56574200, '2025-05-02 11:24:36'),
(82, 7, 13.22702300, 120.56574200, '2025-05-02 11:25:45'),
(83, 7, 13.22702300, 120.56574200, '2025-05-02 11:26:57'),
(84, 7, 13.22702300, 120.56574200, '2025-05-02 11:27:56'),
(85, 7, 13.22702300, 120.56574200, '2025-05-02 11:28:23'),
(86, 7, 13.22702300, 120.56574200, '2025-05-02 11:29:44'),
(87, 7, 13.22702300, 120.56574200, '2025-05-02 11:30:07'),
(88, 7, 13.22702300, 120.56574200, '2025-05-02 11:39:17'),
(89, 7, 13.22702300, 120.56574200, '2025-05-02 11:43:23'),
(90, 7, 13.22702300, 120.56574200, '2025-05-02 11:43:40'),
(91, 7, 13.22702300, 120.56574200, '2025-05-02 11:44:09'),
(92, 7, 13.22702300, 120.56574200, '2025-05-02 11:44:11'),
(93, 7, 13.22702300, 120.56574200, '2025-05-02 11:46:05'),
(94, 7, 13.22702300, 120.56574200, '2025-05-02 11:51:09'),
(95, 7, 13.22702300, 120.56574200, '2025-05-02 11:53:31'),
(96, 7, 13.22702300, 120.56574200, '2025-05-03 16:26:49'),
(97, 7, 13.22702300, 120.56574200, '2025-05-03 16:27:09'),
(98, 7, 13.22702300, 120.56574200, '2025-05-03 16:36:48'),
(99, 7, 13.22702300, 120.56574200, '2025-05-03 17:34:26'),
(100, 7, 13.22702300, 120.56574200, '2025-05-03 17:39:08'),
(101, 7, 13.22702300, 120.56574200, '2025-05-03 17:48:17'),
(102, 7, 13.22702300, 120.56574200, '2025-05-03 17:50:44'),
(103, 7, 13.22702300, 120.56574200, '2025-05-03 18:22:38'),
(104, 7, 13.22702300, 120.56574200, '2025-05-03 18:24:49'),
(105, 7, 13.22702300, 120.56574200, '2025-05-03 18:24:57'),
(106, 7, 13.22702300, 120.56574200, '2025-05-03 18:25:03'),
(107, 7, 13.22702300, 120.56574200, '2025-05-03 18:28:36'),
(108, 7, 13.22702300, 120.56574200, '2025-05-03 18:31:12'),
(109, 7, 13.22702300, 120.56574200, '2025-05-03 18:33:10'),
(110, 7, 13.22702300, 120.56574200, '2025-05-03 18:33:13'),
(111, 7, 13.22702300, 120.56574200, '2025-05-03 18:37:45'),
(112, 7, 13.22702300, 120.56574200, '2025-05-03 18:40:45'),
(113, 7, 13.22702300, 120.56574200, '2025-05-03 18:40:49'),
(114, 7, 13.22702300, 120.56574200, '2025-05-03 18:40:53'),
(115, 7, 13.22702300, 120.56574200, '2025-05-03 18:41:23'),
(116, 7, 13.22702300, 120.56574200, '2025-05-03 18:41:45'),
(117, 7, 13.22702300, 120.56574200, '2025-05-03 18:42:09'),
(118, 7, 13.22702300, 120.56574200, '2025-05-03 18:42:42'),
(119, 7, 13.22702300, 120.56574200, '2025-05-03 18:44:17'),
(120, 7, 13.22702300, 120.56574200, '2025-05-03 18:44:31'),
(121, 7, 13.22702300, 120.56574200, '2025-05-03 18:45:26'),
(122, 7, 13.22702300, 120.56574200, '2025-05-03 18:46:55'),
(123, 7, 13.22702300, 120.56574200, '2025-05-03 18:49:01'),
(124, 7, 13.22702300, 120.56574200, '2025-05-03 18:50:40'),
(125, 7, 13.22702300, 120.56574200, '2025-05-03 18:52:03'),
(126, 7, 13.22702300, 120.56574200, '2025-05-03 18:53:36'),
(127, 7, 13.22702300, 120.56574200, '2025-05-03 18:55:08'),
(128, 7, 13.22702300, 120.56574200, '2025-05-03 18:56:50'),
(129, 7, 13.22702300, 120.56574200, '2025-05-03 18:58:45'),
(130, 7, 13.22702300, 120.56574200, '2025-05-03 19:00:24'),
(131, 7, 13.22702300, 120.56574200, '2025-05-03 19:01:55'),
(132, 7, 13.22702300, 120.56574200, '2025-05-03 19:04:54'),
(133, 7, 13.22702300, 120.56574200, '2025-05-03 19:06:34'),
(134, 7, 13.22702300, 120.56574200, '2025-05-03 19:20:05'),
(135, 7, 13.22702300, 120.56574200, '2025-05-03 19:24:38'),
(136, 7, 13.22702300, 120.56574200, '2025-05-03 19:25:04'),
(137, 7, 13.22702300, 120.56574200, '2025-05-03 19:48:13'),
(138, 7, 13.22702300, 120.56574200, '2025-05-04 05:29:05'),
(139, 7, 13.22702300, 120.56574200, '2025-05-04 05:59:09'),
(140, 7, 13.22702300, 120.56574200, '2025-05-04 05:59:14'),
(141, 7, 13.22702300, 120.56574200, '2025-05-04 06:00:03'),
(142, 7, 13.22702300, 120.56574200, '2025-05-04 06:00:12'),
(143, 7, 13.22702300, 120.56574200, '2025-05-04 06:01:51'),
(144, 7, 13.22702300, 120.56574200, '2025-05-04 06:01:58'),
(145, 7, 13.22702300, 120.56574200, '2025-05-04 06:02:57'),
(146, 7, 13.22702300, 120.56574200, '2025-05-04 06:03:04'),
(147, 7, 13.22702300, 120.56574200, '2025-05-04 06:04:10'),
(148, 7, 13.22702300, 120.56574200, '2025-05-04 06:04:19'),
(149, 7, 13.22702300, 120.56574200, '2025-05-04 06:05:21'),
(150, 7, 13.22702300, 120.56574200, '2025-05-04 06:07:30'),
(151, 7, 13.22702300, 120.56574200, '2025-05-04 06:08:54'),
(152, 7, 13.22702300, 120.56574200, '2025-05-04 06:10:39'),
(153, 7, 13.22702300, 120.56574200, '2025-05-04 06:11:58'),
(154, 7, 13.22702300, 120.56574200, '2025-05-04 06:12:06'),
(155, 7, 13.22702300, 120.56574200, '2025-05-04 06:13:08'),
(156, 7, 13.22702300, 120.56574200, '2025-05-04 06:15:25'),
(157, 7, 13.22702300, 120.56574200, '2025-05-04 06:16:33'),
(158, 7, 13.22702300, 120.56574200, '2025-05-04 06:16:43'),
(159, 7, 13.22702300, 120.56574200, '2025-05-04 06:18:04'),
(160, 7, 13.22702300, 120.56574200, '2025-05-04 06:18:18'),
(161, 7, 13.22702300, 120.56574200, '2025-05-04 06:19:21'),
(162, 7, 13.22702300, 120.56574200, '2025-05-04 06:20:21'),
(163, 7, 13.22702300, 120.56574200, '2025-05-04 06:20:33'),
(164, 7, 13.22702300, 120.56574200, '2025-05-04 06:21:45'),
(165, 7, 13.22702300, 120.56574200, '2025-05-04 06:21:47'),
(166, 7, 13.22702300, 120.56574200, '2025-05-04 06:24:11'),
(167, 7, 13.22702300, 120.56574200, '2025-05-04 06:27:56'),
(168, 7, 13.22702300, 120.56574200, '2025-05-04 06:28:05'),
(169, 7, 13.22702300, 120.56574200, '2025-05-04 08:27:28'),
(170, 7, 13.22702300, 120.56574200, '2025-05-04 08:31:17'),
(171, 7, 13.22702300, 120.56574200, '2025-05-04 08:31:26'),
(172, 7, 13.22702300, 120.56574200, '2025-05-04 08:38:27'),
(173, 7, 13.22702300, 120.56574200, '2025-05-04 08:39:40'),
(174, 7, 13.22702300, 120.56574200, '2025-05-04 08:39:56'),
(175, 7, 13.22702300, 120.56574200, '2025-05-04 09:24:23'),
(176, 7, 13.22702300, 120.56574200, '2025-05-04 09:24:49'),
(177, 7, 13.22702300, 120.56574200, '2025-05-04 09:27:06'),
(178, 7, 13.22702300, 120.56574200, '2025-05-04 09:32:20'),
(179, 7, 13.22702300, 120.56574200, '2025-05-04 09:32:26'),
(180, 7, 13.22702300, 120.56574200, '2025-05-04 09:32:33'),
(181, 7, 13.22702300, 120.56574200, '2025-05-04 11:15:36'),
(182, 7, 13.22702300, 120.56574200, '2025-05-04 14:25:23'),
(183, 7, 13.22702300, 120.56574200, '2025-05-04 14:26:44'),
(184, 7, 13.22702300, 120.56574200, '2025-05-04 14:28:54'),
(185, 7, 13.22702300, 120.56574200, '2025-05-04 14:30:29'),
(186, 7, 13.22702300, 120.56574200, '2025-05-04 14:30:40'),
(187, 7, 13.22702300, 120.56574200, '2025-05-04 14:34:12'),
(188, 7, 13.22702300, 120.56574200, '2025-05-04 14:38:19'),
(189, 7, 13.22702300, 120.56574200, '2025-05-04 14:39:37'),
(190, 7, 13.22702300, 120.56574200, '2025-05-04 14:39:44'),
(191, 7, 13.22702300, 120.56574200, '2025-05-04 14:40:03'),
(192, 7, 13.22702300, 120.56574200, '2025-05-04 14:46:54'),
(193, 7, 13.22702300, 120.56574200, '2025-05-04 14:47:08'),
(194, 7, 13.22702300, 120.56574200, '2025-05-04 14:50:00'),
(195, 7, 13.22702300, 120.56574200, '2025-05-04 14:53:49'),
(196, 7, 13.22702300, 120.56574200, '2025-05-04 15:15:52'),
(197, 7, 13.22702300, 120.56574200, '2025-05-04 15:18:18'),
(198, 7, 13.22702300, 120.56574200, '2025-05-04 15:23:49'),
(199, 7, 13.22702300, 120.56574200, '2025-05-04 15:27:15'),
(200, 7, 13.22702300, 120.56574200, '2025-05-04 15:27:45'),
(201, 7, 13.22702300, 120.56574200, '2025-05-04 15:31:39'),
(202, 7, 13.22702300, 120.56574200, '2025-05-04 15:37:11'),
(203, 7, 13.22702300, 120.56574200, '2025-05-04 15:40:38'),
(204, 7, 13.22702300, 120.56574200, '2025-05-04 15:40:49'),
(205, 7, 13.22702300, 120.56574200, '2025-05-04 15:48:23'),
(206, 7, 13.22702300, 120.56574200, '2025-05-04 16:05:02'),
(207, 7, 13.22702300, 120.56574200, '2025-05-04 16:08:37'),
(208, 7, 13.22702300, 120.56574200, '2025-05-04 16:10:16'),
(209, 7, 13.22702300, 120.56574200, '2025-05-04 16:23:52'),
(210, 7, 13.22702300, 120.56574200, '2025-05-04 16:25:29'),
(211, 7, 13.22702300, 120.56574200, '2025-05-04 16:30:30'),
(212, 7, 13.22702300, 120.56574200, '2025-05-04 16:30:37'),
(213, 7, 13.22702300, 120.56574200, '2025-05-04 16:37:50'),
(214, 7, 13.22702300, 120.56574200, '2025-05-04 17:08:21'),
(215, 7, 13.22702300, 120.56574200, '2025-05-04 17:08:33'),
(216, 7, 13.22702300, 120.56574200, '2025-05-04 17:10:49'),
(217, 7, 13.22702300, 120.56574200, '2025-05-04 17:12:31'),
(218, 7, 13.22702300, 120.56574200, '2025-05-04 23:38:25'),
(219, 7, 13.22702300, 120.56574200, '2025-05-05 00:35:29'),
(220, 7, 13.22702300, 120.56574200, '2025-05-05 00:37:45'),
(221, 7, 13.22702300, 120.56574200, '2025-05-05 00:38:06'),
(222, 7, 13.22702300, 120.56574200, '2025-05-05 00:42:04'),
(223, 7, 13.22702300, 120.56574200, '2025-05-05 00:42:35'),
(224, 7, 13.22702300, 120.56574200, '2025-05-05 00:43:14'),
(225, 7, 13.22702300, 120.56574200, '2025-05-05 00:45:58'),
(226, 7, 13.22702300, 120.56574200, '2025-05-05 00:46:03'),
(227, 7, 13.22702300, 120.56574200, '2025-05-05 01:20:42'),
(228, 7, 13.22702300, 120.56574200, '2025-05-05 01:22:34'),
(229, 7, 13.22702300, 120.56574200, '2025-05-05 01:35:56'),
(230, 7, 13.22702300, 120.56574200, '2025-05-05 02:00:12'),
(231, 7, 13.22702300, 120.56574200, '2025-05-05 02:37:00'),
(232, 7, 13.22702300, 120.56574200, '2025-05-05 02:38:26'),
(233, 7, 13.22702300, 120.56574200, '2025-05-05 02:44:52'),
(234, 7, 13.22702300, 120.56574200, '2025-05-05 02:46:47'),
(235, 7, 13.22702300, 120.56574200, '2025-05-05 02:57:49'),
(236, 7, 13.22702300, 120.56574200, '2025-05-05 03:04:21'),
(237, 7, 13.22702300, 120.56574200, '2025-05-05 03:11:13'),
(238, 7, 13.22702300, 120.56574200, '2025-05-05 03:13:35'),
(239, 7, 13.22702300, 120.56574200, '2025-05-05 03:13:43'),
(240, 7, 13.22702300, 120.56574200, '2025-05-05 03:14:05'),
(241, 7, 13.22702300, 120.56574200, '2025-05-05 03:16:39'),
(242, 7, 13.22702300, 120.56574200, '2025-05-05 03:19:29');

-- --------------------------------------------------------

--
-- Table structure for table `fare_matrix`
--

CREATE TABLE `fare_matrix` (
  `id` int(11) NOT NULL,
  `origin` varchar(100) NOT NULL,
  `destination` varchar(100) NOT NULL,
  `students_senior` decimal(10,2) NOT NULL,
  `fare` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `fare_matrix`
--

INSERT INTO `fare_matrix` (`id`, `origin`, `destination`, `students_senior`, `fare`) VALUES
(1, 'Poblacion', 'CATV', 13.00, 15.00),
(2, 'Poblacion', 'Dike', 15.00, 13.00),
(3, 'Payompon', 'Airport/Forestry/OMECO/Pag Asa', 15.00, 13.00),
(4, 'Poblacion', 'Provincial Capitol', 16.00, 13.00),
(5, 'Poblacion', 'Airport/Forestry/OMECO/Pag Asa', 16.00, 13.00),
(6, 'Poblacion', 'Hospital', 16.00, 13.00),
(7, 'Payompon', 'Hospital', 17.00, 14.00),
(8, 'Payompon', 'Sabungan', 17.00, 14.00),
(9, 'Poblacion', 'Boribor', 17.00, 14.00),
(10, 'Poblacion', 'Dapi-Opening', 16.00, 13.00),
(11, 'Poblacion', 'Dapi-Center', 17.00, 14.00),
(12, 'Poblacion', 'Dapi-End', 19.00, 15.00),
(13, 'Poblacion', 'Boning', 18.00, 15.00),
(14, 'Poblacion', 'Paradise', 18.00, 15.00),
(15, 'Poblacion', 'L.A. Subdivision', 19.00, 15.00),
(16, 'Poblacion', 'Balibago', 19.00, 15.00),
(17, 'Hospital', 'Dapi', 19.00, 15.00),
(18, 'Poblacion', 'Maasim', 20.00, 18.00),
(19, 'Poblacion', 'Aroma', 17.00, 14.00),
(20, 'Poblacion', 'Tayamaan Proper', 22.00, 18.00),
(21, 'Poblacion', 'Nangul', 26.00, 21.00),
(22, 'Poblacion', 'Ragasras', 26.00, 21.00),
(23, 'Poblacion', 'Dungon', 29.00, 24.00),
(24, 'Poblacion', 'Tikian', 30.00, 24.00),
(25, 'Poblacion', 'Parola', 35.00, 28.00),
(26, 'Poblacion', 'Quibrada', 35.00, 28.00),
(27, 'Poblacion', 'Lagdaan', 30.00, 24.00),
(28, 'Poblacion', 'Tuguilan', 30.00, 24.00),
(29, 'Tayamaan', 'Hospital', 18.00, 15.00),
(30, 'Dungon', 'Tayamaan', 22.00, 20.00),
(31, 'Tayamaan', 'Tikian', 20.00, 18.00);

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `rating`, `comment`, `created_at`) VALUES
(1, 4, 'pangit magdrive', '2025-04-16 09:24:25');

-- --------------------------------------------------------

--
-- Table structure for table `hidden_notifications`
--

CREATE TABLE `hidden_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `hidden_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mtfrb_complaints`
--

CREATE TABLE `mtfrb_complaints` (
  `id` int(11) NOT NULL,
  `driver_name` varchar(255) NOT NULL,
  `plate_number` varchar(50) NOT NULL,
  `reason` text NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `complainant_driver_id` int(11) DEFAULT NULL,
  `respondent_passenger_id` int(11) DEFAULT NULL,
  `respondent_driver_id` int(11) DEFAULT NULL,
  `association_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('driver','passenger') NOT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `driver_id` int(11) DEFAULT NULL,
  `passenger_id` int(11) DEFAULT NULL,
  `booking_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `user_type`, `type`, `message`, `status`, `created_at`, `driver_id`, `passenger_id`, `booking_id`) VALUES
(32, 0, 'driver', 'New Booking', 'New booking request from grasya\nPickup: Poblacion\nDrop-off: CATV\nSeats: 1\nTotal Fare: â‚±15', 'read', '2025-05-02 11:45:57', 7, NULL, NULL),
(33, 0, 'driver', 'New Booking', 'New booking request from grasya\nPickup: Poblacion\nDrop-off: CATV\nSeats: 1\nTotal Fare: â‚±15', 'read', '2025-05-03 16:27:03', 7, NULL, NULL),
(34, 0, 'driver', 'New Booking', 'New booking request from grasya\nPickup: Poblacion\nDrop-off: CATV\nSeats: 1\nTotal Fare: â‚±15', 'read', '2025-05-03 17:55:14', 7, NULL, NULL),
(35, 0, 'driver', 'New Booking', 'New booking request from grasya\nPickup: Poblacion\nDrop-off: CATV\nSeats: 1\nTotal Fare: â‚±15', 'read', '2025-05-03 18:12:48', 7, NULL, NULL),
(36, 0, 'driver', 'New Booking', 'New booking request from grasya\nPickup: Poblacion\nDrop-off: CATV\nSeats: 3\nTotal Fare: â‚±45', 'read', '2025-05-03 18:48:53', 7, 1, 49),
(37, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by the driver. Your ride is on the way!', 'unread', '2025-05-03 19:06:46', NULL, 1, NULL),
(38, 0, 'driver', 'Booking Accepted', 'Your booking has been accepted by the driver. Your ride is on the way!', 'read', '2025-05-03 19:06:46', 7, NULL, 49),
(39, 0, 'driver', 'New Booking', 'New booking request from grasya\nPickup: Poblacion\nDrop-off: Boribor\nSeats: 2\nTotal Fare: â‚±28', 'read', '2025-05-03 19:19:06', 7, 1, 50),
(40, 0, 'passenger', 'Booking Cancelled', 'Your booking has been cancelled by the driver. Please try booking another driver.', 'unread', '2025-05-03 19:24:43', NULL, 1, NULL),
(41, 0, 'driver', 'Booking Cancelled', 'Your booking has been cancelled by the driver. Please try booking another driver.', 'read', '2025-05-03 19:24:43', 7, NULL, 50),
(42, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by the driver. Your ride is on the way!', 'unread', '2025-05-04 06:16:38', NULL, 1, NULL),
(43, 0, 'driver', 'Booking Accepted', 'Your booking has been accepted by the driver. Your ride is on the way!', 'read', '2025-05-04 06:16:38', 7, NULL, 49),
(44, 0, 'driver', 'New Booking', 'You have a new booking request.', 'read', '2025-05-04 08:27:16', 7, 1, 51),
(45, 0, 'passenger', 'Booking Cancelled', 'Your booking has been cancelled by the driver. Please try booking another driver.', 'unread', '2025-05-04 08:28:16', NULL, 1, NULL),
(46, 0, 'driver', 'Booking Cancelled', 'Your booking has been cancelled by the driver. Please try booking another driver.', 'read', '2025-05-04 08:28:16', 7, NULL, 51),
(47, 0, 'driver', 'New Booking', 'You have a new booking request.', 'read', '2025-05-04 08:38:03', 7, 1, 52),
(48, 0, 'passenger', 'Booking Cancelled', 'Your booking has been cancelled by Dong Woo\n\nReason: Ayaw ko nga, kasi busy ako.\n\nBooking Details:\nPickup: Poblacion\nDrop-off: Provincial Capitol\nDate: Not specified\nTime: Not specified\nSeats: 4\nTotal Fare: â‚±52.00', 'unread', '2025-05-04 08:38:37', NULL, 1, 52),
(49, 0, 'driver', 'New Booking', 'You have a new booking request.', 'read', '2025-05-04 09:24:08', 7, 1, 53),
(50, 0, 'passenger', 'Booking Cancelled', 'Your booking has been cancelled by Dong Woo\n\nReason: ayaw niya sayo\n\nBooking Details:\nPickup: Payompon\nDrop-off: Hospital\nDate: Not specified\nTime: Not specified\nSeats: 3\nTotal Fare: â‚±42.00', 'unread', '2025-05-04 09:27:20', NULL, 1, 53),
(51, 0, 'passenger', 'Booking Cancelled', 'Your booking has been cancelled by Dong Woo\n\nReason: ayaw niya sayo\n\nBooking Details:\nPickup: Payompon\nDrop-off: Hospital\nDate: Not specified\nTime: Not specified\nSeats: 3\nTotal Fare: â‚±42.00', 'unread', '2025-05-04 09:30:22', NULL, 1, 53),
(52, 0, 'driver', 'New Booking', 'You have a new booking request.', 'read', '2025-05-04 11:15:09', 7, 1, 54),
(53, 0, 'passenger', 'Booking Cancelled', 'Your booking has been cancelled by Dong Woo\n\nReason: I have something to do\n\nBooking Details:\nPickup: Poblacion\nDrop-off: Dike\nDate: Not specified\nTime: Not specified\nSeats: 4\nTotal Fare: â‚±52.00', 'unread', '2025-05-04 11:15:58', NULL, 1, 54),
(54, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: Not specified\nTime: Not specified\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:25:07', NULL, 1, 49),
(55, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: Not specified\nTime: Not specified\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:26:48', NULL, 1, 49),
(56, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:30:32', NULL, 1, 49),
(57, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:33:39', NULL, 1, 49),
(58, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:38:23', NULL, 1, 49),
(59, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:39:49', NULL, 1, 49),
(60, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:40:10', NULL, 1, 49),
(62, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:46:41', NULL, 1, 49),
(63, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:47:14', NULL, 1, 49),
(64, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:06 PM\nSeats: 3\nTotal Fare: â‚±45.00', 'unread', '2025-05-04 14:50:03', NULL, 1, 49),
(65, 0, 'driver', 'New Booking', 'You have a new booking request.', 'read', '2025-05-04 14:53:33', 7, 1, 55),
(66, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 14:53:49', NULL, 1, 55),
(67, 0, 'passenger', 'Driver Arrived', 'Your driver (Dong Woo) has arrived at your destination: CATV.', 'unread', '2025-05-04 14:53:53', NULL, 1, 55),
(68, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 14:55:55', NULL, 1, 55),
(69, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 14:55:58', NULL, 1, 55),
(70, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 14:56:35', NULL, 1, 55),
(71, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:15:58', NULL, 1, 55),
(72, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:16:00', NULL, 1, 55),
(73, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:18:24', NULL, 1, 55),
(74, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:18:28', NULL, 1, 55),
(75, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:23:53', NULL, 1, 55),
(76, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:23:56', NULL, 1, 55),
(77, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:27:21', NULL, 1, 55),
(78, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:27:23', NULL, 1, 55),
(79, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:27:53', NULL, 1, 55),
(80, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:27:55', NULL, 1, 55),
(81, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:32:09', NULL, 1, 55),
(82, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:32:12', NULL, 1, 55),
(83, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:37:11', NULL, 1, 55),
(84, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:37:13', NULL, 1, 55),
(85, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:40:52', NULL, 1, 55),
(86, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:40:55', NULL, 1, 55),
(87, 0, 'passenger', 'Booking Accepted', 'Your booking has been accepted by Dong Woo\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 4, 2025\nTime: 10:53 PM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-04 15:48:26', NULL, 1, 55),
(88, 0, 'passenger', 'Driver Arrived', 'You have arrived safely at your set drop-off point (CATV). Thank you for riding with us!', 'unread', '2025-05-04 15:48:30', NULL, 1, 55),
(89, 0, 'driver', 'New Booking', 'You have a new booking request.', 'read', '2025-05-05 00:36:56', 7, 1, 56),
(90, 0, 'passenger', 'Booking Cancelled', 'Your booking has been cancelled by Dong Woo\n\nReason: paas\n\nBooking Details:\nPickup: Poblacion\nDrop-off: CATV\nDate: May 5, 2025\nTime: 8:36 AM\nSeats: 1\nTotal Fare: â‚±15.00', 'unread', '2025-05-05 00:38:01', NULL, 1, 56),
(91, 0, 'driver', 'New Booking', 'You have a new booking request.', 'read', '2025-05-05 03:55:32', 7, 1, 57),
(92, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 04:25:52', NULL, 1, 57),
(93, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 04:25:54', NULL, 1, 57),
(94, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 04:56:43', NULL, 1, 57),
(95, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 05:00:49', NULL, 1, 57),
(96, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 05:01:26', NULL, 1, 57),
(97, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 05:05:36', NULL, 1, 57),
(98, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 05:08:37', NULL, 1, 57),
(99, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 05:10:39', NULL, 1, 57),
(100, 0, 'passenger', 'Driver Arrived', 'Your driver has arrived at the pickup location.', 'unread', '2025-05-05 05:12:39', NULL, 1, 57),
(101, 0, 'passenger', 'mtfrb', 'Reminder: Follow traffic rules and regulations.', 'unread', '2025-05-05 08:39:41', NULL, 1, NULL),
(102, 0, 'passenger', 'MTFRB', 'System maintenance scheduled for tonight.', 'unread', '2025-05-05 08:41:27', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `passenger`
--

CREATE TABLE `passenger` (
  `passenger_id` int(11) NOT NULL,
  `fullname` varchar(255) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nationality` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `gov_id` varchar(100) DEFAULT NULL,
  `id_type` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_pic` blob DEFAULT NULL,
  `role` enum('passenger','driver') DEFAULT 'passenger',
  `status` enum('active','warned','suspended','blocked') DEFAULT 'active',
  `suspension_end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `passenger`
--

INSERT INTO `passenger` (`passenger_id`, `fullname`, `username`, `dob`, `gender`, `nationality`, `address`, `phone`, `email`, `gov_id`, `id_type`, `password`, `profile_pic`, `role`, `status`, `suspension_end`) VALUES
(1, 'grasya', 'barok', '2004-09-22', 'Female', 'American', '', '43423', 'driver@example.com', '27138943', 'Passport', '$2y$10$lzCmce/UQZ657ocFerPuE.c5u6JMd2lhoVfGrRn32nS7Y5Az9FZ8K', 0x70726f66696c655f36383134366265363234646635332e38323237343236332e6a7067, 'passenger', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `passenger_complaints`
--

CREATE TABLE `passenger_complaints` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passenger_locations`
--

CREATE TABLE `passenger_locations` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `passenger_locations`
--

INSERT INTO `passenger_locations` (`id`, `passenger_id`, `latitude`, `longitude`, `updated_at`) VALUES
(1, 1, 13.227023, 120.565742, '2025-05-05 14:41:35');

-- --------------------------------------------------------

--
-- Table structure for table `passenger_reviews`
--

CREATE TABLE `passenger_reviews` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `passenger_reviews`
--

INSERT INTO `passenger_reviews` (`id`, `passenger_id`, `driver_id`, `booking_id`, `rating`, `review`, `created_at`) VALUES
(2, 1, 7, 55, 3, 'ujhh', '2025-05-04 15:48:36'),
(3, 1, 7, 57, 4, 'gfdgf', '2025-05-05 05:34:00');

-- --------------------------------------------------------

--
-- Table structure for table `passenger_trips`
--

CREATE TABLE `passenger_trips` (
  `id` int(11) NOT NULL,
  `passenger_id` int(11) DEFAULT NULL,
  `brgy_name` varchar(100) DEFAULT NULL,
  `sitio_name` varchar(50) DEFAULT NULL,
  `trip_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `ReviewID` int(11) NOT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Rating` int(11) DEFAULT NULL CHECK (`Rating` between 1 and 5),
  `ReviewText` text DEFAULT NULL,
  `CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `driver_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ride_details`
--

CREATE TABLE `ride_details` (
  `id` int(11) NOT NULL,
  `passenger_name` varchar(255) DEFAULT NULL,
  `pickup` varchar(255) DEFAULT NULL,
  `dropoff` varchar(255) DEFAULT NULL,
  `seats` int(11) DEFAULT NULL,
  `children` int(11) DEFAULT NULL,
  `adults` int(11) DEFAULT NULL,
  `citizens` int(11) DEFAULT NULL,
  `fare` decimal(10,2) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trips`
--

CREATE TABLE `trips` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `sitio_name` varchar(50) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `trips`
--

INSERT INTO `trips` (`id`, `driver_id`, `sitio_name`, `date`) VALUES
(3, 2, 'Sitio 1', '2016-06-10'),
(4, 2, 'Sitio 2', '2017-07-15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `associations`
--
ALTER TABLE `associations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `fk_bookings_passenger` (`passenger_id`);

--
-- Indexes for table `chairman`
--
ALTER TABLE `chairman`
  ADD PRIMARY KEY (`chairman_id`);

--
-- Indexes for table `driver`
--
ALTER TABLE `driver`
  ADD PRIMARY KEY (`driver_id`);

--
-- Indexes for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `fk_driver_locations_drivers` (`driver_id`);

--
-- Indexes for table `fare_matrix`
--
ALTER TABLE `fare_matrix`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hidden_notifications`
--
ALTER TABLE `hidden_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`,`notification_id`);

--
-- Indexes for table `mtfrb_complaints`
--
ALTER TABLE `mtfrb_complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_passenger` (`passenger_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `fk_notifications_drivers` (`user_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `fk_notifications_passengers` (`passenger_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `passenger`
--
ALTER TABLE `passenger`
  ADD PRIMARY KEY (`passenger_id`);

--
-- Indexes for table `passenger_complaints`
--
ALTER TABLE `passenger_complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `passenger_id` (`passenger_id`),
  ADD KEY `driver_id` (`driver_id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `passenger_locations`
--
ALTER TABLE `passenger_locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `passenger_id` (`passenger_id`);

--
-- Indexes for table `passenger_reviews`
--
ALTER TABLE `passenger_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `passenger_id` (`passenger_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `fk_reviews_driver` (`driver_id`);

--
-- Indexes for table `passenger_trips`
--
ALTER TABLE `passenger_trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `passenger_id` (`passenger_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`ReviewID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `ride_details`
--
ALTER TABLE `ride_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `trips`
--
ALTER TABLE `trips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `associations`
--
ALTER TABLE `associations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `chairman`
--
ALTER TABLE `chairman`
  MODIFY `chairman_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `driver`
--
ALTER TABLE `driver`
  MODIFY `driver_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `driver_locations`
--
ALTER TABLE `driver_locations`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `fare_matrix`
--
ALTER TABLE `fare_matrix`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hidden_notifications`
--
ALTER TABLE `hidden_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mtfrb_complaints`
--
ALTER TABLE `mtfrb_complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `passenger`
--
ALTER TABLE `passenger`
  MODIFY `passenger_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `passenger_complaints`
--
ALTER TABLE `passenger_complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passenger_locations`
--
ALTER TABLE `passenger_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `passenger_reviews`
--
ALTER TABLE `passenger_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `passenger_trips`
--
ALTER TABLE `passenger_trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `ReviewID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ride_details`
--
ALTER TABLE `ride_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trips`
--
ALTER TABLE `trips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_passenger` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE;

--
-- Constraints for table `driver_locations`
--
ALTER TABLE `driver_locations`
  ADD CONSTRAINT `driver_locations_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_driver_locations_drivers` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE;

--
-- Constraints for table `mtfrb_complaints`
--
ALTER TABLE `mtfrb_complaints`
  ADD CONSTRAINT `fk_passenger` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_passengers` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `passenger_complaints`
--
ALTER TABLE `passenger_complaints`
  ADD CONSTRAINT `passenger_complaints_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `passenger_complaints_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `passenger_complaints_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `passenger_locations`
--
ALTER TABLE `passenger_locations`
  ADD CONSTRAINT `passenger_locations_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE;

--
-- Constraints for table `passenger_reviews`
--
ALTER TABLE `passenger_reviews`
  ADD CONSTRAINT `fk_reviews_driver` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `passenger_reviews_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `passenger_reviews_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `passenger_reviews_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `passenger_trips`
--
ALTER TABLE `passenger_trips`
  ADD CONSTRAINT `passenger_trips_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `passenger` (`passenger_id`) ON DELETE CASCADE;

--
-- Constraints for table `ride_details`
--
ALTER TABLE `ride_details`
  ADD CONSTRAINT `ride_details_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`);

--
-- Constraints for table `trips`
--
ALTER TABLE `trips`
  ADD CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `driver` (`driver_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
