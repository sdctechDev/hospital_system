-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2026 at 02:11 AM
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
-- Database: `hospital_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `full_name`, `email`, `password`, `created_at`) VALUES
(1, 'ADMIN_Daniel', 'admindaniel06@hospital.com', '$2y$10$x1XZFXmVw3nVpYv/2QKVMOx8qElgUiJDHaXAm7HjLJvHtKfFY7rZa', '2026-10-02 21:12:06');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `reason` text DEFAULT NULL,
  `fee` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `active_slot` varchar(40) GENERATED ALWAYS AS (if(`status` = 'cancelled',NULL,concat(`doctor_id`,'|',`appointment_date`,'|',`appointment_time`))) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `reason`, `fee`, `status`, `created_at`) VALUES
(1, 1, 5, '2026-10-06', '10:30:00', 'Operate on my stone heart, I need a soft one', 20000.00, 'completed', '2026-10-02 22:55:31'),
(2, 1, 6, '2026-10-12', '10:00:00', 'Save my life!!!', 15000.00, 'cancelled', '2026-10-02 23:27:41'),
(3, 1, 1, '2026-10-19', '10:30:00', 'My premolars', 8000.00, 'cancelled', '2026-10-02 23:32:22'),
(4, 1, 3, '2026-10-08', '09:30:00', 'good morning my doctor!', 9000.00, 'cancelled', '2026-10-02 23:58:55'),
(5, 1, 4, '2026-10-13', '11:00:00', 'Money', 11000.00, 'pending', '2026-10-03 00:04:30');

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `doctor_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `specialization_id` int(11) NOT NULL,
  `consultation_fee` decimal(10,2) NOT NULL,
  `bio` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`doctor_id`, `full_name`, `email`, `password`, `phone`, `specialization_id`, `consultation_fee`, `bio`, `status`, `created_at`) VALUES
(1, 'Salako Ayomide', 'salako@hospital.com', '$2y$10$rjJ45e5pSCScwXVVgXlMPexaWVVZxCUt.17qr1n/bo845KgWNCxMi', '08041941941', 6, 8000.00, 'Dentist offering checkups, fillings and cleanings.', 'active', '2026-10-02 22:23:29'),
(2, 'Kunle Afolabi', 'kunle@hospital.com', '$2b$12$zCIQfCEvJv46jcam7XfY5.dsM1AgxnN4JCFs/Q4ozY4SOkzWlL87.', '08077000002', 7, 12000.00, 'Psychiatrist treating anxiety, depression and mood disorders.', 'active', '2026-10-02 22:23:29'),
(3, 'Abel Anita', 'hauwa@hospital.com', '$2b$12$zCIQfCEvJv46jcam7XfY5.dsM1AgxnN4JCFs/Q4ozY4SOkzWlL87.', '08027389177', 8, 9000.00, 'Radiologist specializing in X-ray, ultrasound and MRI reports.', 'active', '2026-10-02 22:23:29'),
(4, 'Damian Desmond', 'damian@hospital.com', '$2b$12$zCIQfCEvJv46jcam7XfY5.dsM1AgxnN4JCFs/Q4ozY4SOkzWlL87.', '08034546776', 9, 11000.00, 'Anesthesiologist for pre-surgery assessment and pain management.', 'active', '2026-10-02 22:23:29'),
(5, 'Babalola Faith', 'babalola@hospital.com', '$2y$10$dhIbYsKVM7qSCPSTJ2Ac.ONTIrj6U.m6vY6GJVNN3/ach5DprhlvO', '08077041914', 10, 20000.00, 'General surgeon with 12 years of experience.', 'active', '2026-10-02 22:23:29'),
(6, 'Itachi Uchiha', 'itachi@hospital.com', '$2b$12$zCIQfCEvJv46jcam7XfY5.dsM1AgxnN4JCFs/Q4ozY4SOkzWlL87.', '08082992221', 2, 15000.00, 'Cardiologist focused on heart failure and hypertension. Japanese old art of treatment.', 'active', '2026-10-02 22:23:29'),
(7, 'Omolaiye Johnpaul', 'johnny@hospital.com', '$2y$10$/.Lk2wyrPdU8HFUk4/IBOeTZ.dcqDGf5sKxuEsN5cF5wuBAJBFuHm', '08094889284', 5, 6000.00, 'Procedures like hysterectomies, fibroid removals, and laparoscopic surgeries.', 'active', '2026-10-02 22:54:10');

-- --------------------------------------------------------

--
-- Table structure for table `doctor_availability`
--

CREATE TABLE `doctor_availability` (
  `availability_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('Mon','Tue','Wed','Thu','Fri','Sat','Sun') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` int(11) NOT NULL DEFAULT 30
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `doctor_availability`
--

INSERT INTO `doctor_availability` (`availability_id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `slot_duration`) VALUES
(1, 6, 'Mon', '09:00:00', '13:00:00', 30),
(2, 6, 'Tue', '09:00:00', '13:00:00', 30),
(3, 6, 'Wed', '09:00:00', '13:00:00', 30),
(4, 6, 'Thu', '09:00:00', '13:00:00', 30),
(5, 6, 'Fri', '09:00:00', '13:00:00', 30),
(6, 1, 'Mon', '09:00:00', '13:00:00', 30),
(7, 1, 'Tue', '09:00:00', '13:00:00', 30),
(8, 1, 'Wed', '09:00:00', '13:00:00', 30),
(9, 1, 'Thu', '09:00:00', '13:00:00', 30),
(10, 1, 'Fri', '09:00:00', '13:00:00', 30),
(11, 2, 'Mon', '09:00:00', '13:00:00', 30),
(12, 2, 'Tue', '09:00:00', '13:00:00', 30),
(13, 2, 'Wed', '09:00:00', '13:00:00', 30),
(14, 2, 'Thu', '09:00:00', '13:00:00', 30),
(15, 2, 'Fri', '09:00:00', '13:00:00', 30),
(16, 3, 'Mon', '09:00:00', '13:00:00', 30),
(17, 3, 'Tue', '09:00:00', '13:00:00', 30),
(18, 3, 'Wed', '09:00:00', '13:00:00', 30),
(19, 3, 'Thu', '09:00:00', '13:00:00', 30),
(20, 3, 'Fri', '09:00:00', '13:00:00', 30),
(21, 4, 'Mon', '09:00:00', '13:00:00', 30),
(22, 4, 'Tue', '09:00:00', '13:00:00', 30),
(23, 4, 'Wed', '09:00:00', '13:00:00', 30),
(24, 4, 'Thu', '09:00:00', '13:00:00', 30),
(25, 4, 'Fri', '09:00:00', '13:00:00', 30),
(26, 5, 'Mon', '09:00:00', '13:00:00', 30),
(27, 5, 'Tue', '09:00:00', '13:00:00', 30),
(28, 5, 'Wed', '09:00:00', '13:00:00', 30),
(29, 5, 'Thu', '09:00:00', '13:00:00', 30),
(30, 5, 'Fri', '09:00:00', '13:00:00', 30);

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `full_name`, `email`, `password`, `phone`, `gender`, `date_of_birth`, `address`, `created_at`) VALUES
(1, 'Daniel Ayelade', 'danerry30@gmail.com', '$2y$10$SFhoECma35oK56XFK/cQfO7Wo3z/5/kvCNoSq6W1sUYJP62Ps8qTC', '08088796119', 'male', NULL, '3 Oyeleye Oyelere Street', '2026-10-02 22:04:55');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `appointment_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('card','bank_transfer') NOT NULL,
  `transaction_ref` varchar(50) NOT NULL,
  `status` enum('success','failed','refunded') NOT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `refund_ref` varchar(50) DEFAULT NULL,
  `refunded_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `appointment_id`, `amount`, `payment_method`, `transaction_ref`, `status`, `paid_at`, `refund_ref`, `refunded_at`) VALUES
(1, 1, 20000.00, 'bank_transfer', 'TXND3F7BE1D8A4E', 'success', '2026-10-02 22:56:13', NULL, NULL),
(2, 2, 15000.00, 'bank_transfer', 'TXN54597B43B064', 'success', '2026-10-02 23:31:18', NULL, NULL),
(3, 3, 8000.00, 'card', 'TXNBFC9AF971A6D', 'success', '2026-10-02 23:33:56', NULL, NULL),
(4, 4, 9000.00, 'bank_transfer', 'TXNAC72B1B1491D', 'refunded', '2026-10-02 23:59:42', 'RFDA0AD9ED4F801', '2026-10-02 23:59:52');

-- --------------------------------------------------------

--
-- Table structure for table `specializations`
--

CREATE TABLE `specializations` (
  `specialization_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `specializations`
--

INSERT INTO `specializations` (`specialization_id`, `name`, `description`) VALUES
(1, 'General Practice', 'General health consultations'),
(2, 'Cardiology', 'Heart and cardiovascular care'),
(3, 'Pediatrics', 'Medical care for children'),
(4, 'Dermatology', 'Skin, hair and nail conditions'),
(5, 'Gynecology', 'Women\'s reproductive health'),
(6, 'Dentistry', 'Teeth, gums and oral health'),
(7, 'Psychiatry', 'Mental health and behavioral care'),
(8, 'Radiology', 'Medical imaging and diagnostics'),
(9, 'Anesthesiology', 'Anesthesia and pain management'),
(10, 'Surgery', 'Surgical procedures and post-operative care');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD UNIQUE KEY `unique_active_slot` (`active_slot`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`doctor_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `specialization_id` (`specialization_id`);

--
-- Indexes for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD PRIMARY KEY (`availability_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `transaction_ref` (`transaction_ref`),
  ADD KEY `appointment_id` (`appointment_id`);

--
-- Indexes for table `specializations`
--
ALTER TABLE `specializations`
  ADD PRIMARY KEY (`specialization_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `doctor_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  MODIFY `availability_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `specializations`
--
ALTER TABLE `specializations`
  MODIFY `specialization_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`specialization_id`) REFERENCES `specializations` (`specialization_id`) ON UPDATE CASCADE;

--
-- Constraints for table `doctor_availability`
--
ALTER TABLE `doctor_availability`
  ADD CONSTRAINT `doctor_availability_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`doctor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`appointment_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
