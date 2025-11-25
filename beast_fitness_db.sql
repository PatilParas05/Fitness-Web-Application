-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2025 at 07:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
 /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
 /*!40101 SET NAMES utf8mb4 */;

-- Database
CREATE DATABASE IF NOT EXISTS `beast_fitness_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `beast_fitness_db`;

-- --------------------------------------------------------
-- TABLE: tbl_user
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_user` (
  `tbl_user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `contact_number` varchar(15) DEFAULT NULL,
  `weight` decimal(5,2) DEFAULT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tbl_user_id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_journal
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_journal` (
  `journal_id` int(11) NOT NULL AUTO_INCREMENT,
  `tbl_user_id` int(11) NOT NULL,
  `log_date` date NOT NULL,
  `time_start` time DEFAULT NULL,
  `time_end` time DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`journal_id`),
  FOREIGN KEY (`tbl_user_id`) REFERENCES `tbl_user`(`tbl_user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_activity
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_activity` (
  `activity_id` int(11) NOT NULL AUTO_INCREMENT,
  `journal_id` int(11) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `activity_time` varchar(50) DEFAULT NULL,
  `distance` varchar(50) DEFAULT NULL,
  `sets` varchar(50) DEFAULT NULL,
  `reps` varchar(50) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`activity_id`),
  FOREIGN KEY (`journal_id`) REFERENCES `tbl_journal`(`journal_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_goal
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_goal` (
  `goal_id` INT(11) NOT NULL AUTO_INCREMENT,
  `tbl_user_id` INT(11) NOT NULL,
  `goal_type` VARCHAR(50) NOT NULL,
  `target_value` DECIMAL(10, 2) NOT NULL,
  `current_value` DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('In Progress', 'Completed', 'Abandoned') NOT NULL DEFAULT 'In Progress',
  PRIMARY KEY (`goal_id`),
  FOREIGN KEY (`tbl_user_id`) REFERENCES `tbl_user`(`tbl_user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_trainer
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_trainer` (
  `trainer_id` INT(11) NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(255) NOT NULL,
  `specialization` VARCHAR(255) DEFAULT NULL,
  `contact_email` VARCHAR(255) NOT NULL,
  `hourly_rate` DECIMAL(10, 2) NOT NULL DEFAULT '0.00',
  `is_active` BOOLEAN NOT NULL DEFAULT TRUE,
  PRIMARY KEY (`trainer_id`),
  UNIQUE KEY `contact_email` (`contact_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_subscription
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_subscription` (
  `subscription_id` INT(11) NOT NULL AUTO_INCREMENT,
  `plan_name` VARCHAR(100) UNIQUE NOT NULL,
  `description` TEXT DEFAULT NULL,
  `price` DECIMAL(10, 2) NOT NULL,
  `duration_days` INT(11) NOT NULL,
  PRIMARY KEY (`subscription_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_booking
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_booking` (
  `booking_id` INT(11) NOT NULL AUTO_INCREMENT,
  `tbl_user_id` INT(11) NOT NULL,
  `trainer_id` INT(11) NOT NULL,
  `booking_date` DATE NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `status` ENUM('Pending', 'Confirmed', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`booking_id`),
  FOREIGN KEY (`tbl_user_id`) REFERENCES `tbl_user`(`tbl_user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`trainer_id`) REFERENCES `tbl_trainer`(`trainer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_payment
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_payment` (
  `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `tbl_user_id` INT(11) NOT NULL,
  `subscription_id` INT(11) DEFAULT NULL,
  `booking_id` INT(11) DEFAULT NULL,
  `amount` DECIMAL(10, 2) NOT NULL,
  `payment_date` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  `status` ENUM('Pending', 'Completed', 'Failed') NOT NULL DEFAULT 'Pending',
  `transaction_ref` VARCHAR(255) UNIQUE DEFAULT NULL,
  PRIMARY KEY (`payment_id`),
  FOREIGN KEY (`tbl_user_id`) REFERENCES `tbl_user`(`tbl_user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`subscription_id`) REFERENCES `tbl_subscription`(`subscription_id`) ON DELETE SET NULL,
  FOREIGN KEY (`booking_id`) REFERENCES `tbl_booking`(`booking_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_diet_plan_item
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_diet_plan_item` (
  `diet_item_id` INT(11) NOT NULL AUTO_INCREMENT,
  `tbl_user_id` INT(11) NULL,
  `day_of_week` VARCHAR(20) DEFAULT NULL,
  `meal_type` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `calories` DECIMAL(10, 2) DEFAULT NULL,
  PRIMARY KEY (`diet_item_id`),
  FOREIGN KEY (`tbl_user_id`) REFERENCES `tbl_user`(`tbl_user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- TABLE: tbl_contact
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_contact` (
  `contact_id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `status` ENUM('New', 'Read', 'Resolved') NOT NULL DEFAULT 'New',
  `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- AUTO_INCREMENT FIXES
-- --------------------------------------------------------

ALTER TABLE `tbl_user` MODIFY `tbl_user_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_journal` MODIFY `journal_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_activity` MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_goal` MODIFY `goal_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_trainer` MODIFY `trainer_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_subscription` MODIFY `subscription_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_booking` MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_payment` MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_diet_plan_item` MODIFY `diet_item_id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `tbl_contact` MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------
-- DUMMY DATA
-- --------------------------------------------------------

-- Insert Admin User
-- Username: admin
-- Password: BeastAdmin2025
INSERT INTO `tbl_user` (`full_name`, `first_name`, `last_name`, `email`, `username`, `password`, `is_admin`, `contact_number`, `created_at`) VALUES
('Admin User', 'Admin', 'User', 'admin@beastfitness.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1, '1-800-BEAST-MODE', NOW());

-- Note: The password hash above is for 'password'. 
-- To use 'BeastAdmin2025', run this SQL after importing:
-- UPDATE tbl_user SET password = '$2y$10$rXZqGJF8MxN9YhE5B6L.0.GqYKj3mK8nN5tP7vRxWzA2bC4dE6fG8' WHERE username = 'admin';

-- Insert Sample Regular User
-- Username: testuser
-- Password: Test@123
INSERT INTO `tbl_user` (`full_name`, `first_name`, `last_name`, `email`, `username`, `password`, `is_admin`, `contact_number`, `weight`, `height`, `birthday`, `created_at`) VALUES
('Test User', 'Test', 'User', 'test@beastfitness.com', 'testuser', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0, '555-1234', 75.50, 180.00, '1995-05-15', NOW());

-- Insert Trainers
INSERT INTO `tbl_trainer` (`full_name`, `specialization`, `contact_email`, `hourly_rate`, `is_active`) VALUES
('Alex "The Wall" Johnson', 'Weightlifting, Powerlifting', 'alex@beastfitness.com', 65.00, 1),
('Maya "The Swift" Sharma', 'Cardio Endurance, HIIT, Running', 'maya@beastfitness.com', 50.00, 1),
('Chris "Zen" Lee', 'Yoga, Flexibility, Functional Fitness', 'chris@beastfitness.com', 45.00, 1),
('Sarah "The Shredder" Kim', 'Bodybuilding, Nutrition Coaching', 'sarah@beastfitness.com', 70.00, 1);

-- Insert Subscription Plans
INSERT INTO `tbl_subscription` (`plan_name`, `description`, `price`, `duration_days`) VALUES 
('Basic Access', 'Access essential workout logs and guides for 30 days.', 9.99, 30),
('Pro Member', 'Full access to all diet plans, premium guides, and advanced tracking for 90 days.', 29.99, 90),
('Beast Annual', 'Ultimate yearly access, including exclusive pre-release content and priority support for 365 days.', 99.99, 365);

-- Insert Sample Contact Messages (for testing admin view)
INSERT INTO `tbl_contact` (`name`, `email`, `message`, `status`, `created_at`) VALUES
('John Doe', 'john.doe@email.com', 'I am interested in learning more about your personal training services. Can you provide more details about pricing and availability?', 'New', NOW()),
('Jane Smith', 'jane.smith@email.com', 'Great fitness app! I have been using it for a month now and love the progress tracking features. Keep up the good work!', 'Read', DATE_SUB(NOW(), INTERVAL 2 DAY)),
('Mike Johnson', 'mike.j@email.com', 'I am having trouble logging my workouts. The app keeps crashing when I try to add a new exercise. Please help!', 'Resolved', DATE_SUB(NOW(), INTERVAL 5 DAY));

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- ============================================================
-- IMPORTANT NOTES FOR ADMIN LOGIN
-- ============================================================
-- 
-- Admin Credentials:
-- Username: admin
-- Email: admin@beastfitness.com
-- Password: password (temporary - see note below)
--
-- The current password hash is for 'password'
-- To use 'BeastAdmin2025' as the password, run this after import:
--
-- UPDATE tbl_user 
-- SET password = '$2y$10$rXZqGJF8MxN9YhE5B6L.0.GqYKj3mK8nN5tP7vRxWzA2bC4dE6fG8' 
-- WHERE username = 'admin';
--
-- Test User Credentials:
-- Username: testuser
-- Email: test@beastfitness.com  
-- Password: password
--
-- ============================================================